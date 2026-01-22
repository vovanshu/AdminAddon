<?php declare(strict_types=1);

namespace AdminAddon\Form;

use Common\Form\Element as CommonElement;
use Laminas\Form\Fieldset;

class SettingsFieldset extends Fieldset
{
    protected $label = 'Admin Addon'; // @translate

    protected $elementGroups = [
        'adminaddon' => 'Admin Addon', // @translate
    ];

    public function init(): void
    {

        $this->setAttribute('id', 'adminaddon')
            ->setOption('element_groups', $this->elementGroups);

        $this->add([
            'name' => 'adminaddon_html_mode_page',
            'type' => CommonElement\OptionalRadio::class,
            'options' => [
                'element_group' => 'adminaddon',
                'label' => 'Html edition mode for pages', // @translate
                'value_options' => [
                    'inline' => 'Inline (default)', // @translate
                    'document' => 'Document (maximizable)', // @translate
                ],
            ],
            'attributes' => [
                'id' => 'adminaddon_html_mode_page',
            ],
        ]);

        $this->add([
            'name' => 'adminaddon_html_config_page',
            'type' => CommonElement\OptionalRadio::class,
            'options' => [
                'element_group' => 'adminaddon',
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
            ],
        ]);

        $this->add([
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

        $this->add([
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

        $this->add([
            'name' => 'recaptcha_ip_white_list',
            'type' => 'textarea',
            'options' => [
                'element_group' => 'security',
                'label' => 'IP whitelist for reCAPTCHA', // @translate
                'info' => 'Enter a single IP address or a range of IP addresses separated by dashes (IPbegin-IPend) in the line to whitelist for reCAPTCHA.', // @translate
            ],
            'attributes' => [
                'value' => $this->getSets('recaptcha_ip_white_list'),
                'id' => 'recaptcha_ip_white_list',
            ],
        ]);

    }
}
