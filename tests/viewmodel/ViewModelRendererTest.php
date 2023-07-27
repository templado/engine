<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;
use DOMElement;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Templado\Engine\Example\ViewModel;
use Templado\Engine\PrefixModel\PrefixCallViewModel;
use Templado\Engine\PrefixModel\PrefixPropertyGetViewModel;
use Templado\Engine\PrefixModel\PrefixPropertyViewModel;
use Templado\Engine\PrefixModel\PrefixViewModel;
use Templado\Engine\ResourceModel\ResourceCallViewModel;
use Templado\Engine\ResourceModel\ResourcePropertyGetViewModel;
use Templado\Engine\ResourceModel\ResourcePropertyViewModel;
use Templado\Engine\ResourceModel\ResourceViewModel;

#[CoversClass(ViewModelRenderer::class)]
#[UsesClass(Signal::class)]
#[UsesClass(StaticNodeList::class)]
#[UsesClass(Document::class)]
#[Small]
class ViewModelRendererTest extends TestCase {
    use DomDocumentsEqualTrait;

    public function testViewModelGetsAppliedAsExcepted(): void {
        $viewModel = new ViewModel();
        $dom       = new DOMDocument();
        $dom->load(__DIR__ . '/../_data/viewmodel/source.html');

        $renderer = new ViewModelRenderer();
        $renderer->render($dom->documentElement, $viewModel);

        $expected = new DOMDocument();
        $expected->load(__DIR__ . '/../_data/viewmodel/expected.html');

        $this->assertResultMatches(
            $expected->documentElement,
            $dom->documentElement,
            true
        );
    }

    public function testUsingContextElementWithOwnerDocumentThrowsException(): void {
        $this->expectException(ViewModelRendererException::class);
        (new ViewModelRenderer())->render(
            new DOMElement('foo'),
            new class {}
        );
    }

    public function testIteratorReturnValueGetsApplied(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><html><body><p property="a" /></body></html>');

        $viewModel = new class {
            public function a() {
                return new \ArrayIterator(['a', 'b']);
            }
        };

        $renderer = new ViewModelRenderer();
        $renderer->render($dom->documentElement, $viewModel);

        $expected = new DOMDocument();
        $expected->loadXML('<?xml version="1.0"?><html><body><p property="a">a</p><p property="a">b</p></body></html>');

        $this->assertEquals(
            $expected->documentElement,
            $dom->documentElement
        );
    }

    public function testMagicCallMethodGetsCalledWhenDefinedAndNoExplicitMethodFits(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><html><body><p property="a" /></body></html>');

        $viewModel = new class {
            public function __call($name, $args) {
                return 'text';
            }
        };

        $renderer = new ViewModelRenderer();
        $renderer->render($dom->documentElement, $viewModel);

        $expected = new DOMDocument();
        $expected->loadXML('<?xml version="1.0"?><html><body><p property="a">text</p></body></html>');

        $this->assertEquals(
            $expected->documentElement,
            $dom->documentElement
        );
    }

    public function testMagicCallMethodGetsCalledForAttributesWhenDefinedAndNoExplicitMethodFits(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><html><body><p property="a" attr="b" /></body></html>');

        $viewModel = new class {
            public function __call($name, $args) {
                switch ($name) {
                    case 'a': return $this;
                    case 'property': return null;
                    default: return 'text';
                }
            }
        };

        $renderer = new ViewModelRenderer();
        $renderer->render($dom->documentElement, $viewModel);

        $expected = new DOMDocument();
        $expected->loadXML('<?xml version="1.0"?><html><body><p property="a" attr="text">text</p></body></html>');

        $this->assertEquals(
            $expected->documentElement,
            $dom->documentElement
        );
    }

    public function testDashesInAttributeNamesGetTranslatedToCamelCase(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><root property="a" attr-with-dash="old"/>');

        $viewModel = new class {
            public function a() {
                return new class {
                    public function getAttrWithDash() {
                        return 'new';
                    }
                };
            }
        };

        $renderer = new ViewModelRenderer();
        $renderer->render($dom->documentElement, $viewModel);

        $expected = new DOMDocument();
        $expected->loadXML('<?xml version="1.0"?><root property="a" attr-with-dash="new"/>');

        $this->assertEquals(
            $expected->documentElement,
            $dom->documentElement
        );
    }

