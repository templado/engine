<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;

class Templado {

    public static function loadHtmlFile(FileName $fileName): Html {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $tmp = $dom->load($fileName->asString());
        if (!$tmp || libxml_get_last_error()) {
            throw new TempladoException(
                sprintf("Loading file '%s' failed.", $fileName->asString())
            );
        }

        return new Html($dom);
    }

    public static function parseHtmlString(string $string): Html {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $tmp = $dom->loadXML($string);
        if (!$tmp || libxml_get_last_error()) {
            throw new TempladoException('Parsing string failed.');
        }

        return new Html($dom);
    }

}
