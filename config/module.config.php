<?php
return array(
    'di' => array(
        'definition' => array(
            'ModuleManager\ModuleObj' => 'ModuleManager\ModuleObj',
        ),
        'instance' => array(
       //     'ModuleManager\Controller\IndexController' => array('parameters' => array(
       //         'moduleForm'      => 'ModuleManager\Form\ModuleForm',
       //     )),
            'Zend\View\Resolver\TemplateMapResolver' => array('parameters' => array(
                'map'  => array(
                    'index/index'     => __DIR__ . '/../view/index/index.phtml',
                ),
            )),
            'Zend\View\Resolver\TemplatePathStack' => array('parameters' => array(
                'paths'  => array(
                    'module-manager' => __DIR__ . '/../view',
                ),
            )),
            'Zend\Mvc\Router\RouteStack' => array('parameters' => array(
                'routes' => array(
                    'mm' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/mm',
                            'defaults' => array(
                                'controller' => 'ModuleManager\Controller\IndexController',
                                'action'     => 'index',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'deactivate' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/deactivate/:module',
                                    'defaults' => array(
                                        'action'     => 'deactivate',
                                    ),
                                ),
                            ),
                            'activate' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/activate/:module',
                                    'defaults' => array(
                                        'action'     => 'activate',
                                    ),
                                ),
                            ),
                            'install' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/install/:module',
                                    'defaults' => array(
                                        'action'     => 'install',
                                    ),
                                ),
                            ),
                            'uninstall' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/uninstall/:module',
                                    'defaults' => array(
                                        'action'     => 'uninstall',
                                    ),
                                ),
                            ),
                            'remove' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/remove/:module',
                                    'defaults' => array(
                                        'action'     => 'remove',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            )),
        ),
    )
);
