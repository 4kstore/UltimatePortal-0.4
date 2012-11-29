<?php
/*---------------------------------------------------------------------------------
*	Ultimate Portal
*	Version 0.3
*	Project manager: vicram10
*	Copyright 2011
*	Powered by SMFSimple.com
**********************************************************************************/

//Show the Ultimate Portal - Area: News / Section: Gral Settings
function template_news_main()
{
	global $context, $scripturl, $txt, $settings, $ultimateportalSettings;	
	echo'
	<div id="admincenter">
		<div class="cat_bar">
			<h3 class="catbg">
				<img alt="',$txt['ultport_admin_news_main_title'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/gral-settings.png"/>&nbsp;', $txt['ultport_admin_news_main_title'], '
			</h3>
		</div>
		<form name="newsform" method="post" action="', $scripturl, '?action=adminportal;area=up-news;sa=ns-main" accept-charset="',$context['character_set'],'">											
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="content">
					<dl class="settings">
						<dt>
							<label for="ultport_admin_news_enable">', $txt['ultport_admin_news_enable'], '</label>
						</dt>
						<dd>
							<input type="checkbox" value="1" name="up_news_enable" ',!empty($ultimateportalSettings['up_news_enable']) ? 'checked="checked"' : '',' />
						</dd>
					</dl>
					<hr class="hrcolor clear">
					<dl class="settings">
						<dt>
							<span><label for="ultport_admin_news_limit">', $txt['ultport_admin_news_limit'], '</label></span>
						</dt>
						<dd>
							<input type="text" name="up_news_limit" size="3" maxlength="3" ',!empty($ultimateportalSettings['up_news_limit']) ? 'value="'.$ultimateportalSettings['up_news_limit'].'"' : '','/>
						</dd>											
					</dl>
					<hr class="hrcolor clear" />
					<div class="righttext">
						<input type="hidden" name="save" value="ok" />
						<input type="hidden" name="sc" value="', $context['session_id'], '" />
						<input type="submit" name="',$txt['ultport_button_save'],'" value="',$txt['ultport_button_save'],'" />
					</div>	
				</div>
				<span class="botslice"><span></span></span>
			</div>
		</form>
	</div>';
}
//Show the Ultimate Portal - Area: News / Section: Announcement
function template_announcement()
{
	global $context, $scripturl, $txt, $settings;
	echo'
	<div id="admincenter">
		<div class="cat_bar">
			<h3 class="catbg">
				<img alt="" style="vertical-align: middle;" width="16" height="16" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/information.png"/>
					', $txt['ultport_admin_announcements_title'], '
			</h3>
		</div>
		<form name="newsform" method="post" action="', $scripturl, '?action=adminportal;area=up-news;sa=announcements" accept-charset="', $context['character_set'], '">											
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="content">
					', $txt['ultport_global_annoucements'], '
					<div id="bbcBox_message"></div>
					<div id="smileyBox_message"></div>
					', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message'),'
				</div>
				<div class="righttext">
					<input type="hidden" name="save" value="ok" />
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="',$txt['ultport_button_save'],'" value="',$txt['ultport_button_save'],'" />	
				</div>
			</div>
		</form>
	</div>';
}

