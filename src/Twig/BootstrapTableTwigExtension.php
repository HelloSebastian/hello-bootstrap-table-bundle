<?php

/*
 * This file is part of the SgDatatablesBundle package.
 *
 * (c) stwe <https://github.com/stwe/DatatablesBundle>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HelloSebastian\HelloBootstrapTableBundle\Twig;


use HelloSebastian\HelloBootstrapTableBundle\HelloBootstrapTable;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class BootstrapTableTwigExtension extends AbstractExtension
{
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

}
