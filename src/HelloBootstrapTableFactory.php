<?php


namespace HelloSebastian\HelloBootstrapTableBundle;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;

class HelloBootstrapTableFactory
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(RouterInterface $router, EntityManagerInterface $em, $defaultConfig = array())
    {
        $this->router = $router;
        $this->em = $em;
    }

    /**
     * @param string $helloTable
     * @param array $options
     * @return HelloBootstrapTable
     * @throws \Exception
     */
    public function create($helloTable, $options = array())
    {
        if (!\is_string($helloTable)) {
            $type = \gettype($helloTable);

            throw new \Exception("HelloBootstrapTableFactory::create(): String expected, {$type} given");
        }

        if (false === class_exists($helloTable)) {
            throw new \Exception("HelloBootstrapTableFactory::create(): {$helloTable} does not exist");
        }

        return new $helloTable(
            $this->router,
            $this->em,
            $options
        );
    }
}