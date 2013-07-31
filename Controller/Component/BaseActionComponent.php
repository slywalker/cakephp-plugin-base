<?php
App::uses('Component', 'Controller');
App::uses('CakeSession', 'Model/Datasource');
App::uses('Inflector', 'Utility');

class BaseActionComponent extends Component {

	public $components = array('Paginator');

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

		$this->names = $names = $this->_generateNames($controller->modelClass);

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

/**
 * index
 * - `modelClass` useing model class
 *
 * @param  array $options options
 * @return array paginate result
 */
	public function index($options = array()) {
		$default = array();
		$options = Hash::merge($this->options, $default, $options);

		return $this->Paginator->paginate($options['modelClass']);
	}

/**
 * view
 * - `modelClass` useing model class
 * - `exception`
 *     - `notFound` NotFoundException message
 * - `fields` fields param Model::read()
 * @param  $id ID
 * @param  array $options options
 * @return array Model::read result
 * @throws NotFoundException
 */
	public function view($id, $options = array()) {
		$default = array();
		$options = Hash::merge($this->options, $default, $options);

		$Model = $this->Controller->{$options['modelClass']};

		$Model->id = $id;
		if (!$Model->exists()) {
			throw new NotFoundException($options['exception']['notFound']);
		}
		return $Model->read($options['fields'], $id);
	}

/**
 * add
 * - `modelClass` useing model class
 * - `saveMethod` save method
 * - `success`
 *     - `redirect` array or string redirect url
 * @param  array $options options
 * @return void
 */
	public function add($options = array()) {
		$names = $this->names;
		$default = array(
			'success' => array(
				'message' => __('The %s has been saved.', __($names['singularHuman']))
			),
			'error' => array(
				'message' => __('The %s could not be saved. Please, try again.', __($names['singularHuman']))			)
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

/**
 * edit
 * - `modelClass` useing model class
 * - `exception`
 *     - `notFound` NotFoundException message
 * - `saveMethod` save method
 * - `success`
 *     - `redirect` array or string redirect url
 * - `fields` fields param Model::read()
 * @param  $id ID
 * @param  array $options options
 * @return void
 * @throws NotFoundException
 */
	public function edit($id, $options = array()) {
		$names = $this->names;
		$default = array(
			'contain' => array(),
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
			$Model->contain($options['contain']);
			$this->Controller->request->data = $Model->read($options['fields'], $id);
		}
	}

/**
 * delete
 * - `modelClass` useing model class
 * - `exception`
 *     - `notFound` NotFoundException message
 * - `success`
 *     - `redirect` array or string redirect url
 * - `error`
 *     - `redirect` array or string redirect url
 * @param  $id ID
 * @param  array $options options
 * @return void
 * @throws MethodNotAllowedException
 * @throws NotFoundException
 */
	public function delete($id, $options = array()) {
		$names = $this->names;
		$default = array(
			'success' => array(
				'message' => __('The %s has been deleted.', __($names['singularHuman'])),
				'redirect' => $this->Controller->referer(array(
					'action' => 'index'
				))
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
			$options = Hash::merge($this->flash, $this->flash[$type], $options[$type]);
		} else {
			if (is_string($options)) {
				$options = array('params' => $options);
			}
			if (!is_array($options['params']) && isset($this->flash[$options['params']])) {
				$options = Hash::merge($options, $this->flash[$options['params']]);
			}
			$options = Hash::merge($this->flash, $options);
			$options['message'] = $type;
		}
		CakeSession::write('Message.' . $options['key'], array(
			'message' => $options['message'],
			'element' => $options['element'],
			'params' => $options['params']
		));
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