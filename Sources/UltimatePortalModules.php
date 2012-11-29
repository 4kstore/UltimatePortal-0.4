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
	
//Modules - Area News
function ShowNews()
{
	global $context, $txt;

	//load template
	loadTemplate('UltimatePortalModules');
	
	$subActions = array(
		'ns-main' => 'ShowNewsMain',
		'section' => 'ShowNewsSection',
		'add-section' => 'ShowAddSection',
		'edit-section' => 'EditSection',
		'delete-section' => 'DeleteSection',
		'admin-news' => 'ShowAdminNews',		
		'add-news' => 'ShowAddNews',
		'edit-news' => 'EditUPNews',						
		'delete-news' => 'DeleteNews',								
		'announcements' => 'ShowAnnouncements'
	);	
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'ns-main';
	$context['sub_action'] = $_REQUEST['sa'];
	$context[$context['adminportal_menu_name']]['tab_data'] = array(
		'title' => $txt['ultport_admin_news_title'] . ' - ' . $txt['ultport_admin_module_title2'],
		'description' => $txt['ultport_admin_news_descrip'],
		'tabs' => array(
			'ns-main' => array(
				'description' => $txt['ultport_admin_news_descrip'],
			),
			'section' => array(
				'description' => $txt['ultport_admin_news_section_descrip'],
			),
			'admin-news' => array(
				'description' => $txt['ultport_admin_news_descrip2'],
			),
			'announcements' => array(
				'description' => $txt['ultport_admin_announcements_descrip'],
			),
		),
	);
	$subActions[$_REQUEST['sa']]();
}

//Modules - Area News - Sect: Gral Settings
function ShowNewsMain()
{
	global $context, $txt;
	
	if(empty($_POST['save']))
		checkSession('get');	

	if (!empty($_POST['save']))
	{
		checkSession('post');
		$_POST['up_news_limit'] = (!empty($_POST['up_news_limit']) && ($_POST['up_news_limit'] > 0)) ? (int)$_POST['up_news_limit'] : 10;	
		saveUltimatePortalSettings("config_up_news");
	}
	$context['sub_template'] = 'news_main';
	$context['page_title'] = $txt['ultport_admin_news_title'] . ' - ' . $txt['ultport_admin_news_main'] . ' - ' . $txt['ultport_admin_module_title2'];
}
//Modules - Area News - Sect: Announcements
function ShowAnnouncements()
{
	global $context, $txt, $sourcedir, $ultimateportalSettings;

	if(!isset($_POST['save']))
		checkSession('get');
	if (isset($_POST['save']))
	{
		checkSession('post');
		$configUltimatePortalVar['up_news_global_announcement'] = censorText($_POST['up_news_global_announcement']);
		updateUltimatePortalSettings($configUltimatePortalVar, 'config_up_news');		
	}
	// Needed for the editor and message icons.
	require_once($sourcedir . '/Subs-Editor.php');
	// Now create the editor.
	$editorOptions = array(
		'id' => 'up_news_global_announcement',
		'value' => $ultimateportalSettings['up_news_global_announcement'],
		'form' => 'newsform',		
	);
	create_control_richedit($editorOptions);
	// Store the ID.
	$context['post_box_name'] = $editorOptions['id'];
	$context['sub_template'] = 'announcement';
	$context['page_title'] = $txt['ultport_admin_news_title'] . ' - ' . $txt['ultport_admin_announcements_title'] . ' - ' . $txt['ultport_admin_module_title2'];
}

