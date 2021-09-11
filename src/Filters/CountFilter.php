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

        $resolver->setAllowedValues("condition", ["gt", "gte", "eq", "neq", "lt", "lte"]);
        $resolver->setAllowedTypes("primaryKey", ["string"]);
    }

    public function addExpression(Composite $composite, QueryBuilder $qb, $dql, $search, $key, ClassMetadata $metadata)
    {
        if (!is_numeric($search)) {
            return;
        }

        $parts  = explode(".", $dql);
        $countParts = count($parts);
        $property = $parts[$countParts - 1];
        $entityShortName = $parts[$countParts - 2];

        $subQueryEntityClass = $metadata->getAssociationMapping($property)['targetEntity'];
        $subQueryMappedBy = $metadata->getAssociationMapping($property)['mappedBy'];

        $subQuery = $qb->getEntityManager()->getRepository($subQueryEntityClass)->createQueryBuilder($property)
            ->select("COUNT($property.id)")
            ->where("$property.$subQueryMappedBy = $entityShortName");

        $composite->add($qb->expr()->{$this->options['condition']}("(" .$subQuery->getDQL() . ")", '?' . $key));
        $qb->setParameter($key, $search);
    }
}