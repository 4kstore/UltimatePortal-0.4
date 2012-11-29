<?php
//Ultimate Portal
//Copyright 2012
//Powered by www.smfsimple.com
//$context['uninstalling'] = 1;
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif(!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify that you put this file in the same place as SMF\'s index.php and SSI.php files.');

if ((SMF == 'SSI') && !$user_info['is_admin'])
	die('Admin privileges required.');

//create actions hooks
$hooks = array(
	'integrate_pre_include' => '$sourcedir/Subs-UltimatePortal.php,$sourcedir/Subs-UltimatePortal-Init-Blocks.php',
	'integrate_actions' => 'UP_Actions_Hooks',
	'integrate_menu_buttons' => 'UP_Menu_Buttons_Hooks',
	'integrate_buffer' => 'up_Hooks_Copy',
);

if (!empty($context['uninstalling']))
	$call = 'remove_integration_function';
else
	$call = 'add_integration_function';

foreach ($hooks as $hook => $function)
	$call($hook, $function);