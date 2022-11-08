<?php declare(strict_types = 1);
namespace Templado\Engine;

class ClearNamespaceDefinitionsFilter implements Filter {
    private string $rootElementName;

    public function __construct(string $rootElementName = 'html') {
        $this->rootElementName = $rootElementName;
    }
    public function apply(string $content): string {
        $content = \preg_replace('/ xmlns=".*[^"]"/U', '', $content);

        if ($content === null) {
            throw new ClearNamespaceDefinitionsFilterException(
                'Error while processing regular expression',
                \preg_last_error()
            );
        }

        return \str_replace('<' . $this->rootElementName, '<' . $this->rootElementName . ' xmlns="http://www.w3.org/1999/xhtml"', $content);
    }
}
