<?php


namespace HelloSebastian\HelloBootstrapTableBundle;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use HelloSebastian\HelloBootstrapTableBundle\Columns\ColumnBuilder;
use HelloSebastian\HelloBootstrapTableBundle\Data\DataBuilder;
use HelloSebastian\HelloBootstrapTableBundle\Query\DoctrineQueryBuilder;
use HelloSebastian\HelloBootstrapTableBundle\Response\TableResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;

abstract class HelloBootstrapTable
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var Environment
     */
    protected $twig;

    /**
     * @var EntityManagerInterface
     */
    protected $_em;

    /**
     * @var Security
     */
    protected $security;

    /**
     * @var ColumnBuilder
     */
    private $columnBuilder;

    /**
     * @var DoctrineQueryBuilder
     */
    private $doctrineQueryBuilder;

    /**
     * @var TableResponse
     */
    private $tableResponse;

    /**
     * Dataset that passed to table as data-attributes.
     *
     * @var array
     */
    private $tableDataset = array();

    /**
     * Internal options for table.
     *
     * @var array
     */
    private $tableOptions = array();

    /**
     * YAML config options.
     *
     * @var array
     */
    private $defaultOptions;

    /**
     * HelloBootstrapTable constructor. Created in HelloBootstrapTableFactory.
     *
     * @param RouterInterface $router
     * @param EntityManagerInterface $em
     * @param Environment $twig
     * @param Security $security
     * @param array $options
     * @param array $defaultOptions
     */
    public function __construct(RouterInterface $router, EntityManagerInterface $em, Environment $twig, Security $security, $options, $defaultOptions = array())
    {
        $this->router = $router;
        $this->twig = $twig;
        $this->security = $security;
        $this->_em = clone $em;
        $this->defaultOptions = $defaultOptions;

        $this->columnBuilder = new ColumnBuilder($router, $this->defaultOptions['action_button_options']);
        $this->doctrineQueryBuilder = new DoctrineQueryBuilder($em, $this->getEntityClass(), $this->columnBuilder);

        $dataBuilder = new DataBuilder($this->columnBuilder);
        $this->tableResponse = new TableResponse($this, $this->doctrineQueryBuilder, $dataBuilder);

        $this->buildColumns($this->columnBuilder, $options);
    }

    /**
     * @param ColumnBuilder $builder
     * @param array $options
     */
    protected abstract function buildColumns(ColumnBuilder $builder, $options);

    /**
     * Returns FQCN of entity class.
     *
     * @return string
     */
    protected abstract function getEntityClass();

    /**
     * Handles request and gets request information.
     *
     * @param Request $request
     */
    public function handleRequest(Request $request)
    {
        $this->tableResponse->handleRequest($request);
    }

    /**
     * Checks if request is a callback.
     *
     * @return boolean
     */
    public function isCallback()
    {
        return $this->tableResponse->isCallback();
    }

    /**
     * Gets data depends on paging, filtering and sorting.
     *
     * @return JsonResponse
     */
    public function getResponse()
    {
        return new JsonResponse($this->tableResponse->getData());
    }

    /**
     * Returns table structure as array.
     *
     * @return array
     */
    public function createView()
    {
        //set default options from yaml config
        $this->tableOptions = array_merge($this->defaultOptions['table_options'], $this->tableOptions);
        $this->tableDataset = array_merge($this->defaultOptions['table_dataset_options'], $this->tableDataset);

        //set up table dataset resolver
        $tableDatasetResolver = new OptionsResolver();
        $this->configureTableDataset($tableDatasetResolver);
        $this->tableDataset = $tableDatasetResolver->resolve($this->tableDataset);

        //remove dataset options that are null
        foreach ($this->tableDataset as $key => $datum) {
            if (is_null($datum)) {
                unset($this->tableDataset[$key]);
            }
        }

        //set up table option resolver
        $tableOptionResolver = new OptionsResolver();
        $this->configureTableOptions($tableOptionResolver);
        $this->tableOptions = $tableOptionResolver->resolve($this->tableOptions);

        $columns = $this->columnBuilder->buildColumnsArray();

        if ($this->tableOptions['enableCheckbox']) {
            if (!$this->columnBuilder->getColumnByField($this->tableOptions['bulkIdentifier'])) {
                throw new \LogicException("Field for bulk identifier not found in columns. Given identifier: " . $this->tableOptions['bulkIdentifier']);
            }

            array_unshift($columns, array("checkbox" => true));
        }

        return array(
            'columns' => $columns,
            'tableName' => $this->getTableName(),
            'tableDataset' => $this->tableDataset,
            'tableOptions' => $this->tableOptions,
            'callbackUrl' => $this->tableResponse->getCallbackUrl()
        );
    }

    /**
     * Gets ColumnBuilder instance.
     *
     * @return ColumnBuilder
     */
    public function getColumnBuilder()
    {
        return $this->columnBuilder;
    }

    /**
     * Gets Doctrine QueryBuilder instance.
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->doctrineQueryBuilder->getQueryBuilder();
    }

    /**
     * Sets default sorting for table.
     *
     * If direction is null, default sorting will be ignored.
     *
     * @param string $columnDql
     * @param string|null $direction can only be "asc", "desc" or null
     */
    public function setDefaultSorting($columnDql, $direction)
    {
        $this->setTableDataset(array(
            "sort-name" => $columnDql,
            "sort-order" => $direction
        ));
    }

    /**
     * Sets and overrides YAML config.
     *
     * @param array $tableDataset
     */
    public function setTableDataset($tableDataset)
    {
        $this->tableDataset = array_merge($this->tableDataset, $tableDataset);
    }

    /**
     * Sets and overrides YAML config.
     *
     * @param array $options
     */
    public function setTableOptions($options)
    {
        $this->tableOptions = array_merge($this->tableOptions, $options);
    }

    public function getTableName()
    {
        $className = get_class($this);
        $className = strtolower($className);
        $className = str_replace("\\", "_", $className);

        return $className;
    }

    /**
     * Configure table dataset options.
     *
     * If option set in YAML config, that option will not be override.
     * Please use setTableDataset to override options.
     *
     * @param OptionsResolver $resolver
     */
    protected function configureTableDataset(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            "pagination" => true,
            "search" => true,
            "show-columns" => true,
            "show-columns-toggle-all" => false,
            "show-footer" => true,
            "show-refresh" => true,
            "filter-control" => true,
            "detail-view" => false,
            "detail-formatter" => "",
            "detail-view-align" => "left",
            "detail-view-icon" => true,
            "detail-view-by-click" => false,
            "toolbar" => "#toolbar",
            "page-list" => "[10, 25, 50, 100, 200, 500, All]",
            "page-size" => 25,
            "sort-reset" => true,
            "pagination-V-Align" => "both",
            "undefined-text" => "",
            "locale" => "en-US",
            "advanced-search" => false,
            "id-table" => $this->getTableName(),
            "icons-prefix" => "fa",
            "checkbox-header" => true,
            "escape" => false,
            "height" => null,
            "multiple-select-row" => false,
            "sort-name" => null,
            "sort-order" => null,

            //extensions
            "click-to-select" => true,
            "show-jump-to" => true,

            //export
            "show-export" => true,
            "export-types" => "['csv', 'txt'', 'excel']",
            "export-options" => array(
                'fileName' => (new \DateTime('now'))->format('Y-m-d_H-i-s') . '_export',
                'ignoreColumn' => array("checkbox", "actions"),
                'csvSeparator' => ';'
            ),

            //sticky header
            "sticky-header" => true,
            "sticky-header-offset-left" => 0,
            "sticky-header-offset-right" => 0,
            "sticky-header-offset-y" => 0,

            "icons" => function (OptionsResolver $resolver) {
                $resolver->setDefaults(array(
                    "advancedSearchIcon" => "fa-filter",
                    "paginationSwitchDown" => "fa-caret-square-o-down",
                    "paginationSwitchUp" => "fa-caret-square-o-up",
                    "columns" => "fa-columns",
                    "refresh" => "fa-sync",
                    "export" => "fa-download",
                    "detailOpen" => "fa-plus",
                    "detailClose" => "fa-minus",
                    "toggleOff" => "fa-toggle-off",
                    "toggleOn" => "fa-toggle-on",
                    "fullscreen" => "fa-arrows-alt",
                    "search" => "fa-search",
                    "clearSearch" => "fa-trash"
                ));

                $resolver->setAllowedTypes("advancedSearchIcon", ["string"]);
                $resolver->setAllowedTypes("paginationSwitchDown", ["string"]);
                $resolver->setAllowedTypes("columns", ["string"]);
                $resolver->setAllowedTypes("refresh", ["string"]);
                $resolver->setAllowedTypes("export", ["string"]);
                $resolver->setAllowedTypes("detailOpen", ["string"]);
                $resolver->setAllowedTypes("toggleOff", ["string"]);
                $resolver->setAllowedTypes("toggleOn", ["string"]);
                $resolver->setAllowedTypes("fullscreen", ["string"]);
                $resolver->setAllowedTypes("search", ["string"]);
                $resolver->setAllowedTypes("clearSearch", ["string"]);
            },
        ));

        $resolver->setAllowedTypes("pagination", ["bool"]);
        $resolver->setAllowedTypes("search", ["bool"]);
        $resolver->setAllowedTypes("show-columns", ["bool"]);
        $resolver->setAllowedTypes("show-columns-toggle-all", ["bool"]);
        $resolver->setAllowedTypes("show-footer", ["bool"]);
        $resolver->setAllowedTypes("detail-view", ["bool"]);
        $resolver->setAllowedTypes("detail-formatter", ["string"]);
        $resolver->setAllowedTypes("detail-view-align", ["string"]);
        $resolver->setAllowedTypes("detail-view-icon", ["bool"]);
        $resolver->setAllowedTypes("detail-view-by-click", ["bool"]);
        $resolver->setAllowedTypes("show-refresh", ["bool"]);
        $resolver->setAllowedTypes("filter-control", ["bool"]);
        $resolver->setAllowedTypes("toolbar", ["string", "null"]);
        $resolver->setAllowedTypes("page-list", ["string"]);
        $resolver->setAllowedTypes("page-size", ["int"]);
        $resolver->setAllowedTypes("sort-reset", ["bool"]);
        $resolver->setAllowedTypes("pagination-V-Align", ["string"]);
        $resolver->setAllowedTypes("undefined-text", ["string"]);
        $resolver->setAllowedTypes("icons-prefix", ["string"]);
        $resolver->setAllowedTypes("icons", ["array"]);
        $resolver->setAllowedTypes("locale", ["string"]);
        $resolver->setAllowedTypes("advanced-search", ["bool"]);
        $resolver->setAllowedTypes("id-table", ["string"]);
        $resolver->setAllowedTypes("click-to-select", ["bool"]);
        $resolver->setAllowedTypes("show-jump-to", ["bool"]);
        $resolver->setAllowedTypes("show-export", ["bool"]);
        $resolver->setAllowedTypes("export-types", ["string"]);
        $resolver->setAllowedTypes("export-options", ["array"]);
        $resolver->setAllowedTypes("sticky-header", ["bool"]);
        $resolver->setAllowedTypes("sticky-header-offset-left", ["int"]);
        $resolver->setAllowedTypes("sticky-header-offset-right", ["int"]);
        $resolver->setAllowedTypes("sticky-header-offset-y", ["int"]);
        $resolver->setAllowedTypes("checkbox-header", ["bool"]);
        $resolver->setAllowedTypes("escape", ["bool"]);
        $resolver->setAllowedTypes("height", ["int", "null"]);
        $resolver->setAllowedTypes("multiple-select-row", ["bool"]);
        $resolver->setAllowedTypes("sort-name", ["string", "null"]);
        $resolver->setAllowedTypes("sort-order", ["string", "null"]);

        $resolver->setAllowedValues("sort-order", ["asc", "desc", null]);
    }

    /**
     * Configure table options.
     *
     * If option set in YAML config, that option will not be override.
     * Please use setTableOptions to override options.
     *
     * @param OptionsResolver $resolver
     */
    protected function configureTableOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'tableClassNames' => 'table table-striped table-sm',
            'enableCheckbox' => false,
            'bulkIdentifier' => 'id',
            'bulkUrl' => '',
            'bulkActionSelectClassNames' => 'form-control',
            'bulkActions' => array(),
            'bulkButtonName' => 'Okay',
            'bulkButtonClassNames' => 'btn btn-primary'
        ));

        $resolver->setAllowedTypes("tableClassNames", ["string"]);
        $resolver->setAllowedTypes("enableCheckbox", ["bool"]);
        $resolver->setAllowedTypes("bulkIdentifier", ["string"]);
        $resolver->setAllowedTypes("bulkUrl", ["string"]);
        $resolver->setAllowedTypes("bulkActionSelectClassNames", ["string"]);
        $resolver->setAllowedTypes("bulkActions", ["array"]);
        $resolver->setAllowedTypes("bulkButtonName", ["string"]);
        $resolver->setAllowedTypes("bulkButtonClassNames", ["string"]);
    }
}
