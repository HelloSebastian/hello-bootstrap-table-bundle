<?php


namespace HelloSebastian\HelloBootstrapTableBundle\Response;


use HelloSebastian\HelloBootstrapTableBundle\Data\DataBuilder;
use HelloSebastian\HelloBootstrapTableBundle\Query\DoctrineQueryBuilder;
use Symfony\Component\HttpFoundation\Request;
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
     * @param DoctrineQueryBuilder $doctrineQueryBuilder
     * @param DataBuilder $dataBuilder
     */
    public function __construct(DoctrineQueryBuilder $doctrineQueryBuilder, DataBuilder $dataBuilder)
    {
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
            'search' => "",
            'offset' => 0,
            'sort' => null,
            'order' => null,
            'limit' => 10,
            'isCallback' => false
        ));
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
     * Checks if request is initial or callback.
     *
     * @return bool
     */
    public function isCallback()
    {
        return $this->requestData['isCallback'];
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