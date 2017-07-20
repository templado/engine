<?php declare(strict_types = 1);
namespace Templado\Engine;

class CSRFProtection {

    /** @var string */
    private $fieldName;

    /** @var string */
    private $tokenValue;

    /**
     * @param string $fieldName
     * @param string $tokenValue
     */
    public function __construct(string $fieldName, string $tokenValue) {
        $this->fieldName = $fieldName;
        $this->tokenValue = $tokenValue;
    }

    /**
     * @return string
     */
    public function getFieldName(): string {
        return $this->fieldName;
    }

    /**
     * @return string
     */
    public function getTokenValue(): string {
        return $this->tokenValue;
    }

}
