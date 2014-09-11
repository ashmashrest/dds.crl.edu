<?php

namespace DDS\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Dds\Form\DownloadForm;
use Zend\View\Model\JsonModel;
use Zend\Session\Container; // We need this for sessions

class ItemController extends AbstractActionController {

    protected $id;
    protected $itemTable;
    protected $titleTable;
    protected $item;
    protected $logger;

    public function indexAction() {

        // Get the item id
        $this->id = $this->params()->fromRoute('id');

        $this->f = $this->params()->fromRoute('f');

        if (!$this->id) {
            $this->getResponse()->setStatusCode(404);
        }

        //Get the item object
        $this->item = $this->getItemTable()->getItem($this->id, $this->f);
        
        //Get the previous and next item 
        $item_previous = $this->itemTable->getPrev();

        //Get next item
        $item_next = $this->itemTable->getNext();

        //Get parts of the item is multipart PDF
        $item_pages = $this->getPages();

        //For Item pagination        
        $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($item_pages));

        $paginator->setItemCountPerPage(1);
        $paginator->setCurrentPageNumber($this->params()->fromRoute('f'));


        //Get user session information
        $user_session = new Container('user');

        //Check if the user has access else redirect them to 403 page
        // TODO: Need a better access control system
        $this->asset = $this->plugin('AssetData');
        if ( !$this->asset->authorize($this->item) ) {         

            $view = new ViewModel();
            $view->setTemplate('error/403');
            return $view;
        }
        // 
       $view = new ViewModel(array(
            'item' => $this->item,
            'user' => $user_session->user,
            'item_previous' => $item_previous,
            'item_next' => $item_next,
            /* 'item_parts' => $item_parts, */
            'params' => $this->params(),
            'paginator' => $paginator,
        ));
        
        if ($this->getItemTable()->getRights($this->item->titlelink, 'CopyRt')) {

            $tids = (array) $tids = (array) $user_session->offsetGet('tids');

            if (!in_array($this->item->titlelink, $tids)) {
                return $this->redirect()->toRoute('dds-title', array('action' => 'index', 'id' => $this->item->titlelink));
            }
        }
        $this->getLogTable()->saveLog($user_session->user['user'], $this->itemTable);

        // If not per page send to download
        if (!$this->item->perpage) {
            return $this->redirect()->toRoute('dds-scan', array('action' => 'download', 'id' => $this->id, 'f' => $this->f));
        }


        return $view;
    }
    
    /**
     * Action used to load the calendar items
     * @return \Zend\View\Model\JsonModel
     */
    public function jsonAction() {
        // Get request data
        $request = $this->getRequest()->getPost();

        $this->getItemTable()->setOptions($request);
        $resultSet = $this->itemTable->getItems($request['TitleLink'])->toArray();

        $items = array();

        foreach ($resultSet as $item) {
            //pubdate // iid
            $d = new \DateTime($item['pubdate']);
            $urlPlugin = $this->plugin('url');
            $url = $urlPlugin->fromRoute('dds-item', array('action' => 'index', 'id' => $item['iid']));
            //This is to catch multiple items in same day
            $date[$d->format('n/j/Y')][] = array('iid' => $item['iid'], 'text' => sprintf("<a href='%s'>%s</a>", $url, $item['item_text']));

            $items = array_merge($items, $date);
            unset($d);
        };


        $json = new JsonModel($items);
        return $json;
    }
    
    /**
     * 
     * @return type ItemTable
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
     * @return type TitleTable
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
     * @return type LogTable
     */
    public function getLogTable() {
        if (!$this->logger) {
            $sm = $this->getServiceLocator();
            $this->logger = $sm->get('Dds\Model\LogTable');
        }
        return $this->logger;
    }

    /**
     * 
     * @return type ZendLog
     */
    protected function _getLog() {
        if ($this->_log == NULL) {
            $this->_log = $this->getServiceLocator()->get('ZendLog');
        }

        return $this->_log;
    }

    /**
     * 
     * @return type
     */
    protected function _log($message, $priority = \Zend\Log\Logger::DEBUG, $extra = array()) {
        return $this->_getLog()->log($priority, $message, $extra);
    }
    
    /**
     * Get pages of the item 
     * @return type array item_pages
     */
   
    private function getPages() {

        $item_pages = array();


        if (is_dir($this->itemTable->getFolderLocation() )) {

            $this->asset = $this->plugin('AssetData');
            $this->asset->setFolder($this->itemTable->getFolderLocation());
            $item_pages = $this->asset->getFiles();
        }


        return $item_pages;
    }

}

?>
