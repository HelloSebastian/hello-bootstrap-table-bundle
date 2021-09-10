<?php


namespace HelloSebastian\HelloBootstrapTableBundle;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
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
     * @var Security
     */
    private $security;

    /**
     * YAML config options.
     *
     * @var array
     */
    private $defaultConfig;

    public function __construct(RouterInterface $router, EntityManagerInterface $em, Environment $twig, Security $security, $defaultConfig = array())
    {
        $this->router = $router;
        $this->em = $em;
        $this->twig = $twig;
        $this->security = $security;
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

            throw new \LogicException("HelloBootstrapTableFactory::create(): String expected, {$type} given");
        }

        if (false === class_exists($helloTable)) {
            throw new \LogicException("HelloBootstrapTableFactory::create(): {$helloTable} does not exist");
        }

        return new $helloTable(
            $this->router,
            $this->em,
            $this->twig,
            $this->security,
            $options,
            $this->defaultConfig
        );
    }
}