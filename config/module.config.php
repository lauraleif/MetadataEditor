<?php
namespace MetadataEditor;

return [
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
    ],
    'form_elements' => [
        'factories' => [
            'MetadataEditor\Form\MainForm' => Service\Form\MainFormFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            'MetadataEditor\Controller\Index' => Service\Controller\IndexControllerFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'admin' => [
                'child_routes' => [
                    'metadataeditor' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/metadataeditor',
                            'defaults' => [
                                '__NAMESPACE__' => 'MetadataEditor\Controller',
                                'controller' => 'Index',
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'preview' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/preview',
                                    'defaults' => [
                                        '__NAMESPACE__' => 'MetadataEditor\Controller',
                                        'controller' => 'Index',
                                        'action' => 'preview',
                                    ],
                                ],
                            ],
                            'replace' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/replace',
                                    'defaults' => [
                                        '__NAMESPACE__' => 'MetadataEditor\Controller',
                                        'controller' => 'Index',
                                        'action' => 'replace',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    
    'navigation' => [
        'AdminModule' => [
            [
                'label' => 'Metadata Editor',
                'route' => 'admin/metadataeditor',
                'resource' => 'MetadataEditor\Controller\Index',
                'pages' => [
                    [
                        'label' => 'Find', // @translate
                        'route' => 'admin/metadataeditor',
                        'resource' => 'MetadataEditor\Controller\Index',
                    ],
                    [
                        'label' => 'Preview', // @translate
                        'route' => 'admin/metadataeditor/preview',
                        'resource' => 'MetadataEditor\Controller\Index',
                        'visible' => false,
                    ],
                                        [
                        'label' => 'Replace', // @translate
                        'route' => 'admin/metadataeditor/replace',
                        'resource' => 'MetadataEditor\Controller\Index',
                        'visible' => false,
                    ],
                ],
            ],
        ],
    ],
];
