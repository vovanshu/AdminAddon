<?php declare(strict_types=1);

namespace AdminAddon\Form;

use Laminas\Form\Fieldset;
use Omeka\Form\Element\PropertySelect;
use AdminAddon\TraitGeneral;

class SettingsFieldset extends Fieldset
{

    use TraitGeneral;

    public function addFields($form): void
    {

        $options = $form->getOptions();

        $modesAdmiUI = $this->getConf('modes_admin_ui');
        foreach($modesAdmiUI as $k => $v){
            $modes[$k] = $v['label'];
        }

        $form->add([
                'name' => 'adminaddon_mode_admin_ui',
                'type' => 'select',
                'options' => [
                    'element_group' => 'display',
                    'label' => 'Mode admin UI', // @translate
                    'value_options' => $modes,
                    'use_hidden_element' => true,
                ],
                'attributes' => [
                    'id' => 'adminaddon_mode_admin_ui',
                    'multiple' => false,
                    'required' => false,
                    'class' => 'select',
                    'data-placeholder' => 'Select mode admin UI', // @translate
                    'value' => $this->getSets('adminaddon_mode_admin_ui')
                ],
            ]);

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
                'value' => $this->getSets('adminaddon_html_mode_page')
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
                'value' => $this->getSets('adminaddon_html_config_page')
            ],
        ]);


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
                'value' => $this->getSets('adminaddon_menuadmindashboard_label'),
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
                'value' => $this->getSets('adminaddon_menuadmindashboard_enable'),
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
                'value' => $this->getSets('adminaddon_menuadmindashboard'),
                'id' => 'adminaddon_menuadmindashboard',
                'class' => 'edit-ini-textarea'
            ],
        ]);

        $form->add([
                'name' => 'adminaddon_advsearch_autocomplete',
                'type' => 'checkbox',
                'options' => [
                    'element_group' => 'search',
                    'label' => 'Autocomplete on Advanced search', // @translate
                    'checked_value' => 'true',
                    'unchecked_value' => 'false',
                ],
                'attributes' => [
                    'id' => 'adminaddon_advsearch_autocomplete',
                    'value' => $this->getSets('adminaddon_advsearch_autocomplete'),
                ],
            ]);

        $this->FilterAllowEmpty[] = 'adminaddon_advsearch_autocomplete';

        $form->add([
                'name' => 'adminaddon_advsearch_autocomplete_fields',
                'type' => PropertySelect::class,
                'required' => false,
                'options' => [
                    'element_group' => 'search',
                    'label' => 'Properties for autocomplete input fields in Advanced search', // @translate
                    'info' => 'Select properties for autocomplete in input fields. Leave field empty for all properties.', // @translate
                    'term_as_value' => true,
                ],
                'attributes' => [
                    'multiple' => true,
                    'class' => 'chosen-select',
                    'data-placeholder' => 'Select properties…', // @translate
                    'id' => 'adminaddon_advsearch_autocomplete_fields',
                    'value' => $this->getSets('adminaddon_advsearch_autocomplete_fields'),
                ],
            ]);

        $this->FilterAllowEmpty[] = 'adminaddon_advsearch_autocomplete_fields';

        $form->add([
                'name' => 'adminaddon_forms_autocomplete',
                'type' => 'checkbox',
                'options' => [
                    'element_group' => 'editing',
                    'label' => 'Autocomplete on forms', // @translate
                    'info' => 'Autocomplete input fields in forms on the page for adding and editing items, item sets, and media files.', // @translate
                    'checked_value' => 'true',
                    'unchecked_value' => 'false',
                ],
                'attributes' => [
                    'id' => 'adminaddon_forms_autocomplete',
                    'value' => $this->getSets('adminaddon_forms_autocomplete'),
                ],
            ]);

        $form->add([
                'name' => 'adminaddon_forms_autocomplete_fields',
                'type' => PropertySelect::class,
                'required' => false,
                'options' => [
                    'element_group' => 'editing',
                    'label' => 'Properties for autocomplete input fields in forms', // @translate
                    'info' => 'Select properties for autocomplete in input fields. Leave field empty for all properties.', // @translate
                    'term_as_value' => true,
                ],
                'attributes' => [
                    'multiple' => true,
                    'class' => 'chosen-select',
                    'data-placeholder' => 'Select properties…', // @translate
                    'id' => 'adminaddon_forms_autocomplete_fields',
                    'value' => $this->getSets('adminaddon_forms_autocomplete_fields'),
                ],
            ]);

        $this->FilterAllowEmpty[] = 'adminaddon_forms_autocomplete_fields';

        // $form->add([
        //     'type' => 'checkbox',
        //     'name' => 'adminaddon_search_fasets_enable',
        //     'options' => [
        //         'element_group' => 'search',
        //         'label' => 'Enable search fasets', // @translate
        //         'info' => 'Enable search fasets config', // @translate
        //         'checked_value' => 'true',
        //         'unchecked_value' => 'false',
        //     ],
        //     'attributes' => [
        //         'value' => $this->getSets('adminaddon_search_fasets_enable'),
        //         'id' => 'adminaddon_search_fasets_enable',
        //     ],
        // ]);

        // $form->add([
        //     'name' => 'adminaddon_search_fasets',
        //     'type' => 'textarea',
        //     'options' => [
        //         'element_group' => 'search',
        //         'label' => 'Search fasets config', // @translate
        //         'info' => '', // @translate
        //     ],
        //     'attributes' => [
        //         'value' => $this->getSets('adminaddon_search_fasets'),
        //         'id' => 'adminaddon_search_fasets',
        //         'class' => 'edit-ini-textarea'
        //     ],
        // ]);

        // $this->FilterAllowEmpty[] = 'adminaddon_search_fasets';

        $form->setOption('element_groups', $options['element_groups']);

    }
}
