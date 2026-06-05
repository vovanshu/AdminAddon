<?php

namespace AdminAddon;

require_once __DIR__ . '/TraitCommon.php';

use AdminAddon\TraitCommon;

trait TraitGeneral
{

    use TraitCommon;

    protected $indexes = [];

    protected $FilterAllowEmpty = [];

    public function getIndex($name){

        if(isset($this->indexes[$name])){
            return $this->indexes[$name];
        }
        return 0;

    }

    public function raiseIndex($name){

        $current = $this->getIndex($name);
        $current++;
        $this->indexes[$name] = $current;
        return $current;

    }

    public function memFilterAllowEmpty($allowedEmpty = False)
    {

        if(!empty($allowedEmpty)){
            $this->FilterAllowEmpty = array_merge_recursive($this->FilterAllowEmpty, $allowedEmpty);
        }
        return $this->FilterAllowEmpty;

    }

    private function getResourceTemplate($id)
    {

        return $this->getServiceLocator()
            ->get('Omeka\ApiAdapterManager')
            ->get('resource_templates')
            ->findEntity($id);

    }

    public function getListIngesters()
    {

        $ingesters = [];
        $OMIM = $this->getServiceLocator()->get('Omeka\Media\Ingester\Manager');
        foreach ($OMIM->getRegisteredNames() as $ingester) {
            $ingesters[$ingester] = $OMIM->get($ingester)->getLabel();
        }
        return $ingesters;

    }

    private function isCompatibleAdminUI($conf, $controller, $action, $need = False)
    {

        foreach($conf as $key => $val){
            if($need){
                if($need == $key){
                    foreach($val as $k => $v){
                        if($controller == $k){
                            if(is_array($v)){
                                if(empty($v)){
                                    return True;
                                }elseif(in_array($action, $v)){
                                    return True;
                                }                                
                            }
                        }
                    }
                }
            }else{
                foreach($val as $k => $v){
                    if($controller == $k){
                        if(is_array($v)){
                            if(empty($v)){
                                return True;
                            }elseif(in_array($action, $v)){
                                return True;
                            }                                
                        }
                    }
                }
            }
        }
        return False;

    }

    private function modeAdminUI($controller, $action)
    {

        $mode = $this->getSets('adminaddon_mode_admin_ui');
        if($mode && $mode !== 'default' && $controller && $action){            
            $conf = $this->getConf('modes_admin_ui', $mode);
            if(!empty($conf['controllers']) && !empty($conf['actions'])){
                // if($this->isCompatibleAdminUI($conf['controllers'], $controller) && $this->isCompatibleAdminUI($conf['actions'], $action)){
                if($this->isCompatibleAdminUI($conf['controllers'], $controller, $action)){
                    return $conf;
                }
            }
        }
        return False;

    }

    private function hadIPInWLrecaptcha()
    {

        if(!empty($value = $this->getSets('recaptcha_ip_white_list'))){
            $list = explode("\r\n", $value);
            if($_SERVER['REMOTE_ADDR'] !== $_SERVER['SERVER_ADDR']){
                $curIP = ip2long($_SERVER['REMOTE_ADDR']);
            }else{
                $curIP = ip2long($_SERVER['HTTP_X_REAL_IP']);
            }
            // if(in_array($curIP, $list)){
                // return True;
            // }
            foreach($list as $v){
                if(stripos($v, '-') !== False){
                    $va = explode('-', $v);
                    if($curIP >= ip2long($va[0]) && $curIP <= ip2long($va[1])){
                        return True;
                    }
                }else{
                    if($curIP == ip2long($v)){
                        return True;
                    }
                }
            }
        }
        return False;

    }

    public function prepareQueriesInput($query, $config)
    {

        $def = ['page', 'advanced', 'submit', 'facets'];
        $filter = array_merge(array_column($config, 'name'), $def);
        $query = array_diff_key($query, array_flip($filter));
        if(!empty($query['property'])){
            foreach($config as $ops){
                if(!empty($ops['facetValues'])){
                    foreach($ops['facetValues'] as $val){
                        $key = $this->searchInArray($query['property'], ['property' => $ops['property_id'], 'text' => $val['value']]);
                        if(!empty($key) && !empty($query['property'][current($key)]['text'])){
                            unset($query['property'][current($key)]);
                        }
                    }
                }
            }
        }
        return $query;

    }

