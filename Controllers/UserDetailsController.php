<?php

namespace QuizService\Controllers;

use QuizService\Models\Response;
use QuizService\Models\UserDetails;


class UserDetailsController
{
    public static function getDetails($user_id)
    {
        if ( $user_id != '' )
        {
            try {
                $user_info = new UserDetails($user_id);
                $result = array(
                    'first_name' => $user_info->first_name,
                    'last_name' => $user_info->last_name,
                    'address' => $user_info->address,
                    'phone' => $user_info->phone
                );

                $response = new Response( $result );
                return $response->success();
            } catch (\Exception $e) {
                $response = new Response( array('error_code' => $e->getCode(), 'error_message' => $e->getMessage()) );
                return $response->error();
            }
        }

        $response = new Response( array('error_code' => 400, 'error_message' => 'Missing parameters') );
        return $response->error();
    }
}