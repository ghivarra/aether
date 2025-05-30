<?php namespace Aether\Interface;

/** 
 * Routes Interface
 * 
 * @class Aether\Interface\RoutesInterface
**/
interface RoutesInterface
{
    public function as(string $alias): RoutesInterface;
    public function match(array|string $httpMethod, string $rule, string $controller, string $controllerMethod): RoutesInterface;
    public function middlewares(array|string $middleware): RoutesInterface;
    public function parse(string $uri): array;
}