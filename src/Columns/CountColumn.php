<?php

namespace HelloSebastian\HelloBootstrapTableBundle\Columns;

use Doctrine\Common\Collections\Collection;
use HelloSebastian\HelloBootstrapTableBundle\Filters\CountFilter;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CountColumn extends AbstractColumn
{

    protected function configureInternalOptions(OptionsResolver $resolver)
    {
        parent::configureInternalOptions($resolver);

        $resolver->setDefaults(array(
            "filter" => array(CountFilter::class, array())
        ));
    }

    public function buildData($entity)
    {
        if (!$this->propertyAccessor->isReadable($entity, $this->getDql())) {
            return $this->getEmptyData();
        }

        $collection = $this->propertyAccessor->getValue($entity, $this->getDql());

        if (is_null($collection)) {
            return $this->getEmptyData();
        }

        if (!$collection instanceof Collection) {
            throw new \LogicException("Value should be implemented the interface Doctrine\Common\Collections\Collection. Type: " . gettype($collection));
        }

        return $collection->count();
    }
}
