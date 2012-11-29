<?php
/*---------------------------------------------------------------------------------
*	Ultimate Portal
*	Version 0.4
*	Project manager: vicram10
*	Copyright 2011
*	Powered by SMFSimple.com
**********************************************************************************/
function template_main()
{
	global $context, $scripturl, $txt, $settings, $ultimateportalSettings, $user_info;
	
	$content = "
		<script type=\"text/javascript\">
			function makesurelink() {
				if (confirm('".$txt['ultport_delete_confirmation']."')) {
					return true;
				} else {
					return false;
				}
			}
		</script>";

	// Create the button set...
	$normal_buttons = array(
		'add_html' => array('condition' => ($user_info['is_admin'] || !empty($user_info['up-modules-permissions']['ipage_add'])), 'text' => 'up_ipage_add_html', 'lang' => true, 'url' => $scripturl .'?action=internal-page;sa=add;type=html;'. $context['session_var'] .'=' . $context['session_id'].''),
		'add_bbc' => array('condition' => ($user_info['is_admin'] || !empty($user_info['up-modules-permissions']['ipage_add'])), 'text' => 'up_ipage_add_bbc', 'lang' => true, 'url' => $scripturl .'?action=internal-page;sa=add;type=bbc;'. $context['session_var'] .'=' . $context['session_id'].''),
	);
	$content .= '
		<div class="UPpagesection">
			'. up_template_button_strip($normal_buttons, 'right') .'
		</div>';

	if(!empty($context['disabled_page']) && $user_info['is_admin'])
	{
		$content .='
		<div style="border: 1px solid;">
			<div id="warning">
				<img alt="" width="30" height="30" title="'. $txt['up_module_ipage_title'] .'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/stop.png"/>
					'. str_replace("IPAGE_URL", $scripturl . '?action=internal-page;sa=inactive' ,$txt['up_ipage_disabled_any_ipage']) .'
			</div>
		</div>';
	}

	$content .= '
		<br />
			<strong>'. $txt['pages'] .':</strong> '. $context['page_index'] .'
		<br /><br />';

	if (!empty($context['view_ipage']))
	{

		foreach ($context['ipage'] as $ipage)
		{
			if ($ipage['can_view'] === true)
			{
				$content .= '
				<div class="ip_view_content">
					<div class="ip_view_title">
						'. $ipage['title'] .' '. (!empty($ipage['sticky']) ? '<span class="ip_view_content_sticky">Sticky</span>' : '') .'
					</div>
					<div class="ip-updateinfo" style="text-align:left;">
						'. $ipage['day_created'] .'/'. $ipage['month_created'] .'/'. $ipage['year_created'] .'
						'. $txt['up_ipage_member'] .' '. $ipage['profile'] .' '. (($user_info['is_admin'] || !empty($user_info['up-modules-permissions']['ipage_moderate'])) ? '&nbsp;'. $ipage['edit'] . ' '. $ipage['delete'] : '') .'
					</div>
				</div>
				';
			}
		}
	}

	$copyright = '<div style="font-size: 0.8em;"><a href="http://www.smfsimple.com">Ultimate Portal - '. $txt['up_module_ipage_title'] .'</a>';
	$left = 0;
	$right = 0;

	if (!empty($ultimateportalSettings['ipage_active_columns']))
	{
		$left = 1;
		$right = 1;
	}

	up_print_page($left, $right, $content, '', 'internal-page', $txt['up_module_ipage_title']);
}

