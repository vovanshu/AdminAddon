<?php

namespace AdminAddon;

if (!class_exists(\Common\TraitModule::class)) {
    require_once dirname(__DIR__) . '/Common/TraitModule.php';
}

require_once __DIR__ . '/src/General.php';

use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\EventManager\Event;
// use Laminas\Mvc\Controller\AbstractController;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\ModuleManager\ModuleEvent;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\MvcEvent;
use Omeka\Module\AbstractModule;
use Omeka\Permissions\Acl;
// use Omeka\Entity\Job;
use Common\TraitModule;
use AdminAddon\General;

class Module extends AbstractModule
{

    use TraitModule;

    use General;

    protected $application;

    const NAMESPACE = __NAMESPACE__;

    
    /**
     * Get the config of the current module.
     *
     * @return array
     */
    public function getConfig()
    {
        return include $this->modulePath() . '/config/module.config.php';
    }

    public function init(ModuleManager $moduleManager): void
    {
        $moduleManager->getEventManager()->attach(ModuleEvent::EVENT_MERGE_CONFIG, [$this, 'onEventMergeConfig']);
    }

    public function onEventMergeConfig(ModuleEvent $event): void
    {

        if(file_exists(OMEKA_PATH . '/config/custom.config.php')){
            $custom_config = include OMEKA_PATH . '/config/custom.config.php';
            if(!empty($custom_config)){
                /** @var \Laminas\ModuleManager\Listener\ConfigListener $configListener */
                $configListener = $event->getParam('configListener');
                // At this point, the config is read only, so it is copied and replaced.
                $config = $configListener->getMergedConfig(false);
                $config = array_replace_recursive($config, $custom_config);
                $configListener->setMergedConfig($config);
            }
        }

    }

    public function onBootstrap(MvcEvent $event): void
    {

        $this->setServiceLocator($this->serviceLocator);
        $this->setMvcEvent($event);
        parent::onBootstrap($event);
        $this->addDefAclRules();

        

    }
    
    /**
     * Add ACL rules for this module.
     */
    protected function addDefAclRules()
    {

        $this->getAcl()->deny(
            null,
            [
                Controller\Admin\SettingsController::class
            ]
        );

        $this->getAcl()->deny(
            Acl::ROLE_SITE_ADMIN,
            [
                Controller\Admin\SettingsController::class
            ]
        );

        $this->getAcl()->allow(
            Acl::ROLE_GLOBAL_ADMIN,
            [
                Controller\Admin\SettingsController::class
            ]
        );
        $this->getAcl()->allow(
            null,
            [
                Controller\AdminAddonController::class
            ]
        );

    }

    public function debugEvent(Event $event)
    {

        if($this->getConf('debug')){

            ob_start();
            print_r($this->getConfigs());
            $data = ob_get_clean();
            file_put_contents(OMEKA_PATH.'/logs/dev.config.log', $data);

            $configuration = $this->getServiceLocator()->get('ApplicationConfig');
   
        }

    }


