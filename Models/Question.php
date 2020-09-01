<?php

namespace QuizService\Models;


class Question extends DataBase
{
    const TABLE_NAME = 'questions';
    const TABLE_KEY = 'id';

    public $id;
    public $title;


    function __construct($id = '') {

        $result = self::getBy(self::TABLE_KEY, $id);
        if (count($result)) {
            foreach ($result[0] as $key => $value) {
                if (property_exists('QuizService\Models\Question', $key)) {
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

    public function getAnswers()
    {
        $answers = Answer::getBy('question_id', $this->id);
        return array_map(function($v) {
            unset($v['question_id']);
            unset($v['correct']);
            unset($v['created_at']);
            unset($v['updated_at']);
            return $v;
        }, $answers);
    }

    public static function getUnplayedQuestionIds($played_question_ids = [])
    {
        $statement = 'SELECT id FROM ' . self::TABLE_NAME;
        $statement .= (!empty($played_question_ids)) ? ' WHERE id NOT IN ( ' . implode(',', $played_question_ids) . ' )' : '';

        return self::select_column($statement);
    }

    public static function getRandomUnplayedQuestion($played_question_ids)
    {
        $uplayed_question_ids = self::getUnplayedQuestionIds($played_question_ids);

        if (count($uplayed_question_ids)) {
            $random_key = mt_rand(0, count($uplayed_question_ids) - 1);
            $question_id = $uplayed_question_ids[$random_key];

            $question = self::getBy(self::TABLE_KEY, $question_id);


            return array('id' => $question[0][self::TABLE_KEY], 'title' => $question[0]['title']);
        }

        return false;
    }
}