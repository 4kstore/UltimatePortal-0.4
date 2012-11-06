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
	
function UPInternalPageMain()
{
	global $sourcedir, $context, $ultimateportalSettings;
	loadtemplate('UPInternalPage');

	if (loadlanguage('UPInternalPage') == false)
		loadLanguage('UPInternalPage','english');

	//Is active the Internal Page module?
	if(empty($ultimateportalSettings['ipage_enable']))
		fatal_lang_error('ultport_error_no_active',false);

	$subActions = array(
		'main' => 'Main',
		'view' => 'View',
			'add' => 'Add',		
			'edit' => 'Edit',
			'delete' => 'Delete',
		'inactive' => 'Inactive',			
		'view-inactive' => 'ViewInactive',		
	);	
	$_REQUEST['sa'] = (!empty($_REQUEST['sa']) && !empty($subActions[$_REQUEST['sa']])) ? $_REQUEST['sa'] : 'main';
	call_user_func($subActions[$_REQUEST['sa']]);
}
function Main()
{
	global $context, $scripturl, $txt, $settings, $ultimateportalSettings;

	if (empty($ultimateportalSettings['ipage_enable']))
		fatal_lang_error('ultport_error_no_active',false);
		
	//Link-tree
	$context['news-linktree'] = '<img alt="" style="float:left" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/open_linktree.gif" />&nbsp;<a href="'. $scripturl .'?action=internal-page">'. $txt['up_module_ipage_title'] .'</a>';	

	//Load Internal Page
	LoadInternalPage('',"WHERE active = 'on'");	
	//It is disabled, any internal page?
	DisablePage("WHERE active = 'off'");

	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=internal-page',
		'name' => $txt['up_module_ipage_title']
	);
	$context['sub_template'] = 'main';
	$context['page_title'] = $txt['up_module_ipage_title'];

}

