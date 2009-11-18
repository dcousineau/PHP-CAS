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
 * @copyright   © 2009 Department of IT, Division of Student Affairs, Texas A&M University
 */

defined('PHPCAS_EXAMPLES_SOURCE_DIRECTORY')
    || define('PHPCAS_EXAMPLES_SOURCE_DIRECTORY', dirname(dirname(dirname(__FILE__))) . '/source');

require_once PHPCAS_EXAMPLES_SOURCE_DIRECTORY . '/CAS/Client.php';

CAS_Client::registerAutoload();
    
// Autoloader needs to be set (or CAS ticket classes included) before they are de-serialized
session_start();