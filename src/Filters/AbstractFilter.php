<?php

namespace HelloSebastian\HelloBootstrapTableBundle\Filters;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\QueryBuilder;
use HelloSebastian\HelloBootstrapTableBundle\Columns\AbstractColumn;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractFilter
{
    /**
     * @var AbstractColumn
     */
    private $column;

    /**
     * @var array
     */
    protected $options;

    public function __construct(AbstractColumn $column, array $options)
    {
        $this->column = $column;
        $this->options = $options;

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);

        if (is_null($this->options['placeholder'])) {
            $this->options['placeholder'] = $this->column->getOutputOptions()['title'] . " ...";
        }
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            "advSearchFieldFormatter" => "defaultAdvSearchTextField",
            "placeholder" => null
        ));

        $resolver->setAllowedTypes("advSearchFieldFormatter", ["string"]);
        $resolver->setAllowedTypes("placeholder", ["string", "null"]);
    }

    public abstract function addExpression(Composite $composite, QueryBuilder $qb, string $dql, string $search, int $key, ClassMetadata $metadata): void;

    public function addOrder(QueryBuilder $qb, string $dql, string $direction, ClassMetadata $metadata): void
    {
        $qb->addOrderBy($dql, $direction);
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