    public function testViewModelMethodReturningBooleanTrueKeepsNode(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root property="test" />');

        $renderer = new ViewModelRenderer();
        $renderer->render($dom->documentElement, new class {
            public function test() {
                return true;
            }
        });

        $expected = new DOMDocument();
        $expected->loadXML('<?xml version="1.0" ?><root property="test" />');

        $this->assertEquals(
            $expected->documentElement,
            $dom->documentElement
        );
    }

    public function testNoMethodForPropertyThrowsException(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root property="test" />');

        $renderer = new ViewModelRenderer();

        $this->expectException(ViewModelRendererException::class);
        $renderer->render($dom->documentElement, new class {
        });
    }

    public function testUnsupportedVariableTypeThrowsException(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root property="test" />');

        $model = new class {
            public function test() {
                return 1;
            }
        };

        $renderer = new ViewModelRenderer();

        $this->expectException(ViewModelRendererException::class);
        $renderer->render($dom->documentElement, $model);
    }

    public function testUnsupportedVariableTypeForAttributeThrowsException(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root property="test" attr="value" />');

        $model = new class {
            public function test() {
                return new class {
                    public function attr() {
                        return 1;
                    }
                };
            }
        };

        $renderer = new ViewModelRenderer();

        $this->expectException(ViewModelRendererException::class);
        $renderer->render($dom->documentElement, $model);
    }

    public function testMissingTypeOfMethodOnConditionContextThrowsException(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root property="test" typeof="A" />');

        $renderer = new ViewModelRenderer();

        $this->expectException(ViewModelRendererException::class);
        $renderer->render($dom->documentElement, new class {
            public function getTest() {
                return new class {
                };
            }
        });
    }

    public function testNonObjectResultForTypeOfMethodOnConditionContextThrowsException(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root property="test" typeof="A" />');

        $renderer = new ViewModelRenderer();

        $this->expectException(ViewModelRendererException::class);
        $this->expectExceptionCode(ViewModelRendererException::WrongTypeForTypeOf);
        $renderer->render($dom->documentElement, new class {
            public function getTest() {
                return [
                    'test'
                ];
            }
        });
    }

    public function testNoExsitingTypeForRequestedTypeOfMethodOnConditionContextThrowsException(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root property="test" typeof="A" />');

        $renderer = new ViewModelRenderer();

        $this->expectException(ViewModelRendererException::class);
        $renderer->render($dom->documentElement, new class {
            public function getTest() {
                return new class {
                    public function typeOf() {
                        return 'B';
                    }
                };
            }
        });
    }

    public function testMultipleElementsForPropertyOnRootNodeThrowsException(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root property="test" />');

        $renderer = new ViewModelRenderer();

        $this->expectException(ViewModelRendererException::class);
        $renderer->render($dom->documentElement, new class {
            public function getTest() {
                return ['a', 'b'];
            }
        });
    }

    public function testEmptyArrayForPropertyOnRootNodeThrowsException(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root property="test" />');

        $renderer = new ViewModelRenderer();

        $this->expectException(ViewModelRendererException::class);
        $renderer->render($dom->documentElement, new class {
            public function getTest() {
                return [];
            }
        });
    }

    public function testTypeOfSelectionPicksCorrectContextInObjectUse(): void {
        $model = new class {
            public function getOne() {
                return new class {
                    public function typeOf() {
                        return 'B';
                    }

                    public function getText() {
                        return 'Replaced text of B';
                    }
                };
            }
        };

        $source = new DOMDocument();
        $source->load(__DIR__ . '/../_data/typeof/source.xhtml');

        $renderer = new ViewModelRenderer();
        $renderer->render($source->documentElement, $model);

        $expected = new DOMDocument();
        $expected->load(__DIR__ . '/../_data/typeof/expected-single.xhtml');

        $this->assertResultMatches(
            $expected->documentElement,
            $source->documentElement,
            true
        );
    }

