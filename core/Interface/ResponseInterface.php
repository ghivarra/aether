<?php namespace Aether\Interface;

/** 
 * Response Interface
 * 
 * @class Aether\Interface\ResponseInterface
**/

interface ResponseInterface
{
    public function json(mixed $data): ResponseInterface;
    public function setCharset(string $charset): ResponseInterface;
    public function setContentType(string $contentType): ResponseInterface;
    public function setHeader(string $key, int|string $value): ResponseInterface;
    public function setJSON(mixed $data): ResponseInterface;
    public function setStatusCode(int $code): ResponseInterface;
    public function setViewData(string $data): ResponseInterface;
    public function redirect(): ResponseInterface;
    public function route(string $routeName, array $params = []): ResponseInterface;
    public function to(string $url): ResponseInterface;
    public function view(string $filepath): ResponseInterface;
    public function with(string|array $key, string|null $value): ResponseInterface;
    public function withInput(): ResponseInterface;
}