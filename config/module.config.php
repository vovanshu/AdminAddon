<?php

namespace AdminAddon;

return [
    'permissions' => [
        'labels' => [
            'settings_adminaddon' => 'Settings Admin Addition', // @translate
            'deactivate_all' => 'Deactivate all', // @translate
            // ''
        ],
        'rules' => [
            'modules' => [
                'AdminAddon\Controller\Admin\SettingsController' => [
                    'settings_adminaddon' => [
                        'edit',
                    ],
                    'deactivate_all' => [
                        'deactivate-all',
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'ckEditor' => View\Helper\CkEditor::class,
        ],
        'factories' => [
            'AdminAddonCommon' => Service\ControllerPlugin\CommonPluginFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\Admin\SettingsController::class => Service\Controller\Admin\SettingsControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            'Omeka\Form\LoginForm' => Service\Form\LoginFormFactory::class,
            'Omeka\Form\ForgotPasswordForm' => Service\Form\ForgotPasswordFormFactory::class,
        ],
        'invokables' => [
            // Form\SettingsFieldset::class => Form\SettingsFieldset::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            'AdminAddon\Common' => Service\ControllerPlugin\CommonPluginFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'admin' => [
                'child_routes' => [
                    'admin-addon-settings' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/admin-addon-settings[/:action][/:name]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'name' => '[.a-zA-Z0-9_-]*',
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'AdminAddon\Controller\Admin',
                                'controller' => Controller\Admin\SettingsController::class,
                                'action' => 'edit',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => dirname(__DIR__) . '/language',
                'pattern' => '%s.mo',
                'text_domain' => null,
            ],
            [
                'type' => 'gettext',
                'base_dir' => OMEKA_PATH . '/files/languages/AdminAddon',
                'pattern' => '%s.mo',
                'text_domain' => null,
            ],
        ],
    ],
    'adminaddon' => [
        'developing' => False,
        'debug' => False,
        'settings' => [
            'adminaddon_editor_change_in_setting' => 'false',
            'adminaddon_html_mode_page' => 'inline',
            'adminaddon_html_config_page' => 'default',
            'adminaddon_mode_admin_ui' => 'default',
            'recaptcha_enable_on_login' => 'false',
            'recaptcha_enable_on_forgot_password' => 'false',
            'recaptcha_ip_white_list' => '',
        ],
        'options' => [
            'editor_change_in_setting' => 'adminaddon_editor_change_in_setting',
            'html_mode' => 'adminaddon_html_mode_page',
            'html_config' => 'adminaddon_html_config_page',
            'mode_admin_ui' => 'adminaddon_mode_admin_ui',
            'recaptcha_enable_on_login' => 'recaptcha_enable_on_login',
            'recaptcha_enable_on_forgot_password' => 'recaptcha_enable_on_forgot_password',
            'recaptcha_ip_white_list' => 'recaptcha_ip_white_list',
        ],
        'modes_admin_ui' => [
            'default' => [
                'label' => 'Default', // @translate
            ],
            'simpleapp' => [
                'label' => 'Simple application', // @translate
                'controllers' => [
                    'general' => [
                        'item' => ['browse', 'show'],
                        'media' => ['browse', 'show'],
                        'item-set' => ['browse', 'show'],
                        'vocabulary' => ['browse', 'classes', 'properties'],
                        'resource-template' => ['browse'],
                        'user' => ['browse'],
                        'module' => ['browse'],
                        'job' => ['browse'],
                        'asset' => ['browse'],
                        'roles' => ['browse', 'show', 'edit'],
                        'item-review' => ['browse', 'edit'],
                        'item-review-contracts' => ['browse'],
                        'settings' => ['browse', 'edit', 'backups'],
                        'Omeka\Controller\Login' => ['login', 'forgot-password']
                    ]
                ],
                'actions' => [
                    'general' => ['browse']
                ],
                'styles' => [
                    'general' => [
                        'css' => ['css/adminaddon.css'],
                        'js' => [],
                    ],
                ]
            ],
        ],
    ]
];
