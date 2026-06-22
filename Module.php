<?php

namespace AdminAddon;

require_once __DIR__ . '/src/TraitGeneral.php';
require_once __DIR__ . '/src/TraitModule.php';

use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\EventManager\Event;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\ModuleManager\ModuleEvent;
// use Laminas\ModuleManager\ModuleManager;
// use Laminas\Mvc\MvcEvent;
use Omeka\Module\AbstractModule;
use Omeka\Permissions\Acl;
use Omeka\Entity\Job;
use AdminAddon\TraitGeneral;
use AdminAddon\TraitModule;

class Module extends AbstractModule
{
    
    use TraitGeneral;
    use TraitModule;
    
    /**
     * Get the config of the current module.
     *
     * @return array
     */

    public function onEventMergeConfig(ModuleEvent $event): void
    {

        if(file_exists(OMEKA_PATH . '/config/custom.config.php')){
            $custom_config = include OMEKA_PATH . '/config/custom.config.php';
            if(!empty($custom_config['AdminAddon'])){
                /** @var \Laminas\ModuleManager\Listener\ConfigListener $configListener */
                $configListener = $event->getParam('configListener');
                // At this point, the config is read only, so it is copied and replaced.
                $config = $configListener->getMergedConfig(false);
                if(!empty($custom_config['AdminAddon']['chosen_js_disable']) && $custom_config['AdminAddon']['chosen_js_disable'] == 'true'){
                    $config['assets']['internals']['vendor/chosen-js/chosen.css'] = 'AdminAddon';
                    $config['assets']['internals']['vendor/chosen-js/chosen.jquery.js'] = 'AdminAddon';
                    $config['assets']['internals']['js/chosen-options.js'] = 'AdminAddon';
                }
                if(!empty($custom_config['AdminAddon']['replace_helper_ckeditor']) && $custom_config['AdminAddon']['replace_helper_ckeditor'] == 'true'){
                    $config['view_helpers']['invokables']['ckEditor'] = '\AdminAddon\View\Helper\CkEditor';
                }
                $configListener->setMergedConfig($config);
            }
        }

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
        $this->getAcl()->allow(
            [
                Acl::ROLE_GLOBAL_ADMIN,
                Acl::ROLE_SITE_ADMIN
            ],
            [
                'Omeka\Controller\Admin\Vocabulary',
                'Omeka\Controller\Admin\Property',
                'Omeka\Controller\Admin\ResourceClass',
            ],
            [
                'browse', 'show-details', 'properties', 'classes', 'add', 'edit', 'delete', 'delete-confirm'
            ]
        );
        $this->getAcl()->allow(
            [
                Acl::ROLE_GLOBAL_ADMIN,
                Acl::ROLE_SITE_ADMIN
            ],
            [
                'Omeka\Controller\Admin\Job',
            ],
            [
                'fix-job', 'clearn', 'delete-error', 'delete', 'run', 'stop'
            ]
        );

    }

    public function getConfigForm(PhpRenderer $renderer)
    {

        return $this->redirecToURL($renderer->url('admin/admin-addon-settings', ['action' => 'edit']));
        
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager): void
    {

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
            \Omeka\Form\SettingForm::class,
            'form.add_input_filters',
            [$this, 'handleSettingsFilters']
        );

        $sharedEventManager->attach(
            \Omeka\Form\SiteSettingsForm::class,
            'form.add_elements',
            [$this, 'appendFieldsSiteSettings']
        );

        $sharedEventManager->attach(
            \Omeka\Form\SiteSettingsForm::class,
            'form.add_input_filters',
            [$this, 'handleSettingsFilters']
        );

        $sharedEventManager->attach(
            'Omeka\Api\Adapter\ItemAdapter',
            'api.search.query',
            [$this, 'handlerSearchQuery']
        );

