<?php declare(strict_types=1);
namespace AdminAddon\Controller\Admin;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\Form\Form;
use Omeka\Stdlib\Message;
use Omeka\Form\ConfirmForm;
// use Common\Form\Element as CommonElement;
use Omeka\Form\Element\PropertySelect;
use AdminAddon\TraitGeneral;

class SettingsController extends AbstractActionController
{

    use TraitGeneral;

    public function editAction()
    {

        $form = $this->getForm(Form::class);

        $form->add([
                'name' => 'replace_helper_ckeditor',
                'type' => 'checkbox',
                'options' => [
                    'label' => 'Replace ckEditor Helper', // @translate
                    'checked_value' => 'true',
                    'unchecked_value' => 'false',
                ],
                'attributes' => [
                    'id' => 'replace_helper_ckeditor',
                    'value' => $this->getCustomConfVal('replace_helper_ckeditor'),
                ],
            ]);

        $form->add([
                'name' => 'adminaddon_search_form_inmenu_hidden',
                'type' => 'checkbox',
                'options' => [
                    'label' => 'Hiding the Search form field in the Admin menu', // @translate
                    'checked_value' => 'true',
                    'unchecked_value' => 'false',
                ],
                'attributes' => [
                    'id' => 'adminaddon_search_form_inmenu_hidden',
                    'value' => $this->getSets('adminaddon_search_form_inmenu_hidden'),
                ],
            ]);

        $form->add([
                'name' => 'adminaddon_select2_enable',
                'type' => 'checkbox',
                'options' => [
                    'label' => 'Select2 enable', // @translate
                    'checked_value' => 'true',
                    'unchecked_value' => 'false',
                ],
                'attributes' => [
                    'id' => 'adminaddon_select2_enable',
                    'value' => $this->getSets('adminaddon_select2_enable'),
                ],
            ]);

        $form->add([
                'name' => 'adminaddon_select2_enable_public',
                'type' => 'checkbox',
                'options' => [
                    'label' => 'Select2 enable on public', // @translate
                    'checked_value' => 'true',
                    'unchecked_value' => 'false',
                ],
                'attributes' => [
                    'id' => 'adminaddon_select2_enable_public',
                    'value' => $this->getSets('adminaddon_select2_enable_public'),
                ],
            ]);

        $form->add([
                'name' => 'chosen_js_disable',
                'type' => 'checkbox',
                'options' => [
                    'label' => 'Chosen-js disable', // @translate
                    'checked_value' => 'true',
                    'unchecked_value' => 'false',
                ],
                'attributes' => [
                    'id' => 'chosen_js_disable',
                    'value' => $this->getCustomConfVal('chosen_js_disable'),
                ],
            ]);


        $form
            ->add([
                'name' => 'adminaddon_vocabulary_edit_all',
                'type' => 'checkbox',
                'options' => [
                    'label' => 'Edit all elements', // @translate
                    '' // ''
                ],
                'attributes' => [
                    'id' => 'adminaddon_vocabulary_edit_all',
                    'value' => $this->getSets('adminaddon_vocabulary_edit_all')
                ],
            ]);
        $form
            ->add([
                'name' => 'adminaddon_vocabulary_can_delete',
                'type' => 'checkbox',
                'options' => [
                    'label' => 'Element can be deleted', // @translate
                    '' // ''
                ],
                'attributes' => [
                    'id' => 'adminaddon_vocabulary_can_delete',
                    'value' => $this->getSets('adminaddon_vocabulary_can_delete')
                ],
            ]);
        $form
            ->add([
                'name' => 'adminaddon_backup_resource_template',
                'type' => 'checkbox',
                'options' => [
                    'label' => 'Backup Resource Templates', // @translate
                    '' // ''
                ],
                'attributes' => [
                    'id' => 'adminaddon_backup_resource_template',
                    'value' => $this->getSets('adminaddon_backup_resource_template')
                ],
            ]);


        $this->saveSettings();

        $view = new ViewModel;
        $view->setVariable('form', $form);
        return $view;

    }