//Modules - Area News - Sect: Config Add - Delete - News Section 
function ShowNewsSection()
{
	global $db_prefix, $context, $scripturl, $txt;
	checkSession('get');	
	//Load the News Section - Source/Subs-UltimatePortal.php
	LoadNewsSection();
	$context['sub_template'] = 'news_section';
	$context['page_title'] = $txt['ultport_admin_news_title'] . ' - ' . $txt['ultport_admin_news_section_title'] . ' - ' . $txt['ultport_admin_module_title2'];
}
//Modules - Area News - Sect: Add News Section 
function ShowAddSection()
{
	global $context, $txt, $settings, $smcFunc;

	if(!isset($_POST['save']))
		checkSession('get');	

	if (isset($_POST['save']))
	{
		if (empty($_POST['title']))
			fatal_lang_error('ultport_error_no_add_news_section_title',false);
		
		$icon = !empty($_POST['icon']) ? (string) $_POST['icon'] : $settings['default_images_url'].'/ultimate-portal/news-icon.png';
		$title = (string) $smcFunc['db_escape_string']($_POST['title']);
		$position = !empty($_POST['position']) ? (int) $_POST['position'] : '';		
		$smcFunc['db_query']('',"
			INSERT INTO {db_prefix}up_news_sections (title, icon, position) 
			VALUES ({string:title}, {string:icon}, {int:position})",
			array(
				'title' => $title,
				'icon' => $icon,
				'position' => $position,
			)
		);
		redirectexit('action=adminportal;area=up-news;sa=section;'. $context['session_var'] .'=' . $context['session_id']);		
	}
	//only load the $context['last_position']
	LoadNewsSection();
	$context['sub_template'] = 'add_news_section';
	$context['page_title'] = $txt['ultport_admin_news_title'] . ' - ' . $txt['ultport_admin_add_sect_title'] . ' - ' . $txt['ultport_admin_module_title2'];
}
//Modules - Area News - Sect: Edit News Section 
function EditSection()
{
	global $context, $txt, $settings, $smcFunc;

	if(empty($_POST['save']))
		checkSession('get');	

	if (!empty($_POST['save']))
	{
		checkSession('post');
		if (empty($_POST['title']))
			fatal_lang_error('ultport_error_no_add_news_section_title',false);

		$id = !empty($_POST['id']) ? (int)$_POST['id'] : '';
		$icon = !empty($_POST['icon']) ? (string)$smcFunc['db_escape_string']($_POST['icon']) : $settings['default_images_url'].'/ultimate-portal/news-icon.png';
		$title = (string) $smcFunc['db_escape_string']($_POST['title']);
		$position = !empty($_POST['position']) ? (int) $_POST['position'] : '';
		
		if (!empty($id))
		{
			//Now update the new section in the smf_up_news_sections 
			$smcFunc['db_query']('',"UPDATE {db_prefix}up_news_sections 
				SET title = {string:title}, 
					icon = {string:icon}, 
					position = {int:position}
				WHERE id = {int:id}
				LIMIT 1",
				array(
					'title' => $title,
					'icon' => $icon,
					'position' => $position,
					'id' => $id,
				)
			);
		}
		redirectexit('action=adminportal;area=up-news;sa=section;'. $context['session_var'] .'=' . $context['session_id']);		
	}
	$id = !empty($_REQUEST['id']) ? (int)$smcFunc['db_escape_string']($_REQUEST['id']) : '';	
	if(!empty($id))
	{
		$myquery = $smcFunc['db_query']('',"
			SELECT id, title, icon, position 
			FROM {db_prefix}up_news_sections 
			WHERE id = {int:id}",
			array(
				'id' => $id,
			)
		);
		while($row = $smcFunc['db_fetch_assoc']($myquery))
		{
			$context['id'] = $row['id'];
			$context['title'] = $row['title'];
			$context['icon'] = $row['icon'];
			$context['position'] = $row['position'];				
		}		
	}
	$context['sub_template'] = 'edit_news_section';
	$context['page_title'] = $txt['ultport_admin_news_title'] . ' - ' . $txt['ultport_admin_add_sect_title'] . ' - ' . $txt['ultport_admin_module_title2'];
}
//Modules - Area News - Sect: Delete News Section 
function DeleteSection()
{
	global $context, $smcFunc;
	checkSession('get');
	if (empty($_REQUEST['id']))
		fatal_lang_error('ultport_error_no_delete_section',false);

	$id = (int)$smcFunc['db_escape_string']($_REQUEST['id']);
	
	//Now is delete the section and the news for this section	
	$smcFunc['db_query']('',"
		DELETE FROM {db_prefix}up_news_sections 
		WHERE id = {int:id}
		LIMIT 1",
		array(
			'id' => $id,
		)
	);
	$smcFunc['db_query']('',"
		DELETE FROM {db_prefix}up_news 
		WHERE id_category = {int:id}",
		array(
			'id' => $id,
		)
	);
	redirectexit('action=adminportal;area=up-news;sa=section;'. $context['session_var'] .'=' . $context['session_id']);
}

