<?php declare(strict_types=1);
/*
 * This file is part of Templado\Engine.
 *
 * Copyright (c) Arne Blankerts <arne@blankerts.de> and contributors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Templado\Engine;

use function implode;
use function libxml_get_errors;
use ArrayIterator;
use DOMDocument;
use DOMElement;
use DOMNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Templado\Engine\Example\ViewModel;
use Throwable;

#[CoversClass(Document::class)]
#[UsesClass(Id::class)]
#[UsesClass(ParsingException::class)]
#[UsesClass(XPathSelector::class)]
#[UsesClass(Selection::class)]
#[UsesClass(ViewModel::class)]
#[UsesClass(ViewModelRenderer::class)]
#[UsesClass(FormData::class)]
#[UsesClass(FormDataRenderer::class)]
#[UsesClass(MergeList::class)]
#[UsesClass(Merger::class)]
#[UsesClass(DocumentCollection::class)]
#[UsesClass(Signal::class)]
#[UsesClass(StaticNodeList::class)]
#[UsesClass(TransformationProcessor::class)]
#[UsesClass(CSRFProtection::class)]
#[UsesClass(CSRFProtectionRenderer::class)]
#[Small]
class DocumentTest extends TestCase {
    use DomDocumentsEqualTrait;

    public function testCanBeConstructedFromString(): void {
        $this->assertInstanceOf(
            Document::class,
            Document::fromString('<?xml version="1.0" ?><root />')
        );
    }

    public function testCanBeConstructFromDomDocument(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root />');

        $this->assertInstanceOf(
            Document::class,
            Document::fromDomDocument($dom)
        );
    }

    public function testDomDocumentCanBeRetrieved(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root />');

        $this->assertSame(
            $dom,
            Document::fromDomDocument($dom)->asDomDocument()
        );
    }

    public function testHasIdReturnsFalseWhenNoneIsSet(): void {
        $this->assertFalse(
            (Document::fromString('<root />'))->hasId()
        );
    }

    public function testHasIdReturnsTrueWhenSet(): void {
        $this->assertTrue(
            (Document::fromString('<root />', new Id('an-ID')))->hasId()
        );
    }

    public function testAccesingNullIdThrowsException(): void {
        $doc = Document::fromString('<root />');

        $this->expectException(DocumentException::class);
        $doc->id();
    }

    public function testExistingLibxmlErrorStateGetsClearedOnConstruct(): void {
        libxml_use_internal_errors(true);
        $dummy = new DOMDocument();
        $dummy->loadXML('parsing-this-will-cause-libxml-errors');
        libxml_use_internal_errors(false);

        $this->assertInstanceOf(
            Document::class,
            Document::fromString('<?xml version="1.0" ?><root />')
        );
    }

    public function testCanBeConstructedFromStringWithId(): void {
        $id       = new Id('abc');
        $instance = Document::fromString('<?xml version="1.0" ?><root />', $id);
        $this->assertInstanceOf(
            Document::class,
            $instance
        );

        $this->assertSame($id, $instance->id());
    }

    public function testCanBeSerializedBackToStringWithoutSerializer(): void {
        $xml      = "<?xml version=\"1.0\"?>\n<root/>\n";
        $instance = Document::fromString($xml);
        $this->assertEquals($xml, $instance->asString());
    }

    public function testTryingToParseInvalidMarkupStringThrowsException(): void {
        $caught = null;

        try {
            Document::fromString('<?xml version="1.0" ?><root>');
        } catch (Throwable $t) {
            $caught = $t;
        }

        $this->assertInstanceOf(ParsingException::class, $caught);
        $this->assertEmpty(libxml_get_errors());
    }

    public function testSelectionOfSingleNodeCanBeExtracted(): void {
        $id     = new Id('test');
        $result = (Document::fromString('<?xml version="1.0" ?><root><child><p>text</p></child></root>'))->extract(
            new XPathSelector('//child'),
            $id
        );

        $this->assertInstanceOf(Document::class, $result);
        $this->assertEquals($id, $result->id());

        $result->asString(new class($this) implements Serializer {
            public function __construct(
                private TestCase $testCase
            ) {
            }
            public function serialize(DOMDocument $document): string {
                $this->testCase->assertEquals('child', $document->documentElement->nodeName);
                $this->testCase->assertTrue($document->documentElement->hasChildNodes());

                return '';
            }
        });
    }

    public function testExtractingEmptySelectionThrowsException(): void {
        $this->expectException(DocumentException::class);
        (Document::fromString('<?xml version="1.0" ?><root><child /></root>'))->extract(
            new XPathSelector('//invalid')
        );
    }

    public function testSelectionOfMultiNodesCanBeExtracted(): void {
        $result = (Document::fromString('<?xml version="1.0" ?><root><child><p>text</p></child><child /></root>'))->extract(
            new XPathSelector('//child')
        );

        $this->assertInstanceOf(Document::class, $result);

        $result->asString(new class($this) implements Serializer {
            public function __construct(
                private TestCase $testCase
            ) {
            }
            public function serialize(DOMDocument $document): string {
                $this->testCase->assertCount(2, $document->getElementsByTagName('child'));
                $this->testCase->assertTrue($document->documentElement->firstElementChild->hasChildNodes());

                return '';
            }
        });
    }

    public function testViewModelCanBeApplied(): void {
        $viewModel = new ViewModel();
        $dom       = new DOMDocument();
        $dom->load(__DIR__ . '/../_data/viewmodel/source.html');

        $page = Document::fromDomDocument($dom);
        $page->applyViewModel($viewModel);

        $expected = new DOMDocument();
        $expected->load(__DIR__ . '/../_data/viewmodel/expected.html');

        $this->assertResultMatches(
            $expected->documentElement,
            $dom->documentElement
        );
    }

    public function testTransformationCanBeApplied(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><child /></root>');

        $selection = $this->createMock(Selection::class);
        $selection->method('getIterator')->willReturn(new ArrayIterator([$dom->documentElement->firstChild]));

        $selector = $this->createMock(Selector::class);
        $selector->method('select')->willReturn($selection);

        $transformation = $this->createMock(Transformation::class);
        $transformation->expects($this->once())->method('selector')->willReturn($selector);
        $transformation->expects($this->once())->method('apply')->with($dom->documentElement->firstChild);

        $page = Document::fromDomDocument($dom);
        $page->applyTransformation($transformation);
    }

    public function testFormDataCanBeApplied(): void {
        $path = __DIR__ . '/../_data/formdata/text';

        $formdata = include $path . '/formdata.php';

        $dom = new DOMDocument();
        $dom->load($path . '/form.html');

        $expected = new DOMDocument();
        $expected->load($path . '/expected.html');

        $page = Document::fromDomDocument($dom);
        $page->applyFormData($formdata);

        $this->assertResultMatches(
            $expected->documentElement,
            $dom->documentElement
        );
    }

    public function testCSRFProtectionCanBeApplied(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><html><body><form></form></body></html>');

        $protection = new CSRFProtection('csrf', 'secure');

        $expected = new DOMDocument();
        $expected->loadXML(
            '<?xml version="1.0"?>
            <html><body><form><input type="hidden" name="csrf" value="secure"/></form></body></html>'
        );

        $page = Document::fromDomDocument($dom);
        $page->applyCSRFProtection($protection);

        $this->assertResultMatches(
            $expected->documentElement,
            $dom->documentElement
        );
    }

    public function testDocumentsCanBeMerged(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<html xmlns="http://www.w3.org/1999/xhtml"><body><span id="a" /><span id="b" /><span id="c" /></body></html>');

        $target = Document::fromDomDocument($dom);

        $snipA = Document::fromString('<templado:document xmlns="http://www.w3.org/1999/xhtml" xmlns:templado="https://templado.io/document/1.0"><div id="d" /><div id="e" /></templado:document>', new Id('a'));

        $list = new DocumentCollection(
            Document::fromString('<templado:document xmlns="http://www.w3.org/1999/xhtml" xmlns:templado="https://templado.io/document/1.0" id="c"><div id="f" /><div id="g" /></templado:document>', new Id('c')),
            Document::fromString('<templado:document xmlns="http://www.w3.org/1999/xhtml" xmlns:templado="https://templado.io/document/1.0"><div id="h" /><div id="i" /></templado:document>', new Id('d'))
        );

        $target->merge(
            $snipA,
            $list
        );

        $expected = new DOMDocument();
        $expected->loadXML('<?xml version="1.0"?><html xmlns="http://www.w3.org/1999/xhtml"><body><span id="a"><div id="d"><div id="h"/><div id="i"/></div><div id="e"/></span><span id="b"/><div id="f"/><div id="g"/></body></html>');

        $this->assertResultMatches(
            $expected->documentElement,
            $dom->documentElement
        );
    }

    public function testTryingToMergeDocumentWithoutIdThrowsException(): void {
        $target = Document::fromString('<?xml version="1.0" ?><root />');

        $this->expectException(DocumentException::class);
        $target->merge($target);
    }

    public function testTryingToMergeDocumentCollectionWithDocumentWithoutIdThrowsException(): void {
        $target = Document::fromString('<?xml version="1.0" ?><root />');

        $list = new DocumentCollection($target);

        $this->expectException(DocumentException::class);
        $target->merge($list);
    }

    public function testBlankWhitespaceGetsRemoved(): void {
        $document = Document::fromString(
            implode("\n", [
                '<?xml version="1.0" ?>',
                '<root>',
                '    <p>text</p>',
                '</root>'
            ])
        );

        $document->asString(new class($this) implements Serializer {
            public function __construct(
                private TestCase $testCase
            ) {
            }
            public function serialize(DOMDocument $document): string {
                $this->testCase->assertCount(1, $document->documentElement->childNodes);

                return '';
            }
        });
    }

    public function testNonFatalWarningsFromParsingAreCaught(): void {
        $this->expectException(ParsingException::class);
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
        . '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'
        . '<body>&nbsp;</body>';
        (Document::fromString($xml));
    }

    public function testAsStringResultIsFormattedAsException(): void {
        $expected = <<<'EOF'
<?xml version="1.0"?>
<root>
  <child1/>
  <child2/>
</root>

EOF;

        $xml = new DOMDocument();
        $xml->loadXML('<root><child1/><child2/></root>');

        $this->assertEquals($expected, (Document::fromDomDocument($xml))->asString());
    }

    public function testSelectorGetsUsedWhenViewModelGetsApplied(): void {
        $target = Document::fromString('<root><child property="handle" /><child property="ignore" /></root>');

        $target->applyViewModel(
            new class {
                public string $handle = 'text body';
            },
            new XPathSelector('//child[@property="handle"]')
        );

        $dom = new DomDocument();
        $dom->loadXML('<root><child property="handle">text body</child><child property="ignore" /></root>');

        $this->assertResultMatches($dom->documentElement, $target->asDomDocument()->documentElement);
    }

    public function testSelectorGetsUsedWhenTransformationGetsApplied(): void {
        $target = Document::fromString('<root><child property="handle" /><child property="ignore" /></root>');

        $target->applyTransformation(
            new class implements Transformation {
                public function selector(): Selector {
                    return new XPathSelector('self::*');
                }
                public function apply(DOMNode $context): void {
                    $context->setAttribute('transformation', 'done');
                }
            },
            new XPathSelector('//child[@property="handle"]')
        );

        $dom = new DomDocument();
        $dom->loadXML('<root><child transformation="done" property="handle" /><child property="ignore" /></root>');

        $this->assertResultMatches($dom->documentElement, $target->asDomDocument()->documentElement);
    }

    public function testCheckingSnapshotAvailabilityReturnsFalseWhenNoneHasBeenMadeYet(): void {
        $this->assertFalse(
            (Document::fromString('<root/>'))->hasSnapshot()
        );
    }

    public function testCheckingNamedSnapshotAvailabilityReturnsFalseWhenNoneHasBeenMadeYet(): void {
        $this->assertFalse(
            (Document::fromString('<root/>'))->hasSnapshot('some-label')
        );
    }

    public function testSnapshotCanBeCreated(): void {
        $doc = Document::fromString('<root/>');
        $doc->snapshot();

        $this->assertTrue(
            $doc->hasSnapshot()
        );
    }

    public function testNamedSnapshotCanBeCreated(): void {
        $doc = Document::fromString('<root/>');
        $doc->snapshot('label');

        $this->assertTrue(
            $doc->hasSnapshot('label')
        );
    }

    public function testSnapshotCanBeCleared(): void {
        $doc = Document::fromString('<root/>');
        $doc->snapshot();

        $this->assertTrue(
            $doc->hasSnapshot()
        );

        $doc->clearSnapshot();
        $this->assertFalse(
            $doc->hasSnapshot()
        );
    }

    public function testNamedSnapshotCanBeCleared(): void {
        $doc = Document::fromString('<root/>');
        $doc->snapshot('label');

        $this->assertTrue(
            $doc->hasSnapshot('label')
        );

        $doc->clearSnapshot('label');
        $this->assertFalse(
            $doc->hasSnapshot('label')
        );
    }

    public function testClearSnapshotsRemovesAll(): void {
        $doc = Document::fromString('<root/>');
        $doc->snapshot('label-1');
        $doc->snapshot('label-2');

        $this->assertTrue(
            $doc->hasSnapshot('label-1')
        );
        $this->assertTrue(
            $doc->hasSnapshot('label-2')
        );

        $doc->clearSnapshots();

        $this->assertFalse(
            $doc->hasSnapshot('label-1')
        );
        $this->assertFalse(
            $doc->hasSnapshot('label-2')
        );
    }

    public function testAttemptToClearNonExistingSnapshotThrowsException(): void {
        $this->expectException(DocumentException::class);
        (Document::fromString('<root/>'))->clearSnapshot();
    }

    public function testAttemptToClearNonExistingNamedSnapshotThrowsException(): void {
        $this->expectException(DocumentException::class);
        (Document::fromString('<root/>'))->clearSnapshot('label');
    }

    public function testCanRestoreToSnapshot(): void {
        $doc = Document::fromString('<root><child /></root>');
        $doc->snapshot();

        $doc->applyTransformation(new class implements Transformation {
            public function selector(): Selector {
                return new XPathSelector('/*');
            }

            public function apply(DOMNode $context): void {
                assert($context instanceof DOMElement);
                $context->setAttribute('test', 'true');
            }
        });

        $this->assertEquals(
            implode("\n", [
                '<?xml version="1.0"?>',
                '<root test="true"><child/></root>',
                ''
            ]),
            $doc->asDomDocument()->saveXML()
        );

        $doc->restore();

        $this->assertEquals(
            implode("\n", [
                '<?xml version="1.0"?>',
                '<root><child/></root>',
                ''
            ]),
            $doc->asDomDocument()->saveXML()
        );
    }

    public function testTryingToRestoreNonExistingSnapshotThrowsException(): void {
        $this->expectException(DocumentException::class);
        (Document::fromString('<root/>'))->restore('non-exisiting');
    }

    public function testClearingNamedSnapshotUsingInternalNameDoesNotConflict(): void {
        $doc = Document::fromString('<root/>');
        $doc->snapshot();
        $doc->snapshot('default');
        $doc->clearSnapshot('default');

        $this->assertTrue(
            $doc->hasSnapshot()
        );

        $this->assertFalse(
            $doc->hasSnapshot('default')
        );
    }

    public function testClearingUnnamedSnapshotWhileKeepingNamedSnapshotWithInternalNameWorks(): void {
        $doc = Document::fromString('<root/>');
        $doc->snapshot();
        $doc->snapshot('default');
        $doc->clearSnapshot();

        $this->assertTrue(
            $doc->hasSnapshot('default')
        );

        $this->assertFalse(
            $doc->hasSnapshot()
        );
    }

    public function testUsingMultipleNamedSnapshots(): void {
        $doc = Document::fromString('<root><child /></root>');
        $doc->snapshot('a');

        $doc->applyTransformation(new class implements Transformation {
            public function selector(): Selector {
                return new XPathSelector('/*');
            }

            public function apply(DOMNode $context): void {
                assert($context instanceof DOMElement);
                $context->setAttribute('test', 'true');
            }
        });

        $doc->snapshot('b');

        $doc->restore('a');

        $this->assertEquals(
            implode("\n", [
                '<?xml version="1.0"?>',
                '<root><child/></root>',
                ''
            ]),
            $doc->asDomDocument()->saveXML()
        );

        $doc->restore('b');
        $this->assertEquals(
            implode("\n", [
                '<?xml version="1.0"?>',
                '<root test="true"><child/></root>',
                ''
            ]),
            $doc->asDomDocument()->saveXML()
        );
    }
}
