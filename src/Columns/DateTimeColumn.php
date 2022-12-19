<?php

namespace HelloSebastian\HelloBootstrapTableBundle\Columns;

use Symfony\Component\OptionsResolver\OptionsResolver;

class DateTimeColumn extends AbstractColumn
{
    protected function configureOutputOptions(OptionsResolver $resolver): void
    {
        parent::configureOutputOptions($resolver);

        $resolver->setDefaults(array(
            'format' => 'Y-m-d H:i:s',
            'filterDatepickerOptions' => array() // see https://bootstrap-datepicker.readthedocs.io/en/latest/index.html
        ));

        $resolver->setAllowedTypes('format', 'string');
        $resolver->setAllowedTypes('filterDatepickerOptions', 'array');
    }

    /**
     * @inheritDoc
     */
    public function buildData($entity)
    {
        if (!$this->propertyAccessor->isReadable($entity, $this->getDql())) {
            return $this->getEmptyData();
        }

        $dateTime = $this->propertyAccessor->getValue($entity, $this->getDql());
        if (is_null($dateTime)) {
            return $this->getEmptyData();
        }

        if (!$dateTime instanceof \DateTime) {
            throw new \LogicException("DateTimeColumn :: Property should be DateTime. Type: " . gettype($dateTime));
        }

        return $dateTime->format($this->outputOptions['format']);
    }
}
