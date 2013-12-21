<?php

function getGravatarImageUrl($email, $size = 80) {
	// $size = 1 .. 2048 (px)
	$hash = md5(strtolower(trim($email)));
	return "http://www.gravatar.com/avatar/$hash" .
		"?size=$size";
}
