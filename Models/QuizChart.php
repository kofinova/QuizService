<?php

namespace QuizService\Models;


class QuizChart extends DataBase
{
    const TABLE_NAME = 'quiz_chart';
    const TABLE_KEY = 'id';
    const BONUS = 20;

    public $id;
    public $start_date;
    public $end_date;
    public $user_id;
    public $period_points;
    public $bonus_points;
    public $winner;


    public static function copyPeriodPlayers($arguments)
    {
        $statement = 'INSERT INTO ' . self::TABLE_NAME . ' (start_date, end_date, user_id, period_points)';
        $statement .= ' SELECT "' . $arguments['start_date'] . '", "' . $arguments['end_date'] . '", user_id, period_points';
        $statement .= ' FROM ' . UserInfo::TABLE_NAME . '';
        $statement .= ' WHERE period_points > 0';

        self::save($statement);
    }


    public static function updateWinners($arguments)
    {
        $statement = 'UPDATE ' . self::TABLE_NAME . ' SET bonus_points = ' . $arguments['bonus_points'] . ', winner = 1, updated_at = NOW()';
        $statement .= ' WHERE user_id IN (' . implode(',', $arguments['user_ids'] ) . ')';
        $statement .= ' AND start_date LIKE "' . $arguments['start_date'] . '"';
        $statement .= ' AND end_date LIKE "' . $arguments['end_date'] . '"';

        return self::save($statement);
    }
}