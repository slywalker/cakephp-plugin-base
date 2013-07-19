<?php
App::uses('Cache', 'Cache');
App::uses('Security', 'Utility');
App::uses('Inflector', 'Utility');

/*
 * CakePHP2.x CacheResultsBehavior on Memchached or APC
 */
class CacheResultsBehavior extends ModelBehavior {

	public function setup(Model $model, $config = array()) {
		$default = array(
			'config' => 'default',
			'duration' => null,
			'namespace' => Inflector::underscore($model->alias) . '_namespace_key',
		);
		$config += $default;
		$this->settings[$model->alias] = $config;
		$this->_clear[$model->alias] = false;
	}

	public function cache(Model $model, $type = 'first', $query = array()) {
		$config = $this->settings[$model->alias]['config'];

		$query += array(
			'duration' => $this->settings[$model->alias]['duration'],
			'method' => 'find'
		);

		$duration = $query['duration'];
		unset($query['duration']);

		$method = $query['method'];
		unset($query['method']);

		$key = implode('_', array(
			Inflector::underscore($model->alias),
			$this->_getNamespaceKey($model),
			$type,
			Security::hash(json_encode($query)),
		));
		if ($results = Cache::read($key, $config)) {
			return $results;
		}

		if ($results = $model->{$method}($type, $query)) {
			if ($duration) {
				Cache::set(compact('duration'), $config);
			}
			Cache::write($key, $results, $config);
		}

		return $results;
	}

	public function afterSave(Model $model, $created) {
		$config = $this->settings[$model->alias]['config'];
		$namespace = $this->settings[$model->alias]['namespace'];

		if (Cache::read($namespace, $config)) {
			$this->clearCacheResults($model);
		}
		return true;
	}

	protected function _getNamespaceKey(Model $model) {
		$config = $this->settings[$model->alias]['config'];
		$namespace = $this->settings[$model->alias]['namespace'];

		if ($namespaceKey = Cache::read($namespace, $config)) {
			return $namespaceKey;
		}

		Cache::set(array('duration' => '+999 days'), $config);
		$namespaceKey = 10000 * rand(0, 9);
		Cache::write($namespace, $namespaceKey, $config);
		return $namespaceKey;
	}

	public function clearCacheResults(Model $model) {
		$config = $this->settings[$model->alias]['config'];
		$namespace = $this->_getNamespaceKey($model);

		Cache::increment($namespace, 1, $config);
	}

}