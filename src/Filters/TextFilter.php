<?php


namespace HelloSebastian\HelloBootstrapTableBundle\Filters;


use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\QueryBuilder;

class TextFilter extends AbstractFilter
{
    public function addExpression(Composite $composite, QueryBuilder $qb, $dql, $search, $parameterKey)
    {
        $composite->add($qb->expr()->like($dql, '?' . $parameterKey));
        $qb->setParameter($parameterKey, '%' . $search . '%');
    }
}