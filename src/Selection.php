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

use function count;
use ArrayIterator;
use DOMNode;
use DOMNodeList;
use IteratorAggregate;

/** @template-implements IteratorAggregate<int,DOMNode> */
class Selection implements IteratorAggregate {
    /** @var DOMNode[] */
    private array $list = [];

    public function __construct(DOMNodeList $nodeList) {
        foreach ($nodeList as $node) {
            $this->list[] = $node;
        }
    }

    public function isEmpty(): bool {
        return count($this->list) === 0;
    }

    public function getIterator(): ArrayIterator {
        return new ArrayIterator($this->list);
    }
}