function View()
{
	global $context, $scripturl, $txt, $settings, $smcFunc, $ultimateportalSettings, $user_info;
	
	if (empty($ultimateportalSettings['ipage_enable']))
		fatal_lang_error('ultport_error_no_active',false);	

	$id = !empty($_REQUEST['id']) ? (int)$smcFunc['db_escape_string']($_REQUEST['id']) : '';	
	if (empty($id))
		fatal_lang_error('ultport_error_no_action',false);
	
	//Load Specific Internal Page
	LoadInternalPage($id);
	
	//Can VIEW?, disabled page?, is admin?
	if ((empty($context['view_ipage']) || ($context['can_view'] === false)) && !$user_info['is_admin'])
		fatal_lang_error('ultport_error_no_view',false);
	
	//News Link-tree
	$context['news-linktree'] = '&nbsp;<img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/open_linktree.gif" />&nbsp;<a href="'. $scripturl .'?action=internal-page">'. $txt['up_module_ipage_title'] .'</a> <br /><img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/linktree_side.gif" />&nbsp;'. $context['title'];	

	//Forum linktree
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=internal-page',
		'name' => $txt['up_module_ipage_title']
	);
	$context['social_bookmarks'] = !empty($ultimateportalSettings['ipage_social_bookmarks']) ? UpSocialBookmarks($scripturl .'?action=internal-page;sa=view;id='. $id ) : '';
	$context['sub_template'] = 'view';
	$context['page_title'] = $context['title'];
}
//Modules Internal Page - Sect: Add 
function Add()
{
	global $context, $scripturl, $txt, $settings, $smcFunc, $user_info, $ultimateportalSettings;

	if(empty($_POST['save']))
		checkSession('get');
		
	if (empty($ultimateportalSettings['ipage_enable']))
		fatal_lang_error('ultport_error_no_active',false);

	if (!$user_info['is_admin'] && !$user_info['up-modules-permissions']['ipage_add'])
		fatal_lang_error('ultport_error_no_perms_groups',false);

	if (!empty($_REQUEST['type']) && !in_array($_REQUEST['type'], array('html', 'bbc')))
		fatal_lang_error('ultport_error_no_action',false);

	if (!empty($_POST['save']))
	{
		checkSession('post');
		if (empty($_POST['title']))
			fatal_lang_error('ultport_error_no_add_ipage_title',false);

		$title = (string)$smcFunc['htmlspecialchars']($_POST['title']);
		$column_left = !empty($_POST['column_left']) ? (int)$_POST['column_left'] : 0;
		$column_right = !empty($_POST['column_right']) ? (int)$_POST['column_right'] : 0;
		$content = ($_POST['type_ipage'] == 'html') ? $_POST['elm1'] : (string)$smcFunc['htmlspecialchars']($_POST['ipage_content'], ENT_QUOTES);	
		$content = $smcFunc['htmltrim']($content, ENT_QUOTES);
		$id_member = (int) $user_info['id'];
		$username = (string) $user_info['username'];
		$date_created = time();
		$type_ipage = $_POST['type_ipage'];
		$permissionsArray = array();
		if (!empty($_POST['perms']))
		{
			foreach ($_POST['perms'] as $rgroup)
				$permissionsArray[] = (int) $rgroup;
		}
		$finalPermissions = implode(",",$permissionsArray);
		$active = !empty($_POST['active']) ? $_POST['active'] : 'off';
		$sticky = !empty($_POST['sticky']) ? $_POST['sticky'] : 0;
		
		//Now insert the NEWS in the smf_up_news			
		$smcFunc['db_insert']('replace',
			'{db_prefix}ultimate_portal_ipage',
			array(
				'title' => 'string', 'sticky' => 'int', 'active' => 'string', 'type_ipage' => 'string', 'content' => 'string', 'perms' => 'string', 'column_left' => 'int', 'column_right' => 'int', 'date_created' => 'int', 'id_member' => 'int', 'username' => 'string',
			),
			array(
				$title,$sticky,$active,$type_ipage,$content,$finalPermissions,$column_left,$column_right,$date_created,$id_member,$username
			),
			array('id')
		);
		redirectexit('action=internal-page');		
	}
	//Load Member Groups
	LoadMemberGroups();
	
	//Type Internal Page
	$context['type_ipage'] = !empty($_REQUEST['type']) ? $smcFunc['db_escape_string']($_REQUEST['type']) : 'html';	
	//Load the Editor HTML or BBC?
	if ($_REQUEST['type'] == 'html')
	{
		context_html_headers();
		$type_ipage_linktree = $txt['up_ipage_add_html'];
	}	
	if ($_REQUEST['type'] == 'bbc')
	{
		// Now create the editor.
		$editorOptions = array(
			'id' => 'ipage_content',
			'value' => '',
			'form' => 'ipageform',
		);
		$context['smileyBox_container'] = 'smileyBox_'.$editorOptions['id'];
		$context['bbcBox_container'] = 'bbcBox_'.$editorOptions['id'];
		up_create_control_richedit($editorOptions);	
		$context['post_box_name'] = $editorOptions['id'];
		$type_ipage_linktree = $txt['up_ipage_add_bbc'];				
	}
	$context['news-linktree'] = '&nbsp;<img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/open_linktree.gif" />&nbsp;<a href="'. $scripturl .'?action=internal-page">'. $txt['up_module_ipage_title'] .'</a> <br /><img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/linktree_side.gif" />&nbsp;'. $type_ipage_linktree;	

	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=internal-page',
		'name' => $txt['up_module_ipage_title']
	);	
	$context['sub_template'] = 'add';
	$context['page_title'] = $type_ipage_linktree;
}
//Modules Internal Page - Sect: Edit
function Edit()
{
	global $context, $scripturl, $txt, $settings, $user_info, $ultimateportalSettings, $smcFunc;

	if(empty($_POST['save']))
		checkSession('get');
		
	if (empty($ultimateportalSettings['ipage_enable']))
		fatal_lang_error('ultport_error_no_active',false);

	if (!$user_info['is_admin'] && !$user_info['up-modules-permissions']['ipage_moderate'])
		fatal_lang_error('ultport_error_no_perms_groups',false);
		
	$id = (!empty($_REQUEST['id']) && is_numeric($_REQUEST['id'])) ? (int) $_REQUEST['id'] : '';

	if(empty($id))
		fatal_lang_error('ultport_error_id_not_found',false);
 
	if (!empty($_POST['save']))
	{
		checkSession('post');
		if (empty($_POST['title']))
			fatal_lang_error('ultport_error_no_add_ipage_title',false);
		$title = (string)$smcFunc['htmlspecialchars']($_POST['title']);
		$column_left = !empty($_POST['column_left']) ? (int)$_POST['column_left'] : 0;
		$column_right = !empty($_POST['column_right']) ? (int) $_POST['column_right'] : 0;
		$content = ($_POST['type_ipage'] == 'html') ? $_POST['elm1'] : (string)$smcFunc['htmlspecialchars']($_POST['ipage_content']);
		$content = $smcFunc['htmltrim']($content, ENT_QUOTES);
		$id_member_updated = (int) $user_info['id'];
		$username_updated = (string) $user_info['username'];
		$date_updated = time();
		$type_ipage = $_POST['type_ipage'];
		$permissionsArray = array();
		if (!empty($_POST['perms']))
		{
			foreach ($_POST['perms'] as $rgroup)
				$permissionsArray[] = (int) $rgroup;
		}
		$finalPermissions = implode(",",$permissionsArray);
		$active = !empty($_POST['active']) ? (string)$_POST['active'] : 'off';
		$sticky = !empty($_POST['sticky']) ? (int)$_POST['sticky'] : 0;
		
		//Now insert the NEWS in the smf_up_news
		$smcFunc['db_query']('',"
			UPDATE {db_prefix}ultimate_portal_ipage
			SET 	title = {string:title}, 
					sticky = {int:sticky}, 
					active = {string:active}, 
					type_ipage = {string:type_ipage}, 
					content = {string:content}, 
					perms = {string:perms}, 
					column_left = {int:column_left}, 
					column_right = {int:column_right}, 
					date_updated = {int:date_updated}, 
					id_member_updated = {int:id_member_updated}, 
					username_updated = {string:username_updated}
			WHERE id = {int:id}",
			array(
				'title' => $title,
				'sticky' => $sticky,
				'active' => $active,
				'type_ipage' => $type_ipage,
				'content' => $content,
				'perms' => $finalPermissions,
				'column_left' => $column_left,
				'column_right' => $column_right,
				'date_updated' => $date_updated,
				'id_member_updated' => $id_member_updated,
				'username_updated' => $username_updated,
				'id' => $id,			
			)
		);		
		//redirect 
		if ($active == 'on')
			redirectexit('action=internal-page;sa=view;id='. $id);		
		else
			redirectexit('action=internal-page;sa=view-inactive;id='. $id);			
	}
	
	if(!empty($id))
	{
		//Load Specific Information
		LoadInternalPage('', $condition = "WHERE id = $id");	
		//Load Member Groups
		LoadMemberGroups();	
		//Load the Editor HTML or BBC?
		if ($context['type_ipage'] == 'html')
		{
			context_html_headers();
			$type_ipage_linktree = $txt['up_ipage_add_html'];
		}	
		if ($context['type_ipage'] == 'bbc')
		{
			$editorOptions = array(
				'id' => 'ipage_content',
				'value' => $context['content'],
				'form' => 'ipageform',
			);
			$context['smileyBox_container'] = 'smileyBox_'.$editorOptions['id'];
			$context['bbcBox_container'] = 'bbcBox_'.$editorOptions['id'];
			up_create_control_richedit($editorOptions);	
			$context['post_box_name'] = $editorOptions['id'];
			$type_ipage_linktree = $txt['up_ipage_add_bbc'];				
		}
	
		//IP Link-tree
		$context['news-linktree'] = '&nbsp;<img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/open_linktree.gif" />&nbsp;<a href="'. $scripturl .'?action=internal-page">'. $txt['up_module_ipage_title'] .'</a> <br /><img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/linktree_side.gif" />&nbsp;'. $txt['ultport_button_edit'] . '( <em><a href="'. $scripturl .'?action=internal-page;sa='. (($context['active'] == 'off' && $user_info['is_admin']) ? 'view-inactive' : 'view') .';id='. $context['id'] .'">'. $context['title'] .'</a></em> )';	
		//Forum linktree
		$context['linktree'][1] = array(
			'url' => $scripturl . '?action=internal-page',
			'name' => $txt['up_module_ipage_title']
		);
	}
	$context['sub_template'] = 'edit';
	$context['page_title'] = $txt['ultport_button_edit'] . '('. $context['title'] .')';
}

