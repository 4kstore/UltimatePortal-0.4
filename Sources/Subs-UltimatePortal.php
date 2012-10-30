<?php
/*---------------------------------------------------------------------------------
*	Ultimate Portal
*	Version 0.3
*	Project manager: vicram10
*	Copyright 2011
*	Powered by SMFSimple.com
**********************************************************************************/
/*
Functions

	- 	void updateUltimatePortalSettings($changeArray, $section, $update = false)!!
		//

	-	void UltimatePortalBlockDir()!!
		//

	-	void UltimatePortalLangs()!!
		//

	-	void context_html_headers()!!
		//Load the TinyMCE link headers

*/

if (!defined('SMF'))
	die('Hacking attempt...');

//Reduce Site Overload is Checked? okay... if save blocks, edit blocks, delete existing cache files....
function RSODeleteCacheFiles()
{
	global $ultimateportalSettings, $cachedir;

	if(!empty($ultimateportalSettings['up_reduce_site_overload']))
	{
		if((cache_get_data('up_bk', 3600)) != NULL
			|| (cache_get_data('load_block_center', 3600)) != NULL
			|| (cache_get_data('load_block_left', 3600)) != NULL
			|| (cache_get_data('load_block_right', 3600)) != NULL)
		{
			$no_load_files = array('index.php', '.htaccess', 'index.html');
			if ($handle = opendir($cachedir))
			{
				while (false !== ($file = readdir($handle)))
				{
					$ext = substr(strrchr($file, "."), 1); //Get file extension
					if ($ext == "php")
					{
						$file_explode = explode('-', str_replace(".php", "", $file));
						if (in_array('up_bk', $file_explode)
							|| in_array('load_block_center', $file_explode)
							|| in_array('load_block_left', $file_explode)
							|| in_array('load_block_right', $file_explode))
						{
							//Borramos el archivo
							unlink($cachedir .'/'.$file);
						}
					}
				}
				closedir($handle);
			}
		}
	}
}
// Load this user's permissions for the Modules.
function LoadMemberGroupsPermissions()
{
	global $user_info, $smcFunc;

	$user_info['up-modules-permissions'] = array();
	if ($user_info['is_admin'])
		return;
		
	$cache_groups = $user_info['groups'];
	asort($cache_groups);
	$cache_groups = implode(',', $cache_groups);

	// Get the general permissions.
	$request = $smcFunc['db_query']('',"
		SELECT permission, value
		FROM {db_prefix}up_groups_perms
		WHERE ID_GROUP IN (" . implode(', ', $user_info['groups']) . ')');
	$removals = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (empty($row['value']))
			$removals[] = $row['permission'];
		else
			$user_info['up-modules-permissions'][$row['permission']] = $row['value'];
	}
	$smcFunc['db_free_result']($request);

	if (isset($cache_groups))
		cache_put_data('up-modules-permissions:' . $cache_groups, array($user_info['up-modules-permissions'], $removals), 240);

}
// Helper function, it sets up the context for database ULTIMATE PORTAL settings.
function prepareUltimatePortalSettingContext($section)
{
	global $context, $smcFunc;
	$context['up-config_vars'] = array();
	$myquery = $smcFunc['db_query']('', "
				SELECT variable
				FROM {db_prefix}ultimate_portal_settings
				WHERE section = {string:section}",
				array(
					'section' => $section,
				));
	while($row = $smcFunc['db_fetch_assoc']($myquery))
	{
		$configvars = &$context['up-config_vars'][];
		$configvars['variable'] = $row['variable'];
	}
	$smcFunc['db_free_result']($myquery);
}

// Helper function for saving database settings.
function saveUltimatePortalSettings($section)
{
	global $context;
	prepareUltimatePortalSettingContext($section);
	foreach ($context['up-config_vars'] as $configvars)	
		$configUltimatePortalVar[$configvars['variable']] = !empty($_POST[$configvars['variable']]) ? $_POST[$configvars['variable']] : '';	
	updateUltimatePortalSettings($configUltimatePortalVar, $section);
}

// Updates the Ultimate Portal Settings table as well as $ultimateportalSettings... only does one at a time if $update is true.
// All input variables and values are assumed to have escaped apostrophes(')!
function updateUltimatePortalSettings($changeArray, $section, $update = false)
{
	global $ultimateportalSettings, $smcFunc;

	if (empty($changeArray) || !is_array($changeArray))
		return;

	// In some cases, this may be better and faster, but for large sets we don't want so many UPDATEs.
	if ($update)
	{
		foreach ($changeArray as $variable => $value)
		{
			$smcFunc['db_query']('',"
				UPDATE {db_prefix}ultimate_portal_settings
				SET value = " . ($value === true ? 'value + 1' : ($value === false ? 'value - 1' : "'$value'")) . "
				WHERE variable = {string:variable}
				LIMIT {int:limit}",
				array(
					'variable' => $variable,
					'limit' => 1,
				)
			);
			$ultimateportalSettings[$variable] = $value === true ? $ultimateportalSettings[$variable] + 1 : ($value === false ? $ultimateportalSettings[$variable] - 1 : stripslashes($value));
		}
		// Clean out the cache and make sure the cobwebs are gone too.
		cache_put_data('ultimateportalSettings', null, 90);
		return;
	}
	$replaceArray = array();
	foreach ($changeArray as $variable => $value)
	{
		// Don't bother if it's already like that ;).
		if (!empty($ultimateportalSettings[$variable]) && $ultimateportalSettings[$variable] == stripslashes($value))
			continue;
		// If the variable isn't set, but would only be set to nothing'ness, then don't bother setting it.
		elseif (empty($ultimateportalSettings[$variable]) && empty($value))
			continue;
			
		$value = $smcFunc['htmlspecialchars']($value, ENT_QUOTES);
		$replaceArray[] = "(SUBSTRING('$variable', 1, 255), SUBSTRING('$value', 1, 65534), SUBSTRING('$section', 1, 65534))";
		$ultimateportalSettings[$variable] = stripslashes($value);
	}

	if (empty($replaceArray))
		return;

	$smcFunc['db_query']('',"
		REPLACE INTO {db_prefix}ultimate_portal_settings
			(variable, value, section)
		VALUES " . implode(',
			', $replaceArray));
	// Kill the cache - it needs redoing now, but we won't bother ourselves with that here.
	cache_put_data('ultimateportalSettings', null, 90);
}

//Load Block Directory
function UltimatePortalBlockDir()
{
	global $boarddir, $smcFunc;

	$listfile = '';
	$file = '';
	$listfile2 = '';

	$myquery = $smcFunc['db_query']('',"
				SELECT file
				FROM {db_prefix}ultimate_portal_blocks"
	);
	while($row = $smcFunc['db_fetch_assoc']($myquery))
		$listfile .= $row['file'];
	

	$dirb = $boarddir."/up-blocks/";
	$no_load_files = array('index.php', '.htaccess', 'index.html');
	
	//Add database entries for new system blocks (uploaded php block files)
	if ($handle = opendir($dirb))
	{
		while (false !== ($file = readdir($handle)))
		{
			if (!in_array($file, $no_load_files))
			{
				$ext = substr(strrchr($file, "."), 1); //Get file extension
				if ($ext == "php")
				{
					$pos = strpos($listfile, $file);
					if ($pos === false)
					{
						$title = str_replace(".php", "", $file);
						$icon = $title;
						$smcFunc['db_query']('',"INSERT INTO {db_prefix}ultimate_portal_blocks(file, title, icon, content, bk_collapse, bk_no_title, bk_style)
								VALUES('$file', '$title', '$icon', '', 'on', '', 'on')");
					}
					$listfile2 .= $file.' ';
				}
			}
		}
		closedir($handle);
   	}
	//Delete database entries for missing system blocks
	$myquery = $smcFunc['db_query']('',"
					SELECT file
					FROM {db_prefix}ultimate_portal_blocks
					WHERE personal={int:personal}",
					array(
						'personal' => 0,
					)
	);
	while($row = $smcFunc['db_fetch_assoc']($myquery))
	{
		$pos = strpos($listfile2, $row['file']);
		if ($pos === false)
		{
			$smcFunc['db_query']('', "
				DELETE FROM {db_prefix}ultimate_portal_blocks
				WHERE file={string:file}",
				array(
					'file' => $row['file'],
				)
			);
		}
	}
}

//Load Lang Directory
function UltimatePortalLangs()
{
	global $context, $boarddir;

	$dir = '';
	$dir = opendir($boarddir . '/Themes/default/languages');

	$context['ult_port_langs'] = "\n<select size='1' name='file'>\n";
	while (($file = readdir($dir)) !== false)
	{
		if ($file != "." && $file != "..")/* && preg_match("`(.*)\.".$context['user']['language']."`i", $file)*/
			$context['ult_port_langs'] .= '<option value="'.$file.'" >'.$file.'</option>\n';		
	}
	closedir($dir);
	$context['ult_port_langs'] .= "</select>\n";
}

//Load Specific lang file
function LoadSpecificLang($this_file)
{
	global $context, $boarddir;
	//Language Dir
	$this_file = $boarddir . '/Themes/default/languages/'. $this_file;

	//Cached title and text values
	if (!$handle = fopen($this_file, "rb"))
		fatal_lang_error('ultport_error_no_add_bk_fopen_error',false);
		
	$context['content'] = fread($handle, filesize($this_file));
	$context['content_htmlspecialchars'] = htmlspecialchars($context['content']);
	fclose($handle);
}

//Create Specific lang file
function CreateSpecificLang($file, $content)
{
	global $boarddir;

		$dir = $boarddir . '/Themes/default/languages/'.$file;
		//Create cached php file
		if (!$handle = fopen($dir, 'wb'))
 			fatal_lang_error('ultport_error_fopen_error',false);
		//Write $content to cached php file
   		if (!fwrite($handle, $content))
			fatal_lang_error('ultport_error_no_add_bk_nofile',false);
		fclose($handle);
		//Close cached php file
}

//Prepare the Ultimate Portal Permissions Groups
function LoadUPModulesPermissions()
{
	global $context, $txt;

	//Add a new value, and this automatically added in the Permissions Forms, from Ultimate Portal Permissions Settings
	$perms_text_name = array(
		'news_add',
		'news_moderate',
		'ipage_add',
		'ipage_moderate',
	);
	$context['permissions'] = array();
	foreach ($perms_text_name as $text_name)
	{
		/*	The $txt['ultport_perms_'] localization is language/UltimatePortalCP.YOUR-LANGUAGE.php
			Search for //Perms - Names	*/
		$permissions = &$context['permissions'][];
		$permissions['name'] = $text_name;
		$permissions['text-name'] = $txt['ultport_perms_'.$text_name];

	}
}
//Load MemberGroups
function LoadMemberGroups($group_selected = -99, $call = '')
{
	global $context, $txt, $smcFunc;

	$guest = true;
	$regularmember = true;

	$dbresult = $smcFunc['db_query']('', "
		SELECT id_group, group_name
		FROM {db_prefix}membergroups
		WHERE id_group <> {int:id_group} AND min_posts = {int:min_posts}
		ORDER BY group_name",
		array(
			'id_group' => 1,
			'min_posts' => -1,
		)
	);

	$context['groups'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$context['groups'][$row['id_group']] = array(
			'id_group' => $row['id_group'],
			'group_name' => $row['group_name'],
			'selected' => (($row['id_group'] == $group_selected) ? 'selected="selected"' : ''),
		);
	}
	//Add Regular member
	if ($regularmember === true)
	{
		$context['groups'][0] = array(
			'id_group' => 0,
			'group_name' => $txt['membergroups_members'],
			'selected' => (($group_selected == 0) ? 'selected="selected"' : ''),
		);
	}
	//Add Guest
	if ($guest === true)
	{
		$context['groups'][-1] = array(
			'id_group' => -1,
			'group_name' => '<strong>'. $txt['membergroups_guests'] .'</strong>',
			'selected' => (($group_selected == -1) ? 'selected="selected"' : ''),
		);
	}
	$smcFunc['db_free_result']($dbresult);
}

function LoadBlocksTable()
{
	global $context, $txt, $smcFunc;

	$context['exists_left'] = 0;
	$context['exists_center'] = 0;
	$context['exists_right'] = 0;
	$myquery = $smcFunc['db_query']('',"
		SELECT * FROM {db_prefix}ultimate_portal_blocks
		ORDER BY active DESC, progressive, id",
		array(
			'position' => 'left',
		)
	);
	$totprog = 	$smcFunc['db_num_rows']($myquery);
	$context['center-progoption'] = '';//only is declared

	
		
	$totalleft = 0;
	$totalcenter = 0;
	$totalright = 0;
	

	while( $row = $smcFunc['db_fetch_assoc']($myquery))
	{
		if($row['position'] == "left" || $row['position'] == "")
		{
			$context['exists_left'] = 1;
			$block_left = &$context['block-left'][];
			$block_left['id'] = $row['id'];
			$block_left['title'] = $row['title'];
			$block_left['position'] = $txt['ultport_blocks_left'];
			$block_left['progressive'] = $row['progressive'];
			$block_left['active'] = $row['active'];
			$block_left['activestyle'] = $block_left['active'] ? "windowbg" : "windowbg2"; //Active block highlighting
			$block_left['active'] = $block_left['active'] ? "checked=\"checked\"" : "";
			$block_left['title_form'] = $block_left['id']."_title";
			$block_left['position_form'] = $block_left['id']."_position";
			$block_left['progressive_form'] = $block_left['id']."_progressive";
			$block_left['active_form'] = $block_left['id']."_active";
			$totalleft++;
		}
		elseif($row['position'] == "center")
		{
			$context['exists_center'] = 1;
			$block_center = &$context['block-center'][];
			$block_center['id'] = $row['id'];
			$block_center['title'] = $row['title'];
			$block_center['position'] = $txt['ultport_blocks_center'];
			$block_center['progressive'] = $row['progressive'];
			$block_center['active'] = $row['active'];
			$block_center['activestyle'] = $block_center['active'] ? "windowbg" : "windowbg2"; //Active block highlighting
			$block_center['active'] = $block_center['active'] ? "checked=\"checked\"" : "";
			$block_center['title_form'] = $block_center['id']."_title";
			$block_center['position_form'] = $block_center['id']."_position";
			$block_center['progressive_form'] = $block_center['id']."_progressive";
			$block_center['active_form'] = $block_center['id']."_active";
			$totalcenter++;
		}
		elseif($row['position'] == "right")
		{
			$context['exists_right'] = 1;
			$block_right = &$context['block-right'][];
			$block_right['id'] = $row['id'];
			$block_right['title'] = $row['title'];
			$block_right['position'] = $txt['ultport_blocks_right'];
			$block_right['progressive'] = $row['progressive'];
			$block_right['active'] = $row['active'];
			$block_right['activestyle'] = $block_right['active'] ? "windowbg" : "windowbg2"; //Active block highlighting
			$block_right['active'] = $block_right['active'] ? "checked=\"checked\"" : "";
			$block_right['title_form'] = $block_right['id']."_title";
			$block_right['position_form'] = $block_right['id']."_position";
			$block_right['progressive_form'] = $block_right['id']."_progressive";
			$block_right['active_form'] = $block_right['id']."_active";
			$totalright++;
		}		
	}
	$smcFunc['db_free_result']($myquery);
	
	$context['left-progoption'] = '';//only is declared
	for ($i = 1; $i <= $totalleft; $i++)
   		$context['left-progoption'] .= "<option value=\"$i\">$i</option>";
	
	$context['center-progoption'] = '';//only is declared
	for ($i = 1; $i <= $totalcenter; $i++)
   		$context['center-progoption'] .= "<option value=\"$i\">$i</option>";
		
	$context['right-progoption'] = '';//only is declared
	for ($i = 1; $i <= $totalright; $i++)
   		$context['right-progoption'] .= "<option value=\"$i\">$i</option>";	
}

function LoadBlocksTableHEADER($filter = "")
{
	global $context, $smcFunc;

	$context['exists_multiheader'] = 0;
	$mbquery = 	$smcFunc['db_query']('',"
		SELECT * FROM {db_prefix}up_multiblock
		WHERE position = {string:position}
		". (!empty($filter) ? " ".$filter." " : "") ."
		ORDER BY id",
		array(
			'position' => 'header',
		)
	);
	while($row = $smcFunc['db_fetch_assoc']($mbquery))
	{
		$context['exists_multiheader'] = 1;
		$context['block-header'][$row['id']] = array(
			'id' => $row['id'],
			'mbtitle' => $row['title'],
			'blocks' => $row['blocks'],
			'position' => $row['position'],
			'design' => $row['design'],
			'mbk_title' => $row['mbk_title'],
			'mbk_collapse' => $row['mbk_collapse'],
			'mbk_style' => $row['mbk_style'],
		);

		$id_blocks = $context['block-header'][$row['id']]['blocks'];
		$myquery = 	$smcFunc['db_query']('',"
			SELECT * FROM {db_prefix}ultimate_portal_blocks
			WHERE position = {string:position} and id in($id_blocks)
			ORDER BY mbk_view ASC, progressive, id",
			array(
				'position' => 'header',
			)
		);
		$totprog = $smcFunc['db_num_rows']($myquery);
		
		$context['header-progoption-'.$row['id']] = '';//only is declared
		for ($i = 1; $i <= $totprog; $i++)
			$context['header-progoption-'.$row['id']] .= "<option value=\"$i\">$i</option>";

		while( $row2 = $smcFunc['db_fetch_assoc']($myquery))
		{
			$context['block-header'][$row['id']]['vblocks'][] = array(
				'id' => $row2['id'],
				'title' => $row2['title'],
				'position' => $row2['position'],
				'progressive' => $row2['progressive']!=100 ? $row2['progressive'] : $totprog,
				'active' => $row2['active'],
				'activestyle' => $row2['active'] ? "windowbg" : "windowbg2", //Active block highlighting
				'active' => $row2['active'] ? "checked=\"checked\"" : "",
				'title_form' => $row2['id']."_title",
				'position_form' => $row2['id']."_position",
				'progressive_form' => $row2['id']."_progressive",
				'active_form' => $row2['id']."_active",
				'file' => $row2['file'],
				'icon' => $row2['icon'],
				'personal' => $row2['personal'],
				'content' => $row2['content'],
				'perms' => $row2['perms'],
				'bk_collapse' => $row2['bk_collapse'],
				'bk_no_title' => $row2['bk_no_title'],
				'bk_style' => $row2['bk_style'],
				'mbk_view' => $row2['mbk_view'],
			);
		}
	}
}

function LoadBlocksTableFOOTER($filter = "")
{
	global $context, $smcFunc;

	$context['exists_footer'] = 0;

	$mbquery = 	$smcFunc['db_query']('',"
		SELECT * FROM {db_prefix}up_multiblock
		WHERE position = {string:position}
		". (!empty($filter) ? " ".$filter." " : "") ."
		ORDER BY id",
		array(
			'position' => 'footer',
		)
	);
	while($row = $smcFunc['db_fetch_assoc']($mbquery))
	{
		$context['exists_footer'] = 1;
		$context['block-footer'][$row['id']] = array(
			'id' => $row['id'],
			'mbtitle' => $row['title'],
			'blocks' => $row['blocks'],
			'position' => $row['position'],
			'design' => $row['design'],
			'mbk_title' => $row['mbk_title'],
			'mbk_collapse' => $row['mbk_collapse'],
			'mbk_style' => $row['mbk_style'],
		);

		$id_blocks = $context['block-footer'][$row['id']]['blocks'];
		$myquery = 	$smcFunc['db_query']('',"
			SELECT * FROM {db_prefix}ultimate_portal_blocks
			WHERE position = {string:position} and id in($id_blocks)
			ORDER BY mbk_view ASC, progressive, id",
			array(
				'position' => 'footer',
			)
		);
		$totprog = $smcFunc['db_num_rows']($myquery);

		$context['footer-progoption-'.$row['id']] = '';//only is declared

		for ($i = 1; $i <= $totprog; $i++)
			$context['footer-progoption-'.$row['id']] .= "<option value=\"$i\">$i</option>";
			
		while( $row2 = $smcFunc['db_fetch_assoc']($myquery) )
		{
			$context['block-footer'][$row['id']]['vblocks'][] = array(
				'id' => $row2['id'],
				'title' => $row2['title'],
				'position' => $row2['position'],
				'progressive' => $row2['progressive']!=100 ? $row2['progressive'] : $totprog,
				'active' => $row2['active'],
				'activestyle' => $row2['active'] ? "windowbg" : "windowbg2", //Active block highlighting
				'active' => $row2['active'] ? "checked=\"checked\"" : "",
				'title_form' => $row2['id']."_title",
				'position_form' => $row2['id']."_position",
				'progressive_form' => $row2['id']."_progressive",
				'active_form' => $row2['id']."_active",
				'file' => $row2['file'],
				'icon' => $row2['icon'],
				'personal' => $row2['personal'],
				'content' => $row2['content'],
				'perms' => $row2['perms'],
				'bk_collapse' => $row2['bk_collapse'],
				'bk_no_title' => $row2['bk_no_title'],
				'bk_style' => $row2['bk_style'],
				'mbk_view' => $row2['mbk_view'],
			);
		}
	}
}

function LoadBlocksHEADERPortal($filter = "")
{
	global $context, $smcFunc;

	$context['exists_multiheader'] = 0;

	$mbquery = 	$smcFunc['db_query']('',"
		SELECT * FROM {db_prefix}up_multiblock
		WHERE position = {string:position}
		". (!empty($filter) ? " ".$filter." " : "") ."
		ORDER BY id",
		array(
			'position' => 'header',
		));
	while($row = $smcFunc['db_fetch_assoc']($mbquery) )
	{
		$context['exists_multiheader'] = 1;
		$context['block-header'][$row['id']] = array(
			'id' => $row['id'],
			'mbtitle' => $row['title'],
			'blocks' => $row['blocks'],
			'position' => $row['position'],
			'design' => $row['design'],
			'mbk_title' => $row['mbk_title'],
			'mbk_collapse' => $row['mbk_collapse'],
			'mbk_style' => $row['mbk_style'],
		);

		$id_blocks = $context['block-header'][$row['id']]['blocks'];

		$myquery = 	$smcFunc['db_query']('',"
			SELECT * FROM {db_prefix}ultimate_portal_blocks
			WHERE position = {string:position} and id in($id_blocks) and active = 'checked'
			ORDER BY mbk_view ASC, progressive, id",
			array(
				'position' => 'header',
			));

		$totprog = $smcFunc['db_num_rows']($myquery);

		$context['header-progoption-'.$row['id']] = '';//only is declared

		for ($i = 1; $i <= $totprog; $i++) {
			$context['header-progoption-'.$row['id']] .= "<option value=\"$i\">$i</option>";
		}

		while( $row2 = $smcFunc['db_fetch_assoc']($myquery) )
		{
			$context['block-header'][$row['id']]['vblocks'][$row2['mbk_view']][] = array(
				'id' => $row2['id'],
				'title' => $row2['title'],
				'position' => $row2['position'],
				'active' => $row2['active'],
				'file' => $row2['file'],
				'icon' => $row2['icon'],
				'personal' => $row2['personal'],
				'content' => $row2['content'],
				'perms' => $row2['perms'],
				'bk_collapse' => $row2['bk_collapse'],
				'bk_no_title' => $row2['bk_no_title'],
				'bk_style' => $row2['bk_style'],
				'mbk_view' => $row2['mbk_view'],
			);
		}
	}
}

function LoadBlocksFOOTERPortal($filter = "")
{
	global $context, $smcFunc;

	$context['exists_multifooter'] = 0;
	$mbquery = 	$smcFunc['db_query']('',"
		SELECT * FROM {db_prefix}up_multiblock
		WHERE position = {string:position}
		". (!empty($filter) ? " ".$filter." " : "") ."
		ORDER BY id",
		array(
			'position' => 'footer',
		));
	while($row = $smcFunc['db_fetch_assoc']($mbquery) )
	{
		$context['exists_multifooter'] = 1;
		$context['block-footer'][$row['id']] = array(
			'id' => $row['id'],
			'mbtitle' => $row['title'],
			'blocks' => $row['blocks'],
			'position' => $row['position'],
			'design' => $row['design'],
			'mbk_title' => $row['mbk_title'],
			'mbk_collapse' => $row['mbk_collapse'],
			'mbk_style' => $row['mbk_style'],
		);

		$id_blocks = $context['block-footer'][$row['id']]['blocks'];

		$myquery = 	$smcFunc['db_query']('',"
			SELECT * FROM {db_prefix}ultimate_portal_blocks
			WHERE position = {string:position} and id in($id_blocks) and active = 'checked'
			ORDER BY mbk_view ASC, progressive, id",
			array(
				'position' => 'footer',
			));

		$totprog = $smcFunc['db_num_rows']($myquery);

		$context['header-progoption-'.$row['id']] = '';//only is declared

		for ($i = 1; $i <= $totprog; $i++) {
			$context['header-progoption-'.$row['id']] .= "<option value=\"$i\">$i</option>";
		}

		while( $row2 = $smcFunc['db_fetch_assoc']($myquery) )
		{
			$context['block-footer'][$row['id']]['vblocks'][$row2['mbk_view']][] = array(
				'id' => $row2['id'],
				'title' => $row2['title'],
				'position' => $row2['position'],
				'active' => $row2['active'],
				'file' => $row2['file'],
				'icon' => $row2['icon'],
				'personal' => $row2['personal'],
				'content' => $row2['content'],
				'perms' => $row2['perms'],
				'bk_collapse' => $row2['bk_collapse'],
				'bk_no_title' => $row2['bk_no_title'],
				'bk_style' => $row2['bk_style'],
				'mbk_view' => $row2['mbk_view'],
			);
		}
	}
}

function LoadBlocksTitle()
{
	global $context, $smcFunc;
	$myquery = $smcFunc['db_query']('',"
		SELECT id, title, active
		FROM {db_prefix}ultimate_portal_blocks
		ORDER BY active DESC, id"
	);

	while( $row = $smcFunc['db_fetch_assoc']($myquery))
	{
		$block_title = &$context['block-title'][];
		$block_title['id'] = $row['id'];
		$block_title['title'] = $row['title'];
		$block_title['active'] = $row['active'];
		$block_title['activestyle'] = $block_title['active'] ? "windowbg" : "windowbg2"; //Active block highlighting
		$block_title['title_block'] = $block_title['id']."_title";
	}
	$smcFunc['db_free_result']($myquery);
}

function LoadMainLinks()
{
	global $context, $settings, $boardurl, $smcFunc;

	$myquery = 	$smcFunc['db_query']('',"
		SELECT id, icon, title, url, position, active
		FROM {db_prefix}ultimate_portal_main_links
		ORDER BY active DESC, position"
	);

	while($row = $smcFunc['db_fetch_assoc']($myquery))
	{
		$main_link = &$context['main-links'][];
		$main_link['id'] = $row['id'];
		$main_link['icon'] = str_replace("<UP_MAIN_LINK_ICON>", $settings['default_images_url'] . '/ultimate-portal/main-links', $row['icon']);
		$main_link['icon'] = '<img width="16" height="16" src="'. $main_link['icon'] .'" alt="" />';
		$main_link['title'] = $row['title'];
		$main_link['url'] = str_replace("<UP_BOARDURL>", $boardurl, $row['url']);
		$main_link['position'] = $row['position'];
		$context['last_position'] = $main_link['position'];
		$main_link['active'] = $row['active'];
		$main_link['active'] = $main_link['active'] ? "checked=\"checked\"" : "";
		$main_link['activestyle'] = $main_link['active'] ? "windowbg" : "windowbg2"; //Active block highlighting
		$main_link['active_form'] = $main_link['id']."_active";
		$main_link['position_form'] = $main_link['id']."_position";
	}
	//Position for the new add main link
	$context['last_position'] = !empty($context['last_position']) ? ($context['last_position'] + 1) : 1;
	$smcFunc['db_free_result']($myquery);
}

//load the custom and system blocks
function SystemBlock()
{
	global $settings, $context, $scripturl, $txt, $smcFunc;

	$context['bkcustom_view'] = 0;
	$myquery = $smcFunc['db_query']('',"
		SELECT id, title, active, icon, personal, file
		FROM {db_prefix}ultimate_portal_blocks
		ORDER BY active DESC, id"
	);
	while( $row = $smcFunc['db_fetch_assoc']($myquery))
	{
		
		if($row['personal'] == 0)
		{
			$block_system = &$context['block-system'][];
			$block_system['id'] = $row['id'];
			$block_system['title'] = $row['title'] . '&nbsp; <strong><em>('. $row['file'] .')</em></strong>';
			$block_system['active'] = $row['active'];
			$block_system['activestyle'] = $block_system['active'] ? "windowbg" : "windowbg2"; //Active block highlighting
			$block_system['permissions'] = '<a href="'. $scripturl .'?action=admin;area=ultimate_portal_blocks;sa=blocks-perms;id='. $row['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_permission'] .'</a>';
			$block_system['edit'] = '<a href="'. $scripturl .'?action=admin;area=ultimate_portal_blocks;sa=blocks-edit;id='. $row['id'] .';personal='. $row['personal'] .';type-php=system;sesc=' . $context['session_id'].'">'. $txt['ultport_button_edit'] .'</a>';
			$block_system['delete'] = '<a onclick="return makesurelink()" href="'. $scripturl .'?action=admin;area=ultimate_portal_blocks;sa=blocks-delete;id='. $row['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_delete'] .'</a>';;
			$block_system['type-img'] = '<img alt="PHP" title="PHP" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/icons/bk-php.png"/>';
		}
		else
		{
			$context['bkcustom_view'] = 1;
			$block_custom = &$context['block-custom'][];
			$block_custom['id'] = $row['id'];
			$block_custom['title'] = $row['title'];
			$block_custom['title_link_edit'] = ($row['personal']==1) ? '<a href="'. $scripturl .'?action=admin;area=ultimate_portal_blocks;sa=blocks-edit;id='. $row['id'] .';personal='. $row['personal'] .';sesc=' . $context['session_id'].'">'. $row['title'] .'</a>' : '<a href="'. $scripturl .'?action=admin;area=ultimate_portal_blocks;sa=blocks-edit;id='. $row['id'] .';personal='. $row['personal'] .';type-php=created;sesc=' . $context['session_id'].'">'. $row['title'] .'</a>';
			$block_custom['active'] = $row['active'];
			$block_custom['activestyle'] = $block_custom['active'] ? "windowbg" : "windowbg2"; //Active block highlighting
			$block_custom['permissions'] = '<a href="'. $scripturl .'?action=admin;area=ultimate_portal_blocks;sa=blocks-perms;id='. $row['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_permission'] .'</a>';
			$block_custom['edit'] = ($row['personal']==1) ? '<a href="'. $scripturl .'?action=admin;area=ultimate_portal_blocks;sa=blocks-edit;id='. $row['id'] .';personal='. $row['personal'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_edit'] .'</a>' : '<a href="'. $scripturl .'?action=admin;area=ultimate_portal_blocks;sa=blocks-edit;id='. $row['id'] .';personal='. $row['personal'] .';type-php=created;sesc=' . $context['session_id'].'">'. $txt['ultport_button_edit'] .'</a>';
			$block_custom['delete'] = '<a onclick="return makesurelink()" href="'. $scripturl .'?action=admin;area=ultimate_portal_blocks;sa=blocks-delete;id='. $row['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_delete'] .'</a>';

			//Block type
			switch($row['personal'])
			{
				case '1': // HTML Block
					$block_custom['type-img'] = '<img alt="HTML" title="HTML" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/icons/bk-html.png"/>';
					break;
				default: // case: 2 - PHP Block
					$block_custom['type-img'] = '<img alt="PHP" title="PHP" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/icons/bk-php.png"/>';
					break;
			}
		}
	}
}


//Load ID_MEMBER
function LoadID_MEMBER($memberName)
{
	global $smcFunc;
	
	$id_member = '';
	$memberName = !empty($memberName) ? (string) $memberName : '';
	if(!empty($memberName))
	{
		$myquery = $smcFunc['db_query']('',"
						SELECT *
						FROM {db_prefix}members
						WHERE member_name = {string:member_name}",
						array(
							'member_name' => $memberName,
						)
		);
		while( $row = $smcFunc['db_fetch_assoc']($myquery))		
			$id_member = $row['id_member'];
		
		$smcFunc['db_free_result']($myquery);
	}
	return $id_member;
}

//load the News Sections
function LoadNewsSection()
{
	global $context, $scripturl, $txt, $smcFunc, $settings;

	$context['news_rows'] = 0;
	$context['last_position'] = 0;
	$myquery = $smcFunc['db_query']('',"
		SELECT id, title, icon, position 
		FROM {db_prefix}up_news_sections 
		ORDER BY id ASC, position"
	);							
	while($row = $smcFunc['db_fetch_assoc']($myquery))
	{
		if(!empty($row['id']))
		{
			$context['news_rows'] = 1;
			$section = &$context['news-section'][];
			$section['id'] = $row['id'];
			$section['title'] = $row['title'];
			$section['icon'] = '<img alt="'.$row['title'].'" src="'. $row['icon'] .'" width="35" height="35" />';
			$section['position'] = $row['position'];
			$section['edit'] = '<a href="'. $scripturl .'?action=admin;area=up-news;sa=edit-section;id='. $row['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_edit'] .'</a>';
			$section['edithref'] = '<a href="'. $scripturl .'?action=admin;area=up-news;sa=edit-section;id='. $row['id'] .';sesc=' . $context['session_id'].'" title="'.$txt['ultport_button_edit'].'"><img alt="'.$txt['ultport_button_edit'].'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/edit.png" /></a>';
			$section['delete'] = '<a style="color:red" onclick="return makesurelink()" href="'. $scripturl .'?action=admin;area=up-news;sa=delete-section;id='. $row['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_delete'] .'</a>';
			$section['deletehref'] = '<a style="color:red" onclick="return makesurelink()" title="'. $txt['ultport_button_delete'] .'" href="'. $scripturl .'?action=admin;area=up-news;sa=delete-section;id='. $row['id'] .';sesc=' . $context['session_id'].'"><img alt="'. $txt['ultport_button_delete'] .'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/delete.png" /></a>';
			
			$context['last_position'] =	$section['position'];
		}
	}
	++$context['last_position'];
	$smcFunc['db_free_result']($myquery);
}

//Load the News
function LoadNews()
{
	global $context, $scripturl, $ultimateportalSettings, $smcFunc, $txt, $settings;
	//Prepare the constructPageIndex() function
	$start = (int) $_REQUEST['start'];
	$db_count = $smcFunc['db_query']('',"
		SELECT count(id)
		FROM {db_prefix}up_news
		ORDER BY id DESC"
	);
	$numNews = array();
	list($numNews) = $smcFunc['db_fetch_row']($db_count);
	$smcFunc['db_free_result']($db_count);

	$context['page_index'] = constructPageIndex($scripturl . "?action=admin;area=up-news;sa=admin-news;sesc=" . $context['session_id'], $start, $numNews, $ultimateportalSettings['up_news_limit']);
	// Calculate the fastest way to get the messages!
	$limit = !empty($ultimateportalSettings['up_news_limit']) ? (int)$ultimateportalSettings['up_news_limit'] : 10;
	$myquery = $smcFunc['db_query']('',"
		SELECT n.id, n.title, n.id_category, n.id_member, n.username, n.body, n.date, s.title as section
		FROM {db_prefix}up_news n
		INNER JOIN {db_prefix}up_news_sections AS s ON (n.id_category = s.id)
		ORDER BY id DESC 
		". ($limit < 0 ? "" : "LIMIT $start, $limit ")
	);
	while( $row = $smcFunc['db_fetch_assoc']($myquery))
	{
		if(!empty($row['id']))
		{
			$context['load_news_admin'] = 1;
			$news = &$context['news-admin'][];
			$news['id'] = $row['id'];
			$news['title'] = $row['title'];
			$news['title-edit'] = '<a href="'. $scripturl .'?action=admin;area=up-news;sa=edit-news;id='. $row['id'] .';sesc=' . $context['session_id'].'">'. $row['title'] .'</a>';
			$news['id_cat'] = $row['id_category'];
			$news['title-section'] = '<a href="'. $scripturl .'?action=admin;area=up-news;sa=edit-section;id='. $row['id_category'] .';sesc=' . $context['session_id'].'">'. $row['section'] .'</a>';
			$news['id_member'] = $row['id_member'];
			$news['username'] = $row['username'];
			$news['body'] = $row['body'];
			$news['date'] = $row['date'];
			$news['edit'] = '<a href="'. $scripturl .'?action=admin;area=up-news;sa=edit-news;id='. $row['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_edit'] .'</a>';
			$news['edithref'] = '<a href="'. $scripturl .'?action=admin;area=up-news;sa=edit-news;id='. $row['id'] .';sesc=' . $context['session_id'].'" title="'.$txt['ultport_button_edit'].'"><img alt="'.$txt['ultport_button_edit'].'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/edit.png" /></a>';
			$news['delete'] = '<a style="color:red" onclick="return makesurelink()" href="'. $scripturl .'?action=admin;area=up-news;sa=delete-news;id='. $row['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_delete'] .'</a>';
			$news['deletehref'] = '<a style="color:red" onclick="return makesurelink()" title="'. $txt['ultport_button_delete'] .'" href="'. $scripturl .'?action=admin;area=up-news;sa=delete-news;id='. $row['id'] .';sesc=' . $context['session_id'].'"><img alt="'. $txt['ultport_button_delete'] .'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/delete.png" /></a>';
		}
	}
	$smcFunc['db_free_result']($myquery);
}
//Load Block NEWS
function LoadBlockNews()
{
	global $context, $scripturl, $txt, $settings, $ultimateportalSettings, $memberContext, $smcFunc;

	// Load Language
	if (loadlanguage('UPNews') == false)
		loadLanguage('UPNews','english');

	//Prepare the constructPageIndex() function
	$start = (!empty($_REQUEST['sa']) && $_REQUEST['sa'] == 'news') ? (int) $_REQUEST['start'] : 0;
	$db_count = $smcFunc['db_query']('',"
					SELECT count(id)
					FROM {db_prefix}up_news
					ORDER BY id DESC"
	);
	$numNews = array();
	list($numNews) = $smcFunc['db_fetch_row']($db_count);
	$smcFunc['db_free_result']($db_count);

	$context['page_index'] = constructPageIndex($scripturl . '?sa=news', $start, $numNews, $ultimateportalSettings['up_news_limit']);

	// Calculate the fastest way to get the messages!
	$limit = $ultimateportalSettings['up_news_limit'];
	//End Prepare constructPageIndex() function

	//Load the NEWS
	$query = $smcFunc['db_query']('',"
					SELECT n.id, n.id_category, n.id_member, n.title, n.username, n.body, n.date, n.id_member_updated,
					n.username_updated, n.date_updated, s.id AS id_cat, s.title AS title_cat, s.icon
					FROM {db_prefix}up_news AS n
					LEFT JOIN {db_prefix}up_news_sections AS s ON(s.id = n.id_category)
					ORDER BY id DESC ". ($limit < 0 ? "" : "LIMIT $start, $limit ")
	);							

	while($row2 = $smcFunc['db_fetch_assoc']($query))
	{		
		$news = &$context['news'][];
		$news['id'] = $row2['id'];
		$news['title'] = '<a href="'. $scripturl .'?action=news;sa=view-new;id='. $row2['id'] .'">'. stripslashes($row2['title']) .'</a>';
		$news['id_member'] = $row2['id_member'];
		$news['username'] = $row2['username'];
		$news['author'] = '<a href="'. $scripturl .'?action=profile;u='. $row2['id_member'] .'">'. stripslashes($row2['username']) .'</a>';
		$news['date'] = timeformat($row2['date']);
		$news['added-news'] = $txt['up_module_news_added_portal_for'];
		$news['added-news'] = str_replace('[MEMBER]', $news['author'], $news['added-news']);
		$news['added-news'] = str_replace('[DATE]', $news['date'], $news['added-news']);
		$news['body'] = stripslashes($row2['body']);
		$news['id_member_updated'] = !empty($row2['id_member_updated']) ? $row2['id_member_updated'] : '';
		$news['username_updated'] = !empty($row2['username_updated']) ? $row2['username_updated'] : '';
		$news['author_updated'] = '<a href="'. $scripturl .'?action=profile;u='. $row2['id_member_updated'] .'">'. stripslashes($row2['username_updated']) .'</a>';
		$news['date_updated'] = !empty($row2['date_updated']) ? timeformat($row2['date_updated']) : '';
		$news['updated-news'] = !empty($news['id_member_updated']) ? $txt['up_module_news_updated_for'] : '';
		$news['updated-news'] = str_replace('[UPDATED_MEMBER]', $news['author_updated'], $news['updated-news']);
		$news['updated-news'] = str_replace('[UPDATED_DATE]', $news['date_updated'], $news['updated-news']);
		$news['view'] = '<img style="vertical-align: middle;" border="0" alt="'. $txt['ultport_button_view'] .'" src="'.$settings['default_images_url'].'/ultimate-portal/view.png" />&nbsp;<a href="'. $scripturl .'?action=news;sa=view-new;id='. $row2['id'] .'">'. $txt['ultport_button_view'] .'</a>';
		$news['edit'] = '<img style="vertical-align: middle;" alt="'. $txt['ultport_button_edit'] .'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/edit.png" />&nbsp;<a href="'. $scripturl .'?action=news;sa=edit-new;id='. $row2['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_edit'] .'</a>';
		$news['delete'] = '<img style="vertical-align: middle;" border="0" alt="'. $txt['ultport_button_delete'] .'" src="'.$settings['default_images_url'].'/ultimate-portal/delete.png" />&nbsp;<a onclick="return makesurelink()" href="'. $scripturl .'?action=news;sa=delete-new;id='. $row2['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_delete'] .'</a>';
		$news['id_cat'] = $row2['id_cat'];
		$news['title_cat'] = $row2['title_cat'];
		$news['icon'] = $row2['icon'];
	}
	$smcFunc['db_free_result']($query);
}
//addslashes before inserting data into database - Portal CP
function up_convert_savedbadmin($t="")
{
	$t = addslashes($t);
	//$t = get_magic_quotes_gpc() ? $t : addslashes($t);
	return $t;
}

//update the blocks perms
function up_update_block_perms($id)
{
	global $context, $smcFunc;

	$permissionsArray = array();
	if (isset($_POST['perms']))
	{
		foreach ($_POST['perms'] as $rgroup)
			$permissionsArray[] = (int) $rgroup;
	}
	$finalPermissions = implode(",",$permissionsArray);

	//Now UPDATE the Ultimate portal Blocks
	$smcFunc['db_query']('',"
		UPDATE {db_prefix}ultimate_portal_blocks
		SET	perms = {string:perms}
		WHERE id = {int:id}",
		array(
			'id' => $id,
			'perms' => $finalPermissions,
		)
	);
	//redirect the Blocks Admin
	redirectexit('action=admin;area=ultimate_portal_blocks;sa=admin-block;sesc=' . $context['session_id']);
}

//Load Image from Ultimate Portal image folder
function load_image_folder($folder = " ", $width = 'width="16"', $height = 'height="16"')
{
	global $context, $settings, $ultimateportalSettings, $boarddir;
   //extension
   $arr_ext=array("jpg","png","gif");
   //open folder dir
   $mydir=opendir($boarddir . "/Themes/default/images/ultimate-portal". $folder);
   //read files
	while($files=readdir($mydir))
	{
		$ext=substr($files,-3);
		$ext_selected = '.'.$ext;
		//si la extension del archivo es correcta muestra la imagen
		if(in_array($ext,$arr_ext) && ($ultimateportalSettings['ultimate_portal_icons_extention'] == $ext_selected))
		{
			$context['folder_images'][] = array(
				'file' => $files,
				'value' => str_replace('.'.$ext, "", $files),
				'image' => '<img '. $width .' '. $height .'src="'. $settings['default_images_url'] . '/ultimate-portal'. $folder . '/'. $files .'" alt="'. $files .'" title="'. $files .'" />',
			);
		}
	}
}

//Add extra headers from index.template.php
function context_html_headers()
{
	global $context, $settings;
	$context['html_headers'] .= '
		<!-- TinyMCE -->
		<script type="text/javascript" src="'. $settings['default_theme_url'] .'/up-editor/jscripts/tiny_mce/tiny_mce.js"></script>
		<script type="text/javascript">
			tinyMCE.init({
				// General options
				mode : "textareas",
                width : "550",
				theme : "advanced",
				plugins : "safari,layer,table,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,xhtmlxtras",
				extended_valid_elements : "iframe[src|width|height|name|align]",
				// Theme options
				theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,fontselect,fontsizeselect",
				theme_advanced_buttons2 : "bullist,numlist,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,preview,|,forecolor,backcolor",
				theme_advanced_buttons3 : "hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
				theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins",
				theme_advanced_toolbar_location : "top",
				theme_advanced_toolbar_align : "left",
				language : "es",
				theme_advanced_statusbar_location : "bottom",
				theme_advanced_resizing : true,
				// Replace values for the template plugin
				template_replace_values : {
					username : "Some User",
					staffid : "991234"
				}
			});
		</script>
		<!-- /TinyMCE -->';
}

//Load the Ultimate Portal Settings
function ultimateportalSettings()
{
	global $ultimateportalSettings, $smcFunc;

	$request = $smcFunc['db_query']('',"
		SELECT *
		FROM {db_prefix}ultimate_portal_settings"
	);

	$ultimateportalSettings = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))	
		$ultimateportalSettings[$row['variable']] = $row['value'];
	
	$smcFunc['db_free_result']($request);

	//Add extra value - The UltimatePortal Version
	$ultimateportalSettings['ultimate_portal_version'] = '0.4';
}

// Prints a post box.  Used everywhere you post or send.
function up_theme_postbox($msg, $post_box_name, $post_form)
{
	$post = up_template_control_richedit($post_box_name,'smileyBox_'.$post_box_name, 'bbcBox_'.$post_box_name);
	return $post;
}

// Creates a box that can be used for richedit stuff like BBC, Smileys etc.
function up_create_control_richedit($editorOptions)
{
	global $txt, $modSettings, $options, $smcFunc;
	global $context, $settings, $user_info, $sourcedir, $scripturl;

	require_once($sourcedir . '/Subs-Editor.php');
	// Load the Post language file... for the moment at least.
	loadLanguage('Post');

	// Every control must have a ID!
	assert(isset($editorOptions['id']));
	assert(isset($editorOptions['value']));

	// Is this the first richedit - if so we need to ensure some template stuff is initialised.
	if (empty($context['controls']['richedit']))
	{
		// Some general stuff.
		$settings['smileys_url'] = $modSettings['smileys_url'] . '/' . $user_info['smiley_set'];

		// This really has some WYSIWYG stuff.
		loadTemplate('GenericControls', $context['browser']['is_ie'] ? 'editor_ie' : 'editor');
		$context['html_headers'] .= '
		<script type="text/javascript"><!-- // --><![CDATA[
			var smf_smileys_url = \'' . $settings['smileys_url'] . '\';
			var oEditorStrings= {
				wont_work: \'' . addcslashes($txt['rich_edit_wont_work'], "'") . '\',
				func_disabled: \'' . addcslashes($txt['rich_edit_function_disabled'], "'") . '\',
				prompt_text_email: \'' . addcslashes($txt['prompt_text_email'], "'") . '\',
				prompt_text_ftp: \'' . addcslashes($txt['prompt_text_ftp'], "'") . '\',
				prompt_text_url: \'' . addcslashes($txt['prompt_text_url'], "'") . '\',
				prompt_text_img: \'' . addcslashes($txt['prompt_text_img'], "'") . '\'
			}
		// ]]></script>
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/editor.js"></script>';

		$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');
		if ($context['show_spellchecking'])
		{
			$context['html_headers'] .= '
				<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/spellcheck.js"></script>';

			// Some hidden information is needed in order to make the spell checking work.
			if (!isset($_REQUEST['xml']))
				$context['insert_after_template'] .= '
				<form name="spell_form" id="spell_form" method="post" accept-charset="' . $context['character_set'] . '" target="spellWindow" action="' . $scripturl . '?action=spellcheck">
					<input type="hidden" name="spellstring" value="" />
				</form>';

			// Also make sure that spell check works with rich edit.
			$context['html_headers'] .= '
				<script type="text/javascript"><!-- // --><![CDATA[
				function spellCheckDone()
				{
					for (i = 0; i < smf_editorArray.length; i++)
						setTimeout("smf_editorArray[" + i + "].spellCheckEnd()", 150);
				}
				// ]]></script>';
		}
	}

	// Start off the editor...
	$context['controls']['richedit'][$editorOptions['id']] = array(
		'id' => $editorOptions['id'],
		'value' => $editorOptions['value'],
		'rich_value' => addcslashes(bbc_to_html($editorOptions['value']), "'"),
		'rich_active' => empty($modSettings['disable_wysiwyg']) && (!empty($options['wysiwyg_default']) || !empty($editorOptions['force_rich']) || !empty($_REQUEST[$editorOptions['id'] . '_mode'])),
		'disable_smiley_box' => !empty($editorOptions['disable_smiley_box']),
		'columns' => isset($editorOptions['columns']) ? $editorOptions['columns'] : 60,
		'rows' => isset($editorOptions['rows']) ? $editorOptions['rows'] : 12,
		'width' => isset($editorOptions['width']) ? $editorOptions['width'] : '100%',
		'height' => isset($editorOptions['height']) ? $editorOptions['height'] : '150px',
		'form' => isset($editorOptions['form']) ? $editorOptions['form'] : 'postmodify',
		'bbc_level' => !empty($editorOptions['bbc_level']) ? $editorOptions['bbc_level'] : 'full',
		'preview_type' => isset($editorOptions['preview_type']) ? (int) $editorOptions['preview_type'] : 1,
		'labels' => !empty($editorOptions['labels']) ? $editorOptions['labels'] : array(),
	);

	// Switch between default images and back... mostly in case you don't have an PersonalMessage template, but do have a Post template.
	if (isset($settings['use_default_images']) && $settings['use_default_images'] == 'defaults' && isset($settings['default_template']))
	{
		$temp1 = $settings['theme_url'];
		$settings['theme_url'] = $settings['default_theme_url'];

		$temp2 = $settings['images_url'];
		$settings['images_url'] = $settings['default_images_url'];

		$temp3 = $settings['theme_dir'];
		$settings['theme_dir'] = $settings['default_theme_dir'];
	}

	if (empty($context['bbc_tags']))
	{
		// The below array makes it dead easy to add images to this control. Add it to the array and everything else is done for you!
		$context['bbc_tags'] = array();
		$context['bbc_tags'][] = array(
			array(
				'image' => 'bold',
				'code' => 'b',
				'before' => '[b]',
				'after' => '[/b]',
				'description' => $txt['bold'],
			),
			array(
				'image' => 'italicize',
				'code' => 'i',
				'before' => '[i]',
				'after' => '[/i]',
				'description' => $txt['italic'],
			),
			array(
				'image' => 'underline',
				'code' => 'u',
				'before' => '[u]',
				'after' => '[/u]',
				'description' => $txt['underline']
			),
			array(
				'image' => 'strike',
				'code' => 's',
				'before' => '[s]',
				'after' => '[/s]',
				'description' => $txt['strike']
			),
			array(),
			array(
				'image' => 'pre',
				'code' => 'pre',
				'before' => '[pre]',
				'after' => '[/pre]',
				'description' => $txt['preformatted']
			),
			array(
				'image' => 'left',
				'code' => 'left',
				'before' => '[left]',
				'after' => '[/left]',
				'description' => $txt['left_align']
			),
			array(
				'image' => 'center',
				'code' => 'center',
				'before' => '[center]',
				'after' => '[/center]',
				'description' => $txt['center']
			),
			array(
				'image' => 'right',
				'code' => 'right',
				'before' => '[right]',
				'after' => '[/right]',
				'description' => $txt['right_align']
			),
		);
		$context['bbc_tags'][] = array(
			array(
				'image' => 'flash',
				'code' => 'flash',
				'before' => '[flash=200,200]',
				'after' => '[/flash]',
				'description' => $txt['flash']
			),
			array(
				'image' => 'img',
				'code' => 'img',
				'before' => '[img]',
				'after' => '[/img]',
				'description' => $txt['image']
			),
			array(
				'image' => 'url',
				'code' => 'url',
				'before' => '[url]',
				'after' => '[/url]',
				'description' => $txt['hyperlink']
			),
			array(
				'image' => 'email',
				'code' => 'email',
				'before' => '[email]',
				'after' => '[/email]',
				'description' => $txt['insert_email']
			),
			array(
				'image' => 'ftp',
				'code' => 'ftp',
				'before' => '[ftp]',
				'after' => '[/ftp]',
				'description' => $txt['ftp']
			),
			array(),
			array(
				'image' => 'glow',
				'code' => 'glow',
				'before' => '[glow=red,2,300]',
				'after' => '[/glow]',
				'description' => $txt['glow']
			),
			array(
				'image' => 'shadow',
				'code' => 'shadow',
				'before' => '[shadow=red,left]',
				'after' => '[/shadow]',
				'description' => $txt['shadow']
			),
			array(
				'image' => 'move',
				'code' => 'move',
				'before' => '[move]',
				'after' => '[/move]',
				'description' => $txt['marquee']
			),
			array(),
			array(
				'image' => 'sup',
				'code' => 'sup',
				'before' => '[sup]',
				'after' => '[/sup]',
				'description' => $txt['superscript']
			),
			array(
				'image' => 'sub',
				'code' => 'sub',
				'before' => '[sub]',
				'after' => '[/sub]',
				'description' => $txt['subscript']
			),
			array(
				'image' => 'tele',
				'code' => 'tt',
				'before' => '[tt]',
				'after' => '[/tt]',
				'description' => $txt['teletype']
			),
			array(),
			array(
				'image' => 'table',
				'code' => 'table',
				'before' => '[table]\n[tr]\n[td]',
				'after' => '[/td]\n[/tr]\n[/table]',
				'description' => $txt['table']
			),
			array(
				'image' => 'code',
				'code' => 'code',
				'before' => '[code]',
				'after' => '[/code]',
				'description' => $txt['bbc_code']
			),
			array(
				'image' => 'quote',
				'code' => 'quote',
				'before' => '[quote]',
				'after' => '[/quote]',
				'description' => $txt['bbc_quote']
			),
			array(),
			array(
				'image' => 'list',
				'code' => 'list',
				'before' => '[list]\n[li]',
				'after' => '[/li]\n[li][/li]\n[/list]',
				'description' => $txt['list']
			),
			array(
				'image' => 'orderlist',
				'code' => 'orderlist',
				'before' => '[list type=decimal]\n[li]',
				'after' => '[/li]\n[li][/li]\n[/list]',
				'description' => $txt['list']
			),
			array(
				'image' => 'hr',
				'code' => 'hr',
				'before' => '[hr]',
				'description' => $txt['horizontal_rule']
			),
		);

		// Show the toggle?
		if (empty($modSettings['disable_wysiwyg']))
		{
			$context['bbc_tags'][count($context['bbc_tags']) - 1][] = array();
			$context['bbc_tags'][count($context['bbc_tags']) - 1][] = array(
				'image' => 'unformat',
				'code' => 'unformat',
				'before' => '',
				'description' => $txt['unformat_text'],
			);
			$context['bbc_tags'][count($context['bbc_tags']) - 1][] = array(
				'image' => 'toggle',
				'code' => 'toggle',
				'before' => '',
				'description' => $txt['toggle_view'],
			);
		}

		foreach ($context['bbc_tags'] as $row => $tagRow)
			$context['bbc_tags'][$row][count($tagRow) - 1]['isLast'] = true;
	}

	// Initialize smiley array... if not loaded before.
	if (empty($context['smileys']) && empty($editorOptions['disable_smiley_box']))
	{
		$context['smileys'] = array(
			'postform' => array(),
			'popup' => array(),
		);

		// Load smileys - don't bother to run a query if we're not using the database's ones anyhow.
		if (empty($modSettings['smiley_enable']) && $user_info['smiley_set'] != 'none')
			$context['smileys']['postform'][] = array(
				'smileys' => array(
					array(
						'code' => ':)',
						'filename' => 'smiley.gif',
						'description' => $txt['icon_smiley'],
					),
					array(
						'code' => ';)',
						'filename' => 'wink.gif',
						'description' => $txt['icon_wink'],
					),
					array(
						'code' => ':D',
						'filename' => 'cheesy.gif',
						'description' => $txt['icon_cheesy'],
					),
					array(
						'code' => ';D',
						'filename' => 'grin.gif',
						'description' => $txt['icon_grin']
					),
					array(
						'code' => '>:(',
						'filename' => 'angry.gif',
						'description' => $txt['icon_angry'],
					),
					array(
						'code' => ':(',
						'filename' => 'sad.gif',
						'description' => $txt['icon_sad'],
					),
					array(
						'code' => ':o',
						'filename' => 'shocked.gif',
						'description' => $txt['icon_shocked'],
					),
					array(
						'code' => '8)',
						'filename' => 'cool.gif',
						'description' => $txt['icon_cool'],
					),
					array(
						'code' => '???',
						'filename' => 'huh.gif',
						'description' => $txt['icon_huh'],
					),
					array(
						'code' => '::)',
						'filename' => 'rolleyes.gif',
						'description' => $txt['icon_rolleyes'],
					),
					array(
						'code' => ':P',
						'filename' => 'tongue.gif',
						'description' => $txt['icon_tongue'],
					),
					array(
						'code' => ':-[',
						'filename' => 'embarrassed.gif',
						'description' => $txt['icon_embarrassed'],
					),
					array(
						'code' => ':-X',
						'filename' => 'lipsrsealed.gif',
						'description' => $txt['icon_lips'],
					),
					array(
						'code' => ':-\\',
						'filename' => 'undecided.gif',
						'description' => $txt['icon_undecided'],
					),
					array(
						'code' => ':-*',
						'filename' => 'kiss.gif',
						'description' => $txt['icon_kiss'],
					),
					array(
						'code' => ':\'(',
						'filename' => 'cry.gif',
						'description' => $txt['icon_cry'],
						'isLast' => true,
					),
				),
				'isLast' => true,
			);
		elseif ($user_info['smiley_set'] != 'none')
		{
			if (($temp = cache_get_data('posting_smileys', 480)) == null)
			{
				$request = $smcFunc['db_query']('', '
					SELECT code, filename, description, smiley_row, hidden
					FROM {db_prefix}smileys
					WHERE hidden IN (0, 2)
					ORDER BY smiley_row, smiley_order',
					array(
					)
				);
				while ($row = $smcFunc['db_fetch_assoc']($request))
				{
					$row['filename'] = htmlspecialchars($row['filename']);
					$row['description'] = htmlspecialchars($row['description']);

					$context['smileys'][empty($row['hidden']) ? 'postform' : 'popup'][$row['smiley_row']]['smileys'][] = $row;
				}
				$smcFunc['db_free_result']($request);

				foreach ($context['smileys'] as $section => $smileyRows)
				{
					foreach ($smileyRows as $rowIndex => $smileys)
						$context['smileys'][$section][$rowIndex]['smileys'][count($smileys['smileys']) - 1]['isLast'] = true;

					if (!empty($smileyRows))
						$context['smileys'][$section][count($smileyRows) - 1]['isLast'] = true;
				}

				cache_put_data('posting_smileys', $context['smileys'], 480);
			}
			else
				$context['smileys'] = $temp;
		}
	}

	// Set a flag so the sub template knows what to do...
	$context['show_bbc'] = !empty($modSettings['enableBBC']) && !empty($settings['show_bbc']);

	// Generate a list of buttons that shouldn't be shown - this should be the fastest way to do this.
	$disabled_tags = array();
	if (!empty($modSettings['disabledBBC']))
		$disabled_tags = explode(',', $modSettings['disabledBBC']);
	if (empty($modSettings['enableEmbeddedFlash']))
		$disabled_tags[] = 'flash';

	foreach ($disabled_tags as $tag)
	{
		if ($tag == 'list')
			$context['disabled_tags']['orderlist'] = true;

		$context['disabled_tags'][trim($tag)] = true;
	}

	// Switch the URLs back... now we're back to whatever the main sub template is.  (like folder in PersonalMessage.)
	if (isset($settings['use_default_images']) && $settings['use_default_images'] == 'defaults' && isset($settings['default_template']))
	{
		$settings['theme_url'] = $temp1;
		$settings['images_url'] = $temp2;
		$settings['theme_dir'] = $temp3;
	}
}


// This function displays all the stuff you'd expect to see with a message box, the box, BBC buttons and of course smileys.
//Adapted, by vicram10, to display fine in "Ultimate Portal Modules"
function up_template_control_richedit($editor_id, $smileyContainer, $bbcContainer)
{
	global $context, $settings, $txt, $modSettings;

	$editor_context = &$context['controls']['richedit'][$editor_id];

	$content =  '
		<div>
			<div>
				<textarea class="editor" name="'. $editor_id .'" id="'. $editor_id .'" rows="'. $editor_context['rows'] .'" cols="'. $editor_context['columns'] .'" onselect="storeCaret(this);" onclick="storeCaret(this);" onkeyup="storeCaret(this);" onchange="storeCaret(this);" tabindex="'. $context['tabindex']++ .'" style="'. ($context['browser']['is_ie8'] ? 'max-width: ' . $editor_context['width'] . '; min-width: ' . $editor_context['width'] : 'width: ' . $editor_context['width'] ) .'; height: '. $editor_context['height'] .';">'. $editor_context['value'] .'</textarea>
			</div>
			<div id="'. $editor_id .'_resizer" style="display: none; '. ($context['browser']['is_ie8'] ? 'max-width: ' . $editor_context['width'] . '; min-width: ' . $editor_context['width'] : 'width: ' . $editor_context['width']) .'" class="richedit_resize"></div>
		</div>
		<input type="hidden" name="'. $editor_id .'_mode" id="'. $editor_id .'_mode" value="0" />
		<script type="text/javascript"><!-- // --><![CDATA[';

		// Show the smileys.
		if (!empty($smileyContainer))
		{
			$content .=  '
				var oSmileyBox_'. $editor_id. ' = new smc_SmileyBox({
					sUniqueId: '. JavaScriptEscape('smileyBox_' . $editor_id). ',
					sContainerDiv: '. JavaScriptEscape($smileyContainer). ',
					sClickHandler: '. JavaScriptEscape('oEditorHandle_' . $editor_id . '.insertSmiley'). ',
					oSmileyLocations: {';

			foreach ($context['smileys'] as $location => $smileyRows)
			{
				$content .=  '
						'. $location .': [';
				foreach ($smileyRows as $smileyRow)
				{
					$content .=  '
							[';
					foreach ($smileyRow['smileys'] as $smiley)
						$content .=  '
								{
									sCode: '. JavaScriptEscape($smiley['code']). ',
									sSrc: '. JavaScriptEscape($settings['smileys_url'] . '/' . $smiley['filename']). ',
									sDescription: '. JavaScriptEscape($smiley['description']). '
								}'. (empty($smiley['isLast']) ? ',' : '');

				$content .=  '
							]'. (empty($smileyRow['isLast']) ? ',' : '');
				}
				$content .=  '
						]'. ($location === 'postform' ? ',' : '');
			}
			$content .=  '
					},
					sSmileyBoxTemplate: '. JavaScriptEscape('
						%smileyRows% %moreSmileys%
					') .',
					sSmileyRowTemplate: '. JavaScriptEscape('
						<div>%smileyRow%</div>
					') .',
					sSmileyTemplate: '. JavaScriptEscape('
						<img src="%smileySource%" align="bottom" alt="%smileyDescription%" title="%smileyDescription%" id="%smileyId%" />
					') .',
					sMoreSmileysTemplate: '. JavaScriptEscape('
						<a href="#" id="%moreSmileysId%">[' . (!empty($context['smileys']['postform']) ? $txt['more_smileys'] : $txt['more_smileys_pick']) . ']</a>
					') .',
					sMoreSmileysLinkId: '. JavaScriptEscape('moreSmileys_' . $editor_id). ',
					sMoreSmileysPopupTemplate: '. JavaScriptEscape('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
						<html>
							<head>
								<title>' . $txt['more_smileys_title'] . '</title>
								<link rel="stylesheet" type="text/css" href="' . $settings['theme_url'] . '/css/index' . $context['theme_variant'] . '.css?rc2" />
							</head>
							<body id="help_popup">
								<div class="padding windowbg">
									<h3 class="catbg"><span class="left"></span>
										' . $txt['more_smileys_pick'] . '
									</h3>
									<div class="padding">
										%smileyRows%
									</div>
									<div class="smalltext centertext">
										<a href="#" id="%moreSmileysCloseLinkId%">' . $txt['more_smileys_close_window'] . '</a>
									</div>
								</div>
							</body>
						</html>') .'
				});';
		}

		if (!empty($bbcContainer))
		{
			$content .=  '
				var oBBCBox_'. $editor_id. ' = new smc_BBCButtonBox({
					sUniqueId: '. JavaScriptEscape('BBCBox_' . $editor_id). ',
					sContainerDiv: '. JavaScriptEscape($bbcContainer). ',
					sButtonClickHandler: '. JavaScriptEscape('oEditorHandle_' . $editor_id . '.handleButtonClick'). ',
					sSelectChangeHandler: '. JavaScriptEscape('oEditorHandle_' . $editor_id . '.handleSelectChange'). ',
					aButtonRows: [';

			// Here loop through the array, printing the images/rows/separators!
			foreach ($context['bbc_tags'] as $i => $buttonRow)
			{
				$content .=  '
						[';
				foreach ($buttonRow as $tag)
				{
					// Is there a "before" part for this bbc button? If not, it can't be a button!!
					if (isset($tag['before']))
						$content .=  '
							{
								sType: \'button\',
								bEnabled: '. (empty($context['disabled_tags'][$tag['code']]) ? 'true' : 'false'). ',
								sImage: '. JavaScriptEscape($settings['images_url'] . '/bbc/' . $tag['image'] . '.gif'). ',
								sCode: '. JavaScriptEscape($tag['code']). ',
								sBefore: '. JavaScriptEscape($tag['before']). ',
								sAfter: '. (isset($tag['after']) ? JavaScriptEscape($tag['after']) : 'null'). ',
								sDescription: '. JavaScriptEscape($tag['description']). '
							}'. (empty($tag['isLast']) ? ',' : '');

					// Must be a divider then.
					else
						$content .=  '
							{
								sType: \'divider\'
							}'. (empty($tag['isLast']) ? ',' : '');
				}

				// Add the select boxes to the first row.
				if ($i == 0)
				{
					// Show the font drop down...
					if (!isset($context['disabled_tags']['font']))
						$content .=  ',
							{
								sType: \'select\',
								sName: \'sel_face\',
								oOptions: {
									\'\': '. JavaScriptEscape($txt['font_face']) .',
									\'courier\': \'Courier\',
									\'arial\': \'Arial\',
									\'arial black\': \'Arial Black\',
									\'impact\': \'Impact\',
									\'verdana\': \'Verdana\',
									\'times new roman\': \'Times New Roman\',
									\'georgia\': \'Georgia\',
									\'andale mono\': \'Andale Mono\',
									\'trebuchet ms\': \'Trebuchet MS\',
									\'comic sans ms\': \'Comic Sans MS\'
								}
							}';

					// Font sizes anyone?
					if (!isset($context['disabled_tags']['size']))
						$content .=  ',
							{
								sType: \'select\',
								sName: \'sel_size\',
								oOptions: {
									\'\': '. JavaScriptEscape($txt['font_size']) .',
									\'1\': \'8pt\',
									\'2\': \'10pt\',
									\'3\': \'12pt\',
									\'4\': \'14pt\',
									\'5\': \'18pt\',
									\'6\': \'24pt\',
									\'7\': \'36pt\'
								}
							}';

					// Print a drop down list for all the colors we allow!
					if (!isset($context['disabled_tags']['color']))
						$content .=  ',
							{
								sType: \'select\',
								sName: \'sel_color\',
								oOptions: {
									\'\': '. JavaScriptEscape($txt['change_color']) .',
									\'black\': '. JavaScriptEscape($txt['black']) .',
									\'red\': '. JavaScriptEscape($txt['red']) .',
									\'yellow\': '. JavaScriptEscape($txt['yellow']) .',
									\'pink\': '. JavaScriptEscape($txt['pink']) .',
									\'green\': '. JavaScriptEscape($txt['green']) .',
									\'orange\': '. JavaScriptEscape($txt['orange']) .',
									\'purple\': '. JavaScriptEscape($txt['purple']) .',
									\'blue\': '. JavaScriptEscape($txt['blue']) .',
									\'beige\': '. JavaScriptEscape($txt['beige']) .',
									\'brown\': '. JavaScriptEscape($txt['brown']) .',
									\'teal\': '. JavaScriptEscape($txt['teal']) .',
									\'navy\': '. JavaScriptEscape($txt['navy']) .',
									\'maroon\': '. JavaScriptEscape($txt['maroon']) .',
									\'limegreen\': '. JavaScriptEscape($txt['lime_green']) .',
									\'white\': '. JavaScriptEscape($txt['white']) .'
								}
							}';
				}
				$content .=  '
						]'. ($i == count($context['bbc_tags']) - 1 ? '' : ',');
			}
			$content .=  '
					],
					sButtonTemplate: '. JavaScriptEscape('
						<img id="%buttonId%" src="%buttonSrc%" align="bottom" width="23" height="22" alt="%buttonDescription%" title="%buttonDescription%" />
					'). ',
					sButtonBackgroundImage: '. JavaScriptEscape($settings['images_url'] . '/bbc/bbc_bg.gif'). ',
					sButtonBackgroundImageHover: '. JavaScriptEscape($settings['images_url'] . '/bbc/bbc_hoverbg.gif'). ',
					sActiveButtonBackgroundImage: '. JavaScriptEscape($settings['images_url'] . '/bbc/bbc_hoverbg.gif'). ',
					sDividerTemplate: '. JavaScriptEscape('
						<img src="' . $settings['images_url'] . '/bbc/divider.gif" alt="|" style="margin: 0 3px 0 3px;" />
					'). ',
					sSelectTemplate: '. JavaScriptEscape('
						<select name="%selectName%" id="%selectId%" style="margin-bottom: 1ex; font-size: x-small;">
							%selectOptions%
						</select>
					'). ',
					sButtonRowTemplate: '. JavaScriptEscape('
						<div>%buttonRow%</div>
					'). '
				});';
		}


		// Now it's all drawn out we'll actually setup the box.
		$content .=  '
				var oEditorHandle_'. $editor_id. ' = new smc_Editor({
					sSessionId: '. JavaScriptEscape($context['session_id']). ',
					sSessionVar: '. JavaScriptEscape($context['session_var']). ',
					sFormId: '. JavaScriptEscape($editor_context['form']). ',
					sUniqueId: '. JavaScriptEscape($editor_id). ',
					bWysiwyg: '. ($editor_context['rich_active'] ? 'true' : 'false'). ',
					sText: '. JavaScriptEscape($editor_context['rich_active'] ? $editor_context['rich_value'] : ''). ',
					sEditWidth: '. JavaScriptEscape($editor_context['width']). ',
					sEditHeight: '. JavaScriptEscape($editor_context['height']). ',
					bRichEditOff: '. (empty($modSettings['disable_wysiwyg']) ? 'false' : 'true') .',
					oSmileyBox: '. (!empty($context['smileys']['postform']) && !$editor_context['disable_smiley_box'] && $smileyContainer !== null ? 'oSmileyBox_' . $editor_id : 'null'). ',
					oBBCBox: '. ($context['show_bbc'] && $bbcContainer !== null ? 'oBBCBox_' . $editor_id : 'null'). '
				});
				smf_editorArray[smf_editorArray.length] = oEditorHandle_'. $editor_id .';';

		$content .=  '
			// ]]></script>';
	return $content;
}

function up_loadJumpTo()
{
	global $smcFunc, $context, $user_info;
	// Based on the loadJumpTo() from SMF 1.1.X
	if (!empty($context['jump_to']))
		return;

	// Find the boards/cateogories they can see.
	$request = $smcFunc['db_query']('', "
		SELECT c.name AS cat_name, c.id_cat, b.id_board, b.name AS board_name, b.child_level
		FROM {db_prefix}boards AS b
		LEFT JOIN {db_prefix}categories AS c ON (c.id_cat = b.id_cat)
		WHERE {query_see_board}"
	);
	$context['jump_to'] = array();
	$this_cat = array('id' => -1);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if ($this_cat['id'] != $row['id_cat'])
		{
			$this_cat = &$context['jump_to'][];
			$this_cat['id'] = $row['id_cat'];
			$this_cat['name'] = $row['cat_name'];
			$this_cat['boards'] = array();
		}
		$this_cat['boards'][] = array(
			'id' => $row['id_board'],
			'name' => $row['board_name'],
			'child_level' => $row['child_level'],
			'is_current' => isset($context['current_board']) && $row['id_board'] == $context['current_board']
		);
	}
	$smcFunc['db_free_result']($request);
}

function ReturnCurrentUrl()
{
	$url = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
	return $url;
}

//Load Internal Page with a Condition = Disable Page?
function DisablePage($condition = "WHERE active = 'off'")
{
	global $context, $smcFunc;

	$db_count = $smcFunc['db_query']('',"
		SELECT *
		FROM {db_prefix}ultimate_portal_ipage
		". $condition .""
	);
	$context['disabled_page'] = $smcFunc['db_num_rows']($db_count);
	$smcFunc['db_free_result']($db_count);
}

//Load Internal Page with a Condition
function LoadInternalPage($id, $condition = "WHERE active = 'on'")
{
	global $context, $scripturl, $txt, $settings, $smcFunc, $ultimateportalSettings, $user_info;
	
	$id = !empty($id) ? (int)$id : '';
	
	if (empty($id))
	{
		//Prepare the constructPageIndex() function
		$num = 0;
		$start = (int) $_REQUEST['start'];
		$db_count = $smcFunc['db_query']('',"
			SELECT *
			FROM {db_prefix}ultimate_portal_ipage
			". $condition ."
			ORDER BY sticky DESC, id DESC "
		);
		while ($sql_count = $smcFunc['db_fetch_assoc']($db_count))
		{
			$perms = '';
			if ($sql_count['perms'])
				$perms =  $sql_count['perms'];

			if(empty($perms) || $user_info['is_admin'])
				$can_view = true;				
			else
			{	
				$perms = explode(',', $perms);
				$can_view = false;				
				foreach($user_info['groups'] as $group_id)
				{
					if(in_array($group_id, $perms)) 
						$can_view = true;
				}							
			}
			
			if ($can_view == true)			
				++$num;			
		}
		$smcFunc['db_free_result']($db_count);
		$context['num_rows'] = $num;
		$context['page_index'] = constructPageIndex($scripturl . '?action=internal-page', $start, $num, $ultimateportalSettings['ipage_limit']);
	}
	// Calculate the fastest way to get the messages!
	$limit = $ultimateportalSettings['ipage_limit'];
	//End Prepare constructPageIndex() function

	$context['view_ipage']	= !$user_info['is_admin'] ? 0 : 1;
	$myquery = $smcFunc['db_query']('',"
		SELECT *
		FROM {db_prefix}ultimate_portal_ipage
		". $condition ."
		". (!empty($id) ? " AND id = {int:id}" : "") ."
		ORDER BY sticky DESC, id DESC 
		". (($limit < 0 || !empty($id)) ? "LIMIT 1" : "LIMIT ".$start.", ".$limit." ")."",
		array(
			'id' => $id,
		)
	);
	
	if(!$smcFunc['db_num_rows']($myquery))
		fatal_lang_error('ultport_error_id_not_found',false);
	
	while($row = $smcFunc['db_fetch_assoc']($myquery))
	{
		$context['view_ipage'] = 1;
		$ipage = &$context['ipage'][];
		$ipage['id'] = $row['id'];
		$context['id'] = $row['id'];
		$ipage['title'] = '<a href="'. $scripturl .'?action=internal-page;sa='. (isset($context['is_inactive_page']) ? 'view-inactive' : 'view') .';id='. $row['id'] .'">'. stripslashes($row['title']) .'</a>';
		$context['title'] = $row['title'];
		if(!empty($id))
			$context['title'] = stripslashes($row['title']);
		$ipage['sticky'] = $row['sticky'];
		$context['sticky'] = $row['sticky'];
		$ipage['active'] = $row['active'];
		$context['active'] = $row['active'];
		$ipage['type_ipage'] = $row['type_ipage'];
		$context['type_ipage'] = $row['type_ipage'];
		$context['content'] = $row['content'];
		$ipage['content'] = $row['content'];
		$ipage['parse_content'] = ($row['type_ipage'] == 'html') ? stripslashes($row['content']) : parse_bbc($row['content']);
		//Can see the internal page?
		$perms = '';
		if (!empty($row['perms']))
		{
			$perms =  $row['perms'];
			$context['perms'] =  $row['perms'];
		}	
		$ipage['can_view'] = false;
		$context['can_view'] = false;
		if(empty($perms) || $user_info['is_admin'])
		{			
			$ipage['can_view'] = true;
			$context['can_view'] = true;
		}
		else
		{
			$perms = explode(',', $perms);
			foreach($user_info['groups'] as $group_id)
			if(in_array($group_id, $perms))
			{
				$ipage['can_view'] = true;
				$context['can_view'] = true;
			}
		}
		//End
		$ipage['column_left'] = $row['column_left'];
		$ipage['column_right'] = $row['column_right'];
		$context['column_left'] = $row['column_left'];
		$context['column_right'] = $row['column_right'];
		$ipage['date_created'] = timeformat($row['date_created']);
		$ipage['month_created'] = strftime('%m',$row['date_created']);
		$ipage['day_created'] = strftime('%d',$row['date_created']);
		$ipage['year_created'] = strftime('%y',$row['date_created']);
		$ipage['id_member'] = $row['id_member'];
		$ipage['username'] = $row['username'];
		$ipage['profile'] = '<a href="'. $scripturl .'?action=profile;u='. $row['id_member'] .'">'. $row['username'] .'</a>';
		$ipage['is_updated'] = !empty($row['date_updated']);
		$ipage['date_updated'] = timeformat($row['date_updated']);
		$ipage['id_member_updated'] = !empty($row['date_updated']) ? $row['id_member_updated'] : '';
		$ipage['username_updated'] = !empty($row['date_updated']) ? $row['username_updated'] : '';
		$ipage['profile_updated'] = !empty($row['date_updated']) ? '<a href="'. $scripturl .'?action=profile;u='. $row['id_member_updated'] .'">'. $row['username_updated'] .'</a>' : '';
		$ipage['read_more'] = '<strong><a href="'. $scripturl .'?action=internal-page;sa='. (isset($context['is_inactive_page']) ? 'view-inactive' : 'view') .';id='. $row['id'] .'">'. $txt['ultport_read_more'] .'</a></strong>';
		$ipage['edit'] = '<a href="'. $scripturl .'?action=internal-page;sa=edit;id='. $row['id'] .';sesc=' . $context['session_id'].'"><img alt="" style="vertical-align: middle;" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/edit.png" /></a>';
		$ipage['delete'] = '<a onclick="return makesurelink()" href="'. $scripturl .'?action=internal-page;sa=delete;id='. $row['id'] .';sesc=' . $context['session_id'].'"><img alt="" style="vertical-align: middle;" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/delete.png" />&nbsp;</a>';
	}
	$smcFunc['db_free_result']($myquery);
}

//Social Bookmarks
function UpSocialBookmarks($url)
{
	global $txt, $settings;

	//The language for this function, is in UltimatePortal.yourlanguage.php

	// Load Language
	if (loadlanguage('UltimatePortal') == false)
		loadLanguage('UltimatePortal','english');

	$twitter = 'http://twitter.com/home?status='. $txt['ultport_social_bookmarks_recommends'] .':%20';
	$facebook = 'http://www.facebook.com/share.php?u=';
	$delicious = 'http://del.icio.us/post?url=';
	$digg = 'http://digg.com/submit?phase=2&amp;url=';

	$social_bookmarks = '
		<table class="tborder" style="border: 1px solid" cellpadding="5" cellspacing="1" width="100%">
			<tr>
				<td valign="top" class="catbg" width="100%" align="left">
					<strong><u>'. $txt['ultport_social_bookmarks_share'] .'</u></strong>
				</td>
			</tr>
			<tr>
				<td valign="top" class="windowbg" width="100%" align="left">
					<a href="'.$facebook.''.$url.'" target="_blank"><img src="'.$settings['default_images_url'].'/ultimate-portal/social-bookmarks/facebook.png"  alt="Facebook" title="Facebook" /></a>
					<a href="'.$twitter.''.$url.'" target="_blank"><img src="'.$settings['default_images_url'].'/ultimate-portal/social-bookmarks/twitter.png" alt=" | Twitter" title="Twitter" /></a>
					<a href="'.$delicious.''.$url.'" target="_blank"><img src="'.$settings['default_images_url'].'/ultimate-portal/social-bookmarks/delicious.png" alt=" | del.icio.us" title="delicious" /></a>
					<a href="'.$digg.''.$url.'" target="_blank"><img src="'.$settings['default_images_url'].'/ultimate-portal/social-bookmarks/digg.png" alt=" | digg" title="digg" /></a>
				</td>
			</tr>
		</table>';

	return $social_bookmarks;
}

// Generate a strip of buttons.
function up_template_button_strip($button_strip, $direction = 'top', $strip_options = array())
{
	global  $txt, $user_info;

	if (!is_array($strip_options))
		$strip_options = array();

	// Create the buttons...
	$buttons = array();
	foreach ($button_strip as $key => $value)
	{
		if ((isset($value['condition']) && $value['condition']) || $user_info['is_admin'])
			$buttons[] = '<a ' . (isset($value['active']) ? 'class="active" ' : '') . 'href="' . $value['url'] . '" ' . (isset($value['custom']) ? $value['custom'] : '') . '><span>' . $txt[$value['text']] . '</span></a>';
	}

	// No buttons? No button strip either.
	if (empty($buttons))
		return;

	// Make the last one, as easy as possible.
	$buttons[count($buttons) - 1] = str_replace('<span>', '<span class="last">', $buttons[count($buttons) - 1]);

	$construct_button = '
		<div class="UPbuttonlist'. (!empty($direction) ? ' align_' . $direction : ''). '"'. ((empty($buttons) ? ' style="display: none;"' : '')) . ((!empty($strip_options['id']) ? ' id="' . $strip_options['id'] . '"': '')) . '>
			<ul>
				<li>'. implode('</li><li>', $buttons) .'</li>
			</ul>
		</div>';

	return $construct_button;
}

function warning_delete($text)
{
	$warning = "
	    <script type=\"text/javascript\">
			function makesurelink() {
				if (confirm('".$text."')) {
					return true;
				} else {
					return false;
				}
			}
	    </script>";

	return $warning;
}

//Board News Module
function upSSI_BoardNews($num_recent = 8, $exclude_boards = null, $include_boards = null, $length, $bkcall = '')
{
	global $context, $settings, $scripturl, $txt, $user_info, $modSettings, $smcFunc;

	loadLanguage('Stats');

	if ($exclude_boards === null && !empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0)
		$exclude_boards = array($modSettings['recycle_board']);
	else
		$exclude_boards = empty($exclude_boards) ? array() : (is_array($exclude_boards) ? $exclude_boards : array($exclude_boards));

	// Only some boards?.
	if (is_array($include_boards) || (int) $include_boards === $include_boards)
	{
		$include_boards = is_array($include_boards) ? $include_boards : array($include_boards);
	}
	elseif ($include_boards != null)
	{
		$output_method = $include_boards;
		$include_boards = array();
	}

	$stable_icons = array('xx', 'thumbup', 'thumbdown', 'exclamation', 'question', 'lamp', 'smiley', 'angry', 'cheesy', 'grin', 'sad', 'wink', 'moved', 'recycled', 'wireless');
	$icon_sources = array();
	foreach ($stable_icons as $icon)
		$icon_sources[$icon] = 'images_url';

	//Prepare the constructPageIndex() function
	$start = (!empty($_REQUEST['sa']) && $_REQUEST['sa'] == $bkcall.'boardnews') ? (int) $_REQUEST['start'] : 0;
	$db_count = $smcFunc['db_query']('', '
		SELECT count(m.id_topic)
		FROM {db_prefix}topics AS t
			INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
			INNER JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)
			INNER JOIN {db_prefix}messages AS ms ON (ms.id_msg = t.id_first_msg)
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = m.id_member)' . (!$user_info['is_guest'] ? '
			LEFT JOIN {db_prefix}log_topics AS lt ON (lt.id_topic = t.id_topic AND lt.id_member = {int:current_member})
			LEFT JOIN {db_prefix}log_mark_read AS lmr ON (lmr.id_board = b.id_board AND lmr.id_member = {int:current_member})' : '') . '
		WHERE m.id_topic > 0
			' . (empty($exclude_boards) ? '' : '
			AND b.id_board NOT IN ({array_int:exclude_boards})') . '
			' . (empty($include_boards) ? '' : '
			AND b.id_board IN ({array_int:include_boards})') . '
			AND {query_wanna_see_board}' . ($modSettings['postmod_active'] ? '
			AND t.approved = {int:is_approved}
			AND m.approved = {int:is_approved}' : '') . '
		ORDER BY t.id_first_msg DESC',
		array(
			'current_member' => $user_info['id'],
			'include_boards' => empty($include_boards) ? '' : $include_boards,
			'exclude_boards' => empty($exclude_boards) ? '' : $exclude_boards,
			'is_approved' => 1,
		)
	);

	$numNews = array();
	list($numNews) = $smcFunc['db_fetch_row']($db_count);
	$smcFunc['db_free_result']($db_count);

	$context['page_index'] = constructPageIndex($scripturl . '?sa='.$bkcall.'boardnews', $start, $numNews, $num_recent);

	// Find all the posts in distinct topics.  Newer ones will have higher IDs.
	$request = $smcFunc['db_query']('', '
		SELECT
			m.poster_time, ms.subject, m.id_topic, m.id_member, m.id_msg, b.id_board, b.name AS board_name, t.num_replies, t.num_views,
			t.locked,
			IFNULL(mem.real_name, m.poster_name) AS poster_name, ' . ($user_info['is_guest'] ? '1 AS is_read, 0 AS new_from' : '
			IFNULL(lt.id_msg, IFNULL(lmr.id_msg, 0)) >= m.id_msg_modified AS is_read,
			IFNULL(lt.id_msg, IFNULL(lmr.id_msg, -1)) + 1 AS new_from') . ', m.body AS body, m.smileys_enabled, m.icon
		FROM {db_prefix}topics AS t
			INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
			INNER JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)
			INNER JOIN {db_prefix}messages AS ms ON (ms.id_msg = t.id_first_msg)
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = m.id_member)' . (!$user_info['is_guest'] ? '
			LEFT JOIN {db_prefix}log_topics AS lt ON (lt.id_topic = t.id_topic AND lt.id_member = {int:current_member})
			LEFT JOIN {db_prefix}log_mark_read AS lmr ON (lmr.id_board = b.id_board AND lmr.id_member = {int:current_member})' : '') . '
		WHERE m.id_topic > 0
			' . (empty($exclude_boards) ? '' : '
			AND b.id_board NOT IN ({array_int:exclude_boards})') . '
			' . (empty($include_boards) ? '' : '
			AND b.id_board IN ({array_int:include_boards})') . '
			AND {query_wanna_see_board}' . ($modSettings['postmod_active'] ? '
			AND t.approved = {int:is_approved}
			AND m.approved = {int:is_approved}' : '') . '
		ORDER BY t.id_first_msg DESC
		 '. ($num_recent < 0 ? "" : " LIMIT {int:start}, {int:limit} ") .'',
		array(
			'current_member' => $user_info['id'],
			'include_boards' => empty($include_boards) ? '' : $include_boards,
			'exclude_boards' => empty($exclude_boards) ? '' : $exclude_boards,
			'is_approved' => 1,
			'start' => $start,
			'limit' => $num_recent,
		)
	);

	$return = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
			// If we want to limit the length of the post.
		if (!empty($length) && $smcFunc['strlen']($row['body']) > $length)
		{
			$row['body'] = $smcFunc['substr']($row['body'], 0, $length);

			// The first space or line break. (<br />, etc.)
			$cutoff = max(strrpos($row['body'], ' '), strrpos($row['body'], '<'));

			if ($cutoff !== false)
				$row['body'] = $smcFunc['substr']($row['body'], 0, $cutoff);
			$row['body'] .= '...';
		}

		$row['body'] = parse_bbc($row['body'], $row['smileys_enabled'], $row['id_msg']);

		// Censor the subject.
		censorText($row['subject']);
		censorText($row['body']);

		if (empty($modSettings['messageIconChecks_disable']) && !isset($icon_sources[$row['icon']]))
			$icon_sources[$row['icon']] = file_exists($settings['theme_dir'] . '/images/post/' . $row['icon'] . '.gif') ? 'images_url' : 'default_images_url';

		// Build the array.
		$return[] = array(
			'board' => array(
				'id' => $row['id_board'],
				'name' => $row['board_name'],
				'href' => $scripturl . '?board=' . $row['id_board'] . '.0',
				'link' => '<a href="' . $scripturl . '?board=' . $row['id_board'] . '.0">' . $row['board_name'] . '</a>'
			),
			'topic' => $row['id_topic'],
			'poster' => array(
				'id' => $row['id_member'],
				'name' => $row['poster_name'],
				'href' => empty($row['id_member']) ? '' : $scripturl . '?action=profile;u=' . $row['id_member'],
				'link' => empty($row['id_member']) ? $row['poster_name'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['poster_name'] . '</a>'
			),
			'subject' => $row['subject'],
			'replies' => $row['num_replies'],
			'views' => $row['num_views'],
			'short_subject' => shorten_subject($row['subject'], 25),
			'preview' => $row['body'],
			'time' => timeformat($row['poster_time']),
			'timestamp' => forum_time(true, $row['poster_time']),
			'href' => $scripturl . '?topic=' . $row['id_topic'] . '.msg' . $row['id_msg'] . ';topicseen#new',
			'link' => '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.msg' . $row['id_msg'] . '#new" rel="nofollow">' . $row['subject'] . '</a>',
			// Retained for compatibility - is technically incorrect!
			'new' => !empty($row['is_read']),
			'is_new' => empty($row['is_read']),
			'new_from' => $row['new_from'],
			'icon' => '<img src="' . $settings[$icon_sources[$row['icon']]] . '/post/' . $row['icon'] . '.gif" align="middle" alt="' . $row['icon'] . '" border="0" />',
			'comment_link' => !empty($row['locked']) ? '' : '<a href="' . $scripturl . '?action=post;topic=' . $row['id_topic'] . '.' . $row['num_replies'] . ';num_replies=' . $row['num_replies'] . '">' . $txt['ssi_write_comment'] . '</a>',
		);
	}
	$smcFunc['db_free_result']($request);

	//ok return now
	return $return;
}

