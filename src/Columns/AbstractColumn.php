<?php


namespace HelloSebastian\HelloBootstrapTableBundle\Columns;


use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Routing\RouterInterface;

abstract class AbstractColumn
{
    /**
     * @var string|null
     */
    protected $dql;

    /**
     * @var array
     */
    protected $internalOptions;

    /**
     * @var array
     */
    protected $outputOptions;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    /**
     * @var ColumnBuilder
     */
    protected $columnBuilder;

    public function __construct($dql, $options)
    {
        $this->dql = $dql;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->setOptions($options);
    }

    public function setOptions($options)
    {
        //configure resolvers ...
        $outputOptionResolver = new OptionsResolver();
        $this->configureOutputOptions($outputOptionResolver);

        $internalOptionResolver = new OptionsResolver();
        $this->configureInternalOptions($internalOptionResolver);

        //output options
        $possibleOutputOptions = array_filter($options, function ($key) use ($internalOptionResolver) {
            return !in_array($key, $internalOptionResolver->getDefinedOptions());
        }, ARRAY_FILTER_USE_KEY);

        $this->outputOptions = $outputOptionResolver->resolve($possibleOutputOptions);

        //internal options
        $possibleInternalOptions = array_filter($options, function ($key) use ($outputOptionResolver) {
            return !in_array($key, $outputOptionResolver->getDefinedOptions());
        }, ARRAY_FILTER_USE_KEY);

        $this->internalOptions = $internalOptionResolver->resolve($possibleInternalOptions);

        //set default values ...
        if (is_null($this->outputOptions['field'])) {
            $this->outputOptions['field'] = $this->dql;
        }

        if (is_null($this->outputOptions['title'])) {
            $this->outputOptions['title'] = $this->dql;
        }
    }

    /**
     * Sets in ColumnBuilder.
     *
     * @param RouterInterface $router
     */
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Sets in ColumnBuilder.
     *
     * @param ColumnBuilder $columnBuilder
     */
    public function setColumnBuilder(ColumnBuilder $columnBuilder)
    {
        $this->columnBuilder = $columnBuilder;
    }

    /**
     * @param $entity
     * @return array
     */
    public abstract function buildData($entity);

    public function getDql()
    {
        return $this->dql;
    }

    /**
     * Replaces option.
     *
     * @param string $key
     * @param mixed $value
     */
    public function replaceOption($key, $value)
    {
        $options = $this->outputOptions;
        $options[$key] = $value;

        $this->setOptions($options);
    }

    public function getOutputOptions()
    {
        return $this->outputOptions;
    }

    public function getPropertyPath()
    {
        if ($this->isAssociation()) {
            $parts = explode(".", $this->dql);
            $c = count($parts);
            return $parts[$c - 2] . '.' . $parts[$c - 1];
        }

        return $this->dql;
    }

    public function getField()
    {
        return $this->outputOptions['field'];
    }

    public function getDataCallback()
    {
        return $this->internalOptions['data'];
    }

    public function getSortCallback()
    {
        return $this->internalOptions['sort'];
    }

    public function getSearchCallback()
    {
        return $this->internalOptions['search'];
    }

    public function getEmptyData()
    {
        return $this->internalOptions['emptyData'];
    }

    public function isAssociation()
    {
        return (false !== strpos($this->dql, "."));
    }

    public function getColumnBuilder()
    {
        return $this->columnBuilder;
    }

    protected function configureInternalOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'emptyData' => '',
            'data' => null,
            'sort' => null,
            'search' => null,
        ));

        $resolver->setAllowedTypes('emptyData', ['string']);
        $resolver->setAllowedTypes('data', ['Closure', 'null']);
        $resolver->setAllowedTypes('sort', ['Closure', 'null']);
        $resolver->setAllowedTypes('search', ['Closure', 'null']);
    }

    protected function configureOutputOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'title' => null,
            'field' => null,
            'width' => null,
            'filterable' => true,
            'sortable' => true,
            'visible' => true,
            'switchable' => true,
            'formatter' => "defaultFormatter"
        ));

        $resolver->setAllowedTypes('title', ['string', 'null']);
        $resolver->setAllowedTypes('field', ['string', 'null']);
        $resolver->setAllowedTypes('width', ['integer', 'null']);
        $resolver->setAllowedTypes('filterable', ['boolean']);
        $resolver->setAllowedTypes('sortable', ['boolean']);
        $resolver->setAllowedTypes('visible', ['boolean']);
        $resolver->setAllowedTypes('switchable', ['boolean']);
        $resolver->setAllowedTypes('formatter', ['string', 'null']);
    }

}