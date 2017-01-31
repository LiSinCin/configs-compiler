<?php

namespace Sli\ConfigsCompiler;

/**
 * @internal
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class PropParser
{
    /**
     * @param string $value  A value like api_key=1234
     *
     * @return array
     */
    public function parseSingle($value)
    {
        if (false === strpos($value, '=')) {
            $msg = implode(' ', [
                "Property '$value' isn't properly formatted, correct syntax is where key and value are separated",
                'by =, for example api_key=1234.'
            ]);

            throw new \DomainException($msg);
        }

        // splitting by first occurrence of = symbol
        $delimiterPosition = strpos($value, '=');

        return [
            substr($value, 0, $delimiterPosition),
            substr($value, $delimiterPosition+1)
        ];
    }

    /**
     * @param ConfigPropertyRegistry $registry  Is used to figure out how properly normalize a property
     * @param string[] $values
     *
     * @return array
     */
    public function parseMultiple(ConfigPropertyRegistry $registry, array $values)
    {
        $result = array();

        foreach ($values as $prop) {
            list ($key, $value) = $this->parseSingle($prop);

            $property = $registry->get($key);

            if (!isset($result[$key])) {
                $result[$key] = $property->isArray() ? [] : null;
            }

            if ($property->isArray()) {
                $result[$key][] = $value;
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}