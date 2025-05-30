<?php namespace Aether\Interface;

/** 
 * Response Interface
 * 
 * @class Aether\Interface\ResponseInterface
**/

interface ResponseInterface
{
    public string $contentType { get; set; }
    public string $charset { get; set; }
    public array $headers { get; set; }
    public string $viewData { get; set; }
    public int $statusCode { get; set; }
    public bool $redirected { get; set; }
    public string $redirectParameter { get; set; }
    public string $redirectURL { get; set; }
    public bool $withInputData { get; set; }
    public array $flashData { get; set; }

    public function json(mixed $data): ResponseInterface;
    public function setCharset(string $charset): ResponseInterface;
    public function setContentType(string $contentType): ResponseInterface;
    public function setHeader(string $key, int|string $value): ResponseInterface;
    public function setStatusCode(int $code): ResponseInterface;
    public function redirect(): ResponseInterface;
    public function route(string $routeName): ResponseInterface;
    public function to(string $url): ResponseInterface;
    public function view(string $filepath): ResponseInterface;
    public function with(string|array $key, string|null $value): ResponseInterface;
    public function withInput(): ResponseInterface;
}