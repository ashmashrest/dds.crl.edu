<?php

namespace DDS\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\FeedModel;
use Zend\View\Model\ViewModel;
use SimpleXMLElement;

//use Zend\Session\Container; // We need this when using sessions

class MemberIPController extends AbstractRestfulController {

    protected $memberIPTable;

    public function indexAction() {
        $this->ipaddress = $this->params()->fromRoute('ip');

        $this->format = $this->params()->fromRoute('format');

        $data = $this->getMemberIPTable()->ipCheck($this->ipaddress);
        return $this->formatData(array('data' => $data));
    }
/**
 * Used for the Shibboleth Discovery Feed
 * 
 * @return \Zend\View\Model\JsonModel
 */
    public function idpAction() {
        
        $responseArray = array();
        
        $data = $this->getMemberIPTable()->getIdps();
        
        while ($idp = $data->current()) {
            //Prepare the json Schema
            $responseArray[] = array('entityID' => $idp['entityID'],
                'DisplayNames' => array(array(
                        'value' => $idp['DisplayNames'],
                        'lang' => 'en'
                    )));
        
            $data->next();
        }

        return new JsonModel($responseArray);
    }

    public function formatData($data) {

        switch ($this->format) {
            case 'json':
                return new JsonModel($data);
                break;
            case 'xml':
                /* Need implementation */
                break;
            default:
                $view = new ViewModel($data);
                $view->setTerminal(true);
                return $view;
                break;
        }
    }

    public function getMemberIPTable() {
        
        if (!$this->memberIPTable) {
            $sm = $this->getServiceLocator();
            $this->memberIPTable = $sm->get('Dds\Model\MemberIPTable');
        }
        return $this->memberIPTable;
    }

}

?>
