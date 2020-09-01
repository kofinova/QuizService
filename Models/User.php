<?php

namespace QuizService\Models;


class User extends DataBase
{
    const TABLE_NAME = 'users';
    const TABLE_KEY = 'id';

    public $id;
    public $client_id;
    public $email;
    public $password;
    public $verified;
    public $active;


    function __construct($id = '') {

        $result = self::getBy(self::TABLE_KEY, $id);
        if (count($result)) {
            foreach ($result[0] as $key => $value) {
                if (property_exists('QuizService\Models\User', $key)) {
                    $this->$key = $value;
                }
            }
        }

    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public function __set($property, $value) {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }

    public static function checkField($field)
    {
        $statement = 'SELECT id FROM ' . self::TABLE_NAME . ' WHERE ' . $field . ' = :' . $field . '  LIMIT 1';
        $parameters = array(':'.$field => $_REQUEST[$field]);
        $result = self::select($statement, $parameters);
        $field_found = count($result) > 0 ? true : false;

        $response = new Response( array('result' => $field_found) );
        return $response->success();
    }

    public static function createUser($arguments)
    {
        $statement = 'INSERT INTO ' . self::TABLE_NAME . ' (client_id, email, username, password) VALUES(:client_id, :email, :username, :password)';
        $parameters = array(
            ':client_id' => $arguments['client_id'],
            ':email' => $arguments['email'],
            ':username' => $arguments['username'],
            ':password' => hash('sha512', $arguments['password'])
        );

        self::save($statement, $parameters);
        return self::lastInsertId();
    }

    public static function verifyAndActivateUser($arguments)
    {
        $statement = 'UPDATE ' . self::TABLE_NAME . ' SET verified = 1, active = 1, updated_at = NOW() WHERE id = :user_id AND client_id = :client_id AND verified = 0 AND active = 0';
        $parameters = array(
            ':client_id' => $arguments['client_id'],
            ':user_id' => $arguments['user_id']
        );

        return self::save($statement, $parameters);
    }

    public static function login($arguments)
    {
        $statement = 'SELECT id, verified FROM ' . self::TABLE_NAME . ' WHERE email = :email AND password = :password LIMIT 1';
        $parameters = array(
            ':email' => $arguments['email'],
            ':password' => hash('sha512', $arguments['password'])
        );

        return self::select($statement, $parameters);
    }

    public static function getNonverifiedUserIds($arguments)
    {
        $statement = 'SELECT id FROM ' . self::TABLE_NAME . ' WHERE verified = 0 AND created_at < :created_at';
        $parameters = array(
            ':created_at' => $arguments['date']
        );

        return self::select_column($statement, $parameters);
    }

    public static function deleteByIds($arguments)
    {
        $statement_users = 'DELETE FROM ' . self::TABLE_NAME . ' WHERE id IN ( ' . implode(',', $arguments) . ' )';

        return self::save($statement_users);
    }

    public static function deactivateUsers($arguments)
    {
        $statement = 'UPDATE ' . self::TABLE_NAME . ' t1';
        $statement .= ' JOIN ' . UserInfo::TABLE_NAME. ' t2 ON t1.id = t2.user_id AND t2.renew_attempts = 3 AND t2.subscription_end_date LIKE :date';
        $statement .= ' SET t1.active = 0, t1.updated_at = NOW(), t2.renew_attempts = 0, t2.updated_at = NOW()';
        $parameters = array(
            ':date' => $arguments['date']
        );

        return self::save($statement, $parameters);
    }
}