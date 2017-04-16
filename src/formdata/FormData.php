<?php declare(strict_types = 1);
namespace Templado\Engine;

class FormData {

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var array
     */
    private $values = [];

    /**
     * FormData constructor.
     *
     * @param string $identifier
     * @param array  $values
     *
     * @throws FormDataException
     */
    public function __construct(string $identifier, array $values) {
        $this->identifier = $identifier;
        $this->values     = $this->initValuesFromArray($values);
    }

    public function getIdentifier(): string {
        return $this->identifier;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasKey(string $key): bool {
        return array_key_exists($key, $this->values);
    }

    /**
     * @param string $key
     *
     * @return mixed
     * @throws FormDataException
     */
    public function getValue(string $key) {
        if (!$this->hasKey($key)) {
            throw new FormDataException(sprintf('No such key: %s', $key));
        }

        return $this->values[$key];
    }

    /**
     * @param array $values
     * @param bool  $recursion
     *
     * @return array
     * @throws FormDataException
     */
    private function initValuesFromArray(array $values, bool $recursion = false): array {
        $result = [];
        foreach($values as $key => $value) {
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
