<?php
App::uses('ModelBehavior', 'Model');

class EmConverterBehavior extends ModelBehavior {

	public function setup(Model $model, $config = array()) {
		$default = array(
			'convertEm' => 'asKV',
			'trim' => true
		);
		$config += $default;
		$this->settings[$model->alias] = $config;
	}

	public function beforeValidate(Model $model) {
		$settings = $this->settings[$model->alias];

		array_walk_recursive(
			$model->data,
			function(&$item, $key) use ($settings) {
				if ($settings['convertEm']) {
					$item = mb_convert_kana($item, $settings['convertEm']);
				}

				if ($settings['trim']) {
					$item = trim($item);
				}
			}
		);

		return true;
	}

}