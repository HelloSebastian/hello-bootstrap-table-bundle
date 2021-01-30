<?php


namespace HelloSebastian\HelloBootstrapTableBundle\Columns;


use HelloSebastian\HelloBootstrapTableBundle\Data\ActionButton;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActionColumn extends AbstractColumn
{
    protected function configureOutputOptions(OptionsResolver $resolver)
    {
        parent::configureOutputOptions($resolver);

        $resolver->setDefaults(array(
            "buttons" => array(),
            "sortable" => false,
            "switchable" => false,
            "searchable" => false,
            "formatter" => "defaultActionFormatter",
            "cellStyle" => "defaultActionCellStyle"
        ));

        $resolver->setRequired('buttons');
        $resolver->setAllowedTypes('buttons', 'array');
    }

    public function buildData($entity)
    {
        $this->buildButtons();
        $item = array();

        /**
         * @var int $key
         * @var ActionButton $button
         */
        foreach ($this->outputOptions['buttons'] as $key => $button) {
            $routeParams = array();
            foreach ($button->getRouteParams() as $param) {
                $routeParams[$param] = $this->propertyAccessor->getValue($entity, $param);
            }

            $item[] = array(
                'displayName' => $button->getDisplayName(),
                'classNames' => $button->getClassNames(),
                'route' => $this->router->generate($button->getRouteName(), $routeParams)
            );
        }

        return $item;
    }

    /**
     * Creates for each array item a ActionButton object and replace it in buttons array.
     */
    private function buildButtons()
    {
        foreach ($this->outputOptions['buttons'] as $key => $button) {
            if (is_array($button)) {
                $this->outputOptions['buttons'][$key] = new ActionButton($this, $button);
            }
        }
    }

}