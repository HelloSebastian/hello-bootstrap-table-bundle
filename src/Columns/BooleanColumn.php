<?php


namespace HelloSebastian\HelloBootstrapTableBundle\Columns;


use Symfony\Component\OptionsResolver\OptionsResolver;

class BooleanColumn extends AbstractColumn
{
    protected function configureOutputOptions(OptionsResolver $resolver)
    {
        parent::configureOutputOptions($resolver);

        $resolver->setDefaults(array(
            'trueLabel' => 'True',
            'falseLabel' => 'False',
            'advancedSearchType' => "checkbox"
        ));

        $resolver->setAllowedTypes('trueLabel', 'string');
        $resolver->setAllowedTypes('falseLabel', 'string');
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
            throw new \Exception("Value should be boolean. Type: " . gettype($booleanValue));
        }

        return $this->outputOptions[$booleanValue ? 'trueLabel' : 'falseLabel'];
    }
}