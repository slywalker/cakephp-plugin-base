<?php
App::uses('ModelBehavior', 'Model');

class DateTimeRFCBehavior extends ModelBehavior {

	public $settings = array();

	protected $_defaults = array(
		'fields' => array('updated', 'modified', 'created'),
		'format' => array(
			'rfc2822' => 'r'
		),
	);

	public function setup(Model $Model, $config = array()) {
		$this->settings[$Model->alias] = array_merge($this->_defaults, $config);
	}

	public function afterFind(Model $Model, $results, $primary) {
		foreach ($results as &$result) {
			$result = $this->_addField($Model, $result);
		}
		return $results;
	}

	protected function _addField(Model $Model, $result) {
		$setting = $this->settings[$Model->alias];

		foreach ($setting['fields'] as $field) {
			if (!isset($result[$Model->alias][$field])) {
				continue;
			}
			foreach ($setting['format'] as $name => $format) {
				if ($datetime = $result[$Model->alias][$field]) {
					$result[$Model->alias][$field . '_' . $name] = date($format, strtotime($datetime));
				} else {
					$result[$Model->alias][$field . '_' . $name] = null;
				}
			}
		}

		return $result;
	}

}