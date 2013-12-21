<?php

function getGravatarImageUrl($email, $size = 80, $default = '') {
	// $size = 1 .. 2048 (px)
	$hash = md5(strtolower(trim($email)));
	$default = urlencode($default);
	return "https://www.gravatar.com/avatar/$hash" .
		"?size=$size" .
		"&default=$default";
}
