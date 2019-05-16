<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;

class SnippetLoader {
    public function load(FileName $fileName): Snippet {
        $this->ensureFileExists($fileName);
        $this->ensureIsReadableFile($fileName);

        $mimeType = $fileName->getMimeType();

        switch ($mimeType) {
            case 'text/x-php':
            case 'text/plain':
                return $this->loadAsText($fileName);

            case 'application/xml':
            case 'text/xml':
            case 'text/html':
                return $this->loadAsSnippet($fileName);
        }

        throw new SnippetLoaderException(
            \sprintf('Unsupported mime-type "%s"', $mimeType)
        );
    }

    private function loadAsText(FileName $fileName): TextSnippet {
        return new TextSnippet(
            $fileName->getName(),
            (new DOMDocument())->createTextNode(\file_get_contents($fileName->asString()))
        );
    }

    /**
     * @throws SnippetLoaderException
     */
    private function loadAsSnippet(FileName $fileName): SimpleSnippet {
        $dom = $this->loadFile($fileName);

        if ($this->isSnippetDocument($dom)) {
            return $this->parseAsSnippet($dom);
        }

        if ($this->isHtmlDocument($dom)) {
            return $this->parseAsHTML($dom);
        }

        throw new SnippetLoaderException('Document does not seem to be a valid HtmlSnippet or (X)HTML file.');
    }

    /**
     * @throws SnippetLoaderException
     */
    private function loadFile(FileName $fileName): DOMDocument {
        \libxml_use_internal_errors(true);
        \libxml_clear_errors();
        $dom                     = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $tmp                     = $dom->load($fileName->asString());

        if (!$tmp || \libxml_get_last_error()) {
            $error = \libxml_get_errors()[0];

            throw new SnippetLoaderException(
                \sprintf(
                    "Loading file '%s' failed: %s (line %d)",
                    $fileName->asString(),
                    \trim($error->message),
                    $error->line
                )
            );
        }

        return $dom;
    }

    private function isSnippetDocument(DOMDocument $dom): bool {
        $root = $dom->documentElement;

        return (
            $root->namespaceURI === 'https://templado.io/snippets/1.0' &&
            $root->localName === 'snippet' &&
            $root->hasChildNodes()
        );
    }

    private function isHtmlDocument(DOMDocument $dom): bool {
        $root = $dom->documentElement;

        return (
            $root->namespaceURI === 'http://www.w3.org/1999/xhtml' ||
            (string)$root->namespaceURI === ''
        );
    }

    /**
     * @throws SnippetLoaderException
     */
    private function ensureFileExists(FileName $fileName): void {
        if (!$fileName->exists()) {
            throw new SnippetLoaderException(
                \sprintf('File "%s" not found.', $fileName->asString())
            );
        }
    }

    /**
     * @throws SnippetLoaderException
     */
    private function ensureIsReadableFile(FileName $fileName): void {
        if (!$fileName->isFile()) {
            throw new SnippetLoaderException(
                \sprintf('File "%s" not a file.', $fileName->asString())
            );
        }

        if (!$fileName->isReadable()) {
            throw new SnippetLoaderException(
                \sprintf('File "%s" can not be read.', $fileName->asString())
            );
        }
    }

    private function parseAsSnippet(DOMDocument $dom): SimpleSnippet {
        $fragment = $dom->createDocumentFragment();

        foreach ($dom->documentElement->childNodes as $child) {
            $fragment->appendChild($child);
        }

        return new SimpleSnippet($dom->documentElement->getAttribute('id'), $fragment);
    }

    private function parseAsHTML(DOMDocument $dom): SimpleSnippet {
        $id = $dom->documentElement->getAttribute('id');

        if ($id === '') {
            $id = \pathinfo($dom->documentURI, \PATHINFO_FILENAME);
        }

        return new SimpleSnippet($id, $dom->documentElement);
    }
}
