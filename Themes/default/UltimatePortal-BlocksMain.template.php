<?php
/*---------------------------------------------------------------------------------
*	Ultimate Portal
*	Version 0.4
*	Project manager: vicram10
*	Copyright 2011
*	Powered by SMFSimple.com
**********************************************************************************/

function template_positions()
{
	global $context, $txt, $settings, $scripturl;
	
	echo'	
	<div class="cat_bar">
		<h3 class="catbg">
			<img alt="',$txt['ultport_blocks_title'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/config.png"/>&nbsp;', $txt['ultport_blocks_title'], '
		</h3>
	</div>
	<div id="admincenter">	
	<form method="post" action="', $scripturl, '?action=adminportal;area=ultimate_portal_blocks;sa=save-positions" accept-charset="', $context['character_set'], '">
		<table class="table_grid" cellspacing="0" width="100%">
			<thead>
				<tr class="catbg">
					<th width="33%" scope="col" class="first_th"><img alt="',$txt['ultport_blocks_left'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/left.png"/>&nbsp;', $txt['ultport_blocks_left'], '</th>
					<th width="33%" scope="col"><img alt="',$txt['ultport_blocks_center'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/center.png"/>&nbsp;', $txt['ultport_blocks_center'], '</th>
					<th width="33%" scope="col" class="last_th">', $txt['ultport_admin_mainlinks_active'], '</th>
				</tr>
			</thead>			
			<tbody>
				<tr>
					<td width="33%" valign="top" class="windowbg2">';
		if(!empty($context['exists_left']))
		{				
			foreach($context['block-left'] as $block_left)
			{	
				echo '
						<!-- block table -->
						<table class="',$block_left['activestyle'],'" cellspacing="0" style="width: 100%; margin-bottom: 5px;">
							<tr>
								<td width="43%" align="left" style="margin: 0px; border: 0px; padding: 0px;">
									<select size="1" name="',$block_left['position_form'],'">
										<option value="left">',$block_left['position'],'</option>
										<option value="left">',$txt['ultport_blocks_left'],'</option>n
										<option value="center">',$txt['ultport_blocks_center'],'</option>n
										<option value="right">',$txt['ultport_blocks_right'],'</option>n
									</select>
								</td>
								<td width="23%" align="left" style="margin: 0px; border: 0px; padding: 0px;">
									<select size="1" name="',$block_left['progressive_form'],'">
										<option value="', $block_left['progressive'] ,'">', $block_left['progressive'] ,'</option>
										', $context['left-progoption'] ,'
									</select>
								</td>
								<td width="33%" align="left" style="margin: 0px; border: 0px; padding: 0px;">
									',$txt['ultport_blocks_enable'],' <input type="checkbox" name="',  $block_left['active_form'] ,'" value="checked" ', $block_left['active'] ,' />
								</td>
							</tr>
							<tr>
							<td colspan="3" align="left" style="margin: 0px; border: 0px; padding: 0px;">
								<input class="',$block_left['activestyle'],'" type="text" name="', $block_left['title_form'] ,'" size="40" value="', $block_left['title'] ,'" style="border: 0; float: left" readonly="readonly" />
							</td>
						</tr>
						</table>
						<!-- end block table -->
						';				
			}
		}
		
		
	echo	'
					</td>
					<td width="33%" valign="top" class="windowbg2">';				
		if(!empty($context['exists_center']))
		{
			foreach($context['block-center'] as $block_center)
			{	
				echo '
						<!-- block table -->
						<table class="',$block_center['activestyle'],'" style="width: 98%; margin-bottom: 5px">
							<tr>
								<td width="43%" align="left" style="margin: 0px; border: 0px; padding: 0px;">
									<select size="1" name="',$block_center['position_form'],'">
										<option value="center">',$block_center['position'],'</option>
										<option value="left">',$txt['ultport_blocks_left'],'</option>n
										<option value="center">',$txt['ultport_blocks_center'],'</option>n
										<option value="right">',$txt['ultport_blocks_right'],'</option>n
									</select>
								</td>
								<td width="23%" align="left" style="margin: 0px; border: 0px; padding: 0px;">
									<select size="1" name="',$block_center['progressive_form'],'">
										<option value="', $block_center['progressive'] ,'">', $block_center['progressive'] ,'</option>
										', $context['center-progoption'] ,'
									</select>
								</td>
								<td width="33%" align="left" style="margin: 0px; border: 0px; padding: 0px;">
									',$txt['ultport_blocks_enable'],' <input type="checkbox" name="',  $block_center['active_form'] ,'" value="checked" ', $block_center['active'] ,' />
								</td>
							</tr>
							<tr>
							<td colspan="3" align="left" style="margin: 0px; border: 0px; padding: 0px;">
								<input class="',$block_center['activestyle'],'" type="text" name="', $block_center['title_form'] ,'" size="40" value="', $block_center['title'] ,'" style="border: 0; float: left" readonly="readonly" />
							</td>
						</tr>
						</table>
						<!-- end block table -->
						';				
			}
		}
	echo	'
				</td>
				<td width="33%" valign="top" class="windowbg2">';
		if(!empty($context['exists_right']))
		{
			foreach($context['block-right'] as $block_right)
			{	
				echo '
						<!-- block table -->
						<table class="',$block_right['activestyle'],'" style="width: 98%; margin-bottom: 5px"> 
							<tr>
								<td width="43%" align="left" style="margin: 0px; border: 0px; padding: 0px;">
									<select size="1" name="',$block_right['position_form'],'">
										<option value="right">',$block_right['position'],'</option>
										<option value="left">',$txt['ultport_blocks_left'],'</option>n
										<option value="center">',$txt['ultport_blocks_center'],'</option>n
										<option value="right">',$txt['ultport_blocks_right'],'</option>n
									</select>
								</td>
								<td width="23%" align="left" style="margin: 0px; border: 0px; padding: 0px;">
									<select size="1" name="',$block_right['progressive_form'],'">
										<option value="', $block_right['progressive'] ,'">', $block_right['progressive'] ,'</option>
										', $context['right-progoption'] ,'
									</select>
								</td>
								<td width="33%" align="left" style="margin: 0px; border: 0px; padding: 0px;">
									',$txt['ultport_blocks_enable'],' <input type="checkbox" name="',  $block_right['active_form'] ,'" value="checked" ', $block_right['active'] ,' />
								</td>
							</tr>
							<tr>
							<td colspan="3" align="left" style="margin: 0px; border: 0px; padding: 0px;">
								<input class="',$block_right['activestyle'],'" type="text" name="', $block_right['title_form'] ,'" size="40" value="', $block_right['title'] ,'" style="border: 0; float: left" readonly="readonly" />
							</td>
						</tr>
						</table>
						<!-- end block table -->
						';				
			}
		}
	echo	'
				</td>
			</tr>			
			</tbody>			
		</table>
		
	</div>';
		

	//Multiblock Header
	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			', $txt['ultport_mb_multiheader'] ,'			
		</h3>
	</div>';
	if(!empty($context['exists_multiheader']))
	{	
		echo '
		<table align="center" width="100%">';
		$alternate = true;
		$i = 1; //flag
		$column = 3;
		foreach($context['block-header'] as $block_header)
		{	
			echo '
			<tr>
				<td colspan="3" align="left">
					<div class="title_bar">
						<h3 class="titlebg">
							', $block_header['id'] ,' - ', $block_header['mbtitle'] ,'
						</h3>
					</div>
				</td>
			</tr>
			<tr>';
			foreach($block_header['vblocks'] as $vblocks)
			{
				echo '
				<!-- block table -->
				<td width="33%" align="top">	
					<div class="', $vblocks['activestyle'] ,'">
						<span class="topslice"><span></span></span>
						<div class="content">
							<table class="',$vblocks['activestyle'],'" style="width: 98%; margin-bottom: 5px">
								<tr>
									<td width="43%" align="left">
										<select readonly="readonly" size="1" name="',$vblocks['position_form'],'">
											<option value="header">',$vblocks['position'],'</option>
										</select>
									</td>
									<td width="23%" align="left">
										<select size="1" name="',$vblocks['progressive_form'],'">
											<option value="', $vblocks['progressive'] ,'">', $vblocks['progressive'] ,'</option>
											', $context['header-progoption-'.$block_header['id']] ,'
										</select>
									</td>
									<td width="33%" align="left">
										',$txt['ultport_blocks_enable'],' <input type="checkbox" name="',  $vblocks['active_form'] ,'" value="checked" ', $vblocks['active'] ,' />				
									</td>
								</tr>
								<tr>
									<td colspan="3" align="left">
										<input class="',$vblocks['activestyle'],'" type="text" name="', $vblocks['title_form'] ,'" size="40" value="', $vblocks['title'] ,'" style="border: 0; float: left" readonly="readonly" />
										<span style="float: right">', $vblocks['id'] ,'</span>
									</td>
								</tr>
							</table>	
						</div>
						<span class="botslice"><span></span></span>
					</div>
				</td>';
				$alternate = !$alternate;
				$i++;
				if ($i==$column+1)
				{
					echo '</tr><tr>';
					$i=1;
				} 
			}
			echo '</tr>';		
		}
		echo '
		</table><br />';
	}

	//Multiblock footer
	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			', $txt['ultport_mb_footer'] ,'			
		</h3>
	</div>';
	if(!empty($context['exists_footer']))
	{	
		echo '
		<table align="center" width="100%">';
		$alternate = true;
		$i = 1; //flag
		$column = 3;
		foreach($context['block-footer'] as $block_footer)
		{	
			echo '
			<tr>
				<td colspan="3" align="left">
					<div class="title_bar">
						<h3 class="titlebg">
							', $block_footer['id'] ,' - ', $block_footer['mbtitle'] ,'
						</h3>
					</div>
				</td>
			</tr>
			<tr>';
			foreach($block_footer['vblocks'] as $vblocks)
			{
				echo '
				<!-- block table -->
				<td width="33%" align="top">	
					<div class="', $vblocks['activestyle'] ,'">
						<span class="topslice"><span></span></span>
						<div class="content">
							<table class="',$vblocks['activestyle'],'" style="width: 98%; margin-bottom: 5px">
								<tr>
									<td width="43%" align="left">
										<select readonly="readonly" size="1" name="',$vblocks['position_form'],'">
											<option value="footer">',$vblocks['position'],'</option>
										</select>
									</td>
									<td width="23%" align="left">
										<select size="1" name="',$vblocks['progressive_form'],'">
											<option value="', $vblocks['progressive'] ,'">', $vblocks['progressive'] ,'</option>
											', $context['footer-progoption-'.$block_footer['id']] ,'
										</select>
									</td>
									<td width="33%" align="left">
										',$txt['ultport_blocks_enable'],' <input type="checkbox" name="',  $vblocks['active_form'] ,'" value="checked" ', $vblocks['active'] ,' />				
									</td>
								</tr>
								<tr>
									<td colspan="3" align="left">
										<input class="',$vblocks['activestyle'],'" type="text" name="', $vblocks['title_form'] ,'" size="40" value="', $vblocks['title'] ,'" style="border: 0; float: left" readonly="readonly" />
										<span style="float: right">', $vblocks['id'] ,'</span>
									</td>
								</tr>
							</table>	
						</div>
						<span class="botslice"><span></span></span>
					</div>
				</td>';
				$alternate = !$alternate;
				$i++;
				if ($i==$column+1)
				{
					echo '</tr><tr>';
					$i=1;
				} 
			}
			echo '</tr>';		
		}
		echo '
		</table><br />';
	}
	
	echo '
		<table width="100%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td align="center" colspan="3">
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="hidden" name="save" value="ok" />						
					<input type="submit" name="',$txt['ultport_button_save'],'" value="',$txt['ultport_button_save'],'" />
				</td>
			</tr>
		</table>
	</form>';

}

