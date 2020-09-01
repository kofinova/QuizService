<?php

namespace QuizService\Models;


class UserDetails extends DataBase
{
    const TABLE_NAME = 'user_details';
    const TABLE_KEY = 'user_id';

    public $user_id;
    public $first_name;
    public $last_name;
    public $address;
    public $phone;


    function __construct($id = '') {

        $result = self::getBy('user_id', $id);
        if (count($result)) {
            foreach ($result[0] as $key => $value) {
                if (property_exists('QuizService\Models\UserDetails', $key)) {
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

    public static function createUserDetails($user_id)
    {
        $statement = 'INSERT INTO ' . self::TABLE_NAME . ' (user_id, first_name, last_name, address, phone) VALUES(:user_id, :first_name, :last_name, :address, :phone)';
        $parameters = array(
            ':user_id' => $user_id,
            ':first_name' => isset($_REQUEST['first_name']) ? $_REQUEST['first_name'] : '',
            ':last_name' => isset($_REQUEST['last_name']) ? $_REQUEST['last_name'] : '',
            ':address' => isset($_REQUEST['address']) ? $_REQUEST['address'] : '',
            ':phone' => isset($_REQUEST['phone']) ? $_REQUEST['phone'] : ''
        );

        self::save($statement, $parameters);
        return self::lastInsertId();
    }

    public static function deleteByIds($arguments)
    {
        $statement_users = 'DELETE FROM ' . self::TABLE_NAME . ' WHERE user_id IN ( ' . implode(',', $arguments) . ' )';

        return self::save($statement_users);
    }
}