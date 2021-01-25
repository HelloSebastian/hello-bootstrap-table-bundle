<?php


namespace HelloSebastian\HelloBootstrapTableBundle\Data;


use HelloSebastian\HelloBootstrapTableBundle\Columns\AbstractColumn;
use HelloSebastian\HelloBootstrapTableBundle\Columns\ColumnBuilder;

class DataBuilder
{
    /**
     * @var ColumnBuilder
     */
    private $columnBuilder;

    public function __construct(ColumnBuilder $columnBuilder)
    {
        $this->columnBuilder = $columnBuilder;
    }

    /**
     * @param $entities
     * @return array
     */
    public function buildDataAsArray($entities)
    {
        $data = array();
        foreach ($entities as $entity) {

            $row = array();

            /** @var AbstractColumn $column */
            foreach ($this->columnBuilder->getColumns() as $column) {

                if (!is_null($column->getDataCallback())) {
                    $row[$column->getField()] = $column->getDataCallback()($entity);
                    continue;
                }

                $row[$column->getDql()] = $column->buildData($entity);
            }

            $data[] = $row;
        }

        return $data;
    }

}