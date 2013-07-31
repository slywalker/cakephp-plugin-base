<?php
App::uses('AccountBehavior', 'Base.Model/Behavior');
App::uses('Security', 'Utility');

class TestAccountBehavior extends AccountBehavior {

	const TEST_NOW = '2008-03-28 02:45:46';

	public function time() {
		return strtotime(self::TEST_NOW);
	}

	public function hash(Model $model, $string, $type = null, $salt = false) {
		return Security::hash($string, $type, $salt);
	}

}

class AccountBehaviorTestCase extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array('plugin.base.user');

/**
 * Start Test callback
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->User = ClassRegistry::init('Base.User');
		$this->User->Behaviors->attach('Base.TestAccount');
	}

/**
 * End a test
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		unset($this->User);
		ClassRegistry::flush();
	}

	public function testVerifyEmail() {
		$result = $this->User->verifyEmail('emailverifiedsuccess');
		$this->assertEquals($result['User']['email_token'], null);
		$this->assertEquals($result['User']['email_token_expires'], null);
		$this->assertEquals($result['User']['email_verified'], 1);
		$this->assertEquals($result['User']['active'], 1);

		$result = $this->User->data;
		$this->assertEquals($result['User']['email_token'], null);
		$this->assertEquals($result['User']['email_token_expires'], null);
		$this->assertEquals($result['User']['email_verified'], 1);
		$this->assertEquals($result['User']['active'], 1);
	}

	public function testVerifyEmailRuntimeException() {
		$this->setExpectedException('InvalidArgumentException');
		$this->User->verifyEmail('emailverifiedfalse');
	}

	public function testVerifyEmailRuntimeException2() {
		$this->setExpectedException('RuntimeException');
		$this->User->verifyEmail('emailverifiedfalse2');
	}

	public function testUpdateLastLogin() {
		$result = $this->User->updateLastLogin('5');
		$this->assertEquals($result['User']['last_login'], TestAccountBehavior::TEST_NOW);

		$result = $this->User->updateLastLogin('999');
		$this->assertFalse($result);
	}

	public function testCheckPasswordToken() {
		$result = $this->User->checkPasswordToken('testtoken');
		$this->assertFalse($result);

		$result = $this->User->checkPasswordToken('testtoken2');
		$this->assertFalse($result);

		$result = $this->User->checkPasswordToken('testtoken5');
		$this->assertEquals($result['User']['id'], '515e36a2-5fjj-46b9-8247-584367265f11');
	}

	public function testResetPassword() {
		$data = array(
			'User' => array(
				'id' => '47ea303a-3cyc-k251-b313-4811c0a800bf',
				'new_password' => 'newpassword',
				'confirm_password' => 'newpassword'
			)
		);
		$result = $this->User->resetPassword($data);
		$this->assertEquals($result['User']['password_token'], null);
		$this->assertEquals($result['User']['email_token_expires'], null);

		$data = array(
			'User' => array(
				'id' => '47ea303a-3cyc-k251-b313-4811c0a800bf',
				'new_password' => 'newpassword',
				'confirm_password' => 'typopassword'
			)
		);
		$result = $this->User->resetPassword($data);
		$this->assertFalse($result);
	}

	public function testSetEmailToken() {
		$result = $this->User->setEmailToken('1');
		$this->assertEquals($result['User']['email_token_expires'], '2008-03-29 02:45:46');
		$this->assertEquals($result['User']['email_verified'], 0);
	}

	public function testSetPasswordToken() {
		$result = $this->User->setPasswordToken('1');
		$this->assertEquals($result['User']['email_token_expires'], '2008-03-29 02:45:46');
	}

	public function testAfterSave() {
		$data = array(
			'User' => array(
				'username' => 'foo',
				'email' => 'foo@example.com'
			)
		);
		$result = $this->User->save($data);
		$this->assertEquals($result['User']['username'], 'foo');
		$this->assertEquals($result['User']['email'], 'foo@example.com');
		$this->assertEquals($result['User']['email_token_expires'], '2008-03-29 02:45:46');
		$this->assertEquals($result['User']['email_verified'], 0);

		$data = array(
			'User' => array(
				'username' => 'foo',
				'email' => 'foo@example.com',
				'old_email' => 'foo@example.com'
			)
		);
		$result = $this->User->save($data);
		$this->assertEquals($result['User']['username'], 'foo');
		$this->assertFalse(isset($result['User']['email_token']));
	}

}