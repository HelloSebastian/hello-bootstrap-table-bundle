<?php


namespace HelloSebastian\HelloBootstrapTableBundle\Filters;


use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\QueryBuilder;

class TextFilter extends AbstractFilter
{
    public function addExpression(Composite $composite, QueryBuilder $qb, $dql, $search, $key, ClassMetadata $metadata)
    {
        $composite->add($qb->expr()->like($dql, '?' . $key));
        $qb->setParameter($key, '%' . $search . '%');
    }
}