function template_blocks_titles()
{
	global $context, $txt, $settings, $scripturl;
	echo'	
	<div class="cat_bar">
		<h3 class="catbg">
			<img alt="',$txt['ultport_admin_portal_menu_title'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/link.png"/>&nbsp;', $txt['ultport_admin_portal_menu_title'], '
		</h3>
	</div>
	<div id="admincenter">	
		<form method="post" action="', $scripturl, '?action=adminportal;area=ultimate_portal_blocks;sa=save-blocks-titles" accept-charset="', $context['character_set'], '">	
			<table class="table_grid" cellspacing="0" width="100%">
				<thead>
					<tr class="catbg">
						<th width="5%" scope="col" class="first_th">', $txt['ultport_blocks_titles_id'], '</th>
						<th width="45%" scope="col">', $txt['ultport_blocks_titles_original_title'], '</th>
						<th width="50%" scope="col" class="last_th">', $txt['ultport_blocks_titles_custom_title'], '</th>
					</tr>
				</thead>			
				<tbody>';
				if(!empty($context['block-title']))	
				{
					foreach($context['block-title'] as $block_title)
					{
						echo '
						<tr class="windowbg" id="news-section">
							<td class="',$block_title['activestyle'],'" style="text-align:center;">									
								', $block_title['id'] ,'
							</td>
							<td class="',$block_title['activestyle'],'">									
								', $block_title['title'] ,'
							</td>
							<td class="',$block_title['activestyle'],'">									
								<input type="text" name="', $block_title['title_block'] ,'" size="85" value="', !empty($block_title['title']) ? $block_title['title'] : '' , '" />
							</td>
						</tr>';
					}
				}
				echo'
				<td class="windowbg" align="right" colspan="8">
						<input type="hidden" name="save" value="ok" />
						<input type="hidden" name="sc" value="', $context['session_id'], '" />
						<input type="submit" name="',$txt['ultport_button_edit'],'" value="',$txt['ultport_button_edit'],'" />
				</td>	
				</tbody>			
			</table>
		</form>
	</div>';
}

