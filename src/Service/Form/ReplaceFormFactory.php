<?php
namespace FindReplace\Service\Form;

use FindReplace\Form\ReplaceForm;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ReplaceFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $form = new ReplaceForm(null, $options);
        return $form;
    }
}
