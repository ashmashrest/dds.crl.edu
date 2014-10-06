<?php

namespace Dds\Controller;

use Zend\Mvc\Controller\AbstractActionController;
//use DDS\Controller\DDSController;
use Zend\View\Model\ViewModel;
use Dds\Form\TitleForm;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Session\Container; // We need this when using sessions

/**
 * Title Controller
 */

class TitleController extends AbstractActionController {

    protected $titleTable;
    protected $itemTable;
    protected $collectionTable;
    protected $folderTable;
    protected $tid;
    protected $rights;
    protected $session;
    protected $views = array(
        "0" => "default",
        "1" => "calendar",
        "2" => "twotier",
        "3" => "threetier"
    );

    /**
     * Default action of the Title class
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction() {

        $this->tid = $this->params()->fromRoute('id');
        /* Need for the return year */
        $this->year = $this->params()->fromRoute('year');

        if (!$this->tid) {
            $this->getResponse()->setStatusCode(404);
        }

        //Get the title information
        $title = $this->getTitleTable()->getTitle($this->tid);
      

        //Get the Item Information 
        $items = $this->getItemTable()->getItems($this->tid);
        $years = $this->itemTable->getYears($this->tid);

        // Get Collection information 
        $collection = $this->getCollectionTable()->getCollection($title->collectionlink);

        //Get the User
        //Need implementation 
        $user_session = new Container('user');
        $user = $user_session->offsetGet('user');


        //Get the access information for the title
        $this->rights->memberonly = ($this->itemTable->getRights($this->tid, 'MemOnly')) ? $this->itemTable->getRights($this->tid, 'MemOnly')->memberonly : false;
        $this->rights->copyright = ($this->itemTable->getRights($this->tid, 'CopyRt')) ?  ' Subject to copyright restrictions  ' : false;

        //Get the view type of the Title
        $viewType = $this->views[(int) $title->viewtype];


        $view = new ViewModel(array('title' => $title,
        'collection' => $collection,
        'user' => $user,
        'rights' => $this->rights,
        'folders' => $this->getFolderTable()->getFoldersArray()));


        if ($this->getItemTable()->getRights($this->tid, 'CopyRt')) {

            $tids = (array) $tids = (array) $user_session->offsetGet('tids');

            if (!in_array($this->tid, $tids)) {
                $form = new TitleForm();
                $form->get('tid')->setValue($this->tid);
                $agreementView = new ViewModel(array('form' => $form, 'tid' => $this->tid));
                $agreementView->setTemplate('dds/title/agreement');
                $view->addChild($agreementView, 'items');
                return $view;
            }
        }

        $itemsView = new ViewModel(array('title' => $title,
            'items' => $items,
            'years' => $years,
            'user' => $user_session->user));
        $itemsView->setTemplate('dds/title/views/' . $viewType);

        // Check if there are any items in the title which are Copyright

        $view->addChild($itemsView, 'items');

        return $view;
    }

    /**
     * Detailed information about the Title linked to the Collection info
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function detailAction() {
        $this->tid = $this->params()->fromRoute('id');
        //Get the title information
        $title = $this->getTitleTable()->getTitle($this->tid);

        $collection = $this->getCollectionTable()->getCollection($title->collectionlink);

        return new ViewModel(array(
            'title' => $title,
            'collection' => $collection,
        ));
    }

    public function programAction() {
        $titles = $this->getTitleTable()->fetchAll(array(
            'filter' => array('field' => "LocationCode ",
                'value' =>  $this->params()->fromRoute('id') . "%"),
            'op' => 'like')
        );
        $titles->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        // set the number of items per page to 10
        $titles->setItemCountPerPage(20);

        return new ViewModel(array(
            'titles' => $titles,
            'program' => $this->params()->fromRoute('id'),
            'folders' => $this->getFolderTable()->getFoldersArray()
        ));
    }
    
    public function collectionAction() {
        $titles = $this->getTitleTable()->fetchAll(array(
            'filter' => array('field' => "Folder ",
                'value' =>  $this->params()->fromRoute('id') ))
        );
        $titles->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        // set the number of items per page to 10
        $titles->setItemCountPerPage(20);

        return new ViewModel(array(
            'titles' => $titles,
            'collection' => $this->params()->fromRoute('id')
        ));
    }

    /* Note used */

    public function agreementAction() {
        $this->tid = (int) $this->params()->fromRoute('id');



        $form = new TitleForm();
        $form->get('tid')->setValue($this->tid);

        $request = $this->getRequest();
        if ($request->isPost()) {

            $form->setData($request->getPost());



            if ($form->isValid()) {
                // Need to add this to the session data
                $user_session = new Container('user');
                $tids = (array) $user_session->offsetGet('tids');
                if (!in_array($this->tid, $tids))
                    array_push($tids, $this->tid);
                $user_session->offsetSet('tids', $tids);

                // Redirect to Title list
                return $this->redirect()->toRoute('dds-title', array('action' => 'index', 'id' => $this->tid));
            } else {
                $form->getMessages();
            }
        }
        return array('form' => $form, 'tid' => $this->tid);
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

    /**
     * 
     * @return type
     */
    public function getFolderTable() {
        if (!$this->folderTable) {
            $sm = $this->getServiceLocator();
            $this->folderTable = $sm->get('Dds\Model\folderTable');
        }
        return $this->folderTable;
    }
    

}

?>
