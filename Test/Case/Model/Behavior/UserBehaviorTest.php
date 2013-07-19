<?php
App::uses('UserBehavior', 'Base.Model/Behavior');

class TestUserBehavior extends UserBehavior {

	const TEST_NOW = '2008-03-28 02:45:46';

	public function time() {
		return strtotime(self::TEST_NOW);
	}

}

class UserBehaviorTestCase extends CakeTestCase {

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
		$this->User->Behaviors->attach('Base.TestUser');
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
		$this->assertEquals($result['User']['last_login'], TestUserBehavior::TEST_NOW);

		$result = $this->User->updateLastLogin('999');
		$this->assertFalse($result);
	}

}