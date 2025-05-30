<?php namespace Aether\Interface;

/** 
 * Routes Interface
 * 
 * @class Aether\Interface\RoutesInterface
**/
interface RoutesInterface
{
    public function as(string $alias): RoutesInterface;
    public function all(string $rule, string $controller, string $controllerMethod): RoutesInterface;
    public function delete(string $rule, string $controller, string $controllerMethod): RoutesInterface;
    public function get(string $rule, string $controller, string $controllerMethod): RoutesInterface;
    public function getAllRoutes(): array;
    public function head(string $rule, string $controller, string $controllerMethod): RoutesInterface;
    public function match(array|string $httpMethod, string $rule, string $controller, string $controllerMethod): RoutesInterface;
    public function middlewares(array|string $middleware): RoutesInterface;
    public function options(string $rule, string $controller, string $controllerMethod): RoutesInterface;
    public function parse(string $uri): array;
    public function patch(string $rule, string $controller, string $controllerMethod): RoutesInterface;
    public function post(string $rule, string $controller, string $controllerMethod): RoutesInterface;
    public function put(string $rule, string $controller, string $controllerMethod): RoutesInterface;
}