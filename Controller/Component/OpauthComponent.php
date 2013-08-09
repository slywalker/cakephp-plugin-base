<?php
App::uses('Component', 'Controller');
App::uses('CakeSession', 'Model/Datasource');

use \Opauth;

class OpauthComponent extends Component {

	public function run() {
		$config = Configure::read('Opauth');
		$Opauth = new Opauth($config);
	}

	public function callback($post) {
		return $this->_validateResponse($post);
	}

/**
 * @throws BadRequestException
 */
	protected function _validateResponse($post) {
		if (!isset($post['opauth'])) {
			throw new BadRequestException('Index `opauth` is not exsists.');
		}

		$response = unserialize(base64_decode($post['opauth']));
		if (is_array($response) && array_key_exists('error', $response)) {
			$response['validated'] = false;
		} else {
			$config = Configure::read('Opauth');
			$Opauth = new Opauth($config, false);

			if (
				empty($response['auth']) ||
				empty($response['timestamp']) ||
				empty($response['signature']) ||
				empty($response['auth']['provider']) ||
				empty($response['auth']['uid'])
			) {
				$response['error'] = array(
					'provider' => $response['auth']['provider'],
					'code' => 'invalid_auth_missing_components',
					'message' => 'Invalid auth response: Missing key auth response components.'
				);
				$response['validated'] = false;
			} elseif (!($Opauth->validate(
				sha1(print_r($response['auth'], true)),
				$response['timestamp'],
				$response['signature'],
				$reason
			))) {
				$response['error'] = array(
					'provider' => $response['auth']['provider'],
					'code' => 'invalid_auth_failed_validation',
					'message' => 'Invalid auth response: ' . $reason
				);
				$response['validated'] = false;
			} else {
				$response['validated'] = true;
			}
		}

		return $response;
	}

}