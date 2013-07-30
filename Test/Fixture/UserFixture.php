<?php
class UserFixture extends CakeTestFixture {

	public $name = 'User';

	public $table = 'users';

	public $fields = array(
			'id' => array('type' => 'string', 'null' => false, 'length' => 36, 'key' => 'primary'),
			'username' => array('type' => 'string', 'null' => false, 'default' => null),
			'slug' => array('type' => 'string', 'null' => false, 'default' => null),
			'password' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128),
			'password_token' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128),
			'email' => array('type' => 'string', 'null' => true, 'default' => null),
			'email_verified' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
			'email_token' => array('type' => 'string', 'null' => true, 'default' => null),
			'email_token_expires' => array('type' => 'datetime', 'null' => true, 'default' => null),
			'tos' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
			'active' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
			'last_action' => array('type' => 'datetime', 'null' => true, 'default' => null),
			'last_login' => array('type' => 'datetime', 'null' => true, 'default' => null),
			'is_admin' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
			'role' => array('type' => 'string', 'null' => true, 'default' => null),
			'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
			'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
			'indexes' => array(
				'PRIMARY' => array('column' => 'id', 'unique' => 1))
			);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => '1',
			'username' => 'adminuser',
			'slug' => 'adminuser',
			'password' => 'test', // test
			'password_token' => 'testtoken',
			'email' => 'adminuser@cakedc.com',
			'email_verified' => 1,
			'email_token' => 'testtoken',
			'email_token_expires' => '2008-03-25 02:45:46',
			'tos' => 1,
			'active' => 1,
			'last_action' => '2008-03-25 02:45:46',
			'last_login' => '2008-03-25 02:45:46',
			'is_admin' => 1,
			'role' => 'admin',
			'created' => '2008-03-25 02:45:46',
			'modified' => '2008-03-25 02:45:46'
		),
		array(
			'id' => '2',
			'username' => 'emailverifiedsuccess',
			'slug' => 'emailverifiedsuccess',
			'password' => 'test', // test
			'password_token' => '',
			'email' => 'emailverifiedsuccess@example.com',
			'email_verified' => 0,
			'email_token' => 'emailverifiedsuccess',
			'email_token_expires' => '2008-03-28 02:45:46',
			'tos' => 1,
			'active' => 0,
			'last_action' => '2008-03-25 02:45:46',
			'last_login' => '2008-03-25 02:45:46',
			'is_admin' => 1,
			'role' => 'admin',
			'created' => '2008-03-25 02:45:46',
			'modified' => '2008-03-25 02:45:46'
		),
		array(
			'id' => '3',
			'username' => 'emailverifiedfalse',
			'slug' => 'emailverifiedfalse',
			'password' => 'test', // test
			'password_token' => '',
			'email' => 'emailverifiedfalse@example.com',
			'email_verified' => 1,
			'email_token' => 'emailverifiedfalse',
			'email_token_expires' => '2008-03-28 02:45:46',
			'tos' => 1,
			'active' => 0,
			'last_action' => '2008-03-25 02:45:46',
			'last_login' => '2008-03-25 02:45:46',
			'is_admin' => 1,
			'role' => 'admin',
			'created' => '2008-03-25 02:45:46',
			'modified' => '2008-03-25 02:45:46'
		),
		array(
			'id' => '4',
			'username' => 'emailverifiedfalse2',
			'slug' => 'emailverifiedfalse2',
			'password' => 'test', // test
			'password_token' => '',
			'email' => 'emailverifiedfalse2@example.com',
			'email_verified' => 0,
			'email_token' => 'emailverifiedfalse2',
			'email_token_expires' => '2008-03-25 02:45:46',
			'tos' => 1,
			'active' => 0,
			'last_action' => '2008-03-25 02:45:46',
			'last_login' => '2008-03-25 02:45:46',
			'is_admin' => 1,
			'role' => 'admin',
			'created' => '2008-03-25 02:45:46',
			'modified' => '2008-03-25 02:45:46'
		),
		array(
			'id' => '5',
			'username' => 'updatelastlogin',
			'slug' => 'updatelastlogin',
			'password' => 'test', // test
			'password_token' => '',
			'email' => 'updatelastlogin@example.com',
			'email_verified' => 1,
			'email_token' => '',
			'email_token_expires' => '',
			'tos' => 1,
			'active' => 1,
			'last_action' => '',
			'last_login' => '',
			'is_admin' => 1,
			'role' => 'admin',
			'created' => '2008-03-25 02:45:46',
			'modified' => '2008-03-25 02:45:46'
		),
		array(
			'id' => '47ea303a-3cyc-k251-b313-4811c0a800bf',
			'username' => 'testuser',
			'slug' => 'testuser',
			'password' => 'secretkey', // secretkey
			'password_token' => '',
			'email' => 'testuser@cakedc.com',
			'email_verified' => '1',
			'email_token' => '',
			'email_token_expires' => '2008-03-25 02:45:46',
			'tos' => 1,
			'active' => 1,
			'last_action' => '2008-03-25 02:45:46',
			'last_login' => '2008-03-25 02:45:46',
			'is_admin' => 0,
			'role' => 'user',
			'created' => '2008-03-25 02:45:46',
			'modified' => '2008-03-25 02:45:46'
		),
		array(
			'id' => '37ea303a-3bdc-4251-b315-1316c0b300fa',
			'username' => 'user1',
			'slug' => 'user1',
			'password' => 'newpass', // newpass
			'password_token' => '',
			'email' => 'testuser1@testuser.com',
			'email_verified' => 0,
			'email_token' => 'testtoken2',
			'email_token_expires' => '2008-03-28 02:45:46',
			'tos' => 0,
			'active' => 0,
			'last_action' => '2008-03-25 02:45:46',
			'last_login' => '2008-03-25 02:45:46',
			'is_admin' => 0,
			'role' => 'user',
			'created' => '2008-03-25 02:45:46',
			'modified' => '2008-03-25 02:45:46'
		),
		array(
			'id' => '495e36a2-1f00-46b9-8247-58a367265f11',
			'username' => 'oidtest',
			'slug' => 'oistest',
			'password' => 'newpass', // newpass
			'password_token' => '',
			'email' => 'oidtest@testuser.com',
			'email_verified' => 0,
			'email_token' => 'testtoken3',
			'email_token_expires' => '2008-03-28 02:45:46',
			'tos' => 0,
			'active' => 0,
			'last_action' => '2008-03-25 02:45:46',
			'last_login' => '2008-03-25 02:45:46',
			'is_admin' => 0,
			'role' => 'user',
			'created' => '2008-03-25 02:45:46',
			'modified' => '2008-03-25 02:45:46'
		),
		array(
			'id' => '315e36a2-1fxj-46b9-8247-58a367265f11',
			'username' => 'oidtest2',
			'slug' => 'oistest',
			'password' => 'newpass', // newpass
			'password_token' => '',
			'email' => 'oidtest2@testuser.com',
			'email_verified' => 0,
			'email_token' => 'testtoken4',
			'email_token_expires' => '2008-03-28 02:45:46',
			'tos' => 1,
			'active' => 1,
			'last_action' => '2008-03-25 02:45:46',
			'last_login' => '2008-03-25 02:45:46',
			'is_admin' => 0,
			'role' => 'user',
			'created' => '2008-03-25 02:45:46',
			'modified' => '2008-03-25 02:45:46'
		),
		array(
			'id' => '515e36a2-5fjj-46b9-8247-584367265f11',
			'username' => 'resetuser',
			'slug' => 'resetuser',
			'password' => 'newpass', // newpass
			'password_token' => 'testtoken5',
			'email' => 'resetuser@testuser.com',
			'email_verified' => 1,
			'email_token' => 'testtoken2',
			'email_token_expires' => '2008-03-28 02:45:46',
			'tos' => 1,
			'active' => 1,
			'last_action' => '2008-03-25 02:45:46',
			'last_login' => '2008-03-25 02:45:46',
			'is_admin' => 0,
			'role' => 'user',
			'created' => '2008-03-25 02:45:46',
			'modified' => '2008-03-25 02:45:46'
		)
	);

}