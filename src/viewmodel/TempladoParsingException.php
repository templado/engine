<?php declare(strict_types = 1);
namespace Templado\Engine;

use Exception;
use LibXMLError;

final class TempladoParsingException extends Exception {

    /** @psalm-param LibXMLError[] */
    private readonly array $errors;

    public function __construct(LibXMLError ...$errors) {
        $this->errors = $errors;
        parent::__construct('Error(s) during parse');
    }

    public function errors(): array {
        return $this->errors;
    }

}
