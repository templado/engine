<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;

class AssetLoader {

    public function load(FileName $fileName): Asset {
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
                return $this->loadAsAsset($fileName);
        }

        throw new AssetLoaderException(
            sprintf('Unsupported mime-type "%s"', $mimeType)
        );
    }

    private function loadAsText(FileName $fileName): TextAsset {
        return new TextAsset(
            $fileName->getName(),
            (new DOMDocument())->createTextNode(file_get_contents($fileName->asString()))
        );
    }

    private function loadAsAsset(FileName $fileName): SimpleAsset {
        $dom = $this->loadFile($fileName);

        if ($this->isAssetDocument($dom)) {
            return $this->parseAsAsset($dom);
        }

        if ($this->isHtmlDocument($dom)) {
            return $this->parseAsHTML($dom);
        }

        throw new AssetLoaderException('Document does not seem to be a valid HtmlAsset or (X)HTML file.');
    }

    /**
     * @param FileName $fileName
     *
     * @return DOMDocument
     * @throws AssetLoaderException
     */
    private function loadFile(FileName $fileName): DOMDocument {
        libxml_use_internal_errors(true);
        libxml_clear_errors();
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $tmp = $dom->load($fileName->asString());
        if (!$tmp || libxml_get_last_error()) {
            $error = libxml_get_errors()[0];
            throw new AssetLoaderException(
                sprintf("Loading file '%s' failed: %s (line %d)",
                    $fileName->asString(),
                    trim($error->message),
                    $error->line
                )
            );
        }

        return $dom;
    }

    /**
     * @param DOMDocument $dom
     *
     * @return bool
     */
    private function isAssetDocument(DOMDocument $dom): bool {
        $root = $dom->documentElement;

        return (
            $root->namespaceURI === 'https://templado.io/assets/1.0' &&
            $root->localName === 'asset' &&
            $root->hasChildNodes()
        );
    }

    /**
     * @param DOMDocument $dom
     *
     * @return bool
     */
    private function isHtmlDocument(DOMDocument $dom): bool {
        $root = $dom->documentElement;

        return (
            $root->namespaceURI === 'http://www.w3.org/1999/xhtml' ||
            (string)$root->namespaceURI === ''
        );
    }

    /**
     * @param FileName $fileName
     *
     * @throws AssetLoaderException
     */
    private function ensureFileExists(FileName $fileName) {
        if (!$fileName->exists()) {
            throw new AssetLoaderException(
                sprintf('File "%s" not found.', $fileName->asString())
            );
        }
    }

    /**
     * @param FileName $fileName
     *
     * @throws AssetLoaderException
     */
    private function ensureIsReadableFile(FileName $fileName) {
        if (!$fileName->isFile()) {
            throw new AssetLoaderException(
                sprintf('File "%s" not a file.', $fileName->asString())
            );
        }
        if (!$fileName->isReadable()) {
            throw new AssetLoaderException(
                sprintf('File "%s" can not be read.', $fileName->asString())
            );
        }
    }

    private function parseAsAsset(DOMDocument $dom): SimpleAsset {
        $fragment = $dom->createDocumentFragment();
        foreach ($dom->documentElement->childNodes as $child) {
            $fragment->appendChild($child);
        }

        return new SimpleAsset($dom->documentElement->getAttribute('id'), $fragment);
    }

    private function parseAsHTML(DOMDocument $dom): SimpleAsset {
        $id = $dom->documentElement->getAttribute('id');
        if ($id === '') {
            $id = pathinfo($dom->documentURI, PATHINFO_FILENAME);
        }

        return new SimpleAsset($id, $dom->documentElement);
    }

}
