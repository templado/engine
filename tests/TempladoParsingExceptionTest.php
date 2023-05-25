<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\TestCase;
use function libxml_get_errors;

class TempladoParsingExceptionTest extends TestCase {
    public function testLibXMLErrorsCanBeRetrieved(): void {
        \libxml_use_internal_errors(true);
        (new \DOMDocument())->loadXML('<?xml version="1.0" ?><parseerror>');
        $exception = new TempladoParsingException(...libxml_get_errors());
        $this->assertCount(1, $exception->errors());
    }
}
