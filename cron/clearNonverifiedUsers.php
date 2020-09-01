<?php
date_default_timezone_set('Europe/Sofia');

define('ROOT', '');
require ROOT . '/QuizService/autoload.php';

use QuizService\Controllers\UserController;


echo '[' . date('Y-m-d H:i:s') . ']';
echo UserController::clearNonverified();
echo '\n';