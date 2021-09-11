<?php

namespace HelloSebastian\HelloBootstrapTableBundle\Columns;

use Doctrine\ORM\PersistentCollection;
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

        $arrayCollection = $this->propertyAccessor->getValue($entity, $this->getDql());

        if (is_null($arrayCollection)) {
            return $this->getEmptyData();
        }

        if (!$arrayCollection instanceof PersistentCollection) {
            throw new \LogicException("Value should be of type of ArrayCollection. Type: " . gettype($arrayCollection));
        }

        return $arrayCollection->count();
    }
}