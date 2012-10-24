<?php

namespace Eluceo\iCal;

abstract class Component
{
    /**
     * @var PropertyBag
     */
    protected $properties;

    protected $components = array();

    /**
     * @abstract
     * @return string
     */
    abstract public function getType();

    protected function addComponent(Component $component, $key = null)
    {
        if (null == $key) {
            $this->components[] = $component;
        } else {
            $this->components[$key] = $component;
        }
    }

    /**
     * Renders an array containing the lines of the ical-file
     *
     * @return array
     */
    public function build()
    {
        $this->buildPropertyBag();

        $lines = array();

        $lines[] = sprintf('BEGIN:%s', $this->getType());

        foreach ($this->properties as $property) {
            $lines[] = $property->toLine();
        }

        foreach ($this->components as $component) {
            foreach ($component->build() as $l) {
                $lines[] = $l;
            }
        }

        $lines[] = sprintf('END:%s', $this->getType());

        foreach ($lines as $key => $line) {
            $lines[$key] = $this->fold($line);
        }
        $lines = explode("\r\n", implode("\r\n", $lines));

        return $lines;
    }

    /**
     * @param $line
     * @return string
     */
    public function fold($string)
    {
        $lines = array();
        $string = trim($string, "\r\n");
        $array = preg_split('/(?<!^|\\\\)(?!$)/u', $string);

        $line   = '';
        $lineNo = 0;
        foreach ($array as $char) {
            $charLen = strlen($char);
            $lineLen = strlen($line);
            if ($lineLen + $charLen > 75) {
                $line = ' ' . $char;
                ++$lineNo;
            } else {
                $line .= $char;
            }
            $lines[$lineNo] = $line;
        }

        return implode("\r\n", $lines);
    }

    /**
     * Renders the output for our
     *
     * @return string
     */
    public function render()
    {
        return implode("\r\n", $this->build());
    }

    /**
     * Building the PropertyBag
     *
     * @abstract
     * @return void
     */
    abstract public function buildPropertyBag();
}
