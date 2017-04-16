<?php declare(strict_types = 1);
namespace Templado\Engine;

class ClearNamespaceDefinitionsFilter implements Filter {

    public function apply(string $content): string {
        $content = preg_replace('/ xmlns=".*[^"]"/U', '', $content);
        $content = str_replace('<html', '<html xmlns="http://www.w3.org/1999/xhtml"', $content);

        return $content;
    }

}
