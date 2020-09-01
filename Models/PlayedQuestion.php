<?php

namespace QuizService\Models;


class PlayedQuestion extends DataBase
{
    const TABLE_NAME = 'played_questions';
    const TABLE_KEY = 'id';

    public $id;
    public $user_id;
    public $question_id;
    public $date;


    public static function getPlayedQuestionIds($arguments)
    {
        $statement = 'SELECT question_id FROM ' . self::TABLE_NAME . ' WHERE user_id = :user_id AND date = :date';
        $parameters = array(
            ':user_id' => $arguments['user_id'],
            ':date' => $arguments['date']
        );

        return self::select_column($statement, $parameters);
    }

    public static function insert($arguments)
    {
        $statement = 'INSERT INTO ' . self::TABLE_NAME . ' (user_id, question_id, date) VALUES(:user_id, :question_id, :date)';
        $parameters = array(
            ':user_id' => $arguments['user_id'],
            ':question_id' => $arguments['question_id'],
            ':date' => $arguments['date']
        );

        self::save($statement, $parameters);
        return self::lastInsertId();
    }
}