//Show the Ultimate Portal - Area: News / Section: News Section
function template_news_section()
{
	global $context, $scripturl, $txt, $settings;
	
	echo"
	    <script type=\"text/javascript\">
			function makesurelink() {
				if (confirm('".$txt['ultport_delete_section_confirmation']."')) {
					return true;
				} else {
					return false;
				}
			}
	    </script>";
	echo'
	<div id="admincenter">		
		<table class="table_grid" cellspacing="0" width="100%">
			<thead>
				<tr class="catbg">
					<th width="5%" scope="col" class="first_th">', $txt['ultport_admin_news_sect_id'], '</th>
					<th width="5%" scope="col">', $txt['ultport_admin_news_sect_icon'], '</th>
					<th scope="col">', $txt['ultport_admin_news_sect_title'], '</th>
					<th width="5%" scope="col">', $txt['ultport_admin_news_sect_position'], '</th>
					<th width="5%" scope="col" class="last_th">', $txt['ultport_admin_news_sect_action'], '</th>
				</tr>
			</thead>			
			<tbody>';
			if(!empty($context['news_rows']))	
			{
				foreach ($context['news-section'] as $section)	
				{
					echo '
					<tr class="windowbg" id="news-section">
						<td style="text-align:center;">									
							', $section['id'] ,'
						</td>
						<td style="text-align:center;">										
							', $section['icon'] ,'
						</td>
						<td>									
							', $section['title'] ,'
						</td>
						<td style="text-align:center;">									
							', $section['position'] ,'
						</td>
						<td style="text-align:center;">									
							'.$section['edithref'].' '.$section['deletehref'].' 
						</td>
					</tr>';
				}
			}			
			echo'
			</tbody>			
		</table>';
		
		$normal_buttons = array(		
			'add_section' => array('text' => 'ultport_admin_add_sect_title', 'lang' => true, 'custom' => 'rel="new_win nofollow"', 'url' => $scripturl . '?action=adminportal;area=up-news;sa=add-section;'. $context['session_var'] .'=' . $context['session_id'].''),
		);		
	echo ''. template_button_strip($normal_buttons, 'right') .'	
	</div>';
	
}
//Show the Ultimate Portal - Area: News / Section: Add Section
function template_add_news_section()
{
	global $context, $scripturl, $txt, $settings;
	
	echo'
	<div id="admincenter">
		<div class="cat_bar">
			<h3 class="catbg">
				<img alt="',$txt['ultport_admin_add_sect_title'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/add.png"/>&nbsp;', $txt['ultport_admin_add_sect_title'], '
			</h3>
		</div>
		<form method="post" action="', $scripturl, '?action=adminportal;area=up-news;sa=add-section" accept-charset="', $context['character_set'], '">											
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="content">
					<dl class="settings">
						<dt>
							<label for="ultport_admin_news_add_sect_title">', $txt['ultport_admin_news_add_sect_title'], '</label>
						</dt>
						<dd>
							<input type="text" name="title" size="50" maxlength="100" />
						</dd>
						<dt>
							<label for="ultport_admin_news_add_sect_icon">', $txt['ultport_admin_news_add_sect_icon'], '</label>
						</dt>
						<dd>
							<input type="text" name="icon" size="50" maxlength="100" />
						</dd>
						<dt>
							<label for="ultport_admin_news_add_sect_position">', $txt['ultport_admin_news_add_sect_position'], '</label>
						</dt>
						<dd>
							<input type="text" name="position" size="3" value="', $context['last_position'] ,'" maxlength="3" />
						</dd>
					</dl>
					<hr class="hrcolor clear" />
					<div class="righttext">
						<input type="hidden" name="save" value="ok" />	
						<input type="hidden" name="sc" value="', $context['session_id'], '" />
						<input type="submit" name="',$txt['ultport_button_add'],'" value="',$txt['ultport_button_add'],'" />
					</div>	
				</div>
				<span class="botslice"><span></span></span>
			</div>
		</form>
	</div>';
}

