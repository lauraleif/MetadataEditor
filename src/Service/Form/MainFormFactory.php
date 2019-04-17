<?php
namespace MetadataEditor\Service\Form;

use MetadataEditor\Form\MainForm;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class MainFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $form = new MainForm(null, $options);
        return $form;
    }
}
