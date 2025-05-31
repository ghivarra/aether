<?php namespace Aether\Interface;

interface RequestInterface
{
    public function file(string $key, mixed $default = null): mixed;
    public function files(string $key, mixed $default = null): mixed;
    public function get(string $key, mixed $default = null): mixed;
    public function input(string $key, mixed $default = null): mixed;
    public function json(mixed $default = null): mixed;
    public function post(string $key, mixed $default = null): mixed;
}