<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use function libxml_get_errors;

#[Small]
class ParsingExceptionTest extends TestCase {
    public function testLibXMLErrorsCanBeRetrieved(): void {
        \libxml_use_internal_errors(true);
        (new \DOMDocument())->loadXML('<?xml version="1.0" ?><parseerror>');
        $exception = new ParsingException(...libxml_get_errors());
        $this->assertCount(1, $exception->errors());
        $this->assertEquals('Error(s) during parse', $exception->getMessage());
    }

    public function testToStringAlsoReturnsLibXMLErrorsAsString(): void {
        \libxml_use_internal_errors(true);
        (new \DOMDocument())->loadXML('<?xml version="1.0" ?><parseerror>');
        $exception = new ParsingException(...libxml_get_errors());
        $exceptionString = (string)$exception;

        $this->assertStringContainsString(
            $exception->getMessage() . "\n",
            $exceptionString
        );

        $this->assertStringContainsString(
            '[line: 1 / column: 35] Premature end of data in tag parseerror line 1',
            $exceptionString
        );
    }
}
