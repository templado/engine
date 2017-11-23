<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMNode;
use DOMNodeList;
use DOMXPath;

class XPathSelector implements Selector {

    /**
     * @var string
     */
    private $queryString;

    /**
     * @var array
     */
    private $prefixMap = [];

    /**
     * @param string $query
     */
    public function __construct(string $query) {
        $this->queryString = $query;
    }

    /**
     * @param string $prefix
     * @param string $uri
     */
    public function registerPrefix(string $prefix, string $uri) {
        $this->prefixMap[$prefix] = $uri;
    }

    public function select(DOMNode $context): Selection {
        $backup = $this->toggleErrorHandling(true);
        $result = $this->getXPath($context)->query(
            $this->queryString,
            $context
        );
        if (!$result instanceof DOMNodeList) {
            $error = libxml_get_last_error();
            $this->toggleErrorHandling($backup);

            throw new XPathSelectorException(
                sprintf('%s: "%s"',
                    trim($error->message),
                    $this->queryString
                ),
                $error->code
            );
        }
        $this->toggleErrorHandling($backup);

        return new Selection($result);
    }

    private function getXPath(DOMNode $node): DOMXPath {
        $xp = new DOMXPath($node->ownerDocument);
        $xp->registerPhpFunctions();

        if (empty($this->prefixMap) || isset($this->prefixMap['html'])) {
            $this->prefixMap['html'] = 'http://www.w3.org/1999/xhtml';
        }

        foreach($this->prefixMap as $prefix => $uri) {
            $xp->registerNamespace($prefix, $uri);
        }

        return $xp;
    }

    /**
     * @return bool
     */
    private function toggleErrorHandling(bool $mode): bool {
        $backup = libxml_use_internal_errors($mode);
        libxml_clear_errors();

        return $backup;
    }

}
