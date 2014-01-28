<?php

try {

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

	// class for generate xml response for shipworks
	class ShipWorksXML extends DOMDocument {

		private $moduleVersion = '3.0.1';
		private $schemaVersion = '1.0.0';

		public function __construct() {
			parent::__construct('1.0', 'utf-8');
			$this->xmlStandalone = true;
			$this->formatOutput = true;
			$this->preserveWhiteSpace = true;
			$this->appendRoot();
		}

		private function appendRoot() {
			$this->root = $this->createElement('ShipWorks');
			$this->root->setAttribute('moduleVersion', $this->moduleVersion);
			$this->root->setAttribute('schemaVersion', $this->schemaVersion);
			$this->appendChild($this->root);
		}

		public function appendModule() {
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
			$this->root->appendChild($module);
		}

		public function appendStore($storeData) {
			$store = $this->createElement('Store');
			foreach( $storeData as $name => $value ) {
				$Name = ucfirst($name);
				$store->appendChild($this->createElement($Name, $value));
			}
			$this->root->appendChild($store);
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


	// class for fetching data in suredone api
	class SuredoneApi {

		public function __construct($username, $password) {
			$this->username = $username;
			$this->password = $password;
			$this->auth();
		}

		private function httpRequestSend($method, $url, $data = null) {
			$http = array(
				'method' => $method,
				'ignore_errors' => true,
			);
			if( isset($this->token) )
				$http['header'] = array(
					'Content-Type: multipart/form-data',
					'X-Auth-User: '. $this->username,
					'X-Auth-Token: '. $this->token,
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
			if( $response->result == 'error' )
				throw new Exception($response->message);
			return $response;
		}

		private function auth() {
			$this->token = $this->httpRequestSend('POST', 'https://api.suredone.com/v1/auth', array(
				'user' => $this->username,
				'pass' => $this->password,
			))->token;
		}

		public function getStoreData() {
			$options = $this->httpRequestSend('GET', 'https://api.suredone.com/v1/options/all');
			return array(
				'name' => $options->site_user,
				'companyOrOwner' => $options->business_name,
				'email' => $options->business_email,
				'street1' => $options->business_street,
				'street2' => $options->business_street_2,
				'city' => $options->business_city,
				'state' => $options->business_state,
				'postalCode' => $options->business_zip,
				'country' => $options->business_country,
				'phone' => $options->business_phone,
				'website' => $options->site_domain,
			);
		}

	}


	// main
	$suredoneApi = new SuredoneApi($_REQUEST['username'], $_REQUEST['password']);
	$shipWorksXML = new ShipWorksXML();

	switch( $_REQUEST['action'] ) {
		case 'getmodule':
			$shipWorksXML->appendModule();
			break;
		case 'getstore':
			$shipWorksXML->appendStore($suredoneApi->getStoreData());
			break;
		case 'getstatuscodes':
			// $body = $this->getStatusCodes();
			break;
		case 'getcount':
			// $body = $this->getCount();
			break;
		case 'getorders':
			// $body = $this->getOrders();
			break;
		case 'updatestatus':
		case 'updateshipment':
			// $body = $this->getUpdate();
			break;
	}

	$shipWorksXML->echoXML(true);
	
} catch( Exception $e ) {
	echo 'Exception: '. $e->getMessage();
}
