<!DOCTYPE html>
<html>

<head>
	<title></title>
	<link rel="stylesheet" type="text/css" href="index.css" />
	<script src='http://code.jquery.com/jquery-latest.min.js'></script>
	<script src='index.js'></script>
</head>

<body>
	<?php
		include_once('php/gravatar.php');

		echo 'Dima.Rgb@gmail.com = '. getGravatarImageUrl('Dima.Rgb@gmail.com'). '<br />';
		echo 'dima.rgb@gmail.com = '. getGravatarImageUrl('dima.rgb@gmail.com'). '<br />';

		$email = 'dima.rgb@gmail.com';
		$k = 1;
		for( $size = 1; $size < 33; $size += $k )
			echo '<img src="'. getGravatarImageUrl($email, $size) .'" />';
	?>

	<form id='gravatar-test' action='php/gravatar-test.php' type='post'>
		<h1>Test Gravatar</h1>
		<div>
			Email: <input type='email' name='email' />
			<input type='submit' value='get image' />
		</div>
	</form>

	<div class="wrap">
		<label  class='switch'>
			<input type="checkbox" />
			<div>
				<div></div>
			</div>
		</label>
	</div>
</body>

</html>
