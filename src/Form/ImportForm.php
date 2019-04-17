<?php
namespace MetadataEditor\Form\ImportForm;

use Omeka\Settings\UserSettings;
use Omeka\Form\Element\ItemSetSelect;
use Omeka\Form\Element\PropertySelect;
use Zend\Form\Form;
use Zend\Form\Element\Text;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\Form\Element;

class ImportForm extends Form
{

    public function init()
    {
        $this->setAttribute('action', 'findreplace/preview');//go to map on submit
        $this->setAttribute('method', 'post');

        $this->add([
                    'name' => 'property',
                    'type' => PropertySelect::class,
                    'attributes' => [
                        'id' => 'select-property',
                        'class' => 'chosen-select',
                        'multiple' => false,
                        'data-placeholder' => 'Select property', // @translate
                    ],
                    'options' => [
                        'label' => 'Select property', // @translate
                        'resource_value_options' => [
                            'resource' => 'property',
                            'query' => [],
                        ],
                    ],
        ]);
        $this->add([
                    'name' => 'find',
                    'type' => 'text',
                    'attributes' => [
                        'id' => 'find-text',
                        'data-placeholder' => 'Search for value', // @translate
                    ],
                    'options' => [
                        'label' => 'Original value', // @translate
                        'resource_value_options' => [
                            'resource' => 'value',
                            'query' => [],
                        ],
                    ],
        ]);
        $this->add([
                    'name' => 'replace',
                    'type' => 'text',
                    'attributes' => [
                        'id' => 'replace-text',
                        'data-placeholder' => 'Value to replace old text', // @translate
                    ],
                    'options' => [
                        'label' => 'Replacement value', // @translate
                        'resource_value_options' => [
                            'resource' => 'replace',
                            'query' => [],
                        ],
                    ],
        ]);
    }
}
