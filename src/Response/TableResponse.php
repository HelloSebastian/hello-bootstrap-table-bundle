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

    public function __construct(DoctrineQueryBuilder $doctrineQueryBuilder, DataBuilder $dataBuilder)
    {
        $this->doctrineQueryBuilder = $doctrineQueryBuilder;
        $this->dataBuilder = $dataBuilder;

        $resolver = new OptionsResolver();
        $this->configureRequestData($resolver);
        $this->requestData = $resolver->resolve(array());
    }

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

    public function getData()
    {
        $entities = $this->doctrineQueryBuilder->fetchData($this->requestData);

        return array(
            "rows" => $this->dataBuilder->buildDataAsArray($entities),
            "total" => $this->doctrineQueryBuilder->getTotalCount()
        );
    }

    /**
     * @return bool
     */
    public function isCallback()
    {
        return $this->requestData['isCallback'];
    }

    public function setCallbackUrl($callbackUrl)
    {
        $this->callbackUrl = $callbackUrl;
    }

    public function getCallbackUrl()
    {
        return is_null($this->callbackUrl) ? $this->defaultRequestUri : $this->callbackUrl;
    }
}