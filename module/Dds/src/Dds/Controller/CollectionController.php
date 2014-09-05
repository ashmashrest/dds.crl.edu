<?php

namespace DDS\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
/**
 * We might be able to delete this 
 */
class CollectionController extends AbstractActionController
{
    protected $collectionTable;
    protected $titleTable;
    protected $cid;
    
   
    public function indexAction()
    {
        $this->cid = $this->params()->fromRoute('id');
               
        $collection = $this->getCollectionTable()->getCollection($this->cid);
        
        $collection->titles = $this->getTitleTable()->fetchAll(array(
            'filter' => array('field' => "collectionlink ",
                'value' => $this->cid))
        );
        $collection->titles->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        // set the number of items per page to 10
        $collection->titles->setItemCountPerPage(20);         
  
        return new ViewModel(array(
                                    'collection' => $collection,
                                     'cid' => $this->cid,));
    }
      /**
   * 
   * @return type
   */  
  public function getCollectionTable()
  {
      if (!$this->collectionTable) {
          $sm = $this->getServiceLocator();
          $this->collectionTable = $sm->get('Dds\Model\CollectionTable');
      }
      return $this->collectionTable;
  }
   /**
   * 
   * @return type
   */
  public function getTitleTable()
  {
      if (!$this->titleTable) {
          $sm = $this->getServiceLocator();
          $this->titleTable = $sm->get('Dds\Model\TitleTable');
      }
      return $this->titleTable;
  }
}

?>
