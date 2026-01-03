<?php

namespace App\Core;

class Router {
    protected $routes = [];

    public function get($uri, $callback) {
        $this->routes['GET'][$uri] = $callback;
    }

    public function post($uri, $callback) {
        $this->routes['POST'][$uri] = $callback;
    }

    public function dispatch($uri) {
        $method = $_SERVER['REQUEST_METHOD'];

        // Strip query string
        $uri = strtok($uri, '?');

        if (isset($this->routes[$method][$uri])) {
            $callback = $this->routes[$method][$uri];
            if (is_array($callback)) {
                $controller = new $callback[0]();
                $action = $callback[1];
                return $controller->$action();
            }
            return call_user_func($callback);
        }

        // 404
        http_response_code(404);
        echo "404 Not Found";
    }
}
