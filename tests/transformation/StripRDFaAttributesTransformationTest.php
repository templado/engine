<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StripRDFaAttributesTransformation::class)]
#[UsesClass(Selection::class)]
#[UsesClass(XPathSelector::class)]
#[Small]
class StripRDFaAttributesTransformationTest extends TestCase {
    use DomDocumentsEqualTrait;

    public function testTransformationRemovedExpectedAttributes(): void {
        $transformation = new StripRDFaAttributesTransformation();
        $selector       = $transformation->selector();

        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root property="p" resource="r" prefix="p" typeof="t" />');

        $selection = $selector->select($dom->documentElement);
        $transformation->apply($selection->getIterator()->current());

        $this->assertResultMatches($dom->createElement('root'), $dom->documentElement, true);
    }

    public function testApplyingOnNoneElementDoesNothing(): void {
        $transformation = new StripRDFaAttributesTransformation();
        $node           = new class extends \DOMText {
            public function removeAttribute(): void {
                throw new \RuntimeException('removeAttribute should not have been called');
            }
        };
        $transformation->apply($node);
        $this->assertTrue(true);
    }
}
