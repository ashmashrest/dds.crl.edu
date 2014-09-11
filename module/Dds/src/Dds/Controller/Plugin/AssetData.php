<?php

/**
 * AssetData Helper for application module.
 */

namespace Dds\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin,
    Zend\Session\Container as SessionContainer,
    Zend\Permissions\Acl\Acl,
    Zend\Permissions\Acl\Role\GenericRole as Role,
    Zend\Permissions\Acl\Resource\GenericResource as Resource;
use ZendPdf\PdfDocument;
use ZendPdf\Resource\Extractor;

//use Dds\Model\Folder;

class AssetData extends AbstractPlugin {

    protected $asset;
    protected $sesscontainer;

    public function getFolder() {

        return $this->_folder;
    }

    public function setFolder($path) {
        $this->_folder = $path;
        return $this;
    }

    private function getSessContainer() {
        if (!$this->sesscontainer) {
            $this->sesscontainer = new SessionContainer('user');
        }

        return $this->sesscontainer;
    }

    public function getFiles() {

        $files = array();
        if ($handle = opendir($this->getFolder())) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != ".." && $entry != "Thumbs.db") {
                    $filename = ltrim(basename($entry, ".pdf"), 0);

                    $files[] = array('name' => $filename, 'f' => $entry);
                }
            }
            closedir($handle);
        }
        return $files;
    }

    /**
     * 
     * Check access permission for each item
     * @param type $item
     * @return boolean
     */
    public function Authorize($item) {

        $user_session = $this->getSessContainer();
        
        //authorize if the item is Public domain 
        if ($item->rights == 'PubDm')
            return true;
        // authorize if the user is from a member institute 
        if ($user_session->user['user']['role'] == 'member') {
            return true;
        }
        // authorize if the title is has location code and the non-member is AMP member having rights
        if (isset($item->title->locationcode) && in_array($item->title->locationcode, $user_session->user['user']['amp'])) {
            return true;
        }
        return false;
    }

    public function streamPDF($uri, $disposition = "inline", $fileName = "download") {


        if (!is_file($uri)) {
            //throw exception for now
            throw new \Exception("Cannot locate the file requested file. Please contact the <a href='mailto:asd@crl.edu'>Access Services Department</a>.");
        }

        $fileContents = file_get_contents($uri);

        $response = new \Zend\Http\Response\Stream();
        $response->setContent($fileContents);

        $headers = new \Zend\Http\Headers();
        $headers->clearHeaders()
                ->addHeaderLine('Content-Type', 'application/pdf')
                ->addHeaderLine('Content-Disposition', $disposition . '; filename="' . $fileName . '"');
        $response->setHeaders($headers);
        $response->setStream(fopen($uri, 'r'));
        $response->setStatusCode(200);

        return $response;
    }

    public function mergePDF($files) {

        $pdfNew = new PdfDocument();
        $extractor = new Zend_Pdf_Resource_Extractor();

        foreach ($files as $file) {
            $pdf = PdfDocument::load($file);
            foreach ($pdf->pages as $p)
                $pdfNew->pages[] = $extractor->clonePage($p);
        }

        //output new pdf as a string
        return $pdfNew->render();
    }

    public function doAuthorization($e) {
        //setting ACL...
        $acl = new Acl();
        //add role ..
        $acl->addRole(new Role('nonmember'));
        $acl->addRole(new Role('member'));
        $acl->addRole(new Role('admin'), 'member');

        $acl->addResource(new Resource('home'));
        $acl->addResource(new Resource('dds-title'));
        $acl->addResource(new Resource('dds-item'));
        $acl->addResource(new Resource('dds-scan'));


        $acl->allow('nonmember', array('home'), array('view'));
        $acl->deny('nonmember', array('dds-title', 'dds-item', 'dds-scan'), array('view'));
        $acl->allow('member', array('home', 'dds-title', 'dds-item', 'dds-scan'), array('view'));

        $route = $e->getRouteMatch()->getMatchedRouteName();

        $role = (!$this->getSessContainer()->user['user']['role'] ) ? 'nonmember' : $this->getSessContainer()->user['user']['role'];
    }

}
