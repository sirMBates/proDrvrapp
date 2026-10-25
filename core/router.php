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
        $errors = [
            401 => [
                'title' => 'Unauthorized',
                'message' => 'You must sign in before accessing this route.'
            ],

            403 => [
                'title' => 'Route Restricted',
                'message' => 'You do not have permission to access this resource.'
            ],

            404 => [
                'title' => 'Route Not Found',
                'message' => 'The destination you are looking for does not exist.'
            ],

            419 => [
                'title' => 'Session Expired',
                'message' => 'Your session has expired. Please sign in again.'
            ],

            429 => [
                'title' => 'Traffic Congestion',
                'message' => 'Too many requests have been sent. Please slow down and try again.'
            ],

            500 => [
                'title' => 'Dispatch Center Offline',
                'message' => 'Something went wrong on our end.'
            ]
        ];

        $errorCode = $code;
        $errorTitle = $errors[$code]['title'] ?? 'Application Error';
        $errorMessage = $errors[$code]['message'] ?? 'An unexpected error occurred.';

        require base_path("app/views/errors/error.php");
        exit();
    }
};

?>