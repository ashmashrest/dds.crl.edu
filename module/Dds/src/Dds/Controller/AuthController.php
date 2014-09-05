<?php 
namespace Dds\Controller;
 
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
 
use Dds\Model\User;
 
class AuthController extends AbstractActionController
{
    protected $form;
    protected $storage;
    protected $authservice;
     
    public function getAuthService()
    {
        if (! $this->authservice) {
            $this->authservice = $this->getServiceLocator()
                                      ->get('AuthService');
        }
         
        return $this->authservice;
    }
     
    public function getSessionStorage()
    {
        if (! $this->storage) {
            $session_user = new Container('user');
            $this->storage = $session_user->getManager()->getStorage();
                              
        }
         
        return $this->storage;
    }
     
    public function getForm()
    {
        if (! $this->form) {
            $user       = new User();
            $builder    = new AnnotationBuilder();
            $this->form = $builder->createForm($user);
        }
         
        return $this->form;
    }
    /**
     * Shibboleth Discory Service form
     * @return \Zend\View\Model\ViewModel
     */ 
    public function loginAction()
    {
        $logged = false;
        $user_session = new Container('user');
    
        if($user_session->user['user']['role'] == "member") {
           
                $logged = true;
        }
     
        return new ViewModel(array('logged'=> $logged));
    }
     
    public function authenticateAction()
    {
        /*$form       = $this->getForm();
        $redirect = 'login';
         
        $request = $this->getRequest();
        if ($request->isPost()){
            $form->setData($request->getPost());
            if ($form->isValid()){
                //check authentication...
                $this->getAuthService()->getAdapter()
                                       ->setIdentity($request->getPost('username'))
                                       ->setCredential($request->getPost('password'));
                                        
                $result = $this->getAuthService()->authenticate();
                foreach($result->getMessages() as $message)
                {
                    //save message temporary into flashmessenger
                    $this->flashmessenger()->addMessage($message);
                }
                 
                if ($result->isValid()) {
                    $redirect = 'success';
                    //check if it has rememberMe :
                    if ($request->getPost('rememberme') == 1 ) {
                        $this->getSessionStorage()
                             ->setRememberMe(1);
                        //set storage again 
                        $this->getAuthService()->setStorage($this->getSessionStorage());
                    }
                    $this->getAuthService()->getStorage()->write($request->getPost('username'));
                }
            }
        }
         
        return $this->redirect()->toRoute($redirect);*/
    }
     
    public function logoutAction()
    {
        $this->getSessionStorage()->clear();
        
        $logoutUrl = 'Shibboleth.sso/Logout?return='. $this->url()->fromRoute('home');
        
        return $this->redirect()->toUrl($logoutUrl);
    }
}