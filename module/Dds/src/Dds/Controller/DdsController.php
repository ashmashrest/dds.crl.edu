<?php

namespace DDS\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class DDSController extends AbstractActionController
{
    public function rightsStatementAction() {
        
        return new ViewModel();
    }
    
    public function contactUsAction() {
        
        return new ViewModel();
    }
    /**
     * 
     * @return type
     */
    public function getTitleTable() {
        if (!$this->titleTable) {
            $sm = $this->getServiceLocator();
            $this->titleTable = $sm->get('Dds\Model\TitleTable');
        }
        return $this->titleTable;
    }

    /**
     * 
     * @return type
     */
    public function getItemTable() {
        if (!$this->itemTable) {
            $sm = $this->getServiceLocator();
            $this->itemTable = $sm->get('Dds\Model\ItemTable');
        }
        return $this->itemTable;
    }

    /**
     * 
     * @return type
     */
    public function getCollectionTable() {
        if (!$this->collectionTable) {
            $sm = $this->getServiceLocator();
            $this->collectionTable = $sm->get('Dds\Model\CollectionTable');
        }
        return $this->collectionTable;
    }
    
}
?>
