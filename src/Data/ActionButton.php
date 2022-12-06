<?php

namespace HelloSebastian\HelloBootstrapTableBundle\Data;

use HelloSebastian\HelloBootstrapTableBundle\Columns\AbstractColumn;
use HelloSebastian\HelloBootstrapTableBundle\Columns\FormatAttributeTrait;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActionButton
{
    use FormatAttributeTrait;

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
    public function __construct(AbstractColumn $column, array $options)
    {
        $this->column = $column;

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'displayName' => null,
            'classNames' => '',
            'additionalClassNames' => '',
            'routeName' => null,
            'routeParams' => array('id'),
            'attr' => array(),
            'addIf' => function ($entity) {
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
        $resolver->setAllowedTypes('addIf', 'Closure');
        $resolver->setAllowedTypes('attr', 'array');
    }

    public function getClassNames(): string
    {
        $defaultActionButtonOptions = $this->column->getColumnBuilder()->getDefaultButtonOptions();
        $defaultClassNames = "";
        if (isset($defaultActionButtonOptions['classNames'])) {
            $defaultClassNames = $defaultActionButtonOptions['classNames'];
        }

        $classNames = (empty($this->options['classNames']) ? $defaultClassNames : $this->options['classNames']);
        return $classNames . ' ' . $this->options['additionalClassNames'];
    }

    public function getDisplayName(): string
    {
        return $this->options['displayName'];
    }

    public function getRouteName(): string
    {
        return $this->options['routeName'];
    }

    public function getRouteParams(): array
    {
        return $this->options['routeParams'];
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getAddIfCallback(): \Closure
    {
        return $this->options['addIf'];
    }

    public function getAttr(): array
    {
        return $this->options['attr'];
    }

}
