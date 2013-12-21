<?php

include_once('gravatar.php');

$email = $_REQUEST['email'];
echo getGravatarImageUrl($email, 200, 'http://images2.wikia.nocookie.net/__cb20110807031160/tuckerverse/images/f/fd/Sasha_Gray.jpg');