    public function testTypeOfSelectionPicksCorrectContextInLists(): void {
        $model = new class {
            public function getOne() {
                return [
                    new class {
                        public function typeOf() {
                            return 'B';
                        }

                        public function getText() {
                            return 'Replaced text of B';
                        }
                    },
                    new class {
                        public function typeOf() {
                            return 'A';
                        }

                        public function getText() {
                            return 'Replaced text of A';
                        }
                    }
                ];
            }
        };

        $source = new DOMDocument();
        $source->load(__DIR__ . '/../_data/typeof/source.xhtml');

        $renderer = new ViewModelRenderer();
        $renderer->render($source->documentElement, $model);

        $expected = new DOMDocument();
        $expected->load(__DIR__ . '/../_data/typeof/expected-list.xhtml');

        $this->assertResultMatches(
            $expected->documentElement,
            $source->documentElement,
            true
        );
    }

    public function testTypeOfSelectionPicksCorrectContextInCombinedObjectListUse(): void {
        $model = new class {
            public function getOne() {
                return [
                    new class {
                        public function getTwo() {
                            return new class {
                                public function typeOf() {
                                    return 'B';
                                }

                                public function getText() {
                                    return 'Replaced text of B';
                                }
                            };
                        }
                    }
                ];
            }
        };

        $source = new DOMDocument();
        $source->load(__DIR__ . '/../_data/typeof/combined-source.xhtml');

        $renderer = new ViewModelRenderer();
        $renderer->render($source->documentElement, $model);

        $expected = new DOMDocument();
        $expected->load(__DIR__ . '/../_data/typeof/combined-expected.xhtml');

        $this->assertResultMatches(
            $expected->documentElement,
            $source->documentElement,
            true
        );
    }

    public function testTypeOfSelectionPicksCorrectContextInComplexScenario(): void {
        $model = new class {
            public function getOne() {
                return [
                    new class {
                        public function typeOf() {
                            return 'A';
                        }

                        public function getText() {
                            return 'new text for type A';
                        }
                    },
                    new class {
                        public function typeOf() {
                            return 'B';
                        }

                        public function getTwo() {
                            return new class {
                                public function typeOf() {
                                    return 'B.2';
                                }

                                public function asString() {
                                    return 'new text two-B.2';
                                }
                            };
                        }
                    }
                ];
            }
        };

        $source = new DOMDocument();
        $source->load(__DIR__ . '/../_data/typeof/complex-source.xhtml');

        $renderer = new ViewModelRenderer();
        $renderer->render($source->documentElement, $model);

        $expected = new DOMDocument();
        $expected->load(__DIR__ . '/../_data/typeof/complex-expected.xhtml');

        $this->assertResultMatches(
            $expected->documentElement,
            $source->documentElement,
            true
        );
    }

    #[DataProvider('resourceViewModelProvider')]
    public function testResourceViewModelGetsAppliedAsExcepted(object $viewModel): void {
        $dom                     = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->load(__DIR__ . '/../_data/viewmodel/resource/source.html');

        $renderer = new ViewModelRenderer();
        $renderer->render($dom->documentElement, $viewModel);

        $expected                     = new DOMDocument();
        $expected->preserveWhiteSpace = false;
        $expected->load(__DIR__ . '/../_data/viewmodel/resource/expected.html');

        $this->assertResultMatches($expected->documentElement, $dom->documentElement);
    }

    public static function resourceViewModelProvider(): array {
        return [
            'method' => [new ResourceViewModel()],
            'call' => [new ResourceCallViewModel()],
            'property' => [new ResourcePropertyViewModel()],
            'get' => [new ResourcePropertyGetViewModel()]
        ];
    }

    public function testUsingAResourceWithNoMethodToRequestItThrowsException(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><root resource="foo" />');

        $renderer = new ViewModelRenderer();

        $this->expectException(ViewModelRendererException::class);
        $renderer->render($dom->documentElement, new \stdClass());
    }

