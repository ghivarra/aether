<?php namespace Aether\Interface\Config;

interface SecurityInterface
{
    public string $tokenName { get; set; }
    public string $headerName { get; set; }
    public string $cookieName { get; set; }
    public int $expires { get; set; }
    public bool $regenerate { get; set; }
    public bool $redirect { get; set; }
}