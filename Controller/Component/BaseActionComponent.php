<?php
App::uses('Component', 'Controller');
App::uses('Inflector', 'Utility');

class BaseActionComponent extends Component {

	public $components = array('Session');

	public $autoLoad = array();

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
		if (!$autoLoad = $this->autoLoad) {
			return;
		}

		if (in_array('*', $autoLoad)) {
			$autoLoad = array('index', 'view', 'add', 'edit', 'delete');
		}

		$action = $controller->request->action;
		if (!in_array($action, $autoLoad)) {
			return;
		}

		$id = current(($controller->request->pass) ?: array(null));
		$names = $this->_generateNames($controller->modelClass);

		switch ($action) {
			case 'index':
				$controller->set($names['plural'], $this->index());
				$controller->set('_serialize', array($names['plural']));
				break;

			case 'view':
				$controller->set($names['singular'], $this->view($id));
				$controller->set('_serialize', array($names['singular']));
				break;

			case 'add':
				$this->add();
				break;

			case 'edit':
				$this->edit($id);
				break;

			case 'delete':
				$this->delete($id);
				break;
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
			'singular' => Inflector::variable(
				Inflector::singularize($modelName)
			),
			'plural' => Inflector::variable(
				Inflector::pluralize($modelName)
			),
			'singularHuman' => Inflector::humanize(
				Inflector::underscore(
					Inflector::singularize($modelName)
				)
			),
			'pluralHuman' => Inflector::humanize(
				Inflector::underscore(
					Inflector::pluralize($modelName)
				)
			)
		);
	}

}