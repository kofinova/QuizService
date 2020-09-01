<?php

namespace QuizService\Controllers;

use QuizService\Models\Plan;
use QuizService\Models\Response;
use QuizService\Models\User;
use QuizService\Models\UserInfo;

use DateTime;
use DateInterval;
use QuizService\Models\UserSubscription;


class UserSubscriptionController
{
    public static function automaticRenew()
    {
        $curr_date = new DateTime();
        $today = $curr_date->format('Y-m-d');
        $new_period_start = $curr_date->add(new DateInterval('P1D'))->format('Y-m-d');
        $new_period_end = $curr_date->add(new DateInterval('P6D'))->format('Y-m-d');


        $cheapest_plan_price = Plan::getCheapestPlanPrice();

        $balance_arguments = array( 'cheapest_plan_price' => $cheapest_plan_price, 'date' => $today );
        $low_balance_count = UserInfo::increaseAttemptsLowBalance($balance_arguments);


        $enough_balance_count = 0;
        $enough_balance_info = UserInfo::getUserIdsWithBalance($balance_arguments);
        if (!empty($enough_balance_info)) {
            $not_enough_balance_arr = array();

            $all_plans = Plan::getAllNonTrial();

            foreach ($enough_balance_info as $user_id => $user_info) {
                $user_plan_id = $user_info['plan_id'];
                $user_plan = $all_plans[$user_plan_id];

                if ($user_info['balance'] < $user_plan['price']) {
                    $not_enough_balance_arr[] = $user_id;
                } else {
                    $user_info = new UserInfo($user_id);
                    $user_info->credits += $user_plan['credits'];
                    $user_info->balance -= $user_plan['price'];
                    $user_info->subscription_end_date = $new_period_end;
                    $user_info->renew_attempts = 0;
                    $user_info->update();

                    $subscription_arguments = array(
                        'user_id' => $user_id,
                        'plan_id' => $user_plan_id,
                        'start_date' => $new_period_start,
                        'end_date' => $new_period_end
                    );
                    UserSubscription::createSubscription($subscription_arguments);

                    $enough_balance_count++;
                }
            }

            if (!empty($not_enough_balance_arr)) {
                $not_enough_balance_count = UserInfo::increaseAttemptsNotEnoughBalance( array( 'user_ids' => $not_enough_balance_arr) );

                $low_balance_count += $not_enough_balance_count;
            }
        }


        $deactivate_arguments = array( 'date' => $today );
        User::deactivateUsers($deactivate_arguments);

        $result = array( 'not_renewed' => $low_balance_count, 'renewed' => $enough_balance_count );
        $response = new Response( $result );
        return $response->success();
    }
}