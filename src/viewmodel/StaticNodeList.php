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

use ArrayIterator;
use Countable;
use DOMNamedNodeMap;
use DOMNode;
use DOMNodeList;
use IteratorAggregate;
use Traversable;

/** @template-implements IteratorAggregate<int,DOMNode> */
class StaticNodeList implements Countable, IteratorAggregate {
    /** @var DOMNode[] */
    private array $nodes;

    public static function fromNodeList(DOMNodeList $list): self {
        return new self(...$list);
    }

    public static function fromNamedNodeMap(DOMNamedNodeMap $attributes) {
        return new self(...$attributes);
    }

    public function __construct(DOMNode ...$node) {
        $this->nodes = $node;
    }

    public function getIterator(): Traversable {
        return new ArrayIterator($this->nodes);
    }

    public function count(): int {
        return count($this->nodes);
    }
}
