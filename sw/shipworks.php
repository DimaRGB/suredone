<?php

	function echoObj($obj) {
		echo '<hr><xmp>';
		print_r($obj);
		echo '</xmp>';
	}

	class ShipWorksXML extends DOMDocument {

		function __construct() {
			$this->formatOutput = true;
			$this->preserveWhiteSpace = true;
		}

		function load($filename, $options = 0) {
			parent::load($filename, $options);
		}

		// function addBook($title, $author) {
		// 	$id = $this->books->childNodes->length + 1;
		// 	$book = $this->createElement('book');
		// 	$book->appendChild($this->createElement('id', $id));
		// 	$book->appendChild($this->createElement('title', $title));
		// 	$book->appendChild($this->createElement('author', $author));
		// 	$this->books->appendChild($book);
		// }

		// function removeBook() {
		// 	if( $this->books->childNodes->length )
		// 		$this->books->removeChild($this->books->firstChild);
		// }

		// function replaceBook($i1, $i2) {
		// 	$book1 = $this->books->childNodes->item($i1);
		// 	$book2 = $this->books->childNodes->item($i2);
		// 	$this->books->replaceChild($book1, $book2);
		// }

		function saveAndEcho($isXML, $node = NULL) {
			if( $isXML ) {
				// HTTP/1.1
				header('Content-Type: text/xml; charset=utf-8');
				header('Cache-Control: no-store, no-cache, must-revalidate');
				header('Cache-Control: post-check=0, pre-check=0', false);
				// HTTP/1.0
				header('Pragma: no-cache');
				echo $this->saveXML($node);
			} else
				echo '<hr><xmp>' . $this->saveXML($node) . '</xmp>';
		}

	}

	$xml = new ShipWorksXML;
	$xml->load('shipworks.xml');

	// $action = $_REQUEST['action'];
	// if( $action == 'add' )
	// 	$xml->addBook('PHP Framework', 'Reza Christian');
	// elseif( $action == 'remove' )
	// 	$xml->removeBook();
	// elseif( $action == 'replace' )
	// 	$xml->replaceBook($_REQUEST['i1'], $_REQUEST['i2']);

	$xml->saveAndEcho(true);
