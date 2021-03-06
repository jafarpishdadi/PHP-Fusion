<?php

// !IMPORTANT - Please do not clean up commented section. It will be nightmare to figure what is missing.
add_to_head("<link href='".THEMES."templates/global/css/forum.css' rel='stylesheet'/>\n");

/* Forum index master template */
if (!function_exists('render_forum')) {
	function render_forum($info) {
		global $userdata, $settings, $locale, $forum_index;
		echo render_breadcrumbs();
		// index.
		$tab_title['title'][] = $locale['forum_0001'];
		$tab_title['id'][] = "thread";
		$tab_title['icon'][] = "entypo window";

		$tab_title['title'][] = $locale['forum_0011'];
		$tab_title['id'][] = "mypost";
		$tab_title['icon'][] = "entypo user";

		$tab_title['title'][] = $locale['global_021'];
		$tab_title['id'][] = "latest";
		$tab_title['icon'][] = "entypo list";

		$tab_title['title'][] = $locale['global_056'];
		$tab_title['id'][] = "tracked";
		$tab_title['icon'][] = "entypo twitter";

		$tab_active = isset($_GET['section']) ? $_GET['section'] : 'thread';

		echo opentab($tab_title, $tab_active, 'forum_tabs', 1);
		echo opentabbody($tab_title['title'], $tab_active, $tab_active, 'viewforum', 1);
		if (isset($_GET['viewforum'])) {
			forum_viewforum($info);
		} else {
			switch($_GET['section']) {
				case 'mypost':
					render_mypost($info);
					break;
				case 'latest':
					render_laft($info);
					break;
				case 'tracked':
					render_tracked($info);
					break;
				default:
					render_forum_main($info);
			}
		}
		echo closetabbody();
		echo closetab();
	}
}

/* Render Forum Board Index */
if (!function_exists('render_forum_main')) {
	function render_forum_main($info) {
		global $locale;
		$type_icon = array('1'=>'entypo folder', '2'=>'entypo chat', '3'=>'entypo link', '4'=>'entypo graduation-cap');
		echo "<div class='m-t-10'>\n";
		if (!empty($info['item'])) {
			foreach($info['item'] as $data) {
				// template for category type.
				if ($data['forum_type'] == '1') {
					echo "<div class='panel panel-default'>\n";
					echo "<div class='panel-heading' ".(isset($data['child']) ? 'style="border-bottom:0;"' : '').">\n";
					echo "<a class='forum-subject' href='".FORUM."index.php?viewforum&amp;forum_id=".$data['forum_id']."&amp;parent_id=".$data['forum_cat']."&amp;forum_branch=".$data['forum_branch']."'>".$data['forum_name']."</a><br/>";
					echo $data['forum_description'] ? "<span class='text-smaller'>".$data['forum_description']."</span>\n<br/>" : '';
					echo "</div>\n";
					if (isset($data['child'])) {
						$i = 1;
						foreach($data['child'] as $cdata) {
							render_forum_item_type($cdata, $i);
							$i++;
						}
					} else {
						echo "<div class='panel-body text-center'>\n";
						echo $locale['forum_0327'];
						echo "</div>\n";
					}
					echo "</div>\n"; // end panel-default
				} else {
					render_forum_item_type($data, 0);
				}
			}
		} else {
			echo "<div class='well text-center'>".$locale['forum_0328']."</div>\n";
		}
		echo "</div>\n";
	}
}

