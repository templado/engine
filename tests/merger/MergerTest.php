<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Merger::class)]
#[UsesClass(Id::class)]
#[UsesClass(MergeList::class)]
#[UsesClass(StaticNodeList::class)]
#[Small]
class MergerTest extends TestCase {

    use DomDocumentsEqualTrait;

    public function testUsingEmptyDocumentThrowsException(): void {
        $merger = new Merger();
        $dom = new DOMDocument();

        $this->expectException(MergerException::class);
        $this->expectExceptionCode(MergerException::EmptyDocument);
        $merger->merge($dom, new MergeList());
    }

    public function testUsingEmptyMergeListThrowsException(): void {
        $merger = new Merger();
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root />');

        $this->expectException(MergerException::class);
        $this->expectExceptionCode(MergerException::EmptyList);
        $merger->merge($dom, new MergeList());
    }

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

    public function testCanReplaceSingleDocument(): void {
        $merger = new Merger();

        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><node id="test" /></root>');

        $toMerge = new DOMDocument();
        $toMerge->loadXML('<?xml version="1.0" ?><merged id="test" />');

        $list = new MergeList();
        $list->add(
            new Id('test'),
            $toMerge
        );

        $merger->merge($dom, $list);

        $expected = new DOMDocument();
        $expected->loadXML('<?xml version="1.0" ?><root><merged id="test" /></root>');

        $this->assertResultMatches($expected->documentElement, $dom->documentElement);
    }

    public function testMissingIdGetsSilentlyIgnored(): void {
        $merger = new Merger();

        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><node id="test" /></root>');

        $list = new MergeList();
        $list->add(
            new Id('not-used'),
            new DOMDocument()
        );

        $merger->merge($dom, $list);

        $expected = new DOMDocument();
        $expected->loadXML('<?xml version="1.0" ?><root><node id="test" /></root>');

        $this->assertResultMatches($expected->documentElement, $dom->documentElement);
    }

    public function testChildOfReplacedNodeGetsSkipped(): void {
        $merger = new Merger();

        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><node id="test"><child id="child" /></node></root>');

        $replace = new DOMDocument();
        $replace->loadXML('<?xml version="1.0" ?><replace id="test" />');

        $list = new MergeList();
        $list->add(
            new Id('test'),
            $replace
        );

        $merger->merge($dom, $list);

        $expected = new DOMDocument();
        $expected->loadXML('<?xml version="1.0" ?><root><replace id="test" /></root>');

        $this->assertResultMatches($expected->documentElement, $dom->documentElement);
    }

    public function testOnlyChildOfReplacedNodeGetsSkipped(): void {
        $merger = new Merger();

        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><node id="test"><child id="test" /></node><other id="other" /></root>');

        $replace = new DOMDocument();
        $replace->loadXML('<?xml version="1.0" ?><replace id="test" />');

        $list = new MergeList();
        $list->add(new Id('test'), $replace);

        $replace2 = new DOMDocument();
        $replace2->loadXML('<?xml version="1.0" ?><replace id="other" />');
        $list->add(new Id('other'), $replace2);

        $merger->merge($dom, $list);

        $expected = new DOMDocument();
        $expected->loadXML('<?xml version="1.0" ?><root><replace id="test" /><replace id="other" /></root>');

        $this->assertResultMatches($expected->documentElement, $dom->documentElement);
    }

    public function testDuplicateUseOfIdThrowsException(): void {
        $merger = new Merger();

        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><first id="test" /></root>');

        $test = new DOMDocument();
        $test->loadXML('<?xml version="1.0" ?><root><second id="test" /></root>');

        $list = new MergeList();
        $list->add(
            new Id('test'),
            $test
        );

        $this->expectException(MergerException::class);
        $this->expectExceptionCode(MergerException::DuplicateId);
        $merger->merge($dom, $list);
    }

    public function testMultipleDocumentsGetMergedRecursively(): void {
        $merger = new Merger();

        $dom = new DOMDocument();
        $dom->loadXML('<html xmlns="http://www.w3.org/1999/xhtml"><body><span id="a" /><span id="b" /><span id="c" /></body></html>');

        $snippets = [
            'a' => '<templado:document xmlns="http://www.w3.org/1999/xhtml" xmlns:templado="https://templado.io/document/1.0"><div id="d" /><div id="e" /></templado:document>',
            'c' => '<templado:document xmlns="http://www.w3.org/1999/xhtml" xmlns:templado="https://templado.io/document/1.0" id="c"><div id="f" /><div id="g" /></templado:document>',
            'd' => '<templado:document xmlns="http://www.w3.org/1999/xhtml" xmlns:templado="https://templado.io/document/1.0"><div id="h" /><div id="i" /></templado:document>'
        ];

        $list = new MergeList();
        foreach($snippets as $id => $snippetXml) {
            $snippetDom = new DOMDocument();
            $snippetDom->loadXML($snippetXml);

            $list->add(
                new Id($id),
                $snippetDom
            );
        }

        $merger->merge($dom, $list);

        $expected = new DOMDocument();
        $expected->loadXML('<?xml version="1.0"?><html xmlns="http://www.w3.org/1999/xhtml"><body><span id="a"><div id="d"><div id="h"/><div id="i"/></div><div id="e"/></span><span id="b"/><div id="f"/><div id="g"/></body></html>');

        $this->assertResultMatches($expected->documentElement, $dom->documentElement);
    }

}
