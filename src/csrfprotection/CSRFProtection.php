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

class CSRFProtection {
    private readonly string $fieldName;

    private readonly string $tokenValue;

    public function __construct(string $fieldName, string $tokenValue) {
        $this->fieldName  = $fieldName;
        $this->tokenValue = $tokenValue;
    }

    public function fieldName(): string {
        return $this->fieldName;
    }

    public function tokenValue(): string {
        return $this->tokenValue;
    }
}