function template_create_blocks()
{
	global $context, $txt, $settings, $scripturl;	
	
	echo'
	<div id="admincenter">
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['ultport_create_blocks_titles'], '
			</h3>
		</div>
		
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
			<div class="content">
				<table width="30%" align="center" class="tborder" cellspacing="1" cellpadding="5" border="0">
					<tr>
						<td width="50%" align="center">
							<a href="', $scripturl, '?action=adminportal;area=ultimate_portal_blocks;sa=add-block-html;sesc=' . $context['session_id'].'"><img alt="" style="cursor:pointer" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/page-html.png"/></a>
							<br />									
							<strong>', $txt['ultport_creat_bk_html_title'], '</strong>
						</td>			
						<td width="50%" align="center">
							<a href="', $scripturl, '?action=adminportal;area=ultimate_portal_blocks;sa=add-block-php;sesc=' . $context['session_id'].'"><img alt="" style="cursor:pointer" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/page-php.png"/></a>
							<br />									
							<strong>', $txt['ultport_creat_bk_php_title'], '</strong>
						</td>			
					</tr>				
				</table>					
			</div>	
			<span class="botslice"><span></span></span>	
		</div>
	</div>';
}

function template_add_block_html()
{
	global $context, $txt, $settings, $scripturl;
	echo'
	<form method="post" action="', $scripturl, '?action=adminportal;area=ultimate_portal_blocks;sa=add-block-html" accept-charset="', $context['character_set'], '">	
	<div id="admincenter">
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['ultport_add_bk_html_titles'], '
			</h3>
		</div>												
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
			<div class="content">
				<dl class="settings">
					<dt>
						<span><label for="ultport_add_bk_title">', $txt['ultport_add_bk_title'], '</label></span>
					</dt>
					<dd>
						<input type="text" name="bk-title" size="85" />
					</dd>';
				if(!empty($context['folder_images']))
				{
					echo'	
					<dt>
						<span><label for="ultport_add_bk_icon">', $txt['ultport_add_bk_icon'], '</label></span>
					</dt>				
					<dd>
						<table width="100%">
							<tr>';									
							$i = 1;
							foreach($context['folder_images'] as $folder)
							{
								echo '
								<td>
									<input value="'. $folder['value'] .'" type="radio" name="icon">&nbsp;', $folder['image'] . '
								</td>';
								$i++;
								if($i==6)
								{
									echo '</tr><tr>';
									$i = 1;
								}
							}
					echo '</tr>
						</table>
					</dd>						
				</dl>';
				}
				echo'
				<div style="margin: 10px;" align="center">
					<textarea id="elm1" name="elm1"></textarea>
				</div>
				<dl class="settings">
					<dt>
						<span><label for="ultport_add_bk_collapse">', $txt['ultport_add_bk_collapse'], '</label></span>
					</dt>
					<dd>
						<input type="checkbox" name="can_collapse" value="on" />
					</dd>
					<dt>
						<span><label for="ultport_add_bk_style">', $txt['ultport_add_bk_style'], '</label></span>
					</dt>
					<dd>
						<input type="checkbox" name="bk_style" value="on" />
					</dd>
					<dt>
						<span><label for="ultport_add_bk_no_title">', $txt['ultport_add_bk_no_title'], '</label></span>
					</dt>
					<dd>
						<input type="checkbox" name="no_title" value="on" />
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
	</div>
	</form>';
}

