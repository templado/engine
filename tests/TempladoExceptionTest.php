<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\TestCase;

class TempladoExceptionTest extends TestCase {

    public function testLibXMLErrorsCanBeRetrieved() {
        libxml_use_internal_errors(true);
        (new \DOMDocument())->loadXML('<?xml version="1.0" ?><parseerror>');
        $exception = new TempladoException('test');
        $this->assertCount(1, $exception->getErrorList());
    }

}
