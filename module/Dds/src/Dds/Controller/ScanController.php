<?php

namespace DDS\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container; // We need this for sessions
use Dds\Form\DownloadForm;
use Dds\Model\Download;
use ZendPdf\PdfDocument;

class ScanController extends AbstractActionController {

    public $item;
    public $title;
    public $itemTable;
    public $titleTable;
    public $asset;
    public $rights;
    public $logger;
    private $tempFolder; // Need to rethink this

    public function indexAction() {

        $this->id = $this->params()->fromRoute('id');
        $this->f = (int) $this->params()->fromRoute('f');


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
        if (($this->item->title->locationcode == NULL && $user_session->user['user']['role'] != 'member') || $this->item->title->locationcode != NULL && $user_session->user['user']['role'] == 'nonmember' || ($this->item->title->locationcode != NULL && ($user_session->user['user']['role'] == 'amp' && !in_array($this->item->title->locationcode, $user_session->user['user']['amp']) ) )) {
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

        $view->setTerminal(true);
        //$this->_log(array('type' => 'item', 'id' => $this->id, 'user' => $user_session->user), 6  );//Sample log call
        return $view;
    }

    public function viewAction() {


        $this->id = $this->params()->fromRoute('id');
        $this->f = (int) $this->params()->fromRoute('f');


        if (!$this->id) {
            $this->getResponse()->setStatusCode(404);
        }

        //Get the item object
        $this->item = $this->getItemTable()->getItem($this->id, $this->f);
        //Get the title information
        $this->title = $this->getTitleTable()->getTitle($this->item->titlelink);

        //Get user session information
        $user_session = new Container('user');

        if (($this->item->title->locationcode == NULL && $user_session->user['user']['role'] != 'member') || $this->item->title->locationcode != NULL && $user_session->user['user']['role'] == 'nonmember' || ($this->item->title->locationcode != NULL && ($user_session->user['user']['role'] == 'amp' && !in_array($this->item->title->locationcode, $user_session->user['user']['amp']) ) )) {

            $view = new ViewModel();
            $view->setTemplate('error/403');
            return $view;
        }

        //Set the uri of the file location
        $this->uri = $this->getFileLocation();
        try {
            if (file_exists($this->uri)) {
                $response = new \Zend\Http\Response\Stream();
                $headers = new \Zend\Http\Headers();
                $headers->addHeaderLine('Content-type', 'application/pdf');
                $response->setHeaders($headers);
                $response->setStream(fopen($this->uri, 'r'));
                return $response;
            } else {
                //To Do need a better exception
                throw new \Exception('File not exist');
            }
        } catch (\Exception $e) {
            $this->flashMessenger()->setNamespace('error')->addMessage('404');
            throw new \Exception('There was a problem getting the item. Please report the issue to websystems@crl.edu');
        }
    }

    public function downloadAction() {

        $this->asset = $this->plugin('AssetData');

        $this->id = (int) $this->params()->fromRoute('id');
        $this->f = $this->params()->fromRoute('f');


        //Get the item object
        $this->item = $this->getItemTable()->getItem($this->id, $this->f);

        //Get the title information
        $this->title = $this->getTitleTable()->getTitle($this->item->titlelink); //check if required
        //Get user session information
        $user_session = new Container('user');

        if (($this->item->title->locationcode == NULL && $user_session->user['user']['role'] != 'member') || $this->item->title->locationcode != NULL && $user_session->user['user']['role'] == 'nonmember' || ($this->item->title->locationcode != NULL && ($user_session->user['user']['role'] == 'amp' && !in_array($this->item->title->locationcode, $user_session->user['user']['amp']) ) )) {
            $view = new ViewModel();
            $view->setTemplate('error/403');
            return $view;
        }
        //Inititate Form
        $form = new DownloadForm();
        $form->get('id')->setValue($this->id);

        //Set the uri of the file location
        $location = $this->itemTable->getFolderLocation();


        $request = $this->getRequest();
        if ($request->isPost()) {

            $download = new Download();
            $form->setInputFilter($download->getInputFilter());

            $from = $request->getPost('scan-from');
            $to = $request->getPost('scan-to');
            $form->setData($request->getPost());

            $dowload_name = 'dds-' . $this->item->iid . '-' . $from . '-' . $to . ".pdf";

            if ($form->isValid()) {


                $files = "";
                $tempfile = $this->getTempFolder() . $request->getPost('security') . ".pdf"; ///Need to modify this



                while ($to >= $from) {
                    $files .= $location . DIRECTORY_SEPARATOR . sprintf("%03d", $from) . ".pdf" . " ";
                    $from++;
                }

                system("pdftk $files cat output $tempfile", $errcode);

                $this->generateZFCoverpage($tempfile);

                if (!$errcode) {

                    $finalPDF = $this->asset->streamPDF($tempfile, 'attachment', $dowload_name);

                    // Check Log table for excessive user and send email 
                    $this->getLogTable()->checkLog($user_session->user['user']['ipaddress']);

                    //Remove temporary files

                    unlink($tempfile);

                    $this->getDownloadLogTable()->saveLog($user_session->user['user'], $this->itemTable);

                    return $finalPDF;
                }
            }
        }

        //  If the item is not per page stream the file
        if (!$this->item->perpage) {
            return $this->asset->streamPDF($this->itemTable->getFileLocation(), 'attachment', 'dds-' . $this->item->iid . ".pdf");
        }



        // If the Item is per page provide the selection form
        $view = new ViewModel(array(
            'form' => $form,
            'item' => $this->item,
            'params' => $this->params(),
        ));

        //$view->setTerminal(true);

        return $view;
    }

    public function downloadAllAction() {

        $this->asset = $this->plugin('AssetData');

        $this->id = (int) $this->params()->fromRoute('id');
        $this->f = $this->params()->fromRoute('f');


        //Get the item object
        $this->item = $this->getItemTable()->getItem($this->id, $this->f);

        //Get the title information
        $this->title = $this->getTitleTable()->getTitle($this->item->titlelink); //check if required
        //Get user session information
        $user_session = new Container('user');

        if (($this->item->title->locationcode == NULL && $user_session->user['user']['role'] != 'member') || $this->item->title->locationcode != NULL && $user_session->user['user']['role'] == 'nonmember' || ($this->item->title->locationcode != NULL && ($user_session->user['user']['role'] == 'amp' && !in_array($this->item->title->locationcode, $user_session->user['user']['amp']) ) )) {
            $view = new ViewModel();
            $view->setTemplate('error/403');
            return $view;
        }



        //Set the uri of the file location
        $location = $this->itemTable->getFolderLocation(); 
        $tempfile = $this->getTempFolder() . $this->id . ".pdf"; ///Need to modify this

        $files = $location . DIRECTORY_SEPARATOR . '*.pdf';
        system("pdftk $files cat output $tempfile", $errcode);


        $this->generateZFCoverpage($tempfile);

        $finalPDF = $this->asset->streamPDF($tempfile, 'attachment', $this->item->titlelink);

        unlink($tempfile);

        $this->getDownloadLogTable()->saveLog($user_session->user['user'], $this->itemTable);

        return $finalPDF;
    }

    /*
     * Get the location of the File
     */

    public function getFileLocation() {


        $location[] = $this->titleTable->getFolderLocation();

        if (is_dir($this->titleTable->getFolderLocation() . DIRECTORY_SEPARATOR . $this->item->filename)) {
            if (!$this->f)
                $this->f = 1;

            //Check if the location is a folder
            $location[] = $this->item->filename;

            if ((int) $this->item->pages > 999)
                $location[] = str_pad($this->f, 4, '0', STR_PAD_LEFT) . ".pdf";
            else
                $location[] = str_pad($this->f, 3, '0', STR_PAD_LEFT) . ".pdf";
        } else if ($this->f) {

            switch ($this->f) {
                case "toc":
                    $location[] = $this->item->filename . '-TOC.pdf';
                    break;
                default:

                    $location[] = $this->item->filename . '-f' . $this->f . ".pdf";
                    break;
            }
        } else {
            $location[] = $this->item->filename . ".pdf";
        }

        $uri = implode('/', $location);

        return $uri;
    }

    //Get pages of the item 
    private function getPages() {

        $item_pages = array();


        if (is_dir($this->item->titleObj->getFolderLocation() . "/" . $this->item->filename)) {

            $this->asset = $this->plugin('AssetData');
            $this->asset->setFolder($this->itemTable->getFolderLocation());
            $item_pages = $this->asset->getFiles();
        }


        return $item_pages;
    }

    //Generate Coverpage
    public function generateZFCoverpage($tempfile) {

        $now = new \DateTime();
        $request = $this->getRequest();

        //If the it is calendar view then updated identifier as the title prefix          
        $identifier = ($this->title->viewtype == 1 ) ? $this->title->fileprefix : $this->item->filename;

        $coverContent = new ViewModel(array(
            'identifier' => $identifier,
            'from' => $request->getPost('scan-from') ? $request->getPost('scan-from') : 0,
            'to' => $request->getPost('scan-to') ? $request->getPost('scan-to') : 0,
        ));
        $coverContent->setTemplate('dds/scan/coverpage_full');
        $coverContent->setTerminal(true);
        $htmlOutput = $this->getServiceLocator()
                ->get('viewrenderer')
                ->render($coverContent);


        $pdf = \ZendPdf\PdfDocument::load($tempfile);




        $page = $pdf->newPage(\ZendPdf\Page::SIZE_A4);
        $pdf->pages = array_reverse($pdf->pages);
        $pdf->pages[] = $page;
        $pdf->pages = array_reverse($pdf->pages);

        $page->setFont(\ZendPdf\Font::fontWithName(\ZendPdf\Font::FONT_HELVETICA), 12);

        // Load image 
        $image = \ZendPdf\Image::imageWithPath(getcwd() . '/public/img/logo.jpeg');
        // Draw image 
        $page->drawImage($image, 50, $page->getHeight() - 100, 365, $page->getHeight() - 50);
        //x1  y1   x2   y2

        $yPosition = $page->getHeight() - 200;
        foreach (explode(PHP_EOL, $htmlOutput) as $i => $line) {

            // $page->drawContentStream($line, 50, $yPosition, 'UTF-8');
            $page->drawText($line, 50, $yPosition);
            $yPosition -= 16;
        }

        $pdf->save($tempfile, true);
    }

    /**
     * Initialize Item table
     */
    public function getItemTable() {
        if (!$this->itemTable) {
            $sm = $this->getServiceLocator();
            $this->itemTable = $sm->get('Dds\Model\ItemTable');
        }
        return $this->itemTable;
    }

    /**
     * Initialize Title table
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
    public function getLogTable() {
        if (!$this->logger) {
            $sm = $this->getServiceLocator();
            $this->logger = $sm->get('Dds\Model\LogTable');
        }
        return $this->logger;
    }

    /**
     * 
     * @return type
     */
    public function getDownloadLogTable() {
        if (!$this->logger) {
            $sm = $this->getServiceLocator();
            $this->logger = $sm->get('Dds\Model\DLogTable');
        }
        return $this->logger;
    }

    /**
     * 
     * @return type
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
     * 
     * @return type
     */
    public function getTempFolder() {
        if (!$this->tempFolder) {
            $config = $this->getServiceLocator()->get('Config');
            $this->tempFolder = $config['TempFolder'];
        }
        return $this->tempFolder;
    }


}

?>
