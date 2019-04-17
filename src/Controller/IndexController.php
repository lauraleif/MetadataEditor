<?php
namespace MetadataEditor\Controller;

use MetadataEditor\Form\MainForm;
use finfo;
use Omeka\Entity\Item;
use Omeka\Api\Representation\ItemRepresentation;
use Omeka\Api\Manager as ApiManager;
use Omeka\Service\Exception\ConfigException;
use Omeka\Settings\UserSettings;
use Omeka\Stdlib\Message;
use Omeka\Module\AbstractModule;
use Omeka\Entity\Job;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    /**
     * @var string
     */
    protected $tempPath;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var UserSettings
     */
    protected $userSettings;
    protected $serviceLocator;
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var ApiManager
     */
    protected $api;

    /**
     * @param array $config
     * @param Manager $api
     * @param UserSettings $userSettings
     */
    public function __construct(array $config, ApiManager $api, UserSettings $userSettings, $serviceLocator)
    {
        $this->config = $config;
        $this->api = $api;
        $this->userSettings = $userSettings;
        $this->serviceLocator = $serviceLocator;
    }

    public function indexAction()
    {
        $view = new ViewModel;
        $form = $this->getForm(MainForm::class);
        $view->form = $form;
        return $view;
    }

    public function previewAction()
    {
        $view = new ViewModel;
        $request = $this->getRequest();
        if ($request->isPost()) {
            $out = $_REQUEST;
            
        } else {
            $out = "Connetion error or no search results";
        }

        // $property = $out['property'];
        // $propertyName = $this->getPropertyName($property);

        // $find = $out['find'];
        // $replace = $out['replace'];
        // $foundItems = $this->getData($property, $find);
        // $outputItems = $this->getHighlights($foundItems, $propertyName, $find, $replace);

        // $count = sizeOf($foundItems);

        // $view->setVariable('collection', $outputItems);
        // $view->setVariable('property', $property);
        // $view->setVariable('propertyName', $propertyName);
        // $view->setVariable('find', $find);
        // $view->setVariable('replace', $replace);
        // $view->setVariable('count', $count);
        return $view;
    }

    public function replaceAction()
    {
        $view = new ViewModel;
        $request = $this->getRequest();
        if ($request->isPost()) {
            $out = $_REQUEST;
            
        } else {
            $out = "Connection error or no search results";
        }
        // $find = $out['find'];
        // $replace = $out['replace'];
        // $property = $out['property'];
        // $propertyName = $out['propertyName'];
        // $foundItems = $this->getData($property, $find);
        // echo sizeOf($foundItems);
        // $api = $this->api;
        // foreach($foundItems as $item){
        //     $properties = $item[$propertyName];
        //     $id = $item['o:id'];
        //     $fileData = array();
        //     foreach($properties as $key => $itemProperty){
        //         $value = $itemProperty['@value'];
        //         $replacement = $this->replacePhrase($value, $find, $replace);
        //         $item[$propertyName][$key]['@value'] = $replacement;
        //     }
        //     echo json_encode($item);
        //     $response = $this->api()->update('items', $id, $item);
        // }
        $view->setVariable('collection', "Your items have been updated.");
        return $view;
    }

    protected function replacePhrase($value, $find, $replace)
    {
        $all = $this ->findAll($value, $find);
        $length = strlen($find);
        $foundCount = sizeOf($all);
        $replacement = "";
        if ($foundCount > 0 ){
            foreach($all as $key => $foundAt){
                if ($key > 0){
                    $previous = $key - 1;
                    $start = $all[$previous];
                    $phrase = substr($value, $start + $length, $foundAt);
                    $replacement = $replacement  . $phrase . $replace ;                        
                }else{
                    $start = 0;
                    $replacement = substr($value, 0, $foundAt) . $replace ;
                }
            }
            $last = $all[$foundCount - 1];
            $replacement = $replacement . substr($value, $last + $length);
        }
        return $replacement;
    }

    protected function getHighlights($items, $property, $find, $replace)
    {
        $highlights = array();

        foreach($items as $item){
            $properties = $item[$property];
            $out = "";
            $replaced = "";
            $insertBefore = "<span style=\"font-size:1.2em;font-weight:800;color:red;\">";
            $insertAfter = "</span>";
            foreach($properties as $itemProperty){
                $value = $itemProperty['@value'];
                $out = $this->replacePhrase($value, $find, $insertBefore . $find . $insertAfter);
                $replaced = $this->replacePhrase($value, $find, $insertBefore . $replace . $insertAfter);
            }
            $item['found'] = $out;
            $item['replace'] = $replaced;
            array_push($highlights, $item);
        }
        return $highlights;
    }

    protected function findAll($haystack, $needle){
        $offset = 0;
        $all = array();
        while(($found = strpos($haystack, $needle, $offset)) !== FALSE){
            $offset = $found + 1;
            array_push($all, $found);
        }
        return $all; 
    }

    protected function getPropertyName($propertyId)
    {   
        $query = array(
            'id' => $propertyId
        );   
        $propertyName = "";
        $properties = $this->apiSearch($query, 'properties');
        foreach($properties as $property){
            if ($property['o:id'] == $propertyId){
                $propertyName = $property['o:term'];
            }
        }
        //$propertyName = json_encode($propertyName);
        return $propertyName;
    }

    protected function propertyQuery($property, $find)
    {
        $property = array(
            "text" => $find,
            "property" => $property,
            "type" => "in",
            "joiner" => "and"
        );
        $query['property'] = $property;
        $results = $this->apiSearch($query, 'items');
        return $results;
    }

    protected function getData($property, $find)
    {
        $query = array();
        $query['search'] = $find;

        $query = $this->splitQuery($query, $property);

        $response = $this->api()->search('items', $query);
        $items = $response->getContent();
        $out = $this->formatData($items);
        return $out;
    }

    protected function apiSearch($query, $type)
    {
        $api = $this->api;
        $items = $api->search($type, $query)->getContent();
        $out = $this->formatData($items);
        return $out;
    }
    protected function formatData($rawData)
    {
        $arr = json_encode($rawData, true);
        $items = json_decode($arr, true);
        return $items ;
    }

    private function splitQuery($query, $propertyId)
    {
        if (array_key_exists("search", $query)){
            $q = $query['search'];
            $word = $q;

            $properties = array();
            $properties = $this->addPropertyQuery($q, $properties, $propertyId);

            $query['property'] = $properties;
            $query['submit'] = "Search";
            unset($query['search']);
        }
        
        return $query;
    }

    private function addPropertyQuery($word, $properties, $propertyId)
    {
        $property = array(
            "text" => $word,
            "property" => $propertyId,
            "type" => "in",
            "joiner" => "and",
        );
        array_push($properties, $property);
        return $properties;
    }
    // public function downloadAction()
    // {
    //     $view = new ViewModel;
        
    //     $request = $this->getRequest();
    //     if ($request->isPost()) {
    //         $out = $_REQUEST["add_to_item_set"][0];
    //     } else {
    //         $out = "Connetion error or no search results";
    //     }

    //     $field = 'item_set_id';
    //     $items = $this->getData($out, 'item_set_id', 'items');

    //     $itemMedia = array();
    //     foreach ($items as $item) {
    //         if (array_key_exists('o:media', $item) && !empty($item['o:media'])) {
    //             $mediaIds = $item['o:media'];
    //             $mediaOut = "";
    //             $mediaJson = "";
    //             foreach ($mediaIds as $mediaId) {
    //                 $id = $mediaId['o:id'];
    //                 $media = $this->getData($id, 'id', 'media');

    //                 foreach ($media as $medium) {
    //                     $mediaOut = $mediaOut . $medium['o:filename'] . ";";
    //                     $mediaJson = $mediaJson . json_encode($medium) . ";";
    //                     $item['media:link'] = $mediaOut;
    //                     $item['media:full'] = $mediaJson;
    //                 }
    //             }
    //         } else {
    //             $item['media:link'] = "";
    //             $item['media:full'] = "";
    //         }
    //         array_push($itemMedia, $item);
    //     }

    //     $properties = $this->getData("", 'term', 'properties');

    //     $propertyNames = array();
    //     foreach ($properties as $property) {
    //         $p = $property['o:term'];
    //         array_push($propertyNames, $p);
    //     }

    //     $view->setVariable('collection', $itemMedia);
    //     $view->setVariable('properties', $propertyNames);
    //     $view->setTerminal(true);
    //     return $view;
    // }
}
