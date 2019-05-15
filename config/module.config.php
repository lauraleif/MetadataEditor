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
                            'fields' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/fields',
                                    'defaults' => [
                                        '__NAMESPACE__' => 'MetadataEditor\Controller',
                                        'controller' => 'Index',
                                        'action' => 'fields',
                                    ],
                                ],
                            ],
                            'changes' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/changes',
                                    'defaults' => [
                                        '__NAMESPACE__' => 'MetadataEditor\Controller',
                                        'controller' => 'Index',
                                        'action' => 'changes',
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
                            'backup' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/backup',
                                    'defaults' => [
                                        '__NAMESPACE__' => 'MetadataEditor\Controller',
                                        'controller' => 'Index',
                                        'action' => 'backup',
                                    ],
                                ],
                            ],
                            'download' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/download',
                                    'defaults' => [
                                        '__NAMESPACE__' => 'MetadataEditor\Controller',
                                        'controller' => 'Index',
                                        'action' => 'download',
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
            ],
        ],
    ],
];
