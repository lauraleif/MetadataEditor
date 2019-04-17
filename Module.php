<?php
namespace MetadataEditor;

use Omeka\Module\AbstractModule;
use Omeka\Entity\Job;
use Zend\ModuleManager\ModuleManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class Module extends AbstractModule
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}
