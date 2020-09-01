<?php
date_default_timezone_set('Europe/Sofia');

require 'autoload.php';

use QuizService\Controllers\ClientController;
use QuizService\Controllers\UserController;
use QuizService\Controllers\UserInfoController;
use QuizService\Controllers\UserDetailsController;
use QuizService\Controllers\UserQuestionController;
use QuizService\Controllers\UserSubscriptionController;
use QuizService\Models\Plan;
use CustomRouting\Route;


// Include router class
include 'Route.php';

define('BASEPATH','/QuizService');


// GENERAL ROUTES
Route::add('/', function() {
    echo 'Welcome';
});


Route::pathNotFound(function($path) {
    header('HTTP/1.0 404 Not Found');
    echo 'Error 404 :-(<br>';
    echo 'The requested path "'.$path.'" was not found!';
});

Route::methodNotAllowed(function($path, $method) {
    header('HTTP/1.0 405 Method Not Allowed');
    echo 'Error 405 :-(<br>';
    echo 'The requested path "'.$path.'" exists. But the request method "'.$method.'" is not allowed on this path!';
});


Route::authError(function($path) {
    echo 'In request path "'.$path.'", Wrong token or clinet id!';
});




// CLIENT ROUTES

Route::add('/token', function() {
    echo ClientController::getToken();
}, 'post', false);


// USER ROUTES

Route::add('/user/checkEmail', function() {
    echo UserController::checkField('email');
}, 'post');

Route::add('/user/checkUsername', function() {
    echo UserController::checkField('username');
}, 'post');

Route::add('/user/create', function() {
    echo UserController::createUser();
}, 'post');

Route::add('/user/verify', function() {
    echo UserController::verifyUser();
}, 'post');

Route::add('/user/login', function() {
    echo UserController::login();
}, 'post');

Route::add('/user/([0-9]+)/topUpBalance', function($user_id) {
    echo UserInfoController::topUpBalance($user_id);
}, 'post');

Route::add('/user/([0-9]+)/info', function($user_id) {
    echo UserInfoController::getInfo($user_id);
}, 'get');

Route::add('/user/([0-9]+)/details', function($user_id) {
    echo UserDetailsController::getDetails($user_id);
}, 'get');

Route::add('/user/([0-9]+)/question', function($user_id) {
    echo UserQuestionController::getQuestion($user_id);
}, 'get');

Route::add('/user/([0-9]+)/questionResponse', function($user_id) {
    echo UserQuestionController::answerQuestion($user_id);
}, 'post');


// PLAN ROUTES

Route::add('/plans', function() {
    echo Plan::getAllNonTrial();
}, 'get');


// Run Router
Route::run(BASEPATH);