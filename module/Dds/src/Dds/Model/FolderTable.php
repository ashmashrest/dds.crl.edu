<?php
namespace Dds\Model;

use Zend\Db\TableGateway\TableGateway;

class FolderTable
{
    protected $tableGateway;
    public $folder;
    public $folderArray;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function getFolders( )
    { 
        $resultSet = $this->tableGateway->select();
        
        return $resultSet;
    }
    public function getFoldersArray() 
    {
        $resultSet = $this->tableGateway->select();
        foreach ($resultSet as $folder) {
            $this->folderArray[$folder->mtype] = $folder->disname;          
            
        }
       
        return $this->folderArray;
    }

   public function getFolder($id)
    {
        if (!$id) {
            throw new \Exception("folder is not set");
        }
        if (!$this->folder) {
            $this->folder = $this->setfolder($id);
        }
        return $this->folder;
    }
    
    public function setFolder($id){
        
         $rowset = $this->getfolder(array('Mtype' => $id));
        
        $this->folder = $rowset->current();
        
        if (!$this->folder) {
            throw new \Exception("Could not find Item $id");
        }     
       
        return $this->folder;
    }
}