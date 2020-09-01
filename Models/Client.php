<?php

namespace QuizService\Models;

use DateTime;


class Client extends DataBase
{
    const TABLE_NAME = 'clients';
    const TABLE_KEY = 'id';

    public $id;
    public $name;
    public $password;


    function __construct(array $arguments = []) {

        foreach ($arguments as $key => $value) {
            if (property_exists('QuizService\Models\Client', $key)) {
                $this->$key = $value;
            }
        }

    }

    public function createToken()
    {
        return self::generateToken($this->id);
    }

    public static function generateToken($client_id)
    {
        $curr_date = new DateTime();
        return md5($client_id . '_' . $curr_date->format('Y-m-d'));
    }

    public static function getClient($request)
    {
        $statement = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE id = :client_id AND password = :password LIMIT 1';
        $parameters = array(':client_id' => $request['client_id'], ':password' => hash('sha512', $request['password']));

        return self::select($statement, $parameters);
    }

    public static function getToken($request)
    {
        $client_select = self::getClient($request);

        if (empty($client_select)) {
            $response = new Response( array('error_code' => 101, 'error_message' => 'Client does not exist or wrong credentials') );
            return $response->error();
        }

        $client = new Client($client_select[0]);
        $response = new Response( array('token' => $client->createToken()) );
        return $response->success();
    }

    public static function checkToken()
    {
        $headers = getallheaders();
        $client_id = isset($headers['X-Auth-Client']) ? $headers['X-Auth-Client'] : '';
        $token = isset($headers['X-Auth-Token']) ? $headers['X-Auth-Token'] : '';

        $check_token = self::generateToken($client_id);
        
        if ($token === $check_token) {
            return true;
        }

        return false;
    }

    public static function getClientId()
    {
        $headers = getallheaders();
        return isset($headers['X-Auth-Client']) ? $headers['X-Auth-Client'] : '';
    }
}