function template_add_block_php()
{
	global $context, $txt, $settings, $scripturl;
	
	//Preview
	if(!empty($context['preview']))
	{
		echo '
		<table align="center" width="70%">
			<tr>
				<td>';
					head_block($context['icon'], $context['title'], -10, $context['bk_collapse'], $context['bk_no_title'], $context['bk_style']);
					eval($context['content']);
					footer_block($context['bk_style']);
		echo '			
				</td>
			</tr>
		</table><br />';
	}
	//End Preview
	echo'
	<form method="post" action="', $scripturl, '?action=adminportal;area=ultimate_portal_blocks;sa=add-block-php" accept-charset="', $context['character_set'], '">
	<div id="admincenter">
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['ultport_add_bk_php_titles'], '
			</h3>
		</div>												
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
			<div class="content">
				<dl class="settings">
					<dt>
						<span><label for="ultport_add_bk_title">', $txt['ultport_add_bk_title'], '</label></span>
					</dt>
					<dd>
						<input type="text" value="', $context['title'] ,'" name="bk-title" size="50" />
					</dd>';
				if(!empty($context['folder_images']))
				{
					echo'	
					<dt>
						<span><label for="ultport_add_bk_icon">', $txt['ultport_add_bk_icon'], '</label></span>
					</dt>				
					<dd>
						<table width="100%">
							<tr>';									
							$i = 1;
							foreach($context['folder_images'] as $folder)
							{
								echo '
								<td>
									<input value="'. $folder['value'] .'" type="radio" name="icon">&nbsp;', $folder['image'] . '
								</td>';
								$i++;
								if($i==6)
								{
									echo '</tr><tr>';
									$i = 1;
								}
							}
					echo '</tr>
						</table>
					</dd>						
				</dl>';
				}
				echo'
				<div style="margin: 10px;" align="center">
					<textarea id="content" name="content" rows="20" cols="80" style="width: 99.2%">', $context['content'] ,'</textarea>
				</div>
				<dl class="settings">
					<dt>
						<span><label for="ultport_add_bk_collapse">', $txt['ultport_add_bk_collapse'], '</label></span>
					</dt>
					<dd>
						<input type="checkbox" name="can_collapse" value="on" ', !empty($context['bk_collapse']) ? 'checked="checked"' : '' ,' />
					</dd>
					<dt>
						<span><label for="ultport_add_bk_style">', $txt['ultport_add_bk_style'], '</label></span>
					</dt>
					<dd>
						<input type="checkbox" name="bk_style" value="on" ', !empty($context['bk_style']) ? 'checked="checked"' : '' ,' />
					</dd>
					<dt>
						<span><label for="ultport_add_bk_no_title">', $txt['ultport_add_bk_no_title'], '</label></span>
					</dt>
					<dd>
						<input type="checkbox" name="no_title" value="on" ', !empty($context['bk_no_title']) ? 'checked="checked"' : '' ,' />
					</dd>
				</dl>	
				<hr class="hrcolor clear" />
				<div class="righttext">
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="save" value="', $txt['ultport_button_add'] ,'" />&nbsp;
					<input type="submit" name="preview" value="', $txt['ultport_button_preview'] ,'" />
				</div>	
			</div>	
			<span class="botslice"><span></span></span>	
		</div>		
	</div>
	</form>';
}