    public function testResolvingResourceToNonObjectThrowsException(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><root resource="foo" />');

        $renderer = new ViewModelRenderer();

        $this->expectException(ViewModelRendererException::class);
        $renderer->render($dom->documentElement, new class { public function foo(): string { return 'crash'; }});
    }


    #[DataProvider('prefixViewModelProvider')]
    public function testPrefixViewModelGetsAppliedAsExcepted(object $viewModel): void {
        $dom                     = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->load(__DIR__ . '/../_data/viewmodel/prefix/source.html');

        $renderer = new ViewModelRenderer();
        $renderer->render($dom->documentElement, $viewModel);

        $expected                     = new DOMDocument();
        $expected->preserveWhiteSpace = false;
        $expected->load(__DIR__ . '/../_data/viewmodel/prefix/expected.html');

        $this->assertResultMatches($expected->documentElement, $dom->documentElement);
    }

    public static function prefixViewModelProvider(): array {
        return [
            'method' => [new PrefixViewModel()],
            'call' => [new PrefixCallViewModel()],
            'property' => [new PrefixPropertyViewModel()],
            'get' => [new PrefixPropertyGetViewModel()]
        ];
    }

    public function testPrefixWithDoubleColonViewModelGetsAppliedAsExcepted(): void {
        $viewModel               = new PrefixViewModel();
        $dom                     = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->load(__DIR__ . '/../_data/viewmodel/prefix/colon-source.html');

        $renderer = new ViewModelRenderer();
        $renderer->render($dom->documentElement, $viewModel);

        $expected                     = new DOMDocument();
        $expected->preserveWhiteSpace = false;
        $expected->load(__DIR__ . '/../_data/viewmodel/prefix/colon-expected.html');

        $this->assertResultMatches($expected->documentElement, $dom->documentElement);
    }

    public function testUsingAPrefixWithNoMethodToRequestItThrowsException(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><root prefix="p: foo" />');

        $renderer = new ViewModelRenderer();

        $this->expectException(ViewModelRendererException::class);
        $renderer->render($dom->documentElement, new \stdClass());
    }

    public function testResolvingPrefixToNonObjectThrowsException(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><root prefix="p: foo" />');

        $renderer = new ViewModelRenderer();

        $this->expectException(ViewModelRendererException::class);
        $renderer->render($dom->documentElement, new class { public function foo(): string { return 'crash'; }});
    }

    public function testUsingAnUndefinedPrefixThrowsException(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><root property="p:foo" />');

        $renderer = new ViewModelRenderer();

        $this->expectException(ViewModelRendererException::class);
        $renderer->render($dom->documentElement, new \stdClass());
    }

    public function testInvalidPrefixDefinitionThrowsException(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><root prefix="invalid" />');

        $renderer = new ViewModelRenderer();

        $this->expectException(ViewModelRendererException::class);
        $renderer->render($dom->documentElement, new \stdClass());
    }

    public function testForeignRDFaAnnotationsGetIgnored(): void {
        $viewModel = new class {
        };
        $dom                     = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->load(__DIR__ . '/../_data/viewmodel/prefix/og-source.html');

        $renderer = new ViewModelRenderer();
        $renderer->render($dom->documentElement, $viewModel);

        $expected                     = new DOMDocument();
        $expected->preserveWhiteSpace = false;
        $expected->load(__DIR__ . '/../_data/viewmodel/prefix/og-source.html');

        $this->assertResultMatches($expected->documentElement, $dom->documentElement);
    }

    public function testNonObjectcannotBeAddedToStack(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><root property="test"><child property="foo" /></root>');

        $class = new class {
            public function test(): bool {
                return true;
            }
        };

        $renderer = new ViewModelRenderer();

        $this->expectException(ViewModelRendererException::class);
        $renderer->render($dom->documentElement, $class);
    }

