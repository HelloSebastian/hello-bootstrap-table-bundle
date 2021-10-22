<?php

namespace HelloSebastian\HelloBootstrapTableBundle\Columns;

trait FormatAttributeTrait
{
    public function formatAttr(array $attr): string
    {
        $formattedAttributes = array();
        foreach ($attr as $attribute => $value) {
            if (empty($value) === false) {
                $formattedAttributes[] = sprintf("%s=\"%s\"", $attribute, $value);
            }
        }

        if (count($formattedAttributes) < 1) {
            return "";
        }

        return " " . implode(" ", $formattedAttributes);
    }
}