function template_admin_block()
{
	global $context, $txt, $settings, $scripturl;

	echo "
	    <script type=\"text/javascript\">
			function makesurelink() {
				if (confirm('".$txt['ultport_delete_confirmation']."')) {
					return true;
				} else {
					return false;
				}
			}
	    </script>";
	echo'	
	<div class="cat_bar">
		<h3 class="catbg">
			', $txt['ultport_admin_bk_custom'], '
		</h3>
	</div>
	<div id="admincenter">	
		<table class="table_grid" cellspacing="0" width="100%">
			<thead>
				<tr class="catbg">
					<th width="5%" scope="col" class="first_th">', $txt['ultport_blocks_titles_id'], '</th>
					<th width="5%" scope="col">', $txt['ultport_admin_bk_type'], '</th>
					<th scope="col">', $txt['ultport_add_bk_title'], '</th>
					<th scope="col">', $txt['ultport_button_permission'], '</th>
					<th width="10%" scope="col" class="last_th">', $txt['ultport_admin_bk_action'], '</th>
				</tr>
			</thead>			
			<tbody>';
		if (!empty($context['bkcustom_view']) && !empty($context['block-custom'])) 	
		{
			foreach($context['block-custom'] as $block_custom)	
			{
				echo '
				<tr class="windowbg" id="news-section">
					<td class="',$block_custom['activestyle'],'" style="text-align:center;">									
						', $block_custom['id'] ,'
					</td>
					<td class="',$block_custom['activestyle'],'" style="text-align:center;">									
						', $block_custom['type-img'] ,'
					</td>
					<td class="',$block_custom['activestyle'],'">									
						', $block_custom['title_link_edit'] ,'
					</td>
					<td class="',$block_custom['activestyle'],'" style="text-align:center;">									
						', $block_custom['permissions'] ,'
					</td>
					<td class="',$block_custom['activestyle'],'" style="text-align:center;">									
						', $block_custom['editimage'] ,' ', $block_custom['deleteimage'] ,'
					</td>
				</tr>';
			}
		}
			echo'			
			</tbody>			
		</table>
	</div>';
	
	echo'	
	<div class="cat_bar">
		<h3 class="catbg">
			', $txt['ultport_admin_bk_system'], '
		</h3>
	</div>
	<div id="admincenter">	
		<table class="table_grid" cellspacing="0" width="100%">
			<thead>
				<tr class="catbg">
					<th width="5%" scope="col" class="first_th">', $txt['ultport_blocks_titles_id'], '</th>
					<th width="5%" scope="col">', $txt['ultport_admin_bk_type'], '</th>
					<th scope="col">', $txt['ultport_add_bk_title'], '</th>
					<th scope="col">', $txt['ultport_button_permission'], '</th>
					<th width="10%" scope="col" class="last_th">', $txt['ultport_admin_bk_action'], '</th>
				</tr>
			</thead>			
			<tbody>';
		if (!empty($context['block-system'])) 	
		{
			foreach($context['block-system'] as $block_system)	
			{
				echo '
				<tr class="windowbg" id="news-section">
					<td class="',$block_system['activestyle'],'" style="text-align:center;">									
						', $block_system['id'] ,'
					</td>
					<td class="',$block_system['activestyle'],'" style="text-align:center;">									
						', $block_system['type-img'] ,'
					</td>
					<td class="',$block_system['activestyle'],'">									
						', $block_system['title'] ,'
					</td>
					<td class="',$block_system['activestyle'],'" style="text-align:center;">									
						', $block_system['permissions'] ,'
					</td>
					<td class="',$block_system['activestyle'],'" style="text-align:center;">									
						', $block_system['editimage'] ,' ', $block_system['deleteimage'] ,'
					</td>
				</tr>';
			}			
		}
			echo'			
			</tbody>			
		</table>
	</div>';
}

