<?php
namespace CustomRouting;

use QuizService\Models\Client;

class Route {

    private static $routes = Array();
    private static $pathNotFound = null;
    private static $methodNotAllowed = null;
    private static $authError = null;

    /**
    * Function used to add a new route
    * @param string $expression    Route string or expression
    * @param callable $function    Function to call if route with allowed method is found
    * @param string $method    String of allowed method
    *
    */
    public static function add($expression, $function, $method = 'get', $auth = true){
        array_push(self::$routes, Array(
            'expression' => $expression,
            'function' => $function,
            'method' => $method,
            'auth'=> $auth
        ));
    }

    public static function pathNotFound($function) {
        self::$pathNotFound = $function;
    }

    public static function methodNotAllowed($function) {
        self::$methodNotAllowed = $function;
    }

    public static function authError($function) {
        self::$authError = $function;
    }

    public static function run($basepath = '') {
        $basepath = rtrim($basepath, '/');

        // Parse current URL
        $parsed_url = parse_url($_SERVER['REQUEST_URI']);

        $path = '/';

        // If there is a path available
        if (isset($parsed_url['path'])) {
            $path = $parsed_url['path'];
        }

        // Get current request method
        $method = $_SERVER['REQUEST_METHOD'];

        $path_match_found = false;

        $route_match_found = false;

        $auth_match = false;

        foreach (self::$routes as $route) {
            if ($basepath != '') {
                $route['expression'] = '('.$basepath.')'.$route['expression'];
            }
            $route['expression'] = '^'.$route['expression'].'$';

            // Check path match
            if (preg_match('#'.$route['expression'].'#i', $path, $matches)) {
                $path_match_found = true;
                if (strtolower($method) === strtolower($route['method'])) {
                    array_shift($matches);

                    if ($basepath != '') {
                        array_shift($matches);
                    }

                    if (($route['auth'] && Client::checkToken()) || !$route['auth']) {
                        $auth_match = true;
                        call_user_func_array($route['function'], $matches);
                    }

                    $route_match_found = true;
                }
            }

            if ($route_match_found){
                break;
            }
        }

        // No matching route was found
        if (!$route_match_found) {
            // But a matching path exists
            if ($path_match_found) {
                if (self::$methodNotAllowed) {
                    call_user_func_array(self::$methodNotAllowed, Array($path,$method));
                }
            } else {
                if (self::$pathNotFound) {
                    call_user_func_array(self::$pathNotFound, Array($path));
                }
            }
        }

        if (!$auth_match) {
            call_user_func_array(self::$authError, Array($path));
        }
    }
}