<?php declare(strict_types = 1);
namespace Templado\Engine;

interface Filter {

    public function apply(string $content): string;

}
