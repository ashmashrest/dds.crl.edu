<?php

namespace Dds\Authentication\Adapter;

use ZfcShib\Authentication\Adapter\Shibboleth;
use Zend\Authentication\Result;


class AuthAdapter extends Shibboleth {
    
   // protected $role;
     /**
     * Constructor.
     * 
     * @param array $config Adapter options.
     * @param array $serverVars If the array is set, it will be used instead of the standard $_SERVER array.
     * @param IdentityFactoryInterface $identityFactory Optional identity factory.
     */
    public function __construct(array $config = array(), array $serverVars = null, IdentityFactoryInterface $identityFactory = null)
    {
        $this->setConfig($config);
        
        if (null === $serverVars) {
            $serverVars = $_SERVER;
        }
        
        $this->setServerVars($serverVars);
       
        if (null !== $identityFactory) {
            $this->setIdentityFactory($identityFactory);
        }
    }
    
     public function authenticate()
    {
        $ipAddress = $this->getUserId();
        if (null === $ipAddress) {          
            return $this->createFailureAuthenticationResult(Result::FAILURE_IDENTITY_NOT_FOUND, sprintf("User identity attribute '%s' not found", $this->getIdAttributeName()));
        }
        
       
        
        $userData = $this->extractAttributeValues($this->getUserAttributeNames(), $this->getServerVars()); 
        if( count($userData) > 0) {
           /* We are providing access to all the users who authenticate against shibboleth since we are restricting the list in the DS to member only */
            $userData ['role'] = "member"; 
         }
         $systemData = $this->extractAttributeValues($this->getSystemAttributeNames(), $this->getServerVars());
         return $this->createSuccessfulAuthenticationResult($userData, $systemData);
    }
    
   

}
?>
