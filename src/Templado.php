<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;
use LibXMLError;

class Templado {
    public static function loadHtmlFile(FileName $fileName): Html {
        \libxml_use_internal_errors(true);
        \libxml_clear_errors();

        $dom                     = new DOMDocument();
        $dom->preserveWhiteSpace = false;

        if (!$dom->load($fileName->asString())) {
            $error   = \libxml_get_last_error();
            assert($error instanceof LibXMLError);

            throw new TempladoException(
                \sprintf("Loading file '%s' failed:\n%s",
                    $fileName->asString(),
                    self::formatError($error))
            );
        }

        return new Html($dom);
    }

    public static function parseHtmlString(string $string): Html {
        \libxml_use_internal_errors(true);
        \libxml_clear_errors();

        $dom     = new DOMDocument();
        if (!$dom->loadXML($string)) {
            $error   = \libxml_get_last_error();
            assert($error instanceof LibXMLError);

            throw new TempladoException('Parsing string failed:' . \PHP_EOL . self::formatError($error));
        }

        return new Html($dom);
    }

    private static function formatError(\LibXMLError $error): string {
        return \sprintf(
            '%s (Line %d, Column %d)',
            \trim($error->message),
            $error->line,
            $error->column
        );
    }
}
