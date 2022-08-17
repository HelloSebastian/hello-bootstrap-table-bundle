<?php


namespace HelloSebastian\HelloBootstrapTableBundle\Twig;


use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class BootstrapTableTwigExtension extends AbstractExtension
{
    const ASSET_VERSION = "5";

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'hello_bootstrap_table_twig_extension';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'hello_bootstrap_table_render',
                [$this, 'helloBootstrapTableRender'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new TwigFunction(
                'hello_bootstrap_table_js',
                [$this, 'helloBootstrapTableJs'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new TwigFunction(
                'hello_bootstrap_table_css',
                [$this, 'helloBootstrapTableCss'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            )
        ];
    }

    /**
     * @param Environment $twig
     * @param array $bootstrapTable
     * @return string
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function helloBootstrapTableRender(Environment $twig, $bootstrapTable)
    {
        return $twig->render('@HelloBootstrapTable/table/hello_bootstrap_table.html.twig', array(
            'table' => $bootstrapTable,
        ));
    }

    public function helloBootstrapTableJs(Environment $twig)
    {
        $assetFunction = $twig->getFunction('asset')->getCallable();
        return sprintf('<script src="%s?v=%s" defer></script>',
            call_user_func($assetFunction, "bundles/hellobootstraptable/bootstrap-table.js"),
            self::ASSET_VERSION
        );
    }

    public function helloBootstrapTableCss(Environment $twig)
    {
        $assetFunction = $twig->getFunction('asset')->getCallable();
        return sprintf('<link rel="stylesheet" href="%s?v=%s">',
            call_user_func($assetFunction, "bundles/hellobootstraptable/bootstrap-table.css"),
            self::ASSET_VERSION
        );
    }

}
