<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use Templado\Engine\Example\ViewModel;
use Templado\Engine\ResourceModel\ResourceViewModel;
use Templado\Engine\ResourceModel\ResourceCallViewModel;

/**
 * @covers \Templado\Engine\ViewModelRenderer
 * @uses \Templado\Engine\SnapshotDOMNodelist
 * @uses \Templado\Engine\SnapshotAttributeList
 *
 */
class ViewModelRendererTest extends TestCase {

    public function testViewModelGetsAppliedAsExcepted() {
        $viewModel = new ViewModel();
        $dom       = new DOMDocument();
        $dom->load(__DIR__ . '/../_data/viewmodel/source.html');

        $renderer = new ViewModelRenderer();
        $renderer->render($dom->documentElement, $viewModel);

        $expected = new DOMDocument();
        $expected->load(__DIR__ . '/../_data/viewmodel/expected.html');


        $this->assertEqualXMLStructure(
            $expected->documentElement,
            $dom->documentElement,
            true
        );
    }

    public function testIteratorReturnValueGetsApplied() {
        $dom       = new DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><html><body><p property="a" /></body></html>');

        $viewModel = new class {
            public function a() {
                return new \ArrayIterator(['a','b']);
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

    public function testMagicCallMethodGetsCalledWhenDefinedAndNoExplicitMethodFits() {
        $dom       = new DOMDocument();
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

    public function testMagicCallMethodGetsCalledForAttributesWhenDefinedAndNoExplicitMethodFits() {
        $dom       = new DOMDocument();
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

    public function testDashesInAttributeNamesGetTranslatedToCamelCase() {
        $dom       = new DOMDocument();
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

    public function testViewModelMethodReturningBooleanTrueKeepsNode() {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root property="test" />');


        $renderer = new ViewModelRenderer();
        $renderer->render($dom->documentElement,new class {
            public function test() {
                return true;
        }});

        $expected = new DOMDocument();
        $expected->loadXML('<?xml version="1.0" ?><root property="test" />');

        $this->assertEquals(
            $expected->documentElement,
            $dom->documentElement
        );

    }

    public function testUseOfNonObjectModelThrowsException() {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root property="test" />');

        $renderer = new ViewModelRenderer();

        $this->expectException(ViewModelRendererException::class);
        $renderer->render($dom->documentElement, 'Non-Object');
    }

    public function testNoMethodForPropertyThrowsException() {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root property="test" />');

        $renderer = new ViewModelRenderer();

        $this->expectException(ViewModelRendererException::class);
        $renderer->render($dom->documentElement, new class {
        });
    }

    public function testUnsupportedVariableTypeThrowsException() {
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

    public function testUnsupportedVariableTypeForAttributeThrowsException() {
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

    public function testMissingTypeOfMethodOnConditionContextThrowsException() {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root property="test" typeof="A" />');

        $renderer = new ViewModelRenderer();

        $this->expectException(ViewModelRendererException::class);
        $renderer->render($dom->documentElement, new class {
            public function getTest() {
                return new class {};
            }
        });
    }

    public function testNoExsitingTypeForRequestedTypeOfMethodOnConditionContextThrowsException() {
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

    public function testMultipleElementsForPropertyOnRootNodeThrowsException() {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root property="test" />');

        $renderer = new ViewModelRenderer();

        $this->expectException(ViewModelRendererException::class);
        $renderer->render($dom->documentElement, new class {
            public function getTest() {
                return ['a','b'];
            }
        });
    }

    public function testEmptyArrayForPropertyOnRootNodeThrowsException() {
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

    public function testTypeOfSelectionPicksCorrectContextInObjectUse() {

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

        $this->assertEqualXMLStructure(
            $expected->documentElement,
            $source->documentElement,
            true
        );

    }

    public function testTypeOfSelectionPicksCorrectContextInLists() {

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

        $this->assertEqualXMLStructure(
            $expected->documentElement,
            $source->documentElement,
            true
        );

    }

    public function testTypeOfSelectionPicksCorrectContextInCombinedObjectListUse() {

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

        $this->assertEqualXMLStructure(
            $expected->documentElement,
            $source->documentElement,
            true
        );

    }

    public function testTypeOfSelectionPicksCorrectContextInComplexScenario() {

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
                                    return 'B.3';
                                }

                                public function asString() {
                                    return 'new text two-B.3';
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

        $this->assertEqualXMLStructure(
            $expected->documentElement,
            $source->documentElement,
            true
        );

    }

    public function testViewModelIteratorWithoutCountableThrowsException() {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><child property="test" /></root>');

        $renderer = new ViewModelRenderer();
        $this->expectException(ViewModelRendererException::class);
        $renderer->render($dom->documentElement, new class {
            public function getTest() {
                return new class implements \Iterator {
                    private $valid = true;

                    public function current() {
                        return 'a';
                    }

                    public function next() {
                        $this->valid = false;
                    }

                    public function key() {
                        return 0;
                    }

                    public function valid() {
                        return $this->valid;
                    }

                    public function rewind() {
                        $this->valid = true;
                    }
                };
            }
        });

    }

    public function testResourceViewModelGetsAppliedAsExcepted() {
        $viewModel = new ResourceViewModel();
        $dom       = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->load(__DIR__ . '/../_data/viewmodel/resource/source.html');

        $renderer = new ViewModelRenderer();
        $renderer->render($dom->documentElement, $viewModel);

        $expected = new DOMDocument();
        $expected->preserveWhiteSpace = false;
        $expected->load(__DIR__ . '/../_data/viewmodel/resource/expected.html');

        $this->assertXmlStringEqualsXmlString($expected, $dom);
    }

    public function testResourceViewModelWithMagicCallGetsAppliedAsExcepted() {
        $viewModel = new ResourceCallViewModel();
        $dom       = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->load(__DIR__ . '/../_data/viewmodel/resource/source.html');

        $renderer = new ViewModelRenderer();
        $renderer->render($dom->documentElement, $viewModel);

        $expected = new DOMDocument();
        $expected->preserveWhiteSpace = false;
        $expected->load(__DIR__ . '/../_data/viewmodel/resource/expected.html');

        $this->assertXmlStringEqualsXmlString($expected, $dom);
    }

    public function testUsingAResourceWithNoMethodToRequestItThrowsException() {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><root resource="foo" />');

        $renderer = new ViewModelRenderer();

        $this->expectException(ViewModelRendererException::class);
        $renderer->render($dom->documentElement, new \stdClass());

    }
}
