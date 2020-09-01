<?php

namespace QuizService\Models;


class UserInfo extends DataBase
{
    const TABLE_NAME = 'user_info';
    const TABLE_KEY = 'user_id';

    public $user_id;
    public $credits;
    public $points;
    public $period_points;
    public $balance;
    public $plan_id;
    public $subscription_end_date;
    public $renew_attempts;
    public $played_questions;


    function __construct($id = '') {

        $result = self::getBy(self::TABLE_KEY, $id);
        if (count($result)) {
            foreach ($result[0] as $key => $value) {
                if (property_exists('QuizService\Models\UserInfo', $key)) {
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

    public static function createUserInfo($user_id)
    {
        $statement = 'INSERT INTO ' . self::TABLE_NAME . ' (user_id, plan_id) VALUES(:user_id, :plan_id)';
        $parameters = array(
            ':user_id' => $user_id,
            ':plan_id' => $_REQUEST['plan_id']
        );

        self::save($statement, $parameters);
        return self::lastInsertId();
    }

    public static function activateTrialPeriod($arguments)
    {
        $statement = 'UPDATE ' . self::TABLE_NAME . ' SET credits = credits + :credits, subscription_end_date = :subscription_end_date, updated_at = NOW() WHERE user_id = :user_id';
        $parameters = array(
            ':subscription_end_date' => $arguments['end_date'],
            ':credits' => $arguments['credits'],
            ':user_id' => $arguments['user_id']
        );

        return self::save($statement, $parameters);
    }

    public function topUpBalance($arguments)
    {
        $this->balance += $arguments['amount'];
        $this->update();
    }

    public function playQuestion()
    {
        $this->credits -= 1;
        $this->played_questions += 1;
        $this->update();
    }

    public function increasePoints($arguments)
    {
        $this->points += $arguments['question_points'];
        $this->period_points += $arguments['question_points'];
        $this->update();
    }

    public function getQuestionPoints()
    {
        return (($this->played_questions % 3) == 0) ? 20 : 10;
    }

    public static function deleteByIds($arguments)
    {
        $statement_users = 'DELETE FROM ' . self::TABLE_NAME . ' WHERE user_id IN ( ' . implode(',', $arguments) . ' )';

        return self::save($statement_users);
    }

    public static function nullPeriodPoints()
    {
        $statement = 'UPDATE ' . self::TABLE_NAME . ' SET period_points = 0, updated_at = NOW() WHERE 1';

        return self::save($statement);
    }

    public static function getTopUserIds()
    {
        $statement = 'SELECT * FROM ' . self::TABLE_NAME . ' t1 INNER JOIN';
        $statement .= '( SELECT DISTINCT period_points FROM ' . self::TABLE_NAME . ' WHERE period_points != 0 ORDER BY period_points DESC LIMIT 3 ) t2';
        $statement .= ' ON t1.period_points = t2.period_points';

        return self::select_column($statement);
    }

    public static function distributeBonusPoints($arguments)
    {
        $statement = 'UPDATE ' . self::TABLE_NAME . ' SET points = points + ' . $arguments['bonus_points'] . ', updated_at = NOW()';
        $statement .= ' WHERE user_id IN (' . implode(',', $arguments['user_ids'] ) . ')';

        return self::save($statement);
    }

    public static function increaseAttemptsLowBalance($arguments)
    {
        $cheapest_plan_price = (!empty($arguments['cheapest_plan_price'])) ? $arguments['cheapest_plan_price'][0] : 0;
        $statement = 'UPDATE ' . self::TABLE_NAME . ' SET renew_attempts = renew_attempts + 1, updated_at = NOW()';
        $statement .= ' WHERE renew_attempts < 3 && balance < :balance && subscription_end_date like :date';
        $parameters = array(
            ':balance' => $cheapest_plan_price,
            ':date' => $arguments['date']
        );

        return self::save($statement, $parameters);
    }

    public static function increaseAttemptsNotEnoughBalance($arguments)
    {
        $statement = 'UPDATE ' . self::TABLE_NAME . ' SET renew_attempts = renew_attempts + 1, updated_at = NOW()';
        $statement .= ' WHERE user_id IN (' . implode(',', $arguments['user_ids']) . ')';

        return self::save($statement);
    }

    public static function getUserIdsWithBalance($arguments)
    {
        $cheapest_plan_price = (!empty($arguments['cheapest_plan_price'])) ? $arguments['cheapest_plan_price'][0] : 0;
        $statement = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE renew_attempts < 3 && balance >= :balance && subscription_end_date like :date';
        $parameters = array(
            ':balance' => $cheapest_plan_price,
            ':date' => $arguments['date']
        );

        return self::select_all($statement, $parameters);
    }
    
}