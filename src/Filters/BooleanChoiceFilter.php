<?php


namespace HelloSebastian\HelloBootstrapTableBundle\Filters;


use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\QueryBuilder;
use HelloSebastian\HelloBootstrapTableBundle\Columns\AbstractColumn;
use HelloSebastian\HelloBootstrapTableBundle\Columns\BooleanColumn;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BooleanChoiceFilter extends ChoiceFilter
{
    public function __construct(AbstractColumn $column, $options)
    {
        if ($column instanceof BooleanColumn) {
            if (!isset($options["choices"]) || (count($options["choices"]) == 0)) {
                $options["choices"] = array(
                    "null" => $column->getOutputOptions()["allLabel"],
                    "true" => $column->getOutputOptions()["trueLabel"],
                    "false" => $column->getOutputOptions()["falseLabel"]
                );
            }
        }

        parent::__construct($column, $options);
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            "choices" => array(
                "null" => "All",
                "true" => "True",
                "false" => "False"
            )
        ));
    }


    public function addExpression(Composite $composite, QueryBuilder $qb, $dql, $search, $key, ClassMetadata $metadata)
    {
        if ($search == "null") {
            return;
        }

        $composite->add($qb->expr()->eq($dql, '?' . $key));
        $qb->setParameter($key, ($search == "true"));
    }
}
