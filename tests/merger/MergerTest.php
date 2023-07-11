<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Merger::class)]
#[UsesClass(Id::class)]
#[UsesClass(MergeList::class)]
#[UsesClass(SnapshotDOMNodelist::class)]
class MergerTest extends TestCase {

    use DomDocumentsEqualTrait;

    public function testCanMergeSingleDocument(): void {
        $merger = new Merger();

        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><node id="test" /></root>');

        $toMerge = new DOMDocument();
        $toMerge->loadXML('<?xml version="1.0" ?><merged />');

        $list = new MergeList();
        $list->add(
            new Id('test'),
            $toMerge
        );


        $merger->merge($dom, $list);

        $expected = new DOMDocument();
        $expected->loadXML('<?xml version="1.0" ?><root><node id="test"><merged /></node></root>');

        $this->assertResultMatches($expected->documentElement, $dom->documentElement);

    }
}
