<?php

	// init cycle
	// Call 1: GetModule (action=getmodule) 
	// Call 2: GetStore
	// Call 3: GetStatusCodes

	// download order cycle
	// Call 1: GetModule
	// Call 2: GetStatusCodes 
	// Call 3: GetCount
	// Call 4...N: GetOrders - виполняеться бесконечно пока не прийдет пустой ответ

	function echoObj($obj) {
		echo '<hr><xmp>';
		print_r($obj);
		echo '</xmp>';
	}

	class ShipWorksXML extends DOMDocument {

		private $moduleVersion = '3.0.1';
		private $schemaVersion = '1.0.0';

		public function __construct($action, $data) {
			parent::__construct('1.0', 'utf-8');
			$this->xmlStandalone = true;
			$this->formatOutput = true;
			$this->preserveWhiteSpace = true;
			$this->initRoot();

			$this->action = $action;
			$this->data = $data;
			$this->initBody();
		}

		private function initRoot() {
			$this->root = $this->createElement('ShipWorks');
			$this->root->setAttribute('moduleVersion', $this->moduleVersion);
			$this->root->setAttribute('schemaVersion', $this->schemaVersion);
			$this->appendChild($this->root);
		}

		private function initBody() {
			switch( $this->action ) {
				case 'getmodule':
					$body = $this->getModule();
					break;
				case 'getstore':
					$body = $this->getStore();
					break;
				case 'getstatuscodes':
					$body = $this->getStatusCodes();
					break;
				case 'getcount':
					$body = $this->getCount();
					break;
				case 'getorders':
					$body = $this->getOrders();
					break;
				case 'updatestatus':
				case 'updateshipment':
					$body = $this->getUpdate();
					break;
			}
			if( isset($body) )
				$this->root->appendChild($body);
		}

		public function getModule() {
			$module = $this->createElement('Module');
			$platform = $this->createElement('Platform', 'Suredone');
			$developer = $this->createElement('Developer', 'dima.rgb (dima.rgb@gmail.com)');
			$capabilities = $this->createElement('Capabilities');
			$downloadStrategy = $this->createElement('DownloadStrategy', 'ByModifiedTime');
			$onlineCustomerID = $this->createElement('OnlineCustomerID');
			$onlineCustomerID->setAttribute('supported', 'true');
			$onlineCustomerID->setAttribute('dataType', 'numeric');
			$onlineStatus = $this->createElement('OnlineStatus');
			$onlineStatus->setAttribute('supported', 'true');
			$onlineStatus->setAttribute('dataType', 'numeric');
			$onlineStatus->setAttribute('supportsComments', 'true');
			$onlineShipmentUpdate = $this->createElement('OnlineShipmentUpdate');
			$onlineShipmentUpdate->setAttribute('supported', 'true');
			$capabilities->appendChild($downloadStrategy);
			$capabilities->appendChild($onlineCustomerID);
			$capabilities->appendChild($onlineStatus);
			$capabilities->appendChild($onlineShipmentUpdate);
			$module->appendChild($platform);
			$module->appendChild($developer);
			$module->appendChild($capabilities);
			return $module;
		}

		public function getStore() {
			$store = $this->createElement('Store');
			foreach( $this->data as $name => $value ) {
				$Name = ucfirst($name);
				$store->appendChild($this->createElement($Name, $value));
			}
			return $store;
		}

		public function echoXML($isXML) {
			if( $isXML ) {
				// HTTP/1.1
				header('Content-Type: text/xml; charset=utf-8');
				header('Cache-Control: no-store, no-cache, must-revalidate');
				header('Cache-Control: post-check=0, pre-check=0', false);
				// HTTP/1.0
				header('Pragma: no-cache');
				echo $this->saveXML();
			} else
				echo '<hr><xmp>' . $this->saveXML() . '</xmp>';
			// $this->saveInHistory();
		}

		private function saveInHistory() {
			$fileName = 'history/sw_'.
				date('Y-m-d_H-i-s').
				'-'.
				(round(microtime(true) * 1000) % 1000).
				'.xml';
			$this->save($fileName);
		}

	}

	// $action = 'getstore';
	// $data = array(
	// 	'name' => 'stan', // site_user
	// 	'companyOrOwner' => 'smetner', // business_name
	// 	'email' => 'stan@smetner.com', // business_email
	// 	'street1' => '', // business_street
	// 	'street2' => '', // business_street_2
	// 	'city' => 'New York', // business_city
	// 	'state' => 'NY', // business_state
	// 	'postalCode' => '10001', // business_zip
	// 	'country' => 'US', // business_country
	// 	'phone' => '', // business_phone
	// 	'website' => 'stan.suredone.com', // site_domain
	// );

	// $xml = new ShipWorksXML($action, $data);
	// $xml->echoXML(true);

	function httpRequestSend($method = 'GET', $url, $headers = null, $data = null) {
		$http = array(
			'method' => $method,
			'ignore_errors' => true,
			'header' => $headers,
		);
		if( $data !== null ) {
			$data = http_build_query($data);
			if( $method == 'GET' )
				$url .= '?'. $data;
			else
				$http['content'] = $data;
		}
		$context = stream_context_create(array('http' => $http));
		$stream = fopen($url, 'rb', false, $context);
		$response = $stream ? stream_get_contents($stream) : false;
		if( $response === false )
			throw new Exception("Failed $method $url: $php_errormsg");
		$response = json_decode($response);
		if( $response === null )
			throw new Exception("Failed to decode $response as json");
		return $response;
	}

	// $request = new HttpRequest('https://api.suredone.com/v1/options/all', HttpRequest::METH_POST);
	// $request->addHeaders(array(
	// 	'Content-Type' => 'multipart/form-data',
	// 	'X-Auth-User' => 'smetner',
	// 	'X-Auth-Token' => '48533192907E87B3754470CE8DF8C773920C0869D6D1BD2A70F159DB81D56F46295EF0585F6163D36VPELH82AJEYGWHYNSJR2XR6PYLEWFBE32IGI50LK4DZZO0USM08TO0LXQE943RO5BIKDWAZ4VO9WYM4MFWHSCEZVCA68VZ0',
	// ));

	try {
		$authResponse = httpRequestSend('POST', 'https://api.suredone.com/v1/auth', array(
				'Content-Type' => 'multipart/form-data',
			), array(
				'user' => $_REQUEST['username'],
				'pass' => $_REQUEST['password'],
			)
		);
		if( $authResponse->result == 'success' )
			echo $authResponse->token;
		else
			throw new Exception($authResponse->message);
	} catch( Exception $e ) {
		echo 'Exception: '. $e->getMessage();
	}
