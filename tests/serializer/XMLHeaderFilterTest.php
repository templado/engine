<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\TestCase;

class XMLHeaderFilterTest extends TestCase {

    public function testStripsXMLHeaderFromStartOfString(): void {
        $this->assertSame(
            '<stripped />',
            (new XMLHeaderFilter())->apply('<?xml version="1.0"?><stripped />')
        );
    }

    public function testStripsXMLHeaderFromStartOfStringWithWhitespace(): void {
        $this->assertSame(
            '<stripped />',
            (new XMLHeaderFilter())->apply('<?xml version="1.0"?>        <stripped />')
        );
    }

    public function testStripsXMLHeaderFromStartOfStringWithLinebreak(): void {
        $this->assertSame(
            '<stripped />',
            (new XMLHeaderFilter())->apply('<?xml version="1.0"?>' . PHP_EOL .'<stripped />')
        );
    }

    public function testIgnoresXMLHeaderWhenNotAtBeginning(): void {
        $markup = '<!-- something at the start --><?xml version="1.0"?><stripped />';

        $this->assertSame(
            $markup,
            (new XMLHeaderFilter())->apply($markup)
        );
    }

}
