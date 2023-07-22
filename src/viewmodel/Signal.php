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

abstract readonly class Signal {
    public static function ignore(): Ignore {
        return new Ignore;
    }

    public static function remove(): Remove {
        return new Remove;
    }

    public function isIgnore(): bool {
        return false;
    }

    public function isRemove(): bool {
        return false;
    }
}
