<?php declare(strict_types = 1);
namespace Templado\Engine;

class JsonMapper {

    /**
     * @param string $json
     * @param int    $options
     *
     * @return GenericViewModel
     * @throws JsonMapperException
     */
    public function fromString(string $json, int $options = 0): GenericViewModel {
        $data = json_decode($json, false, 512, $options);
        if (json_last_error() !== 0) {
            throw new JsonMapperException(json_last_error_msg(), json_last_error());
        }

        return $this->parseObject($data);
    }

    /**
     * @param \StdClass $data
     *
     * @return GenericViewModel
     */
    private function parseObject(\StdClass $data): GenericViewModel {
        $properties = [];
        foreach(get_object_vars($data) as $name => $value) {
            $properties[$name] = $this->parseValue($value);
        }

        return new GenericViewModel($properties);
    }

    /**
     * @param array $value
     *
     * @return array
     */
    private function parseArray(array $value): array {
        $result = [];
        foreach($value as $item) {
            $result[] = $this->parseValue($item);
        }

        return $result;
    }

    private function parseValue($value) {
        switch (true) {
            case is_scalar($value): {
                return $value;
            }
            case is_object($value): {
                return $this->parseObject($value);
            }
            case is_array($value): {
                return $this->parseArray($value);
            }
        }
    }

}
