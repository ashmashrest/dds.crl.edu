<?php

namespace Dds;

use Dds\Model\Title;
use Dds\Model\TitleTable;
use Dds\Model\Item;
use Dds\Model\ItemTable;
use Dds\Model\Collection;
use Dds\Model\CollectionTable;
use Dds\Model\Folder;
use Dds\Model\FolderTable;
use Dds\Model\MemberIP;
use Dds\Model\MemberIPTable;
use Dds\Model\Log;
use Dds\Model\LogTable; // View Log
use Dds\Model\DLog;
use Dds\Model\DLogTable; // Download Log
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Stdlib\Hydrator\ArraySerializable;
use Zend\Db\TableGateway\TableGateway;
use Zend\Authentication\Storage;
use Zend\Authentication\AuthenticationService;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Db\Adapter\Adapter;
use Zend\Session\Config\SessionConfig;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use ApplicationServiceErrorHandling as ErrorHandlingService;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream as LogWriterStream;

class Module {

    public function onBootstrap(MvcEvent $e) {
        $eventManager = $e->getApplication()->getEventManager();
        $eventManager->attach('route', array($this, 'loadConfiguration'), 2); //This is attached to load the Controller plugins
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $this->bootstrapSession($e);
    }

    /**
     * Start the User session based on the IP table
     * @param type $e
     */
    public function bootstrapSession($e) {

        $config = $e->getApplication()
                ->getServiceManager()
                ->get('Configuration');
        $sessionConfig = new SessionConfig();
        $sessionConfig->setOptions($config['session']);
        $sessionManager = new SessionManager($sessionConfig, null, null);

        $sessionManager->start();

        $container = new Container('user');
        $viewModel = $e->getApplication()->getMvcEvent()->getViewModel();

        if (!isset($container->user)) {
            $e->getApplication()
                    ->getServiceManager()
                    ->get('Dds\Model\MemberIPTable')
                    ->getMemberInfo();
        }

        /* This is for the Shibboleth Authtnetication which needs to be fixed. */
        if ($container->user['user']['role'] == 'nonmember') {
            $user_session = $e->getApplication()
                    ->getServiceManager()
                    ->get('AuthService')
                    ->getIdentity($container);

            if (isset($container->user['user'])) {
                $e->getApplication()
                        ->getServiceManager()
                        ->get('Dds\Model\MemberIPTable')
                        ->getMemberInfo($user_session['user']);
            }
        }
        $viewModel->user = $container->user;
    }

