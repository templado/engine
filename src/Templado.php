<?php declare(strict_types = 1);
namespace TheSeer\Templado;

use DOMDocument;

class Templado {

    public static function loadFile(FileName $fileName): Page {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $tmp = $dom->load($fileName->asString());
        if (!$tmp || libxml_get_last_error()) {
            throw new TempladoException(
                sprintf("Loading file '%s' failed.", $fileName->asString())
            );
        }

        return new Page($dom);
    }

    public static function parseString(string $string): Page {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $tmp = $dom->loadXML($string);
        if (!$tmp || libxml_get_last_error()) {
            throw new TempladoException('Parsing string failed.');
        }

        return new Page($dom);
    }

}
