<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;

class Templado {
    public static function loadHtmlFile(FileName $fileName): Html {
        \libxml_use_internal_errors(true);
        \libxml_clear_errors();

        $dom                     = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $tmp                     = $dom->load($fileName->asString());
        $error                   = \libxml_get_last_error();
        $message                 = \sprintf("Loading file '%s' failed", $fileName->asString());

        if (!$tmp && $error === false) {
            throw new TempladoException($message);
        }

        if ($error instanceof \LibXMLError) {
            throw new TempladoException($message . ':' . \PHP_EOL . self::formatError($error));
        }

        return new Html($dom);
    }

    public static function parseHtmlString(string $string): Html {
        \libxml_use_internal_errors(true);
        \libxml_clear_errors();

        $dom     = new DOMDocument();
        $tmp     = $dom->loadXML($string);
        $error   = \libxml_get_last_error();
        $message = 'Parsing string failed';

        if (!$tmp && $error === false) {
            throw new TempladoException($message);
        }

        if ($error instanceof \LibXMLError) {
            throw new TempladoException($message . ':' . \PHP_EOL . self::formatError($error));
        }

        return new Html($dom);
    }

    private static function formatError(\LibXMLError $error): string
    {
        return \sprintf(
            '%s (Line %d, Column %d)',
            \trim($error->message),
            $error->line,
            $error->column
        );
    }
}
