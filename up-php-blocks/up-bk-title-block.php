<?php
/*------------------------------------------------------
*	Ultimate Portal
*	Version 0.3
*	Project Manager: vicram10
*	Copyright 2011
*	Powered by SMFSimple.com
--------------------------------------------------------
Got DB connection, all global variables
and all functions of the Portal and your availability Forum
*/
//NOT DELETE THIS PART
if (!defined('SMF'))
	die('Hacking attempt...');
//END IMPORTANT PART

global $user_info, $txt, $context;
$username = $user_info['username'];
echo $txt['ultport_tmp_bk_php_hello'] . ' <strong>'. $username . '</strong>';
?>