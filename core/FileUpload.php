<?php

declare(strict_types = 1);

namespace Aether;

class FileUpload
{
    private array $file = [];
    private string $ext = '';
    private string $name = '';
    private string $basename = '';
    private string $path = '';

    //====================================================================================================

    public function __construct(array $file)
    {
        $this->file = $file;
        $this->path = $file['tmp_name'];
        $this->name = $file['name'];

        $ext = str_contains($file['name'], '.') ? strrchr($file['name'], '.') : '';
        $this->ext = substr($ext, 1);
        $this->basename = substr($file['name'], 0, (strlen($file['name']) - strlen($ext)));
    }

    //====================================================================================================

    public function isValid(): bool
    {
        return ($this->file['error'] === 0) && file_exists($this->file['tmp_name']) && is_writable($this->file['tmp_name']);
    }

    //====================================================================================================

    public function move(string $dir, string|null $basename = null): string|false
    {
        if ($this->hasMoved())
        {
            return false;
        }

        $basename = is_null($basename) ? $this->basename : $basename;
        $filename = $basename . '.' . $this->ext;
        $fullpath = $dir . DIRECTORY_SEPARATOR . $filename;

        // move
        move_uploaded_file($this->file['tmp_name'], $fullpath);

        // set path & name
        $this->basename = $basename;
        $this->name     = $filename;
        $this->path     = $fullpath;

        // return
        return $fullpath;
    }

    //====================================================================================================

    public function hasMoved(): bool
    {
        return $this->path === $this->file['tmp_name'];
    }

    //====================================================================================================

    public function getExtension(): string
    {
        return $this->ext;
    }

    //====================================================================================================

    public function getMimeType(): string
    {
        return mime_content_type($this->path);
    }

    //====================================================================================================

    public function getName(): string
    {
        return $this->name;
    }

    //====================================================================================================

    public function getPath(): string
    {
        return $this->path;
    }

    //====================================================================================================

    public function getRandomName(): string
    {
        return random_string('alpanumeric', 24) . '_' . time();
    }

    //====================================================================================================

    public function getSize(): int
    {
        return $this->file['size'];
    }

    //====================================================================================================

    public function getSizeByUnit(string $option): int|float
    {
        $option = strtolower($option);
        $size   = $this->file['size'];

        switch ($option) {
            case 'kb':
                return round($size / 1000, 2);
                break;

            case 'mb':
                return round($size / 1000000, 2);
                break;

            case 'gb':
                return round($size / 1000000000, 2);
                break;
            
            default:
                return $size;
                break;
        }
    }

    //====================================================================================================

    public function getClientName(): string
    {
        return $this->file['name'];
    }

    //====================================================================================================

    public function getClientExtension(): string
    {
        return str_contains($this->file['name'], '.') ? substr(strrchr($this->file['name'], '.'), 1) : '';
    }

    //====================================================================================================

    public function getClientMimeType(): string
    {
        return $this->file['type'];
    }

    //====================================================================================================

    public function getClientPath(): string
    {
        return $this->file['tmp_name'];
    }

    //====================================================================================================
}