//Load Multiblocks
function MultiBlocksLoads()
{
	global $settings, $context, $scripturl, $txt, $smcFunc;
	$context['mb_view'] = false;

	$request = $smcFunc['db_query']('', "
		SELECT id, title, blocks, position, design, mbk_title, mbk_collapse, mbk_style, enable
		FROM {db_prefix}up_multiblock
		ORDER BY id ASC"
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['mb_view'] = true;
		if ($context['mb_view'])
		{
			$context['multiblocks'][] = array(
				'id' => $row['id'],
				'title' => $row['title'],
				'blocks' => $row['blocks'],
				'position' => $row['position'],
				'design' => $row['design'],
				'mbk_title' => $row['mbk_title'],
				'mbk_collapse' => $row['mbk_collapse'],
				'mbk_style' => $row['mbk_style'],
				'enable' => $row['enable'],
				'edit' => '<a href="'. $scripturl .'?action=admin;area=multiblock;sa=edit;id='. $row['id'] .';'. $context['session_var'] .'=' . $context['session_id'].'"><img alt="" title="'. $txt['ultport_button_edit'] .'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/edit.png"/></a>',
				'delete' => '<a style="color:red" onclick="return makesurelink()" href="'. $scripturl .'?action=admin;area=multiblock;sa=delete;id='. $row['id'] .';'. $context['session_var'] .'=' . $context['session_id'].'"><img alt="" title="'. $txt['ultport_button_delete'] .'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/delete.png"/></a>',
			);
		}
	}
	$smcFunc['db_free_result']($request);
}

