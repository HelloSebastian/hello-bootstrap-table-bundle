<?php


namespace HelloSebastian\HelloBootstrapTableBundle\Columns;


use Symfony\Component\OptionsResolver\OptionsResolver;

class HiddenColumn extends TextColumn
{
    protected function configureOutputOptions(OptionsResolver $resolver)
    {
        parent::configureOutputOptions($resolver);

        $resolver->setDefaults(array(
            'filterable' => false,
            'sortable' => false,
            'visible' => false,
            'switchable' => false
        ));
    }
}