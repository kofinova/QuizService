<?php

namespace QuizService\Models;

use PDO;
use PDOException;


class DataBase
{
    private static $server = 'localhost';
    private static $username = 'root';
    private static $password = '';
    private static $dbname = 'quiz_service';
    private static $pdo;


    public function update()
    {
        $parameters = array(':pk' => $this->{static::TABLE_KEY});
        $statement = 'UPDATE ' . static::TABLE_NAME . ' SET updated_at = NOW() ';
        foreach ($this as $key => $value) {
            $statement .= ', ' . $key . ' = :' . $key;

            $parameters[':'.$key] = $value;
        }
        $statement .= ' WHERE ' . static::TABLE_KEY . ' = :pk';


        return self::save($statement, $parameters);
    }

    public static function getBy($field, $value)
    {
        $statement = 'SELECT * FROM ' . static::TABLE_NAME . ' WHERE {$field} = :{$field}';
        $parameters = array(':' . $field => $value);

        return self::select($statement, $parameters);
    }

    public static function initConnection()
    {
        if (!isset(self::$pdo)) {
            try {
                self::$pdo = new PDO('mysql:host=' . self::$server . ';dbname=' . self::$dbname, self::$username, self::$password);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            } catch (PDOException $e) {
                die('Unable to connect with the database');
            }
        }


        return self::$pdo;
    }

    public static function closeConnection()
    {
        self::$pdo = null;
    }

    public static function select($statement, $parameters=null, $fetch_style=PDO::FETCH_ASSOC)
    {
        $stmt = self::query($statement);
        $stmt->execute($parameters);

        return $stmt->fetchAll($fetch_style);
    }

    public static function select_all($statement, $parameters=null)
    {
        $stmt = self::query($statement);
        $stmt->execute($parameters);

        return array_map('reset',  $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC));
    }

    public static function select_column($statement, $parameters=null)
    {
        $stmt = self::query($statement);
        $stmt->execute($parameters);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function save($statement, $parameters = null)
    {
        $stmt = self::query($statement);
        $stmt->execute($parameters);

        return $stmt->rowCount();
    }

    public static function query($statement)
    {
        $pdo = self::initConnection();

        return $pdo->prepare($statement);
    }

    public static function lastInsertId() {
        return self::$pdo->lastInsertId();
    }

    public static function beginTransaction() {
        return self::$pdo->beginTransaction();
    }

    public static function commit() {
        return self::$pdo->commit();
    }

    public static function rollBack() {
        return self::$pdo->rollBack();
    }




}