    private function saveSettings()
    {

        $request = $this->getRequest();
        if ($request->isPost()) {

            $post = $request->getPost()->toArray();
            foreach($this->getConf('settings') as $key => $defval){
                if(isset($post[$key])){
                    $this->setSets($key, $post[$key]);
                }
            }

            $file = OMEKA_PATH . '/config/custom.config.php';
            $config = file_exists($file) ? include $file : [];

            foreach($this->getConf('custom_configs') as $key => $defval){
                if(isset($post[$key])){
                    $config = $this->prepCustomConfig($config, $key, $post[$key]);
                }
            }

            $content = "<?php\n\nreturn " . var_export($config, true) . ";\n";
            file_put_contents($file, $content);

            $message = new Message(
                'Settings save successfully.' // @translate
            );
            $message->setEscapeHtml(false);
            $this->messenger()->addSuccess($message);
            return $this->redirect()->refresh();
        }

    }

    private function prepCustomConfig($config, $key, $val)
    {

        if($key == 'chosen_js_disable'){
            if($val == 'true'){
                $config['AdminAddon']['chosen_js_disable'] = 'true';
            }else{
                if(isset($config['AdminAddon']['chosen_js_disable'])){
                    unset($config['AdminAddon']['chosen_js_disable']);
                }
            }
        }

        if($key == 'replace_helper_ckeditor'){
            if($val == 'true'){
                $config['AdminAddon']['replace_helper_ckeditor'] = 'true';
            }else{
                if(isset($config['AdminAddon']['replace_helper_ckeditor'])){
                    unset($config['AdminAddon']['replace_helper_ckeditor']);
                }
            }
        }

        if(isset($config['AdminAddon']) && empty($config['AdminAddon'])){
            unset($config['AdminAddon']);
        }

        return $config;

    }

    public function getCustomConfVal($key, $child = Null)
    {

        // if($this->isSetNotInCustomConf($key)){
        //     return$this->getConf($key);
        // }else{
            if(!empty($child)){
                $def = $this->getConf('custom_configs', $key.'_'.$child);
            }else{
                $def = $this->getConf('custom_configs', $key);
            }
            $file = OMEKA_PATH . '/config/custom.config.php';
            $config = [];
            if(file_exists($file)){
                if(function_exists('opcache_invalidate')){
                    opcache_invalidate($file, true);    
                }
                $config = (include $file);
            }
            if(!empty($config) && !empty($config['AdminAddon'][$key])){
                if(!empty($child) && !empty($config['AdminAddon'][$key][$child]) &&($config['AdminAddon'][$key][$child] == $child || $config['AdminAddon'][$key][$child] == 'true')){
                    return 'true'; 
                }elseif($config['AdminAddon'][$key] == $key || $config['AdminAddon'][$key] == 'true'){
                    return 'true'; 
                }elseif($def !== False){
                    return $def;
                }                
            }
        // }
        return 'false';
    }

    public function deactivateAllAction()
    {

        $rc = $this->getConnection()->executeQuery("UPDATE `module` SET `is_active` = '0';");
        $this->messenger()->addSuccess('The module was successfully deactivated'); // @translate
        return $this->redirect()->toRoute('admin/default', ['controller' => 'module', 'action' => 'browse'], true);

    }


    public function updoctrineAction()
    {

        if ($this->isAppDevMode() && $this->userIsAllowed('AdminAddon\Controller\Admin\SettingsController', 'updoctrine')){
            $params = [
                'process' => 'UpdateDoctrine',
            ];
            $this->jobDispatcher()->dispatch(\AdminAddon\Job\UpdateDoctrine::class, $params);
            $message = new Message(
                'Update Doctrine Module add to Jobs.' // @translate
            );
            $this->messenger()->addSuccess($message);
        }else{
            $message = new Message(
                'Update Doctrine Module not allowed.' // @translate
            );
            $this->messenger()->addError($message);
        }
        return $this->redirect()->toRoute('admin/admin-addon-settings', ['action' => 'edit']);

    }

