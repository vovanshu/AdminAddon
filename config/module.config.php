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
            'formCollectionElementGroups' => Form\View\Helper\FormCollectionElementGroups::class,
            'formCollectionElementGroupsCollapsible' => Form\View\Helper\FormCollectionElementGroupsCollapsible::class,
        ],
        'factories' => [
            'AdminAddon' => Service\ControllerPlugin\GeneralPluginFactory::class,
            // 'AdminAddonFacets' => Service\ViewHelper\FacetsFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\Admin\SettingsController::class => Service\Controller\Admin\SettingsControllerFactory::class,
            Controller\AdminAddonController::class => Service\Controller\AdminAddonControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            'Omeka\Form\LoginForm' => Service\Form\LoginFormFactory::class,
            'Omeka\Form\ForgotPasswordForm' => Service\Form\ForgotPasswordFormFactory::class,
            // Form\SettingsFieldset::class => Form\SettingsFieldsetFactory::class
            // Form\SiteSettingsFieldset::class => Form\SiteSettingsFieldsetFactory::class
        ],
    ],
    'service_manager' => [
        'factories' => [
            'AdminAddon' => Service\ControllerPlugin\GeneralPluginFactory::class,
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
                    'admin-addon-controller' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/admin-addon[/:action]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'AdminAddon\Controller',
                                'controller' => Controller\AdminAddonController::class,
                                'action' => 'suggestions',
                            ],
                        ],
                    ],
                ],
            ],
            'site' => [
                'child_routes' => [
                    'admin-addon-controller' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/admin-addon[/:action]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'AdminAddon\Controller',
                                'controller' => Controller\AdminAddonController::class,
                                'action' => 'suggestions',
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
    'AdminAddon' => [
        'developing' => False,
        'debug' => False,
        'settings' => [
            'adminaddon_replace_helper_ckeditor' => 'false',
            'adminaddon_editor_change_in_setting' => 'false',
            'adminaddon_html_mode_page' => 'inline',
            'adminaddon_html_config_page' => 'default',
            'adminaddon_mode_admin_ui' => 'default',
            'adminaddon_search_form_inmenu_hidden' => 'false',
            'recaptcha_enable_on_login' => 'false',
            'recaptcha_enable_on_forgot_password' => 'false',
            'recaptcha_ip_white_list' => '',
            'adminaddon_menuadmindashboard_enable' => 'false',
            'adminaddon_menuadmindashboard_label' => '',
            'adminaddon_menuadmindashboard' => '',
            'adminaddon_select2_enable' => 'false',
            'adminaddon_select2_enable_public' => 'false',
            'adminaddon_chosen_js_disable' => 'false',
            'adminadon_advsearch_autocomplete' => 'false',
            'adminadon_advsearch_public_autocomplete' => 'false',
            'adminadon_advsearch_autocomplete_fields' => [],
            'adminadon_forms_autocomplete' => 'false',
            'adminadon_forms_autocomplete_fields' => [],
            'adminadon_search_fasets_enable' => 'false',
            'adminadon_search_fasets' => '',
            'adminadon_render_by_js' => 'false',
        ],
        'options' => [
            'replace_helper_ckeditor' => 'adminaddon_replace_helper_ckeditor',
            'editor_change_in_setting' => 'adminaddon_editor_change_in_setting',
            'html_mode' => 'adminaddon_html_mode_page',
            'html_config' => 'adminaddon_html_config_page',
            'mode_admin_ui' => 'adminaddon_mode_admin_ui',
            'search_form_hidden' => 'adminaddon_search_form_inmenu_hidden',
            'recaptcha_enable_on_login' => 'recaptcha_enable_on_login',
            'recaptcha_enable_on_forgot_password' => 'recaptcha_enable_on_forgot_password',
            'recaptcha_ip_white_list' => 'recaptcha_ip_white_list',
            'menuadmindashboard' => 'adminaddon_menuadmindashboard',
            'menuadmindashboard_enable' => 'adminaddon_menuadmindashboard_enable',
            'menuadmindashboard_label' => 'adminaddon_menuadmindashboard_label',
            'select2' => 'adminaddon_select2_enable',
            'select2public' => 'adminaddon_select2_enable_public',
            'chosen_js_disable' => 'adminaddon_chosen_js_disable',
            'advsearch_autocomplete' => 'adminadon_advsearch_autocomplete',
            'advsearch_public_autocomplete' => 'adminadon_advsearch_public_autocomplete',
            'forms_autocomplete' => 'adminadon_forms_autocomplete',
            'advsearch_autocomplete_fields' => 'adminadon_advsearch_autocomplete_fields',
            'forms_autocomplete_fields' => 'adminadon_forms_autocomplete_fields',
            'search_fasets_enable' => 'adminadon_search_fasets_enable',
            'search_fasets' => 'adminadon_search_fasets',
            'render_by_js' => 'adminadon_render_by_js',
        ],
        'site_settings' => [
            'adminadon_advsearch_autocomplete' => 'false',
            'adminadon_advsearch_autocomplete_fields' => [],
        ],
        'custom_configs' => [
            'adminaddon_replace_helper_ckeditor',
            'adminaddon_chosen_js_disable'
        ],
        'modes_admin_ui' => [
            'default' => [
                'label' => 'Default', // @translate
            ],
            'simpleapp' => [
                'label' => 'Simple application', // @translate
                'controllers' => [
                    'general' => [
                        'AdminAddon\Controller\Admin\SettingsController' => ['edit'],
                        'item' => ['browse', 'show', 'add', 'edit', 'search'],
                        'media' => ['browse', 'show', 'add', 'edit', 'search'],
                        'item-set' => ['browse', 'show', 'add', 'edit', 'search'],
                        'vocabulary' => ['browse', 'classes', 'properties'],
                        'resource-template' => ['browse', 'add', 'edit'],
                        'user' => ['browse'],
                        'module' => ['browse', 'index'],
                        'job' => ['browse'],
                        'asset' => ['browse'],
                        'roles' => ['browse', 'show', 'edit'],
                        'item-review' => ['browse', 'edit'],
                        'item-review-contracts' => ['browse'],
                        'setting' => ['browse'],
                        'settings' => ['edit', 'backups'],
                        'Index' => ['index', 'browse', 'show', 'edit', 'theme', 'theme-settings', 'theme-resource-pages', 'resources', 'navigation', 'users'],
                        'index' => ['browse'],
                        'Page' => ['index', 'show', 'edit'],
                        'Omeka\Controller\Login' => ['login', 'forgot-password'],
                        'event' => ['browse'],
                        'check-and-fix' => ['index'],
                        'theme' => ['index'],
                        'Admin\Index' => ['index'],
                        'Log\Controller\Admin\LogController' => ['browse'],
                    ]
                ],
                'actions' => [
                    'general' => ['browse']
                ],
                'styles' => [
                    'general' => [
                        'css' => ['css/simpleapp.css'],
                        'js' => ['js/simpleapp.js'],
                    ],
                ]
            ],
        ],
    ]
];