//Modules Internal Page - Sect: Delete Page
function Delete()
{
	global $user_info, $smcFunc;
	checkSession('get');
	if (!$user_info['is_admin'] && !$user_info['up-modules-permissions']['ipage_moderate'])
		fatal_lang_error('ultport_error_no_perms_groups',false);
	
	$id = !empty($_REQUEST['id']) ? (int) $smcFunc['db_escape_string']($_REQUEST['id']) : '';

	if (empty($id))
		fatal_lang_error('ultport_error_no_delete_ippage',false);
	
	$smcFunc['db_query']('',"
		DELETE FROM {db_prefix}ultimate_portal_ipage 
		WHERE id = {int:id}
		LIMIT 1",
		array(
			'id' => $id,
		)
	);
	redirectexit('action=internal-page');
}

function Inactive()
{
	global $context, $scripturl, $txt, $settings, $ultimateportalSettings;

	if (empty($ultimateportalSettings['ipage_enable']))
		fatal_lang_error('ultport_error_no_active',false);
		
	//IP Link-tree
	$context['news-linktree'] = '&nbsp;<img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/open_linktree.gif" />&nbsp;<a href="'. $scripturl .'?action=internal-page">'. $txt['up_module_ipage_title'] .'</a> <br /><img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/linktree_side.gif" />&nbsp;'. $txt['up_ipage_disabled_any_ipage_title'];	

	$context['is_inactive_page'] = 1;
	//Load Internal Page
	LoadInternalPage('',"WHERE active = 'off'");
	
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=internal-page',
		'name' => $txt['up_module_ipage_title']
	);
	$context['sub_template'] = 'main';
	$context['page_title'] = $txt['up_module_title'] . ' - ' . $txt['up_module_ipage_title'];
}

