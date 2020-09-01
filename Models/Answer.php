<?php

namespace QuizService\Models;


class Answer extends DataBase
{
    const TABLE_NAME = 'answers';
    const TABLE_KEY = 'id';

    public $id;
    public $question_id;
    public $title;
    public $correct;


    function __construct($id = '') {

        $result = self::getBy(self::TABLE_KEY, $id);
        if (count($result)) {
            foreach ($result[0] as $key => $value) {
                if (property_exists('QuizService\Models\Answer', $key)) {
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
}