/* Render forum items - internally used in render_forum_main and forum_viewforum */
if (!function_exists('render_forum_item_type')) {
	function render_forum_item_type($data, $i) {
		global $locale, $info, $userdata, $settings;
		$type_icon = array('1'=>'entypo archive', '2'=>'entypo folder', '3'=>'entypo link', '4'=>'entypo graduation-cap');
		/* Forum matching */
		$forum_match = "\|".$data['forum_lastpost']."\|".$data['forum_id'];
		/* Show moderators */
		$moderators = '';
		if ($data['forum_mods']) {
			$mod_groups = explode(".", $data['forum_mods']);
			foreach ($mod_groups as $mod_group) {
				if ($moderators) $moderators .= ", ";
				$moderators .= $mod_group < 101 ? "<a href='".BASEDIR."profile.php?group_id=".$mod_group."'>".getgroupname($mod_group)."</a>" : getgroupname($mod_group);
			}
		}

		/* new status */
		$fim = '';
		if ($data['forum_lastpost'] > $info['lastvisited']) {
			if (iMEMBER && ($data['forum_lastuser'] !== $userdata['user_id'] || !preg_match("({$forum_match}\.|{$forum_match}$)", $userdata['user_threads']))) {
				$fim = "<span class='forum-new-icon'><i title='".$locale['forum_0260']."' class='entypo ialert'></i></span>";
			}
		}

		if ($i>0) {
			echo "<div id='forum_".$data['forum_id']."' class='forum-list list-group-item'>\n";
		} else {
			echo "<div id='forum_".$data['forum_id']."' class='panel panel-default'>\n";
			echo "<div class='panel-body'>\n";
		}

		echo "<div class='pull-left m-r-10 forum-thumbnail'>\n";
		if ($data['forum_image'] && file_exists(IMAGES."forum/".$data['forum_image'])) {
			echo thumbnail(IMAGES."forum/".$data['forum_image'], '50px');
		} else {
			echo "<i class='".$type_icon[$data['forum_type']]." icon-sm low-opacity'></i>";
		}
		echo "</div>\n";

		echo "<div class='overflow-hide'>\n";
		echo "<div class='row'>\n";

		if ($data['forum_type'] !=='3') {
			echo "<div class='col-xs-12 col-sm-5'>\n";
		} else {
			echo "<div class='col-xs-12 col-sm-12'>\n";
		}
		echo "<!--forum_name-->\n";
		echo "<a class='display-inline-block forum-subject' href='".FORUM."index.php?viewforum&amp;forum_id=".$data['forum_id']."&amp;parent_id=".$data['forum_cat']."&amp;forum_branch=".$data['forum_branch']."'>".$data['forum_name']."</a>\n";
		echo $fim;
		echo $data['forum_description'] ? "<div class='forum-description'>".nl2br(parseubb($data['forum_description']))."</div>\n" : '';
		echo ($moderators ? "<span class='forum-moderators text-smaller'><strong>".$locale['forum_0007']."</strong>".$moderators."</span>\n" : "")."\n";
		if (isset($data['child'])) {
			echo "<div class='clearfix'>\n";
			echo "<div class='pull-left'>\n";
			echo "<i class='entypo level-down'></i>\n";
			echo "</div>\n";
			echo "<div class='overflow-hide'>\n";
			foreach($data['child'] as $cdata) {
				echo "<span class='nowrap'>\n";
				if (isset($cdata['forum_type'])) {
					echo "<i class='mid-opacity ".$type_icon[$cdata['forum_type']]."'></i>\n";
				}
				echo "<a href='".FORUM."index.php?viewforum&amp;forum_id=".$cdata['forum_id']."&amp;parent_id=".$cdata['forum_cat']."&amp;forum_branch=".$cdata['forum_branch']."' class='forum-subforum display-inline-block m-r-10'>".$cdata['forum_name']."</a></span>";
			}
			echo "</div>\n";
			echo "</div>\n";
		}
		echo "</div>\n";
		if ($data['forum_type'] !=='3') {
			echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0 p-r-0 text-center'>\n";

			echo "<div class='display-inline-block forum-stats well p-5 m-r-5 m-b-0'>\n";
			echo "<span class='text-bigger strong text-dark m-0'>".number_format($data['forum_postcount'])."</span><br/>\n";
//			echo "<span class='text-smaller'>".$locale['forum_0003']."</span><br/>\n";
			echo "<span class='text-smaller'>".format_word($data['forum_postcount'], $locale['fmt_post'], 0)."</span><br/>\n";
			echo "</div>\n";

			echo "<div class='display-inline-block forum-stats well p-5 m-r-10 m-b-0'>\n";
			echo "<span class='text-bigger strong m-0 text-dark'>".number_format($data['forum_threadcount'])."</span><br/>\n";
//			echo "<span class='text-smaller'>".($data['forum_type'] == '4' ? $locale['forum_0340'] : $locale['forum_0341'])."</span><br/>\n";
			echo "<span class='text-smaller'>".($data['forum_type'] == '4' ? format_word($data['forum_threadcount'], $locale['fmt_question'], 0) : format_word($data['forum_threadcount'], $locale['fmt_thread'], 0))."</span><br/>\n";
			echo "</div>\n";

			echo "</div><div class='col-xs-12 col-sm-4 col-md-4 col-lg-3 p-l-0'>\n";
			if ($data['forum_lastpost'] == 0) {
				echo $locale['forum_0005'];
			} else {
				echo "<div class='clearfix'>\n";

				if ($settings['forum_last_post_avatar'] == 1) {
					echo "<div class='pull-left lastpost-avatar m-r-10 m-t-5'>".display_avatar($data, '30px', '', '', 'img-rounded')."</div>";
				}
				echo "<div class='overflow-hide'>\n";
				echo "<a class='lastpost-title strong' href='".FORUM."viewthread.php?thread_id=".$data['thread_id']."' title='".$data['thread_subject']."'>".trimlink($data['thread_subject'], 25)."</a> ";
				echo "<a class='lastpost-goto' href='".FORUM."viewthread.php?thread_id=".$data['thread_id']."&amp;pid=".$data['thread_lastpostid']."#post_".$data['thread_lastpostid']."' title='".$data['thread_subject']."'>";
				if ($data['forum_lastpost'] > $info['lastvisited']) {
					if (iMEMBER && preg_match("({$forum_match}\.|{$forum_match}$)", $userdata['user_threads'])) {
						$fim = "<img src='".get_image("lastpost")."' alt='".$locale['forum_0004']."' title='".$locale['forum_0004']."' />";
					} else {
						$fim = "<img src='".get_image("lastpostnew")."' alt='".$locale['forum_0004']."' title='".$locale['forum_0004']."' />";
					}
				} else {
					$fim = "<img src='".get_image("lastpost")."' alt='".$locale['forum_0004']."' title='".$locale['forum_0004']."' />";
				}

				echo "</a>$fim<br />\n";
				echo "<span class='forum_profile_link'>".$locale['by']." ".profile_link($data['forum_lastuser'], $data['user_name'], $data['user_status'])."</span><br />\n";
				echo "<span class='lastpost-date text-smaller'>".showdate("forumdate", $data['forum_lastpost'])."</span> \n";
				echo "</div>\n</div>\n";
			}
			echo "</div>\n";
		}
		echo "</div>\n"; // end row
		echo "</div>\n"; // end overflow-hide
		if ($i > 0)  {
			echo "</div>\n";
		} else {
			echo "</div>\n</div>\n";
		}
	}
}

/* Forum View - ex viewforum.php */
if (!function_exists('forum_viewforum')) {
	function forum_viewforum($info) {
		global $locale, $settings;
		$data = $info['item'][$_GET['forum_id']];
		echo "<h2 class='m-t-20'>".$data['forum_name']."</h2>\n";
		echo "<div class='forum-description m-b-20'>\n";
		echo $data['forum_description'] ? "<span class='display-inline-block'>".$data['forum_description']."</span>\n" : '';
		echo "</div>\n";
		echo $data['forum_rules'] ? "<div class='alert alert-info m-b-20'><span class='strong m-b-10'><i class='entypo megaphone'></i>".$locale['forum_0350']."</span> ".$data['forum_rules']."</div>" : '';
		if ($data['forum_type'] > 1) {
			// post button & forum filter
			echo "<div class='clearfix m-b-20'>\n";
			if (iMEMBER && $info['permissions']['can_post']) {
				echo "<a title='".$locale['forum_0264']."' alt='".$locale['forum_0264']."' class='btn button btn-primary text-white' href='".FORUM."post.php?action=newthread&amp;forum_id=".$_GET['forum_id']."'><i class='entypo plus-circled'></i> ".$locale['forum_0264']."</a>";
			}
			echo "<div class='pull-right'>\n";
			forum_filter($info);
			echo "</div>\n";
			echo "</div>\n";
		}
		// subforums
		if (isset($info['item'][$_GET['forum_id']]['child'])) {
			echo "<div class='panel panel-default'>\n";
			echo "<div class='panel-heading strong'>".$locale['forum_0351']."</div>\n";
			$i = 1;
			foreach ($info['item'][$_GET['forum_id']]['child'] as $subforum_id => $subforum_data) {
				render_forum_item_type($subforum_data, $i);
				$i++;
			}
			echo "</div>\n";
		}

		if (isset($info['threads'])) {
			echo "<!--pre_forum-->\n";
			if (!empty($info['threads']['sticky'])) {
				echo "<div class='panel panel-default m-t-15'>\n";
				echo "<div class='panel-heading' style='border-bottom:0;'>".$locale['forum_0352']."</div>\n";
				$i = 1;
				foreach ($info['threads']['sticky'] as $cdata) {
					render_thread_item($cdata, $i);
					$i++;
				}
				echo "</div>\n";
			}
			if (!empty($info['threads']['item'])) {
				echo "<div class='panel panel-default m-t-15'>\n";
				echo "<div class='panel-heading strong' style='border-bottom:0;'>".$locale['forum_0341']."</div>\n";

				$i = 1;
				foreach ($info['threads']['item'] as $cdata) {
					render_thread_item($cdata, $i);
					$i++;
				}
				echo "</div>\n";
			}
		} else {
			if ($info['item'][$_GET['forum_id']]['forum_type'] !=='1') {
				echo "<div class='well text-center'>".$locale['forum_0269']."</div>\n";
			}
		}
	}
}

