<?php declare(strict_types = 1);
namespace TheSeer\Templado;

use DOMNode;
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
        return new Selection(
            $this->getXPath($context)->query(
                $this->queryString,
                $context
            )
        );
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

}
