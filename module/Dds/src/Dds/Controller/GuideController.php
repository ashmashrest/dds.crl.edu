<?php

namespace Dds\Controller;

use Zend\Mvc\Controller\AbstractActionController;

/**
 * Guides Controller
 * This is the replacement of images.crl.edu. The content will be moved to DDS share 
 * and will be streamed via this controller
 * 
 */
class GuideController extends AbstractActionController {

    protected $repository;
    protected $folder = 'images';

    /**
     * Default action of the Image class
     * 
     * @return 
     */
    public function indexAction() {

        $uri = $this->getRepository() . '/'
                . $this->folder . '/'
                . $this->tid = $this->params()->fromRoute('id');

        $this->asset = $this->plugin('AssetData');
        $finalPDF = $this->asset->streamPDF($uri);

        return $finalPDF;
    }

    /**
     * 
     * @return type
     */
    public function getRepository() {

        if (!$this->repository) {
            $sm = $this->getServiceLocator();
            $this->repository = $sm->get('repository');
        }
        return $this->repository;
    }

}

?>
