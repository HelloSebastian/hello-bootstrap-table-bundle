<?php

namespace HelloSebastian\HelloBootstrapTableBundle\Filters;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CountFilter extends AbstractFilter
{
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            "condition" => "gte",
            "primaryKey" => "id"
        ));

        $resolver->setAllowedValues("condition", array("gt", "gte", "eq", "neq", "lt", "lte"));
        $resolver->setAllowedTypes("primaryKey", ["string"]);

    }

    /**
     * @param Composite $composite
     * @param QueryBuilder $qb
     * @param $dql
     * @param $search
     * @param $key
     * @param ClassMetadata|null $metadata
     */
    public function addExpression(Composite $composite, QueryBuilder $qb, $dql, $search, $key, $metadata = null)
    {
        if (!is_numeric($search)) {
            return;
        }

        $parts  = explode(".", $dql);
        $property = $parts[count($parts) - 1];
        $entityShortName = $parts[0];

        $subQueryEntityClass = $metadata->getAssociationMapping($property)['targetEntity'];
        $subQueryMappedBy = $metadata->getAssociationMapping($property)['mappedBy'];

        $subQuery = $qb->getEntityManager()->getRepository($subQueryEntityClass)->createQueryBuilder($property)
            ->select("COUNT($property.id)")
            ->where("$property.$subQueryMappedBy = $entityShortName");


        $composite->add($qb->expr()->gte("(" .$subQuery->getDQL() . ")", '?' . $key));

        $qb->setParameter($key, $search);
    }
}