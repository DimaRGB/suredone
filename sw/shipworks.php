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

		private $moduleVersion = '3.0.0';
		private $schemaVersion = '1.0.0';

		public function __construct() {
			parent::__construct('1.0', 'utf-8');
			$this->xmlStandalone = true;
			$this->formatOutput = true;
			$this->preserveWhiteSpace = true;
			$this->initRoot();
		}

		private function initRoot() {
			$root = $this->createElement('ShipWorks');
			$root->setAttribute('moduleVersion', $this->moduleVersion);
			$root->setAttribute('schemaVersion', $this->schemaVersion);
			$this->appendChild($root);
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

	$xml = new ShipWorksXML;
	$xml->echoXML(true);
