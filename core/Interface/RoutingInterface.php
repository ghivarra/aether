<?php namespace Aether\Interface;

/** 
 * Routing Interface
 * 
 * @class Aether\Interface\RoutingInterface
**/
interface RoutingInterface
{
    public function as(string $alias): RoutingInterface;
    public function all(string $rule, string $controller, string $controllerMethod): RoutingInterface;
    public function delete(string $rule, string $controller, string $controllerMethod): RoutingInterface;
    public function find(string $uri): array;
    public function findByAlias(string $alias, array $params = []): array;
    public function group(string $prefix, callable $callback, array $middlewares = ['before' => [], 'after' => []]): RoutingInterface;
    public function get(string $rule, string $controller, string $controllerMethod): RoutingInterface;
    public function getAllRoutes(): array;
    public function head(string $rule, string $controller, string $controllerMethod): RoutingInterface;
    public function match(array|string $httpMethod, string $rule, string $controller, string $controllerMethod): RoutingInterface;
    public function middlewares(array|string $middleware, string $executionTime = 'before'): RoutingInterface;
    public function options(string $rule, string $controller, string $controllerMethod): RoutingInterface;
    public function patch(string $rule, string $controller, string $controllerMethod): RoutingInterface;
    public function post(string $rule, string $controller, string $controllerMethod): RoutingInterface;
    public function put(string $rule, string $controller, string $controllerMethod): RoutingInterface;
}