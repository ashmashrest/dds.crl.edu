<?php
namespace Dds\Model;

use Zend\Db\TableGateway\TableGateway;

class CollectionTable
{
    protected $tableGateway;
    public $collection;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function getCollections($options = NULL )
    { 
        $resultSet = $this->tableGateway->select($options);
        
        return $resultSet;
    }

    public function getCollection($id)
    {
        $id  = (int) $id; 
        if (!$id) {
            throw new \Exception("Collection ID is not set");
        }
        if (!$this->collection) {
            $this->collection = $this->setCollection($id);
        }
        return $this->collection;
    }
    
    public function setCollection($id){
         $id  = (int) $id;
        
         $rowset = $this->getCollections(array('CID' => $id));
        
        $this->collection = $rowset->current();
        
        if (!$this->collection) {
            throw new \Exception("Could not find Item $id");
        }     
       
        //Set Variables based on the collection format
        switch ($this->collection->format) {
            case 'microfilm':
                $this->collection->resolution .= " for bitonal; 300 dpi for grayscale";
                $this->collection->colordepth .= "; 8 bit grayscale as needed";
                $this->collection->scanner = "Mekel M525 microfilm scanner";
                break;
            case 'microfiche':
                $this->collection->colordepth .= "; 8 bit grayscale as needed";
                $this->collection->scanner = "Mekel Mach VII fiche scanner";
                break;
            case 'print':
                $this->collection->colordepth .= "; 8 bit grayscale or 24 bit color as needed";
                $this->collection->scanner = "i2S CopiBook RGB overhead book scanner or Fujitsu fi-5015C flatbed scanner";
                break;
            case 'microcard':
                $this->collection->colordepth .= "; 8 bit grayscale as needed";
                $this->collection->scanner = "Mekel Mach VII fiche scanner";
                break;
            default:
                $this->collection->scanner = "N/A";
                break;
        }
        return $this->collection;
    }
}