function template_edit_block_html()
{
	global $context, $txt, $settings, $scripturl;
	echo'
	<form method="post" action="', $scripturl, '?action=adminportal;area=ultimate_portal_blocks;sa=blocks-html-edit" accept-charset="', $context['character_set'], '">
	<div id="admincenter">
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['ultport_add_bk_html_titles'], '
			</h3>
		</div>												
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
			<div class="content">
				<dl class="settings">
					<dt>
						<span><label for="ultport_add_bk_title">', $txt['ultport_add_bk_title'], '</label></span>
					</dt>
					<dd>
						<input type="text" value="', $context['title'] ,'" name="bk-title" size="85" />
					</dd>';
				if(!empty($context['folder_images']))
				{
					echo'	
					<dt>
						<span><label for="ultport_add_bk_icon">', $txt['ultport_add_bk_icon'], '</label></span>
					</dt>				
					<dd>
						<table width="100%">
							<tr>';									
							$i = 1;
							foreach($context['folder_images'] as $folder)
							{
								echo '
								<td>
									<input '. ($context['icon'] == $folder['value'] ? 'checked="checked"' : '') .' value="'. $folder['value'] .'" type="radio" name="icon">&nbsp;', $folder['image'] . '
								</td>';
								$i++;
								if($i==6)
								{
									echo '</tr><tr>';
									$i = 1;
								}
							}
					echo '</tr>
						</table>
					</dd>						
				</dl>';
				}
				echo'
				<div style="margin: 10px;" align="center">
					<textarea id="elm1" name="elm1">', $context['content'] ,'</textarea>
				</div>
				<dl class="settings">
					<dt>
						<span><label for="ultport_add_bk_collapse">', $txt['ultport_add_bk_collapse'], '</label></span>
					</dt>
					<dd>
						<input type="checkbox" name="can_collapse" value="on" ', !empty($context['bk_collapse']) ? 'checked="checked"' : '' ,' />
					</dd>
					<dt>
						<span><label for="ultport_add_bk_style">', $txt['ultport_add_bk_style'], '</label></span>
					</dt>
					<dd>
						<input type="checkbox" name="bk_style" value="on" ', !empty($context['bk_style']) ? 'checked="checked"' : '' ,' />
					</dd>
					<dt>
						<span><label for="ultport_add_bk_no_title">', $txt['ultport_add_bk_no_title'], '</label></span>
					</dt>
					<dd>
						<input type="checkbox" name="no_title" value="on" ', !empty($context['bk_no_title']) ? 'checked="checked"' : '' ,' />
					</dd>
				</dl>	
				<hr class="hrcolor clear" />
				<div class="righttext">
					<input type="hidden" name="save" value="ok" />						
					<input type="hidden" name="id" value="', $context['id'] ,'" />	
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="',$txt['ultport_button_save'],'" value="',$txt['ultport_button_save'],'" />
				</div>	
			</div>	
			<span class="botslice"><span></span></span>	
		</div>		
	</div>
	</form>';
}