    public function uplocaletplAction()
    {

        if ($this->isAppDevMode() && $this->userIsAllowed('AdminAddon\Controller\Admin\SettingsController', 'uplocaletpl')){
            $params = [
                'process' => 'UpdateLocaleTemplate',
            ];
            $this->jobDispatcher()->dispatch(\AdminAddon\Job\UpdateLocaleTemplate::class, $params);
            $message = new Message(
                'Update Locale template add to Jobs.' // @translate
            );
            $this->messenger()->addSuccess($message);
        }else{
            $message = new Message(
                'Update Locale template not allowed.' // @translate
            );
            $this->messenger()->addError($message);
        }
        return $this->redirect()->toRoute('admin/admin-addon-settings', ['action' => 'edit']);

    }


    public function backupsAction()
    {

        $path = $this->getConf('backups');
        $list = glob($path.'*.sql');
        $view = new ViewModel;
        $view->setVariable('list', $list);
        return $view;

    }

    public function backupingAction()
    {

        $settings = $this->getConf('settings');
        
        if($this->getSets('adminaddon_backup_resource_template')){
            $tables = ['vocabulary', 'resource_class', 'property', 'resource_template', 'resource_template_data', 'resource_template_property', 'resource_template_property_data'];
        }else{
            $tables = ['vocabulary', 'resource_class', 'property'];
        }
        $path = $this->getConf('backups');
        $r = $this->backuping_data($settings, $tables, $path);
        $view = new ViewModel;
        $view->setVariable('result', $r);
        return $view;

    }
    
    public function restoreAction()
    {

        $name = $this->params('name');
        $path = $this->getConf('backups');
        if(file_exists($path.$name)){
            $sql = "SET FOREIGN_KEY_CHECKS=0;";
            $sql .= file_get_contents($path.$name);
            $sql .= "SET FOREIGN_KEY_CHECKS=1;";
            try{
                $result = $this->getConnection()->executeStatement($sql);
                $this->messenger()->addSuccess('Restore successfully.'); // @translate
            }catch(\Exception $e){
                $this->getLogger()->err((string) $e);
                $this->messenger()->addError('Restore failed!'); // @translate
            }
        }else{
            $this->messenger()->addError('Restore failed - file no found!'); // @translate
        }
        return $this->redirect()->toRoute('admin/admin-addon-settings', ['action' => 'backups']);
    }

    public function restoreConfirmAction()
    {

        $name = $this->params('name');
        $path = $this->getConf('backups');
        $info = $this->infoAboutBackup($path.$name);
        $form = $this->getForm(ConfirmForm::class);
        $form = $this->getForm(ConfirmForm::class);
        $form->setAttribute('action', $this->url()->fromRoute('admin/admin-addon-settings', ['action' => 'restore', 'name' => $name]));
        $view = new ViewModel();
        $view->setVariable('form', $form);
        $view->setVariable('file', $name);
        $view->setVariable('info', $info);
        $view->setTemplate('admin-addon/admin/settings/restore-confirm');
        return $view->setTerminal(true);

    }

