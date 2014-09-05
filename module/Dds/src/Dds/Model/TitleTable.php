<?php

namespace Dds\Model;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Dds\Model\ItemTable;


//Setting up cache 
use Zend\Cache\Storage\StorageInterface;
class TitleTable {

    protected $tableGateway;
    
    //Title id
    protected $id;
    
    //Title object
    protected $title;
    
    //Rights infromation
    protected $rights;
    
    //Items in the title
    protected $items;
    
    //Folder location
    protected $folder;
    
    //server repository location
    public $repository;
    
    //filter options
    protected $options;

    /**
     * TitleTable class constructor* 
     */
    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
      
    }
    
     public function setCache(StorageInterface $cache)
    {
        $this->cache = $cache;
    } 

    /**
     * Fetch all the Title in the Collection * 
     * @return resultSet
     */
    public function fetchAll($options, $paginated = true) {
           
         /* Set filter options */
        
        $select = $this->tableGateway->getSql()->select();
        $this->setOptions($options,  &$select);

        $select->order('title ASC');
        
        if ($paginated) {

            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new Title());

            // create a new pagination adapter object
            $paginatorAdapter = new DbSelect(
                            // our configured select object
                            $select,
                            // the adapter to run it against
                            $this->tableGateway->getAdapter(),
                            // the result set to hydrate
                            $resultSetPrototype
            );

            $paginator = new Paginator($paginatorAdapter);
            return $paginator;
        }
        
        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;

    }
    
    public function setOptions($options, &$select) {
       /* if (!$this->options) 
            $this->options = new Where();    
*/
        switch ($options['op']) {
            case 'like':
                $select->where->like($options['filter']['field'],$options['filter']['value']);
                break;
            case 'notEqualTo':
                $select->where->notEqualTo($options['filter']['field'],$options['filter']['value']);
                break;
            default :
                $select->where->equalTo($options['filter']['field'],$options['filter']['value']);
                break;
        }        
        
    }

    /*
     * Get filter option for the query
     */

    public function getOptions() {
        return $this->options;
    }

    /**
     * Set the Title object * 
     * @return Title object
     */

    public function setTitle($id) {
        $this->id = (int) $id;
        $rowset = $this->tableGateway->select(array('TID' => $this->id));
        $this->title = $rowset->current();
     
        if (!$this->title) {
            throw new \Exception("The title requested cannot be found in the system. Please contact Acess Service Department(ads@crl.edu) for further assistance. ($this->id)");
        }

        return $this->title;
    }

    /**
     * Fetch all the Title * 
     * @return Title object
     */
    public function getTitle($id) {
        if (!$this->title) {
            $this->id = (int) $id;
            $this->title = $this->setTitle($id);
        }
        return $this->title;
    }
    

    /**
     * Fetch the Folder information * 
     * @return rights object
     */
   
    public function getFolderLocation() {
        if (!$this->folder) {
            $this->folder = $this->setFolderLocation();
        }
        return $this->folder;
    }
    
    public function setRepository( $repository) {
        if (!$this->repository) {
            $this->repository = $repository;
        }        
        return  $this->repository;
    }
    
   /**
     * Set the folder location for the Title * 
     * @return folder (string)
     */
    public function setFolderLocation() {
        if (!$this->title) {
            $this->title = $this->setTitle($this->id);
        }
        $folder[] = $this->repository;
        $folder[] = $this->title->tfolder;
        $folder[] = $this->title->folder;
        $folder[] = $this->title->subfolder;
        $folder[] = $this->title->fileprefix;
        if (!is_dir(implode('/', $folder))) {
            array_pop($folder);
        }
        return $this->folder = implode('/', $folder);
    }

}