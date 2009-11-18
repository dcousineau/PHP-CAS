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
class CAS_Client
{
    const REDIRECTED_FOR_LOGIN = -10;
    
    protected $_serverSSL = false;
    protected $_serverHostname = null;
    protected $_serverPort = null;
    protected $_serverURI = null;
    
    /**
     * 
     * @var CAS_Version
     */
    protected $_version;

    protected $_curlOptions = array(
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

    public function __construct(array $options = array())
    {
        if( !function_exists('curl_init') )
        {
            throw new Exception("PHP's CURL extension is required for CAS authentication");
        }

        if( !function_exists('simplexml_load_string') )
        {
            throw new Exception("PHP's SIMPLEXML extension is required for CAS authentication");
        }
        
        $this->setOptions($options);
    }
    
    /**
     *
     * @param array $options
     * @return CAS_Client *fluent interface*
     */
    public function setOptions(array $options)
    {
        foreach( $options as $key => $value )
        {
            $method = "set$key";
            
            if( $method == strtolower(__FUNCTION__) )
                throw new CAS_Exception("Invalid Option '$key'");
            
            $reflection = new ReflectionObject($this);
            
            if( $reflection->hasMethod($method) )
            {
                if( !is_array($value) )
                    $value = array($value);
                
                $reflection->getMethod($method)->invokeArgs($this, $value);
            }
            else
            {
                throw new CAS_Exception("'$key' option does not exist");
            }
        }
        
        return $this;
    }
    
    /**
     * Triggers a login.
     * 
     * If a ticket is present in the _GET string, it validates and returns the
     * ticket.
     * 
     * If not ticket is present it redirects to the login service (only if
     * $autoRedirect is set to true) and returns a CAS_Client::REDIRECTED_FOR_LOGIN 
     * status code.
     * 
     * @param boolean $autoRedirect
     * @return CAS_Ticket|int
     */
    public function login(CAS_Ticket $ticket = null, $autoRedirect = true)
    {
        if( $ticket )
        {
            return $this->getVersion()->validateTicket($ticket);
        }
        else
        {
            if( $autoRedirect )
                header('Location: ' . $this->getCASLoginService());
    
            return self::REDIRECTED_FOR_LOGIN;
        }
    }

    public function logout()
    {
        // Nothing to do here
    }
    
    /**
     * Shortcut to get CAS Login Service URL from the CAS version
     * 
     * @return string
     */
    public function getCASLoginService()
    {
        return $this->getVersion()->getCASLoginService();
    }
    
    /**
     * Wrapper function to make a CURL request
     * 
     * @param string $url
     * @return string
     */
    public function curl_fetch($url)
    {
        $ch = curl_init($url);
        
        curl_setopt_array($ch, $this->getCurlOptions());

        $output = curl_exec($ch);

        if( $output === false )
        {
            throw new CAS_Exception("CURL Error: #" . curl_errno($ch) . " " . curl_error($ch));
        }

        curl_close($ch);

        return $output;
    }
    
    /**
     * Force the use of SSL when connecting to the CAS server
     *
     * @param boolean $ssl
     * @return CAS_Client *Provides a fluid interface*
     */
    public function setServerSSL($ssl)
    {
        $this->_serverSSL = (bool)$ssl;
        return $this;
    }

    /**
     *
     * @return boolean
     */
    public function getServerSSL()
    {
        return (bool)$this->_serverSSL;
    }

    /**
     * The domain portion of the URL (e.g. http://DOMAIN/path/)
     *
     * @param string $hostname
     * @return CAS_Client *Provides a fluid interface*
     */
    public function setServerHostname($hostname)
    {
        $this->_serverHostname = trim($hostname, '\\/');
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getServerHostname()
    {
        return $this->_serverHostname;
    }

    /**
     * @param int $port
     * @return CAS_Client *Provides a fluid interface*
     */
    public function setServerPort($port)
    {
        if( $port == null )
            $this->_serverPort = null;
        else if( !is_int($port) )
            throw new Exception("Port '{$port}' must be an integer");
        else
            $this->_serverPort = (int)$port;

        return $this;
    }

    /**
     *
     * @return int
     */
    public function getServerPort()
    {
        return $this->_serverPort;
    }

    /**
     * The path portion of the URL (e.g. http://domain/PATH/)
     *
     * @param string $uri
     * @return CAS_Client *Provides a fluid interface*
     */
    public function setServerURI($uri)
    {
        $this->_serverURI = '/' . trim($uri,'\\/');
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getServerURI()
    {
        return $this->_serverURI;
    }

    /**
     * @param array $options
     * @return CAS_Client *Provides a fluid interface*
     */
    public function setCurlOptions(array $options)
    {
        foreach( $options as $key => $value )
        {
            $this->_curlOptions[$key] = $value;
        }
        
        return $this;
    }

    /**
     * @return array
     */
    public function getCurlOptions()
    {
        return $this->_curlOptions;
    }
    
    /**
     * 
     * @param CAS_Version|string $version
     * @return CAS_Client *Provides a fluid interface*
     */
    public function setVersion($version)
    {
        if( $version instanceOf CAS_Version )
        {
            $this->_version = $version;
        }
        else if( is_string($version) )
        {
            $class = "CAS_Version_$version";
            
            if( class_exists($class) )
            {
                $this->_version = new $class();
            }
            else
            {
                throw new CAS_Exception("CAS version $version class not found (Looking for '$class')");
            }
        }
        else if( is_array($version) && count($version) > 0 && count($version) <= 2 )
        {
            $version_no = $version[0];
            $class = "CAS_Version_$version_no";
            
            if( class_exists($class) )
            {
                $reflection = new ReflectionClass($class);
                
                if( !isset($version[1]) )
                    $version[1] = array();
                    
                if( !is_array($version[1]) )
                    $version[1] = array($version[1]);
                
                $this->_version = $reflection->newInstanceArgs($version[1]);
            }
            else
            {
                throw new CAS_Exception("CAS version $version_no class not found (Looking for '$class')");
            }
        }
        else
        {
            throw new CAS_Exception("Invalid version provided");
        }
        
        $this->_version->setClient($this);
        
        return $this;
    }
    
    /**
     * 
     * @return CAS_Version
     */
    public function getVersion()
    {
        return $this->_version;
    }
}