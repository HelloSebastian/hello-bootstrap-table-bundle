<?php

namespace HelloSebastian\HelloBootstrapTableBundle\Data;


use HelloSebastian\HelloBootstrapTableBundle\Columns\AbstractColumn;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActionButton
{
    /**
     * @var array
     */
    private $options;

    /**
     * Called column.
     *
     * @var AbstractColumn
     */
    private $column;

    /**
     * ActionButton constructor. Created in ActionColumn.
     *
     * @param AbstractColumn $column
     * @param array $options
     */
    public function __construct(AbstractColumn $column, $options)
    {
        $this->column = $column;

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'displayName' => null,
            'classNames' => '',
            'additionalClassNames' => '',
            'routeName' => null,
            'routeParams' => array('id'),
            'addIf' => function () {
                return true;
            }
        ));

        $resolver->setRequired('displayName');
        $resolver->setRequired('routeName');

        $resolver->setAllowedTypes('displayName', 'string');
        $resolver->setAllowedTypes('routeName', 'string');
        $resolver->setAllowedTypes('classNames', 'string');
        $resolver->setAllowedTypes('additionalClassNames', 'string');
        $resolver->setAllowedTypes('routeParams', 'array');
    }

    public function getClassNames()
    {
        $defaultActionButtonOptions = $this->column->getColumnBuilder()->getDefaultButtonOptions();
        $defaultClassNames = "";
        if (isset($defaultActionButtonOptions['classNames'])) {
            $defaultClassNames = $defaultActionButtonOptions['classNames'];
        }

        $classNames = (empty($this->options['classNames']) ? $defaultClassNames : $this->options['classNames']);
        return $classNames . ' ' . $this->options['additionalClassNames'];
    }

    public function getDisplayName()
    {
        return $this->options['displayName'];
    }

    public function getRouteName()
    {
        return $this->options['routeName'];
    }

    public function getRouteParams()
    {
        return $this->options['routeParams'];
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getAddIfCallback()
    {
        return $this->options['addIf'];
    }

}