        $sharedEventManager->attach(
            'Omeka\Api\Adapter\MediaAdapter',
            'api.search.query',
            [$this, 'handlerSearchQuery']
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
            '*',
            'view.layout-admin.body.top',
            [$this, 'handleViewLayoutAdminBodyTop'],
        );

        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Index',
            'view.browse.after',
            [$this, 'addMenuAdminDashboard']
        );

        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Job',
            'view.browse.before',
            [$this, 'addActionsToJobBrowse']
        );

        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Job',
            'view.layout',
            [$this, 'addActionsToJobShow']
        );

    }

    public function handleViewLayout(Event $event): void
    {

        $view = $event->getTarget();
        [$controller, $action, $mode] = $this->modeAdminUI($event);
        $params = $view->params()->fromRoute();
        // $controller = False;
        // $action = False;
        $siteSlug = isset($params['site-slug']) ? $params['site-slug'] : '';
        // if(!empty($params['__CONTROLLER__'])){
        //     $controller = $params['__CONTROLLER__'];
        // }elseif(!empty($params['controller'])){
        //     $controller = $params['controller'];
        // }
        // if(!empty($params['action'])){
        //     $action = $params['action'];
        // }
        
        $view->headLink()->appendStylesheet($view->assetUrl('css/adminaddon.css', 'AdminAddon'));
        // $view->headScript()->appendFile($view->assetUrl('js/adminaddon.js', 'AdminAddon'));
        
        if(!empty($params['__ADMIN__']) && $this->getSets('adminaddon_search_fasets_enable') == 'true' || !empty($params['__SITE__']) && $this->getSiteSets('adminaddon_search_fasets_enable') == 'true'){
            $view->headLink()->appendStylesheet($view->assetUrl('css/facets.css', 'AdminAddon'));
            $view->headScript()->appendFile($view->assetUrl('js/fasets.js', 'AdminAddon'));
        }

        if(!empty($params['__ADMIN__'])){
            if($mode){
                
                if($this->isAppDevMode() && $this->userIsGlobalAdmin()){
                    if(function_exists('d')){
                        if(class_exists('Kint\Renderer\RichRenderer')){
                            \Kint\Kint::$depth_limit = 0;
                            \Kint\Renderer\RichRenderer::$folder = True;
                        }
                        d($mode);
                    }else{
                        echo "<!--\r\n";
                        print_r($mode);
                        echo "\r\n-->\r\n";
                    }
                }

                foreach($mode['styles'] as $name => $style){
                    if($this->isCompatibleAdminUI($mode['controllers'], $controller, $action, $name)){
                        if(!empty($style['css'])){
                            foreach($style['css'] as $file => $type){
                                if($type == 'URL'){
                                    $assertURL = $file;
                                }else{
                                    $assertURL = $view->assetUrl($file, $type);
                                }
                                $view->headLink()->appendStylesheet($assertURL);
                            }
                        }
                        if(!empty($style['js'])){
                            foreach($style['js'] as $file => $type){
                                if($type == 'URL'){
                                    $assertURL = $file;
                                }else{
                                    $assertURL = $view->assetUrl($file, $type);
                                }
                                $view->headScript()->appendFile($assertURL);
                            }
                        }
                    }
                }
            }

            if($controller == 'Page' && $action == 'edit'){
                $view->headScript()->appendFile($view->assetUrl('js/ckeditor/mode.js', 'AdminAddon'));
            }

            if($this->getSets('adminaddon_search_form_inmenu_hidden') == 'true'){
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

        if(!empty($params['__ADMIN__']) && $this->getSets('adminaddon_select2_enable') == 'true' || !empty($params['__SITE__']) && $this->getSiteSets('adminaddon_select2_enable_public') == 'true'){
            if(in_array($action, ['search', 'add', 'edit'])){
                $view->headLink()->appendStylesheet($view->assetUrl('vendor/select2/css/select2.min.css', 'AdminAddon'));
                $view->headScript()->appendFile($view->assetUrl('vendor/select2/js/select2.full.min.js', 'AdminAddon'));
                $view->headScript()->appendFile($view->assetUrl('js/select2-init.js', 'AdminAddon'));
            }
        }

        if(!empty($params['__ADMIN__']) && ($this->getSets('adminaddon_advsearch_autocomplete') == 'true' || $this->getSets('adminaddon_search_fasets_enable') == 'true') || !empty($params['__SITE__']) && ($this->getSiteSets('adminaddon_advsearch_autocomplete') == 'true'  || $this->getSiteSets('adminaddon_search_fasets_enable') == 'true')){
            
            if(in_array($controller, $this->getConf('compatible_autocomplete_facets', 'controllers')) && in_array($action, $this->getConf('compatible_autocomplete_facets', 'actions'))){
                $script = '';               
                if(in_array($action, ['search', 'add', 'edit'])){
                    $view->headLink()->appendStylesheet('//code.jquery.com/ui/1.14.2/themes/base/jquery-ui.css');
                    $view->headScript()->appendFile('//code.jquery.com/ui/1.14.2/jquery-ui.min.js');
                }
                if(!empty($params['__ADMIN__']) && in_array($action, ['search'])){  
                    $script .= 'window.AdminAdonNeededFields = '.json_encode($this->getSets('adminaddon_advsearch_autocomplete_fields'), JSON_UNESCAPED_UNICODE).';';
                }
                if(!empty($params['__SITE__']) && in_array($action, ['search'])){  
                    $script .= 'window.AdminAdonNeededFields = '.json_encode($this->getSiteSets('adminaddon_advsearch_autocomplete_fields'), JSON_UNESCAPED_UNICODE).';';
                }
                if(!empty($params['__ADMIN__']) && in_array($action, ['add', 'edit'])){
                    $script .= 'window.AdminAdonNeededFields = '.json_encode($this->getSets('adminaddon_forms_autocomplete_fields'), JSON_UNESCAPED_UNICODE).';';
                }
                $script .= 'window.OmekaAdonController = \''.$controller.'\';';
                $script .= 'window.OmekaAdonAction = \''.$action.'\';';
                $script .= 'window.OmekaSiteSlug = \''.$siteSlug.'\';';
                $view->headScript()->appendScript($script);
                if(in_array($action, ['search'])){  
                    $view->headScript()->appendFile($view->assetUrl('js/search-property-autocomplete.js', 'AdminAddon'));
                }
                if(in_array($action, ['add', 'edit'])){
                    $view->headScript()->appendFile($view->assetUrl('js/form-property-autocomplete.js', 'AdminAddon'));
                }
            }
        }

    }

    public function handleViewLayoutAdminBodyTop(Event $event)
    {

        $view = $event->getTarget();
        [$controller, $action, $mode] = $this->modeAdminUI($event);
        if($mode && !empty($mode['view.layout-admin.body.top'])){
            echo $view->partial($mode['view.layout-admin.body.top']);
        }

    }


    public function addMenuAdminDashboard(Event $event): void
    {

        if($this->getSets('adminaddon_menuadmindashboard_enable') == 'true' && !empty($this->getSets('adminaddon_menuadmindashboard')) && !empty($this->getSets('adminaddon_menuadmindashboard_label'))){
            $view = $event->getTarget();
            $menurc = $this->getSets('adminaddon_menuadmindashboard');
            $menu = parse_ini_string($menurc, true, INI_SCANNER_TYPED);
            echo $view->partial('admin-addon/admin/index/menu', [
                'label' => $this->getSets('adminaddon_menuadmindashboard_label'),
                'menu' => $menu,
            ]);
        }
        
    }

    public function appendFieldsSiteSettings(Event $event): void
    {

        $fieldset = new Form\SiteSettingsFieldset;
        $fieldset->setServiceLocator($this->getServiceLocator());
        $fieldset->addFields($event->getTarget());
        $this->memFilterAllowEmpty($fieldset->memFilterAllowEmpty());

    }

    public function appendFieldsSettings(Event $event): void
    {

        $fieldset = new Form\SettingsFieldset;
        $fieldset->setServiceLocator($this->getServiceLocator());
        $fieldset->addFields($event->getTarget());
        $this->memFilterAllowEmpty($fieldset->memFilterAllowEmpty());

    }

    public function handleSettingsFilters(Event $event): void
    {

        $inputFilter = $event->getParam('inputFilter');
        $allowedEmpty = $this->memFilterAllowEmpty();
        if(!empty($allowedEmpty)){
            foreach($allowedEmpty as $var){
                if(is_array($var)){
                    foreach($var as $group => $name){
                        $inputFilter->get($group)->add([
                            'name' => $name,
                            'allow_empty' => true,
                        ]);
                    }
                }else{
                    $inputFilter->add([
                        'name' => $var,
                        'required' => false,
                        'allow_empty' => true,
                    ]);
                }
            }
        }

    }

    public function handlerSearchQuery(Event $event): void
    {

        // $routeMatch = $this->getServiceLocator()->get('Application')->getMvcEvent()->getRouteMatch();
        $routeMatch = $this->getStatus()->getRouteMatch();
        if(!empty($routeMatch) && method_exists($routeMatch, 'getParam')){
            $controller = $routeMatch->getParam('__CONTROLLER__');
            if(in_array($controller, ['media', 'item', 'Item', 'Media'])){
                $siteSlug = False;
                if(!empty($routeMatch->getParam('site-slug'))){
                    $siteSlug = $routeMatch->getParam('site-slug');
                }
                $request = $event->getParam('request');
                $params = $request->getContent();
                $qb = $event->getParam('queryBuilder');
                $entityAlias = $qb->getRootAlias();
                $expr = $qb->expr();
                if($controller == 'media' || $controller == 'Media'){
                    $mediaAlias = $qb->createAlias();
                    if(!empty($params['ingester'])){
                        $qb->andWhere($expr->eq($entityAlias . '.ingester', "'".$params['ingester']."'"));
                    }
                    if(!empty($params['media_type'])){
                        $qb->andWhere($expr->eq($entityAlias . '.mediaType', "'".$params['media_type']."'"));
                    }
                }
                if($controller == 'item' || $controller == 'Item'){
                    $mediaAlias = $qb->createAlias();
                    if(!empty($params['ingester'])){
                        $qb->leftJoin($entityAlias . '.media', $mediaAlias);
                        $qb->andWhere($expr->eq($mediaAlias . '.ingester', "'".$params['ingester']."'"));
                    }
                    if(!empty($params['media_type'])){
                        $qb->leftJoin($entityAlias . '.media', $mediaAlias);
                        $qb->andWhere($expr->eq($mediaAlias . '.mediaType', "'".$params['media_type']."'"));
                    }
                    if(!empty($params['facets'])){
                        $configFasets = $this->getConfigSearchFasets($siteSlug);
                        if(!empty($configFasets)){
                            $fasetsQuery = [];
                            // $qb->leftJoin($entityAlias.'.values', $valuesAlias);
                            foreach($configFasets as $faset){
                                if(!empty($params['facets'][$faset['name']])){
                                    $valuesAlias = $qb->createAlias();
                                    $facetOps = $params['facets'][$faset['name']];
                                    $qb->leftJoin($entityAlias.'.values', $valuesAlias, 'WITH', $qb->expr()->eq("$valuesAlias.property", $faset['property_id']));
                                    if($faset['type'] == 'range'){
                                        $fasetsQuery[] = $expr->andX(
                                            $expr->gte($valuesAlias.'.value', $facetOps['from']),
                                            $expr->lte($valuesAlias.'.value', $facetOps['to'])
                                        );
                                    }
                                    if($faset['type'] == 'checkboxe'){
                                        $fvars = [];
                                        foreach($facetOps as $fvar){
                                            $fvars[] = $expr->eq($valuesAlias.'.value', "'$fvar'");
                                        }
                                        $fasetsQuery[] = '('.join(' OR ', $fvars).')';                        
                                    }
                                    if($faset['type'] == 'select'){

                                        echo "<!--\r\n facetOps:\r\n";
                                        print_r($facetOps);
                                        echo "\r\n-->\r\n";

                                    }
                                }
                            }
                            if(!empty($fasetsQuery)){
                                $qb->andWhere(join(' AND ', $fasetsQuery));
                            }
                        }
                    }
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


    public function addActionsToJobBrowse(Event $event): void
    {

        $view = $event->getTarget();
        $plugins = $view->getHelperPluginManager();
        $translate = $plugins->get('translate');

        $view->headLink()->appendStylesheet($view->assetUrl('css/settings.css', 'AdminAddon'));
        $view->headScript()->appendFile($view->assetUrl('js/settings.js', 'AdminAddon'));
        $maintenance = [];
        if ($view->userIsAllowed('Omeka\Controller\Admin\Job', 'fix-job')){
            $maintenance[$translate('Fix jobs')] = $view->url('admin/default', ['controller'=> 'job', 'action' => 'fix-job'], true);
        }
        if ($view->userIsAllowed('Omeka\Controller\Admin\Job', 'delete-error')){
            $maintenance[$translate('Deleting jobs with error')] = $view->url('admin/default', ['controller' => 'job', 'action' => 'delete-error'], true);
        }
        if ($view->userIsAllowed('Omeka\Controller\Admin\Job', 'clearn')){
            $maintenance[$translate('Deleting finished & stoped jobs')] = $view->url('admin/default', ['controller' => 'job', 'action' => 'clearn'], true);
        }
        echo '<div id="page-actions">';
        if(!empty($maintenance)){
            if(count($maintenance) > 1){
                echo '<a href="#" id="expand-menu" class="collapsed button expand-more" aria-label="'.$translate('Maintenance').'" title="'. $translate('Maintenance').'" aria-expanded="false" aria-target="#maintenance-menu">'. $translate('Maintenance') .'</a>
                <ul id="maintenance-menu" class="collapsible-menu">';
                foreach($maintenance as $title => $url){
                    echo $view->hyperlink($title, $url, ['class' => 'link']);
                }
                echo '</ul>';    
                
            }else{
                echo $view->hyperlink(key($maintenance), current($maintenance), ['class' => 'button']);
            }
        }
        if ($view->userIsAllowed('AdminAddon\Controller\Admin\SettingsController', 'edit')){
            echo $view->hyperlink($translate('Settings'), $view->url('admin/admin-addon-settings', ['action' => 'edit'], true), ['class' => 'button']);
        }
        echo '</div>';

    }

    public function addActionsToJobShow(Event $event): void
    {

        $view = $event->getTarget();
        $params = $view->params()->fromRoute();
        if(!empty($params['action']) && $params['action'] == 'show'){
            $plugins = $view->getHelperPluginManager();
            $translate = $plugins->get('translate');
            $api = $plugins->get('api');
            $job = $api->read('jobs', $params['id'])->getContent();
            $jobStatus = $job->status();
            $vars = $view->vars();
            $replace = '<div id="page-actions">';
            if ($view->userIsAllowed('Omeka\Controller\Admin\Job', 'run') && in_array($jobStatus, [Job::STATUS_STARTING, Job::STATUS_COMPLETED, Job::STATUS_STOPPED, Job::STATUS_ERROR])){
                $replace .= $view->hyperlink($translate('Run'), $view->url('admin/id', ['controller'=> 'job', 'action' => 'run', 'id' => $params['id']], true), ['class' => 'button']);
            }
            if ($view->userIsAllowed('Omeka\Controller\Admin\Job', 'stop') && in_array($jobStatus, [Job::STATUS_STOPPING, Job::STATUS_IN_PROGRESS])){
                $replace .= $view->hyperlink($translate('Stop'), $view->url('admin/id', ['controller'=> 'job', 'action' => 'stop', 'id' => $params['id']], true), ['class' => 'button']);
            }
            if ($view->userIsAllowed('Omeka\Controller\Admin\Job', 'terminate') && in_array($jobStatus, [Job::STATUS_STOPPING, Job::STATUS_IN_PROGRESS])){
                $replace .= $view->hyperlink($translate('Terminate'), $view->url('admin/id', ['controller'=> 'job', 'action' => 'terminate', 'id' => $params['id']], true), ['class' => 'button']);
            }
            if ($view->userIsAllowed('Omeka\Controller\Admin\Job', 'delete') && in_array($jobStatus, [Job::STATUS_STARTING, Job::STATUS_COMPLETED, Job::STATUS_STOPPED, Job::STATUS_ERROR])){
                $replace .= $view->hyperlink($translate('Delete'), $view->url('admin/id', ['controller'=> 'job', 'action' => 'delete', 'id' => $params['id']], true), ['class' => 'button']);
            }
            $content = $vars->offsetGet('content');
            $content = preg_replace([
                '/(<div id="page-actions">)/i'
            ], [
                $replace    
            ], $content);
            $vars->offsetSet('content', $content);
        }

    }

}
