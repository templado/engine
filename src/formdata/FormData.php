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

use const PREG_SPLIT_NO_EMPTY;
use function array_key_exists;
use function gettype;
use function implode;
use function is_array;
use function is_string;
use function preg_split;
use function sprintf;

final class FormData {
    private readonly string $identifier;

    private array $values = [];

    /**
     * @throws FormDataException
     */
    public function __construct(string $identifier, array $values) {
        $this->identifier = $identifier;
        $this->flattenArray($values);
    }

    public function identifier(): string {
        return $this->identifier;
    }

    public function has(string $key): bool {
        return array_key_exists($this->translateKey($key), $this->values);
    }

    /**
     * @throws FormDataException
     */
    public function value(string $key): string {
        $lookupKey = $this->translateKey($key);

        if (!$this->has($lookupKey)) {
            throw new FormDataException(sprintf('No such key: %s', $key));
        }

        return $this->values[$lookupKey];
    }

    private function flattenArray(array $values, array $keyPrefixes = []): void {
        foreach ($values as $key => $value) {
            if (is_string($value)) {
                $this->values[implode('|', [...$keyPrefixes, $key])] = $value;

                continue;
            }

            if (!is_array($value)) {
                throw new FormDataException(
                    sprintf('Data type "%s" in key "%s" not supported', gettype($value), $key)
                );
            }

            $this->flattenArray($value, [...$keyPrefixes, $key]);
        }
    }

    private function translateKey(string $key): string {
        return implode('|', preg_split('/\[|\]/', $key, flags: PREG_SPLIT_NO_EMPTY));
    }
}