//Show the Ultimate Portal - Area: News / Section: Edit Section
function template_edit_news_section()
{
	global $context, $scripturl, $txt, $settings;
	
	echo'
	<div id="admincenter">
		<div class="cat_bar">
			<h3 class="catbg">
				<img alt="',$txt['ultport_admin_edit_sect_title'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/edit.png"/>&nbsp;', $txt['ultport_admin_edit_sect_title'], '
			</h3>
		</div>
		<form method="post" action="', $scripturl, '?action=adminportal;area=up-news;sa=edit-section" accept-charset="', $context['character_set'], '">											
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="content">
					<dl class="settings">
						<dt>
							<label for="ultport_admin_news_add_sect_title">', $txt['ultport_admin_news_add_sect_title'], '</label>
						</dt>
						<dd>
							<input type="text" name="title" size="50" value="', $context['title'] ,'" maxlength="100" />
						</dd>
						<dt>
							<label for="ultport_admin_news_add_sect_icon">', $txt['ultport_admin_news_add_sect_icon'], '</label>
						</dt>
						<dd>
							<input type="text" name="icon" size="50" value="', $context['icon'] ,'"  maxlength="100" />
						</dd>
						<dt>
							<label for="ultport_admin_news_add_sect_position">', $txt['ultport_admin_news_add_sect_position'], '</label>
						</dt>
						<dd>
							<input type="text" name="position" size="3" value="', $context['position'] ,'" maxlength="3" />
						</dd>
					</dl>
					<hr class="hrcolor clear" />
					<div class="righttext">
						<input type="hidden" name="save" value="ok" />						
						<input type="hidden" name="id" value="', $context['id'] ,'" />	
						<input type="hidden" name="sc" value="', $context['session_id'], '" />
						<input type="submit" name="',$txt['ultport_button_edit'],'" value="',$txt['ultport_button_edit'],'" />
					</div>	
				</div>
				<span class="botslice"><span></span></span>
			</div>
		</form>
	</div>';
}

//Show the Ultimate Portal - Area: News / Section: Admin News
function template_admin_news()
{
	global $context, $scripturl, $txt, $settings;
	echo"
	<script type=\"text/javascript\">
		function makesurelink() {
			if (confirm('".$txt['ultport_delete_news_confirmation']."')) {
				return true;
			} else {
				return false;
			}
		}
	</script>";
		
	echo'
	<div id="admincenter">
		<div>
			<div class="pagesection">'. $txt['pages'] .': '. $context['page_index'] .' </div>
		</div>
		<table class="table_grid" cellspacing="0" width="100%">
			<thead>
				<tr class="catbg">
					<th width="5%" scope="col" class="first_th">', $txt['ultport_admin_news_sect_id'], '</th>
					<th scope="col">', $txt['ultport_admin_add_news_title'], '</th>
					<th scope="col">', $txt['ultport_admin_add_news_sect_title'], '</th>
					<th width="5%" scope="col" class="last_th">', $txt['ultport_admin_news_sect_action'], '</th>
				</tr>
			</thead>			
			<tbody>';
			if(!empty($context['load_news_admin']))	
			{
				foreach ($context['news-admin'] as $news)	
				{
					echo '
					<tr class="windowbg" id="news-section">
						<td style="text-align:center;">									
							', $news['id'] ,'
						</td>
						<td>									
							', $news['title-edit'] ,'
						</td>
						<td>									
							', $news['title-section'] ,'
						</td>
						<td style="text-align:center;">									
							', $news['edithref'] ,' ', $news['deletehref'] ,'
						</td>
					</tr>';
				}
			}
			
			echo'
			</tbody>			
		</table>
		<div>
			<div class="pagesection">'. $txt['pages'] .': '. $context['page_index'] .' </div>
		</div>';
		
		//button strip
		$normal_buttons = array(		
				'add_news' => array('text' => 'ultport_admin_add_news_btn', 'lang' => true, 'custom' => 'rel="new_win nofollow"', 'url' => $scripturl . '?action=adminportal;area=up-news;sa=add-news;'. $context['session_var'] .'=' . $context['session_id'].''),
		);		
		echo template_button_strip($normal_buttons, 'right');
		//end button strip
			
	echo '
	</div>';
}

