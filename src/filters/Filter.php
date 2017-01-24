<?php declare(strict_types = 1);
namespace TheSeer\Templado;

interface Filter {

    public function apply(string $content): string;

}
