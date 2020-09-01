<?php

namespace QuizService\Controllers;

use QuizService\Models\Answer;
use QuizService\Models\PlayedQuestion;
use QuizService\Models\Question;
use QuizService\Models\Response;
use QuizService\Models\User;
use QuizService\Models\UserInfo;

use DateTime;
use QuizService\Models\UserQuestion;


class UserQuestionController
{
    public static function getQuestion($user_id)
    {
        if ( $user_id != '' )
        {
            $user = new User($user_id);

            if ($user->id == 0 || $user->verified == 0) {
                $response = new Response( array('error_code' => 501, 'error_message' => 'User is not verified') );
                return $response->error();
            }
            if ($user->active == 0) {
                $response = new Response( array('error_code' => 502, 'error_message' => 'User is not active') );
                return $response->error();
            }


            $user_info = new UserInfo($user_id);

            if ($user_info->credits == 0) {
                $response = new Response( array('error_code' => 503, 'error_message' => 'Not enought credits') );
                return $response->error();
            }


            $curr_time = new DateTime();
            $curr_date = $curr_time->format('Y-m-d');

            $arguments = array(
                'user_id' => $user_id,
                'date' => $curr_date
            );
            $daily_played_questions_num = UserQuestion::getNumberOfDailyPlayedQuestions($arguments);

            if ($daily_played_questions_num >= 10) {
                $response = new Response( array('error_code' => 504, 'error_message' => 'Daily limit of questions reached') );
                return $response->error();
            }


            $played_question_ids = PlayedQuestion::getPlayedQuestionIds($arguments);
            $random_question = Question::getRandomUnplayedQuestion($played_question_ids);
            if (!$random_question) {
                $response = new Response( array('error_code' => 505, 'error_message' => 'No questions found') );
                return $response->error();
            }


            $question = new Question($random_question['id']);
            $answers = $question->getAnswers();
            if (empty($answers)) {
                $response = new Response( array('error_code' => 506, 'error_message' => 'No answers found') );
                return $response->error();
            }


            $user_info->playQuestion();
            $points = $user_info->getQuestionPoints();
            $random_question['points'] = $points;

            $played_question_arguments = $arguments;
            $played_question_arguments['question_id'] = $random_question['id'];
            PlayedQuestion::insert($played_question_arguments);

            $user_question_arguments = $played_question_arguments;
            $user_question_arguments['points'] = $points;
            $user_question_id = UserQuestion::insert($user_question_arguments);


            $result = array(
                'question' => $random_question,
                'answers' => $answers,
                'user_question_id' => $user_question_id
            );

            $response = new Response( $result );
            return $response->success();

        }

        $response = new Response( array('error_code' => 500, 'error_message' => 'Missing parameters') );
        return $response->error();
    }

    public static function answerQuestion($user_id)
    {
        if ( $user_id != ''
            && isset($_REQUEST['question_id']) && $_REQUEST['question_id'] != ''
            && isset($_REQUEST['answer_id']) && $_REQUEST['answer_id'] != ''
            && isset($_REQUEST['user_question_id']) && $_REQUEST['user_question_id'] != ''
        ) {

            $user_question = new UserQuestion($_REQUEST['user_question_id']);
            $answer = new Answer($_REQUEST['answer_id']);

            if ( $user_question->question_id != $_REQUEST['question_id'] || $answer->question_id != $_REQUEST['question_id'] ) {
                $response = new Response( array('error_code' => 507, 'error_message' => 'Answer does not correspond to question') );
                return $response->error();
            }

            if ( $user_question->answer_id != '' ) {
                $response = new Response( array('error_code' => 508, 'error_message' => 'Question already answered') );
                return $response->error();
            }


            $user_question_arguments = array( 'answer_id' => $_REQUEST['answer_id'] );
            $user_question->answerQuestion($user_question_arguments);


            $result = array();
            $user_info = new UserInfo($user_id);
            if ($answer->correct) {
                $info_arguments = array( 'question_points' => $user_question->points );
                $user_info->increasePoints($info_arguments);

                $result['correct_answer'] = true;
            } else {
                $result['correct_answer'] = false;
            }
            $result['points'] = $user_info->points;


            $response = new Response( $result );
            return $response->success();
        }

        $response = new Response( array('error_code' => 500, 'error_message' => 'Missing parameters') );
        return $response->error();
    }
}