<?php
/**
 *
 *
 * @package     PHPCAS
 *
 * @author      Daniel Cousineau <danielc@doit.tamu.edu>
 * 
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @copyright   © 2009 Department of IT, Division of Student Affairs, Texas A&M University
 */
class CAS_Ticket
{
    protected $_ticketid;
    protected $_netid;
    protected $_uin;
    protected $_user;
    
    /**
     * 
     * @return CAS_Ticket
     */
    public static function createFromGET($key = 'ticket')
    {
        if( !isset($_GET[$key]) )
            return false;
        
        return new self($_GET[$key]);
    }
    
    /**
     *
     * @param string $ticketid
     * @param string $netid
     * @param string $uin
     * @param string $user
     */
    public function __construct($ticketid, $netid = null, $uin = null, $user = null)
    {
        $this->setTicketID($ticketid)
             ->setNetID($netid)
             ->setUIN($uin)
             ->setUser($user);
    }
    
    /**
     *
     * @param string $ticketid
     * @return CAS_Ticket *Provides a fluid interface*
     */
    public function setTicketID($ticketid)
    {
        $this->_ticketid = (string)$ticketid;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getTicketID()
    {
        return $this->_ticketid;
    }

    /**
     *
     * @param string $netid
     * @return CAS_Ticket *Provides a fluid interface*
     */
    public function setNetID($netid)
    {
        $this->_netid = (string)$netid;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getNetID()
    {
        return $this->_netid;
    }

    /**
     *
     * @param string $uin
     * @return CAS_Ticket *Provides a fluid interface*
     */
    public function setUIN($uin)
    {
        $this->_uin = (string)$uin;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getUIN()
    {
        return $this->_uin;
    }

    /**
     *
     * @param string $user
     * @return CAS_Ticket *Provides a fluid interface*
     */
    public function setUser($user)
    {
        $this->_user = $user;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getUser()
    {
        return $this->_user;
    }
}