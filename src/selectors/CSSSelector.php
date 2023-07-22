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

use TheSeer\CSS2XPath\Translator;

final class CSSSelector extends XPathSelector {
    public function __construct(string $query) {
        parent::__construct(
            (new Translator())->translate($query)
        );
    }
}
