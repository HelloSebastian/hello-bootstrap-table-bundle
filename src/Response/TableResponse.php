<?php


namespace HelloSebastian\HelloBootstrapTableBundle\Response;


use HelloSebastian\HelloBootstrapTableBundle\Data\DataBuilder;
use HelloSebastian\HelloBootstrapTableBundle\HelloBootstrapTable;
use HelloSebastian\HelloBootstrapTableBundle\Query\DoctrineQueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TableResponse
{
    private $requestData = array();

    private $defaultRequestUri;

    /**
     * @var DoctrineQueryBuilder
     */
    private $doctrineQueryBuilder;

    /**
     * @var HelloBootstrapTable
     */
    private $bootstrapTable;

    /**
     * @var DataBuilder
     */
    private $dataBuilder;

    /**
     * @var string
     */
    private $callbackUrl;

    /**
     * TableResponse constructor. Created in HelloBootstrapTable.
     *
     * @param HelloBootstrapTable $bootstrapTable
     * @param DoctrineQueryBuilder $doctrineQueryBuilder
     * @param DataBuilder $dataBuilder
     */
    public function __construct(HelloBootstrapTable $bootstrapTable, DoctrineQueryBuilder $doctrineQueryBuilder, DataBuilder $dataBuilder)
    {
        $this->bootstrapTable = $bootstrapTable;
        $this->doctrineQueryBuilder = $doctrineQueryBuilder;
        $this->dataBuilder = $dataBuilder;

        $resolver = new OptionsResolver();
        $this->configureRequestData($resolver);
        $this->requestData = $resolver->resolve(array());
    }

    /**
     * Handles clients request and sets search, filter and pagination.
     *
     * @param Request $request
     */
    public function handleRequest(Request $request)
    {
        $this->defaultRequestUri = $request->getRequestUri();

        $requestData = array();

        if ($request->isMethod("GET")) {
            $requestData = $request->query->all();
        }

        if ($request->isMethod("POST")) {
            $requestData = $request->request->all();
        }

        $resolver = new OptionsResolver();
        $this->configureRequestData($resolver);
        $this->requestData = $resolver->resolve($requestData);
    }

    /**
     * Configure request data array.
     *
     * @param OptionsResolver $resolver
     */
    public function configureRequestData(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'filter' => array(), //advanced search
            'search' => "", //global search
            'offset' => 0,
            'sort' => null,
            'order' => null,
            'limit' => 10,
            'isCallback' => false,
            'tableName' => '',
            'searchable' => array()
        ));

        $resolver->setNormalizer('filter', function (Options $options, $value) {
            if (!is_array($value)) {
                return json_decode($value, true);
            }

            return $value;
        });
    }

    /**
     * Gets all fetches data from database with total count.
     *
     * @return array
     */
    public function getData()
    {
        $entities = $this->doctrineQueryBuilder->fetchData($this->requestData);

        return array(
            "rows" => $this->dataBuilder->buildDataAsArray($entities),
            "total" => $this->doctrineQueryBuilder->getTotalCount()
        );
    }

    /**
     * Checks if request is initial or callback and if table name is equal.
     *
     * @return bool
     */
    public function isCallback()
    {
        return $this->requestData['isCallback'] && $this->requestData['tableName'] == $this->bootstrapTable->getTableName();
    }

    /**
     * Sets callback url. Used if callback should handle by other controller.
     *
     * @param $callbackUrl
     */
    public function setCallbackUrl($callbackUrl)
    {
        $this->callbackUrl = $callbackUrl;
    }

    /**
     * Gets callback URL.
     * If no custom callback URl is set, request url is taken.
     *
     * @return string
     */
    public function getCallbackUrl()
    {
        return is_null($this->callbackUrl) ? $this->defaultRequestUri : $this->callbackUrl;
    }
}