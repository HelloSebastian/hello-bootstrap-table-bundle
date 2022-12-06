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

    public function getName(): string
    {
        return 'hello_bootstrap_table_twig_extension';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
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
    public function helloBootstrapTableRender(Environment $twig, array $bootstrapTable): string
    {
        return $twig->render('@HelloBootstrapTable/table/hello_bootstrap_table.html.twig', array(
            'table' => $bootstrapTable,
        ));
    }

    public function helloBootstrapTableJs(Environment $twig): string
    {
        $assetFunction = $twig->getFunction('asset')->getCallable();
        return sprintf('<script src="%s?v=%s" defer></script>',
            call_user_func($assetFunction, "bundles/hellobootstraptable/bootstrap-table.js"),
            self::ASSET_VERSION
        );
    }

    public function helloBootstrapTableCss(Environment $twig): string
    {
        $assetFunction = $twig->getFunction('asset')->getCallable();
        return sprintf('<link rel="stylesheet" href="%s?v=%s">',
            call_user_func($assetFunction, "bundles/hellobootstraptable/bootstrap-table.css"),
            self::ASSET_VERSION
        );
    }

}