    public function getHiddenQueriesInput($query, $config)
    {

        $html = '';
        if(!empty($query)){
            $query = $this->prepareQueriesInput($query, $config);
            foreach (explode("\n", http_build_query($query, '', "\n")) as $nameValue) {
                if (!$nameValue) {
                    continue;
                }
                [$name, $value] = mb_strpos($nameValue, '=') === false ? [$nameValue, ''] : explode('=', $nameValue, 2);
                $name = urldecode($name);
                // if (is_null($value) || in_array($name, $skipNames)) {
                if (is_null($value)) {
                    continue;
                }
                $name = htmlspecialchars($name, ENT_COMPAT | ENT_HTML5);
                $value = htmlspecialchars(urldecode($value), ENT_COMPAT | ENT_HTML5);
                $html .= '<input type="hidden" name="' . $name . '" value="' . $value . '"' . "/>\n";
            }
            
        }
        return $html;

    }

    public function getConfigSearchFasets($siteSlug = False)
    {
        if($siteSlug){
            $siteID = $this->getSiteID($siteSlug);
            $rc = $this->getSiteSets('adminaddon_search_fasets', $siteID);
        }else{
            $rc = $this->getSets('adminaddon_search_fasets');
        }
        if(!empty($rc)){
            return parse_ini_string($rc, true, INI_SCANNER_TYPED);
        }
        return False;

    }

    public function prepareSearchFasets($query, $route = False)
    {
        
        $siteSlug = False;
        if($this->getStatus()->isSiteRequest()){
            $siteSlug = $query['site_slug'] = $this->getStatus()->getRouteParam('site-slug');
        }
        if(!empty($route['site-slug'])){
            $siteSlug = $query['site_slug'] = $route['site-slug'];
        }
        $config = $this->getConfigSearchFasets($siteSlug);
        if(!empty($config)){

            foreach($config as $k => $v){
                $q = $this->buidQueryValues($query, $v);
                if($v['type'] == 'range'){
                    
                    $rc = $this->getConnection()->executeQuery($q)->fetchAssociative();
                    if(!empty($rc)){
                        if($v['result'] == 'date-year'){
                            if (strlen($rc['min_value']) > 4) {
                                $rc['min_value']= date("Y", strtotime($rc['min_value']));
                            }
                            if (strlen($rc['max_value']) > 4) {
                                $rc['max_value']= date("Y", strtotime($rc['max_value']));
                            }
                        }
                        $config[$k]['options'] = $rc;
                    }
                }
                if(in_array($v['type'], ['checkboxe', 'select'])){
                    // $q = $this->buidQueryValues($query, $v);
                    $rc = $this->getConnection()->executeQuery($q)->fetchAll();
                    if(!empty($rc)){
                        foreach($rc as $id => $val){
                            if(stripos($val['type'], 'customvocab') !== False){
                                $val['label'] = $this->getLabelFromCustomVocab($val);
                            }else{
                                $val['label'] = $val['value'];
                            }
                            $rc[$id] = $val;
                        }
                        $config[$k]['facetValues'] = $rc;
                    }
                }
                $config[$k]['facetID'] = $k;
                $config[$k]['query'] = $query;
                if($this->isAppDevMode()){
                    $config[$k]['SQL'] = $q;
                }
            }
            return $config;
        }
        return False;        

    }

