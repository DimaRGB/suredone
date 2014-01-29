<?php

try {

	// init cycle
	// Call 1: GetModule (action=getmodule) 
	// Call 2: GetStore

	// download order cycle
	// Call 1: GetModule
	// Call 2: GetCount
	// Call 3...N: GetOrders - виполняеться бесконечно пока не прийдет пустой ответ

	define('DEV_MODE', false);

	function echoObj($obj) {
		echo '<hr><xmp>';
		print_r($obj);
		echo '</xmp>';
	}

	// class for generate xml response for shipworks
	class ShipWorksXML extends DOMDocument {

		private $moduleVersion = '3.0.2';
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
			$onlineCustomerID->setAttribute('supported', 'false');
			$onlineStatus = $this->createElement('OnlineStatus');
			$onlineStatus->setAttribute('supported', 'false');
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

		public function appendStore($store) {
			$storeE = $this->createElement('Store');
			foreach( $store as $name => $value ) {
				$Name = ucfirst($name);
				$storeE->appendChild($this->createElement($Name, $value));
			}
			$this->root->appendChild($storeE);
		}

		public function appendOrderCount($count) {
			$this->root->appendChild($this->createElement('OrderCount', $count));
		}

		public function appendOrders($orders) {
			$ordersE = $this->createElement('Orders');
			foreach( $orders as $order )
				$ordersE->appendChild($this->createOrderE($order));
			$this->root->appendChild($ordersE);
		}

		public function createOrderE($order) {
			$orderE = $this->createCollectionE('Order', $order, array(
				'OrderNumber' => 'order',
				'OrderDate' => 'date',
				'LastModified' => 'dateupdated',
				'ShippingMethod' => 'payment',
			));
			$orderE->appendChild($this->createAddressE('Shipping', $order, array(
				'sfirstname', 'slastname', 'sbusiness', 'saddress1', 'saddress2', 'saddress3', 'scity', 'sstate', 'szip', 'scountry', 'sphone',
			)));
			$orderE->appendChild($this->createAddressE('Billing', $order, array(
				'bfirstname', 'blastname', 'bbusiness', 'baddress1', 'baddress2', 'baddress3', 'bcity', 'bstate', 'bzip', 'bcountry', 'bphone',
			)));
			$itemsE = $this->createElement('Items');
			$itemsE->appendChild($this->createCollectionE('Item', $order, array(
				'Code' => 'code',
				'SKU' => 'items',
				'Name' => 'titles',
				'Quantity' => 'qtys',
				'UnitPrice' => 'prices',
				'Image' => 'media',
				'Weight' => 'weights',
			)));
			$orderE->appendChild($itemsE);
			$orderE->appendChild($this->createTotalsE($order, array(
				'item' => 'itemtotal',
				'shipping' => 'shippingtotal',
				'handling' => 'handlingtotal',
				'tax' => 'taxtotal',
				'discount' => 'discount',
			)));
			return $orderE;
		}

		public function createAddressE($name, $data, $fields) {
			return $this->createCollectionE($name .'Address', $data, array(
				'FirstName', 'LastName', 'Company', 'Street1', 'Street2', 'Street3', 'City', 'State', 'PostalCode', 'Country', 'Phone',
			), $fields);
		}

		public function createTotalsE($data, $hash) {
			$totalsE = $this->createElement('Totals');
			foreach ($hash as $name => $field) {
				$totalE = $this->createElement('Total', $data[$field]);
				$totalE->setAttribute('name', $name);
				$totalsE->appendChild($totalE);
			}
			return $totalsE;
		}

		public function createCollectionE($name, $data, $hash, $fields = null) {
			$collectionE = $this->createElement($name);
			$this->appendChilds($collectionE, $data, $hash, $fields);
			return $collectionE;
		}

		public function appendChilds($element, $data, $hash, $fields = null) {
			foreach( $hash as $key => $value )
				if( $fields )
					$element->appendChild($this->createElement($value, $data[$fields[$key]]));
				else
					$element->appendChild($this->createElement($key, $data[$value]));
		}

		public function appendUpdateSuccess() {
			$this->root->appendChild($this->createElement('UpdateSuccess'));
		}

		public function appendError($code = '500', $description = 'Internal Error.') {
			$errorE = $this->createElement('Error');
			$errorE->appendChild($this->createElement('Code', $code));
			$errorE->appendChild($this->createElement('Description', $description));
			$this->root->appendChild($errorE);
		}

		public function echoXML() {
			if( DEV_MODE )
				echo '<hr><xmp>' . $this->saveXML() . '</xmp>';
			else {
				// HTTP/1.1
				header('Content-Type: text/xml; charset=utf-8');
				header('Cache-Control: no-store, no-cache, must-revalidate');
				header('Cache-Control: post-check=0, pre-check=0', false);
				// HTTP/1.0
				header('Pragma: no-cache');
				echo $this->saveXML();
				$this->writeToLogFile();
			}
		}

		private function writeToLogFile() {
			$fileName = 'requests.log';
			if( file_exists($fileName) ) {
				$file = fopen($fileName, 'r') or die;
				$fileContents = fread($file, filesize($fileName));
				fclose($file);
			} else
				$fileContents = '';
			$file = fopen($fileName, 'w+') or die;
			$fileContents .= date('Y-m-d H:i:s').
				'.'.
				(round(microtime(true) * 1000) % 1000).
				' -> '.
				urldecode(http_build_query($_REQUEST)).
				"\n";
			fwrite($file, $fileContents);
			fclose($file);
		}

	}


	// class for fetching data in suredone api
	class SuredoneApi {

		const PATH = 'https://api.suredone.com/v1/';

		public function __construct($username, $password) {
			$this->username = $username;
			$this->password = $password;
			$this->auth();
		}

		private function sendHttpRequest($method, $url, $data = null) {
			$http = array(
				'method' => $method,
				'ignore_errors' => true,
			);
			$url = self::PATH . $url;
			if( isset($this->token) )
				$http['header'] = array(
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
				throw new Exception("Failed $method $url: $php_errormsg", 502);
			$response = json_decode($response, true);
			if( $response === null )
				throw new Exception("Failed to decode $response as json", 503);
			if( $response['result'] == 'error' || $response['result'] == 'failure' ) {
				throw new Exception($response['message'], 504);
			}
			return $response;
		}

		private function auth() {
			$auth = $this->sendHttpRequest('POST', 'auth', array(
				'user' => $this->username,
				'pass' => $this->password,
			));
			$this->token = $auth['token'];
		}

		public function getStore() {
			$options = $this->sendHttpRequest('GET', 'options/all');
			return array(
				'name' => $options['site_user'],
				'companyOrOwner' => $options['business_name'],
				'email' => $options['business_email'],
				'street1' => $options['business_street'],
				'street2' => $options['business_street_2'],
				'city' => $options['business_city'],
				'state' => $options['business_state'],
				'postalCode' => $options['business_zip'],
				'country' => $options['business_country'],
				'phone' => $options['business_phone'],
				'website' => $options['site_domain'],
			);
		}

		public function getCount($start) {
			if( !$start )
				$start = '0000-00-00T00:00:00';
			$orders = $this->sendHttpRequest('GET', 'orders/all?sort=dateupdated');
			$count = $orders['all'];
			for( $i = $count; $i >= 1 ; $i-- )
				if( $start >= $this->utc($orders[$i]['dateupdated']) )
					break;
			return $count - $i;
		}

		public function getOrders($start, $maxcount) {
			if( !$start )
				$start = '0000-00-00T00:00:00';
			if( $maxcount <= 0 )
				$maxcount = 50;
			$orders = $this->sendHttpRequest('GET', 'orders/all?sort=dateupdated');
			$count = $orders['all'];
			for( $i = $count; $i > 0 ; $i-- ) {
				$orders[$i]['order'] = $this->number($orders[$i]['order']);
				$orders[$i]['date'] = $this->utc($orders[$i]['date']);
				$orders[$i]['dateupdated'] = $this->utc($orders[$i]['dateupdated']);
				if( $start >= $orders[$i]['dateupdated'] )
					break;
			}
			return array_slice($orders, $i, $count - $i > $maxcount ? $maxcount : $count - $i);
		}

		public function updateShipment($order, $tracking) {
			$this->sendHttpRequest('POST', 'orders/edit', array(
				'order' => $order,
				'shiptracking' => $tracking,
			));
		}

		public function number($value) {
			return substr(preg_replace('/(\D+)/i', '', $value), -10);
		}

		public function utc($dateTime) {
			return str_replace(' ', 'T', $dateTime);
		}

	}


	// main
	$shipWorksXML = new ShipWorksXML();
	$suredoneApi = new SuredoneApi($_REQUEST['username'], $_REQUEST['password']);

	switch( $_REQUEST['action'] ) {
		case 'getmodule':
			$shipWorksXML->appendModule();
			break;
		case 'getstore':
			$shipWorksXML->appendStore($suredoneApi->getStore());
			break;
		case 'getcount':
			$shipWorksXML->appendOrderCount($suredoneApi->getCount($_REQUEST['start']));
			break;
		case 'getorders':
			$shipWorksXML->appendOrders($suredoneApi->getOrders($_REQUEST['start'], $_REQUEST['maxcount']));
			break;
		case 'updateshipment':
			$suredoneApi->updateShipment($_REQUEST['order'], $_REQUEST['tracking']);
			$shipWorksXML->appendUpdateSuccess();
			break;
		default:
			throw new Exception('Invalid action', 501);
	}

	$shipWorksXML->echoXML();

} catch( Exception $e ) {
	$shipWorksXML->appendError($e->getCode(), $e->getMessage());
	$shipWorksXML->echoXML();
}
