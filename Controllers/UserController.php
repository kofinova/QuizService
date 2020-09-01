<?php

namespace QuizService\Controllers;

use QuizService\Models\Client;
use QuizService\Models\DataBase;
use QuizService\Models\Plan;
use QuizService\Models\Response;
use QuizService\Models\User;
use QuizService\Models\UserDetails;
use QuizService\Models\UserInfo;
use QuizService\Models\UserSubscription;

use DateTime;
use DateInterval;


class UserController
{
    public static function checkField($field) {

        if (isset($_REQUEST[$field]) && $_REQUEST[$field] != '') {
            return User::checkField($field);
        }

        $response = new Response( array('error_code' => 200, 'error_message' => 'Missing parameters') );
        return $response->error();

    }

    public static function createUser() {

        if (isset($_REQUEST['email']) && $_REQUEST['email'] != ''
            && isset($_REQUEST['username']) && $_REQUEST['username'] != ''
            && isset($_REQUEST['password']) && $_REQUEST['password'] != ''
            && isset($_REQUEST['plan_id']) && $_REQUEST['plan_id'] != ''
        ) {

            try {
                $arguments = array(
                    'client_id' => Client::getClientId(),
                    'email' => $_REQUEST['email'],
                    'username' => $_REQUEST['username'],
                    'password' => $_REQUEST['password']
                );
                $user_id = User::createUser($arguments);
                UserDetails::createUserDetails($user_id);
                UserInfo::createUserInfo($user_id);

                $response = new Response(array('user_id' => $user_id));
                return $response->success();
            } catch (\Exception $e) {
                $response = new Response( array('error_code' => $e->getCode(), 'error_message' => $e->getMessage()) );
                return $response->error();
            }
        }

        $response = new Response( array('error_code' => 200, 'error_message' => 'Missing parameters') );
        return $response->error();

    }

    public static function verifyUser()
    {
        if ( isset($_REQUEST['user_id']) && $_REQUEST['user_id'] != '' )
        {
            $pdo = DataBase::initConnection();

            try {
                $client_id = Client::getClientId();
                $user_id = $_REQUEST['user_id'];
                $trial_plan = Plan::getTrialPlan();
                $plan_id = $trial_plan[0]['id'];
                $credits = $trial_plan[0]['credits'];

                $curr_time = new DateTime();
                $start_date = $curr_time->format('Y-m-d');
                $end_date = ($curr_time->format('H') < 18) ? $start_date : $curr_time->add(new DateInterval('P1D'))->format('Y-m-d');


                $pdo->beginTransaction();

                $user_args = array(
                    'client_id' => $client_id,
                    'user_id' => $user_id
                );
                $user_rows = User::verifyAndActivateUser($user_args);


                $info_args = array(
                    'user_id' => $user_id,
                    'end_date' => $end_date,
                    'credits' => $credits
                );
                $info_rows = UserInfo::activateTrialPeriod($info_args);


                $subscription_args = array(
                    'user_id' => $user_id,
                    'plan_id' => $plan_id,
                    'start_date' => $start_date,
                    'end_date' => $end_date
                );
                $subscription_id = UserSubscription::createSubscription($subscription_args);


                if ( $user_rows && $info_rows && $subscription_id ) {
                    $pdo->commit();

                    $response = new Response();
                    return $response->success();
                }


                $pdo->rollBack();
                $response = new Response( array('error_code' => 202, 'error_message' => 'Verification and activation of user id ' . $_REQUEST['user_id'] . ' is not possible!') );
                return $response->error();

            } catch (\Exception $e) {
                $pdo->rollBack();

                $response = new Response( array('error_code' => $e->getCode(), 'error_message' => $e->getMessage()) );
                return $response->error();
            }
        }

        $response = new Response( array('error_code' => 200, 'error_message' => 'Missing parameters') );
        return $response->error();
    }

    public static function login()
    {
        if ( isset($_REQUEST['email']) && $_REQUEST['email'] != '' && isset($_REQUEST['password']) && $_REQUEST['password'] != '' )
        {
            $result = User::login($_REQUEST);

            if (count($result)) {
                if ($result[0]['verified'] == 1) {
                    $response = new Response( array('user_id' => $result[0]['id']) );
                    return $response->success();
                }

                $response = new Response( array('error_code' => 210, 'error_message' => 'User is not verified!') );
                return $response->error();
            }

            $response = new Response( array('error_code' => 201, 'error_message' => 'User not found or wrong credentials!') );
            return $response->error();
        }

        $response = new Response( array('error_code' => 200, 'error_message' => 'Missing parameters') );
        return $response->error();
    }

    public static function clearNonverified()
    {
        $curr_time = new DateTime();
        $one_day_ago = $curr_time->sub(new DateInterval('P1D'))->format('Y-m-d H:i:s');
        $arguments = array('date' => $one_day_ago);
        $non_verified_useer_ids = User::getNonverifiedUserIds($arguments);


        if (!empty($non_verified_useer_ids)) {

            DataBase::initConnection();
            DataBase::beginTransaction();

            try {
                UserDetails::deleteByIds($non_verified_useer_ids);
                UserInfo::deleteByIds($non_verified_useer_ids);
                $result = User::deleteByIds($non_verified_useer_ids);


                DataBase::commit();

                $response = new Response( array('deleted' => $result) );
                return $response->success();
            } catch (\PDOException $e) {

                DataBase::rollBack();

                $response = new Response( array('error_code' => $e->getCode(), 'error_message' => $e->getMessage()) );
                return $response->error();
            }
        }

        $response = new Response( array( 'deleted' => 0) );
        return $response->success();
    }
}