    private function buidQueryValues($query, $config){

        $q = 'SELECT';
        $queryType = 'list';
        if(stripos($config['type'], 'range') !== False){
            $queryType = 'range';
            $q .= ' MIN(`value`.`value`) AS min_value, MAX(`value`.`value`) AS max_value';
        }else{
            $q .= ' `value`.`id`, `value`.`value`, `value`.`type`, `value`.`lang`, `property`.`local_name`, `vocabulary`.`prefix`';
        }
        $q .= ' FROM `value`';
        $q .= ' LEFT JOIN `property` ON `property`.`id` = `value`.`property_id`';
        $q .= ' LEFT JOIN `vocabulary` ON `vocabulary`.`id` = `property`.`vocabulary_id`';
        $q .= ' LEFT JOIN `resource` ON `resource`.`id` = `value`.`resource_id`';
        if(!empty($query['site_slug'])){
            $q .= ' LEFT JOIN `item_site` ON `item_site`.`item_id` = `value`.`resource_id`';
            $query['site_id'] = $this->getSiteID($query['site_slug']);
        }
        if(!empty($config['query_limited']) && !empty($query['item_set_id'])){
            $q .= ' LEFT JOIN `item_item_set` ON `item_item_set`.`item_id` = `value`.`resource_id`';
        }
        if(!empty($query['property_id'])){
            $q .= ' WHERE `property`.`id` = \''.$query['property_id'].'\'';
        }elseif(!empty($config['property_id'])){
            $q .= ' WHERE `property`.`id` = \''.$config['property_id'].'\'';
        }elseif(!empty($query['term'])){
            $q .= $this->prepQueryWhereByTerm($query['term']);
        }elseif(!empty($config['term'])){
            $q .= $this->prepQueryWhereByTerm($config['term']);
        }
        if(!empty($config['query_limited'])){
            // echo $config['name'];
            // print_r($query);
            if(!empty($query['value'])){
                $q .= ' AND `value`.`value` LIKE \'%'.$query['value'].'%\'';
            }
            if(!empty($query['item_set_id'])){
                if(is_array($query['item_set_id'])){
                    $q .= ' AND `item_item_set`.`item_set_id` in (\''.join('\', \'', $query['item_set_id']).'\')';
                }else{
                    $q .= ' AND `item_item_set`.`item_set_id` = \''.$query['item_set_id'].'\'';
                }
            }
        }
        if(!empty($query['site_id'])){
            $q .= ' AND `item_site`.`site_id` = \''.$query['site_id'].'\'';
        }
        $q .= ' AND `resource`.`is_public` = 1';

        if($queryType == 'list'){
            $q .= ' GROUP BY `value`';
            $q .= ' ORDER BY `value`.`value` ASC';
            $q .= $this->prepQueryLimit($query, $config);
        }

        return $q.';';

    }

    private function prepQueryWhereByTerm($term){

        if(stripos($term, ':') !== False){
            $needed = explode(':', $term);
            $prefix = $needed[0];
            $name = $needed[1];
        }else{
            $prefix = False;
            $name = $term;
        }
        $q = ' WHERE `property`.`local_name` = \''.$name.'\'';
        if(!empty($prefix)){
            $q .= ' AND `vocabulary`.`prefix` = \''.$prefix.'\'';
        }
        return $q;
    }

    private function prepQueryLimit($query, $config){

        if(isset($query['limit'])){
            if(isset($query['offset'])){
                return ' LIMIT '.$query['offset'].', '.$query['limit'];
            }else{
                return ' LIMIT 0, '.$query['limit'];
            }
        }
        if(isset($config['limit'])){
            if($config['limit'] == '0'){
                return '';
            }else{
                if(isset($config['offset'])){
                    return ' LIMIT '.$config['offset'].', '.$config['limit'];
                }else{
                    return ' LIMIT 0, '.$config['limit'];
                }   
            }
        }
        return ' LIMIT 0, 20';

    }

    public function getLabelFromCustomVocab($val){

        try {
            $voc = explode(':', $val['type']);
            $vid = $voc[1];
            $response = $this->getApiManager()->read('custom_vocabs', $vid);
            if(!empty($response)){
                $resource = $response->getContent();
                $tems = $resource->listTerms();
                if(!empty($tems[$val['value']])){
                    return $tems[$val['value']];
                }else{
                    return $val['value'];
                }
            }
        } catch (\Exception $e) {
            return $val['value'];
        }

    }


    private function getSelectVocabularies()
    {

        $response = $this->api()->search('vocabularies');
        $vocabularies = $response->getContent();
        foreach ($vocabularies as $vocabulary){
            $result[$vocabulary->id()] = $vocabulary->label();
        }
        return $result;

    }


    private function getVocabularyID($data = Null)
    {

        $params = $this->params()->fromRoute();
        $id = Null;
        if(!empty($params['id'])){
            $id = $params['id'];
        }
        if(!empty($data['o:vocabulary'])){
            $id = $data['o:vocabulary']->jsonSerialize()['o:id'];
        }
        return $id;

    }

    private function getVocabularyEntry($id)
    {
        return $this->getApiAdapterManager('vocabularies')->findEntity($id);
    }
    
}
