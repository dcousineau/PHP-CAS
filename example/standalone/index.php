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
?>
<html>
<head>
    <title>PHP CAS Standalone Example</title>
</head>
<body>

<?php if( isset($_SESSION['auth']) ): ?>
    <p>
        <a href="./logout.php">Log Out</a>
    </p>
    
    <pre><?php print_r($_SESSION['auth']) ?></pre>
<?php else: ?>
    <p>
        <a href="./login.php">Log In</a>
    </p>
<?php endif; ?>




</body>
</html>