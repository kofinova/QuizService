<?php

namespace QuizService\Models;


class UserSubscription extends DataBase
{
    const TABLE_NAME = 'user_subscriptions';
    const TABLE_KEY = 'id';

    public $id;
    public $user_id;
    public $plan_id;
    public $start_date;
    public $end_date;


    public static function createSubscription($arguments)
    {
        $statement = 'INSERT INTO ' . self::TABLE_NAME . ' (user_id, plan_id, start_date, end_date) VALUES(:user_id, :plan_id, :start_date, :end_date)';
        $parameters = array(
            ':user_id' => $arguments['user_id'],
            ':plan_id' => $arguments['plan_id'],
            ':start_date' => $arguments['start_date'],
            ':end_date' => $arguments['end_date']
        );
        self::save($statement, $parameters);


        return self::lastInsertId();
    }
}