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

    /**
     * DataBuilder constructor. Created in HelloBootstrapTable.
     *
     * @param ColumnBuilder $columnBuilder
     */
    public function __construct(ColumnBuilder $columnBuilder)
    {
        $this->columnBuilder = $columnBuilder;
    }

    /**
     * Loops over all columns and builds data array.
     *
     * @param array $entities
     * @return array
     */
    public function buildDataAsArray(array $entities): array
    {
        $data = array();
        foreach ($entities as $entity) {
            $row = array();

            foreach ($this->columnBuilder->getColumns() as $column) {
                // if custom data callback is set, execute it.
                if (!is_null($column->getDataCallback())) {
                    $row[$column->getField()] = $column->getDataCallback()($entity);
                    continue;
                }

                $row[$column->getField()] = $column->buildData($entity);
            }

            $data[] = $row;
        }

        return $data;
    }
}
