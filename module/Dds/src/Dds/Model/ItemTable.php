<?php

namespace Dds\Model;

use Zend\Db\TableGateway\TableGateway;
use Dds\Model\TitleTable;
use Zend\Db\Sql\Expression;

class ItemTable {

    protected $tableGateway;
    protected $id;
    public $item;
    protected  $titleTable;
    protected $options;
    protected $item_prev;
    protected $item_next;
    protected $item_parts;
    protected $fileLocation;
    protected $folderLocation;

    /* Set the tableGateway */

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }

    /* Get all the items in the title */

    public function getItems($tid) {
        /* Set filter options */
        $this->setOptions(array('TitleLink' => $tid));

        $select = $this->tableGateway->getSql()->select();
        $select->where($this->getOptions());

        $select->order('sortorder ASC');
        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    /*
     * Get the Item 
     */

    public function getItem($id, $f = null) {
        $this->id = (int) $id;
        $this->f = $f;
        if (!$this->id) {
            throw new \Exception("Item needs to be set");
        }
        if (!$this->item) {
            $this->item = $this->setItem();
        }
        return $this->item;
    }

    /*
     * Set Item 
     */

    public function setItem() {

        $rowset = $this->tableGateway->select(array('IID' => $this->id));

        $this->item = $rowset->current();

        if (!$this->item) {
            throw new \Exception("The issue requested cannot be found in the system. Please contact Acess Service Department(ads@crl.edu) for further assistance. ($this->id)");
        }
        //Set f
        if ($this->f)
            $this->item->f = $this->f;

        //Set Title Information 
        $this->item->title = $this->titleTable->getTitle($this->item->titlelink);
        $this->setFileLocation();

        return $this->item;
    }

    public function setTitle(TitleTable $titleTable) {
        
        //Set Title object 
        $this->titleTable = $titleTable;
    }

    /*
     * Set filter option for the query
     */

    public function setOptions($options) {

        if (!$this->options)
            $this->options = (array) $options;
        else
            $this->options = array_merge($this->options, $options);
    }

    /*
     * Get filter option for the query
     */

    public function getOptions() {
        return array_unique($this->options);
    }

    /*
     * Get the Previous item
     */

    public function getPrev() {
        if (!$this->item_prev) {
            $this->item_prev = $this->setPrev($this->id, $this->item->sortorder);
        }
        return $this->item_prev;
    }

    /*
     * Set the Previous item in the list
     */

    public function setPrev($id, $sortorder) {
        if (!$this->item) {
            $this->item = $this->getItem($id);
        }

        $select = $this->tableGateway->getSql()->select();
        $select->columns(array('*'));

        if (!empty($sortorder)) {
            // If the sortorder is present, get the previous based on the sort order
            $select->where(array('TitleLink=' . $this->item->titlelink, 'SortOrder <' . $sortorder));
            $select->order('sortorder DESC');
        } else {
            //If there is no sortorder, get the previous based on the IID
            $select->where(array('TitleLink=' . $this->item->titlelink, 'IID <' . $id));
            $select->order('IID DESC');
        }

        // $select->limit(1); //TODO this is giving problem with the MSSQL (PDO) adapter

        $resultSet = $this->tableGateway->selectWith($select);
        $this->item_prev = $resultSet->current();
        return $this->item_prev;
    }

    /*
     * Get the Next item in the list
     */

    public function getNext() {
        if (!$this->item_next) {
            $this->item_next = $this->setNext($this->id, $this->item->sortorder);
        }
        return $this->item_next;
    }

    /*
     * Set the Previous item
     */

    public function setNext($id, $sortorder) {
        if (!$this->item) {
            $this->item = $this->getItem($id);
        }

        $select = $this->tableGateway->getSql()->select();
        $select->columns(array('*'));

        if (!empty($sortorder)) {
            // If the sortorder is present, get the next based on the sort order
            $select->where(array('TitleLink=' . $this->item->titlelink, 'SortOrder >' . $sortorder));
            $select->order('SortOrder ASC');
        } else {
            //If there is no sortorder, get the next based on the IID
            $select->where(array('TitleLink=' . $this->item->titlelink, 'IID >' . $id));
            $select->order('IID ASC');
        }

        // $select->limit(1); //TODO this is giving problem with the MSSQL (PDO) adapter

        $resultSet = $this->tableGateway->selectWith($select);
        $this->item_next = $resultSet->current();

        return $this->item_next;
    }

    /*
     * Get if the right is set for the items in the title
     */

    public function getRights($tid, $access) {
        $this->setOptions(array('Rights' => $access));
        $rights = $this->getItems($tid)->current();
        return $rights;
    }

    /*
     * Get the parts of items
     */

    public function getParts() {
        if (!$this->item_parts) {
            $this->item_parts = $this->setParts($this->id);
        }
        return $this->item_parts;
    }

    /*
     * Set item parts
     *  This concept is carried over form the previous data where items are divided into multiple parts.
     */

    public function setParts($id) {

        if (!$this->item) {
            $this->item = $this->getItem($id);
        }
        $parts = array();
        //Toc
        if ($this->item->toc) {
            array_push($parts, array("name" => "TOC",
                "f" => $this->item . '-TOC.pdf'));
        }
        // PDF Split 
        if ($this->item->digitalpno > 1) {
            if ($this->item->fileuneven == "0" || empty($this->item->fileuneven)) {
                $pageMin = 1;
                $pageMax = 100;
                for ($file = 1; $file <= $this->item->digitalpno - 1; $file++) {
                    array_push($parts, array("name" => "Scans" . $pageMin . "-" . $pageMax,
                        "f" => $this->item->filename . '-f' . $file . ".pdf"));

                    // Updated the page count
                    $pageMin = $pageMin + 100;
                    $pageMax = $pageMax + 100;
                }
                array_push($parts, array("name" => "Scans" . $pageMin . "-" . $this->item->pages,
                    "f" => $file));
            } else {
                for ($file = 1; $file <= $this->item->digitalpno; $file++) {
                    array_push($parts, array("name" => "File " . $file,
                        "f" => $this->item->filename . '-f' . $file . ".pdf"));
                }
            }
        }


        $this->item_parts = $parts;
        return $this->item_parts;
    }

    /*
     * Get the list of list of Scans for each items
     * This is a new concept where CRL decided to use PER page PDF 
     */

    public function setFolderLocation() {

        if (!$this->titleTable) {
            throw new \Exception("Cannot find the Title Object");
        }
        $this->folderLocation = $this->titleTable->getFolderLocation();

        if (is_dir($this->folderLocation . "/" . $this->item->filename)) {
            $this->folderLocation .= "/" . $this->item->filename;
            $this->item->perpage = "true";
        }

        return $this->folderLocation;
    }

    /**
     * Get the Folder location
     * @return type string
     */
    public function getFolderLocation() {

        if (!$this->folderLocation) {
            $this->folderLocation = $this->setFolderLocation($this->id);
        }
        return $this->folderLocation;
    }

    /**
     * 
     * Get the list of list of Scans for each items
     * This is a new concept where CRL decided to use PER page PDF 
     */
    public function setFileLocation() {

        if (!$this->titleTable) {
            throw new \Exception("Cannot find the Title Object");
        }
        $folder = $this->getFolderLocation();

        if ($this->f) {

            if ($this->item->perpage)
                $this->fileLocation = $folder . "/" . sprintf("%03d", $this->item->f) . ".pdf";
            else
                $this->fileLocation = $folder . "/" . $this->item->filename . "-" . $this->item->f . ".pdf";
        }

        if (!$this->fileLocation)
            $this->fileLocation = $folder . "/" . $this->item->filename . ".pdf";
        return $this->fileLocation;
    }

    /**
     * Get the File Location
     * @return type string
     */
    public function getFileLocation() {

        if (!$this->fileLocation) {
            $this->fileLocation = $this->setFileLocation($this->id);
        }
        return $this->fileLocation;
    }

    /**
     * Get the list of year range for the title
     * This function is used for the Calendar view to reduce the number of iterations to generate the Year dropdown
     * @param type $tid
     * @return type array
     */
    public function getYears($tid) {

        $years = array();
        $select = $this->tableGateway->getSql()->select();
        $select->columns(array(new Expression('DISTINCT(Year) as Year')));

        $select->where(array('TitleLink=' . $tid));

        $select->group('Year');
        // $select->limit(1); //TODO this is giving problem with the MSSQL (PDO) adapter

        $resultSet = $this->tableGateway->selectWith($select)->toArray();
        foreach ($resultSet as $result) {
            $years[] = $result['year'];
        }

        return $years;
    }

    /**
     * Update the iCounter for Item viewed
     * @param type $id
     * @throws \Exception
     */
    public function updateCounter($id) {
        if (!$this->item) {
            $this->item = $this->getItem($id);
        }
        try {
            $this->tableGateway->update(array(
                'iCounter' => new Expression('iCounter + 1')), array(
                'IID' => $this->id)
            );
        } catch (\Exception $e) {
            //Do something
            throw new \Exception("Could not update counter");
        }
        return;
    }

}