/* display threads -- need to simplify */
if (!function_exists('render_thread_item')) {
	function render_thread_item($data, $i) {
		global $locale, $info, $userdata;
		$settings = fusion_get_settings();
		$type_icon = array('1'=>'entypo folder', '2'=>'entypo chat', '3'=>'entypo link', '4'=>'entypo graduation-cap');
		/* Forum matching */
		$thread_match = $data['thread_id']."\|".$data['thread_lastpost']."\|".$data['forum_id'];
		// Icons
		$icon = '';
		$xicon = '';
		// thread locked
		if ($data['thread_locked']) {
			$xicon .= "<i class='entypo lock' title='".$locale['forum_0263']."'></i>";
		} else {
			// normal folder
			if ($data['thread_lastpost'] > $info['lastvisited']) {
				if (iMEMBER && ($data['thread_lastuser'] == $userdata['user_id'] || preg_match("(^\.{$thread_match}$|\.{$thread_match}\.|\.{$thread_match}$)", $userdata['user_threads']))) {
					$xicon .= "<i class='pull-left m-r-10 entypo chat icon-sm low-opacity' title='".$locale['forum_0261']."'></i>";
				} else {
					$xicon .= "<i class='pull-left m-r-10  entypo lamp icon-sm low-opacity' title='".$locale['forum_0260']."'></i>";
				}
			} else {
				$xicon .= "<i class='pull-left m-r-10 entypo chat icon-sm low-opacity' title='".$locale['forum_0261']."'></i>";
			}
		}
		// sticky
		if ($data['thread_sticky'] == 1) {
			$sticky_status = "<span>".$locale['forum_0103']." : </span>\n";
			$icon .= "<i class='entypo megaphone icon-xs mid-opacity' title='".$locale['forum_0103']."'></i>\n";
		}
		// hot icon
		if ($data['thread_postcount'] >= 50) {
			$icon .= "<i class='entypo thermometer icon-xs mid-opacity' title='".$locale['forum_0311']."'></i>";
		}
		if ($data['thread_views'] >= 50) {
			$icon .= "<i class='entypo eye icon-xs mid-opacity' title='Interesting Topic'></i>";
		}
		// @todo: render attachments out on thread
		// attach iconS
		$attach_icon_result = dbquery("SELECT attach_id, attach_mime FROM ".DB_FORUM_ATTACHMENTS." WHERE thread_id ='".$data['thread_id']."'");
		if (dbrows($attach_icon_result)>0) {
			$attach_image = 0; $attach_file = 0;
			// lets play with some graphics.
			require_once INCLUDES."mimetypes_include.php";
			while($adata = dbarray($attach_icon_result)) {
				if (in_array($adata['attach_mime'], img_mimeTypes())) {
					$attach_image = 1;
				} else {
					$attach_file = 1;
				}
			}
			$icon .= $attach_image ? "<i class='entypo picture icon-xs mid-opacity' title='".$locale['forum_0313']."'></i>" : '';
			$icon .= $attach_file ? "<i class='entypo attach icon-xs mid-opacity' title='".$locale['forum_0312']."'></i>" : '';
		}
		// poll icon
		if ($data['thread_poll']) {
			$icon .= "<i class='entypo chart-pie icon-xs mid-opacity' title='".$locale['forum_0314']."'></i>";
		}
		// reps
		$reps = ($data['thread_postcount'] > $info['threads_per_page']) ? ceil($data['thread_postcount']/$info['threads_per_page']) : 0;
		$threadsubject = "<div class='display-inline-block m-0'><a class='thread_title' href='".FORUM."viewthread.php?thread_id=".$data['thread_id']."'>".$data['thread_subject']."</a> $icon</div>";
		if ($reps > 1) {
			$ctr = 0;
			$ctr2 = 1;
			$pages = "";
			$middle = FALSE;
			while ($ctr2 <= $reps) {
				if ($reps < 5 || ($reps > 4 && ($ctr2 == 1 || $ctr2 > ($reps-3)))) {
					$pnum = "<a href='viewthread.php?thread_id=".$data['thread_id']."&amp;rowstart=$ctr'>$ctr2</a> ";
				} else {
					if ($middle == FALSE) {
						$middle = TRUE;
						$pnum = "... ";
					} else {
						$pnum = "";
					}
				}
				$pages .= $pnum;
				$ctr = $ctr+$info['threads_per_page'];
				$ctr2++;
			}
			$threadsubject .= "<br/><span class='forum-pages'><small>(".$locale['forum_0055'].trim($pages).")</small></span>\n";
		}
		// mods
		$moderators = !empty($data['moderators']) ? $data['moderators'] : '';
		$author = array(
			'user_id' => $data['thread_author'],
			'user_name' => $data['author_name'],
			'user_status'=> $data['author_status'],
			'user_avatar' => $data['author_avatar']
		);
		/* new status */
		$fim = '';
		if ($data['thread_lastpost'] > $info['lastvisited']) {
			if (iMEMBER && ($data['forum_lastuser'] == $userdata['user_id'] || preg_match("({$thread_match}\.|{$thread_match}$)", $userdata['user_threads']))) {
				$fim = "<span class='forum-new-icon'><i title='".$locale['forum_0261']."' class='entypo ialert'></i></span>";
			} else {
				$fim = "<span class='forum-new-icon'><i title='".$locale['forum_0260']."' class='entypo ialert'></i></span>";
			}
		}

		echo "<div id='forum_".$data['forum_id']."' class='list-group-item p-t-20' style='border:0; border-top:1px solid #ddd;'>\n";

		echo "<div class='row m-0'>\n";
		echo "<div class='col-xs-12 col-sm-9 col-md-6 p-l-0'>\n";
			echo "<div class='pull-left m-r-10'>\n".$xicon."</div>\n";
			echo "<div class='overflow-hide'>\n";
			echo $threadsubject.$fim;
			echo "<div class='m-t-10 m-b-10'>".$locale['forum_0006'].display_avatar($author, '20px', '', '', 'img-rounded')." <span class='forum_profile_link'>".profile_link($author['user_id'], $author['user_name'], $author['user_status'])."</span> ".$locale['on']." ".showdate('forumdate', $data['post_datestamp'])."</div>\n";
			echo $moderators ? "<div class='forum_moderators'>".$moderators."</div>\n" : '';
			echo isset($data['track_button']) ? "<div class='forum_track'><a onclick=\"return confirm('".$locale['global_060']."');\" href='".$data['track_button']['link']."'>".$data['track_button']['name']."</a>\n</div>\n" : '';
			echo "</div>\n";
		echo "</div>\n"; // end grid

		echo "<div class='hidden-xs col-sm-3 col-md-3 p-l-0 p-r-0 text-center'>\n";
		echo "<div class='display-inline-block forum-stats well p-5 m-r-5 m-b-0'>\n";
		echo "<h4 class='text-bigger strong text-dark m-0'>".number_format($data['thread_views'])."</h4>\n";
//		echo "<span>".$locale['forum_0370']."</span>";
		echo "<span>".format_word($data['thread_views'], $locale['fmt_views'], 0)."</span>";
		echo "</div>\n";
		echo "<div class='display-inline-block forum-stats well p-5 m-r-5 m-b-0'>\n";
		echo "<h4 class='text-bigger strong text-dark m-0'>".number_format($data['thread_postcount'])."</h4>\n";
//		echo "<span>".$locale['forum_0371']."</span>";
		echo "<span>".format_word($data['thread_postcount'], $locale['fmt_post'], 0)."</span>";
		echo "</div>\n";

		if ($data['forum_type'] == '4') {
			echo "<div class='display-inline-block forum-stats well p-5 m-r-5 m-b-0'>\n";
			echo "<h4 class='text-bigger strong text-dark m-0'>".number_format($data['vote_count'])."</h4>\n";
			echo "<span>".format_word($data['vote_count'], $locale['fmt_vote'], 0)."</span>";
			echo "</div>\n";
		}
		echo "</div>\n"; // end grid

		echo "<div class='hidden-xs hidden-sm col-md-3 p-l-0'>\n";
		// this is the last replied
		$lastuser = array(
			'user_id' => $data['thread_lastuser'],
			'user_name' => $data['last_user_name'],
			'user_status' => $data['last_user_status'],
			'user_avatar' => $data['last_user_avatar']
		);
		echo "<div class='pull-left m-r-10'>\n";
		echo display_avatar($lastuser, '35px', '', '', 'img-rounded');
		echo "</div>\n";
		echo "<div class='overflow-hide'>\n";
		echo "<span class=''>".$locale['forum_0373']."</span>\n";
		echo "<span class='forum_profile_link'>".profile_link($lastuser['user_id'], $lastuser['user_name'], $lastuser['user_status'])."</span><br/>\n";
		echo timer($data['post_datestamp']);
		echo "</div>\n";
		echo "</div>\n"; // end grid.
		echo "</div>\n";
		echo "</div>\n";
	}
}

