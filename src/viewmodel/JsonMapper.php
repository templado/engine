<?php declare(strict_types = 1);
namespace Templado\Engine;

class JsonMapper {

    /**
     * @param string $json
     * @param int    $options
     *
     * @return GenericViewModel
     */
    public function fromString(string $json, int $options = 0) {
        $data = json_decode($json, false, 512, $options);
        if (json_last_error() !== 0) {
            throw new \RuntimeException(json_last_error_msg(), json_last_error());
        }

        return $this->parseObject($data);
    }

    /**
     * @param \StdClass $data
     *
     * @return GenericViewModel
     */
    private function parseObject(\StdClass $data) {
        $properties = [];
        foreach (get_object_vars($data) as $name => $value) {
            switch (true) {
                case is_scalar($value): {
                    $properties[$name] = $value;
                    continue 2;
                }

                case is_object($value): {
                    $properties[$name] = $this->parseObject($value);
                    continue 2;
                }

                case is_array($value): {
                    $properties[$name] = $this->parseArray($value);
                    continue 2;
                }
            }
        }

        return new GenericViewModel($properties);
    }

    /**
     * @param array $value
     *
     * @return array
     */
    private function parseArray(array $value) {
        $result = [];
        foreach ($value as $item) {
            switch (true) {
                case is_scalar($item): {
                    $result[] = $item;
                    continue 2;
                }
                case is_object($item): {
                    $result[] = $this->parseObject($item);
                    continue 2;
                }
                case is_array($item): {
                    $result[] = $this->parseArray($item);
                    continue 2;
                }
            }
        }

        return $result;
    }

}
