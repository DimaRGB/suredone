<?php

include_once('gravatar.php');

$email = $_REQUEST['email'];
echo getGravatarImageUrl($email, 200);