function template_edit_block_php()
{
	global $context, $txt, $settings, $scripturl;
	
	//Preview
	if($context['preview'])
	{
		echo '
		<table align="center" width="70%">
			<tr>
				<td>';
					head_block($context['icon'], $context['title'], -10, $context['bk_collapse'], $context['bk_no_title'], $context['bk_style']);
					$context['content'] = trim($context['content'], '<?php');
					$context['content'] = trim($context['content'], '?>');
					eval($context['content']);
					footer_block($context['bk_style']);
		echo '			
				</td>
			</tr>
		</table><br />';
	}
	//End Preview
	echo'
	<form method="post" action="', $scripturl, '?action=adminportal;area=ultimate_portal_blocks;sa=blocks-php-edit;id='. $context['id'] .';type-php='. $context['type_php'] .'" accept-charset="', $context['character_set'], '">	
	<div id="admincenter">
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['ultport_add_bk_php_titles'], '
			</h3>
		</div>												
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
			<div class="content">
				<dl class="settings">
					<dt>
						<span><label for="ultport_add_bk_title">', $txt['ultport_add_bk_title'], '</label></span>
					</dt>
					<dd>
						<input type="text" value="', $context['title'] ,'" name="bk-title" size="50" />
					</dd>';
				if(!empty($context['folder_images']))
				{
					echo'	
					<dt>
						<span><label for="ultport_add_bk_icon">', $txt['ultport_add_bk_icon'], '</label></span>
					</dt>				
					<dd>
						<table width="100%">
							<tr>';									
							$i = 1;
							foreach($context['folder_images'] as $folder)
							{
								echo '
								<td>
									<input '. ($context['icon'] == $folder['value'] ? 'checked="checked"' : '') .' value="'. $folder['value'] .'" type="radio" name="icon">&nbsp;', $folder['image'] . '
								</td>';
								$i++;
								if($i==6)
								{
									echo '</tr><tr>';
									$i = 1;
								}
							}
					echo '</tr>
						</table>
					</dd>						
				</dl>';
				}
				echo'
				<div style="margin: 10px;" align="center">
					<textarea id="content" name="content" rows="20" cols="80" style="width: 99.2%">', $context['content'] ,'</textarea>
				</div>
				<dl class="settings">
					<dt>
						<span><label for="ultport_add_bk_collapse">', $txt['ultport_add_bk_collapse'], '</label></span>
					</dt>
					<dd>
						<input type="checkbox" name="can_collapse" value="on" ', !empty($context['bk_collapse']) ? 'checked="checked"' : '' ,' />
					</dd>
					<dt>
						<span><label for="ultport_add_bk_style">', $txt['ultport_add_bk_style'], '</label></span>
					</dt>
					<dd>
						<input type="checkbox" name="bk_style" value="on" ', !empty($context['bk_style']) ? 'checked="checked"' : '' ,' />
					</dd>
					<dt>
						<span><label for="ultport_add_bk_no_title">', $txt['ultport_add_bk_no_title'], '</label></span>
					</dt>
					<dd>
						<input type="checkbox" name="no_title" value="on" ', !empty($context['bk_no_title']) ? 'checked="checked"' : '' ,' />
					</dd>
				</dl>	
				<hr class="hrcolor clear" />
				<div class="righttext">
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="hidden" name="use_folder" value="', $context['use_folder'] ,'" />&nbsp;
					<input type="submit" name="save" value="', $txt['ultport_button_save'] ,'" />&nbsp;
					<input type="submit" name="preview" value="', $txt['ultport_button_preview'] ,'" />
				</div>	
			</div>	
			<span class="botslice"><span></span></span>	
		</div>		
	</div>
	</form>';
}

