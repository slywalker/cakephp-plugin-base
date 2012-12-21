<?php
App::uses('Component', 'Controller');
App::uses('Inflector', 'Utility');

class BaseActionComponent extends Component {

	public $components = array('Session');

	public $autoLoad = false;

	public $flash = array(
		'element' => 'default',
		'key' => 'flash',
		'params' => array('class' => 'message'),
		'successParams' => array(),
		'errorParams' => array()
	);

	public $Controller;

	public function initialize(Controller $controller) {
		$this->Controller = $controller;
	}

	public function beforeRender(Controller $controller) {
		if (!$this->autoLoad) {
			return;
		}

		if ($controller->request->action === 'index') {
			$names = $this->_generateNames($controller->modelClass);
			$controller->set($names['plural'], $this->index());
		}
		elseif ($controller->request->action === 'view') {
			$names = $this->_generateNames($controller->modelClass);
			$id = current(($controller->request->pass) ?: array(null));
			$controller->set($names['singular'], $this->view($id));
		}
		elseif ($controller->request->action === 'add') {
			$this->add();
		}
		elseif (in_array(
			$controller->request->action,
			array('edit', 'delete')
		)) {
			$id = current(($controller->request->pass) ?: array(null));
			$this->{$controller->request->action}($id);
		}
	}

	public function index($options = array()) {
		$default = array(
			'modelClass' => $this->Controller->modelClass
		);
		$options += $default;

		return $this->Controller->paginate($options['modelClass']);
	}

	public function view($id, $options = array()) {
		$default = array(
			'modelClass' => $this->Controller->modelClass,
			'fields' => null,
			'exceptionMessage' => null
		);
		$options += $default;

		$names = $this->_generateNames($options['modelClass']);
		if (!$options['exceptionMessage']) {
			$options['exceptionMessage'] = __(
				'Invalid %s',
				__($names['singularHuman'])
			);
		}

		$Model = $this->Controller->{$options['modelClass']};

		$Model->id = $id;
		if (!$Model->exists()) {
			throw new NotFoundException($options['exceptionMessage']);
		}
		return $Model->read($options['fields'], $id);
	}

	public function add($options = array()) {
		$default = array(
			'modelClass' => $this->Controller->modelClass,
			'saveMethod' => 'save',
			'successRedirect' => array(
				'action' => 'index'
			),
			'successMessage' => null,
			'successParams' => array(),
			'errorMessage' => null,
			'errorParams' => array()
		);
		$options += $default;

		$names = $this->_generateNames($options['modelClass']);
		if (!$options['successMessage']) {
			$options['successMessage'] = __(
				'The %s has been saved.',
				__($names['singularHuman'])
			);
		}
		if (!$options['errorMessage']) {
			$options['errorMessage'] = __(
				'The %s could not be saved. Please, try again.',
				__($names['singularHuman'])
			);
		}

		$Model = $this->Controller->{$options['modelClass']};

		if ($this->Controller->request->is('post')) {
			$this->Controller->request->data(
				$options['modelClass'] . '.' . $Model->primaryKey,
				null
			);
			$Model->create();
			if ($Model->{$options['saveMethod']}($this->Controller->request->data)) {
				$this->Session->setFlash(
					$options['successMessage'],
					$this->flash['element'],
					array_merge(
						$this->flash['params'],
						$this->flash['successParams'],
						$options['successParams']
					),
					$this->flash['key']
				);
				return $this->Controller->redirect($options['successRedirect']);
			} else {
				$this->Session->setFlash(
					$options['errorMessage'],
					$this->flash['element'],
					array_merge(
						$this->flash['params'],
						$this->flash['errorParams'],
						$options['errorParams']
					),
					$this->flash['key']
				);
			}
		}
	}

