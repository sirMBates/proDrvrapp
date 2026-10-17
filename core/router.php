<?php

namespace Core;

class Router {
    protected $routes = [];
    protected $protectedRoutes = [];

    public function add($method, $uri, $controller, $protected = false) {
        $this->routes[] = [
            'uri' => $uri,
            'controller' => $controller,
            'method' => strtoupper($method),
            'protected' => $protected
        ];

        if ($protected) {
            $this->protectedRoutes[] = $uri;
        }
    }

    public function get($uri, $controller, $protected = false){
        $this->add('GET', $uri, $controller, $protected);
    }
    public function post($uri, $controller, $protected = false){
        $this->add('POST', $uri, $controller, $protected);
    }
    public function delete($uri, $controller, $protected = false){
        $this->add('DELETE', $uri, $controller, $protected);
    }
    public function patch($uri, $controller, $protected = false){
        $this->add('PATCH', $uri, $controller, $protected);
    }
    public function put($uri, $controller, $protected = false){
        $this->add('PUT', $uri, $controller, $protected);
    }

    public function route($uri, $method) {
        foreach($this->routes as $route) {
            if($route['uri'] === $uri && $route['method'] === strtoupper($method)) {
                // Check if route is protected
                if ($route['protected'] && (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true)) {
                    /*
                    * Preserve only a Messenger page destination.
                    * Do not save POST requests or arbitrary URLs.
                    */
                    if (strtoupper($method) === 'GET' && $route['uri'] === '/message-center') {
                        $destination = '/message-center';

                        $conversationId = filter_var($_GET['conversation'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

                        $recipientId = filter_var($_GET['recipient'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

                        if ($conversationId !== false && $conversationId !== null) {
                            $destination .= '?conversation=' . (int) $conversationId;
                        } elseif ($recipientId !== false && $recipientId !== null) {
                            $destination .= '?recipient=' . (int) $recipientId;
                        }

                        $_SESSION['messenger_login_destination'] = $destination;
                    }

                    header("Location: /signin");
                    exit();
                }
                return require base_path($route['controller']);
            }
        }
        $this->abort();
    }

    protected function abort($code = 404){
        http_response_code($code);
        $_SERVER['IS_ERROR_PAGE'] = true;
        require base_path("app/views/errors/{$code}.php");
        exit();
    }
};


?>