    public function testSettingTextStringWithXMLSpecialCharsGetsProperlyEncoded(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><root property="test" />');

        $class = new class {
            public function test(): string {
                return 'Text with <tag> and & included';
            }
        };

        $renderer = new ViewModelRenderer();
        $renderer->render($dom->documentElement, $class);

        $expected = new DOMDocument();
        $expected->loadXML('<?xml version="1.0"?><root property="test">Text with &lt;tag&gt; and &amp; included</root>');

        $this->assertResultMatches($expected->documentElement, $dom->documentElement);
    }

    public function testSettingTextStringWithXMLSpecialCharsFromObjectGetsProperlyEncoded(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><root property="test" />');

        $class = new class {
            public function test(): object {
                return new class {
                    public function asString(): string {
                        return 'Text with <tag> and & included';
                    }
                };
            }
        };

        $renderer = new ViewModelRenderer();
        $renderer->render($dom->documentElement, $class);

        $expected = new DOMDocument();
        $expected->loadXML('<?xml version="1.0"?><root property="test">Text with &lt;tag&gt; and &amp; included</root>');

        $this->assertResultMatches($expected->documentElement, $dom->documentElement);
    }

    public function testReturningUnsupportedTypeViaAsStringThrowsException(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><root property="test" />');

        $class = new class {
            public function test(): object {
                return new class {
                    public function asString() {
                        return \STDIN;
                    }
                };
            }
        };

        $renderer = new ViewModelRenderer();

        $this->expectException(ViewModelRendererException::class);
        $renderer->render($dom->documentElement, $class);
    }

    public function testTryingToCallTypeOfOnNoneObjectThrowsException(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><root property="test" typeof="a" />');

        $class = new class {
            public function test(): array {
                return ['i-am-not-an-object'];
            }
        };

        $renderer = new ViewModelRenderer();

        $this->expectException(ViewModelRendererException::class);
        $renderer->render($dom->documentElement, $class);
    }

    public function testReturningNonStringValueFromTypeOfThrowsException(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><root property="test" typeof="a" />');

        $class = new class {
            public function test(): object {
                return new class {
                    public function typeOf() {
                        return \STDIN;
                    }
                };
            }
        };

        $renderer = new ViewModelRenderer();

        $this->expectException(ViewModelRendererException::class);
        $renderer->render($dom->documentElement, $class);
    }

    public function testTwoNodesOnSameLavelWithSamePropertyGetProcessedInNonArrayMode(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><root><a property="test" /><b property="test" /></root>');

        $class             = new class {
            private $count = 0;
            public function test(): object {
                $this->count++;

                return new class($this->count) {
                    private $count;
                    public function __construct(int $count) {
                        $this->count = $count;
                    }
                    public function asString(): string {
                        return (string)$this->count;
                    }
                };
            }
        };

        $renderer = new ViewModelRenderer();
        $renderer->render($dom->documentElement, $class);

        $expected = new DOMDocument();
        $expected->loadXML('<?xml version="1.0"?><root><a property="test">1</a><b property="test">2</b></root>');

        $this->assertResultMatches($expected->documentElement, $dom->documentElement);
    }

    public function testUnsuportedVocabGetsIgnored(): void {
        $markup = '<?xml version="1.0"?><root vocab="foo" property="bar" />';

        $expected = new DOMDocument();
        $expected->loadXML($markup);

        $dom = new DOMDocument();
        $dom->loadXML($markup);

        $renderer = new ViewModelRenderer();
        $renderer->render(
            $dom->documentElement,
            new class {
                public function vocab(): string {
                    return 'other';
                }
            }
        );

        $this->assertResultMatches($expected->documentElement, $dom->documentElement);
    }

    public function testNonStringResponseForVocabThrowsException(): void {
        $markup = '<?xml version="1.0"?><root vocab="foo" property="bar" />';

        $dom = new DOMDocument();
        $dom->loadXML($markup);

        $renderer = new ViewModelRenderer();

        $this->expectException(ViewModelRendererException::class);
        $renderer->render(
            $dom->documentElement,
            new class {
                public function vocab(): bool {
                    return false;
                }
            }
        );
    }

