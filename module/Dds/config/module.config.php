<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'Dds\Controller\Dds' => 'Dds\Controller\DdsController',
            'Dds\Controller\Collection' => 'Dds\Controller\CollectionController',
            'Dds\Controller\Title' => 'Dds\Controller\TitleController',
            'Dds\Controller\Item' => 'Dds\Controller\ItemController',
            'Dds\Controller\Scan' => 'Dds\Controller\ScanController',
            'Dds\Controller\MemberIP' => 'Dds\Controller\MemberIPController',
            'Dds\Controller\Auth' => 'Dds\Controller\AuthController',
        ),
    ),
    'controller_plugins' => array(
        'invokables' => array(
            'AssetData' => 'Dds\Controller\Plugin\AssetData',
        ), /* Invokable controller plugins that can be used within any controller */
    ),
    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'dds-pages' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Dds\Controller\Dds',
                        'action' => 'rights',
                    ),
                ),
            ),
            'dds-collection' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/collection[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Dds\Controller\Collection',
                        'action' => 'index',
                    ),
                ),
            ),
            'dds-title' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/crldelivery[/:action][/:id][/:year]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                        'year' => '[1-2][0-9][0-9][0-9]',
                    ),
                    'defaults' => array(
                        'controller' => 'Dds\Controller\Title',
                        'action' => 'index',
                    ),
                ),
            ),
            'dds-programs' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/program[/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Dds\Controller\Title',
                        'action' => 'program',
                    ),
                ),
            ),
            'dds-item' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/item[/:action][/:id][/:f]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                        'f' => '[a-zA-Z0-9_-]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Dds\Controller\Item',
                        'action' => 'index',
                        'f' => null,
                    ),
                ),
            ),
             'dds-scan' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/page[/:action][/:id][/:f]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                        'f' => '[a-zA-Z0-9_-]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Dds\Controller\Scan',
                        'action' => 'index',
                        'f' => null,
                    ),
                ),
            ),
            'ipcheck' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/ipcheck[/:ip][/:format]',
                    'constraints' => array(
                        'format' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'ip' => '[0-9.]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Dds\Controller\MemberIP',
                        'action' => 'index',
                        'format' => 'default',
                    ),
                ),
            ),
              'dds-member' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/member[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Dds\Controller\MemberIP',
                        'action' => 'index',
                    ),
                ),
            ),
            'logout' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/logout',
                    'defaults' => array(
                        'controller' => 'Dds\Controller\Auth',
                        'action' => 'logout',
                    ),
                ),
            ),
            'login' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/login',
                    'defaults' => array(
                        'controller' => 'Dds\Controller\Auth',
                        'action' => 'login',
                    ),
                ),
            ),
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/',
                    'defaults' => array(
                        'controller' => 'Dds\Controller\Dds',
                        'action' => 'index',
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/403' => __DIR__ . '/../view/error/403.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml',
            'partial/paginator' => __DIR__ . '/../view/partial/paginator.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy'
        )
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo',
            ),
        ),
    ),
    'session' => array(
        'remember_me_seconds' => 2419200,
        'use_cookies' => true,
        'cookie_httponly' => true,
    ),   
);