//Show the Ultimate Portal - Module Internal Page - View
function template_view()
{
	global $context, $txt, $settings, $ultimateportalSettings, $user_info;

	$content = "
	    <script type=\"text/javascript\">
			function makesurelink() {
				if (confirm('".$txt['ultport_delete_confirmation']."')) {
					return true;
				} else {
					return false;
				}
			}
	    </script>";

	if (!empty($context['view_ipage']))
	{
		foreach ($context['ipage'] as $ipage)
		{
			$content .= '
					<div class="post-date">
						<p class="day">
							'. $ipage['day_created'] .'/'. $ipage['month_created'] .'/'. $ipage['year_created'] .'
						</p>
					</div>
					<div class="post-info">
						<p class="author alignleft">
							'. $txt['up_ipage_member'] .' '. $ipage['profile'] .'
						</p>
						<p class="moderate alignright">

							'. (($user_info['is_admin'] || $user_info['up-modules-permissions']['ipage_moderate']) ? $ipage['edit'] .' | '. $ipage['delete'] : '') .'
						</p>
					</div>
					<div class="ip-content">
						<div>
							<h2 class="titleipage">
								'. $ipage['title'] .''. (!empty($ipage['sticky']) ? '&nbsp;&nbsp;<img alt="" style="vertical-align:middle" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/internal-page/sticky.gif" />' : '') .'
							</h2>
						</div>
						<div class="ip-body">
							'. $ipage['parse_content'] .'
						</div>
						'.($ipage['is_updated'] ? '
							<div class="ip-updateinfo">
								<strong>'. $txt['up_ipage_date_updated'] .'</strong> '. $ipage['date_updated'] .'
								<strong>'. $txt['up_ipage_member_updated'] .'</strong> '. $ipage['profile_updated'] .'
							</div>' : '').'
							<div class="ip-share">
								<strong>'. $txt['ultport_social_bookmarks_share'] .':</strong>
							</div>
							'. (!empty($ultimateportalSettings['ipage_social_bookmarks']) ? $context['social_bookmarks'] : '') .'

					</div>
					';
		}
	}

	$copyright = '<div style="font-size: 0.8em;"><a href="http://www.smfsimple.com">Ultimate Portal - '. $txt['up_module_ipage_title'] .'</a>';
	up_print_page($context['column_left'], $context['column_right'], $content, $copyright, 'internal-page', $context['title']);
}
//Form for Edit Internal Page
function template_add()
{
	global $context, $txt, $scripturl, $ultimateportalSettings;

	$content.='
	<div style="border: 1px dashed" cellpadding="5" cellspacing="1" width="100%"><strong>'. $context['news-linktree'] .'</strong></div>
	<form name="ipageform" method="post" action="'. $scripturl .'?action=internal-page;sa=add" accept-charset="'. $context['character_set'] .'">
		<div class="windowbg2">
			<div class="content">
				<dl class="settings">
					<dt>
						<label for="up_ipage_title">'. $txt['up_ipage_title'] .'</label>
					</dt>
					<dd>
						<input type="text" name="title" size="50" maxlength="100" />
					</dd>
					<dt>
						<label for="ipage_column_left">'.$txt['ipage_column_left'].'</label>
					</dt>
					<dd>
						<input type="checkbox" value="1" name="column_left" />
					</dd>
					<dt>
						<label for="ipage_column_right">'.$txt['ipage_column_right'].'</label>
					</dt>
					<dd>
						<input type="checkbox" value="1" name="column_right"/>
					</dd>
				</dl>';
				if ($context['type_ipage'] == 'html')
					$content .='
					<div><textarea id="elm1" name="elm1"></textarea></div>';

				//Internal Page BBC
				if ($context['type_ipage'] == 'bbc')
				{
					$content .='
					<div id="'. $context['bbcBox_container'] .'"></div>
					<div id="'. $context['smileyBox_container'] .'"></div>
					'. up_template_control_richedit($context['post_box_name'],$context['smileyBox_container'],$context['bbcBox_container']) .'';
				}
					$content .='
				<dl class="settings">
					<hr class="hrcolor clear" />
					<dt>
						<label for="up_ipage_perms">'.$txt['up_ipage_perms'].'</label>
					</dt>
					<dd>';

					if(!empty($context['groups']))
					{
						foreach ($context['groups'] as $group)
							$content .='
							<input type="checkbox" name="perms[]" value="'. $group['id_group'] .'" id="groups_'. $group['id_group'] .'"/>'. $group['group_name'] .'<br />';
						$content .='
						<input type="checkbox" onclick="invertAll(this, this.form, \'perms[]\');" /> <i>'. $txt['ultport_button_select_all'] .'</i><br />';
					}

					$content .='
					</dd>
					<hr class="hrcolor clear" />
					<dt>
						<label for="up_ipage_active">'.$txt['up_ipage_active'].'</label>
					</dt>
					<dd>
						<input type="checkbox" value="on" name="active"/>
					</dd>
					<dt>
						<label for="up_ipage_sticky">'.$txt['up_ipage_sticky'].'</label>
					</dt>
					<dd>
						<input type="checkbox" value="1" name="sticky"/>
					</dd>
				</dl>
				<hr class="hrcolor clear" />
				<div class="righttext">
					<input type="hidden" name="save" value="ok" />
						<input type="hidden" name="sc" value="'. $context['session_id'] .'" />
						<input type="hidden" name="type_ipage" value="'. $context['type_ipage'] .'" />
						<input type="submit" name="'.$txt['ultport_button_add'].'" value="'.$txt['ultport_button_add'].'" />
				</div>

			</div>
		</div>
	</form>';
	//The News Module Copyright - PLEASE NOT REMOVE
	$copyright = '';
	$left = 0;
	$right = 0;
	if (!empty($ultimateportalSettings['ipage_active_columns']))
	{
		$left = 1;
		$right = 1;
	}
	//Now Print the PAGE
	up_print_page($left, $right, $content, $copyright, 'internal-page', $txt['up_ipage_add_title']);
}
//Form for Edit Internal Page
function template_edit()
{
	global $context, $txt, $scripturl, $ultimateportalSettings;
	$content .='
	<div style="border: 1px dashed" cellpadding="5" cellspacing="1" width="100%"><strong>'. $context['news-linktree'] .'</strong></div>
	<form name="ipageform" method="post" action="'. $scripturl .'?action=internal-page;sa=edit;id='.$context['id'].'" accept-charset="'. $context['character_set'] .'">
		<div class="windowbg2">
			<div class="content">
				<dl class="settings">
					<dt>
						<label for="up_ipage_title">'. $txt['up_ipage_title'] .'</label>
					</dt>
					<dd>
						<input type="text" name="title" value="'. $context['title'] .'" size="50" maxlength="100" />
					</dd>
					<dt>
						<label for="ipage_column_left">'.$txt['ipage_column_left'].'</label>
					</dt>
					<dd>
						<input type="checkbox" '. (!empty($context['column_left']) ? 'checked="checked"' : '') .' value="1" name="column_left" />
					</dd>
					<dt>
						<label for="ipage_column_right">'.$txt['ipage_column_right'].'</label>
					</dt>
					<dd>
						<input type="checkbox" '. (!empty($context['column_right']) ? 'checked="checked"' : '') .' value="1" name="column_right" />
					</dd>
				</dl>';
				if ($context['type_ipage'] == 'html')
					$content .='
					<div><textarea id="elm1" name="elm1" rows="15" cols="80" style="width: 100%">'. $context['content'] .'</textarea></div>';

				//Internal Page BBC
				if ($context['type_ipage'] == 'bbc')
				{
					$content .='
					<div id="'. $context['bbcBox_container'] .'"></div>
					<div id="'. $context['smileyBox_container'] .'"></div>
					'. up_template_control_richedit($context['post_box_name'],$context['smileyBox_container'],$context['bbcBox_container']).'';
				}
					$content .='
				<dl class="settings">
					<hr class="hrcolor clear" />
					<dt>
						<label for="up_ipage_perms">'.$txt['up_ipage_perms'].'</label>
					</dt>
					<dd>';

					if(!empty($context['groups']))
					{
						$permissionsGroups = explode(',',$context['perms']);
						foreach ($context['groups'] as $group)
							$content .='
							<input type="checkbox" name="perms[]" value="'. $group['id_group'] .'" id="groups_'. $group['id_group'] .'"'. ((in_array($group['id_group'],$permissionsGroups) == true) ? ' checked="checked" ' : '') .'/>'. $group['group_name'] .'<br />';
						$content .='
						<input type="checkbox" onclick="invertAll(this, this.form, \'perms[]\');" /> <i>'. $txt['ultport_button_select_all'] .'</i><br />';
					}

					$content .='
					</dd>
					<hr class="hrcolor clear" />
					<dt>
						<label for="up_ipage_active">'.$txt['up_ipage_active'].'</label>
					</dt>
					<dd>
						<input type="checkbox" '. (($context['active'] == 'on') ? 'checked="checked"' : '') .' value="on" name="active"/>
					</dd>
					<dt>
						<label for="up_ipage_sticky">'.$txt['up_ipage_sticky'].'</label>
					</dt>
					<dd>
						<input type="checkbox" '. (!empty($context['sticky']) ? 'checked="checked"' : '') .' value="1" name="sticky"/>
					</dd>
				</dl>
				<hr class="hrcolor clear" />
				<div class="righttext">
					<input type="hidden" name="save" value="ok" />
						<input type="hidden" name="save" value="ok" />
						<input type="hidden" name="type_ipage" value="'. $context['type_ipage'] .'" />
						<input type="hidden" name="id_ipage" value="'. $context['id'] .'" />
						<input type="hidden" name="sc" value="'. $context['session_id'] .'" />
						<input type="submit" name="'.$txt['ultport_button_edit'].'" value="'.$txt['ultport_button_edit'].'" />
				</div>
			</div>
		</div>
	</form>';
	$copyright = '';
	$left = 0;
	$right = 0;
	if (!empty($ultimateportalSettings['ipage_active_columns']))
	{
		$left = 1;
		$right = 1;
	}
	//Now Print the PAGE
	up_print_page($left, $right, $content, $copyright, 'internal-page', $txt['up_ipage_edit_title']);
}