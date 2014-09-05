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

    public function streamPDF($uri, $disposition = "inline", $fileName ="download") {
        

            if(!is_file($uri)) {
                //throw exception for now
                throw new \Exception("Cannot locate the file requested file. Please contact the <a href='mailto:asd@crl.edu'>Access Services Department</a>." );
            }

            $fileContents = file_get_contents($uri);

            $response = new \Zend\Http\Response\Stream();
            $response->setContent($fileContents);

           $headers = new \Zend\Http\Headers();
            $headers->clearHeaders()
                ->addHeaderLine('Content-Type', 'application/pdf')
                ->addHeaderLine('Content-Disposition', $disposition.'; filename="' . $fileName . '"');
            $response->setHeaders($headers);
            $response->setStream(fopen($uri, 'r'));
            $response->setStatusCode(200);
            
            return $response;
  
    }
    
    public function mergePDF($files) {
        
        $pdfNew = new PdfDocument();
        $extractor = new Zend_Pdf_Resource_Extractor();

        //load pdf 1 from a string
      //  $pdf1 = Zend_Pdf::parse(parent::renderPDF());
        //Clone pages of first pdf document and add the pages to the new pdf object
    //    foreach ($pdf1->pages as $p) $pdfNew->pages[] = $extractor->clonePage($p);

        //load pdf 2 from a file
        foreach ( $files as $file) {
            $pdf  = PdfDocument::load($file);
            //Clone pages of second pdf document and add the pages to the new pdf object
            foreach ($pdf->pages as $p) $pdfNew->pages[] = $extractor->clonePage($p);

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

      
        $acl->allow('nonmember', array('home'), array('view') );
        $acl->deny('nonmember', array('dds-title', 'dds-item', 'dds-scan'), array('view') );
        $acl->allow('member', array('home', 'dds-title', 'dds-item', 'dds-scan'), array('view') );
        
        //admin is child of user, can publish, edit, and view too !
        //$acl->allow('admin', array('DDS'), array('publish', 'edit')
           //  );

        $route = $e -> getRouteMatch() -> getMatchedRouteName();
      
         $role = (!$this->getSessContainer()->user['user']['role'] ) ? 'nonmember' : $this->getSessContainer()->user['user']['role'];
       /* if (!$acl->isAllowed($role, $route, 'view')) {
            $router = $e->getRouter();
           // $url    = $router->assemble(array(), array('name' => 'home'));

            $response = $e->getResponse();
            $response->setStatusCode(404);
            //redirect to login route...
            /* change with header('location: '.$url); if code below not working */
          //   $response->getHeaders()->addHeaderLine('Location', $url);
           /* $e->stopPropagation();
        }*/
    }

}