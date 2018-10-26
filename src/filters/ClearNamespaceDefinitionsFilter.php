<?php declare(strict_types = 1);
namespace Templado\Engine;

class ClearNamespaceDefinitionsFilter implements Filter {

    public function apply(string $content): string {
        $content = preg_replace('/ xmlns=".*[^"]"/U', '', $content);
        if ($content === NULL) {
            $errorCode = preg_last_error();
            throw new ClearNamespaceDefinitionsFilterException(
                sprintf('Error while processing regular expression: %s (%d)',
                    array_flip(get_defined_constants(true)['pcre'])[$errorCode],
                    $errorCode
                )
            );
        }

        $content = str_replace('<html', '<html xmlns="http://www.w3.org/1999/xhtml"', $content);

        return $content;
    }

}
