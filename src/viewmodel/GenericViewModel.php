<?php declare(strict_types = 1);
namespace Templado\Engine;

class GenericViewModel {
    /**
     * @var array
     */
    private $properties;

    /**
     * @param array $properties
     *
     */
    public function __construct(array $properties) {
        $this->properties = $properties;
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments) {

        if (!isset($this->properties[$name])) {
            return $arguments[0] ?? null;
        }

        return $this->properties[$name];
    }

}
