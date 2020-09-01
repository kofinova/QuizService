<?php

namespace QuizService\Controllers;

use DateTime;
use DateInterval;
use QuizService\Models\QuizChart;
use QuizService\Models\Response;
use QuizService\Models\UserInfo;


class QuizChartController
{

    public static function chooseWinners()
    {
        $yesterday = new DateTime('yesterday');
        $week_end = $yesterday->format('Y-m-d');
        $week_start = $yesterday->sub(new DateInterval('P6D'))->format('Y-m-d');


        $quiz_chart_arguments = array( 'start_date' => $week_start, 'end_date' => $week_end);
        QuizChart::copyPeriodPlayers($quiz_chart_arguments);


        $top_players = UserInfo::getTopUserIds();
        if (!empty($top_players)) {
            $bonus_points_arguments = array( 'bonus_points' => QuizChart::BONUS, 'user_ids' => $top_players);
            UserInfo::distributeBonusPoints($bonus_points_arguments);

            $winners_arguments = array_merge($quiz_chart_arguments, $bonus_points_arguments);
            QuizChart::updateWinners($winners_arguments);

            UserInfo::nullPeriodPoints();


            $response = new Response( array( 'winners' => count($top_players)) );
            return $response->success();
        }

        $response = new Response( array( 'winners' => 0) );
        return $response->success();
    }
}