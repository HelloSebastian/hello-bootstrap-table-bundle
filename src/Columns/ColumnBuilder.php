<?php

namespace HelloSebastian\HelloBootstrapTableBundle\Columns;

use Symfony\Component\Routing\RouterInterface;

class ColumnBuilder
{
    /**
     * @var AbstractColumn[]
     */
    private $columns = array();

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * YAML config option for action buttons.
     *
     * @var array
     */
    private $defaultButtonOptions;

    /**
     * ColumnBuilder constructor. Created in HelloBootstrapTable.
     *
     * @param RouterInterface $router
     * @param array $defaultButtonOptions
     */
    public function __construct(RouterInterface $router, array $defaultButtonOptions)
    {
        $this->router = $router;
        $this->defaultButtonOptions = $defaultButtonOptions;
    }

    /**
     * Gets column by field. If no column found returns null.
     *
     * @param string $field
     * @return AbstractColumn|null
     */
    public function getColumnByField(string $field): ?AbstractColumn
    {
        foreach ($this->columns as $column) {
            if ($column->getField() == $field) {
                return $column;
            }
        }

        return null;
    }

    /**
     * Gets column by dql. If no column found returns null.
     *
     * @param string $dql
     * @return AbstractColumn|null
     */
    public function getColumnByDql(string $dql): ?AbstractColumn
    {
        foreach ($this->columns as $column) {
            if ($column->getDql() == $dql) {
                return $column;
            }
        }

        return null;
    }

    /**
     * Adds new column to table.
     *
     * @param string|null $dql
     * @param string $columnClass
     * @param array $options
     * @return $this
     */
    public function add(string $dql, string $columnClass, array $options = array()): self
    {
        /** @var AbstractColumn $column */
        $column = new $columnClass($dql, $options);
        $column->setColumnBuilder($this);
        $column->setRouter($this->router);

        $this->columns[] = $column;

        return $this;
    }

    /**
     * Removes column by dql.
     *
     * @param string $dql
     */
    public function remove(string $dql): void
    {
        foreach ($this->columns as $key => $column) {
            if ($column->getDql() == $dql) {
                unset($this->columns[$key]);
                return;
            }
        }
    }

    /**
     * @return array
     */
    public function buildColumnsArray(): array
    {
        $data = array();
        foreach ($this->getColumns() as $column) {
            $data[] = $column->getOutputOptions();
        }

        return $data;
    }

    /**
     * @param bool $ignoreAddIf
     * @return AbstractColumn[]
     */
    public function getColumns(bool $ignoreAddIf = false): array
    {
        if (!$ignoreAddIf) {
            $columns = array();
            foreach ($this->columns as $column) {
                if ($column->getAddIfCallback()()) {
                    $columns[] = $column;
                }
            }

            return $columns;
        }

        return $this->columns;
    }

    public function getDefaultButtonOptions(): array
    {
        return $this->defaultButtonOptions;
    }

}
