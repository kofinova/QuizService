<?php

namespace QuizService\Models;


class Response
{
    private $arguments = [];

    function __construct(array $arguments = [])
    {
        $this->arguments = $arguments;
    }

    public function error()
    {
        $this->arguments['status'] = 'error';

        return json_encode($this->arguments);
    }

    public function success()
    {
        $this->arguments['status'] = 'success';

        return json_encode($this->arguments);
    }
}