function LoadsBlocksForMultiBlock($view = false)
{
	global $context, $smcFunc;
	$result = $smcFunc['db_query']('',"
		SELECT id, file, title, icon, position, progressive, active, personal, content, perms, bk_collapse, bk_no_title, bk_style
		FROM {db_prefix}ultimate_portal_blocks
		". ($view ? "WHERE position in ('left', 'right', 'center')" : "") ."
		ORDER BY progressive"
	);
	while($row = $smcFunc['db_fetch_assoc']($result)) 
		$context['blocks'][] = $row;	
}

//Load Specific Multiblocks
function SpecificMultiBlocks($id)
{
	global $context, $smcFunc;	
	$id = !empty($id) ? (int)$id : '';	
	if(!empty($id))
	{
		$context['mb_view'] = false;
		$request = $smcFunc['db_query']('', "
			SELECT id, title, blocks, position, design, mbk_title, mbk_collapse, mbk_style, enable
			FROM {db_prefix}up_multiblock
			WHERE id = {int:id}
			ORDER BY id ASC
			LIMIT 1",
			array(
				'id' => $id,
			)						
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$context['mb_view'] = true;
			if ($context['mb_view'])
			{
				$context['multiblocks'][$id] = array(
					'id' => $row['id'],
					'title' => $row['title'],
					'blocks' => $row['blocks'],
					'position' => $row['position'],
					'design' => $row['design'],
					'mbk_title' => $row['mbk_title'],
					'mbk_collapse' => $row['mbk_collapse'],
					'mbk_style' => $row['mbk_style'],
					'enable' => $row['enable'],
				);
			}
		}
		$smcFunc['db_free_result']($request);
		//Order of Blocks
		$id_blocks = explode(',', $context['multiblocks'][$id]['blocks']);
		foreach($id_blocks as $bk)
		{
			$rbk = $smcFunc['db_query']('', "
				SELECT mbk_view
				FROM {db_prefix}ultimate_portal_blocks
				WHERE id = {int:bk}",
				array(
					'bk' => $bk
				)
			);
			while ($row = $smcFunc['db_fetch_assoc']($rbk))
			{
				$context['oblocks'][$bk] = array(
					'mbk_view' => $row['mbk_view'],
				);
			}
		}
		$smcFunc['db_free_result']($rbk);
	}
}