function template_perms_block()
{
	global $context, $txt, $settings, $scripturl;
	echo'
	<form method="post" action="', $scripturl, '?action=adminportal;area=ultimate_portal_blocks;sa=blocks-perms;id='. $context['id'] .'" accept-charset="', $context['character_set'], '">	
	<div id="admincenter">
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['ultport_admin_edit_perms'], ' (', $context['title'] ,')
			</h3>
		</div>												
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
			<div class="content">
				<dl class="settings">
					<dt>
						<span><label for="ultport_admin_select_perms">', $txt['ultport_admin_select_perms'], '</label></span>
					</dt>
					<dd>
						<div id="allowedAutoUnhideGroupsList">';
							$permissionsGroups = explode(',',$context['perms']);
							// List all the membergroups so the user can choose who may access this board.
							foreach ($context['groups'] as $group)
							echo '
							<input type="checkbox" name="perms[]" value="', $group['id_group'], '" id="groups_', $group['id_group'], '"', ((in_array($group['id_group'],$permissionsGroups) == true) ? ' checked="checked" ' : ''), '/>', $group['group_name'], '<br />';
							echo '
							<input type="checkbox" onclick="invertAll(this, this.form, \'perms[]\');" /> <i>', $txt['ultport_button_select_all'], '</i><br />
							<br />
						</div>
					</dd>								
				</dl>					
				<hr class="hrcolor clear" />
				<div class="righttext">
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="save" value="', $txt['ultport_button_save'] ,'" />
				</div>	
			</div>	
			<span class="botslice"><span></span></span>	
		</div>		
	</div>
	</form>';
}