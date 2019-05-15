<?php
namespace MetadataEditor\Form;

use Omeka\Settings\UserSettings;
use Omeka\Form\Element\ItemSetSelect;
use Omeka\Form\Element\PropertySelect;
//use MetadataEditor\Form\Element\MetadataEditor_Form_Element_Note;
use Zend\Form\Form;
//use Zend\Form\Fieldset;
use Zend\Form\Element\Text;
use Zend\Form\Element\Checkbox;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\Form\Element;

class MainForm extends Form
{

    /**
     * Initialize the form.
     *
     * @return void
     */
    public function init()
    {
        $this->setAttribute('id', 'metadata-editor-form');
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', 'metadataeditor/replace');
        $this->_registerElements();
    }

    /**
     * Populate the form
     *
     * @return void
     */
    private function _registerElements()
    {
        $this->add([
                    'name' => 'bmeCollectionId',
                    'type' => ItemSetSelect::class,
                    'attributes' => [
                        'id' => 'select-itemset',
                        'class' => 'chosen-select',
                        'multiple' => 'multiple',
                        'data-placeholder' => 'Item set',
                        'value' => '0',
                    ],
                    'options' => [
                        'label' => 'Select item set or sets.',
                        'resource_value_options' => [
                            'resource' => 'itemset',
                            'query' => [],
                        ],
                    ],
        ]);
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'item-select-meta',
            'attributes' => [
                'id' => 'item-select-meta',
            ],
            'options' => array(
                'label' => 'Use properties to select items.',
                'checked_value' => '1',
                'unchecked_value' => '0',
                'use_hidden_element' => true,
            )
        ));

        $this->add([
                    'name' => 'bulk-metadata-editor-element-id',
                    'type' => PropertySelect::class,
                    'attributes' => [
                        'id' => 'item-meta-selects',
                        'class' => 'chosen-select',
                        'multiple' => false,
                        'data-placeholder' => 'Collection', 
                        'value' => '0',
                    ],
                    'options' => [
                        'label' => 'Select Items by Metadata',
                        'resource_value_options' => [
                            'resource' => 'property',
                            'query' => [],
                        ],
                    ],
        ]);
        $this->add([
                    'name' => 'bulk-metadata-editor-compare',
                    'type' => 'select',
                    'attributes' => [
                        'id' => 'bulk-metadata-editor-compare',
                        'class' => 'chosen-select',
                        'multiple' => false,
                    ],
                    'options' => [
                        'label' => 'Operator', 
                        'value_options' => [
                            'eq' => 'Is Exactly',
                            'neq' => 'Is Not Exactly',
                            'in' => 'Contains',
                            'nin' => 'Does Not Contain',
                            'ex' => 'Has Any Value',
                            'nex' => 'Has No Value',
                        ],
                    ],
        ]);
        $this->add([
                    'name' => 'bulk-metadata-editor-selector',
                    'type' => 'text',
                    'attributes' => [
                        'id' => 'bulk-metadata-editor-selector',
                        'class' => 'chosen-select',
                        'multiple' => false,
                        'data-placeholder' => 'Selector', 
                    ],
                    'options' => [
                        'label' => 'Search text', 
                    ],
        ]);
        $this->add([
                    'name' => 'bulk-metadata-editor-case',
                    'type' => 'checkbox',
                    'attributes' => [
                        'id' => 'bulk-metadata-editor-case',
                        'class' => 'chosen-select',
                    ],
                    'options' => [
                        'label' => 'Match case?', 
                        'checked_value' => '1',
                        'unchecked_value' => '0',
                        'use_hidden_element' => true,
                    ],
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Button',
            'id' => 'preview-items-button',
            'name' => 'preview-items-button',
            'class' => 'preview-button',
            'options' => [
                'label' => 'Preview',
            ],                    
            'attributes' => [
                'id' => 'preview-items-button',
            ],
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Button',
            'id' => 'hideItemPreview',
            'name' => 'hideItemPreview',
            'class' => 'hideItemPreview',
            'options' => [
                'label' => 'Hide Preview',
            ],
            'attributes' => [
                'id' => 'hide-items-button',
            ],
        ]);

        $this->add([
                'name' => 'selectFields',
                'type' => PropertySelect::class,
                'attributes' => [
                    'id' => 'item-select-fields',
                    'class' => 'chosen-select',
                    'multiple' => 'true',
                    'data-placeholder' => 'Property', 
                    'value' => '0',
                ],
                'options' => [
                    'label' => 'Select the properties you would like to edit. You may select more than one.',
                    'resource_value_options' => [
                        'resource' => 'property',
                        'query' => [],
                    ],
                ],
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Button',
            'name' => 'preview-fields-button',
            'class' => 'preview-button',
            'options' => [
                'label' => 'Preview',
            ],
            'attributes' => [
                'id' => 'preview-fields-button',
            ],
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Button',
            'name' => 'hide-field-preview',
            'class' => 'hideItemPreview',
            'options' => [
                'label' => 'Hide Preview',
            ],
            'attributes' => [
                'id' => 'hide-field-preview',
            ],
        ]);

        $this->add([
                'name' => 'changesRadio',
                'type' => 'select',
                'attributes' => [
                    'id' => 'changesRadio',
                    'class' => 'changesDropdown',
                    'multiple' => false,//for single only option
                    'value' => '0',
                ],
                'options' => [
                    'label' => 'Type of Change',
                    'empty_option' => 'Choose a change type',
                    'value_options' => [
                        'replace' => 'Search and replace text',
                        'prepend' => 'Prepend text to existing metadata in the selected properties',
                        'append' => 'Append text to existing metadata in the selected properties',
                        'explode' => 'Use delimiter to separate elements into multiple properties',
                        'deduplicate' => 'Deduplicate & remove empty fields in the selected properties',
                    ],
                ],
        ]);
        $this->add([
                    'name' => 'bulk-metadata-editor-search-field',
                    'type' => 'text',
                    'attributes' => [
                        'id' => 'bulk-metadata-editor-search-field',
                        'class' => 'chosen-select',
                        'multiple' => false,
                        'data-placeholder' => 'Search for', 
                    ],
                    'options' => [
                        'label' => 'Original text: what you want to find and change', 
                    ],
        ]);
        $this->add([
                    'name' => 'bulk-metadata-editor-replace-field',
                    'type' => 'text',
                    'attributes' => [
                        'id' => 'bulk-metadata-editor-replace-field',
                        'class' => 'chosen-select',
                        'multiple' => false,
                        'data-placeholder' => 'Replace with', 
                    ],
                    'options' => [
                        'label' => 'Replacement text: what you want to to replace the original text', 
                    ],
        ]); 
        $this->add([//add checkbox info
                    'name' => 'regexp-field',
                    'type' => 'checkbox',
                    'attributes' => [
                        'id' => 'regexp-field',
                        'class' => 'chosen-select',
                        'multiple' => false,
                        'data-placeholder' => 'Regular Expression', 
                    ],
                    'options' => [
                        'label' => 'Use PHP regular expressions', 
                    ],
        ]); 
        $this->add([
                    'name' => 'bulk-metadata-editor-prepend-field',
                    'type' => 'text',
                    'attributes' => [
                        'id' => 'bulk-metadata-editor-prepend-field',
                        'class' => 'chosen-select',
                        'multiple' => false,
                        'data-placeholder' => 'Text to add in front of text', 
                    ],
                    'options' => [
                        'label' => 'Text to add before property', 
                    ],
        ]);  
        $this->add([
                    'name' => 'bulk-metadata-editor-append-field',
                    'type' => 'text',
                    'attributes' => [
                        'id' => 'bulk-metadata-editor-append-field',
                        'class' => 'chosen-select',
                        'multiple' => false,
                        'data-placeholder' => 'Text to add after text', 
                    ],
                    'options' => [
                        'label' => 'Text to add after after property', 
                    ],
        ]); 
        $this->add([
                    'name' => 'bulk-metadata-editor-explode-field',
                    'type' => 'text',
                    'attributes' => [
                        'id' => 'bulk-metadata-editor-explode-field',
                        'class' => 'chosen-select',
                        'multiple' => false,
                        'data-placeholder' => 'Delimiter (a character or phrase which separates fields)', 
                    ],
                    'options' => [
                        'label' => 'Delimiter (separates fields)', 
                    ],
        ]); 

        $this->add([
            'type' => 'Zend\Form\Element\Button',
            'name' => 'preview-changes-button',
            'class' => 'preview-button',
            'options' => [
                'label' => 'Preview',
            ],
            'attributes' => [
                'id' => 'preview-changes-button',
            ],
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Button',
            'name' => 'hide-changes-preview',
            'class' => 'hideItemPreview',
            'options' => [
                'label' => 'Hide Preview',
            ],
            'attributes' => [
                'id' => 'hide-changes-preview',
            ],
        ]);
        $this->add([
            'type' => 'checkbox',
            'id' => 'download',
            'name' => 'download',
            'options' => [
                'label' => 'Download CSV files?',
            ],
        ]);
        $this->add([
            'type' => 'submit',
            'id' => 'performButton',
            'options' => [
                'label' => 'Apply Edits Now',
            ],
        ]);


    }
}
