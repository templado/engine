<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Templado\Engine\TransformationProcessor
 */
class TransformationProcessorTest extends TestCase {
    public function testProcessCallsTransformation(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><child /></root>');

        $selection = $this->createMock(Selection::class);
        $selection->method('getIterator')->willReturn(new \ArrayIterator([$dom->documentElement->firstChild]));

        $selector = $this->createMock(Selector::class);
        $selector->method('select')->willReturn($selection);

        $transformation = $this->createMock(Transformation::class);
        $transformation->expects($this->any())->method('getSelector')->willReturn($selector);
        $transformation->expects($this->once())->method('apply')->with($dom->documentElement->firstChild);

        (new TransformationProcessor($dom->documentElement, $transformation))->process(
            $dom->documentElement,
            $transformation
        );
    }

    public function testEmptySelectionDoesNotCallTransformation(): void {
        $selection = $this->createMock(Selection::class);
        $selection->method('isEmpty')->willReturn(true);

        $selector = $this->createMock(Selector::class);
        $selector->method('select')->willReturn($selection);

        $transformation = $this->createMock(Transformation::class);
        $transformation->expects($this->any())->method('getSelector')->willReturn($selector);
        $transformation->expects($this->never())->method('apply');

        (new TransformationProcessor(new \DOMElement('foo'), $transformation))->process(
            new \DOMElement('bar'),
            $transformation
        );
    }
}