/* Viewthread.php */
if (!function_exists('render_post')) {
	function render_post($info) {
		global $locale, $userdata, $settings;
		echo render_breadcrumbs();
		//opentable($locale['forum_0150']);
		if (isset($info['post_items']) && !empty($info['post_items'])) {
			echo "<!--forum_thread_title-->\n";
			echo "<div class='thread-header clearfix m-t-20 m-b-20'>\n"; // start headerbar
			// notify and print
			echo "<div class='pull-right'>\n";
			echo isset($info['notify']) ? "<a class='btn button btn-default' href='".$info['notify']['link']."'><i class='entypo twitter'></i> ".$info['notify']['name']."</a>\n" : '';
			echo "<a class='btn button btn-default m-l-10' href='".$info['print']['link']."'><i class='entypo print'></i> ".$info['print']['name']."</a>\n";
			echo "</div>\n";
			// thread buttons
			echo "<div class='thread-buttons'>\n";
			echo (isset($info['newthread'])) ? "<a class='btn btn-default text-dark pull-left m-r-10' href='".$info['newthread']['link']."'><i class='entypo plus-circled'></i>".$info['newthread']['name']."</a>\n" : '';
			echo (isset($info['reply'])) ? "<a class='btn btn-default text-dark pull-left m-r-10' href='".$info['reply']['link']."'><i class='entypo plus-circled'></i>".$info['reply']['name']."</a>\n" : '';
			echo "</div>\n";
			echo "</div>\n"; // end headerbar

			echo "<h2 class='m-b-5 thread-header'>".$info['thread_subject']."</h2>\n";
			echo "<a class='forum_cat' href='".$info['forum_cat_link']."'>".$info['forum_name']."</a>\n";
			echo $info['forum_type'] == 4 ? "<br/>\n".(number_format($info['thread_postcount']-1)).$locale['forum_0365']."" : '';
			echo "<span class='thread_date'><i class='fa fa-calendar'></i> ".$locale['forum_0363'].showdate('forumdate', $info['thread_lastpost'])." - ".timer($info['thread_lastpost'])."</span>\n";

			// poll.
			if (isset($info['poll'])) {
				echo "<div class='panel panel-default'>\n";
				echo "<div class='panel-body'>\n";
				if ($info['permissions']['can_vote_poll']) {
					echo openform('voteform', 'post', "".($settings['site_seo'] ? FUSION_ROOT : '').FORUM."viewthread.php?forum_id=".$info['forum_id']."&amp;thread_id=".$_GET['thread_id'], array('notice'=>0, 'max_tokens' => 1));
				}
				echo "<span class='text-bigger strong display-inline-block m-b-10'><i class='entypo chart-pie'></i>".$info['poll']['forum_poll_title']."</span>\n";
				echo "<hr class='m-t-0 m-b-10'/>\n";
				echo "<ul class='p-l-20 p-t-0'>\n";
				$i = 1;
				foreach ($info['poll']['poll_opts'] as $poll_option) {
					if ($info['permissions']['can_vote_poll']) {
						echo "<li><label for='opt-".$i."'><input id='opt-".$i."' type='radio' name='poll_option' value='".$i."' class='m-r-20'> <span class='m-l-10'>".$poll_option['forum_poll_option_text']."</span>\n</label></li>\n";
					} else {
						$option_votes = ($info['poll']['forum_poll_votes'] ? number_format(100/$info['poll']['forum_poll_votes']*$poll_option['forum_poll_option_votes']) : 0);
						echo progress_bar($option_votes, $poll_option['forum_poll_option_text'], '', '10px');
					}
					$i++;
				}
				echo "</ul>\n";
				if ($info['permissions']['can_vote_poll']) {
					echo "<hr class='m-t-10 m-b-10'/>\n";
					echo form_button('vote', 'Cast Vote', 'vote', array('class'=>'btn btn-sm btn-primary m-l-20 '));
					echo closeform();
				}
				echo "</div>\n";
				echo "</div>\n";
			}

			// filter UI vars
			$p_title = array();
			if (isset($info['post-filters'])) {
				foreach($info['post-filters'] as $i => $filters) {
					$p_title['title'][] = $filters['locale'];
					$p_title['id'][] = $info['allowed-post-filters'][$i];
					$p_title['icon'][] = '';
				}
			}

			$tab_active = tab_active($p_title, 0, '1');

			echo opentab($p_title, $tab_active, 'post_tabs', FORUM."viewthread.php?thread_id=".$_GET['thread_id']);
			echo opentabbody('', isset($_GET['section']) ? $_GET['section'] : 'oldest', $tab_active, 'oldest');
			echo "<div id='top' class='thread_pagenav m-t-5'>\n".$info['page_nav']."</div>\n";
			echo "<!--pre_forum_thread-->\n";
			echo iMOD ? openform('mod_form', 'mod_form', 'post', "".($settings['site_seo'] ? FUSION_ROOT : '').FUSION_SELF."?thread_id=".$_GET['thread_id']."&amp;rowstart=".$_GET['rowstart'], array('max_tokens' => 1,'notice' => 0)) : '';
			$i = 0;
			// items
			foreach($info['post_items'] as $post_id => $post_data) {
				$i++;
				echo "<!--forum_thread_prepost_".$post_data['post_id']."-->\n";
				post_item($post_data, $i);
			}

			// Moderation Panel
			if (iMOD) {
				$mod_options = array(
					'renew' => $locale['forum_0207'],
					'delete' => $locale['forum_0201'],
					$info['thread_locked'] ? "unlock" : "lock" => $info['thread_locked'] ? $locale['forum_0203'] : $locale['forum_0202'],
					$info['thread_sticky'] ? "nonsticky" : "sticky" => $info['thread_sticky'] ? $locale['forum_0205'] : $locale['forum_0204'],
					'move' => $locale['forum_0206']
				);
				echo "<hr>\n";
				echo "<div class='list-group-item'>\n";
				echo "<div class='btn-group m-r-10'>\n";
				echo "<a id='check' class='btn button btn-sm btn-default text-dark' href='#' onclick=\"javascript:setChecked('mod_form','delete_post[]',1);return false;\">".$locale['forum_0080']."</a>\n";
				echo "<a id='uncheck' class='btn button btn-sm btn-default text-dark' href='#' onclick=\"javascript:setChecked('mod_form','delete_post[]',0);return false;\">".$locale['forum_0081']."</a>\n";
				echo "</div>\n";
				echo form_button('move_posts', $locale['forum_0176'], $locale['forum_0176'], array('class' => 'btn-default btn-sm m-r-10'));
				echo form_button('delete_posts', $locale['forum_0177'], $locale['forum_0177'], array('class' => 'btn-default btn-sm'));
				echo "<div class='pull-right'>\n";
				echo form_button('go', $locale['forum_0208'], $locale['forum_0208'], array('class' => 'btn-default pull-right btn-sm m-t-0 m-l-10'));
				echo form_select('', 'step', 'step', $mod_options, '', array('placeholder' => $locale['forum_0200'], 'width'=>'250px', 'allowclear'=>1, 'class'=>'m-b-0 m-t-5', 'inline'=>1));
				echo "</div>\n";
				echo "</div>\n";
			}

			// buttons

			echo "<div class='overflow-hide m-t-20'>\n";
			echo (isset($info['newthread'])) ? "<a class='btn btn-default pull-left m-r-10' href='".$info['newthread']['link']."'><i class='entypo plus-circled'></i>".$info['newthread']['name']."</a>\n" : '';
			echo (isset($info['reply'])) ? "<a class='btn btn-default pull-left  m-r-10' href='".$info['reply']['link']."'><i class='entypo plus-circled'></i>".$info['reply']['name']."</a>\n" : '';
			echo "</div>\n";
			if (iMOD) {
				echo closeform();
			}

			// Quick reply
			if ($info['permissions']['can_reply'] && $info['forum_quick_edit'] && !$info['thread_locked']) {
				$form_action = ($settings['site_seo'] ? FUSION_ROOT : '').FORUM."post.php?action=reply&amp;forum_id=".$info['forum_id']."&amp;thread_id=".$_GET['thread_id'];
				echo openform('qr_form', 'post', $form_action, array('class'=>'m-b-20 m-t-20 list-group-item', 'max_tokens' => 1));
				echo "<h4 class='m-t-20 pull-left'>".$locale['forum_0168']."</h4>\n";
				echo form_textarea('', 'post_message', 'post_message', '', array('bbcode' => 1, 'required' => 1, 'autosize'=>1, 'preview'=>1, 'form_name'=>'qr_form'));
				echo "<div class='m-t-10 pull-right'>\n";
				echo $settings['site_seo'] ? '' : form_button('previewreply', $locale['forum_0173'], 'previewreply', $locale['forum_0173'], array('class' => 'btn-default btn-sm m-r-10')); // post lost.
				echo form_button('postreply', $locale['forum_0172'], $locale['forum_0172'], array('class' => 'btn-primary btn-sm m-r-10'));
				echo "</div>\n";
				echo "<div class='overflow-hide'>\n";
				echo form_checkbox($locale['forum_0169'], 'post_smileys', 'post_smileys', '', array('class'=>'m-b-0'));
				if (array_key_exists("user_sig", $userdata) && $userdata['user_sig']) {
					echo form_checkbox($locale['forum_0170'], 'post_showsig', 'post_showsig', '1', array('class'=>'m-b-0'));
				}
				if ($settings['thread_notify']) {
					echo form_checkbox($locale['forum_0171'], 'notify_me', 'notify_me', $info['tracked_threads'], array('class'=>'m-b-0'));
				}
				echo "</div>\n";
				echo closeform();
				//echo "<!--sub_forum_thread-->\n";
			}
			echo "</div>\n";
			echo "</div>\n";
			echo "</div>\n";
		} else {
			echo "<div class='text-center well'>".$locale['forum_0270']."</div>\n";
		}
		//closetable();
	}
}

