<?php


namespace Form\Fields;


class EmailField extends AbstractFormField
{
    public function generateHtml(): string
    {
        $html = "<input type=\"email\" name=\"{$this->getName()}\" id=\"{$this->getId()}\" class=\"{$this->getFieldClasses()}\" />";

        if (null !== $this->getLabel()) {
            $html = "<label for=\"{$this->getId()}\" class=\"{$this->getLabelClasses()}\">{$this->getLabel()}</label>" . $html;
        }

        return $html;
    }
}