function ViewInactive()
{
	global $context, $scripturl, $txt, $settings, $smcFunc, $ultimateportalSettings, $user_info;
	
	if (empty($ultimateportalSettings['ipage_enable']))
		fatal_lang_error('ultport_error_no_active',false);

	$id = !empty($_REQUEST['id']) ? (int) $smcFunc['db_escape_string']($_REQUEST['id']) : '';
	
	if (empty($id))
		fatal_lang_error('ultport_error_no_action',false);
	
	//Load Specific Internal Page
	LoadInternalPage($id, "WHERE active = 'off'");
	
	//Can VIEW?, disabled page?, is admin?
	if ((empty($context['view_ipage']) || ($context['can_view'] === false)) && !$user_info['is_admin'])
		fatal_lang_error('ultport_error_no_view',false);
	
	//IP Link-tree
	$context['news-linktree'] = '&nbsp;<img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/open_linktree.gif" />&nbsp;<a href="'. $scripturl .'?action=internal-page">'. $txt['up_module_ipage_title'] .'</a> <br /><img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/linktree_side.gif" />&nbsp;'. $context['title'];
	
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=internal-page',
		'name' => $txt['up_module_ipage_title']
	);
	//Social Bookmarks
	$context['social_bookmarks'] = !empty($ultimateportalSettings['ipage_social_bookmarks']) ? UpSocialBookmarks($scripturl .'?action=internal-page;sa=view;id='. $id ) : '';

	$context['sub_template'] = 'view';
	$context['page_title'] = $txt['up_module_ipage_title'] .' - '. $context['title'];
}