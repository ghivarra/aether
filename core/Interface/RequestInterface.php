<?php namespace Aether\Interface;

interface RequestInterface
{
    public function cookie(string $key, mixed $default = null): mixed;
    public function file(string $key, mixed $default = null): mixed;
    public function files(string $key, mixed $default = null): mixed;
    public function get(string $key, mixed $default = null): mixed;
    public function header(string $key, string|null $default = null): string|null;
    public function headers(): array;
    public function input(mixed $default = null): mixed;
    public function json(mixed $default = null): mixed;
    public function post(string $key, mixed $default = null): mixed;
    public function requestType(): string;
    public function server(string $key, mixed $default = null): mixed;
}