/* Post Item */
if (!function_exists('render_post_item')) {
	function post_item($data, $i) {
		global $userdata, $locale, $info, $settings;

		$post_reply = !empty($data['post_quote']) ? "<a class='forum_user_actions' title='".$data['post_quote']['name']."' href='".$data['post_quote']['link']."'>".$data['post_quote']['name']."</a>\n" : '';
		$post_reply .= !empty($data['post_edit']) ? " &middot; <a class='forum_user_actions' title='".$data['post_edit']['name']."' href='".$data['post_edit']['link']."'>".$data['post_edit']['name']."</a>\n" : '';
		$print = !empty($data['print']) ? " &middot; <a class='forum_user_actions' title='".$data['print']['name']."' href='".$data['print']['link']."'>".$data['print']['name']."</a>" : '';
		$user_web = !empty($data['user_web']) ? "&middot; <a class='forum_user_actions' href='".$data['user_web']['link']."' target='_blank'>".$data['user_web']['name']."</a>" : '';
		$user_msg = !empty($data['user_message']) ? "<a class='forum_user_actions' href='".$data['user_message']['link']."' target='_blank'>".$data['user_message']['name']."</a>" : '';
		$user_ip = !empty($data['user_ip']) ? "<span class='forum_thread_ip text-smaller'>".$data['user_ip']."</span>\n" : '';
		$user_sig = !empty($data['user_ip']) ? "<div class='forum_sig text-smaller'>".parseubb($data['user_sig'])."</div>\n" : '';

		$marker = !empty($data['marker']) ? "<a class='marker' href='".$data['marker']['link']."' id='".$data['marker']['id']."'>".$data['marker']['name']."</a>" : '';
		$edit_reason = !empty($data['edit_reason']) ? $data['edit_reason'] : '';
		$vote = '';
		if ($info['permissions']['can_rate'] && $data['vote_time']) {
			$vote = "<div class='text-center'>\n";
			if (!empty($data['vote_up'])) {
				$vote .= "<a href='".$data['vote_up']['link']."' class='mid-opacity text-dark'>\n<i class='entypo up-dir icon-sm'></i></a>\n";
			} else {
				$vote .= "<i class='entypo up-dir low-opacity icon-sm'></i>";
			}
			$vote .= "<h4 class='m-0'>".$data['vote_points']."</h4>\n";
			if (!empty($data['vote_down'])) {
				$vote .= "<a href='".$data['vote_down']['link']."' class='mid-opacity text-dark'>\n<i class='entypo down-dir icon-sm'></i></a>\n";
			} else {
				$vote .= "<i class='entypo down-dir low-opacity icon-sm'></i>";
			}
			$vote .= "</div>\n";
		}
		// date
		$date = ucfirst($locale['posted'])." ".timer($data['post_datestamp'])." - ".showdate('forumdate', $data['post_datestamp']);
		// attachment
		$attach = '';
		if (!empty($data['attach-files'])) {
			$attach .= "<div class='emulated-fieldset'>\n";
			$attach .= "<span class='emulated-legend'>".profile_link($data['user_id'], $data['user_name'], $data['user_status']).$locale['forum_0154'].($data['attach-files-count'] > 1 ? $locale['forum_0158'] : $locale['forum_0157'])."</span>\n";
			$attach .= "<div class='attachments-list m-t-10'>".$data['attach-files']."</div>\n";
			$attach .= "</div>\n";
		}
		if (!empty($data['attach-image'])) {
			$attach .= "<div class='emulated-fieldset'>\n";
			$attach .= "<span class='emulated-legend'>".profile_link($data['user_id'], $data['user_name'], $data['user_status']).$locale['forum_0154'].($data['attach-image-count'] > 1 ? $locale['forum_0156'] : $locale['forum_0155'])."</span>\n";
			$attach .= "<div class='attachments-list'>".$data['attach-image']."</div>\n";
			$attach .= "</div>\n";
		}

		echo "<!--forum_thread_prepost_".$data['post_id']."-->\n";
		echo "<div class='clearfix m-t-20 list-group-item'>\n";

			echo "<div class='pull-left text-center forum-user m-t-5 m-r-20'>\n";
			echo "<div class='m-b-10'>\n";
			echo display_avatar($data, '50px', '', '', '');
			echo "</div>\n";
			echo ($data['user_lastvisit'] >= time()-3600 ? "<span class='btn text-white label label-success'>".$locale['online']."</span>" : "<span class='label text-white label-danger'>".$locale['offline']."</span>")."\n";
			echo "<br/>\n<span class='forum_rank display-inline-block m-t-10'>\n".$data['rank_img']."</span>\n";
			echo "</div>\n";

		echo $vote ? "<div class='display-inline-block pull-left m-r-10'>\n$vote</div>\n" : '';

		echo "<div class='overflow-hide'>\n";
		echo "<!--forum_thread_user_name-->\n";
		echo "<span class='forum-profile-link strong m-r-10'>".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."</span>";
		echo "<span class='forum_user_post_count'>".$data['user_posts']." ".$locale['forum_0151']."</span>\n";
		echo "<span class='forum_date pull-right'> <i class='fa fa-clock-o'></i> $date - $marker";
		echo "<a title='".$locale['forum_0241']."' role='button' class='pull-right icon-xs' href='#top'><i class='entypo up-open'></i></a>\n";
		echo "</span>\n";
		echo "<div class='overflow-hide m-b-20'>\n";
		echo "<div class='forum_thread_user_post'>".parseubb($data['post_smileys'] ? parsesmileys($data['post_message']) : $data['post_message'])."</div>\n";
		echo "</div>\n";
		echo $attach;
		echo "<!--sub_forum_post_message-->";
		echo $edit_reason ? $edit_reason : '';
		echo "<div class='user_signature'>".$user_sig."</div>\n";
		echo "<!--sub_forum_post-->";
		echo "<div class='thread-footer'>\n";
		if (iMOD) {
			echo "<div class='pull-right'>\n";
			echo "<input type='checkbox' name='delete_post[]' value='".$data['post_id']."'/>\n";
			echo "</div>\n";
		}
		echo "".$user_web.$user_msg.$post_reply.$print.$user_ip;
		echo "</div>\n";

		echo "</div>\n";
		echo "</div>\n";

	}
}

