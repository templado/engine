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

use DOMNode;

interface Transformation {
    public function selector(): Selector;

    /** @psalm-suppress MissingReturnType Adding void here would qualify as a BC break :-/ */
    public function apply(DOMNode $context);
}