    public function loadConfiguration(MvcEvent $e) {
        $application = $e->getApplication();

        $sm = $application->getServiceManager();
        $sharedManager = $application->getEventManager()->getSharedManager();

        $router = $sm->get('router');
        $request = $sm->get('request');

        $matchedRoute = $router->match($request);
        if (null !== $matchedRoute) {
            $sharedManager->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', function($e) use ($sm) {
                $sm->get('ControllerPluginManager')->get('AssetData')
                        ->doAuthorization($e); //pass to the plugin...    
            }, 2
            );
        }
        
        /* Selects the Exception template */
        $exceptionstrategy = $sm->get('ViewManager')->getExceptionStrategy();
        $exceptionstrategy->setExceptionTemplate('error/index');
    }

    /**
     * Load Module Config file
     * @return type
     */
    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Autoloaders
     * @return type array
     */
    public function getAutoloaderConfig() {
        return array(
            /* 'Zend\Loader\ClassMapAutoloader' => array(
              __DIR__ . '/autoload_classmap.php',
              ), */
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    /**
     * Service Configurations
     * @return type array 
     */
    public function getServiceConfig() {
        return array(
            'factories' => array(
                'Dds\Model\TitleTable' => function($sm) {
            $tableGatewayTitle = $sm->get('TitleTableGateway');
            $table = new TitleTable($tableGatewayTitle);
            $table->setRepository($sm->get('repository')); // Setting the share folder location
            return $table;
        },
                'repository' => function($sm) { // get share folder location from configuration
            $config = $sm->get('Config');
            return $config['repository'];
        },
                'Dds\Model\ItemTable' => function($sm) {
            $tableGatewayTitle = $sm->get('TitleTableGateway');
            $tableGatewayItem = $sm->get('ItemTableGateway');
            $table = new ItemTable($tableGatewayItem);
            $table->setTitle($sm->get('Dds\Model\TitleTable'));
            return $table;
        },
                'TitleTableGateway' => function ($sm) {
            $dbAdapter = $sm->get('adapter');
            $resultSetPrototype = new HydratingResultSet();
            $resultSetPrototype->setHydrator(new ArraySerializable());
            $resultSetPrototype->setObjectPrototype(new Title());
            return new TableGateway('title', $dbAdapter, null, $resultSetPrototype);
        },
                'ItemTableGateway' => function ($sm) {
            $dbAdapter = $sm->get('adapter');
            $resultSetPrototype = new HydratingResultSet();
            $resultSetPrototype->setHydrator(new ArraySerializable());
            $resultSetPrototype->setObjectPrototype(new Item());
            return new TableGateway('item', $dbAdapter, null, $resultSetPrototype);
        },
                'Dds\Model\CollectionTable' => function($sm) {
            $tableGateway = $sm->get('CollectionTableGateway');
            $table = new CollectionTable($tableGateway);
            return $table;
        },
                'CollectionTableGateway' => function ($sm) {
            $dbAdapter = $sm->get('adapter');
            $resultSetPrototype = new HydratingResultSet();
            $resultSetPrototype->setHydrator(new ArraySerializable());
            $resultSetPrototype->setObjectPrototype(new Collection());
            return new TableGateway('collection', $dbAdapter, null, $resultSetPrototype);
        },
                'Dds\Model\FolderTable' => function($sm) {
            $tableGateway = $sm->get('FolderTableGateway');
            $table = new FolderTable($tableGateway);
            return $table;
        },
                'FolderTableGateway' => function ($sm) {
            $dbAdapter = $sm->get('adapter');
            $resultSetPrototype = new HydratingResultSet();
            $resultSetPrototype->setHydrator(new ArraySerializable());
            $resultSetPrototype->setObjectPrototype(new Folder());
            return new TableGateway('folders', $dbAdapter, null, $resultSetPrototype);
        },
                'Dds\Model\MemberIPTable' => function ($sm) {
            $dbAdapter = $sm->get('adapter2');
            $table = new MemberIPTable($dbAdapter);
            return $table;
        },
                'MemberIPTableGateway' => function ($sm) {
            $dbAdapter = $sm->get('adapter2');
            return new TableGateway('MemberIP', $dbAdapter, null, null);
        },
                'Dds\Model\LogTable' => function ($sm) { // View log
            $dbAdapter = $sm->get('LogTableGateway');
            $table = new LogTable($dbAdapter);
            return $table;
        },
                'LogTableGateway' => function ($sm) { //View log
            $dbAdapter = $sm->get('adapter');
            $resultSetPrototype = new HydratingResultSet();
            $resultSetPrototype->setHydrator(new ArraySerializable());
            $resultSetPrototype->setObjectPrototype(new Log());
            return new TableGateway('LogV', $dbAdapter, null, $resultSetPrototype);
        },
                'Dds\Model\DLogTable' => function ($sm) { //Download log
            $dbAdapter = $sm->get('DLogTableGateway');
            $table = new DLogTable($dbAdapter);
            return $table;
        },
                'DLogTableGateway' => function ($sm) {  // Download log
            $dbAdapter = $sm->get('adapter');
            $resultSetPrototype = new HydratingResultSet();
            $resultSetPrototype->setHydrator(new ArraySerializable());
            $resultSetPrototype->setObjectPrototype(new Log());
            return new TableGateway('LogD', $dbAdapter, null, $resultSetPrototype);
        },
                'AuthService' => function($sm) {
            $adapter = new \Dds\Authentication\Adapter\AuthAdapter(array(
                'id_attr_name' => 'REMOTE_ADDR',
                'user_attr_names' => array(
                    'cn',
                    'mail',
                    'Meta-displayName',
                    'Shib-Identity-Provider'
                ),
                'system_attr_names' => array(
                    'Shib-Application-ID',
                    'Shib-Identity-Provider',
                    'Shib-Authentication-Instant',
                    'Shib-Authentication-Method',
                    'Shib-AuthnContext-Class',
                    'Shib-Session-Index',
                    'REMOTE_ADDR'
                ),
            ));

            $result = $adapter->authenticate();
            return $result;
        },
                'ApplicationServiceErrorHandling' => function($sm) {
            $logger = $sm->get('ZendLog');
            $service = new ErrorHandlingService($logger);
            return $service;
        },
                'ZendLog' => function ($sm) { //This is not used yet
            $filename = 'log_' . date('F') . '.txt';
            $log = new Logger();
            $writer = new LogWriterStream('./data/log/' . $filename);
            $log->addWriter($writer);
            return $log;
        },
            ),
        );
    }

    public function getControllerConfig() {
        return array(
            'factories' => array(
                'index' => function($controllers) {
            $sm = $controllers->getServiceLocator();
            $controller = new Controller\IndexController();
            $controller->setLog($sm->get('ZendLog'));
            return $controller;
        },
            ),
        );
    }

}
