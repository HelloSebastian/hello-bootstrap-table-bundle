<?php

namespace HelloSebastian\HelloBootstrapTableBundle\Columns;

use Symfony\Component\OptionsResolver\OptionsResolver;

class LinkColumn extends AbstractColumn
{
    use FormatAttributeTrait;

    protected function configureOutputOptions(OptionsResolver $resolver)
    {
        parent::configureOutputOptions($resolver);

        $resolver->setDefaults(array(
            "routeName" => null,
            "routeParams" => array(),
            "attr" => array(),
            "formatter" => "defaultLinkFormatter"
        ));

        $resolver->isRequired('routeName');

        $resolver->setAllowedTypes('routeName', 'string');
        $resolver->setAllowedTypes('routeParams', 'array');
        $resolver->setAllowedTypes('attr', 'array');
    }

    public function buildData($entity)
    {
        if (!$this->propertyAccessor->isReadable($entity, $this->getDql())) {
            return $this->getEmptyData();
        }

        $routeParams = array();
        foreach ($this->outputOptions['routeParams'] as $routeParam => $paramPath) {
            $routeParams[$routeParam] = $this->propertyAccessor->getValue($entity, $paramPath);
        }

        return array(
            'displayName' => $this->propertyAccessor->getValue($entity, $this->getDql()),
            'route' => $this->router->generate($this->outputOptions['routeName'], $routeParams),
            'attr' => $this->formatAttr($this->outputOptions['attr'])
        );
    }
}
