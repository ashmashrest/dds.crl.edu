<?php

namespace Dds\Model;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use DateTime;
use Zend\Mail\Message;
use Zend\Mail\Transport\Sendmail;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Mail\Transport\SmtpOptions;

class DLogTable {

    protected $tableGateway;

    /**
     * DLogTable class constructor* 
     */
    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }

    
    /**
     *  Save the downlaod log information for the item
     * @return 
     */
    public function saveLog($user, ItemTable $itemtable) {

        if (!is_array($user)) {
            throw new Exception\InvalidArgumentException(
            '$user must be an array '
            );
        }
        // Do not log if accessing from CRL (institution id 462) or the user is view parts > 1 if multipart item
        if ((isset($user['instid']) && $user['instid'] == '462') || $itemtable->item->f > 1) {
           return;
        }
        $datatime = new DateTime();

        $data = array(
            'ItemLink' => $itemtable->item->iid,
            'InstLink' => ($user['instid'])? $user['instid']: 0,
            'IPaddress' => $user['ipaddress'],
            'DateTimeStamp' => $datatime->format('m/d/Y h:i:s A'),
        );
     
        if (!$this->tableGateway->insert($data)) {
            throw new \Exception('Cannot add log');
        } 
        return;
    }

}
