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
        $foundItems = $this->getItems();
        if($foundItems==-1){
            $warning = array("count"=>0, "alt"=>$foundItems);
            $foundItems = $warning;
        }else{
            $foundItems['count'] = sizeOf($foundItems);
            $foundItems['alt'] = "";
        }
        $response = $this->getResponse();
        $response->setContent(\Zend\Json\Json::encode($foundItems));
        return $response;
    }

    private function getItems()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $out = $_REQUEST;
        } else {
            $out = "Connection error or no search results";
        }
        if(isset($out['bmeCollectionId'])){
            $itemset = $out['bmeCollectionId'];
            $useProperties = $out['item-select-meta'];
            if ($useProperties == 0){
                 $foundItems = $this->getData("", "", "", $itemset, 0);
            }else{
                $property = $out['bulk-metadata-editor-element-id'];
                $operator = $out['bulk-metadata-editor-compare'];
                $find = $out['bulk-metadata-editor-selector'];
                $foundItems = $this->getData($property, $find, $operator, $itemset, 0);
            }
            return $foundItems;
        }else{
            return -1;
        }
        
    }

    public function fieldsAction()
    {
        $view = new ViewModel;
        $foundItems = $this->getItems();
        $results = $this->fieldsFilter($foundItems);
        $response = $this->getResponse();
        $response->setContent(\Zend\Json\Json::encode($results));
        return $response;
    }


    private function fieldsFilter($foundItems)
    {   
        if($foundItems != -1){
             $out = $_REQUEST;

            if(isset($out['selectFields'])){
                $fields = $out['selectFields'];
            }else{
                $fields="";
            }
            $propertyInfo = $this->getPropertyName($fields);
            $results = array();
            
            foreach ($propertyInfo as $prop){
                $term = $prop['o:term'];

                foreach($foundItems as $item){
                    if(array_key_exists ($term, $item)){
                        if (! in_array($item, $results)){
                            array_push($results, $item);
                        }
                    }
                }
            }
            $results['count'] = sizeOf($results);
            $results['alt'] = "";
            $results['matchProperties'] = $propertyInfo; 
            
        }else{
            $results = array("count"=>0, "alt"=>$foundItems);
        }
        return $results;
    }

    public function backupAction()
    {
        $view = new ViewModel;
        $foundItems = $this->getItems();
        if($foundItems == -1){
            $results = "No items selected. Please change your selections and try again.";
        }else{
            $results = $foundItems;
        }
        $propertyNames = $this->getPropNames();
        $view->setVariable('collection', $results);
        $view->setVariable('properties', $propertyNames);
        $view->setTerminal(true);
        return $view;
    }

    private function getPropNames(){
        $api = $this->api;
        $query['term'] = "";
        $prop = $api->search("properties", $query)->getContent();
        $arr = json_encode($prop, true);
        $prop = json_decode($arr, true);

        $propertyNames = [];
        foreach ($prop as $property) {
            $p = $property['o:term'];
            array_push($propertyNames, $p);
        }
        return $propertyNames;
    }

    public function downloadAction()
    {
        $view = new ViewModel;
        $foundItems = $this->getItems();
        $filteredItems = $this->fieldsFilter($foundItems);

        if($filteredItems['count'] == 0){
            $results = "No items selected. Please change your selections and try again.";
        }else{
            $propertyInfo = $filteredItems['matchProperties'];

            $out = $_REQUEST;
            if(isset($out['changesRadio'])){

                $change = $out['changesRadio'];

                $results = array();
                foreach($filteredItems as $item){
                    if(is_array($item)){
                        if(array_key_exists('o:id', $item)){
                            $id = $item['o:id'];
                            foreach ($propertyInfo as $info){
                                $term = $info['o:term'];
                                if(array_key_exists ($term, $item)){
                                    $properties = $item[$term];
                                    if($change == "deduplicate"){
                                        $changedItem = $this->deduplicate($properties, $item, $term);
                                    }
                                    elseif($change == "replace"){
                                        $search = $out['bulk-metadata-editor-search-field'];
                                        $regexp = $out['regexp-field'];
                                        $replace = $out['bulk-metadata-editor-replace-field'];
                                        $changedItem = $this->replace($item, $term, $properties, $search, $replace, $regexp);

                                    }elseif($change == "append"){
                                        $append = $out['bulk-metadata-editor-append-field'];
                                        $changedItem = $this->append($item, $term, $properties, $append);
                                    }elseif($change == "prepend"){
                                        $prepend = $out['bulk-metadata-editor-prepend-field'];
                                         $changedItem = $this->prepend($item, $term, $properties, $prepend);
                                    }elseif($change == "explode"){
                                        $explode = $out['bulk-metadata-editor-explode-field'];
                                        $changedItem = $this->explode($item, $term, $properties, $explode);
                                    }else{
                                        $changedItem = $item;
                                    }
                                    if( $changedItem !== $item){
                                        array_push($results, $changedItem);
                                    }
                                    
                                }
                            }
                        }
                    }
                }
            }else{
                $results = "No change selected.";
            }
        }
        $propertyNames = $this->getPropNames();
        $view->setVariable('collection', $results);
        $view->setVariable('properties', $propertyNames);
        $view->setTerminal(true);
        return $view;
    }

    public function changesAction()
    {
        $view = new ViewModel;
        $foundItems = $this->getItems();
        $filteredItems = $this->fieldsFilter($foundItems);

        if($filteredItems['count'] == 0){
            $results = $filteredItems;
        }else{
            $propertyInfo = $filteredItems['matchProperties'];

            $out = $_REQUEST;
            if(isset($out['changesRadio'])){
                $change = $out['changesRadio'];

                $results = array();
                foreach($filteredItems as $item){
                    if(is_array($item)){
                        if(array_key_exists('o:id', $item)){
                            $id = $item['o:id'];
                            foreach ($propertyInfo as $info){
                                $term = $info['o:term'];
                                if(array_key_exists ($term, $item)){
                                    $properties = $item[$term];
                                    if($change == "deduplicate"){
                                        $changedItem = $this->deduplicate($properties, $item, $term);
                                    }
                                    elseif($change == "replace"){
                                        $search = $out['bulk-metadata-editor-search-field'];
                                        $regexp = $out['regexp-field'];
                                        $replace = $out['bulk-metadata-editor-replace-field'];
                                        $changedItem = $this->replace($item, $term, $properties, $search, $replace, $regexp);

                                    }elseif($change == "append"){
                                        $append = $out['bulk-metadata-editor-append-field'];
                                        $changedItem = $this->append($item, $term, $properties, $append);
                                    }elseif($change == "prepend"){
                                        $prepend = $out['bulk-metadata-editor-prepend-field'];
                                         $changedItem = $this->prepend($item, $term, $properties, $prepend);
                                    }elseif($change == "explode"){
                                        $explode = $out['bulk-metadata-editor-explode-field'];
                                        $changedItem = $this->explode($item, $term, $properties, $explode);
                                    }
                                    if( $changedItem !== $item){
                                        $item['newItem'] = $changedItem;
                                    }
                                    
                                }
                            }
                            array_push($results, $item);
                        }
                    }
                }
                $results['matchProperties'] = $propertyInfo;
            }else{
                $results = array("count"=>0);
            }
        }
        $response = $this->getResponse();
        $response->setContent(\Zend\Json\Json::encode($results));
        return $response;
    }


    private function deduplicate($properties, $item, $term)
    {
        $values = array();
        foreach($properties as $key=>$property){
             $val = $property['@value'];                                     
            if (trim($val) == ''){
                 unset($properties[$key]);
            }else{
                if(!in_array($val, $values, true)){
                    array_push($values, $val);
                }else{
                    unset($item[$term][$key]);
                }
            }            
        }
        return $item;
    }

    public function replaceAction()
    {
        $view = new ViewModel;
        $foundItems = $this->getItems();
        $filteredItems = $this->fieldsFilter($foundItems);
        if($filteredItems['count'] == 0){
            $results = $filteredItems;
        }else{
            $propertyInfo = $filteredItems['matchProperties'];

            $out = $_REQUEST;
            if(isset($out['changesRadio'])){
                $change = $out['changesRadio'];
                $search = $out['bulk-metadata-editor-search-field'];
                $replace = $out['bulk-metadata-editor-replace-field'];
                $regexp = $out['regexp-field'];
                $prepend = $out['bulk-metadata-editor-prepend-field'];
                $append = $out['bulk-metadata-editor-append-field'];
                $explode = $out['bulk-metadata-editor-explode-field'];

                $results = array();
                foreach($filteredItems as $item){
                    if(is_array($item)){
                        if(array_key_exists('o:id', $item)){
                            $id = $item['o:id'];
                            foreach ($propertyInfo as $info){
                                $term = $info['o:term'];
                                if(array_key_exists ($term, $item)){
                                    $properties = $item[$term];
                                    if($change == "deduplicate"){
                                        $item = $this->deduplicate($properties, $item, $term);
                                    }
                                    elseif($change == "replace"){
                                        $item = $this->replace($item, $term, $properties, $search, $replace, $regexp);
                                    }elseif($change == "append"){
                                        $item = $this->append($item, $term, $properties, $append);
                                    }elseif($change == "prepend"){
                                         $item = $this->prepend($item, $term, $properties, $prepend);
                                    }elseif($change == "explode"){
                                         $item = $this->explode($item, $term, $properties, $explode);
                                    }
                                }
                            }
                            $response = $this->api()->update('items', $id, $item);
                            array_push($results, $item);
                        }
                    }
                }
                $view->setVariable('properties', json_encode($propertyInfo));
            }else{
                $results = array("count"=>0, "error"=>"No changes selected.");
            }
        }
        $view->setVariable('collection', json_encode($results));
        return $view;
    }

    protected function replace($item, $term, $properties, $search, $replace, $regexp){
        foreach($properties as $key=>$property){
            $value = $property['@value'];
            if($regexp == 1){
                if(@preg_match($search, null) !== false){
                    $newValue = preg_replace($search, $replace, $value);
                }else{
                    $newValue = $value;
                }
            }else{
                $newValue = str_replace($search, $replace, $value);
            }
            // if(($newValue !== $value) && ($newValue !== '')){
            if(($newValue !== $value) && ($newValue !== '')){
                $item[$term][$key]['@value'] =  $newValue;
            }elseif($newValue == ''){
                unset($item[$term][$key]);
            }
        }
        return $item;
    }

    protected function append($item, $term, $properties, $append){
        foreach($properties as $key=>$property){
            $value = $property['@value'];
            $newValue = $value . $append;
            if(($newValue !== $value) && ($newValue !== '')){
                $item[$term][$key]['@value'] =  $newValue;
            }
        }
        return $item;
    }

    protected function prepend($item, $term, $properties, $prepend){
        foreach($properties as $key=>$property){
            $value = $property['@value'];
            $newValue = $prepend . $value ;
            if(($newValue !== $value) && ($newValue !== '')){
                $item[$term][$key]['@value'] =  $newValue;
            }
        }
        return $item;
    }
    
    protected function explode($item, $term, $properties, $explode){
        foreach($properties as $key=>$property){
            $value = $property['@value'];
            $newValueSet = explode($explode, $value);
            $blank = $property;
            foreach($newValueSet as $i =>$newValue){
                $blank['@value'] = $newValue;
                array_push($item[$term], $blank);
            }
            unset($item[$term][$key]);
        }
        return $item;
    }

    protected function getPropertyName($propertyId)
    {   
        $results = array();
        if(is_array($propertyId)){
            foreach($propertyId as $id){
                $query = array(
                    'id' => $id
                );
                $properties =  $this->apiSearch($query, 'properties');
                foreach($properties as $property){
                    $result = array();
                    if($property['o:id'] == $id){
                        $result['o:label'] = $property['o:label'];
                        $result['o:term'] = $property['o:term'];
                        array_push($results, $result);
                    }
                }
            }
        }else{
            $query = array(
                'id' => $propertyId
            );
            $properties =  $this->apiSearch($query, 'properties');
            foreach($properties as $property){
                $result = array();
                if($property['o:id'] == $propertyId){
                    $result['o:label'] = $property['o:label'];
                    $result['o:term'] = $property['o:term'];
                    array_push($results, $result);
                }
            }
        }
        return $results;
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

    protected function getData($property, $find, $operator, $itemset, $case)
    {
        $query = array();
        $query['search'] = $find;

        $query = $this->splitQuery($query, $property, $operator, $itemset, $case);

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

    private function splitQuery($query, $propertyId, $operator, $itemset, $case)
    {
        if (array_key_exists("search", $query)){
            $q = $query['search'];
            $word = $q;
            $upperCase = strtoupper ( $word );
            $lowerCase = strtolower( $word) ;

            $properties = array();
            $properties = $this->addPropertyQuery($q, $properties, $propertyId, $operator, "and");

            if($case == 0){
                $properties = $this->addPropertyQuery($upperCase, $properties, $propertyId, $operator, "or");
                $properties = $this->addPropertyQuery($lowerCase, $properties, $propertyId, $operator, "or");
            }

            $query['item_set_id'] = $itemset;
            $query['property'] = $properties;
            $query['submit'] = "Search";
            unset($query['search']);
        }
        
        return $query;
    }

    private function addPropertyQuery($word, $properties, $propertyId, $operator, $joiner)
    {
        $property = array(
            "text" => $word,
            "property" => $propertyId,
            "type" => $operator,
            "joiner" => $joiner,
        );
        array_push($properties, $property);
        return $properties;
    }
}
