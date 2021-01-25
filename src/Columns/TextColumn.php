<?php


namespace HelloSebastian\HelloBootstrapTableBundle\Columns;

class TextColumn extends AbstractColumn
{
    public function buildData($entity)
    {
        if (!$this->propertyAccessor->isReadable($entity, $this->getDql())) {
            return $this->getEmptyData();
        }

        return $this->propertyAccessor->getValue($entity, $this->getDql());
    }
}