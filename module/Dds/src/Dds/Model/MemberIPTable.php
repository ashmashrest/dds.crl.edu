<?php

namespace Dds\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;
use Zend\Http\PhpEnvironment\RemoteAddress;
use Zend\Session\Config\SessionConfig;
use Zend\Session\SessionManager;
use Zend\Session\Container;

class MemberIPTable {

    protected $tableGateway;
    protected $user;
    protected $ipData;

    /**
     * Initiation for the memberIPtable
     * @param type $tableGateway
     */
    public function __construct($tableGateway) {
        $this->tableGateway = $tableGateway;
    }

    /**
     * Function used in the bootstrap initiatio 
     * @param type $shib
     * @return \Zend\Session\Container
     */
    public function getMemberInfo($shib = NULL) {

        $remote = new RemoteAddress();
        $ipaddress = $remote->getIpAddress();
        //$ipaddress = '123.21.12.4'; // non member ip
        //$ipaddress = '129.12.1.1'; //standford Member
        //$ipaddress = '132.229.36.1'; //CAMP & SEAM test
        $user_session = new Container('user');
        if (empty($shib)) {
            $user_session->user = array('user' => $this->ipCheck($ipaddress));
        } else {
            // If shibboleth authentication is passed track the member ip and update the session
            $shib = array_merge($shib, $this->getMemberID($shib['Shib-Identity-Provider']));
            $shib['ipaddress'] = $ipaddress;
            $user_session->user = array('user' => $shib);
        }

        return $user_session;
    }

    /**
     * Get the CRL member id based on shibboleth entity id
     * 
     * @param type $entity_id Shibboleth entity id
     * @return type
     */
    public function getMemberID($entity_id) {
        /* We will have to rewrite this when we migrate Member database to something other than MSSQL. For now it seems to work good */
        $sql = "Select MemberID as instid from members "
                . "where Shibboleth = '" . $entity_id . "'";

        $statement = $this->tableGateway->query($sql);

        $result = $statement->execute();
        return $result->current();
    }

    /**
     * Function used by the Drupal member check as well for the validation
     * 
     * @param type $ipaddress
     * @return type
     */
    public function ipCheck($ipaddress) {
        $this->user['ipaddress'] = $ipaddress;
        //Check IP in member table
        if (!$this->ipData)
            $this->checkIPTable($ipaddress, 'memberIP');

        if (!$this->ipData)
            $this->checkIPTable($ipaddress, 'goodIP');

        if ($this->ipData) {
            $this->user['role'] = 'member';
            $this->user['displayName'] = $this->ipData['MemberNameDisplay'];
            $this->user['instid'] = $this->ipData['MemberID'];
            if (!$this->ipData['CurrentCRLmember']) {
                $this->user['role'] = 'amp';
                $this->user['amp'] = $this->checkAMP($this->ipData['MemberID']);
            }
        }

        if (!$this->ipData || $this->checkIPTable($ipaddress, 'blockedIP')) {
            $this->user['role'] = 'nonmember';
        }
        return $this->user;
    }

 
    /**
     * Function for iterating the IP tables and get the member information as well
     * 
     * @param type $ipaddress
     * @param type $table
     * @return type
     */
    public function checkIPTable($ipaddress, $table) {
        /* We will have to rewrite this when we migrate Member database to something other than MSSQL. For now it seems to work good */
        $sql = "select * from " . $table . " LEFT JOIN members ON members.MemberID = " . $table . ".MemberID  where 
                (CAST(PARSENAME('" . $ipaddress . "',4) as int) Between CAST(PARSENAME(IPlow,4) as int) AND CAST(PARSENAME(IPhigh,4)as int)) AND
                (CAST(PARSENAME('" . $ipaddress . "',3) as int) Between CAST(PARSENAME(IPlow,3) as int) AND CAST(PARSENAME(IPhigh,3)as int)) AND
                (CAST(PARSENAME('" . $ipaddress . "',2) as int) Between CAST(PARSENAME(IPlow,2) as int) AND CAST(PARSENAME(IPhigh,2)as int)) AND
                (CAST(PARSENAME('" . $ipaddress . "',1) as int) Between CAST(PARSENAME(IPlow,1) as int) AND CAST(PARSENAME(IPhigh,1) as int))";
        if ($table == "memberIP")
            $sql .= " AND " . $table . ".expired IS NULL";

        $statement = $this->tableGateway->query($sql);

        $result = $statement->execute();

        $this->ipData = $result->current();
        return $this->ipData;
    }
    
    /**
     * Check the AMP memberships 
     * 
     * @param type $memberid
     * @return array
     */
    public function checkAMP($memberid) {

        $ampArray = array();

        $sql = "SELECT ampType FROM AMP WHERE MemberID ='" . $memberid . "'";

        $statement = $this->tableGateway->query($sql);

        $result = $statement->execute();

        if (!$result->count()) {
            return;
        }

        $rows = new ResultSet();

        $amps = $rows->initialize($result)->toArray();

        foreach ($amps as $amp) {

            array_push($ampArray, trim($amp['ampType']));
        }

        return $ampArray;
    }
    /**
     * Get the list of Shibboleth EntityIDs to populate in the json fed to discovery
     * 
     * @return type
     */
    public function getIdps() {
        
        $sql = "SELECT Shibboleth as entityID, MemberNameDisplay as DisplayNames FROM members "
                . "WHERE members.Shibboleth  IS NOT NULL  "
                . "AND CurrentCRLmember=1 "
                . "ORDER by MemberNameAlpha;";

        $statement = $this->tableGateway->query($sql);

        $result = $statement->execute();
        
        return $result;
    }

}
