<?php declare(strict_types = 1);
namespace Templado\Engine;

interface FileReader {
    public function fromFile(Filename $filename, ?Id $id = null): Templado;
}
