<?php

namespace PhpEssence\Helper;


abstract class Entity
{
    /**
     * Entity constructor.
     * @param array $data
     */
    public function __construct($data = array())
    {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->{$key} = $value;
                }
            }
        }
    }

    /**
     * @param array $fields
     * @return array
     */
    public function toArray($fields = array())
    {
        $output = array();
        foreach ($this as $key => $value) {
            if (empty($fields) || in_array($key, $fields)) {
                $output[$key] = $value;
            }
        }
        return $output;
    }

    /**
     * @param array $fields
     * @return array
     */
    public function toArrayExcludes($fields = array())
    {
        $output = array();
        foreach ($this as $key => $value) {
            if (!in_array($key, $fields)) {
                $output[$key] = $value;
            }
        }
        return $output;
    }
}