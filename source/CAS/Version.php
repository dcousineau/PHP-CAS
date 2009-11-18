<?php
/**
 *
 *
 * @package     PHPCAS
 * @subpackage  Version
 *
 * @author      Daniel Cousineau <danielc@doit.tamu.edu>
 * 
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @copyright   © 2009 Department of IT, Division of Student Affairs, Texas A&M University
 */

abstract class CAS_Version
{
    /**
     * 
     * @var CAS_Client
     */
    protected $_client;
    
    /**
     * 
     * @param CAS_Ticket $ticket
     * @return CAS_Ticket Validated Ticket
     */
    abstract public function validateTicket(CAS_Ticket $ticket = null);
    
    public function getCASURL()
    {
        return ($this->getClient()->getServerSSL() ? 'https://' : 'http://') .
               $this->getClient()->getServerHostname() .
               ($this->getClient()->getServerPort() !== null ? ':'.$this->getClient()->getServerPort() : '') .
               $this->getClient()->getServerURI();
    }
    
    public function getCASLoginService()
    {
        return $this->getCASURL() . '/login?service=' . urlencode($this->getThisService());
    }
    
    public function getCASValidateService(CAS_Ticket $ticket)
    {
        return $this->getCASURL() . '/serviceValidate?ticket='.urlencode($ticket->getTicketID()).'&service=' . urlencode($this->getThisService());
    }
    
    /**
     * Returns the full URL (including GET string) of the current page.
     * 
     * @return string
     */
    protected function getThisService()
    {
        $this_service = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://' ) .
                        $_SERVER['HTTP_HOST'];

        if( isset($_SERVER['QUERY_STRING']) && trim($_SERVER['QUERY_STRING']) != '' )
        {
            $this_service .= preg_replace('#'.preg_quote('?'.$_SERVER['QUERY_STRING'],'#').'$#i', '', $_SERVER['REQUEST_URI']);
        }
        else
        {
            $this_service .= $_SERVER['REQUEST_URI'];
        }

        return $this_service;
    }
    
    /**
     * 
     * @param CAS_Client $client
     * @return CAS_Version *fluent interface*
     */
    public function setClient(CAS_Client $client)
    {
        $this->_client = $client;
        
        return $this;
    }
    
    /**
     * 
     * @return CAS_Client
     */
    public function getClient()
    {
        return $this->_client;
    }
}