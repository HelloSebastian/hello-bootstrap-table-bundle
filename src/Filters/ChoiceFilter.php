<?php


namespace HelloSebastian\HelloBootstrapTableBundle\Filters;


use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChoiceFilter extends AbstractFilter
{
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            "choices" => array(),
            "advSearchFieldFormatter" => "defaultAdvSearchChoiceField",
            "selectedValue" => "null"
        ));

        $resolver->setAllowedTypes("choices", ["array"]);
    }

    public function addExpression(Composite $composite, QueryBuilder $qb, $dql, $search, $key, ClassMetadata $metadata)
    {
        if ($search == "null") {
            return;
        }

        $composite->add($qb->expr()->eq($dql, '?' . $key));
        $qb->setParameter($key, $search);
    }
}