/* My Post Section */
if (!function_exists('render_mypost')) {
	function render_mypost($info) {
		global $locale;
		$type_icon = array('1'=>'entypo folder', '2'=>'entypo chat', '3'=>'entypo link', '4'=>'entypo graduation-cap');
		if (!empty($info['item'])) {
			// sort by date.
			$last_date = ''; $i = 0;
			foreach($info['item'] as $data) {
				$cur_date = date('M d, Y', $data['post_datestamp']);
				$xim = '';
				if ($cur_date != $last_date) {
					$last_date = $cur_date;
					$title = "<div class='post_title m-b-10'>Posts on ".$last_date."</div>\n";
					echo $i > 0 ? "</div>\n".$title."<div class='list-group'>\n" : $title."<div class='list-group'>\n";
				}

				echo "<div class='list-group-item clearfix'>\n";
				echo "<div class='pull-left m-r-10'>\n";
				echo "<i class='".$type_icon[$data['forum_type']]." icon-sm low-opacity'></i>";
				echo "</div>\n";
				echo "<div class='overflow-hide'>\n";
				echo "<a class='post_title strong' href='".FORUM."viewthread.php?thread_id=".$data['thread_id']."&amp;pid=".$data['post_id']."#post_".$data['post_id']."' title='".$data['thread_subject']."'>".trimlink($data['thread_subject'], 40)."</a>\n";
				echo "<br/><span class='forum_name'>".trimlink($data['forum_name'], 30)."</span> <span class='thread_date'>&middot; ".showdate("forumdate", $data['post_datestamp'])."</span>\n";
				echo "</div>\n";
				echo "</div>\n";
				$i++;
			}

			echo "</div>\n"; // addition of a div the first time which did not close where $i = 0;

			if ($info['post_rows'] > 20) {
				echo "<div align='center' style='margin-top:5px;'>\n".makepagenav($_GET['rowstart'], 20, $info['post_rows'], 3)."\n</div>\n";
			}
		} else {
			echo "<div class='well text-center'>".$locale['global_054']."</div>\n";
		}
		// not used locale. global_042, 048, 044, 049
	}
}