function template_add_news()
{
	global $context, $txt, $scripturl, $user_info;
	echo'
	<div id="admincenter">
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['ultport_admin_add_news_title2'], '
			</h3>
		</div>
		<form method="post" action="', $scripturl, '?action=adminportal;area=up-news;sa=add-news" accept-charset="', $context['character_set'], '">										
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="content">
					<dl class="settings">
						<dt>
							<span><label for="ultport_admin_add_news_title">', $txt['ultport_admin_add_news_title'], '</label></span>
						</dt>
						<dd>
							<input type="text" name="title" size="85" />
						</dd>
						<dt>
							<span><label for="ultport_admin_add_news_section">', $txt['ultport_admin_add_news_section'], '</label></span>
						</dt>
						<dd>
							<select size="1" name="id_cat">
								', $context['section'] ,'
							</select>
						</dd>										
					</dl>
					<textarea id="elm1" name="elm1"></textarea>
					<hr class="hrcolor clear" />
					<div class="righttext">
						<input type="hidden" name="save" value="ok" />						
						<input type="hidden" name="id_member" value="', $user_info['id'] ,'" />						
						<input type="hidden" name="username" value="', $user_info['username'] ,'" />
						<input type="hidden" name="sc" value="', $context['session_id'], '" />
						<input type="submit" name="',$txt['ultport_button_add'],'" value="',$txt['ultport_button_add'],'" />
					</div>	
				</div>
				<span class="botslice"><span></span></span>
			</div>
		</form>
	</div>';
}
function template_edit_news()
{
	global $context, $txt, $scripturl, $user_info;
	echo'
	<div id="admincenter">
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['ultport_admin_edit_news_title'], '
			</h3>
		</div>
		<form method="post" action="', $scripturl, '?action=adminportal;area=up-news;sa=add-news" accept-charset="', $context['character_set'], '">										
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="content">
					<dl class="settings">
						<dt>
							<span><label for="ultport_admin_add_news_title">', $txt['ultport_admin_add_news_title'], '</label></span>
						</dt>
						<dd>
							<input type="text" value="', $context['title'] ,'" name="title" size="85" />
						</dd>
						<dt>
							<span><label for="ultport_admin_add_news_section">', $txt['ultport_admin_add_news_section'], '</label></span>
						</dt>
						<dd>
							<select size="1" name="id_cat">
								', $context['section-edit'] ,'
							</select>
						</dd>										
					</dl>
					<textarea id="elm1" name="elm1" rows="15" cols="80" style="width: 100%">', $context['body'] ,'</textarea>
					<hr class="hrcolor clear" />
					<div class="righttext">
						<input type="hidden" name="save" value="ok" />						
						<input type="hidden" name="id" value="', $context['id'] ,'" />											
						<input type="hidden" name="id_member_updated" value="', $user_info['id'] ,'" />						
						<input type="hidden" name="username_updated" value="', $user_info['username'] ,'" />
						<input type="hidden" name="sc" value="', $context['session_id'], '" />
						<input type="submit" name="',$txt['ultport_button_edit'],'" value="',$txt['ultport_button_edit'],'" />
					</div>	
				</div>
				<span class="botslice"><span></span></span>
			</div>
		</form>
	</div>';
}
//Show the Ultimate Portal - Area: Board News / Section: Gral Settings
function template_board_news_main()
{
	global $context, $scripturl, $txt, $settings,  $ultimateportalSettings;	
	echo'
	<div id="admincenter">
		<div class="cat_bar">
			<h3 class="catbg">
				<img alt="',$txt['ultport_admin_bn_main_title'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/gral-settings.png"/>&nbsp;', $txt['ultport_admin_bn_main_title'], '
			</h3>
		</div>
		<form method="post" action="', $scripturl, '?action=adminportal;area=board-news;sa=bn-main" accept-charset="', $context['character_set'], '">											
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="content">
					<dl class="settings">
						<dt>
							<span><label for="ultport_admin_bn_limit">', $txt['ultport_admin_bn_limit'], '</label></span>
						</dt>
						<dd>
							<input type="text" name="board_news_limit" size="3" maxlength="3" ',!empty($ultimateportalSettings['board_news_limit']) ? 'value="'.$ultimateportalSettings['board_news_limit'].'"' : '','/>
						</dd>
						<dt>
							<span><label for="ultport_admin_bn_lenght">', $txt['ultport_admin_bn_lenght'], '</label></span>
						</dt>
						<dd>
							<input type="text" name="board_news_lenght" size="4" maxlength="5" ',!empty($ultimateportalSettings['board_news_lenght']) ? 'value="'.$ultimateportalSettings['board_news_lenght'].'"' : '','/>
						</dd>
						<dt>
							<span><label for="ultport_admin_bn_view">', $txt['ultport_admin_bn_view'], '</label></span>
						</dt>
						<dd>
							<select name="boards[]" size="10" multiple="multiple" style="width: 88%;">';
							$id_boards = explode(',',$ultimateportalSettings['board_news_view']);			
								echo'
								<option value="0" ' ,isset($id_boards) ? (in_array(0, $id_boards) ? 'selected="selected"' : '') : '', '>' .$txt['ultport_admin_bn_select_all']. '</option>';
							foreach ($context['jump_to'] as $category)
							{
								echo '
								<option disabled="disabled">----------------------------------------------------</option>
								<option disabled="disabled">', $category['name'], '</option>
								<option disabled="disabled">----------------------------------------------------</option>';
								foreach ($category['boards'] as $board)
									echo '
									<option value="' ,$board['id'], '" ' ,isset($id_boards) ? (in_array($board['id'], $id_boards) ? 'selected="selected"' : '') : '', '> ' . str_repeat('&nbsp;&nbsp;&nbsp; ', $board['child_level']) . '|--- ' . $board['name'] . '</option>';
							}
							echo '
							</select>
						</dd>						
					</dl>
					<hr class="hrcolor clear" />
					<div class="righttext">
						<input type="hidden" name="bn-save" value="ok" />	
						<input type="hidden" name="sc" value="', $context['session_id'], '" />
						<input type="submit" name="',$txt['ultport_button_save'],'" value="',$txt['ultport_button_save'],'" />
					</div>	
				</div>
				<span class="botslice"><span></span></span>
			</div>
		</form>
	</div>';
}

