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

use LibXMLError;
use function sprintf;
use function trim;

final class ParsingException extends DocumentException {
    /** @psalm-param list<LibXMLError> */
    private readonly array $errors;

    public function __construct(LibXMLError ...$errors) {
        $this->errors = $errors;
        parent::__construct('Error(s) during parse');
    }

    public function errors(): array {
        return $this->errors;
    }

    public function __toString(): string {
        $msg = $this->getMessage() . "\n";

        foreach($this->errors as $error) {
            $msg .= sprintf(
                "[line: %d / column: %d] %s\n",
                $error->line,
                $error->column,
                $error->message
            );
        }

        $msg .= $this->getTraceAsString();

        return $msg;
    }
}
