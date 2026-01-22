<?php declare(strict_types=1);

namespace AdminAddon;

// use Doctrine\ORM\EntityManager;
// use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
// use Omeka\Api\Manager as ApiManager;
// use Omeka\Api\Representation\AbstractEntityRepresentation;
// use Omeka\Entity\AbstractEntity;
// use Omeka\Entity\Value;
// use Omeka\Permissions\Acl;
// use Omeka\Api\Request;
// use Omeka\Api\Response;
// use Interop\Container\ContainerInterface;

trait Common
{

    protected $configName = 'adminaddon';

    protected $mvcEvent;

    protected $services;

    protected $requestedName;

    protected $acl;

    protected $connection;

    protected $settings;

    protected $config;

    protected $apiManager;

    protected $ApiAdapter = [];

    protected $entityManager;

    protected $translator;

    protected $formElementManager;

    protected $logger;


    public function setMvcEvent($mvcEvent)
    {
        $this->mvcEvent = $mvcEvent;
    }

    public function getMvcEvent()
    {
        return $this->mvcEvent;
    }

    public function setServiceLocator($serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
    
    public function getAdapter($resourceName)
    {

        if($this->serviceLocator){
            if(empty($this->ApiAdapter[$resourceName])){
                $this->ApiAdapter[$resourceName] = $this->getServiceLocator()->get('Omeka\ApiAdapterManager')->get($resourceName);
            }
            return $this->ApiAdapter[$resourceName];
        }
        return;

    }

    public function getConnection()
    {

        if($this->getServiceLocator()){
            if(!$this->connection){
                $this->connection = $this->getServiceLocator()->get('Omeka\Connection');
            }
            return $this->connection;
        }
        return;

    }

    public function getLogger()
    {

        if($this->getServiceLocator()){
            if(!$this->logger){
                $this->logger = $this->getServiceLocator()->get('Omeka\Logger');
            }
            return $this->logger;
        }
        return;

    }

    public function getApiManager()
    {

        if($this->getServiceLocator()){
            if(!$this->apiManager){
                $this->apiManager = $this->getServiceLocator()->get('Omeka\ApiManager');
            }
            return $this->apiManager;
        }
        return;

    }

    public function getEntityManager()
    {

        if($this->getServiceLocator()){
            if(!$this->entityManager){
                $this->entityManager = $this->getServiceLocator()->get('Omeka\EntityManager');
            }
            return $this->entityManager;
        }
        return;

    }

    public function getAcl()
    {

        if($this->getServiceLocator()){
            if(!$this->acl){
                $this->acl = $this->getServiceLocator()->get('Omeka\Acl');
            }
            return $this->acl;
        }
        return;

    }

    public function getSettings()
    {

        if($this->getServiceLocator()){
            if(!$this->settings){
                $this->settings = $this->getServiceLocator()->get('Omeka\Settings');
            }
            return $this->settings;
        }
        return;

    }

    public function getUserSettings()
    {

        if($this->getServiceLocator()){
            if(!$this->userSettings){
                $this->userSettings = $this->getServiceLocator()->get('Omeka\Settings\User');
            }
            return $this->userSettings;
        }
        return;

    }

    public function getConfigs()
    {

        if($this->getServiceLocator()){
            if(!$this->config){
                $this->config = $this->getServiceLocator()->get('Config');
            }
            return $this->config;
        }
        return;
        
    }

    public function getTranslator()
    {

        if($this->getServiceLocator()){
            if(!$this->translator){
                $this->translator = $this->getServiceLocator()->get('MvcTranslator');
            }
            return $this->translator;
        }
        return;
        
    }

    public function getFormElementManager()
    {

        if($this->getServiceLocator()){
            if(!$this->formElementManager){
                $this->formElementManager = $this->getServiceLocator()->get('FormElementManager');
            }
            return $this->formElementManager;
        }
        return;
        
    }

    public function getConf($name = Null, $param = Null, $all = False)
    {

        $config = $this->getConfigs()[$this->configName];
        if(!empty($name)){
            if(!empty($config[$name])){
                if(!empty($param)){
                    if(!empty($config[$name][$param])){
                        return $config[$name][$param];
                    }else{
                        return False;
                    }
                }else{
                    return $config[$name];
                }
            }
        }else{
            if($all){
                return $config;
            }else{
                return False;
            }
        }

    }

    public function getOps($name)
    {

        $config = $this->getConfigs()[$this->configName];
        if(!empty($name)){
            if(!empty($config['options']) && !empty($config['options'][$name])){
                return $config['options'][$name];
            }
        }
        return False;

    }

    public function getSets($name, $callback = [])
    {
        
        $name = (($opt = $this->getOps($name)) ? $opt : $name);
        $r = ($this->getSettings()->get($name) ? $this->getSettings()->get($name) : ($this->getConf('settings', $name) ? $this->getConf('settings', $name) : Null));
        if(!empty($callback)){
            $r = call_user_func_array($callback, [$r]);
        }
        return $r;
        
    }

    public function setSets($name, $value)
    {
        
        $name = (($opt = $this->getOps($name)) ? $opt : $name);
        $this->getSettings()->set($name, $value);
        
    }

    public function getCurentUserID()
    {

        $user = $this->getAcl()->getAuthenticationService()->getIdentity();
        if($user){
            return $user->getId();
        }
        return Null;

    }
    
    private function getRoleCurentUser()
    {

        $r = 'public';
        $rc = $this->getAcl()->getAuthenticationService()->getIdentity();
        if($rc){
            $r = $rc->getRoleId();
        }
        return $r;

    }

    private function getRoleUser($userID)
    {

        $r = $this->getUser($userID);
        if(!empty($r['role'])){
            return $r['role'];
        }
        return False;

    }

    private function getUser($userID)
    {

        $rc = $this->getConnection()->executeQuery("SELECT id, name, email, role, created FROM `user` WHERE `id` = '{$userID}' LIMIT 1;");
        if(!empty($rc)){
            return $rc->fetchAssociative();
        }
        return False;

    }

    private function getUserEntry($id)
    {
        return $this->getAdapter('users')->findEntity($id);
    }

    public function setPublic($item, $public = True)
    {

        if(!empty($item)){
            if(is_numeric($item)){
                $id = $item;
            }elseif(is_object($item)){
                $id = $item->id();
            }else{
                return False;
            }
            $q = 'UPDATE `resource` SET ';
            if($public){
                $q .= "`is_public` = 1";
            }else{
                $q .= "`is_public` = 0";
            }
            $q .= ' WHERE `id` = '.$id;
            $this->getConnection()->executeQuery($q);
        }

    }

    public function getStrConf($name, $param = Null)
    {

        if(is_array($name)){
            $rc = $this->getConf($name[0], $name[0]);
        }else{
            $rc = $this->getConf($name, Null);
        }
        if(!empty($rc)){
            $str = $this->translate($rc);
            if(!empty($param)){
                return vsprintf($str, $param);
            }else{
                return $str;
            }
            
        }
        return False;

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

}