    public function testAttemptToUseNonObjectOrIterableModelForTypeOfThrowsException(): void {
        $markup = '<?xml version="1.0"?><root typeof="foo" property="bar" />';

        $dom = new DOMDocument();
        $dom->loadXML($markup);

        $renderer = new ViewModelRenderer();

        $this->expectException(ViewModelRendererException::class);
        $renderer->render(
            $dom->documentElement,
            new class {
                public function bar(): string {
                    return 'will-not-work';
                }
            }
        );
    }

    public function testUnsupportedModelTypeAsListItemThrowsException(): void {
        $markup = '<?xml version="1.0"?><root><child property="foo" /></root>';

        $dom = new DOMDocument();
        $dom->loadXML($markup);

        $renderer = new ViewModelRenderer();

        $this->expectException(ViewModelRendererException::class);
        $renderer->render(
            $dom->documentElement,
            new class {
                public function foo(): array {
                    return [false];
                }
            }
        );
    }

    public function testMagicToStringMethodGetsCalledOnModelForText(): void {
        $markup = '<?xml version="1.0"?><root property="foo" />';

        $dom = new DOMDocument();
        $dom->loadXML($markup);

        $renderer = new ViewModelRenderer();

        $renderer->render(
            $dom->documentElement,
            new class {
                public function foo(): object {
                    return new class {
                        public function __toString(): string {
                            return 'toString-Text';
                        }
                    };
                }
            }
        );

        $this->assertEquals('toString-Text', $dom->documentElement->textContent);
    }

    public function testResourceResolvingHonorsPrefixSetup(): void {
        $markup = '<?xml version="1.0"?><root prefix="f: test" resource="f:foo" property="bar" />';

        $dom = new DOMDocument();
        $dom->loadXML($markup);

        $renderer = new ViewModelRenderer();

        $renderer->render(
            $dom->documentElement,
            new class {
                public function test(): object {
                    return new class {
                        public function foo(): object {
                            return new class {
                                public function bar(): string {
                                    return 'resource-resolved-via-prefix';
                                }
                            };
                        }
                    };
                }
            }
        );

        $this->assertEquals('resource-resolved-via-prefix', $dom->documentElement->textContent);
    }

    public function testResourceResovlingUsingUndefinedPrefixThrowsException(): void {
        $markup = '<?xml version="1.0"?><root prefix="f: external:other" resource="f:foo" />';

        $dom = new DOMDocument();
        $dom->loadXML($markup);

        $renderer = new ViewModelRenderer();

        $this->expectException(ViewModelRendererException::class);
        $renderer->render(
            $dom->documentElement,
            new class {}
        );
    }

    #[DataProvider('vocabAccess')]
    public function testVocabCanBeResolvedViaAllSupportedAccessTypes(object $model, string $expected): void {
        $markup = '<?xml version="1.0"?><root vocab="foo" property="test" />';

        $dom = new DOMDocument();
        $dom->loadXML($markup);

        $renderer = new ViewModelRenderer();
        $renderer->render(
            $dom->documentElement,
            $model
        );

        $this->assertEquals($expected, $dom->documentElement->textContent);
    }

    #[DataProvider('vocabAccess')]
    public function testVocabCanBeResolvedViaAllSupportedAccessTypesButIgnoresNonMatching(object $model): void {
        $markup = '<?xml version="1.0"?><root vocab="bar" property="test">original</root>';

        $dom = new DOMDocument();
        $dom->loadXML($markup);

        $renderer = new ViewModelRenderer();
        $renderer->render(
            $dom->documentElement,
            $model
        );

        $this->assertEquals('original', $dom->documentElement->textContent);
    }

