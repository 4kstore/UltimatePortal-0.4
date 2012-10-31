<?php
/*---------------------------------------------------------------------------------
*	Ultimate Portal
*	Version 0.3
*	Project manager: vicram10
*	Copyright 2011
*	Powered by SMFSimple.com
**********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');
	
function UltimatePortalMainCP()
{
	global $sourcedir, $context;

	//Inicialized the Ultimate Portal?
	$context['ultimate_portal_initialized'] = false;	
	
	// Load UltimatePortal Settings
	ultimateportalSettings();
	// Load UltimatePortal template
	loadtemplate('UltimatePortalCP');
	// Load Language
	if (loadlanguage('UltimatePortalCP') == false)
		loadLanguage('UltimatePortalCP','english');

	//Load Important Files
	require_once($sourcedir . '/Security.php');
	require_once($sourcedir . '/Load.php');
	require_once($sourcedir . '/Subs-UltimatePortal.php');
	
	$areas = array(
		'preferences' => array('', 'ShowPreferences'),
		'ultimate_portal_blocks' => array('UltimatePortal-BlocksMain.php', 'ShowBlocksMain'),
		'multiblock' => array('', 'ShowMultiblock'),
	);

	$_REQUEST['area'] = isset($_REQUEST['area']) && isset($areas[$_REQUEST['area']]) ? $_REQUEST['area'] : 'preferences';
	$context['admin_area'] = $_REQUEST['area'];

	if (!empty($areas[$_REQUEST['area']][0]))
		require_once($sourcedir . '/' . $areas[$_REQUEST['area']][0]);

	$areas[$_REQUEST['area']][1]();
}

function ShowPreferences()
{
	global $context, $txt, $settings;

	if (!allowedTo('ultimate_portal_cp'))
		isAllowedTo('ultimate_portal_cp');
		
	loadTemplate('UltimatePortalCP');
	
	//Load subactions for the ultimate portal preferences
	$subActions = array(
		'main' => 'ShowPreferencesMain',
		'gral-settings' => 'ShowPreferencesGralSettings',
		'lang-maintenance' => 'ShowPreferencesLangMaintenance',
		'lang-edit' => 'UltimatePortalEditLangs',
		'permissions-settings' => 'ShowPreferencesPermissionsSettings',
		'portal-menu' => 'ShowPortalMenuSettings',
		'save-portal-menu' => 'SaveMainLinks',
		'add-portal-menu' => 'AddMainLinks',
		'edit-portal-menu' => 'EditMainLinks',
		'delete-portal-menu' => 'DeleteMainLinks',
		'seo' => 'ShowSEO',		
	);
	
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'main';

	$context['sub_action'] = $_REQUEST['sa'];

	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['ultport_admin_preferences_title'] . ' - ' . $txt['ultport_preferences_title'],
		'description' =>  $txt['ultport_admin_preferences_description'],
		'tabs' => array(
			'main' => array(
				'description' => $txt['ultport_admin_preferences_description'],
			),
			'gral-settings' => array(
				'description' => $txt['ultport_admin_gral_settings_description'],
			),
			'lang-maintenance' => array(
				'description' => $txt['ultport_admin_gral_settings_description'],
			),
			'permissions-settings' => array(
				'description' => $txt['ultport_admin_gral_settings_description'],
			),
			'portal-menu' => array(
				'description' => $txt['ultport_admin_gral_settings_description'],
			),
			'seo' => array(
				'description' => $txt['ultport_seo_description'],
			),			
		),
	);

	$subActions[$_REQUEST['sa']]();
}

function ShowPreferencesMain()
{
	global $context, $txt, $sourcedir;
	require_once($sourcedir . '/Subs-UltimatePortal.php');		
	$context['sub_template'] = 'preferences_main';
	$context['page_title'] = $txt['ultport_admin_preferences_title'] . ' - ' . $txt['ultport_preferences_title'];
}

function ShowPreferencesGralSettings()
{
	global $context, $txt, $sourcedir;	

	require_once($sourcedir . '/Subs-UltimatePortal.php');	

	if (!empty($_POST['save']))
	{
		checkSession('post');
		saveUltimatePortalSettings("config_preferences");
		redirectexit('action=admin;area=preferences;sa=gral-settings;'. $context['session_var'] .'=' . $context['session_id']);
	}	
	$context['sub_template'] = 'preferences_gral_settings';
	$context['page_title'] = $txt['ultport_admin_gral_settings_title'] . ' - ' . $txt['ultport_preferences_title'];	
}

function ShowPreferencesLangMaintenance()
{
	global $context, $txt, $sourcedir;

	require_once($sourcedir . '/Subs-UltimatePortal.php');	

	//Load all Language from language folder
	UltimatePortalLangs();
	
	$context['sub_template'] = 'preferences_lang_maintenance';
	$context['page_title'] = $txt['ultport_admin_lang_maintenance_title'] . ' - ' . $txt['ultport_preferences_title'];
}

function UltimatePortalEditLangs()
{
	global $context, $txt, $sourcedir;

	require_once($sourcedir . '/Subs-UltimatePortal.php');	

	if (!empty($_POST['save']))
	{
		checkSession('post');
		//Content and File
		$file = trim($_POST['file']);
		$content = trim($_POST['content']);
		//Create Edit Lang File
		CreateSpecificLang($file, $content);		
		redirectexit('action=admin;area=preferences;sa=lang-maintenance;sesc=' . $context['session_id']);		
	}	

	if (!empty($_POST['duplicate']))
	{
		checkSession('post');		
		if (empty($_POST['new_file']))
			fatal_lang_error('ultport_error_no_name',false);	
		//Content and File
		$file = trim($_POST['file']);		
		//Load the original lang
		LoadSpecificLang($file);		
		$new_file_name = $_POST['new_file'] .'.php';
		//Create Edit Lang File
		CreateSpecificLang($new_file_name, $context['content']);
		redirectexit('action=admin;area=preferences;sa=lang-maintenance;sesc=' . $context['session_id']);		
	}	

	if(!empty($_POST['editing']))
		checkSession('post');

	//If not select the lang file, then redirect the selec lang form
	if (empty($_POST['file']))
		redirectexit('action=admin;area=preferences;sa=lang-maintenance');	
		
	$context['file'] = stripslashes($_POST['file']);	
	$this_file = $context['file'];
	
	//Load Specific Lang - from Subs-UltimatePortal.php
	LoadSpecificLang($this_file);

	$context['sub_template'] = 'preferences_lang_edit';
	$context['page_title'] = $txt['ultport_admin_lang_maintenance_edit'] . ' - ' . $txt['ultport_preferences_title'];	
}

//Load Permissions Settings
function ShowPreferencesPermissionsSettings()
{
	global $context, $txt, $smcFunc, $sourcedir;

	require_once($sourcedir . '/Subs-UltimatePortal.php');	
	
	$context['view-perms'] = 0;
	$group_selected = '';

	//Load Permissions - Source/Subs-UltimatePortal.php
	LoadUPModulesPermissions();

	//View Perms?
	if (!empty($_POST['view-perms']))
	{
		checkSession('post');		
		$context['view-perms'] = 1;
		$group_selected = (int) $_POST['group'];
		$context['group-selected'] = $group_selected;
		
		$permissions = array();
		if(!empty($group_selected))
		{
			$result = $smcFunc['db_query']('',"
				SELECT permission, value
				FROM {db_prefix}up_groups_perms
				WHERE ID_GROUP = {int:id_group}",
				array(
					'id_group' => $group_selected,
				)
			);
			while ($row = $smcFunc['db_fetch_assoc']($result))			
				$context[$row['permission']]['value'] = $row['value'];
				
			$smcFunc['db_free_result']($result);
		}		
	}	
	if (!empty($_POST['save']))
	{
		checkSession('post');		
		$id_group = (int) $_POST['group_selected'];
		$smcFunc['db_query']('',"
			DELETE FROM {db_prefix}up_groups_perms
			WHERE ID_GROUP = {int:id_group}",
			array(
				'id_group' => $id_group,
			)
		);		
		foreach ($context['permissions'] as $permissions)
		{
			$smcFunc['db_query']('',"
				INSERT IGNORE INTO {db_prefix}up_groups_perms(ID_GROUP, permission, value)
				VALUES({int:id_group}, {string:perm_name}, ". (!empty($_POST[$permissions['name']]) ? '{int:active}' : '{int:disabled}' ) .")",
				array(
					'id_group' => $id_group,
					'perm_name' => $permissions['name'],
					'active' => 1,
					'disabled' => 0,
				)
			);				
		}
		redirectexit('action=admin;area=preferences;sa=permissions-settings;sesc=' . $context['session_id']);
	}
	//Load the MemberGroups
	LoadMemberGroups($group_selected);
	
	$context['sub_template'] = 'preferences_permissions_settings';
	$context['page_title'] = $txt['ultport_admin_permissions_settings_title'] . ' - ' . $txt['ultport_preferences_title'];
}

//Portal Menu Settings
function ShowPortalMenuSettings()
{
	global $context, $txt, $sourcedir;

	require_once($sourcedir . '/Subs-UltimatePortal.php');	

	//Load the Main links from BD
	LoadMainLinks();
	$context['sub_template'] = 'preferences_main_links';
	$context['page_title'] = $txt['ultport_admin_portal_menu_title'] . ' - ' . $txt['ultport_preferences_title'];
}

//Save Main Links
function SaveMainLinks()
{
	global $context, $smcFunc, $sourcedir;
	require_once($sourcedir . '/Subs-UltimatePortal.php');	
	
	if (empty($_POST['save-menu']))
		redirectexit('action=admin;area=preferences;sa=portal-menu');	

	checkSession('post');
	$myquery = $smcFunc['db_query']('',"
				SELECT id 
				FROM {db_prefix}ultimate_portal_main_links"
	);
	while($row = $smcFunc['db_fetch_assoc']($myquery))	
		$inforows[$row['id']] = $row['id'];
	
	foreach ($inforows as $infor => $id)
	{
		$position_form = $id."_position";
		$active_form = $id."_active";
		
		$position_form = !empty($_POST[$position_form]) ? (int) $_POST[$position_form] : 0;
		$active_form = !empty($_POST[$active_form]) ? 1 : 0;			
		
		$smcFunc['db_query']('',"
			UPDATE {db_prefix}ultimate_portal_main_links
			SET position = {int:position}, 
				active = {int:active}
			WHERE id = {int:id}",
			array(
				'position' => $position_form,
				'active' => $active_form,
				'id' => $id,
			)
		);
	}					
	$smcFunc['db_free_result']($myquery);
	redirectexit('action=admin;area=preferences;sa=portal-menu;sesc=' . $context['session_id']);	
}

//Add Main Links
function AddMainLinks()
{
	global $context, $smcFunc, $sourcedir;
	require_once($sourcedir . '/Subs-UltimatePortal.php');	
	
	if (!isset($_POST['add-menu']))
		redirectexit('action=admin;area=preferences;sa=portal-menu');
	checkSession('post');
	$icon = !empty($_POST['icon']) ? (string)$smcFunc['htmlspecialchars']($_POST['icon'], ENT_QUOTES) : '';
	$title = !empty($_POST['title']) ? (string)$smcFunc['htmlspecialchars']($_POST['title'], ENT_QUOTES) : '';
	$url = !empty($_POST['url']) ? (string)$smcFunc['htmlspecialchars']($_POST['url'], ENT_QUOTES) : '';
	$position = !empty($_POST['position']) ? (int)$_POST['position'] : '';
	$active = !empty($_POST['active']) ? (int)$_POST['active'] : '0';
		
	$result = $smcFunc['db_query']('',"
		INSERT INTO {db_prefix}ultimate_portal_main_links(id, icon, title, url, position, active)
		VALUES({int:id}, {string:icon}, {string:title}, {string:url}, {int:position}, {int:active})",
		array(
			'id' => 0,
			'icon' => $icon,
			'title' => $title,
			'url' => $url,
			'position' => $position,
			'active' => $active,			
		)
	);
	redirectexit('action=admin;area=preferences;sa=portal-menu;sesc=' . $context['session_id']);	
}

//Edit Main Links
function EditMainLinks()
{
	global $context, $txt, $smcFunc, $sourcedir;
			
	require_once($sourcedir . '/Subs-UltimatePortal.php');	
	
	if (empty($_REQUEST['id']))
		redirectexit('action=admin;area=preferences;sa=portal-menu');	

	if (!empty($_REQUEST['id']))
	{
		$id = (int) $_REQUEST['id'];
		
		$myquery = $smcFunc['db_query']('',"
			SELECT * 
			FROM {db_prefix}ultimate_portal_main_links
			WHERE id = {int:id}",
			array(
				'id' => $id,			
			)
		);
		while($row = $smcFunc['db_fetch_assoc']($myquery))
		{
			$edit_main_link = &$context['edit-main-links'][];
			$edit_main_link['id'] = $row['id'];
			$edit_main_link['icon'] = $row['icon'];			
			$edit_main_link['title'] = $row['title'];
			$edit_main_link['url'] = $row['url'];
			$edit_main_link['position'] = $row['position'];
			$edit_main_link['active'] = $row['active'];	
		}
		$smcFunc['db_free_result']($myquery);
		$context['sub_template'] = 'preferences_edit_main_links';
		$context['page_title'] = $txt['ultport_admin_portal_menu_title'] . ' - ' . $txt['ultport_preferences_title'];		
	}				
	
	if (isset($_POST['save']))
	{	
		checkSession('post');	
		$id = !empty($_POST['id']) ? (int)$_POST['id'] : '';
		$icon = !empty($_POST['icon']) ? (string)$smcFunc['htmlspecialchars']($_POST['icon'], ENT_QUOTES) : '';
		$title = !empty($_POST['title']) ? (string)$smcFunc['htmlspecialchars']($_POST['title'], ENT_QUOTES) : '';
		$url = !empty($_POST['url']) ? (string)$smcFunc['htmlspecialchars']($_POST['url'], ENT_QUOTES) : '';
		$position = !empty($_POST['position']) ? (int)$_POST['position'] : '';
		$active = !empty($_POST['active']) ? 1 : 0;
		
		if(!empty($id))
		{
			$smcFunc['db_query']('',"
				UPDATE {db_prefix}ultimate_portal_main_links
				SET icon = {string:icon},
					title = {string:title},
					url = {string:url},
					position = {int:position}, 
					active = {int:active}
				WHERE id={int:id}
				LIMIT 1",
				array(
					'id' => $id,
					'icon' => $icon,
					'title' => $title,
					'url' => $url,
					'position' => $position,
					'active' => $active,			
				)
			);
		}		
		redirectexit('action=admin;area=preferences;sa=portal-menu;sesc=' . $context['session_id']);
	}	
}
function DeleteMainLinks()
{
	global $context, $smcFunc, $sourcedir;
	require_once($sourcedir . '/Subs-UltimatePortal.php');	
	
	if (empty($_REQUEST['id']))
		redirectexit('action=admin;area=preferences;sa=portal-menu');	
		
	$id = (int) $_REQUEST['id'];
	
	$myquery = $smcFunc['db_query']('',"
		DELETE  
		FROM {db_prefix}ultimate_portal_main_links
		WHERE id = {int:id}",
		array(
			'id' => $id,
		)		
	);				
	redirectexit('action=admin;area=preferences;sa=portal-menu;sesc=' . $context['session_id']);
}
//Settings SEO
function ShowSEO()
{
	global $context, $txt, $sourcedir, $boarddir, $smcFunc, $ultimateportalSettings;
	
	require_once($sourcedir . '/Subs-UltimatePortal.php');	

	//Save Robot
	if (!empty($_POST['save_robot']))
	{
		checkSession('post');		
		if (!empty($_POST['robots_add']))
		{
			$robots_txt = stripslashes($_POST['robots_add']);
			$filename = $boarddir . '/robots.txt';
			@chmod($filename, 0644);
			if (!$handle = fopen($filename, 'w'))
				fatal_error($txt['ultport_error_fopen_error'] . $filename   . '.',false);		
			// Write the headers to our opened file.
			if (!fwrite($handle, $robots_txt))			
				fatal_error($txt['ultport_error_fopen_error'] . $filename   . '.',false);		
			fclose($handle);
		}	
	}
	//Save Config General
	if (!empty($_POST['save_seo_config']))
	{
		checkSession('post');		
		//save the ultimate portal settings section seo
		saveUltimatePortalSettings("config_seo");
		redirectexit('action=admin;area=preferences;sa=seo;sesc=' . $context['session_id']);
	}
	//Save Google Verification Code
	if (!empty($_POST['save_seo_google_verification_code']))
	{
		checkSession('post');		
		$verification = $smcFunc['db_escape_string']($_POST['seo_google_verification_code']);
		$extension_code = strtolower(substr(strrchr($verification, '.'), 1));		
		if (!empty($extension_code))
			fatal_error($txt['seo_google_verification_code_error'], false);											
			
		//save the ultimate portal settings section seo
		$configUltimatePortalVar['seo_google_verification_code'] = empty($ultimateportalSettings['seo_google_verification_code']) ? $verification : $ultimateportalSettings['seo_google_verification_code'].','.$verification;
		updateUltimatePortalSettings($configUltimatePortalVar, 'config_seo');
		if (!empty($verification))
		{			
			$filename = $boarddir . '/'. $verification . '.html';
			$content = 'google-site-verification: '. $verification . '.html';
			if (!$handle = fopen($filename, 'a'))
				fatal_error($txt['ultport_error_fopen_error'] . $filename,false);
			fwrite($handle, $content);	
			fclose($handle);
		}			
		redirectexit('action=admin;area=preferences;sa=seo;sesc=' . $context['session_id']);
	}
	//Delete google Verification Code?
	if(!empty($_REQUEST['file']))
	{
		$verification = $smcFunc['db_escape_string']($_REQUEST['file']);
		unlink($boarddir . '/'. $verification . '.html');
		$verifications_codes = explode(',', $ultimateportalSettings['seo_google_verification_code']);
		$count = count($verifications_codes);
		if($count > 1)
		{
			for($i = 0; $i <= $count; $i++)
			{
				if(!empty($verifications_codes[$i]))
				{
					//save the ultimate portal settings section seo
					if($verifications_codes[$i] == $verification)
						$position = $i;
				}
			}
		}
		else
			$configUltimatePortalVar['seo_google_verification_code'] = '';
		
		//Not first?
		if(!empty($position) && $position >= 1 && (($position != count($verifications_codes)-1) || ($position == count($verifications_codes)-1)))		
			$configUltimatePortalVar['seo_google_verification_code'] = str_replace(','.$verification,'', $ultimateportalSettings['seo_google_verification_code']);
			
		//Okay, is first :P
		if($count > 1 && $position == 0)		
			$configUltimatePortalVar['seo_google_verification_code'] = str_replace($verification.',','', $ultimateportalSettings['seo_google_verification_code']);
			
		updateUltimatePortalSettings($configUltimatePortalVar, 'config_seo');						
		redirectexit('action=admin;area=preferences;sa=seo;sesc=' . $context['session_id']);
	}
	if(file_exists($boarddir . '/robots.txt'))	
		$context['robots_txt'] = file_get_contents($boarddir . '/robots.txt');	

	$context['sub_template'] = 'preferences_seo';
	$context['page_title'] = $txt['ultport_seo_title'] . ' - ' . $txt['ultport_preferences_title'];	
}

function ShowMultiblock()
{
	global $context, $txt;

	if (!allowedTo('ultimate_portal_cp'))
		isAllowedTo('ultimate_portal_cp');
		
	loadTemplate('UltimatePortalCP');
	
	//Load subactions for the ultimate portal preferences
	$subActions = array(
		'main' => 'ShowMBMain',
		'add' => 'ShowMBAdd',
		'edit' => 'ShowMBEdit',
		'delete' => 'ShowMBDelete',
	);
	
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'main';
	$context['sub_action'] = $_REQUEST['sa'];
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['ultport_mb_title'],
		'description' =>  $txt['ultport_mb_main_descrip'],
		'tabs' => array(
			'main' => array(
				'description' => $txt['ultport_mb_main_descrip'],
			),
			'add' => array(
				'description' => $txt['ultport_mb_main_descrip'],
			),
		),
	);
	$subActions[$_REQUEST['sa']]();
}

function ShowMBMain()
{
	global $context, $txt, $sourcedir;
	require_once($sourcedir . '/Subs-UltimatePortal.php');

	//Load Multiblocks
	MultiBlocksLoads();	
	$context['sub_template'] = 'mb_main';
	$context['page_title'] = $txt['ultport_mb_title'] . ' - ' . $txt['ultport_mb_main'];
}

function ShowMBAdd()
{
	global $context, $txt, $sourcedir, $smcFunc;
	require_once($sourcedir . '/Subs-UltimatePortal-Init-Blocks.php');	
		
	if(!empty($_POST['next']))
	{
		checkSession('post');		
		$step = $_POST['step'];
		$context['title'] = !empty($_POST['title']) ? $_POST['title'] : '';
		$context['position'] = !empty($_POST['position']) ? $_POST['position'] : '';
		$context['enable'] = isset($_POST['enable']) ? 1 : 0;
		$context['mbk_title'] = isset($_POST['mbk_title']) ? 'on' : '';
		$context['mbk_collapse'] = isset($_POST['mbk_collapse']) ? 'on' : '';
		$context['mbk_style'] = isset($_POST['mbk_style']) ? 'on' : '';
		
		if (isset($_POST['block']))
		{
			foreach ($_POST['block'] as $i => $v)
				 if (!is_numeric($_POST['block'][$i])) 
				 	unset($_POST['block'][$i]);	
			$context['id_blocks'] = implode(',', $_POST['block']);
		}
		$context['design'] = !empty($_POST['design']) ? $_POST['design'] : '';
	}	
	if (!empty($step))	
		$context['sub_template'] = 'mb_add_'.$step;
	else
		$context['sub_template'] = 'mb_add';
	
	if (!empty($_POST['save']))
	{
		checkSession('post');			
		$title = !empty($_POST['title']) ? $_POST['title'] : '';
		$position = !empty($_POST['position']) ? $_POST['position'] : '';
		$id_blocks = !empty($_POST['blocks']) ? $_POST['blocks'] : '';
		$design = !empty($_POST['design']) ? $_POST['design'] : '';		
		$enable = !empty($_POST['enable']) ? 1 : 0;
		$mbk_title = !empty($_POST['mbk_title']) ? 'on' : '';
		$mbk_collapse = !empty($_POST['mbk_collapse']) ? 'on' : '';
		$mbk_style = !empty($_POST['mbk_style']) ? 'on' : '';
		
		//Create New MultiBlock
		$smcFunc['db_query']('',"
			INSERT INTO {db_prefix}up_multiblock(id, title, blocks, position, design, mbk_title, mbk_collapse, mbk_style, enable)
			VALUES(0, {string:title}, {string:id_blocks}, {string:position}, {string:design}, {string:mbk_title}, {string:mbk_collapse}, {string:mbk_style}, {int:enable})",
			array(
				'title' => $title,
				'id_blocks' => $id_blocks,
				'position' => $position,
				'design' => $design,
				'mbk_title' => $mbk_title,
				'mbk_collapse' => $mbk_collapse,
				'mbk_style' => $mbk_style,
				'enable' => $enable,
			)
		);
		
		//Updates the blocks
		$id_block = explode(',', $id_blocks);
		foreach($id_block as $bk)
		{
			if(!empty($bk))
			{
				$mbk_view_pos = !empty($_POST['mbk_view_'.$bk]) ? (string) $_POST['mbk_view_'.$bk] : '';				
				if(!empty($mbk_view_pos))
				{
					$smcFunc['db_query']('',"
						UPDATE {db_prefix}ultimate_portal_blocks
						SET position = {string:position}, 
							mbk_view = {string:mbk_view}
						WHERE id = {int:id_block}",
						array(
							'position' => $position,
							'mbk_view' => $mbk_view_pos,
							'id_block' => $bk,
						)
					);
				}
			}	
		}
		redirectexit('action=admin;area=multiblock;sa=main;'. $context['session_var'] .'=' . $context['session_id']);
	}
	
	//Loads only right, left, and center blocks
	LoadsBlocksForMultiBlock(true);	
	$context['page_title'] = $txt['ultport_mb_title'] . ' - ' . $txt['ultport_mb_add'];
}

function ShowMBEdit()
{
	global $context, $txt, $sourcedir, $smcFunc;	
	require_once($sourcedir . '/Subs-UltimatePortal-Init-Blocks.php');	

	if(empty($_GET['id']))
		redirectexit('action=admin;area=multiblock;sa=main;'. $context['session_var'] .'=' . $context['session_id']);
	
	//Catch id
	$context['idmbk'] = mysql_real_escape_string($_GET['id']);
	//Load Specific
	SpecificMultiBlocks($context['idmbk']);
	//Loads all blocks
	LoadsBlocksForMultiBlock(false);
	
	if(!empty($_POST['next']))
	{
		checkSession('post');		
		$step = $_POST['step'];
		$context['title'] = !empty($_POST['title']) ? $_POST['title'] : '';
		$context['position'] = !empty($_POST['position']) ? $_POST['position'] : '';
		$context['enable'] = !empty($_POST['enable']) ? 1 : 0;
		$context['mbk_title'] = !empty($_POST['mbk_title']) ? 'on' : '';
		$context['mbk_collapse'] = !empty($_POST['mbk_collapse']) ? 'on' : '';
		$context['mbk_style'] = !empty($_POST['mbk_style']) ? 'on' : '';
		
		if (!empty($_POST['block']))
		{
			foreach ($_POST['block'] as $i => $v)
				 if (!is_numeric($_POST['block'][$i])) 
				 	unset($_POST['block'][$i]);	
			$context['id_blocks'] = implode(',', $_POST['block']);
		}
		$context['design'] = !empty($_POST['design']) ? $_POST['design'] : '';
	}
	
	if (!empty($step))	
		$context['sub_template'] = 'mb_edit_'.$step;
	else
		$context['sub_template'] = 'mb_edit';
	if (!empty($_POST['save']))
	{
		checkSession('post');			
		$title = !empty($_POST['title']) ? (string)$_POST['title'] : '';
		$position = !empty($_POST['position']) ? (string)$_POST['position'] : '';
		$id_blocks = !empty($_POST['blocks']) ? (string)$_POST['blocks'] : '';
		$design = !empty($_POST['design']) ?(string)$_POST['design'] : '';		
		$enable = !empty($_POST['enable']) ? 1 : 0;
		$mbk_title = !empty($_POST['mbk_title']) ? 'on' : '';
		$mbk_collapse = !empty($_POST['mbk_collapse']) ? 'on' : '';
		$mbk_style = !empty($_POST['mbk_style']) ? 'on' : '';		
		
		//Create New MultiBlock
		$smcFunc['db_query']('',"
			UPDATE {db_prefix}up_multiblock
			SET title = {string:title}, 
				blocks = {string:blocks}, 
				position = {string:position}, 
				design = {string:design}, 
				mbk_title = {string:mbk_title}, 
				mbk_collapse =  {string:mbk_collapse}, 
				mbk_style =  {string:mbk_style}, 
				enable = {int:enable}
			WHERE id = {int:id}
			LIMIT 1",
			array(
				'title' => $title,
				'blocks' => $id_blocks,
				'position' => $position,
				'design' => $design,
				'mbk_title' => $mbk_title,
				'mbk_collapse' => $mbk_collapse,
				'mbk_style' => $mbk_style,
				'enable' => $enable,
				'id' => $context['idmbk'],
			)			
		);
		
		//Updates the blocks
		$id_block = explode(',', $id_blocks);
		foreach($id_block as $bk)
		{
			if(!empty($bk))
			{
				$mbk_view_pos = !empty($_POST['mbk_view_'.$bk]) ? (string) $_POST['mbk_view_'.$bk] : '';
				if(!empty($mbk_view_pos))
				{
					$smcFunc['db_query']('',"
						UPDATE {db_prefix}ultimate_portal_blocks
						SET position = {string:position}, 
							mbk_view = {string:mbk_view}
						WHERE id = {int:id_block}
						LIMIT 1",
						array(
							'position' => $position,
							'mbk_view' => $mbk_view_pos,
							'id_block' => $bk,
						)
					);
				}
			}
		}		
		//Unchecked block
		$old_blocks = explode(',',$context['multiblocks'][$context['idmbk']]['blocks']);
		foreach($old_blocks as $obk)
		{
			if(!in_array($obk, $id_block))
			{
				$smcFunc['db_query']('',"
					UPDATE {db_prefix}ultimate_portal_blocks
					SET position = {string:position}, 
						mbk_view = {string:mbk_view},
						active = {string:active}
					WHERE id = {int:id_block}
					LIMIT 1",
					array(
						'id_block' => $obk,
						'mbk_view' => '',
						'active' => '',
						'position' => 'left',
					)
				);				
			}
		}		
		redirectexit('action=admin;area=multiblock;sa=main;'. $context['session_var'] .'=' . $context['session_id']);
	}
	$context['page_title'] = $txt['ultport_mb_title'] . ' - ' . $txt['ultport_mb_edit'] .' - '.$context['multiblocks'][$context['idmbk']]['title'];
}

function ShowMBDelete()
{
	global $context, $smcFunc,$sourcedir;
	require_once($sourcedir . '/Subs-UltimatePortal.php');	
	
	if (!isset($_REQUEST['id']))
		redirectexit('action=admin;area=multiblock;sa=main;'. $context['session_var'] .'=' . $context['session_id']);

	$id = (int) $_REQUEST['id'];

	//Load Specific
	SpecificMultiBlocks($id);

	$id_blocks = explode(',',$context['multiblocks'][$id]['blocks']);
	foreach($id_blocks as $bk)
	{
		if(!empty($bk))
		{
			$smcFunc['db_query']('',"
				UPDATE {db_prefix}ultimate_portal_blocks
				SET position = {string:position}, 
					mbk_view = {string:mbk_view},
					active = {string:active}
				WHERE id = {int:id}
				LIMIT 1",
				array(
					'id' => $bk,
					'mbk_view' => '',
					'active' => '',
					'position' => 'left',
				)
			);
		}			
	}	
	//Now Delete	
	$myquery = $smcFunc['db_query']('',"
		DELETE FROM {db_prefix}up_multiblock
		WHERE id = {int:id}
		LIMIT 1",
		array(
			'id' => $id,
		)
	);				
	redirectexit('action=admin;area=multiblock;sa=main;'. $context['session_var'] .'=' . $context['session_id']);
}