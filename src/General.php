<?php

namespace AdminAddon;

require_once __DIR__ . '/Common.php';

use AdminAddon\Common;

trait General
{

    use Common;

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

}