    public static function vocabAccess():array {
        return [
            'method' => [new class {
                public function vocab(): string {
                    return 'foo';
                }

                public function test(): string {
                    return 'vocab-based';
                }
            }, 'vocab-based'],
            'method-__call' => [new class {
                public function __call(string $key, $params): string {
                    if ($key === 'vocab') return 'foo';

                    throw new InvalidArgumentException();
                }

                public function test(): string {
                    return 'vocab-based';
                }
            }, 'vocab-based'],
            'method-get' => [new class {
                public function getVocab(): string {
                    return 'foo';
                }

                public function test(): string {
                    return 'vocab-based';
                }
            }, 'vocab-based'],
            'property-__get' => [new class {
                public function __get(string $key): string {
                    if ($key === 'vocab') return 'foo';

                    throw new InvalidArgumentException();
                }

                public function test(): string {
                    return 'vocab-based';
                }
            }, 'vocab-based'],
            'property' => [new class {
                public string $vocab = 'foo';

                public function test(): string {
                    return 'vocab-based';
                }
            }, 'vocab-based'],
            'none' => [new class {
                public function test(): string {
                    return 'original';
                }
            }, 'original']
        ];
    }

    public function testRemovedSubTreeNodeGetsIgnored(): void {
        $markup = <<<EOF
            <?xml version="1.0"?>
            <root property="foo">
                <child property="bar" typeof="a">org</child>
                <child property="bar" typeof="b">removed</child>
                <other property="survive" />
            </root>
            EOF;
        $dom = new DOMDocument();
        $dom->loadXML($markup);

        $renderer = new ViewModelRenderer();
        $renderer->render(
            $dom->documentElement,
            new class {
                public function foo(): object {
                    return new class {

                        public function bar(): object {
                            return new class {
                                public function typeOf(): string {
                                    return 'a';
                                }

                                public function asString(): string {
                                    return 'new-text-value';
                                }
                            };
                        }

                        public function survive(): string {
                            return 'other-text';
                        }
                    };
                }
            }
        );

        $this->assertEquals('other-text', $dom->documentElement->lastElementChild->textContent);
    }

    public function testViewModelReturningPlainDocumentGetsRenderedAsExcepted(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<root property="document" />');

        $renderer = new ViewModelRenderer();
        $renderer->render(
            $dom->documentElement,
            new class {
                public function document(): Document {
                    return Document::fromString('<child><sub /></child>');
                }
            }
        );

        $expected = new DOMDocument();
        $expected->loadXML('<root property="document"><child><sub /></child></root>');

        $this->assertResultMatches($expected->documentElement, $dom->documentElement);
    }

    public function testViewModelReturningTempladoWrappedDocumentGetsRenderedAsExcepted(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<root property="document" />');

        $renderer = new ViewModelRenderer();
        $renderer->render(
            $dom->documentElement,
            new class {
                public function document(): Document {
                    return Document::fromString(
                        '<t:d xmlns:t="https://templado.io/document/1.0"><c1 /><c2 /><c3 /></t:d>');
                }
            }
        );

        $expected = new DOMDocument();
        $expected->loadXML('<root property="document"><c1 /><c2 /><c3 /></root>');

        $this->assertResultMatches($expected->documentElement, $dom->documentElement);
    }

    #[DataProvider('attributeViewModelProvider')]
    public function testAttributesGetAppliedFromViewModelProperties(object $model, string $value): void {
        $dom = new DOMDocument();
        $dom->loadXML('<root property="document" attr="default" />');

        $renderer = new ViewModelRenderer();
        $renderer->render(
            $dom->documentElement,
            $model
        );

        $expected = new DOMDocument();
        $expected->loadXML('<root property="document" attr="'.$value.'" />');

        $this->assertResultMatches($expected->documentElement, $dom->documentElement);
    }

    public static function attributeViewModelProvider(): array {
        return [
            'property' => [
                new class {
                    public function document(): object {
                        return new class {
                            public string $attr='changed';
                        };
                    }
                },
                'changed'
            ],
            'get' => [
                new class {
                    public function document(): object {
                        return new class {
                            public function __get(string $key): string|true {
                                return $key === 'attr' ? 'changed' : true;
                            }
                        };
                    }
                },
                'changed'
            ],
            'none' => [
                new class {
                    public function document(): object {
                        return new class {
                        };
                    }
                },
                'default'
            ]
        ];
    }
}
