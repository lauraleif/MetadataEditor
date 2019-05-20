<?php
namespace MetadataEditor\Controller;

use MetadataEditor\Form\MainForm;
use Omeka\Api\Manager as ApiManager;
use Omeka\Settings\UserSettings;
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
        $params = $this->getMainParams();
        $foundItems = $this->getItems($params);
        $response = $this->getResponse();
        $response->setContent(\Zend\Json\Json::encode($foundItems));
        return $response;
    }

    public function fieldsAction()
    {
        $view = new ViewModel;
        $params = $this->getMainParams();
        $foundItems = $this->getItems($params);
        $results = $this->fieldsFilter($params, $foundItems);
        $response = $this->getResponse();
        $response->setContent(\Zend\Json\Json::encode($results));
        return $response;
    }

    public function backupAction()
    {
        $view = new ViewModel;
        $params = $this->getMainParams();
        $foundItems = $this->getItems($params);
        if ($foundItems['count'] == 0) {
            $results = "No items selected. Please change your selections and try again.";
        } else {
            $results = $foundItems;
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
        $params = $this->getMainParams();
        $foundItems = $this->getItems($params);
        $filteredItems = $this->fieldsFilter($params, $foundItems);
        if (($filteredItems['count'] == 0)) {
            $results = $filteredItems;
        }elseif(array_key_exists('matchProperties', $filteredItems) ){
            $propertyInfo = $filteredItems['matchProperties'];
            $results = [];
            foreach ($filteredItems as $item) {
                if (is_array($item)) {
                    $changedItem = $this->changeItem($params, $item, $propertyInfo);
                    if ($changedItem !== $item) {
                        $item['newItem'] = $changedItem;
                        array_push($results, $item);
                    }
                }
            }
            $results['matchProperties'] = $propertyInfo;
        }
        if (! is_array($results)){
            $results = array('count' => 0);
        }
        $response = $this->getResponse();
        $response->setContent(\Zend\Json\Json::encode($results));
        return $response;
    }

    private function changeItem($params, $item, $propertyInfo)
    {
        if ($params['changesRadio']) {
            $change = $params['changesRadio'];
            $results = [];
            if (is_array($item)) {
                if (array_key_exists('o:id', $item)) {
                    $id = $item['o:id'];
                    foreach ($propertyInfo as $info) {
                        $term = $info['o:term'];
                        if (array_key_exists($term, $item)) {
                            $properties = $item[$term];
                            if ($change == "deduplicate") {
                                $item = $this->deduplicate($properties, $item, $term);
                            } elseif ($change == "replace") {
                                $search = $params['bulk-metadata-editor-search-field'];
                                $regexp = $params['regexp-field'];
                                $replace = $params['bulk-metadata-editor-replace-field'];
                                $item = $this->replace($item, $term, $properties, $search, $replace, $regexp);
                            } elseif ($change == "append") {
                                $append = $params['bulk-metadata-editor-append-field'];
                                $item = $this->append($item, $term, $properties, $append);
                            } elseif ($change == "prepend") {
                                $prepend = $params['bulk-metadata-editor-prepend-field'];
                                $item = $this->prepend($item, $term, $properties, $prepend);
                            } elseif ($change == "explode") {
                                if ($params['bulk-metadata-editor-explode-field']) {
                                    $explode = $params['bulk-metadata-editor-explode-field'];
                                    $item = $this->explode($item, $term, $properties, $explode);
                                }
                            }
                        }
                    }
                }
            }
        }
        return $item;
    }

    public function downloadAction()
    {
        $view = new ViewModel;
        $params = $this->getMainParams();
        $foundItems = $this->getItems($params);
        $filteredItems = $this->fieldsFilter($params, $foundItems);

        if ($filteredItems['count'] == 0) {
            $results = $filteredItems;
        } else {
            $propertyInfo = $filteredItems['matchProperties'];
            $results = [];
            foreach ($filteredItems as $item) {
                if (is_array($item)) {
                    $changedItem = $this->changeItem($params, $item, $propertyInfo);
                    array_push($results, $changedItem);
                }
            }
            $results['count'] = sizeOf($results);
        }

        $propertyNames = $this->getPropNames();
        $view->setVariable('collection', $results);
        $view->setVariable('properties', $propertyNames);
        $view->setTerminal(true);
        return $view;
    }

    public function replaceAction()
    {
        $view = new ViewModel;
        $params = $this->getMainParams();
        $foundItems = $this->getItems($params);
        $filteredItems = $this->fieldsFilter($params, $foundItems);
        if ($filteredItems['count'] == 0) {
            $results = $filteredItems;
        } else {
            $propertyInfo = $filteredItems['matchProperties'];
            $results = [];
            foreach ($filteredItems as $item) {
                if (is_array($item)) {
                    $changedItem = $this->changeItem($params, $item, $propertyInfo);
                    if (array_key_exists('o:id', $item)) {
                        $id = $item['o:id'];
                        if ($changedItem !== $item) {
                            $response = $this->api()->update('items', $id, $changedItem);
                            array_push($results, $changedItem);
                        }
                    }
                }
            }
            $view->setVariable('properties', json_encode($propertyInfo));
            $results['count'] = sizeOf($results);
        }

        $view->setVariable('collection', json_encode($results));
        return $view;
    }

    protected function getMainParams()
    {
        $params = [];
        $request = $this->getRequest();
        if ($request->isPost()) {
            $out = $_REQUEST;
        } else {
            $out = "Connection error or no search results";
        }
        $names = ['bmeCollectionId', 'item-select-meta', 'bulk-metadata-editor-element-id', 'bulk-metadata-editor-compare', 'bulk-metadata-editor-selector', 'selectFields', 'changesRadio', 'bulk-metadata-editor-search-field', 'bulk-metadata-editor-replace-field', 'regexp-field', 'bulk-metadata-editor-prepend-field', 'bulk-metadata-editor-append-field', 'bulk-metadata-editor-explode-field'];
        foreach ($names as $paramName) {
            if (isset($out[$paramName])) {
                $params[$paramName] = $out[$paramName];
            } else {
                $params[$paramName] = false;
            }
        }
        return $params;
    }

    protected function getPropertyName($propertyId)
    {
        $results = [];
        if (is_array($propertyId)) {
            foreach ($propertyId as $id) {
                $query = [
                    'id' => $id,
                ];
                $properties = $this->apiSearch($query, 'properties');
                foreach ($properties as $property) {
                    $result = [];
                    if ($property['o:id'] == $id) {
                        $result['o:label'] = $property['o:label'];
                        $result['o:term'] = $property['o:term'];
                        array_push($results, $result);
                    }
                }
            }
        } else {
            $query = [
                'id' => $propertyId,
            ];
            $properties = $this->apiSearch($query, 'properties');
            foreach ($properties as $property) {
                $result = [];
                if ($property['o:id'] == $propertyId) {
                    $result['o:label'] = $property['o:label'];
                    $result['o:term'] = $property['o:term'];
                    array_push($results, $result);
                }
            }
        }
        return $results;
    }

    private function getItems($params)
    {
        if ($params['bmeCollectionId']) {
            $itemset = $params['bmeCollectionId'];
            $useProperties = $params['item-select-meta'];
            if ($useProperties) {
                $properties = $params['bulk-metadata-editor-element-id'];
                $operator = $params['bulk-metadata-editor-compare'];
                $find = $params['bulk-metadata-editor-selector'];
                $foundItems = $this->getData($properties, $find, $operator, $itemset);
            } else {
                $foundItems = $this->getData("", "", "", $itemset);
            }
            $foundItems['count'] = sizeOf($foundItems);
            $foundItems['alt'] = "";
        } else {
            $foundItems = ["count" => 0, "alt" => "No item sets selected."];
        }
        return $foundItems;
    }

    protected function getData($properties, $find, $operator, $itemset)
    {
        $query = [];
        $query = $this->getQuery($find, $properties, $operator, $itemset);
        $out = $this->apiSearch($query, 'items');
        return $out;
    }

    private function getQuery($find, $properties, $operator, $itemset)
    {
        $query = [];
        $properties = $this->addQueryProperty($find, $properties, $operator, "and");
        $query['item_set_id'] = $itemset;
        $query['property'] = $properties;
        return $query;
    }

    private function addQueryProperty($find, $properties, $operator, $joiner)
    {
        $properties = [];
        $property = [
            "text" => $find,
            "property" => $properties,
            "type" => $operator,
            "joiner" => $joiner,
        ];
        array_push($properties, $property);
        return $properties;
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

    protected function propertyQuery($property, $find)
    {
        $property = [
            "text" => $find,
            "property" => $property,
            "type" => "in",
            "joiner" => "and",
        ];
        $query['property'] = $property;
        $results = $this->apiSearch($query, 'items');
        return $results;
    }

    private function fieldsFilter($params, $foundItems)
    {
        if ($foundItems['count'] !== 0) {
            if ($params['selectFields']) {
                $fields = $params['selectFields'];

                $propertyInfo = $this->getPropertyName($fields);
                $results = [];

                foreach ($propertyInfo as $prop) {
                    $term = $prop['o:term'];

                    foreach ($foundItems as $item) {
                        if (is_array($item)) {
                            if (array_key_exists($term, $item)) {
                                if (! in_array($item, $results)) {
                                    array_push($results, $item);
                                }
                            }
                        }
                    }
                }
                $results['count'] = sizeOf($results);
                $results['alt'] = "";
                $results['matchProperties'] = $propertyInfo;
            } else {
                $results = ["count" => 0, "alt" => "No items selected."];
            }
            return $results;
        }
    }

    private function getPropNames()
    {
        $query['term'] = "";
        $properties = $this->apiSearch($query, 'properties');
        $propertyNames = [];
        foreach ($properties as $property) {
            $propertyName = $property['o:term'];
            array_push($propertyNames, $propertyName);
        }
        return $propertyNames;
    }

    private function deduplicate($properties, $item, $term)
    {
        $values = [];
        foreach ($properties as $key => $property) {
             if (array_key_exists('@value', $property) && ($property['type'] == 'literal')) {
                $val = $property['@value'];
                if (trim($val) == '') {
                    unset($properties[$key]);
                } else {
                    if (!in_array($val, $values, true)) {
                        array_push($values, $val);
                    } else {
                        unset($item[$term][$key]);
                    }
                }
            }
        }
        return $item;
    }

    protected function replace($item, $term, $properties, $search, $replace, $regexp)
    {
        foreach ($properties as $key => $property) {
            if (array_key_exists('@value', $property) && ($property['type'] == 'literal')) {
                $value = $property['@value'];
                if ($regexp == 1) {
                    if (@preg_match($search, null) !== false) {
                        $newValue = preg_replace($search, $replace, $value);
                    } else {
                        $newValue = $value;
                    }
                } else {
                    $newValue = str_replace($search, $replace, $value);
                }
                if (($newValue !== $value) && ($newValue !== '')) {
                    $item[$term][$key]['@value'] = $newValue;
                } elseif ($newValue == '') {
                    unset($item[$term][$key]);
                }
            }
        }
        return $item;
    }

    protected function append($item, $term, $properties, $append)
    {
        foreach ($properties as $key => $property) {
            if (array_key_exists('@value', $property) && ($property['type'] == 'literal')) {
                $value = $property['@value'];
                $newValue = $value . $append;
                if (($newValue !== $value) && ($newValue !== '')) {
                    $item[$term][$key]['@value'] = $newValue;
                }
            }
        }
        return $item;
    }

    protected function prepend($item, $term, $properties, $prepend)
    {
        foreach ($properties as $key => $property) {
            if (array_key_exists('@value', $property) && ($property['type'] == 'literal')) {
                $value = $property['@value'];
                $newValue = $prepend . $value ;
                if (($newValue !== $value) && ($newValue !== '')) {
                    $item[$term][$key]['@value'] = $newValue;
                }
            }
        }
        return $item;
    }

    protected function explode($item, $term, $properties, $explode)
    {
        foreach ($properties as $key => $property) {
            if (array_key_exists('@value', $property) && ($property['type'] == 'literal')) {
                $value = $property['@value'];
                if ($explode !== '') {
                    if (strpos($value, $explode) !== false) {
                        $newValueSet = explode($explode, $value);
                        $blank = $property;
                        foreach ($newValueSet as $i => $newValue) {
                            $blank['@value'] = $newValue;
                            if ($newValue !== '') {
                                array_push($item[$term], $blank);
                            }
                        }
                        unset($item[$term][$key]);
                    }
                }
            }
        }
        return $item;
    }
}
