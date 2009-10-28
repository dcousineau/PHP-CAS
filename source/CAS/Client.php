<?php
/**
 *
 *
 * @package     PHP CAS
 *
 * @author      Daniel Cousineau <danielc@doit.tamu.edu>
 * 
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @copyright   © 2009 Department of IT, Division of Student Affairs, Texas A&M University
 */
class CAS_Client
{
    const REDIRECTED_FOR_LOGIN = -10;
    
    protected $serverSSL = false;
    protected $serverHostname = null;
    protected $serverPort = null;
    protected $serverURI = null;

    protected $curlOptions = array(
        CURLOPT_SSL_VERIFYHOST => false,    // verify server's certificate corresponds to its name
        CURLOPT_SSL_VERIFYPEER => false,    // don't verify the whole certificate though
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING       => "",       // handle all encodings
        CURLOPT_USERAGENT      => "curl",   // who am i
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
        CURLOPT_TIMEOUT        => 120,      // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
    );

    public function __construct($serverHostname = null, $serverPort = null, $serverURI = null, $serverSSL = false, $curlOptions = array())
    {
        if( !function_exists('curl_init') )
        {
            throw new Exception("PHP's CURL extension is required for CAS authentication");
        }

        if( !function_exists('simplexml_load_string') )
        {
            throw new Exception("PHP's SIMPLEXML extension is required for CAS authentication");
        }

        $this->setServerHostname($serverHostname)
             ->setServerPort($serverPort)
             ->setServerURI($serverURI)
             ->setServerSSL($serverSSL)
             ->setCurlOptions($curlOptions);
    }

    public function login($autoRedirect = true)
    {
        if( $ticket = $this->createTicketFromGET() )
        {
            return $ticket;
        }

        if( $autoRedirect )
            header('Location: ' . $this->getCASLoginService());

        return self::REDIRECTED_FOR_LOGIN;
    }

    public function logout()
    {
        // Nothing to do here
    }

    public function createTicketFromGET()
    {
        if( !isset($_GET['ticket']) )
            return false;

        $ticket = new CAS_Ticket($_GET['ticket']);

        return $this->validateTicket($ticket);
    }

    public function validateTicket(CAS_Ticket $ticket)
    {
        $output = $this->curl_fetch($this->getCASValidateService($ticket));

        $return_data = simplexml_load_string($output);

        $cas_namespace = $return_data->getDocNamespaces();
        $cas_namespace = $cas_namespace['cas'];

        if( isset($return_data->children($cas_namespace)->authenticationFailure) )
        {
            throw new Exception($return_data->children($cas_namespace)->authenticationFailure);
        }
        elseif( isset($return_data->children($cas_namespace)->authenticationSuccess) )
        {
            $ticket->setNetID($return_data->children($cas_namespace)->authenticationSuccess->NetID)
                   ->setUIN($return_data->children($cas_namespace)->authenticationSuccess->UIN);

            return $ticket;
        }
        else
        {
            throw new Exception("Possible Malformed CAS Response");
        }
    }

    protected function curl_fetch($url)
    {
        $ch = curl_init($url);
        
        curl_setopt_array($ch, $this->getCurlOptions());

        $output = curl_exec($ch);

        if( $output === false )
        {
            throw new Exception("CURL Error: #" . curl_errno($ch) . " " . curl_error($ch));
        }

        curl_close($ch);

        return $output;
    }

    public function getCASURL()
    {
        return ($this->getServerSSL() ? 'https://' : 'http://') .
               $this->getServerHostname() .
               ($this->getServerPort() !== null ? ':'.$this->getServerPort() : '') .
               $this->getServerURI();
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
     * Force the use of SSL when connecting to the CAS server
     *
     * @param boolean $ssl
     * @return CAS_Client *Provides a fluid interface*
     */
    public function setServerSSL($ssl)
    {
        $this->serverSSL = (bool)$ssl;
        return $this;
    }

    /**
     *
     * @return boolean
     */
    public function getServerSSL()
    {
        return (bool)$this->serverSSL;
    }

    /**
     * The domain portion of the URL (e.g. http://DOMAIN/path/)
     *
     * @param string $hostname
     * @return CAS_Client *Provides a fluid interface*
     */
    public function setServerHostname($hostname)
    {
        $this->serverHostname = trim($hostname, '\\/');
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getServerHostname()
    {
        return $this->serverHostname;
    }

    /**
     * @param int $port
     * @return CAS_Client *Provides a fluid interface*
     */
    public function setServerPort($port)
    {
        if( $port == null )
            $this->serverPort = null;
        else if( !is_int($port) )
            throw new Exception("Port '{$port}' must be an integer");
        else
            $this->serverPort = (int)$port;

        return $this;
    }

    /**
     *
     * @return int
     */
    public function getServerPort()
    {
        return $this->serverPort;
    }

    /**
     * The path portion of the URL (e.g. http://domain/PATH/)
     *
     * @param string $uri
     * @return CAS_Client *Provides a fluid interface*
     */
    public function setServerURI($uri)
    {
        $this->serverURI = '/' . trim($uri,'\\/');
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getServerURI()
    {
        return $this->serverURI;
    }

    /**
     * @param array $options
     * @return CAS_Client *Provides a fluid interface*
     */
    public function setCurlOptions(array $options)
    {
        foreach( $options as $key => $value )
        {
            $this->curlOptions[$key] = $value;
        }
    }

    /**
     * @return array
     */
    public function getCurlOptions()
    {
        return $this->curlOptions;
    }
}