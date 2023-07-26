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

use function libxml_clear_errors;
use function libxml_get_last_error;
use function libxml_use_internal_errors;
use function sprintf;
use function trim;
use DOMDocument;
use DOMNode;
use DOMNodeList;
use DOMXPath;

class XPathSelector implements Selector {
    /** @var string */
    private $queryString;

    /** @var array<string, string> */
    private $prefixMap = [];

    public function __construct(string $query) {
        $this->queryString = $query;
    }

    public function registerPrefix(string $prefix, string $uri): void {
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
                sprintf(
                    '%s: "%s"',
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
        $dom = $node instanceof DOMDocument ? $node : $node->ownerDocument;
        assert($dom instanceof DOMDocument);

        $xp = new DOMXPath($dom);
        $xp->registerPhpFunctions();

        if (empty($this->prefixMap) || !isset($this->prefixMap['html'])) {
            $this->prefixMap['html'] = 'http://www.w3.org/1999/xhtml';
        }

        foreach ($this->prefixMap as $prefix => $uri) {
            $xp->registerNamespace($prefix, $uri);
        }

        return $xp;
    }

    private function toggleErrorHandling(bool $mode): bool {
        $backup = libxml_use_internal_errors($mode);
        libxml_clear_errors();

        return $backup;
    }
}
