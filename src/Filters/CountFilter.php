<?php

namespace HelloSebastian\HelloBootstrapTableBundle\Filters;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CountFilter extends AbstractFilter
{
    /**
     * To ensure sub query alias are unique.
     *
     * @var int
     */
    static $subQueryCounter = 0;

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

    public function addOrder(QueryBuilder $qb, $dql, $direction, ClassMetadata $metadata)
    {
        $alias = str_replace(".", "_", $dql);

        // sub query must be placed in select area but as hidden attribute
        // the count_$alias can then be used to order
        // sub query must be in brackets but not the alias
        $subQuery = $this->createSubQuery($qb, $metadata, $dql);
        $qb->addSelect("(" . $subQuery->getDQL() . ") AS HIDDEN count_$alias");
        $qb->addOrderBy("count_$alias", $direction);
    }

    public function addExpression(Composite $composite, QueryBuilder $qb, $dql, $search, $key, ClassMetadata $metadata)
    {
        if (!is_numeric($search)) {
            return;
        }

        $subQuery = $this->createSubQuery($qb, $metadata, $dql);

        // sub query must be in brackets
        $composite->add($qb->expr()->{$this->options['condition']}("(" . $subQuery->getDQL() . ")", '?' . $key));
        $qb->setParameter($key, $search);
    }

    private function createSubQuery(QueryBuilder $qb, ClassMetadata $metadata, $dql)
    {
        self::$subQueryCounter++;

        // split dql
        // e.g. dql is "user.someAttributes". user is the entity short name and someAttributes the property name.
        $parts = explode(".", $dql);
        $countParts = count($parts);
        $property = $parts[$countParts - 1];
        $entityShortName = $parts[$countParts - 2];

        // get information about (class) names of the ArrayCollection field type
        $subQueryEntityClass = $metadata->getAssociationMapping($property)['targetEntity'];
        $subQueryMappedBy = $metadata->getAssociationMapping($property)['mappedBy'];

        // to ensure sub query alias are unique
        $alias = $property . "_" . self::$subQueryCounter;

        // create sub query builder with target entity class and unique alias
        // COUNT attribute can be managed by option "primaryKey" (default is id)
        return $qb->getEntityManager()->getRepository($subQueryEntityClass)->createQueryBuilder($alias)
            ->select("COUNT($alias.{$this->options['primaryKey']})")
            ->where("$alias.$subQueryMappedBy = $entityShortName");
    }

}