    public function debugTrigersEvent(Event $event)
    {

        if($this->getConf('debug')){

            $name = $event->getName();
            $target = $event->getTarget();
            $params = $event->getParams();
            $data = date('Y-m-d H:i:s');
            $data .= ' | '.get_class($target);
            $data .= ' | '.$name;
            $data .= ' | '.json_encode($params);
            $data .= "\r\n";
            file_put_contents(OMEKA_PATH.'/logs/trigger.log', $data, FILE_APPEND);

        }

    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager): void
    {

        $sharedEventManager->attach(
            '*',
            // 'Laminas\Mvc\Application',
            'view.layout',
            // 'route',
            [$this, 'debugEvent'],
            1000
        );

        // $sharedEventManager->attach('*', '*', [$this, 'debugTrigersEvent'],1000);

        $sharedEventManager->attach(
            \Omeka\Stdlib\HtmlPurifier::class,
            'htmlpurifier_config',
            [$this, 'handleHtmlPurifier']
        );

        $sharedEventManager->attach(
            \Omeka\Form\SettingForm::class,
            'form.add_elements',
            [$this, 'appendFieldsSettings']
        );

        $sharedEventManager->attach(
            'Omeka\Api\Adapter\ItemAdapter',
            'api.search.query',
            [$this, 'addInSearchQuery']
        );

        $sharedEventManager->attach(
            'Omeka\Api\Adapter\MediaAdapter',
            'api.search.query',
            [$this, 'addInSearchQuery']
        );

        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Item',
            'view.advanced_search',
            [$this, 'addFieldsToAdvancedSearch']
        );

        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Module',
            'view.browse.before',
            [$this, 'addActionsToModuleBrowse']
        );
        
        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Media',
            'view.advanced_search',
            [$this, 'addFieldsToAdvancedSearch']
        );

        $sharedEventManager->attach(
            '*',
            'view.layout',
            [$this, 'handleViewLayout'],
            -1001
        );

        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Index',
            'view.browse.after',
            [$this, 'addMenuAdminDashboard']
        );
    }

    public function handleViewLayout(Event $event): void
    {

        $view = $event->getTarget();
        $params = $view->params()->fromRoute();

        $controller = False;
        $action = False;

        if(!empty($params['__CONTROLLER__'])){
            $controller = $params['__CONTROLLER__'];
        }elseif(!empty($params['controller'])){
            $controller = $params['controller'];
        }
        if(!empty($params['action'])){
            $action = $params['action'];
        }

        $view->headLink()->appendStylesheet($view->assetUrl('css/adminaddon.css', 'AdminAddon'));

        if(!empty($params['__ADMIN__'])){

            $mode = $this->modeAdminUI($controller, $action);
            if($mode){

                if($this->getConf('developing')){
                    echo "<!--\r\n";
                    print_r($mode);
                    echo "\r\n-->\r\n";
                }

                foreach($mode['styles'] as $name => $style){
                    if($this->isCompatibleAdminUI($mode['controllers'], $controller, $action, $name)){
                        if(!empty($style['css'])){
                            foreach($style['css'] as $file){
                                $view->headLink()->appendStylesheet($view->assetUrl($file, 'AdminAddon'));
                            }
                        }
                        if(!empty($style['js'])){
                            foreach($style['js'] as $file){
                                $view->headScript()->appendFile($view->assetUrl($file, 'AdminAddon'));
                            }
                        }
                    }
                }

                // echo $controller.' - '.$action;
                // if(in_array($controller, $con) && in_array($action, $act)){
                    // $view->headLink()->appendStylesheet($view->assetUrl('css/general.css', 'AdminAddon'));
                    // $view->headLink()->appendStylesheet($view->assetUrl('css/flex-table.css', 'AdminAddon'));
                // }
                // $view->headScript()->appendFile($view->assetUrl('js/adminaddon.js', 'AdminAddon'));

            }

            if($controller == 'Page' && $action == 'edit'){
                $view->headScript()->appendFile($view->assetUrl('js/ckeditor/mode.js', 'AdminAddon'));
            }
                

            // add theme ckeditor css (provide defaults)
            // todo: check if exists
            // $view->headLink()->appendStylesheet($view->assetUrl('css/ckeditor.css', 'AdminAddon', true));
            // add ckeditor styles (provide defaults)
            // todo: check if exists
            // $styleSetUrl = $view->assetUrl('js/ckeditor_styles.js', 'AdminAddon', true);

            if($this->getSets('search_form_hidden') == 'true'){
                $css = <<<CSS
header #user {
    margin-bottom: 24px;
}
header #search {
    display: none;
}
CSS;
                $view->HeadStyle()->appendStyle($css);
            }

        }

        if($this->getSets('select2') == 'true' && !empty($params['__ADMIN__']) || $this->getSets('select2public') == 'true'){
            $view->headLink()->appendStylesheet($view->assetUrl('vendor/select2/css/select2.min.css', 'AdminAddon'));
            $view->headScript()->appendFile($view->assetUrl('vendor/select2/js/select2.full.min.js', 'AdminAddon'));
            $view->headScript()->appendFile($view->assetUrl('js/select2-init.js', 'AdminAddon'));
        }         

        if($this->getSets('advsearch_autocomplete') == 'true' && !empty($params['__ADMIN__']) || $this->getSets('advsearch_autocomplete') == 'true'){
            $view->headLink()->appendStylesheet('//code.jquery.com/ui/1.14.2/themes/base/jquery-ui.css');
            $view->headScript()->appendFile('//code.jquery.com/ui/1.14.2/jquery-ui.min.js');
            $autocompleteURL = $view->url('admin-addon-controller', ['action' => 'autocomplete']);
    $script = <<<JS
window.AdminAdonAutocompleteURL = '$autocompleteURL';
JS;
            $view->headScript()->appendScript($script);
            $view->headScript()->appendFile($view->assetUrl('js/property-autocomplete.js', 'AdminAddon'));
        }

        if($this->getConf('developing') || $this->getConf('debug')){
            echo "<!-- Controller: " . $controller . " -->\r\n";
            echo "<!-- Action: " . $action . " -->\r\n";
        }

        if($this->getConf('debug')){
            echo "<!--\r\n Params:\r\n";
            print_r($params);

            // print_r(get_class_methods($cfg));

            echo "\r\n-->\r\n";
            echo "<!-- Current memory usage: " . $this->convert_size(memory_get_usage()) . " -->\r\n";
            echo "<!-- Peak memory usage: " . $this->convert_size(memory_get_peak_usage()) . " -->\n";
            $usege = getrusage();
            echo "<!-- User CPU time: ".($usege['ru_utime.tv_usec']/1000000)." seconds -->\r\n";
            echo "<!-- System CPU time: ".($usege['ru_stime.tv_usec']/1000000)." seconds -->\r\n";
        }

    }

    public function addMenuAdminDashboard(Event $event): void
    {

        if($this->getSets('menuadmindashboard_enable') == 'true' && !empty($this->getSets('menuadmindashboard')) && !empty($this->getSets('menuadmindashboard_label'))){
            $view = $event->getTarget();
            $menurc = $this->getSets('menuadmindashboard');
            $menu = parse_ini_string($menurc, true, INI_SCANNER_TYPED);
            echo $view->partial('admin-addon/admin/index/menu', [
                'label' => $this->getSets('menuadmindashboard_label'),
                'menu' => $menu,
            ]);
        }
        
    }


    public function appendFieldsSettings(Event $event): void
    {

        if(!empty($mode = $this->getSets('editor_change_in_setting')) && $mode !== 'default'){

            $form = $event->getTarget();
            $options = $form->getOptions();

            $form->add([
                'name' => 'adminaddon_html_mode_page',
                'type' => 'radio',
                'options' => [
                    'element_group' => 'editing',
                    'label' => 'Html edition mode for pages', // @translate
                    'value_options' => [
                        'inline' => 'Inline (default)', // @translate
                        'document' => 'Document (maximizable)', // @translate
                    ],
                ],
                'attributes' => [
                    'id' => 'adminaddon_html_mode_page',
                    'value' => $this->getSets('html_mode')
                ],
            ]);

            $form->add([
                'name' => 'adminaddon_html_config_page',
                'type' => 'radio',
                'options' => [
                    'element_group' => 'editing',
                    'label' => 'Html edition config and toolbar for pages', // @translate
                    'value_options' => [
                        // @see https://ckeditor.com/cke4/presets-all
                        'default' => 'Default', // @translate
                        'standard' => 'Standard', // @translate
                        'full' => 'Full', // @translate
                    ],
                ],
                'attributes' => [
                    'id' => 'adminaddon_html_config_page',
                    'value' => $this->getSets('html_config')
                ],
            ]);

        }

        $form->add([
            'type' => 'checkbox',
            'name' => 'recaptcha_enable_on_login',
            'options' => [
                'element_group' => 'security',
                'label' => 'Enable reCAPTCHA on Login page', // @translate
                'info' => 'Check this to enable reCAPTCHA on Login page.', // @translate
                'checked_value' => 'true',
                'unchecked_value' => 'false',
            ],
            'attributes' => [
                'value' => $this->getSets('recaptcha_enable_on_login'),
                'id' => 'recaptcha_enable_on_login',
            ],
        ]);

        $form->add([
            'type' => 'checkbox',
            'name' => 'recaptcha_enable_on_forgot_password',
            'options' => [
                'element_group' => 'security',
                'label' => 'Enable reCAPTCHA on Forgot Password page', // @translate
                'info' => 'Check this to enable reCAPTCHA on Forgot Password page.', // @translate
                'checked_value' => 'true',
                'unchecked_value' => 'false',
            ],
            'attributes' => [
                'value' => $this->getSets('recaptcha_enable_on_forgot_password'),
                'id' => 'recaptcha_enable_on_forgot_password',
            ],
        ]);

        $form->add([
            'name' => 'recaptcha_ip_white_list',
            'type' => 'textarea',
            'options' => [
                'element_group' => 'security',
                'label' => 'IP whitelist for reCAPTCHA', // @translate
                'info' => 'Enter a single IP address or a range of IP addresses separated by dashes (IPbegin-IPend) in the line to whitelist for reCAPTCHA.' // @translate
            ],
            'attributes' => [
                'value' => $this->getSets('recaptcha_ip_white_list'),
                'id' => 'recaptcha_ip_white_list',
            ],
        ]);


        $options['element_groups']['login&forgot'] = 'Pages Log in and Forgot Password'; // @translate
        
        $form->add([
            'name' => 'adminaddon_lf_1_url',
            'type' => 'Text',
            'options' => [
                'element_group' => 'login&forgot',
                'label' => 'Button 1 - URL', // @translate
                'info' => 'URL for button' // @translate
            ],
            'attributes' => [
                'value' => $this->getSets('adminaddon_lf_1_url'),
                'id' => 'adminaddon_lf_1_url',
            ],
        ]);

        $form->add([
            'name' => 'adminaddon_lf_1_label',
            'type' => 'Text',
            'options' => [
                'element_group' => 'login&forgot',
                'label' => 'Button 1 - Label', // @translate
                'info' => 'Label for button', // @translate
            ],
            'attributes' => [
                'value' => $this->getSets('adminaddon_lf_1_label'),
                'id' => 'adminaddon_lf_1_label',
            ],
        ]);

        $form->add([
            'name' => 'adminaddon_lf_2_url',
            'type' => 'Text',
            'options' => [
                'element_group' => 'login&forgot',
                'label' => 'Button 2 - URL', // @translate
                'info' => 'URL for button', // @translate
            ],
            'attributes' => [
                'value' => $this->getSets('adminaddon_lf_2_url'),
                'id' => 'adminaddon_lf_2_url',
            ],
        ]);

        $form->add([
            'name' => 'adminaddon_lf_2_label',
            'type' => 'Text',
            'options' => [
                'element_group' => 'login&forgot',
                'label' => 'Button 2 - Label', // @translate
                'info' => 'Label for button', // @translate
            ],
            'attributes' => [
                'value' => $this->getSets('adminaddon_lf_2_label'),
                'id' => 'adminaddon_lf_2_label',
            ],
        ]);

        $options['element_groups']['menuadmindashboard'] = 'Menu on Admin dashboard'; // @translate

        $form->add([
            'name' => 'adminaddon_menuadmindashboard_label',
            'type' => 'Text',
            'options' => [
                'element_group' => 'menuadmindashboard',
                'label' => 'Label', // @translate
                'info' => 'Label for menu on Admin dashboard', // @translate
            ],
            'attributes' => [
                'value' => $this->getSets('menuadmindashboard_label'),
                'id' => 'adminaddon_menuadmindashboard_label',
            ],
        ]);

        $form->add([
            'type' => 'checkbox',
            'name' => 'adminaddon_menuadmindashboard_enable',
            'options' => [
                'element_group' => 'menuadmindashboard',
                'label' => 'Enable', // @translate
                'info' => 'Enable menu on Admin dashboard', // @translate
                'checked_value' => 'true',
                'unchecked_value' => 'false',
            ],
            'attributes' => [
                'value' => $this->getSets('menuadmindashboard_enable'),
                'id' => 'adminaddon_menuadmindashboard_enable',
            ],
        ]);

        $form->add([
            'name' => 'adminaddon_menuadmindashboard',
            'type' => 'textarea',
            'options' => [
                'element_group' => 'menuadmindashboard',
                'label' => 'Content', // @translate
                'info' => '', // @translate
            ],
            'attributes' => [
                'value' => $this->getSets('menuadmindashboard'),
                'id' => 'adminaddon_menuadmindashboard',
            ],
        ]);

        $form->setOption('element_groups', $options['element_groups']);

    }

    public function addInSearchQuery(Event $event): void
    {

        // $routeMatch = $this->getApplicationRouteMatch();
        $routeMatch = $this->getServiceLocator()->get('Application')->getMvcEvent()->getRouteMatch();
        $controller = $routeMatch->getParam('__CONTROLLER__');
        if($controller == 'media' || $controller == 'item'){
        // if(!empty($routeMatch) && is_object($routeMatch) && method_exists($routeMatch, 'getParam')){
            // if($routeMatch->getParam('__ADMIN__')){
            $request = $event->getParam('request');
            $params = $request->getContent();
            $qb = $event->getParam('queryBuilder');
            $entityAlias = $qb->getRootAlias();
            $mediaAlias = $qb->createAlias();
            $expr = $qb->expr();
            if($controller == 'media'){
                if(!empty($params['ingester'])){
                    $qb->andWhere($expr->eq($entityAlias . '.ingester', "'".$params['ingester']."'"));
                }
                if(!empty($params['media_type'])){
                    $qb->andWhere($expr->eq($entityAlias . '.mediaType', "'".$params['media_type']."'"));
                }
            }
            if($controller == 'item'){
                if(!empty($params['ingester'])){
                    $qb->leftJoin($entityAlias . '.media', $mediaAlias);
                    $qb->andWhere($expr->eq($mediaAlias . '.ingester', "'".$params['ingester']."'"));
                }
                if(!empty($params['media_type'])){
                    $qb->leftJoin($entityAlias . '.media', $mediaAlias);
                    $qb->andWhere($expr->eq($mediaAlias . '.mediaType', "'".$params['media_type']."'"));
                }
            }
        }
        
    }

    public function addFieldsToAdvancedSearch(Event $event)
    {

        $partials = $event->getParam('partials');
        $partials[] = 'common/advanced-search/media-ingester';
        $partials[] = 'common/advanced-search/media-type';
        $event->setParam('partials', $partials);

    }

    public function addActionsToModuleBrowse(Event $event)
    {

        $view = $event->getTarget();
        //if ($this->userIsAllowed('Omeka\Controller\Admin\Vocabulary', 'add'))
        echo '<div id="page-actions">';
        if($view->userIsAllowed('AdminAddon\Controller\Admin\SettingsController', 'deactivate-all')){
            echo $view->hyperlink($view->translate('Deactivate all'), $view->url('admin/admin-addon-settings', ['action' => 'deactivate-all'], true), ['class' => 'button']);
        }
        echo '</div>';

    }

    public function handleHtmlPurifier(Event $event): void
    {

        // @see https://github.com/ezyang/htmlpurifier/

        /** @var \HTMLPurifier_Config $config */
        $config = $event->getParam('config');

        $config->set('Attr.EnableID', true);
        $config->set('HTML.AllowedAttributes', [
            'a.id',
            'a.rel',
            'a.href',
            'a.target',
            'li.id',
            'section.class',
            'img.src',
            'img.alt',
            // 'img.loading',
        ]);

        $config->set('HTML.TargetBlank', true);

        /** @var \HTMLPurifier_HTMLDefinition $def */
        $def = $config->getHTMLDefinition(true);

        $def->addElement('article', 'Block', 'Flow', 'Common');
        $def->addElement('section', 'Block', 'Flow', 'Common');
        $def->addElement('header', 'Block', 'Flow', 'Common');
        $def->addElement('footer', 'Block', 'Flow', 'Common');

        $def->addAttribute('sup', 'data-footnote-id', 'ID');
        // This is the same id than sup, but Html Purifier ID should be unique
        // among all the submitted html ids, so use Class.
        // $def->addAttribute('li', 'data-footnote-id', 'Class');

        $def->addAttribute('a', 'target', new \HTMLPurifier_AttrDef_Enum(['_blank', '_self', '_target', '_top']));

        $event->setParam('config', $config);
    }

    public function getConfigForm(PhpRenderer $renderer)
    {

        $url = $renderer->url('admin/admin-addon-settings', ['action' => 'edit']);
        // return "<script>window.location.href = '$url';</script>";
        $response = $this->getMvcEvent()->getResponse();
        $response->getHeaders()->addHeaderLine('Location', $url);
        $response->setStatusCode(302);
        $response->sendHeaders();
        return $response;
        
    }

}
