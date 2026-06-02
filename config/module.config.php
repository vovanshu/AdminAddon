<?php

namespace AdminAddon;

return [
    'permissions' => [
        'classes' => [
            'vocabulary' => 'Vocabularies', // @translate
            'properties' => 'Properties', // @translate
            'resourceclasses' => 'Resource Classes', // @translate
        ],
        'labels' => [
            'settings_adminaddon' => 'Settings Admin Addition', // @translate
            'deactivate_all' => 'Deactivate all', // @translate
            'settings_vocabularyaddon' => 'Settings Vocabulary Addition', // @translate
            'jobs' => 'Jobs',  // @translate
            'management' => 'Management',  // @translate
            'control' => 'Control',  // @translate
        ],
        'rules' => [
            'jobs' => [
                'Omeka\Controller\Admin\Job' => [
                    'browse' => [
                        'browse', 'show'
                    ],
                    'management' => [
                        'fix-job', 'clearn', 'delete-error', 'delete'
                    ],
                    'control' => [
                        'stop', 'run', 'terminate'
                    ]
                ]
            ],
            'vocabulary' => [
                'Omeka\Controller\Admin\Vocabulary' => [
                    'browse' => [
                        'browse', 'properties', 'classes',
                    ],
                    'show' => [
                        'show-details', 'show', 'read',
                    ],
                    'add' => [
                        'add', 'import'
                    ],
                    'edit' => [
                        'edit', 'update'
                    ],
                    'delete' => [
                        'delete', 'delete-confirm'
                    ],
                ],
                'Omeka\Api\Adapter\VocabularyAdapter' => [
                    'add' => [
                        'create'
                    ],
                    'edit' => [
                        'update'
                    ],
                    'delete' => [
                        'delete'
                    ],
                ]
            ],
            'properties' => [
                'Omeka\Controller\Admin\Property' => [
                    'browse' => [
                        'browse',
                    ],
                    'show' => [
                        'show-details', 'show', 'read'
                    ],
                    'add' => [
                        'add'
                    ],
                    'edit' => [
                        'edit'
                    ],
                    'delete' => [
                        'delete'
                    ],
                ]
            ],
            'resourceclasses' => [
                'Omeka\Controller\Admin\ResourceClass' => [
                    'browse' => [
                        'browse', 'show-details', 'show', 'read'
                    ],
                    'show' => [
                        'show-details', 'show', 'read'
                    ],
                    'add' => [
                        'add'
                    ],
                    'edit' => [
                        'edit'
                    ],
                    'delete' => [
                        'delete'
                    ],
                ]
            ],
            'modules' => [
                'AdminAddon\Controller\Admin\SettingsController' => [
                    'settings_adminaddon' => [
                        'edit', 'info-about', 'details', 'delete-confirm', 'delete', 'backups', 'backuping', 'restore-confirm', 'restore'
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
        'controller_map' => [
            Controller\Admin\VocabularyControllerDelegator::class => 'omeka/admin/vocabulary',
            Controller\Admin\PropertyControllerDelegator::class => 'omeka/admin/property',
            Controller\Admin\ResourceClassControllerDelegator::class => 'omeka/admin/resource-class',
            Controller\Admin\JobController::class => 'omeka/admin/job',
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
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\Admin\SettingsController::class => Service\Controller\Admin\SettingsControllerFactory::class,
            Controller\AdminAddonController::class => Service\Controller\AdminAddonControllerFactory::class,
            \Omeka\Controller\Admin\PropertyController::class => Service\Controller\Admin\PropertyControllerFactory::class,
            \Omeka\Controller\Admin\ResourceClassController::class => Service\Controller\Admin\ResourceClassControllerFactory::class,
            \Omeka\Controller\Admin\JobController::class => Service\Controller\Admin\JobControllerFactory::class,
        ],
        'delegators' => [
            'Omeka\Controller\Admin\Vocabulary' => [
                Service\Controller\Admin\VocabularyControllerDelegatorFactory::class
            ],
        ]
    ],
    'form_elements' => [
        'factories' => [
            'Omeka\Form\LoginForm' => Service\Form\LoginFormFactory::class,
            'Omeka\Form\ForgotPasswordForm' => Service\Form\ForgotPasswordFormFactory::class,
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
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/admin-addon-settings[/:action][/:name]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'name' => '[.a-zA-Z0-9_-]*',
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'AdminAddon\Controller\Admin',
                                '__CONTROLLER__' => 'settings',
                                'controller' => Controller\Admin\SettingsController::class,
                                'action' => 'edit',
                            ],
                        ],
                    ],
                ],
            ],
            'api' => [
                'child_routes' => [
                    'admin-addon-controller' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/admin-addon/:action[/:site-slug]',
                            'constraints' => [                               
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'site-slug' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'AdminAddon\Controller',
                                'controller' => Controller\AdminAddonController::class,
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
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
        ],
    ],
    'AdminAddon' => [
        'debug' => False,
        'backups' => OMEKA_PATH.'/files/backup/AdminAddon/',
        'settings' => [
            // 'adminaddon_replace_helper_ckeditor' => 'false',
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
            // 'adminaddon_chosen_js_disable' => 'false',
            'adminaddon_advsearch_autocomplete' => 'false',
            'adminaddon_advsearch_public_autocomplete' => 'false',
            'adminaddon_advsearch_autocomplete_fields' => [],
            'adminaddon_forms_autocomplete' => 'false',
            'adminaddon_forms_autocomplete_fields' => [],
            'adminaddon_search_fasets_enable' => 'false',
            'adminaddon_search_fasets' => '',
            'adminaddon_render_by_js' => 'false',
            'adminaddon_vocabulary_edit_all' => 'false',
            'adminaddon_vocabulary_can_delete' => 'false',
            'adminaddon_backup_resource_template' => 'false',
        ],
        'custom_configs' => [
            'replace_helper_ckeditor' => 'false',
            'chosen_js_disable' => 'false',
        ],
        'modes_admin_ui' => [
            'default' => [
                'label' => 'Default', // @translate
            ],
            'simpleapp' => [
                'label' => 'Simple application', // @translate
                'controllers' => [
                    'general' => [
                        'item' => [],
                        'media' => [],
                        'item-set' => [],
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
                        'settings' => [],
                        'Index' => ['index', 'browse', 'show', 'edit', 'theme', 'theme-settings', 'theme-resource-pages', 'resources', 'navigation', 'users'],
                        'index' => ['browse'],
                        'Page' => ['index', 'show', 'edit'],
                        'Omeka\Controller\Login' => ['login', 'forgot-password'],
                        'event' => ['browse'],
                        'check-and-fix' => ['index'],
                        'theme' => ['index'],
                        'Admin\Index' => ['index'],
                        'Log\Controller\Admin\LogController' => ['browse'],
                        'Reference\Controller\Admin\ReferenceController' => ['browse'],
                    ]
                ],
                'actions' => [
                    'general' => ['browse']
                ],
                'styles' => [
                    'general' => [
                        'css' => ['css/simpleapp.css' => 'AdminAddon'],
                        'js' => ['js/simpleapp.js' => 'AdminAddon'],
                    ],
                ]
            ],
        ],
        'compatible_autocomplete_facets' => [
            'controllers' => ['Item', 'item', 'media', 'item-set'],
            'actions' => ['browse', 'search', 'add', 'edit'],
        ],
    ]
];
