<?php declare(strict_types = 1);
namespace Templado\Engine;

require __DIR__ . '/../src/autoload.php';

try {
    $page = Templado::loadHtmlFile(
        new FileName(__DIR__ . '/html/basic.xhtml')
    );

    $snippetListCollection = new SnippetListCollection();

    $sample   = new \DOMDocument();
    $fragment = $sample->createDocumentFragment();
    $fragment->appendXML('This is a first test: <span id="nested" />');

    $snippetListCollection->addAsset(
        new SimpleSnippet('test', $fragment)
    );

    $snippetListCollection->addAsset(
        new SimpleSnippet('nested', new \DOMText('Hello world'))
    );
    $page->applySnippets(
        $snippetListCollection
    );

    echo $page->asString();

} catch (TempladoException $e) {
    foreach($e->getErrorList() as $error) {
        echo (string)$error;
    }
}
