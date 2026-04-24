<?php

namespace AdminAddon;

require_once __DIR__ . '/Common.php';

use AdminAddon\Common;

trait General
{

    use Common;

    protected $indexes = [];

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
                            if(in_array($action, $v)){
                                return True;
                            }
                        }
                    }
                }
            }else{
                foreach($val as $k => $v){
                    if($controller == $k){
                        if(in_array($action, $v)){
                            return True;
                        }
                    }
                }
            }
        }
        return False;

    }

    private function modeAdminUI($controller, $action)
    {

        $mode = $this->getSets('mode_admin_ui');
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

    public function getHiddenQueriesInput($query, $config)
    {
        if(!empty($query)){    
            
            $filter = array_column($config, 'name');

            $def = ['page', 'advanced'];
            $filter = array_merge($filter, $def);
            foreach($query as $name => $val){
                if(!in_array($name, $filter)){
                    if(!empty($val)){
                        if(is_array($val)){

                        }else{
                            echo '<input name="'.$name.'" type="hidden" data-type="query" value="'.$val.'"/>';
                        }
                    }
                    
                }                
            }
        }

    }

    public function getConfigSearchFasets($admin = False)
    {
        if($admin){
            $rc = $this->getSets('search_fasets');
        }else{
            $rc = $this->getSiteSets('search_fasets');
        }
        if(!empty($rc)){
            return parse_ini_string($rc, true, INI_SCANNER_TYPED);
        }
        return False;

    }

    public function prepareSearchFasets($query, $admin = False){
        
        $config = $this->getConfigSearchFasets($admin);
        if(!empty($config)){

            foreach($config as $k => $v){
                if($v['type'] == 'range'){
                    $q = $this->createQueryRange($query, $v);
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
                    $q = $this->buidQueryValues($query, $v);
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
            $siteID = $this->getSiteID($query['site_slug']);
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
            if(!empty($query['value'])){
                $q .= ' AND `value`.`value` LIKE \'%'.$query['value'].'%\'';
            }
        }
        if(!empty($siteID)){
            $q .= ' AND `item_site`.`site_id` = \''.$siteID.'\'';
        }
        $q .= ' AND `resource`.`is_public` = 1';

        if($queryType == 'list'){
            $q .= ' GROUP BY `value`';
            $q .= ' ORDER BY `value`.`value` ASC';
            $q .= $this->prepQueryLimit($query, $config);
        }

        return $q.';';

    }

    private function createQueryRange($query, $config){

        $q = 'SELECT';
        $q .= ' MIN(`value`.`value`) AS min_value, MAX(`value`.`value`) AS max_value';
        $q .= ' FROM `value`';
        $q .= ' LEFT JOIN `property` ON `property`.`id` = `value`.`property_id`';
        $q .= ' LEFT JOIN `vocabulary` ON `vocabulary`.`id` = `property`.`vocabulary_id`';
        $q .= ' LEFT JOIN `resource` ON `resource`.`id` = `value`.`resource_id`';
        if(!empty($query['site_slug'])){
            $q .= ' LEFT JOIN `item_site` ON `item_site`.`item_id` = `value`.`resource_id`';
            $siteID = $this->getSiteID($query['site_slug']);
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
        if( $config['query_limited'] ){
            if(!empty($query['value'])){
                $q .= ' AND `value`.`value` LIKE \'%'.$query['value'].'%\'';
            }
        }
        if(!empty($siteID)){
            $q .= ' AND `item_site`.`site_id` = \''.$siteID.'\'';
        }
        $q .= ' AND `resource`.`is_public` = 1';
        $q .= ';';

        return $q;

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
                }
            }
        } catch (\Exception $e) {
            return $val['value'];
        }

    }

}