//Show the Ultimate Portal - Area: Internal Page / Section: Gral Settings
function template_ipage_main()
{
	global $context, $scripturl, $txt, $settings, $ultimateportalSettings;	
	echo'
	<div id="admincenter">
		<div class="cat_bar">
			<h3 class="catbg">
				<img alt="',$txt['ipage_settings_title'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/gral-settings.png"/>&nbsp;', $txt['ipage_settings_title'],'
			</h3>
		</div>
		<form method="post" action="', $scripturl, '?action=adminportal;area=internal-page;sa=main" accept-charset="', $context['character_set'], '">												
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="content">
					<dl class="settings">
						<dt>
							<label for="ipage_enable">', $txt['ipage_enable'], '</label>
						</dt>
						<dd>
							<input type="checkbox" value="on" name="ipage_enable" ',!empty($ultimateportalSettings['ipage_enable']) ? 'checked="checked"' : '',' />
						</dd>
					</dl>
					<hr class="hrcolor clear">
					<dl class="settings">
						<dt>
							<span><label for="ipage_active_columns">', $txt['ipage_active_columns'], '</label></span>
						</dt>
						<dd>
							<input type="checkbox" value="on" name="ipage_active_columns" ',!empty($ultimateportalSettings['ipage_active_columns']) ? 'checked="checked"' : '',' />
						</dd>
						<dt>
							<span><label for="ipage_limit">', $txt['ipage_limit'], '</label></span>
						</dt>
						<dd>
							<input type="text" name="ipage_limit" size="5" maxlength="4" ',!empty($ultimateportalSettings['ipage_limit']) ? 'value="'.$ultimateportalSettings['ipage_limit'].'"' : 'value="10"','/>
						</dd>
						<dt>
							<span><label for="ipage_social_bookmarks">', $txt['ipage_social_bookmarks'], '</label></span>
						</dt>
						<dd>
							<input type="checkbox" value="on" name="ipage_social_bookmarks" ',!empty($ultimateportalSettings['ipage_social_bookmarks']) ? 'checked="checked"' : '',' />
						</dd>						
					</dl>
					<hr class="hrcolor clear" />
					<div class="righttext">
						<input type="hidden" name="save" value="ok" />
						<input type="hidden" name="sc" value="', $context['session_id'], '" />
						<input type="submit" name="',$txt['ultport_button_save'],'" value="',$txt['ultport_button_save'],'" />
					</div>	
				</div>
				<span class="botslice"><span></span></span>
			</div>
		</form>
	</div>';
}