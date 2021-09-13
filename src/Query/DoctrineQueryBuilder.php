<?php


namespace HelloSebastian\HelloBootstrapTableBundle\Query;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use HelloSebastian\HelloBootstrapTableBundle\Columns\AbstractColumn;
use HelloSebastian\HelloBootstrapTableBundle\Columns\ColumnBuilder;
use HelloSebastian\HelloBootstrapTableBundle\Filters\BooleanChoiceFilter;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

class DoctrineQueryBuilder
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var QueryBuilder
     */
    private $qb;

    /**
     * @var ColumnBuilder
     */
    private $columnBuilder;

    /**
     * @var int
     */
    private $totalCountAfterFiltering;

    /**
     * @var string|int
     */
    private $entityIdentifier;

    /**
     * @var string
     */
    private $entityName;

    /**
     * @var string
     */
    private $entityShortName;

    /**
     * @var string
     */
    private $rootAlias;

    /**
     * @var int
     */
    private $parameterIndex = 1;

    /**
     * @var ClassMetadata
     */
    private $metadata;


    public function __construct(EntityManagerInterface $em, $entityName, ColumnBuilder $columnBuilder)
    {
        $this->em = $em;
        $this->entityName = $entityName;
        $this->columnBuilder = $columnBuilder;

        $this->metadata = $this->em->getMetadataFactory()->getMetadataFor($this->entityName);
        $this->entityShortName = $this->getSafeName(strtolower($this->metadata->getReflectionClass()->getShortName()));
        $this->entityIdentifier = $this->getIdentifier($this->metadata);
        $this->qb = $this->em->getRepository($this->entityName)->createQueryBuilder($this->entityShortName);
        $this->rootAlias = $this->qb->getRootAliases()[0];
    }

    /**
     * Adds filtering and sorting to query, executes the query and returns data.
     *
     * @param array $requestData
     * @return mixed
     */
    public function fetchData($requestData)
    {
        $this->setupJoinFields();
        $this->setupGlobalSearch($requestData['search']);
        $this->setupFilterSearch($requestData['filter']);
        $this->setupSort($requestData['sort'], $requestData['order']);

        $this->setTotalCountAfterFiltering();

        $this->qb
            ->setFirstResult($requestData['offset'])
            ->setMaxResults($requestData['limit']);

        return $this->qb->getQuery()->getResult();
    }

    private function setupSort($sortColumn, $order)
    {
        if ($sortColumn) {
            $column = $this->columnBuilder->getColumnByField($sortColumn);
            $path = $this->getPropertyPath($column);

            if ($sortCallback = $column->getSortCallback()) {
                $sortCallback($this->qb, $order);
            } else {
                // every column has a filter instance
                $column->getFilter()->addOrder($this->qb, $path, $order, $this->metadata);
            }
        }
    }

    private function setupFilterSearch($filters)
    {
        // in filter search all searchable columns are connected as an AND expression
        $andExpr = $this->qb->expr()->andX();

        foreach ($filters as $columnField => $value) {
            $column = $this->columnBuilder->getColumnByField($columnField);

            if ($column->isSearchable()) {
                $path = $this->getPropertyPath($column);
                if ($searchCallback = $column->getSearchCallback()) {
                    $searchCallback($andExpr, $this->qb, $value);
                } else {
                    $column->getFilter()->addExpression($andExpr, $this->qb, $path, $value, $this->parameterIndex, $this->metadata);
                }
            }

            $this->parameterIndex++;
        }

        if ($andExpr->count() > 0) {
            $this->qb->andWhere($andExpr);
        }
    }

    private function setupGlobalSearch($search)
    {
        // in global search all searchable columns are connected as a OR expression
        $orExpr = $this->qb->expr()->orX();

        if ($search) {
            foreach ($this->columnBuilder->getColumns() as $column) {

                if ($column->isSearchable() && !$column->getFilter() instanceof BooleanChoiceFilter) {
                    $path = $this->getPropertyPath($column);
                    if ($searchCallback = $column->getSearchCallback()) {
                        $searchCallback($orExpr, $this->qb, $search);
                    } else {
                        $column->getFilter()->addExpression($orExpr, $this->qb, $path, $search, $this->parameterIndex, $this->metadata);
                    }
                }

                $this->parameterIndex++;
            }
        }

        if ($orExpr->count() > 0) {
            $this->qb->andWhere($orExpr);
        }
    }

    private function setupJoinFields()
    {
        $joins = array();

        foreach ($this->columnBuilder->getColumns() as $column) {
            if ($column->isAssociation()) {
                $currentPart = $this->rootAlias;
                $currentAlias = $currentPart;
                $propertyPath = $column->getDql();
                $parts = explode(".", $propertyPath);

                while (\count($parts) > 1) {
                    $previousPart = $currentPart;
                    $previousAlias = $currentAlias;

                    $currentPart = array_shift($parts);
                    $currentAlias = ($previousPart === $this->rootAlias ? '' : $previousPart . '_') . $currentPart;

                    if (!\array_key_exists($previousAlias . '.' . $currentPart, $joins)) {
                        $joins[$previousAlias . '.' . $currentPart] = $currentPart;
                    }
                }
            }
        }

        foreach ($joins as $key => $value) {
            $this->qb->leftJoin($key, $value);
        }
    }

    /**
     * Executes sub query to count data after filtering was added to query.
     */
    private function setTotalCountAfterFiltering()
    {
        try {
            $qb = clone $this->qb;
            $qb->resetDQLPart('orderBy');
            $this->totalCountAfterFiltering = $qb->select('COUNT(' . $this->rootAlias . '.' . $this->entityIdentifier . ')')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException $e) {
            $this->totalCountAfterFiltering = 0;
        } catch (NonUniqueResultException $e) {
            $this->totalCountAfterFiltering = 0;
        }
    }

    /**
     * Returns total count of fetched data.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->totalCountAfterFiltering;
    }

    public function getQueryBuilder()
    {
        return $this->qb;
    }

    private function getSafeName($name)
    {
        try {
            $reservedKeywordsList = $this->em->getConnection()->getDatabasePlatform()->getReservedKeywordsList();
            $isReservedKeyword = $reservedKeywordsList->isKeyword($name);
        } catch (\Exception $exception) {
            $isReservedKeyword = false;
        }

        return $isReservedKeyword ? "_{$name}" : $name;
    }

    private function getIdentifier(ClassMetadata $metadata)
    {
        $identifiers = $metadata->getIdentifierFieldNames();
        return array_shift($identifiers);
    }

    private function getPropertyPath(AbstractColumn $column)
    {
        if ($column->isAssociation()) {
            $path = $column->getPropertyPath();
        } else {
            $path = $this->rootAlias . '.' . $column->getDql();
        }

        return $path;
    }
}