/* Latest Section */
if (!function_exists('render_laft')) {
	function render_laft($info) {
		global $locale;
		if (!empty($info['item'])) {
			$i = 0;
			foreach($info['item'] as $data) {
				// do a thread.
				render_thread_item($data, $i);
				$i++;
			}
		} else {
			echo "<div class='well text-center'>".$locale['global_023']."</div>\n";
		}
		// filter --- this need to be translated to links.
		$opts = array('0' => $locale['forum_p999'], '1' => $locale['forum_p001'], '7' => $locale['forum_p007'], '14' => $locale['forum_p014'], '30' => $locale['forum_p030'],
			'90' => $locale['forum_p090'], '180' => $locale['forum_p180'], '365' => $locale['forum_p365']);
		echo "<hr/>\n";
		echo openform('filter_form', 'post', FORUM."index.php?section=latest", array('max_tokens' => 1));
		echo form_select($locale['forum_0009'], 'filter', 'filter', $opts, isset($_POST['filter']) && $_POST['filter'] ? $_POST['filter'] : 0, array('width' => '300px', 'class'=>'pull-left m-r-10'));
		echo form_button('go', $locale['go'], $locale['go'], array('class' => 'btn-default btn-sm m-b-20'));
		echo closeform();
	}
}

/* Tracked Section */
if (!function_exists('render_tracked')) {
	function render_tracked($info) {
		global $locale;
		if (!empty($info['item'])) {
			$i = 0;
			foreach($info['item'] as $data) {
				// do a thread.
				render_thread_item($data, $i);
				$i++;
			}
		} else {
			echo "<div class='well text-center'>".$locale['global_059']."</div>\n";
		}
	}
}

