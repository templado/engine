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

use function array_key_exists;
use function gettype;
use function is_array;
use function is_string;
use function sprintf;

class FormData {
    /** @var string */
    private $identifier;

    /** @var array */
    private $values;

    /**
     * @throws FormDataException
     */
    public function __construct(string $identifier, array $values) {
        $this->identifier = $identifier;
        $this->values     = $this->initValuesFromArray($values);
    }

    public function getIdentifier(): string {
        return $this->identifier;
    }

    public function hasKey(string $key): bool {
        return array_key_exists($key, $this->values);
    }

    /**
     * @throws FormDataException
     */
    public function getValue(string $key): string {
        if (!$this->hasKey($key)) {
            throw new FormDataException(sprintf('No such key: %s', $key));
        }

        /** @psalm-var string */
        return $this->values[$key];
    }

    /**
     * @throws FormDataException
     */
    private function initValuesFromArray(array $values, bool $recursion = false): array {
        $result = [];

        /** @psalm-suppress MixedAssignment */
        foreach ($values as $key => $value) {
            if (is_string($value)) {
                $result[$key] = $value;

                continue;
            }

            if ($recursion === false && is_array($value)) {
                $result[$key] = $this->initValuesFromArray($value, true);

                continue;
            }

            throw new FormDataException(
                sprintf('Data type "%s" in key "%s" not supported', gettype($value), $key)
            );
        }

        return $result;
    }
}