//Modules - Area News - Sect: Config Add - Delete - News  
function ShowAdminNews()
{
	global $context, $txt;
	checkSession('get');
	//Load News - Source/Subs-UltimatePortal.php
	LoadNews();
	$context['sub_template'] = 'admin_news';
	$context['page_title'] = $txt['ultport_admin_news_title'] . ' - ' . $txt['ultport_admin_admin_news_title'] . ' - ' . $txt['ultport_admin_module_title2'];
}
//Modules - Area News - Sect: Add News  
function ShowAddNews()
{
	global $context, $txt, $smcFunc;

	if(empty($_POST['save']))
		checkSession('get');
	if (!empty($_POST['save']))
	{
		checkSession('post');
		if (empty($_POST['title']))
			fatal_lang_error('ultport_error_no_add_news_title',false);

		$title = (string)$smcFunc['db_escape_string']($_POST['title']);
		$id_cat = !empty($_POST['id_cat']) ? (int)$_POST['id_cat'] : '';
		$body = !empty($_POST['elm1']) ? (string) up_convert_savedbadmin($_POST['elm1']) : '';
		$id_member = !empty($_POST['id_member']) ? (int)$_POST['id_member'] : '';
		$username = !empty($_POST['username']) ? (string)$_POST['username'] : '';
		$date = time();		
		if(!empty($id_cat) && !empty($body))
		{
			//Now insert the NEWS in the smf_up_news
			$smcFunc['db_query']('',"
				INSERT INTO {db_prefix}up_news(id_category, id_member, title, username, body, date) 
				VALUES({int:id_cat}, {int:id_cat}, {string:title}, {string:username}, {string:body}, {string:date})",
				array(
					'id_cat' => $id_cat,
					'id_member' => $id_member,
					'title' => $title,
					'username' => $username,
					'body' => $body,
					'date' => $date,
				)
			);		
			redirectexit('action=adminportal;area=up-news;sa=admin-news;'. $context['session_var'] .'=' . $context['session_id']);
		}		
	}
	//Load the sections
	$context['section'] = '';
	$myquery = $smcFunc['db_query']('',"
		SELECT id, title 
		FROM {db_prefix}up_news_sections 
		ORDER BY id ASC");
	while($row = $smcFunc['db_fetch_assoc']($myquery)) 
		$context['section'] .= '<option value="'. $row['id'] .'">'. $row['title'] .'</option>';	
		
	//Load the html headers and load the Editor
	context_html_headers("elm1");
	
	$context['sub_template'] = 'add_news';
	$context['page_title'] = $txt['ultport_admin_news_title'] . ' - ' . $txt['ultport_admin_add_news_title2'] . ' - ' . $txt['ultport_admin_module_title2'];
}

//Modules - Area News - Sect: Edit News  
function EditUPNews()
{
	global $context, $txt, $smcFunc;

	if(empty($_POST['save']))
		checkSession('get');	

	//Save 
	if (!empty($_POST['save']))
	{
		checkSession('post');
		if (empty($_POST['title']))
			fatal_lang_error('ultport_error_no_add_news_title',false);

		$id = !empty($_POST['id']) ? (int)$_POST['id'] : '';
		$title = (string)$smcFunc['db_escape_string']($_POST['title']);
		$id_cat = !empty($_POST['id_cat']) ? (int)$_POST['id_cat'] : '';
		$body = !empty($_POST['elm1']) ? (string) up_convert_savedbadmin($_POST['elm1']) : '';
		$id_member_updated = !empty($_POST['id_member_updated']) ? (int)$_POST['id_member_updated'] : '';
		$username_updated = !empty($_POST['username_updated']) ? (string)$_POST['username_updated'] : '';
		$date_updated = time();
		
		if(!empty($id_cat) && !empty($body) && !empty($id))
		{
			//Now insert the NEWS in the smf_up_news
			$smcFunc['db_query']('',"
				UPDATE {db_prefix}up_news
				SET id_category = {int:id_cat}, 
					title = {string:title}, 
					body = {string:body}, 
					id_member_updated = {int:id_member_updated},
					username_updated ={string:username_updated},										
					date_updated = {string:date_updated}
				WHERE id = {int:id}
				LIMIT 1",
				array(
					'id_cat' => $id_cat,
					'id_member_updated' => $id_member_updated,
					'title' => $title,
					'username_updated' => $username_updated,
					'body' => $body,
					'date_updated' => $date_updated,
					'id' => $id,
				)
			);
		}		
		//redirect the News Admin Section
		redirectexit('action=adminportal;area=up-news;sa=admin-news;'. $context['session_var'] .'=' . $context['session_id']);		
	}
	//Load the News
	$id = !empty($_REQUEST['id']) ? (int)$smcFunc['db_escape_string']($_REQUEST['id']) : '';	
	$myquery = $smcFunc['db_query']('',"
		SELECT * 
		FROM {db_prefix}up_news 
		WHERE id = {int:id}
		LIMIT 1",
		array(
			'id' => $id,
		)
	);
	while($row = $smcFunc['db_fetch_assoc']($myquery))
	{
		$context['id'] = $row['id'];
		$context['id_category'] = $row['id_category'];
		$context['id_member'] = $row['id_member'];
		$context['title'] = stripslashes($row['title']);
		$context['username'] = $row['username'];
		$context['body'] = stripslashes($row['body']);		
		$context['date'] = $row['date'];		
	}

	//Load the sections
	$context['section-edit'] = '';
	$myquery2 = $smcFunc['db_query']('',"
		SELECT id, title 
		FROM {db_prefix}up_news_sections 
		ORDER BY id ASC"
	);
	while($row2 = $smcFunc['db_fetch_assoc']($myquery2))
	{
		$active = '';				
		if ($context['id_category'] == $row2['id'])		
			$active = 'selected="selected"';			
		$context['section-edit'] .= '<option '. $active .' value="'. $row2['id'] .'">'. $row2['title'] .'</option>';
	}
	context_html_headers();	
	$context['sub_template'] = 'edit_news';
	$context['page_title'] = $txt['ultport_admin_news_title'] . ' - ' . $txt['ultport_admin_edit_news_title'] . ' - ' . $txt['ultport_admin_module_title2'];
}

//Modules - Area News - Sect: Delete News 
function DeleteNews()
{
	global $context, $smcFunc;
	checkSession('get');
	if (empty($_REQUEST['id']))
		fatal_lang_error('ultport_error_no_delete_news',false);

	$id = $smcFunc['db_escape_string']($_REQUEST['id']);	
	//Now is delete the news
	$smcFunc['db_query']('',"
		DELETE FROM {db_prefix}up_news 
		WHERE id = {int:id}
		LIMIT 1",
		array(
			'id' => $id,
		)
	);	
	redirectexit('action=adminportal;area=up-news;sa=admin-news;'. $context['session_var'] .'=' . $context['session_id']);	
}

//Modules - Area Board News
function ShowBoardNews()
{
	global $context, $txt;

	//load template
	loadTemplate('UltimatePortalModules');
	$subActions = array(
		'bn-main' => 'ShowBoardNewsMain',
	);	
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'bn-main';
	$context['sub_action'] = $_REQUEST['sa'];
	$context[$context['adminportal_menu_name']]['tab_data'] = array(
		'title' => $txt['ultport_admin_board_news_title'] . ' - ' . $txt['ultport_admin_module_title2'],
		'description' => $txt['ultport_admin_board_news_descrip'],
		'tabs' => array(
			'bn-main' => array(
				'description' => $txt['ultport_admin_board_news_descrip'],
			),
		),
	);
	$subActions[$_REQUEST['sa']]();
}

function ShowBoardNewsMain()
{
	global $context, $txt;
	
	if(empty($_POST['bn-save']))
		checkSession('get');
	//Save 
	if (!empty($_POST['bn-save']))
	{
		checkSession('post');
		if (!empty($_POST['boards']))
		{
			foreach ($_POST['boards'] as $i => $v)
				 if (!is_numeric($_POST['boards'][$i])) 
				 	unset($_POST['boards'][$i]);	
			$id_boards = implode(',', $_POST['boards']);
		}
		$_POST['board_news_limit'] = (!empty($_POST['board_news_limit']) && is_numeric($_POST['board_news_limit'])) ? (int)$_POST['board_news_limit'] : 10;
		$_POST['board_news_lenght'] = (!empty($_POST['board_news_lenght']) && is_numeric($_POST['board_news_lenght'])) ? (int)$_POST['board_news_lenght'] : 0;
		//save the ultimate portal settings section board news
		saveUltimatePortalSettings("config_board_news");		
		
		//save the select multiple in the ultimate portal table settings
		$board_news_view = $id_boards;		
		$configUltimatePortalVar['board_news_view'] = $board_news_view;
		updateUltimatePortalSettings($configUltimatePortalVar, 'config_board_news');		

		redirectexit('action=adminportal;area=board-news;sa=bn-main;'. $context['session_var'] .'=' . $context['session_id']);
	}
	up_loadJumpTo();
	$context['sub_template'] = 'board_news_main';
	$context['page_title'] = $txt['ultport_admin_board_news_title'] . ' - ' . $txt['ultport_admin_board_news_main'] . ' - ' . $txt['ultport_admin_module_title2'];
}
//Internal Page SubActions
function ShowInternalPage()
{
	global $context, $txt;
	
	//load template		
	loadTemplate('UltimatePortalModules');	
	//Load subactions for the ultimate portal Internal Page
	$subActions = array(
		'main' => 'ShowInternalPageMain',
	);
	
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'main';
	$context['sub_action'] = $_REQUEST['sa'];
	$context[$context['adminportal_menu_name']]['tab_data'] = array(
		'title' => $txt['ipage_title'] . ' - ' . $txt['ultport_admin_module_title2'],
		'description' => '',
		'tabs' => array(
			'main' => array(
				'description' => $txt['ipage_settings_description'],
			),
		),
	);
	$subActions[$_REQUEST['sa']]();
}

//Internal Page Main
function ShowInternalPageMain()
{
	global $context, $txt;

	if(empty($_POST['save']))
		checkSession('get');	

	if (!empty($_POST['save']))
	{
		checkSession('post');
		$_POST['ipage_limit'] = (!empty($_POST['ipage_limit']) && is_numeric($_POST['ipage_limit'])) ? (int)$_POST['ipage_limit'] : 10;
		//save the ultimate portal settings section internal page module
		saveUltimatePortalSettings('config_ipage');					
	}
	$context['sub_template'] = 'ipage_main';
	$context['page_title'] = $txt['ipage_title'] . ' - ' . $txt['ipage_settings_title'] . ' - ' . $txt['ultport_admin_module_title2'];
}