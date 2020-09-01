<?php

namespace QuizService\Models;


class UserQuestion extends DataBase
{
    const TABLE_NAME = 'user_questions';
    const TABLE_KEY = 'id';

    public $id;
    public $user_id;
    public $question_id;
    public $answer_id;
    public $date;
    public $points;


    function __construct($id = '') {

        $result = self::getBy(self::TABLE_KEY, $id);
        if (count($result)) {
            foreach ($result[0] as $key => $value) {
                if (property_exists('QuizService\Models\UserQuestion', $key)) {
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

    public function answerQuestion($arguments)
    {
        $this->answer_id = $arguments['answer_id'];
        $this->update();
    }

    public static function getDailyPlayedQuestions($arguments)
    {
        $statement = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE user_id = :user_id AND date = :date';
        $parameters = array(
            ':user_id' => $arguments['user_id'],
            ':date' => $arguments['date']
        );

        return self::select($statement, $parameters);
    }

    public static function getNumberOfDailyPlayedQuestions($arguments)
    {
        return count(self::getDailyPlayedQuestions($arguments));
    }

    public static function insert($arguments)
    {
        $statement = 'INSERT INTO ' . self::TABLE_NAME . ' (user_id, question_id, date, points) VALUES(:user_id, :question_id, :date, :points)';
        $parameters = array(
            ':user_id' => $arguments['user_id'],
            ':question_id' => $arguments['question_id'],
            ':date' => $arguments['date'],
            ':points' => $arguments['points']
        );

        self::save($statement, $parameters);
        return self::lastInsertId();
    }

    public static function getUserQuestionId($arguments)
    {
        $statement = 'SELECT id FROM ' . self::TABLE_NAME . ' WHERE user_id = :user_id AND question_id = :question_id LIMIT 1';
        $parameters = array(
            ':user_id' => $arguments['user_id'],
            ':question_id' => $arguments['question_id'],
            ':date' => $arguments['date']
        );

        return self::select($statement, $parameters);
    }
}