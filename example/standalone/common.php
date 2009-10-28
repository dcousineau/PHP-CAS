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

/**
 * Autoloader function to load CAS and other classes located in source.
 * 
 * Works on the standard PEAR naming conventions
 * 
 * @param string $class
 * @return boolean
 */
function _phpcas_examples_autoload($class)
{
    $filename = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
    
    if( file_exists(PHPCAS_EXAMPLES_SOURCE_DIRECTORY . '/' . $filename) )
    {
        include_once PHPCAS_EXAMPLES_SOURCE_DIRECTORY . '/' .$filename;
        return true;
    }
    
    return false;
}

spl_autoload_register('_phpcas_examples_autoload');

// Autoloader needs to be set (or CAS ticket classes included) before they are de-serialized
session_start();