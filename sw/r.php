<style>
	body {
		font: 14px/22px monospace;
	}
</style>

<?php

try {
	echo '<xmp>'. file_get_contents('requests.log') .'</xmp>';
} catch( Exception $e ) {
	echo $e->getCode(). ': '. $e->getMessage();
}
