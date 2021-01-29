<?php


namespace HelloSebastian\HelloBootstrapTableBundle\Query;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Mapping\ClassMetadata;
use HelloSebastian\HelloBootstrapTableBundle\Columns\ColumnBuilder;
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


    public function __construct(EntityManagerInterface $em, $entityName, ColumnBuilder $columnBuilder)
    {
        $this->em = $em;
        $this->entityName = $entityName;
        $this->columnBuilder = $columnBuilder;

        $metadata = $this->em->getMetadataFactory()->getMetadataFor($this->entityName);
        $this->entityShortName = $this->getSafeName(strtolower($metadata->getReflectionClass()->getShortName()));
        $this->entityIdentifier = $this->getIdentifier($metadata);
        $this->qb = $this->em->getRepository($this->entityName)->createQueryBuilder($this->entityShortName);
    }

    private function setupJoinFields()
    {
        $joins = array();

        foreach ($this->columnBuilder->getColumns() as $column) {
            if ($column->isAssociation()) {
                $currentPart = $this->qb->getRootAliases()[0];
                $currentAlias = $currentPart;
                $propertyPath = $column->getDql();
                $parts = explode(".", $propertyPath);

                while (\count($parts) > 1) {
                    $previousPart = $currentPart;
                    $previousAlias = $currentAlias;

                    $currentPart = array_shift($parts);
                    $currentAlias = ($previousPart === $this->qb->getRootAliases()[0] ? '' : $previousPart . '_') . $currentPart;

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
     * Adds filtering and sorting to query, executes the query and returns data.
     *
     * @param array $requestData
     * @return mixed
     */
    public function fetchData($requestData)
    {
        $this->setupJoinFields();

        $orExpr = $this->qb->expr()->orX();

        if ($requestData['search']) {
            foreach ($this->columnBuilder->getColumns() as $key => $column) {
                ++$key;

                if ($column->getOutputOptions()['filterable']) {

                    if ($column->isAssociation()) {
                        $path = $column->getPropertyPath();
                    } else {
                        $path = $this->qb->getRootAliases()[0] . '.' . $column->getDql();
                    }

                    if ($searchCallback = $column->getSearchCallback()) {
                        $searchCallback($orExpr, $this->qb, $path, $requestData['search'], $key);

                    } else {
                        $orExpr->add($this->qb->expr()->like($path, '?' . $key));
                        $this->qb->setParameter($key, '%' . $requestData['search'] . '%');
                    }
                }
            }
        }

        if ($orExpr->count() > 0) {
            $this->qb->andWhere($orExpr);
        }

        if ($requestData["sort"]) {
            $column = $this->columnBuilder->getColumnByField($requestData["sort"]);

            if ($column->isAssociation()) {
                $path = $column->getPropertyPath();
            } else {
                $path = $this->qb->getRootAliases()[0] . '.' . $column->getDql();
            }

            if ($sortCallback = $column->getSortCallback()) {
                $sortCallback($this->qb, $requestData["order"]);
            } else {
                $this->qb->addOrderBy($path, $requestData["order"]);
            }
        }

        $this->setTotalCountAfterFiltering();

        $this->qb
            ->setFirstResult($requestData['offset'])
            ->setMaxResults($requestData['limit']);

        return $this->qb->getQuery()->getResult();
    }

    /**
     * Executes sub query to count data after filtering was added to query.
     */
    private function setTotalCountAfterFiltering()
    {
        try {
            $qb = clone $this->qb;
            $this->totalCountAfterFiltering = $qb->select('COUNT(' . $qb->getRootAliases()[0] . '.' . $this->entityIdentifier . ')')
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
}