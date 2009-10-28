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

require_once dirname(__FILE__) . '/common.php';

unset($_SESSION['auth']);

header('Location: ./index.php', true, 302);