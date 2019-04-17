<?php
namespace MetadataEditor\Form;

use Omeka\Settings\UserSettings;
use Omeka\Form\Element\ItemSetSelect;
use Omeka\Form\Element\PropertySelect;
//use MetadataEditor\Form\Element\MetadataEditor_Form_Element_Note;
use Zend\Form\Form;
//use Zend\Form\Fieldset;
use Zend\Form\Element\Text;
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
        $this->_registerElements();
    }

    /**
     * Populate the form
     *
     * @return void
     */
    private function _registerElements()
    {
        //$selectItems = new Fieldset('selectItems');
        $this->add([
            'type' =>'hidden', 
            'name' => 'callback', 
            array('value' => '')
        ]);

        // $text = new MetadataEditor_Form_Element_Note('myformnote');
        // $text->setValue("Testing text formnote");
        // $this->add($text);

        $this->add([
                    'name' => 'bmeCollectionId',
                    'type' => ItemSetSelect::class,
                    'attributes' => [
                        'id' => 'select-itemset',
                        'class' => 'chosen-select',
                        'multiple' => false,
                        'data-placeholder' => 'Collection', 
                        'value' => '0',// @translate
                    ],
                    'options' => [
                        'label' => 'Select itemset', // @translate
                        'resource_value_options' => [
                            'resource' => 'itemset',
                            'query' => [],
                        ],
                    ],
        ]);

        $this->add([
                    'name' => 'itemSelectMeta',
                    'type' => PropertySelect::class,
                    'attributes' => [
                        'id' => 'item-select-meta',
                        'class' => 'chosen-select',
                        'multiple' => false,
                        'data-placeholder' => 'Collection', 
                        'value' => '0',// @translate
                    ],
                    'options' => [
                        'label' => 'Select Items by Metadata', // @translate
                        'resource_value_options' => [
                            'resource' => 'property',
                            'query' => [],
                        ],
                    ],
        ]);
        $this->add([
                    'name' => 'rulebox',
                    'type' => 'text',
                    'attributes' => [
                        'class' => 'field',
                    ],
        ]);

        //not actually a text element, but
        //rendered with its own viewscript so it doesn't matter
        // $this->add('text', 'rulebox', array(
        //     'order' => 3,
        //     'decorators' => array(
        //         array(
        //             'ViewScript',
        //             array(
        //                 'viewScript' => 'form-rule-box.php',
        //                 'class' => 'field',
        //             )
        //         )
        //     )
        //));

        $this->add([
            'type' => 'Zend\Form\Element\Button',
            'id' => 'preview-items-button',
            'name' => 'preview-items-button',
            'class' => 'preview-button',
            'options' => [
                'label' => 'Preview Selected Items',
            ],
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Button',
            'id' => 'hideItemPreview',
            'name' => 'hideItemPreview',
            'class' => 'hideItemPreview',
            'options' => [
                'label' => 'Hide Item Preview',
            ],
        ]);


        //not actually a text element, but
        //rendered with its own viewscript so it doesn't matter
        // $this->add('text', 'itemPreviewDiv', array(
        //     'order' => 6,
        //     'decorators' => array(array(
        //         'ViewScript',
        //         array(
        //             'viewScript' => 'form-preview-div.php',
        //             'class' => 'field',
        //         )
        //     ))
        // ));
        //$selectProperties = new Fieldset('selectProperties');
        $this->add([
                'name' => 'selectFields',
                'type' => PropertySelect::class,
                'attributes' => [
                    'id' => 'item-select-meta',
                    'class' => 'chosen-select',
                    'multiple' => true,
                    'data-placeholder' => 'Select the metadata elements you would like to edit. You can select multiple values.', 
                    'value' => '0',// @translate
                ],
                'options' => [
                    'label' => 'Select Items by Metadata', // @translate
                    'resource_value_options' => [
                        'resource' => 'property',
                        'query' => [],
                    ],
                ],
        ]);

        $this->add([
            'type' => 'Zend\Form\Element\Button',
            'name' => 'preview-fields-button',
            'id' => 'preview-fields-button',
            'class' => 'preview-button',
            'options' => [
                'label' => 'Preview Selected Items',
            ],
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Button',
            'name' => 'hide-field-preview',
            'id' => 'hide-field-preview',
            'class' => 'hideItemPreview',
            'options' => [
                'label' => 'Hide Item Preview',
            ],
        ]);


        //not actually a text element, but
        //rendered with its own viewscript so it doesn't matter
        // $this->add('text', 'fieldPreviewDiv', array(
        //     'order' => 10,
        //     'decorators' => array(array(
        //         'ViewScript',
        //         array(
        //             'viewScript' => 'form-preview-div.php',
        //             'class' => 'field',
        //         )
        //     ))
        // ));


        //$selectChanges = new Fieldset('selectChanges');
        $this->add([
                'name' => 'changesRadio',
                'type' => 'radio',
                'attributes' => [
                    'id' => 'item-select-meta',
                    'class' => 'chosen-select',
                    'multiple' => true,
                    'data-placeholder' => 'Select the metadata elements you would like to edit. You can select multiple values.', 
                    'value' => '0',// @translate
                ],
                'options' => [
                    'label' => 'Select Items by Metadata', // @translate
                    'value_options' => [
                        'replace' => 'Search and replace text',
                        'add' => 'Add a new metadatum in the selected field',
                        'prepend' => 'Prepend text to existing metadata in the selected fields',
                        'append' => 'Append text to existing metadata in the selected fields',
                        'explode' => 'Explode metadata with a separator in multiple elements in the selected fields',
                        'deduplicate' => 'Deduplicate and remove empty metadata in the selected fields',
                        'deduplicate-files' => 'Deduplicate files of selected items by hash',
                        'delete' => 'Delete all existing metadata in the selected fields',
                    ],
                ],
        ]);


        $this->add([
            'type' => 'Zend\Form\Element\Button',
            'name' => 'preview-changes-button',
            'id' => 'preview-changes-button',
            'class' => 'preview-button',
            'options' => [
                'label' => 'Preview Selected Items',
            ],
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Button',
            'name' => 'hide-changes-preview',
            'id' => 'hide-changes-preview',
            'class' => 'hideItemPreview',
            'options' => [
                'label' => 'Hide Item Preview',
            ],
        ]);

        //not actually a text element, but
        //rendered with its own viewscript so it doesn't matter
        // $this->add('text', 'changesPreviewDiv', array(
        //     'order' => 14,
        //     'decorators' => array(
        //         array(
        //             'ViewScript',
        //             array(
        //                 'viewScript' => 'form-preview-div.php',
        //                 'class' => 'field',
        //             )
        //         )
        //     )
        // ));

        // $this->add('checkbox', 'useBackgroundJob', array(
        //     'label' => __('Background Job'),
        //     'id' => 'use-background-job',
        //     'description' => __('If checked, the job will be processed in the background.'),
        //     'value' => '1',
        //     'order' => 15,
        // ));

        //The following elements will be re-ordered in javascript
        //gotta create a new element that can be hidden and shown and junk?

        // $this->add('text', 'bmeSearch', array(
        //     'label' => __('Search for:'),
        //     'id' => 'bulk-metadata-editor-search',
        //     'class' => 'elementHidden',
        //     'description' => __('Input text you want to search for '),
        // ));
        // $this->add('text', 'bmeReplace', array(
        //     'label' => __('Replace with:'),
        //     'id' => 'bulk-metadata-editor-replace',
        //     'class' => 'elementHidden',
        //     'description' => __('Input text you want to replace with '),
        // ));
        // $this->add('checkbox', 'regexp', array(
        //     'description' => __('Use regular expressions'),
        //     'id' => 'regexp',
        //     'class' => 'elementHidden',
        //     'value' => 'true',
        // ));
        // $this->add('text', 'bmeAdd', array(
        //     'label' => __('Text to Add'),
        //     'id' => 'bulk-metadata-editor-add',
        //     'class' => 'elementHidden',
        //     'description' => __('Input text you want to add as metadata'),
        // ));
        // $this->add('text', 'bmePrepend', array(
        //     'label' => __('Text to Prepend'),
        //     'id' => 'bulk-metadata-editor-prepend',
        //     'class' => 'elementHidden',
        //     'description' => __('Input text you want to prepend to metadata'),
        // ));
        // $this->add('text', 'bmeAppend', array(
        //     'label' => __('Text to Append'),
        //     'id' => 'bulk-metadata-editor-append',
        //     'class' => 'elementHidden',
        //     'description' => __('Input text you want to append to metadata'),
        // ));
        // $this->add('text', 'bmeExplode', array(
        //     'label' => __('Separator'),
        //     'id' => 'bulk-metadata-editor-explode',
        //     'class' => 'elementHidden',
        //     'description' => __('The separator used to explode metadata (usually ",", ";" or "|", or any chain of characters).')
        //         . ' ' . __('The html tags will be stripped before process.'),
        // ));

        // $this->addDisplayGroup(
        //     array(
        //         'bmeCollectionId',
        //         'itemSelectMeta',
        //         //'rulebox',
        //         'previewItemsButton',
        //         'hideItemPreview',
        //         //'itemPreviewDiv',
        //     ),
        //     //'bmeItemsSet',
        //     array(
        //         'legend' => __('Step 1: Select Items'),
        //         'class' => 'bmeFieldset',
        // ));

        // $this->addDisplayGroup(
        //     array(
        //         'selectFields[]',
        //         'previewFieldsButton',
        //         'hideFieldPreview',
        //         'fieldPreviewDiv',
        //     ),
        //     'bmeFieldsSet',
        //     array(
        //         'legend' => __('Step 2: Select Fields'),
        //         'class' => 'bmeFieldset',
        // ));

        // $this->addDisplayGroup(
        //     array(
        //         'changesRadio',
        //         'previewChangesButton',
        //         'bmePrepend',
        //         'bmeAppend',
        //         'bmeExplode',
        //         'regexp',
        //         'bmeAdd',
        //         'bmeSearch',
        //         'bmeReplace',
        //         'hideChangesPreview',
        //         'changesPreviewDiv',
        //     ),
        //     'bmeChangesSet',
        //     array(
        //         'legend' => __('Step 3: Define Changes'),
        //         'description' => __('Define Edits to Apply'),
        //         'class' => 'bmeFieldset',
        // ));

        // $this->addDisplayGroup(
        //     array(
        //         'useBackgroundJob',
        //     ),
        //     'bmeJob'
        // );

        // if(version_compare(OMEKA_VERSION, '2.2.1') >= 0)
        //     $this->add('hash', 'bulk_editor_token');

        //  $this->add($selectItems);
        // $this->add($selectProperties);
        // $this->add($selectChanges);

        $this->add([
            'type' => 'submit',
            'id' => 'performButton',
            'options' => [
                'label' => 'Apply Edits Now',
            ],
        ]);

    // }
    }

    // // /**
    // //  * Overrides standard omeka form behavior to tweak display
    // //  * and fix radio display eccentricity
    // //  *
    // //  * @return void
    // //  */
    // // public function applyOmekaStyles()
    // // {
    // //     foreach ($this->getElements() as $element) {

    // //         if ($element instanceof Zend_Form_Element_Submit) {
    // //             // All submit form elements should be wrapped in a div with
    // //             // class "field".
    // //             $element->setDecorators(array(
    // //                 'ViewHelper',
    // //                 array('HtmlTag', array('tag' => 'div'))
    // //             )
    // //             );

    // //         } elseif ($element->getAttrib('class') == 'elementHidden') {
    // //             $element->getDecorator('FieldTag')->setOption('class', 'field bmeHidden');
    // //             $id = $element->getAttrib('id');

    // //             $element->getDecorator('FieldTag')->setOption('id', $id . '-field');


    // //         } elseif ($element instanceof Zend_Form_Element_Hidden
    // //                 || $element instanceof Zend_Form_Element_Hash) {
    // //             $element->setDecorators(array('ViewHelper'));
    // //         }
    // //     }
    // // }

    // // /**
    // //  * Get an array to be used in 'select' elements containing all collections.
    // //  *
    // //  * @return array $collectionOptions Array of all collections and their
    // //  * IDs, which will be used to populate a dropdown menu on the main view
    // //  */
    // // private function _getCollectionOptions()
    // // {
    // //     $options = get_table_options('Collection');
    // //     unset($options['']);
    // //     // Add the id of collections to simplify selection with similar names.
    // //     array_walk($options, function (&$value, $key) {
    // //         $value = '(#' . $key . ') ' . $value;
    // //     });
    // //     return array('0' => __('All Collections')) + $options;
    // // }

    // // /**
    // //  * Get an array to be used in html select input containing all elements.
    // //  *
    // //  * @return array $elementOptions Array of options for a dropdown
    // //  * menu containing all elements applicable to records of type Item
    // //  */
    // // private function _getElementOptions()
    // // {
    // //     /*
    // //     $options = get_table_options('Element', null, array(
    // //         'record_types' => array('Item', 'All'),
    // //         'sort' => 'alphaBySet')
    // //     );
    // //     unset($options['']);
    // //     return $options;
    // //     */

    // //     $db = get_db();
    // //     $sql = "
    // //     SELECT es.name AS element_set_name, e.id AS element_id,
    // //     e.name AS element_name, it.name AS item_type_name
    // //     FROM {$db->ElementSet} es
    // //     JOIN {$db->Element} e ON es.id = e.element_set_id
    // //     LEFT JOIN {$db->ItemTypesElements} ite ON e.id = ite.element_id
    // //     LEFT JOIN {$db->ItemType} it ON ite.item_type_id = it.id
    // //     WHERE es.record_type IS NULL OR es.record_type = 'Item'
    // //     ORDER BY es.name, it.name, e.name";
    // //     $elements = $db->fetchAll($sql);
    // //     $options = array();
    // //     //        $options = array('' => __('Select Below'));
    // //     foreach ($elements as $element) {
    // //         $optGroup = $element['item_type_name']
    // //             ? __('Item Type') . ': ' . __($element['item_type_name'])
    // //             : __($element['element_set_name']);
    // //         $value = __($element['element_name']);

    // //         $options[$optGroup][$element['element_id']] = $value;
    // //     }
    // //     return $options;
    // // }
}
