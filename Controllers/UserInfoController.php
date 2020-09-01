<?php

namespace QuizService\Controllers;

use QuizService\Models\Response;
use QuizService\Models\UserInfo;


class UserInfoController
{
    public static function topUpBalance($user_id)
    {
        if ( $user_id != '' && isset($_REQUEST['amount']) && $_REQUEST['amount'] != '' )
        {
            try {
                $user_info = new UserInfo($user_id);
                $user_info->topUpBalance($_REQUEST);

                $response = new Response( array('new balance' => $user_info->balance) );
                return $response->success();
            } catch (\Exception $e) {
                $response = new Response( array('error_code' => $e->getCode(), 'error_message' => $e->getMessage()) );
                return $response->error();
            }
        }

        $response = new Response( array('error_code' => 300, 'error_message' => 'Missing parameters') );
        return $response->error();
    }

    public static function getInfo($user_id)
    {
        if ( $user_id != '' )
        {
            try {
                $user_info = new UserInfo($user_id);
                $result = array(
                    'credits' => $user_info->credits,
                    'points' => $user_info->points,
                    'balance' => $user_info->balance,
                    'plan_id' => $user_info->plan_id,
                    'subscription_end_date' => $user_info->subscription_end_date
                );

                $response = new Response( $result );
                return $response->success();
            } catch (\Exception $e) {
                $response = new Response( array('error_code' => $e->getCode(), 'error_message' => $e->getMessage()) );
                return $response->error();
            }
        }

        $response = new Response( array('error_code' => 300, 'error_message' => 'Missing parameters') );
        return $response->error();
    }
}