<?php declare(strict_types=1);

namespace AdminAddon\Form;

use Laminas\Form\Fieldset;
use Omeka\Form\Element\PropertySelect;
use AdminAddon\General;

class SettingsFieldset extends Fieldset
{

    use General;

    protected $form;

    public function setForm($form)
    {
        $this->form = $form;
    }

    public function init(): void
    {

        $options = $this->form->getOptions();

        $modesAdmiUI = $this->getConf('modes_admin_ui');
        foreach($modesAdmiUI as $k => $v){
            $modes[$k] = $v['label'];
        }

        $this->form->add([
                'name' => $this->getOps('mode_admin_ui'),
                'type' => 'select',
                'options' => [
                    'element_group' => 'display',
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

        $this->form->add([
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

        $this->form->add([
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


        $this->form->add([
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

        $this->form->add([
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

        $this->form->add([
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
        
        $this->form->add([
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

        $this->form->add([
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

        $this->form->add([
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

        $this->form->add([
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

        $this->form->add([
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

        $this->form->add([
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

        $this->form->add([
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
                'class' => 'edit-ini-textarea'
            ],
        ]);

        $this->form->add([
                'name' => $this->getOps('advsearch_autocomplete'),
                'type' => 'checkbox',
                'options' => [
                    'element_group' => 'search',
                    'label' => 'Autocomplete on Advanced search', // @translate
                    'checked_value' => 'true',
                    'unchecked_value' => 'false',
                ],
                'attributes' => [
                    'id' => $this->getOps('advsearch_autocomplete'),
                    'value' => $this->getSets('advsearch_autocomplete'),
                ],
            ]);

        $allowedEmpty[] = $this->getOps('advsearch_autocomplete');

        $this->form->add([
                'name' => $this->getOps('advsearch_autocomplete_fields'),
                'type' => PropertySelect::class,
                'options' => [
                    'element_group' => 'search',
                    'empty_option' => '[Any Property]', // @translate 
                    'label' => 'Properties for autocomplete input fields in Advanced search', // @translate
                    'info' => 'Select properties for autocomplete in Advanced search input fields.', // @translate
                    'term_as_value' => true,
                ],
                'attributes' => [
                    'multiple' => true,
                    'class' => 'chosen-select',
                    'data-placeholder' => 'Select properties…', // @translate
                    'id' => $this->getOps('advsearch_autocomplete_fields'),
                    'value' => $this->getSets('advsearch_autocomplete_fields'),
                ],
            ]);

        $allowedEmpty[] = $this->getOps('advsearch_autocomplete_fields');

        $this->form->add([
                'name' => $this->getOps('forms_autocomplete'),
                'type' => 'checkbox',
                'options' => [
                    'element_group' => 'editing',
                    'label' => 'Autocomplete on forms', // @translate
                    'info' => 'Autocomplete input fields in forms on the page for adding and editing items, item sets, and media files.', // @translate
                    'checked_value' => 'true',
                    'unchecked_value' => 'false',
                ],
                'attributes' => [
                    'id' => $this->getOps('forms_autocomplete'),
                    'value' => $this->getSets('forms_autocomplete'),
                ],
            ]);

        $this->form->add([
                'name' => $this->getOps('forms_autocomplete_fields'),
                'type' => PropertySelect::class,
                'options' => [
                    'element_group' => 'editing',
                    'empty_option' => '[Any Property]', // @translate 
                    'label' => 'Properties for autocomplete input fields in forms', // @translate
                    'info' => 'Select properties for autocomplete in form input fields.', // @translate
                    'term_as_value' => true,
                ],
                'attributes' => [
                    'multiple' => true,
                    'class' => 'chosen-select',
                    'data-placeholder' => 'Select properties…', // @translate
                    'id' => $this->getOps('forms_autocomplete_fields'),
                    'value' => $this->getSets('forms_autocomplete_fields'),
                ],
            ]);

        $allowedEmpty[] = $this->getOps('forms_autocomplete_fields');


        $this->form->add([
            'type' => 'checkbox',
            'name' => $this->getOps('search_fasets_enable'),
            'options' => [
                'element_group' => 'search',
                'label' => 'Enable search fasets', // @translate
                'info' => 'Enable search fasets config', // @translate
                'checked_value' => 'true',
                'unchecked_value' => 'false',
            ],
            'attributes' => [
                'value' => $this->getSets('search_fasets_enable'),
                'id' => $this->getOps('search_fasets_enable'),
            ],
        ]);

        $this->form->add([
            'name' => $this->getOps('search_fasets'),
            'type' => 'textarea',
            'options' => [
                'element_group' => 'search',
                'label' => 'Search fasets config', // @translate
                'info' => '', // @translate
            ],
            'attributes' => [
                'value' => $this->getSets('search_fasets'),
                'id' => $this->getOps('search_fasets'),
                'class' => 'edit-ini-textarea'
            ],
        ]);

        $allowedEmpty[] = $this->getOps('search_fasets');

        $this->form->setOption('element_groups', $options['element_groups']);

        $inputFilter = $this->form->getInputFilter();
        $this->inputFilterAllowEmpty($inputFilter, $allowedEmpty);

    }
}
