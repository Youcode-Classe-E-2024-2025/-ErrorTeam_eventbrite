<?php

namespace App\Core;

class Router
{
    private $routes = [];
    private $namedRoutes = []; 

    public function addRoute(string $method, string $path, $controller, string $action, string $name = null)
    {
        $this->routes[$method][$path] = [
            'controller' => $controller,
            'action' => $action,
            'name' => $name
        ];

        if ($name !== null) {
            $this->namedRoutes[$name] = $path;
        }
    }

    public function dispatch(string $method, string $uri)
    {
        $uri = strtok($uri, '?'); 
        $uri = $this->removeTrailingSlash($uri);

        foreach ($this->routes[$method] as $path => $route) {
            $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $path);
            $pattern = "#^" . $pattern . "$#";
            if (preg_match($pattern, $uri, $matches)) {
                $controllerName = $route['controller'];
                $actionName = $route['action'];
                $params = array_filter($matches, function ($key) {
                    return is_string($key);
                }, ARRAY_FILTER_USE_KEY);
                if (is_string($controllerName)) {
                    $controller = new $controllerName();
                    call_user_func_array([$controller, $actionName], $params);
                } else {
                    call_user_func_array($controllerName, $params);
                }
                return;
            }
        }

        http_response_code(404);
        echo View::render('front/error404.twig');
    }
    private function removeTrailingSlash(string $uri): string
    {
        $uri = rtrim($uri, '/');
        return $uri === "" ? "/" : $uri;
    }

    public function generateUrl(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \Exception("Route nommée '$name' non trouvée.");
        }

        $path = $this->namedRoutes[$name];
        foreach ($params as $key => $value) {
            $path = str_replace('{' . $key . '}', $value, $path);
        }

        return $path;
    }

    public function get(string $path, $controller, string $action, string $name = null) {
        $this->addRoute('GET', $path, $controller, $action, $name);
    }

    public function post(string $path, $controller, string $action, string $name = null) {
        $this->addRoute('POST', $path, $controller, $action, $name);
    }

    public function put(string $path, $controller, string $action, string $name = null) {
        $this->addRoute('PUT', $path, $controller, $action, $name);
    }

    public function delete(string $path, $controller, string $action, string $name = null) {
        $this->addRoute('DELETE', $path, $controller, $action, $name);
    }

    public function group(string $prefix, callable $callback) {
        $groupRouter = new GroupRouter($prefix, $this);
        $callback($groupRouter); 
    }
}

