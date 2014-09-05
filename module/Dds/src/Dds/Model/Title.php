<?php
namespace Dds\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Title implements InputFilterAwareInterface
{
    public $tid;
    public $title;
    public $oclc;
    public $oclcold;
    public $call;
    public $locationcode;
    public $collectionlink;
    public $tfolder;
    public $folder;
    public $subfolder;
    public $fileprefix;
    public $holdingrange;
    public $seq;
    public $viewtype;
    public $titleimage;
    public $tnotes;
    public $tcreated;
    public $timestamp;
    
    

    public function exchangeArray($data)
    {        
        $this->tid  = (isset($data['TID'])) ? $data['TID'] : null;
        $this->title = (isset($data['Title'])) ? $data['Title'] : null;
        $this->oclc = (isset($data['OCLC'])) ? $data['OCLC'] : null;
        $this->oclcold = (isset($data['OCLCOld'])) ? $data['OCLCOld'] : null;
        $this->call = (isset($data['call'])) ? $data['call'] : null;
        $this->locationcode = (isset($data['LocationCode'])) ? strtoupper(substr($data['LocationCode'], 0, 4)) : null;
        $this->collectionlink = (isset($data['CollectionLink'])) ? $data['CollectionLink'] : null;
        $this->tfolder = (isset($data['TFolder'])) ? $data['TFolder'] : null;
        $this->folder = (isset($data['Folder'])) ? $data['Folder'] : null;
        $this->subfolder = (isset($data['SubFolder'])) ? $data['SubFolder'] : null;
        $this->fileprefix = (isset($data['FilePrefix'])) ? $data['FilePrefix'] : null;
        $this->holdingrange = (isset($data['holdingRange'])) ? $data['holdingRange'] : null;
        $this->seq = (isset($data['Seq'])) ? $data['Seq'] : null;
        $this->viewtype = (isset($data['ViewType'])) ? $data['ViewType'] : null;
        $this->viewtypename = $this->getViewTypeName($this->viewtype);
        $this->titleimage = (isset($data['TitleImage'])) ? $data['TitleImage'] : null;
        $this->tnotes = (isset($data['tNotes'])) ? $data['tNotes'] : null;
        $this->tcreated = (isset($data['tCreated'])) ? $data['tCreated'] : null;
        $this->timestamp = (isset($data['Timestemp'])) ? $data['Timestemp'] : null;
    }
    public function getViewTypeName($viewtype){
        switch ($viewtype) {
                case 1:
                    return 'Calendar';
                    break;
                case 2:
                    return 'Two-Tier';
                    break;
                case 3:
                    return 'Three-tier';
                    break;
                default:
                    return 'Default';
                    break;
            }
    }
    public function getArrayCopy() {
        return get_object_vars($this);
    }
    
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }
     
    public function getInputFilter()
    {
        /* common code here */
        return $this->inputFilter;
    }
        
}