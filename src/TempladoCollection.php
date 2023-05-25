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
use IteratorAggregate;

/** @template-implements IteratorAggregate<int,Templado> */
class TempladoCollection implements IteratorAggregate {

    /** @psalm-param list<int,Templado> */
    private array $documents = [];
    public function getIterator(): ArrayIterator {
        return new ArrayIterator($this->documents);
    }
}
