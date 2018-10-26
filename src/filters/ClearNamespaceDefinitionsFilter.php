<?php declare(strict_types = 1);
namespace Templado\Engine;

class ClearNamespaceDefinitionsFilter implements Filter {

    public function apply(string $content): string {
        $content = preg_replace('/ xmlns=".*[^"]"/U', '', $content);
        if ($content === NULL) {
            throw new ClearNamespaceDefinitionsFilterException(
                'Error while processing regular expression',
                preg_last_error()
            );
        }

        $content = str_replace('<html', '<html xmlns="http://www.w3.org/1999/xhtml"', $content);

        return $content;
    }

}
