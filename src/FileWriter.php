<?php declare(strict_types = 1);
namespace Templado\Engine;

interface FileWriter {
    public function toFile(Templado $document, Filename $filename): void;
}
