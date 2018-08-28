<?php declare(strict_types = 1);
namespace Templado\Engine;

class FileName {

    /** @var string */
    private $path;

    public function __construct(string $path) {
        $this->path = $path;
    }

    public function asString(): string {
        return $this->path;
    }

    public function getMimeType(): string {
        return mime_content_type($this->path);
    }

    public function getName(): string {
        return pathinfo($this->path, PATHINFO_FILENAME);
    }

    public function exists(): bool {
        return file_exists($this->path);
    }

    public function isFile(): bool {
        return is_file(realpath($this->path));
    }

    public function isReadable(): bool {
        return is_readable($this->path);
    }

}
