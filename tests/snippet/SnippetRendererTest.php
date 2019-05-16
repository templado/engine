<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Templado\Engine\SnippetRenderer
 */
class SnippetRendererTest extends TestCase {
    public function testSimpleElementGetsAdded(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><child id="a"/></root>');
        $collection = $this->createMocksForDom($dom);

        $renderer = new SnippetRenderer($collection);
        $renderer->render($dom->documentElement);
    }

    public function testMissingIdGetsIgnored(): void {
        $xml = '<?xml version="1.0" ?><root><child id="a"/></root>';

        $dom = new DOMDocument();
        $dom->loadXML($xml);

        /** @var \PHPUnit_Framework_MockObject_MockObject|SnippetListCollection $collection */
        $collection = $this->createMock(SnippetListCollection::class);
        $collection->method('hasSnippetsForId')->willReturn(false);

        $renderer = new SnippetRenderer($collection);
        $renderer->render($dom->documentElement);

        $expected = new DOMDocument();
        $expected->loadXML($xml);

        $this->assertEqualXMLStructure(
            $expected->documentElement,
            $dom->documentElement
        );
    }

    public function testNonElementNodesGetIgnored(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><!-- comment --></root>');

        /** @var \PHPUnit_Framework_MockObject_MockObject|SnippetListCollection $collection */
        $collection = $this->createMock(SnippetListCollection::class);
        $collection->method('hasSnippetsForId')->willReturn(false);

        $renderer = new SnippetRenderer($collection);
        $renderer->render($dom->documentElement);

        $expected = new DOMDocument();
        $expected->loadXML('<?xml version="1.0" ?><root><!-- comment --></root>');

        $this->assertEqualXMLStructure(
            $expected->documentElement,
            $dom->documentElement
        );
    }

    public function testRenderWorksRecursively(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><child id="a"><subchild /></child></root>');

        $collection = $this->createMocksForDom($dom);

        $renderer = new SnippetRenderer($collection);
        $renderer->render($dom->documentElement);
    }

    public function testRenderingWorksRecursivelyOverSnippetReplacedElements(): void {
        $page = new DOMDocument();
        $page->loadXML('<?xml version="1.0" ?><root><target id="a" /></root>');

        $dom1 = new DOMDocument();
        $dom1->loadXML('<?xml version="1.0" ?><child id="a"><subchild id="b" /></child>');
        $snippet1 = $this->createMock(Snippet::class);
        $snippet1->expects($this->once())->method('applyTo')
            ->with($page->documentElement->firstChild)
            ->willReturn($dom1->documentElement);

        $dom2 = new DOMDocument();
        $dom2->loadXML('<?xml version="1.0" ?><replacement id="b"><Nested in="b" /></replacement>');
        $snippet2 = $this->createMock(Snippet::class);
        $snippet2->expects($this->once())->method('applyTo')
            ->with($dom1->documentElement->firstChild)
            ->willReturn($dom2->documentElement);

        $snippetList1 = $this->createSnippetListMock($snippet1);
        $snippetList2 = $this->createSnippetListMock($snippet2);

        $collection = $this->createMock(SnippetListCollection::class);
        $collection->expects($this->exactly(2))->method('hasSnippetsForId')->withConsecutive(['a'], ['b'])->willReturn(true);
        $collection->method('getSnippetsForId')->willReturnOnConsecutiveCalls(
            $snippetList1,
            $snippetList2
        );

        $renderer = new SnippetRenderer($collection);
        $renderer->render($page->documentElement);
    }

    public function testNonElementReplacementtGetsHandled(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><child id="a"/></root>');

        $snippet = $this->createMock(Snippet::class);
        $snippet->expects($this->once())->method('applyTo')
            ->with($dom->documentElement->firstChild)
            ->willReturn($dom->createTextNode('replacement-text'));

        $snippetList = $this->createSnippetListMock($snippet);
        $collection  = $this->createSnippetCollectionMock($snippetList);

        $renderer = new SnippetRenderer($collection);
        $renderer->render($dom->documentElement);
    }

    private function createSnippetMock(DOMDocument $dom): \PHPUnit_Framework_MockObject_MockObject {
        $snippet = $this->createMock(Snippet::class);
        $snippet->expects($this->once())->method('applyTo')
            ->with($dom->documentElement->firstChild)
            ->willReturn($dom->documentElement->firstChild);

        return $snippet;
    }

    /**
     * @param $snippet
     */
    private function createSnippetListMock($snippet): \PHPUnit_Framework_MockObject_MockObject {
        $snippetList = $this->createMock(SnippetList::class);
        $snippetList->method('valid')->willReturn(true, false);
        $snippetList->method('current')->willReturn($snippet);

        return $snippetList;
    }

    /**
     * @param $snippetList
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|SnippetListCollection
     */
    private function createSnippetCollectionMock($snippetList) {
        /** @var \PHPUnit_Framework_MockObject_MockObject|SnippetListCollection $collection */
        $collection = $this->createMock(SnippetListCollection::class);
        $collection->method('hasSnippetsForId')->willReturn(true);
        $collection->method('getSnippetsForId')->willReturn($snippetList);

        return $collection;
    }

    /**
     * @param $dom
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|SnippetListCollection
     */
    private function createMocksForDom($dom) {
        $snippet     = $this->createSnippetMock($dom);
        $snippetList = $this->createSnippetListMock($snippet);

        return $this->createSnippetCollectionMock($snippetList);
    }
}
