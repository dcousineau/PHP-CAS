<?php
/**
 *
 *
 * @package     PHP CAS
 * @subpackage  Examples
 *
 * @author      Daniel Cousineau <danielc@doit.tamu.edu>
 * 
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @copyright   © 2010 Department of IT, Division of Student Affairs, Texas A&M University
 */

require_once dirname(__FILE__) . '/common.php';

if( !isset($_SESSION['auth']) )
{
    $version_2 = array(
        'serverHostname' => 'netid-dev.tamu.edu',
        'serverPort' => null,
        'serverURI' => '/cas',
        'serverSSL' => true,
        'version' => array('2', array()),
    );
    
    $version_3 = array(
        'serverHostname' => 'cas-dev.tamu.edu',
        'serverPort' => null,
        'serverURI' => '/cas',
        'serverSSL' => true,
        'version' => array('3', array('renew' => true)),
    );
    
    $cas_client = new CAS_Client($version_3);
    
    $ticket = $cas_client->login(CAS_Ticket::createFromGET(), false);
    
    if( $ticket === CAS_Client::REDIRECTED_FOR_LOGIN )
    {
        // Redirecting to the CAS login page
        header('Location: ' . $cas_client->getCASLoginService(), true, 302);
    }
    else
    {
        $_SESSION['auth'] = array(
            'ticket' => $ticket,
        );
        
        header('Location: ./index.php', true, 302);
    }
}
else
{
    // Already Logged In
}
?>
<html>
<head>
    <title>Login | PHP CAS Standalone Example</title>
</head>
<body>

<a href="./index.php">&laquo; Home</a>

<pre><?php isset($_SESSION['auth']) ? print_r($_SESSION['auth']) : 'NO AUTHENTICATION DATA PRESENT'; ?></pre>

</body>
</html>