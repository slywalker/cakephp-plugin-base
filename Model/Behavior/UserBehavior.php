<?php
App::uses('Security', 'Utility');

class UserBehavior extends ModelBehavior {

	public function setup(Model $model, $config = array()) {
		$default = array(
			'fields' => array(
				'username' => 'username',
				'password' => 'password',
				'passwordToken' => 'password_token',
				'email' => 'email',
				'emailVerified' => 'email_verified',
				'emailToken' => 'email_token',
				'emailTokenExpires' => 'email_token_expires',
				'active' => 'active',
				'lastLogin' => 'last_login'
			),
			'emailTokenExpirationTime' => 86400,
			'hashMethod' => 'hash'
		);
		$config += $default;
		$this->settings[$model->alias] = $config;
	}

/**
 * Verifies a users email by a token that was sent to him via email and flags the user record as active
 *
 * @param string $token The token that wa sent to the user
 * @return array On success it returns the user data record
 * @throws InvalidArgumentException
 * @throws RuntimeException
 */
	public function verifyEmail(Model $model, $token = null) {
		$fields = $this->settings[$model->alias]['fields'];

		$user = $model->find('first', array(
			'contain' => array(),
			'conditions' => array(
				$model->alias . '.' . $fields['emailVerified'] => 0,
				$model->alias . '.' . $fields['emailToken'] => $token
			),
			'fields' => array(
				$model->primaryKey,
				$fields['email'],
				$fields['emailTokenExpires']
			)
		));

		if (empty($user)) {
			throw new InvalidArgumentException(__d('base', 'Invalid token, please check the email you were sent, and retry the verification link.'));
		}

		$expires = strtotime($user[$model->alias][$fields['emailTokenExpires']]);
		if ($expires < $this->time()) {
			throw new RuntimeException(__d('base', 'The token has expired.'));
		}

		$user[$model->alias][$fields['active']] = 1;
		$user[$model->alias][$fields['emailVerified']] = 1;
		$user[$model->alias][$fields['emailToken']] = null;
		$user[$model->alias][$fields['emailTokenExpires']] = null;

		$user = $model->save($user, array(
			'validate' => false,
			'callbacks' => false
		));
		$model->data = $user;
		return $user;
	}

/**
 * Checks the token for a password change
 *
 * @param string $token Token
 * @return mixed False or user data as array
 */
	public function checkPasswordToken(Model $model, $token = null) {
		$fields = $this->settings[$model->alias]['fields'];

		$user = $model->find('first', array(
			'contain' => array(),
			'conditions' => array(
				$model->alias . '.' . $fields['active'] => 1,
				$model->alias . '.' . $fields['passwordToken'] => $token,
				$model->alias . '.' . $fields['emailTokenExpires'] . ' >='
					=> date('Y-m-d H:i:s', $this->time())
			),
			'fields' => array(
				$model->primaryKey
			)
		));
		if (empty($user)) {
			return false;
		}
		return $user;
	}

/**
 * Resets the password
 *
 * @param array $postData Post data from controller
 * @return boolean True on success
 */
	public function resetPassword(Model $model, $postData = array()) {
		$result = false;

		$tmp = $model->validate;
		$model->validate = array(
			'new_password' => $tmp['password'],
			'confirm_password' => array(
				'required' => array(
					'rule' => array('compareFields', 'new_password', 'confirm_password'),
					'message' => __d('users', 'The passwords are not equal.')
				)
			)
		);

		$this->set($postData);
		if ($model->validates()) {
			$model->data[$model->alias]['password'] = $model->hash(
				$model->data[$model->alias]['new_password']
			);
			$model->data[$model->alias]['password_token'] = null;
			$result = $model->save($model->data, array(
				'validate' => false,
				'callbacks' => false
			));
		}

		$model->validate = $tmp;
		return $result;
	}

	public function time() {
		return time();
	}

/**
 * Generate token used by the user registration system
 *
 * @param int $length Token Length
 * @return string
 */
	public function generateToken() {
		return uniqid(md5(rand()));
	}

/**
 * Optional data manipulation before the registration record is saved
 *
 * @param array post data array
 * @param boolean Use email generation, create token, default true
 * @return array
 */
	protected function _beforeRegistration(Model $model, $postData = array(), $useEmailVerification = true) {
		if ($useEmailVerification == true) {
			$postData[$model->alias]['email_token'] = $this->generateToken();
			$postData[$model->alias]['email_token_expires'] = date('Y-m-d H:i:s', time() + 86400);
		} else {
			$postData[$model->alias]['email_verified'] = 1;
		}
		$postData[$model->alias]['active'] = 1;
		$defaultRole = Configure::read('Users.defaultRole');
		if ($defaultRole) {
			$postData[$model->alias]['role'] = $defaultRole;
		} else {
			$postData[$model->alias]['role'] = 'registered';
		}
		return $postData;
	}

/**
 * Updates the last activity field of a user
 *
 * @param string $user User ID
 * @return boolean True on success
 */
	public function updateLastLogin(Model $model, $id = null) {
		$fields = $this->settings[$model->alias]['fields'];

		$model->id = $id;
		if ($model->exists()) {
			return $model->saveField($fields['lastLogin'], date('Y-m-d H:i:s', $this->time()));
		}
		return false;
	}

}