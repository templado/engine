<?php declare(strict_types = 1);
namespace Templado\Engine;

class ClearRedundantHtmlNamespaceDefinitionsFilter implements Filter {
    private string $rootElementName;

    public function __construct(string $rootElementName = 'html') {
        $this->rootElementName = $rootElementName;
    }
    public function apply(string $content): string {
        $content = \preg_replace('# xmlns="http://www.w3.org/1999/xhtml"#U', '', $content,  -1, $count);

        if ($content === null) {
            throw new ClearNamespaceDefinitionsFilterException(
                'Error while processing regular expression',
                \preg_last_error()
            );
        }

        if ($count === 0) {
            return $content;
        }

        return \str_replace('<' . $this->rootElementName, '<' . $this->rootElementName . ' xmlns="http://www.w3.org/1999/xhtml"', $content);
    }
}
