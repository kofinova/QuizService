<?php

namespace QuizService\Controllers;

use QuizService\Models\Client;
use QuizService\Models\Response;


class ClientController
{
    public static function getToken() {
        if (isset($_REQUEST['client_id']) && $_REQUEST['client_id'] != '' && isset($_REQUEST['password']) && $_REQUEST['password'] != '') {
            return Client::getToken($_REQUEST);
        }

        $response = new Response( array('error_code' => 100, 'error_message' => 'Missing parameters') );
        return $response->error();
    }

    public static function checkToken() {
        return Client::checkToken();
    }
}