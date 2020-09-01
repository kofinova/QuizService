<?php

namespace QuizService\Models;


class Plan extends DataBase
{
    const TABLE_NAME = 'plans';
    const TABLE_KEY = 'id';

    public $id;
    public $name;
    public $credits;
    public $price;
    public $trial;


    public static function getTrialPlan()
    {
        $statement = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE trial = 1 LIMIT 1';

        return self::select($statement);
    }

    public static function getAllNonTrial()
    {
        $statement = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE trial = 0';

        return self::select_all($statement);
    }

    public static function getCheapestPlanPrice()
    {
        $statement = 'SELECT price FROM ' . self::TABLE_NAME . ' WHERE trial = 0 ORDER BY price ASC LIMIT 1';

        return self::select_column($statement);
    }
}