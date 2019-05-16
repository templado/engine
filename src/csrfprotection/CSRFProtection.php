<?php declare(strict_types = 1);
namespace Templado\Engine;

class CSRFProtection {

    /** @var string */
    private $fieldName;

    /** @var string */
    private $tokenValue;

    public function __construct(string $fieldName, string $tokenValue) {
        $this->fieldName  = $fieldName;
        $this->tokenValue = $tokenValue;
    }

    public function getFieldName(): string {
        return $this->fieldName;
    }

    public function getTokenValue(): string {
        return $this->tokenValue;
    }
}
