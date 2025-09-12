<?php

namespace core;

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

    public function route($uri, $method){
        foreach($this->routes as $route){
            if($route['uri'] === $uri && $route['method'] === strtoupper($method)){
                // Check if route is protected
                if ($route['protected'] && !isset($_SESSION['driver_id']) && !isset($_SESSION['logged_in'])) {
                    header("Location: /signin");
                    exit();
                }
                return require base_path($route['controller']);
                //return $route['controller'];
            }
        }
        $this->abort();
    }

    protected function abort($code = 404){
        http_response_code($code);
        header("Location: views/{$code}.php");
        die();
    }
};


?>