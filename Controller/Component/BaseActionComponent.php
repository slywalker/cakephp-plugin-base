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
		'success' => array(
			'params' => array('class' => 'message success')
		),
		'error' => array(
			'params' => array('class' => 'message error')
		)
	);

	public $Controller;

	public function initialize(Controller $controller) {
		$this->Controller = $controller;

		$this->names = $names = $this->_generateNames($options['modelClass']);

		$this->options = array(
			'modelClass' => $controller->modelClass,
			'fields' => null,
			'saveMethod' => 'save',
			'exception' => array(
				'notFound' => __('Invalid %s', __($names['singularHuman']))
			),
			'success' => array(
				'redirect' => array('action' => 'index')
			),
			'error' => array(
				'redirect' => $controller->referer(array(
					'action' => 'index'
				))
			)
		);
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
		$default = array();
		$options = Hash::merge($this->options, $default, $options);

		return $this->Controller->paginate($options['modelClass']);
	}

	public function view($id, $options = array()) {
		$default = array();
		$options = Hash::merge($this->options, $default, $options);

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
			throw new NotFoundException($options['exception']['notFound']);
		}
		return $Model->read($options['fields'], $id);
	}

	public function add($options = array()) {
		$names = $this->names;
		$default = array(
			'success' => array(
				'message' => __('The %s has been saved.', __($names['singularHuman'])),
			),
			'error' => array(
				'message' => __('The %s could not be saved. Please, try again.', __($names['singularHuman'])),
			)
		);

		$options = Hash::merge($this->options, $default, $options);

		$Model = $this->Controller->{$options['modelClass']};

		if ($this->Controller->request->is('post')) {
			$this->Controller->request->data(
				$options['modelClass'] . '.' . $Model->primaryKey,
				null
			);
			$Model->create();
			if ($Model->{$options['saveMethod']}($this->Controller->request->data)) {
				$this->setFlash('success', $options);
				return $this->Controller->redirect($options['success']['redirect']);
			} else {
				$this->setFlash('error', $options);
			}
		}
	}

	public function edit($id, $options = array()) {
		$names = $this->names;
		$default = array(
			'success' => array(
				'message' => __('The %s has been saved.', __($names['singularHuman'])),
			),
			'error' => array(
				'message' => __('The %s could not be saved. Please, try again.', __($names['singularHuman'])),
			)
		);

		$options = Hash::merge($this->options, $default, $options);

		$Model = $this->Controller->{$options['modelClass']};

		$Model->id = $id;
		if (!$Model->exists()) {
			throw new NotFoundException($options['exception']['notFound']);
		}
		if ($this->Controller->request->is('post') || $this->Controller->request->is('put')) {
			if ($Model->{$options['saveMethod']}($this->Controller->request->data)) {
				$this->setFlash('success', $options);
				return $this->Controller->redirect($options['success']['redirect']);
			} else {
				$this->setFlash('error', $options);
			}
		} else {
			$this->Controller->request->data = $Model->read($options['fields'], $id);
		}
	}

	public function delete($id, $options = array()) {
		$names = $this->names;
		$default = array(
			'success' => array(
				'message' => __('The %s has been deleted.', __($names['singularHuman'])),
			),
			'error' => array(
				'message' => __('The %s could not be deleted. Please, try again.', __($names['singularHuman'])),
			)
		);

		$options = Hash::merge($this->options, $default, $options);

		$Model = $this->Controller->{$options['modelClass']};

		if (!$this->Controller->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$Model->id = $id;
		if (!$Model->exists()) {
			throw new NotFoundException($options['exception']['notFound']);
		}
		if ($Model->delete($id)) {
			$this->setFlash('success', $options);
			return $this->Controller->redirect($options['success']['redirect']);
		}
		$this->setFlash('error', $options);
		return $this->Controller->redirect($options['error']['redirect']);
	}

	public function setFlash($type, $options = array()) {
		if (in_array($type, array('success', 'error'))) {
			$params = array_merge($this->flash, $this->flash[$type], $options[$type]);
			extract($params);
		} else {
			$message = $type;
			$params = array_merge($this->flash, $options);
			extract($params);
		}
		CakeSession::write('Message.' . $key, compact('message', 'element', 'params'));
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