    public function deleteAction()
    {

        if ($this->getRequest()->isPost()) {
            $form = $this->getForm(ConfirmForm::class);
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $name = $this->params('name');
                $path = $this->getConf('backups');
                if (unlink($path.$name)) {
                    $this->messenger()->addSuccess('File backup successfully deleted.'); // @translate
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }
        return $this->redirect()->toRoute('admin/admin-addon-settings', ['action' => 'backups']);

    }

    public function deleteConfirmAction()
    {

        $name = $this->params('name');
        $path = $this->getConf('backups');
        $info = $this->infoAboutBackup($path.$name);
        $form = $this->getForm(ConfirmForm::class);
        $form->setAttribute('action', $this->url()->fromRoute('admin/admin-addon-settings', ['action' => 'delete', 'name' => $name]));
        $view = new ViewModel();
        $view->setVariable('form', $form);
        $view->setVariable('file', $name);
        $view->setVariable('info', $info);
        $view->setTemplate('admin-addon/admin/settings/delete-confirm');
        return $view->setTerminal(true);

    }

    public function detailsAction()
    {

        $name = $this->params('name');
        $path = $this->getConf('backups');
        $info = $this->infoAboutBackup($path.$name);
        $view = new ViewModel();
        $view->setVariable('file', $name);
        $view->setVariable('info', $info);
        return $view->setTerminal(true);

    }

    private function infoAboutBackup($file)
    {

        $content = file_get_contents($file);
        if(stripos($content, 'Begin backup DB') !== False){
            $rc = explode("--\n--  Begin backup DB\n\n\n", $content);
            $r = strtr($rc[0], ["\n" => '<br>', '--' => '']);
        }else{
            $r = 'Information about backup no foud!';
        }
        return $r;

    }

    private function backuping_data($settings, $tables, $path) 
    {

        $time_zone = $this->getSets('time_zone');
        date_default_timezone_set($time_zone);
        $r['timestamp'] = $timestamp = date('Y-m-d H:i:s');
        $dest = $path.date('Y-m-d-H-i-s').'.sql';

        $reader = new \Laminas\Config\Reader\Ini;
        $db = $reader->fromFile(OMEKA_PATH . '/config/database.ini');

        $link = mysqli_connect($db['host'],$db['user'],$db['password'], $db['dbname']);
        mysqli_query($link, "SET NAMES 'utf8'");

        $result = '';
        $result .= "--\n-- Backup Settings\n--\n\n";

        $oi = 1;
        foreach($settings as $name => $defval){
            $value = $this->getSets($name);
            if(!empty($value)){
                if(is_array($value)){
                    $value = json_encode($value);
                }elseif(is_string($value)){
                    $value = strtr($value, ["\r"=> '\r', "\n"=> '\n']);
                    $value = '"'.$value.'"';
                }
                $value = addslashes($value);
                $result .= "DELETE FROM `setting` WHERE `id` = '$name';\n";
                $result .= "INSERT INTO setting VALUES('$name', '$value');\n";
                $totalCount['Settings'] = $oi;
                $oi++;
            }
        }
        $result.="\n\n\n";
        
        foreach($tables as $table)
        {

            $rc = mysqli_query($link, "SELECT * FROM `$table`;");
            $num_fields = mysqli_num_fields($rc);
            $num_rows = mysqli_num_rows($rc);

            $result.= "--\n-- Backup table $table\n--\n\n";
            $result.= 'DROP TABLE IF EXISTS '.$table.';';

            $createTable = mysqli_fetch_row(mysqli_query($link, "SHOW CREATE TABLE `$table`;"));
            $result.= "\n\n".$createTable[1].";\n\n";
            $counter = 1;

            //Over tables
            for ($i = 0; $i < $num_fields; $i++){
            //Over rows
                while($row = mysqli_fetch_row($rc)){   
                    if($counter == 1){
                        $result.= 'INSERT INTO '.$table.' VALUES(';
                    } else{
                        $result.= '(';
                    }

                    //Over fields
                    for($j=0; $j<$num_fields; $j++) 
                    {
                        if(is_string($row[$j])){
                            $row[$j] = addslashes($row[$j]);
                            $row[$j] = str_replace("\n","\\n",$row[$j]);
                        }
                        if(isset($row[$j])) {
                            $result.= '"'.$row[$j].'"' ;
                        }else{
                            $result.= 'Null';
                        }
                        if($j<($num_fields-1)){
                            $result.= ',';
                        }
                    }

                    if($num_rows == $counter){
                        $result.= ");\n";
                    } else{
                        $result.= "),\n";
                    }
                    $counter++;
                }
                $totalCount[$table] = $counter-1;
            }
            $result.="\n\n\n";
        }

        $head = "--    Info about Backup\n--\n--   Timestampe = $timestamp\n\n--   Total count\n";
        foreach($totalCount as $k => $v){
            $r[$k] = $v;
            $head .= "--   $k = $v\n";
        }
        $head .= "--\n--  Begin backup DB\n\n\n";

        $result = $head.$result;
        if(!file_exists($path)){
            mkdir($path, 0755, True);
        }
        if(!file_exists(dirname($path).'/.htaccess')){
            file_put_contents(dirname($path).'/.htaccess', "
<IfModule mod_authz_core.c>
    Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
    Order Allow,Deny
    Deny from all
</IfModule>
");
        }
        file_put_contents($dest, $result);
        return $r;

    }

}