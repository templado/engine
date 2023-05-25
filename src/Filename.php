<?php declare(strict_types=1);
/*
 * This file is part of Templado\Engine.
 *
 * Copyright (c) Arne Blankerts <arne@blankerts.de> and contributors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Templado\Engine;

use const PATHINFO_FILENAME;
use function file_exists;
use function is_file;
use function is_readable;
use function mime_content_type;
use function pathinfo;
use function realpath;

class Filename {
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
