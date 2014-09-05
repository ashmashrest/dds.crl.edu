<?php
namespace Dds\Model;

class dLog
{
    public $lid; //PK
    public $itemlink; 
    public $instlink;
    public $ipaddress;
    public $timestamp;
    public $total;
       
    

    public function exchangeArray($data)
    {
        $this->lid  = (isset($data['LID'])) ? $data['LID'] : null;
        $this->itemlink = (isset($data['ItemLink'])) ? $data['ItemLink'] : null;
        $this->instlink = (isset($data['InstLink'])) ? $data['InstLink'] : null;
        $this->ipaddress = (isset($data['IPaddress'])) ? $data['IPaddress'] : null;
        $this->timestamp = (isset($data['DateTimeStamp'])) ? $data['DateTimeStamp'] : null;
        $this->total = (isset($data['total'])) ? $data['total'] : null;
        
    }
}