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

class LogTable {

    protected $tableGateway;

    /**
     * LogTable class constructor* 
     */
    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }

    /**
     * Fetch all the log from an IP within the interval * 
     * @return count
     */
    public function checkLog($ipaddress) {
        //Get the total usuage count in last 2 days
        $select = $this->tableGateway->getSql()->select();
        $select->columns(array('total' => new \Zend\Db\Sql\Expression('COUNT(*)')));
        $select->where(array("ipaddress = '" . $ipaddress . "'",
            "DateTimeStamp > DATEADD(dd, -2, GETDATE())"
        ));
        // echo $select->getSqlString();
        $resultSet = $this->tableGateway->selectWith($select);

        //Check if the total usage is greater than 100 and send out email
        if ($resultSet->current()->total > 100) {
            $message = new Message();
            $message->addFrom("ashrestha@crl.edu", "DDS Systems")
                ->addTo("ashrestha@crl.edu")
                ->setSubject("DDS - high IP alert");
            $message->setBody("IP =" . $ipaddress);

        $transport = new Sendmail();
        $transport->send($message);
        }
        return;
    }

    /**
     *  Save the log information for the item
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
        } else {
            //Increase Item Counter
            $itemtable->updateCounter($itemtable->item->iid);
        }

        return;
    }

}
