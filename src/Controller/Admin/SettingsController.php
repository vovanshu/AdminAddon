<?php declare(strict_types=1);
namespace AdminAddon\Controller\Admin;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\Form\Form;
use Omeka\Stdlib\Message;
use AdminAddon\General;

class SettingsController extends AbstractActionController
{

    use General;

    public function editAction()
    {

        $form = $this->getForm(Form::class);

        $form->add([
                'name' => $this->getOps('replace_helper_ckeditor'),
                'type' => 'checkbox',
                'options' => [
                    'label' => 'Replace ckEditor Helper', // @translate
                    'checked_value' => 'true',
                    'unchecked_value' => 'false',
                ],
                'attributes' => [
                    'id' => $this->getOps('replace_helper_ckeditor'),
                    'value' => $this->getSets('replace_helper_ckeditor'),
                ],
            ]);

        $form->add([
                'name' => $this->getOps('editor_change_in_setting'),
                'type' => 'checkbox',
                'options' => [
                    'label' => 'Allow change Editor config in the setting', // @translate
                    'checked_value' => 'true',
                    'unchecked_value' => 'false',
                ],
                'attributes' => [
                    'id' => $this->getOps('editor_change_in_setting'),
                    'value' => $this->getSets('editor_change_in_setting'),
                ],
            ]);

        $form->add([
            'name' => $this->getOps('html_mode'),
            'type' => 'radio',
            'options' => [
                // 'element_group' => 'editing',
                'label' => 'Html edition mode for pages', // @translate
                'value_options' => [
                    'inline' => 'Inline (default)', // @translate
                    'document' => 'Document (maximizable)', // @translate
                ],
            ],
            'attributes' => [
                'id' => $this->getOps('html_mode'),
                'value' => $this->getSets('html_mode'),
            ],
        ]);

        $form->add([
            'name' => $this->getOps('html_config'),
            'type' => 'radio',
            'options' => [
                // 'element_group' => 'editing',
                'label' => 'Html edition config and toolbar for pages', // @translate
                'value_options' => [
                    // @see https://ckeditor.com/cke4/presets-all
                    'default' => 'Default', // @translate
                    'standard' => 'Standard', // @translate
                    'full' => 'Full', // @translate
                ],
            ],
            'attributes' => [
                'id' => $this->getOps('html_config'),
                'value' => $this->getSets('html_config'),
            ],
        ]);

        $modesAdmiUI = $this->getConf('modes_admin_ui');
        foreach($modesAdmiUI as $k => $v){
            $modes[$k] = $v['label'];
        }

        $form->add([
                'name' => $this->getOps('mode_admin_ui'),
                'type' => 'select',
                'options' => [
                    'label' => 'Mode admin UI', // @translate
                    'value_options' => $modes,
                    'use_hidden_element' => true,
                ],
                'attributes' => [
                    'id' => $this->getOps('mode_admin_ui'),
                    'multiple' => false,
                    'required' => false,
                    'class' => 'select',
                    'data-placeholder' => 'Select mode admin UI', // @translate
                    'value' => $this->getSets('mode_admin_ui')
                ],
            ]);

        $form->add([
                'name' => $this->getOps('search_form_hidden'),
                'type' => 'checkbox',
                'options' => [
                    'label' => 'Hiding the Search form field in the Admin menu', // @translate
                    'checked_value' => 'true',
                    'unchecked_value' => 'false',
                ],
                'attributes' => [
                    'id' => $this->getOps('search_form_hidden'),
                    'value' => $this->getSets('search_form_hidden'),
                ],
            ]);

        $form->add([
                'name' => $this->getOps('select2'),
                'type' => 'checkbox',
                'options' => [
                    'label' => 'Select2 enable', // @translate
                    'checked_value' => 'true',
                    'unchecked_value' => 'false',
                ],
                'attributes' => [
                    'id' => $this->getOps('select2'),
                    'value' => $this->getSets('select2'),
                ],
            ]);

        $form->add([
                'name' => $this->getOps('select2public'),
                'type' => 'checkbox',
                'options' => [
                    'label' => 'Select2 enable on public', // @translate
                    'checked_value' => 'true',
                    'unchecked_value' => 'false',
                ],
                'attributes' => [
                    'id' => $this->getOps('select2public'),
                    'value' => $this->getSets('select2public'),
                ],
            ]);

        $form->add([
                'name' => $this->getOps('chosen_js_disable'),
                'type' => 'checkbox',
                'options' => [
                    'label' => 'Chosen-js disable', // @translate
                    'checked_value' => 'true',
                    'unchecked_value' => 'false',
                ],
                'attributes' => [
                    'id' => $this->getOps('chosen_js_disable'),
                    'value' => $this->getSets('chosen_js_disable'),
                ],
            ]);

        $form->add([
                'name' => $this->getOps('advsearch_autocomplete'),
                'type' => 'checkbox',
                'options' => [
                    'label' => 'Autocomplete on Advanced search', // @translate
                    'checked_value' => 'true',
                    'unchecked_value' => 'false',
                ],
                'attributes' => [
                    'id' => $this->getOps('advsearch_autocomplete'),
                    'value' => $this->getSets('advsearch_autocomplete'),
                ],
            ]);

        $form->add([
                'name' => $this->getOps('advsearch_public_autocomplete'),
                'type' => 'checkbox',
                'options' => [
                    'label' => 'Autocomplete on public Advanced search', // @translate
                    'checked_value' => 'true',
                    'unchecked_value' => 'false',
                ],
                'attributes' => [
                    'id' => $this->getOps('advsearch_public_autocomplete'),
                    'value' => $this->getSets('advsearch_public_autocomplete'),
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
            $ops_custom_configs = $this->getConf('custom_configs');

            $file = OMEKA_PATH . '/config/custom.config.php';
            $config = file_exists($file) ? include $file : [];

            $post = $request->getPost()->toArray();
            foreach($this->getConf('options') as $key){
                if(isset($post[$key])){
                    $this->setSets($key, $post[$key]);
                    if(in_array($key, $ops_custom_configs)){
                        $config = $this->prepCustomConfig($config, $key, $post[$key]);
                    }
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

        if($key == 'adminaddon_chosen_js_disable'){
            if($val == 'true'){
                $config['assets']['internals']['vendor/chosen-js/chosen.css'] = 'AdminAddon';
                $config['assets']['internals']['vendor/chosen-js/chosen.jquery.js'] = 'AdminAddon';
                $config['assets']['internals']['js/chosen-options.js'] = 'AdminAddon';
            }else{
                if(isset($config['assets']['internals']['vendor/chosen-js/chosen.css'])){
                    unset($config['assets']['internals']['vendor/chosen-js/chosen.css']);
                }
                if(isset($config['assets']['internals']['vendor/chosen-js/chosen.jquery.js'])){
                    unset($config['assets']['internals']['vendor/chosen-js/chosen.jquery.js']);
                }
                if(isset($config['assets']['internals']['js/chosen-options.js'])){
                    unset($config['assets']['internals']['js/chosen-options.js']);
                }
                if(!empty($config['assets']['internals'])){
                    unset($config['assets']['internals']);
                }
                if(!empty($config['assets'])){
                    unset($config['assets']);
                }
            }
        }

        if($key == 'adminaddon_replace_helper_ckeditor'){
            if($val == 'true'){
                $config['view_helpers']['invokables']['ckEditor'] = '\AdminAddon\View\Helper\CkEditor';
            }else{
                if(isset($config['view_helpers']['invokables']['ckEditor'])){
                    unset($config['view_helpers']['invokables']['ckEditor']);
                }
                if(!empty($config['view_helpers']['invokables'])){
                    unset($config['view_helpers']['invokables']);
                }
                if(!empty($config['view_helpers'])){
                    unset($config['view_helpers']);
                }
            }
        }

        return $config;

    }

    public function deactivateAllAction()
    {

        $rc = $this->getConnection()->executeQuery("UPDATE `module` SET `is_active` = '0';");
        $this->messenger()->addSuccess('The module was successfully deactivated'); // @translate
        return $this->redirect()->toRoute('admin/default', ['controller' => 'module', 'action' => 'browse'], true);

    }

}
