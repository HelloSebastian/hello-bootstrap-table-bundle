<?php


namespace HelloSebastian\HelloBootstrapTableBundle;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

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

    /**
     * @var Environment
     */
    private $twig;

    /**
     * YAML config options.
     *
     * @var array
     */
    private $defaultConfig;

    public function __construct(RouterInterface $router, EntityManagerInterface $em, Environment $twig, $defaultConfig = array())
    {
        $this->router = $router;
        $this->em = $em;
        $this->twig = $twig;
        $this->defaultConfig = $defaultConfig;
    }

    /**
     * Creates HelloBootstrapTable.
     *
     * @param string $helloTable
     * @param array $options
     * @return HelloBootstrapTable
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
            $this->twig,
            $options,
            $this->defaultConfig
        );
    }
}