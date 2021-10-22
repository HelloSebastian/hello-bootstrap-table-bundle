<?php

namespace HelloSebastian\HelloBootstrapTableBundle\Columns;

use HelloSebastian\HelloBootstrapTableBundle\Filters\BooleanChoiceFilter;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BooleanColumn extends AbstractColumn
{
    protected function configureOutputOptions(OptionsResolver $resolver)
    {
        parent::configureOutputOptions($resolver);

        $resolver->setDefaults(array(
            "allLabel" => "All",
            "trueLabel" => "True",
            "falseLabel" => "False",
            "filterControl" => "select"
        ));

        $resolver->setAllowedTypes('allLabel', 'string');
        $resolver->setAllowedTypes('trueLabel', 'string');
        $resolver->setAllowedTypes('falseLabel', 'string');
    }

    protected function configureInternalOptions(OptionsResolver $resolver)
    {
        parent::configureInternalOptions($resolver);

        $resolver->setDefaults(array(
            "filter" => array(BooleanChoiceFilter::class, array())
        ));
    }

    public function buildData($entity)
    {
        if (!$this->propertyAccessor->isReadable($entity, $this->getDql())) {
            return $this->getEmptyData();
        }

        $booleanValue = $this->propertyAccessor->getValue($entity, $this->getDql());

        if (is_null($booleanValue)) {
            return $this->getEmptyData();
        }

        if (!is_bool($booleanValue)) {
            throw new \LogicException("Value should be boolean. Type: " . gettype($booleanValue));
        }

        return $this->outputOptions[$booleanValue ? 'trueLabel' : 'falseLabel'];
    }
}
