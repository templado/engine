<?php declare(strict_types = 1);
namespace TheSeer\Templado;

use DOMDocument;
use PHPUnit\Framework\TestCase;

class TransformationProcessorTest extends TestCase {

    public function testProcessCallsTransformation() {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><child /></root>');

        $selection = new Selection($dom->documentElement->childNodes);

        $selector = $this->createMock(Selector::class);
        $selector->method('select')->willReturn($selection);

        $transformation = $this->createMock(Transformation::class);
        $transformation->expects($this->any())->method('getSelector')->willReturn($selector);
        $transformation->expects($this->once())->method('apply')->with($dom->documentElement->firstChild);

        (new TransformationProcessor($dom->documentElement, $transformation))->process(
            $dom->documentElement, $transformation
        );
    }

    public function testEmptySelectionDoesNotCallTransformation() {
        $selection = new Selection(new \DOMNodeList());

        $selector = $this->createMock(Selector::class);
        $selector->method('select')->willReturn($selection);

        $transformation = $this->createMock(Transformation::class);
        $transformation->expects($this->any())->method('getSelector')->willReturn($selector);
        $transformation->expects($this->never())->method('apply');

        (new TransformationProcessor(new \DOMElement('foo'), $transformation))->process(
            new \DOMElement('bar'), $transformation
        );
    }

}
