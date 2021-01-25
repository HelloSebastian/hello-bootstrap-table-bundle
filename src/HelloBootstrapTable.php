<?php


namespace HelloSebastian\HelloBootstrapTableBundle;

use HelloSebastian\HelloBootstrapTableBundle\Columns\ColumnBuilder;
use HelloSebastian\HelloBootstrapTableBundle\Data\DataBuilder;
use HelloSebastian\HelloBootstrapTableBundle\Query\DoctrineQueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use HelloSebastian\HelloBootstrapTableBundle\Response\TableResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

abstract class HelloBootstrapTable
{
    static $unique = 0;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var EntityManagerInterface
     */
    protected $_em;

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


    public function __construct(RouterInterface $router, EntityManagerInterface $em, $options)
    {
        self::$unique++;

        $this->router = $router;
        $this->_em = clone $em;

        $this->columnBuilder = new ColumnBuilder($router);
        $this->doctrineQueryBuilder = new DoctrineQueryBuilder($em, $this->getEntityClass(), $this->columnBuilder);

        $dataBuilder = new DataBuilder($this->columnBuilder);
        $this->tableResponse = new TableResponse($this->doctrineQueryBuilder, $dataBuilder);

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
     * Returns table structure as encoded array.
     *
     * @return array
     */
    public function createView()
    {
        //set up table dataset resolver
        $tableDatasetResolver = new OptionsResolver();
        $this->configureTableDataset($tableDatasetResolver);

        //set up table option resolver
        $tableOptionResolver = new OptionsResolver();
        $this->configureTableOptions($tableOptionResolver);
        $this->tableOptions = $tableOptionResolver->resolve($this->tableOptions);

        $columns = $this->columnBuilder->buildColumnsArray();

        if ($this->tableOptions['enableCheckbox']) {
            array_unshift($columns, array("checkbox" => true));
        }

        return array(
            'columns' => $columns,
            'tableName' => $this->getTableName(),
            'tableDataset' => $tableDatasetResolver->resolve($this->tableDataset),
            'tableOptions' => $this->tableOptions,
            'callbackUrl' => $this->tableResponse->getCallbackUrl()
        );
    }

    public function getColumnBuilder()
    {
        return $this->columnBuilder;
    }

    public function getQueryBuilder()
    {
        return $this->doctrineQueryBuilder->getQueryBuilder();
    }

    public function setTableDataset($tableDataset)
    {
        $this->tableDataset = array_merge($this->tableDataset, $tableDataset);
    }

    public function setTableOptions($options)
    {
        $this->tableOptions = array_merge($this->tableOptions, $options);
    }

    private function getTableName()
    {
        $className = get_class($this);
        $className = strtolower($className);
        $className = str_replace("\\", "_", $className);

        return $className . '_' . self::$unique;
    }

    protected function configureTableDataset(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            "pagination" => true,
            "search" => true,
            "show-columns" => true,
            "show-footer" => true,
            "show-refresh" => true,
            "toolbar" => "#toolbar",
            "page-list" => "[10, 25, 50, 100, 200, 500, All]",
            "page-size" => 25,
            "sort-reset" => true,
            "pagination-V-Align" => "both",
            "undefined-text" => "",
            "locale" => "en-US",

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
            "sticky-header-offset-y" => 0
        ));

        $resolver->setAllowedTypes("pagination", ["bool"]);
        $resolver->setAllowedTypes("search", ["bool"]);
        $resolver->setAllowedTypes("show-columns", ["bool"]);
        $resolver->setAllowedTypes("show-footer", ["bool"]);
        $resolver->setAllowedTypes("show-refresh", ["bool"]);
        $resolver->setAllowedTypes("toolbar", ["string", "null"]);
        $resolver->setAllowedTypes("page-list", ["string"]);
        $resolver->setAllowedTypes("page-size", ["int"]);
        $resolver->setAllowedTypes("sort-reset", ["bool"]);
        $resolver->setAllowedTypes("pagination-V-Align", ["string"]);
        $resolver->setAllowedTypes("undefined-text", ["string"]);
        $resolver->setAllowedTypes("locale", ["string"]);
        $resolver->setAllowedTypes("click-to-select", ["bool"]);
        $resolver->setAllowedTypes("show-jump-to", ["bool"]);
        $resolver->setAllowedTypes("show-export", ["bool"]);
        $resolver->setAllowedTypes("export-types", ["string"]);
        $resolver->setAllowedTypes("export-options", ["array"]);
        $resolver->setAllowedTypes("sticky-header", ["bool"]);
        $resolver->setAllowedTypes("sticky-header-offset-left", ["int"]);
        $resolver->setAllowedTypes("sticky-header-offset-right", ["int"]);
        $resolver->setAllowedTypes("sticky-header-offset-y", ["int"]);
    }

    protected function configureTableOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'enableCheckbox' => true,
            'bulkUrl' => '',
            'bulkActionSelectClassNames' => 'form-control',
            'bulkActions' => array(),
            'bulkButtonName' => 'Okay',
            'bulkButtonClassNames' => 'btn btn-primary'
        ));

        $resolver->setAllowedTypes("enableCheckbox", "bool");
    }
}