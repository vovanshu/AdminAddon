<?php declare(strict_types=1);
namespace AdminAddon\Controller\Admin;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\Form\Form;
use Omeka\Stdlib\Message;
use AdminAddon\Common;

class SettingsController extends AbstractActionController
{

    use Common;

    public function editAction()
    {

        $form = $this->getForm(Form::class);

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
                    'class' => 'chosen-select',
                    'data-placeholder' => 'Select mode admin UI', // @translate
                    'value' => $this->getSets('mode_admin_ui')
                ],
            ]);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost()->toArray();
            foreach($this->getConf('options') as $key){
                if(isset($post[$key])){
                    $this->setSets($key, $post[$key]);
                }
            }
            $message = new Message(
                'Settings save successfully.' // @translate
            );
            $message->setEscapeHtml(false);
            $this->messenger()->addSuccess($message);
            return $this->redirect()->refresh();
        }

        $view = new ViewModel;
        $view->setVariable('form', $form);
        return $view;

    }

    public function deactivateAllAction()
    {

        $rc = $this->getConnection()->executeQuery("UPDATE `module` SET `is_active` = '0';");
        $this->messenger()->addSuccess('The module was successfully deactivated'); // @translate
        return $this->redirect()->toRoute('admin/default', ['controller' => 'module', 'action' => 'browse'], true);

    }

}
