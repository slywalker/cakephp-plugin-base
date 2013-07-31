<?php
App::uses('Security', 'Utility');

class AccountBehavior extends ModelBehavior {

/**
 * Setup
 *
 * @param array $config
 * @return void
 */
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
				'lastLogin' => 'last_login',
				'newPassword' => 'new_password',
				'confirmPassword' => 'confirm_password',
				'oldEmail' => 'old_email'
			),
			'emailVerification' => true,
			'emailTokenExpirationTime' => '+1 day',
			'hashMethod' => 'hash'
		);
		$this->settings[$model->alias] = Hash::merge($default, $config);
	}

	public function afterSave(Model $model, $created) {
		$fields = $this->settings[$model->alias]['fields'];
		$emailVerification = $this->settings[$model->alias]['emailVerification'];
		$data = $model->data[$model->alias];

		$oldEmail = '';
		if (!$created && isset($data[$fields['oldEmail']])) {
			$oldEmail = $data[$fields['oldEmail']];
		}

		if (
			$emailVerification &&
			isset($data[$fields['email']]) &&
			$data[$fields['email']] !== $oldEmail
		) {
			$id = $model->data[$model->alias][$model->primaryKey];
			$model->data = $this->setEmailToken($model, $id);
		}
	}

/**
 * Set email token
 *
 * @param string $id
 * @return array On success it returns the user data record
 */
	public function setEmailToken(Model $model, $id) {
		$fields = $this->settings[$model->alias]['fields'];
		$expirationTime = $this->settings[$model->alias]['emailTokenExpirationTime'];

		$data = array(
			$model->alias => array(
				$model->primaryKey => $id,
				$fields['emailToken'] => $this->generateToken(),
				$fields['emailTokenExpires'] => $this->emailTokenExpirationTime($expirationTime),
				$fields['emailVerified'] => 0
			)
		);
		return $model->save($data, array(
			'validate' => false,
			'callbacks' => false
		));
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
 * Set email token
 *
 * @param string $id
 * @return array On success it returns the user data record
 */
	public function setPasswordToken(Model $model, $id) {
		$fields = $this->settings[$model->alias]['fields'];
		$expirationTime = $this->settings[$model->alias]['emailTokenExpirationTime'];

		$data = array(
			$model->alias => array(
				$model->primaryKey => $id,
				$fields['passwordToken'] => $this->generateToken(),
				$fields['emailTokenExpires'] => $this->emailTokenExpirationTime($expirationTime),
			)
		);
		return $model->save($data, array(
			'validate' => false,
			'callbacks' => false
		));
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
		$fields = $this->settings[$model->alias]['fields'];
		$hashMethod = $this->settings[$model->alias]['hashMethod'];

		$result = false;

		$model->validator()->add(
			$fields['newPassword'],
			$model->validator()->getField($fields['password'])
		);
		$model->validator()->add($fields['confirmPassword'], array(
			'required' => array(
				'rule' => array(
					'compareFields',
					$fields['newPassword'],
					$fields['confirmPassword']
				),
				'message' => __d('base', 'The passwords are not equal.')
			)
		));

		$model->set($postData);
		if ($model->validates()) {
			$model->data[$model->alias][$fields['password']] = $model->{$hashMethod}(
				$model->data[$model->alias][$fields['newPassword']]
			);
			$model->data[$model->alias][$fields['passwordToken']] = null;
			$model->data[$model->alias][$fields['emailTokenExpires']] = null;
			$result = $model->save($model->data, array(
				'validate' => false,
				'callbacks' => false
			));
		}

		return $result;
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

/**
 * Validation method to compare two fields
 *
 * @param mixed $field1 Array or string, if array the first key is used as fieldname
 * @param string $field2 Second fieldname
 * @return boolean True on success
 */
	public function compareFields(Model $model, $field1, $field2) {
		if (is_array($field1)) {
			$field1 = key($field1);
		}

		if (
			isset($model->data[$model->alias][$field1]) &&
			isset($model->data[$model->alias][$field2]) &&
			$model->data[$model->alias][$field1] == $model->data[$model->alias][$field2]
		) {
			return true;
		}
		return false;
	}

/**
 * Generate token used by the user registration system
 *
 * @param int $length Token Length
 * @return string
 */
	public function generateToken($length = 45) {
		return substr(uniqid(md5(rand())), 0, $length);
	}

/**
 * Returns the time the email verification token expires
 *
 * @return string
 */
	public function emailTokenExpirationTime($expires) {
		if (is_string($expires)) {
			$expires = strtotime(date('Y-m-d H:i:s', $this->time()) . ' ' . $expires);
		} else {
			$expires = $this->time() + $expires;
		}
		return date('Y-m-d H:i:s', $expires);
	}

/**
 * Return time
 *
 * @return time
 */
	public function time() {
		return time();
	}

}