/* Forum Filter */
if (!function_exists('forum_filter')) {
	function forum_filter($info) {
		global $locale;
		$selector = array(
			'today' => $locale['forum_p000'],
			'2days' => $locale['forum_p002'],
			'1week' => $locale['forum_p007'],
			'2week' => $locale['forum_p014'],
			'1month' => $locale['forum_p030'],
			'2month' => $locale['forum_p060'],
			'3month' => $locale['forum_p090'],
			'6month' => $locale['forum_p180'],
			'1year' => $locale['forum_p365']
		);
		$selector2 = array(
			'all' => $locale['forum_0374'],
			'discussions' => $locale['forum_0375'],
			'attachments' => $locale['forum_0376'],
			'poll' => $locale['forum_0377'],
			'solved' => $locale['forum_0378'],
			'unsolved' => $locale['forum_0379'],
		);
		$selector3 = array(
			'author' => $locale['forum_0380'],
			'time' => $locale['forum_0381'],
			'subject' => $locale['forum_0382'],
			'reply' => $locale['forum_0383'],
			'view' => $locale['forum_0384'],
		);
		$selector4 = array(
			'ascending' => $locale['forum_0385'],
			'descending' => $locale['forum_0386'],
		);
		echo $locale['forum_0388'];
		echo "<span class='display-inline-block m-l-10 m-r-10' style='position:relative; vertical-align:middle;'>\n";
		echo "<button class='btn btn-xs btn-default' data-toggle='dropdown' class='dropdown-toggle'>".(isset($_GET['time']) && in_array($_GET['time'], array_flip($selector)) ? $selector[$_GET['time']] : $locale['forum_0387'])." <span class='caret'></span></button>\n";
		echo "<ul class='dropdown-menu'>\n";
		foreach($info['filter']['time'] as $filter_locale => $filter_link) {
			echo "<li><a class='text-smaller' href='".$filter_link."'>".$filter_locale."</a></li>\n";
		}
		echo "</ul>\n";
		echo "</span>\n";
		echo $locale['forum_0389'];

		echo "<span class='display-inline-block m-l-10 m-r-10' style='position:relative; vertical-align:middle;'>\n";
		echo "<button class='btn btn-xs btn-default' data-toggle='dropdown' class='dropdown-toggle'>".(isset($_GET['type']) && in_array($_GET['type'], array_flip($selector2)) ? $selector2[$_GET['type']] : $locale['forum_0390'])." <span class='caret'></span></button>\n";
		echo "<ul class='dropdown-menu'>\n";
		foreach($info['filter']['type'] as $filter_locale => $filter_link) {
			echo "<li><a class='text-smaller' href='".$filter_link."'>".$filter_locale."</a></li>\n";
		}
		echo "</ul>\n";
		echo "</span>\n";
		echo $locale['forum_0225'];

		echo "<span class='display-inline-block m-l-10 m-r-10' style='position:relative; vertical-align:middle;'>\n";
		echo "<button class='btn btn-xs btn-default' data-toggle='dropdown' class='dropdown-toggle'>".(isset($_GET['sort']) && in_array($_GET['sort'], array_flip($selector3)) ? $selector3[$_GET['sort']] : $locale['forum_0391'])." <span class='caret'></span></button>\n";
		echo "<ul class='dropdown-menu'>\n";
		foreach($info['filter']['sort'] as $filter_locale => $filter_link) {
			echo "<li><a class='text-smaller' href='".$filter_link."'>".$filter_locale."</a></li>\n";
		}
		echo "</ul>\n";
		echo "</span>\n";

		echo "<span class='display-inline-block' style='position:relative; vertical-align:middle;'>\n";
		echo "<button class='btn btn-xs btn-default' data-toggle='dropdown' class='dropdown-toggle'>".(isset($_GET['order']) && in_array($_GET['order'], array_flip($selector4)) ? $selector4[$_GET['order']] : $locale['forum_0385'])." <span class='caret'></span></button>\n";
		echo "<ul class='dropdown-menu'>\n";
		foreach($info['filter']['order'] as $filter_locale => $filter_link) {
			echo "<li><a class='text-smaller' href='".$filter_link."'>".$filter_locale."</a></li>\n";
		}
		echo "</ul>\n";
		echo "</span>\n";
	}
}

/* Custom Modal New Topic */
if (!function_exists('forum_newtopic')) {

	function forum_newtopic() {
		global $settings, $locale;

		if (isset($_POST['select_forum'])) {
			$_POST['forum_sel'] = isset($_POST['forum_sel']) && isnum($_POST['forum_sel']) ? $_POST['forum_sel'] : 0;
			redirect(FORUM.'post.php?action=newthread&forum_id='.$_POST['forum_sel']);
		}

		echo openmodal('newtopic', $locale['forum_0057'], array('button_id'=>'newtopic', 'class'=>'modal-md'));
		$index = dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat');
		$result = dbquery("SELECT a.forum_id, a.forum_name, b.forum_name as forum_cat_name, a.forum_post
		 FROM ".DB_FORUMS." a
		 LEFT JOIN ".DB_FORUMS." b ON a.forum_cat=b.forum_id
		 WHERE ".groupaccess('a.forum_access')." ".(multilang_table("FO") ? "AND a.forum_language='".LANGUAGE."' AND" : "AND")."
		 (a.forum_type ='2' or a.forum_type='4') AND a.forum_post < ".USER_LEVEL_PUBLIC." AND a.forum_lock !='1' ORDER BY a.forum_cat ASC, a.forum_branch ASC, a.forum_name ASC");
		$options = array();
		if (dbrows($result)>0) {
			while ($data = dbarray($result)) {
				$depth = get_depth($index, $data['forum_id']);
				if (checkgroup($data['forum_post'])) {
					$options[$data['forum_id']] = str_repeat("&#8212;", $depth).$data['forum_name']." ".($data['forum_cat_name'] ? "(".$data['forum_cat_name'].")" : '');
				}
			}
			echo openform('qp_form', 'post', ($settings['site_seo'] ? FUSION_ROOT : '').FORUM.'index.php', array('notice'=>0, 'max_tokens' => 1));
			echo "<div class='well clearfix m-t-10'>\n";
			echo form_select($locale['forum_0395'], 'forum_sel', 'forum_sel', $options, '', array('inline'=>1, 'width'=>'100%'));
			echo "<div class='display-inline-block col-xs-12 col-sm-offset-3'>\n";
			echo form_button('select_forum', $locale['forum_0396'], 'select_forum', array('class'=>'btn-primary btn-sm'));
			echo "</div>\n";
			echo "</div>\n";
			echo closeform();
		} else {
			echo "<div class='well text-center'>\n";
			echo $locale['forum_0328'];
			echo "</div>\n";
		}
		echo closemodal();
	}
}
?>