	public function edit($id, $options = array()) {
		$default = array(
			'modelClass' => $this->Controller->modelClass,
			'fields' => null,
			'saveMethod' => 'save',
			'successRedirect' => array(
				'action' => 'index'
			),
			'successMessage' => null,
			'successParams' => array(),
			'errorMessage' => null,
			'errorParams' => array(),
			'exceptionMessage' => null
		);
		$options += $default;

		$names = $this->_generateNames($options['modelClass']);
		if (!$options['exceptionMessage']) {
			$options['exceptionMessage'] = __(
				'Invalid %s',
				__($names['singularHuman'])
			);
		}
		if (!$options['successMessage']) {
			$options['successMessage'] = __(
				'The %s has been saved.',
				__($names['singularHuman'])
			);
		}
		if (!$options['errorMessage']) {
			$options['errorMessage'] = __(
				'The %s could not be saved. Please, try again.',
				__($names['singularHuman'])
			);
		}

		$Model = $this->Controller->{$options['modelClass']};

		$Model->id = $id;
		if (!$Model->exists()) {
			throw new NotFoundException($options['exceptionMessage']);
		}
		if ($this->Controller->request->is('post') || $this->Controller->request->is('put')) {
			if ($Model->{$options['saveMethod']}($this->Controller->request->data)) {
				$this->Session->setFlash(
					$options['successMessage'],
					$this->flash['element'],
					array_merge(
						$this->flash['params'],
						$this->flash['successParams'],
						$options['successParams']
					),
					$this->flash['key']
				);
				return $this->Controller->redirect($options['successRedirect']);
			} else {
				$this->Session->setFlash(
					$options['errorMessage'],
					$this->flash['element'],
					array_merge(
						$this->flash['params'],
						$this->flash['errorParams'],
						$options['errorParams']
					),
					$this->flash['key']
				);
			}
		} else {
			$this->Controller->request->data = $Model->read($options['fields'], $id);
		}
	}

	public function delete($id, $options = array()) {
		$default = array(
			'modelClass' => $this->Controller->modelClass,
			'fields' => null,
			'saveMethod' => 'save',
			'successRedirect' => array(
				'action' => 'index'
			),
			'errorRedirect' => $this->Controller->referer(array(
				'action' => 'index'
			)),
			'successMessage' => null,
			'successParams' => array(),
			'errorMessage' => null,
			'errorParams' => array(),
			'exceptionMessage' => null
		);
		$options += $default;

		$names = $this->_generateNames($options['modelClass']);
		if (!$options['exceptionMessage']) {
			$options['exceptionMessage'] = __(
				'Invalid %s',
				__($names['singularHuman'])
			);
		}
		if (!$options['successMessage']) {
			$options['successMessage'] = __(
				'The %s has been deleted.',
				__($names['singularHuman'])
			);
		}
		if (!$options['errorMessage']) {
			$options['errorMessage'] = __(
				'The %s could not be deleted. Please, try again.',
				__($names['singularHuman'])
			);
		}

		$Model = $this->Controller->{$options['modelClass']};

		if (!$this->Controller->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$Model->id = $id;
		if (!$Model->exists()) {
			throw new NotFoundException($options['exceptionMessage']);
		}
		if ($Model->delete($id)) {
			$this->Session->setFlash(
				$options['successMessage'],
				$this->flash['element'],
				array_merge(
					$this->flash['params'],
					$this->flash['successParams'],
					$options['successParams']
				),
				$this->flash['key']
			);
			return $this->Controller->redirect($options['successRedirect']);
		}
		$this->Session->setFlash(
			$options['errorMessage'],
			$this->flash['element'],
			array_merge(
				$this->flash['params'],
				$this->flash['errorParams'],
				$options['errorParams']
			),
			$this->flash['key']
		);
		return $this->Controller->redirect($options['errorRedirect']);
	}

	protected function _generateNames($modelName) {
		return array(
			'singular' => Inflector::variable($modelName),
			'plural' => Inflector::variable(
				Inflector::pluralize($modelName)
			),
			'singularHuman' => Inflector::humanize(
				Inflector::underscore(
					Inflector::singularize($modelName)
				)
			),
			'pluralHuman' => Inflector::humanize(
				Inflector::underscore($modelName)
			)
		);
	}

}