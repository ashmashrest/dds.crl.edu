<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return array(    
    'site' => array(
        'name' => 'Digital Delivery System',
        'admin-email' =>  'ashrestha@crl.edu',
        'access-email' => 'ads@crl.edu',        
    ),
    
  
    'db'=> array( 
    'adapters'=>array(
        'adapter' => array(
            'platform'       => 'SqlServer',
            'driver'         => 'Pdo',
            'dsn'            => 'dblib:host=CRLMsSqlServer;dbname=DDSregistry;charset=utf8;',
            'pdotype'       => 'dblib',
            'charset' => 'UTF-8',
            "driver_options" => array(
                "charset" => "UTF-8",
                ),
            ),
        'adapter2' => array(
            'platform'       => 'SqlServer',
            'driver'         => 'Pdo',
            'dsn'            => 'dblib:host=CRLMsSqlServer;dbname=members;charset=utf8;',
            'pdotype'       => 'dblib',
            'charset' => 'UTF-8',
             ),
        ),
        
    ),
    'service_manager' => array(
           'abstract_factories' => array(
                   'Zend\Db\Adapter\AdapterAbstractServiceFactory',
           )
    ),
    
);
