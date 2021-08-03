<?php
/* Developed by Kernel Team.
   http://kernel-team.com
*/
require_once '../admin/include/setup.php';
require_once '../admin/include/functions_base.php';

header("Content-Type: text/html");

if ($_REQUEST['embed']=='true')
{
	$player_data=@unserialize(file_get_contents("$config[project_path]/admin/data/player/embed/config.dat"));
	if ($_REQUEST['referer']!='')
	{
		$parsed_url=parse_url($_REQUEST['referer']);
		if ($parsed_url['host'])
		{
			$embed_folders=get_contents_from_dir("$config[project_path]/admin/data/player/embed",2);
			foreach ($embed_folders as $embed_folder)
			{
				if (is_file("$config[project_path]/admin/data/player/embed/$embed_folder/config.dat"))
				{
					$embed_profile=@unserialize(file_get_contents("$config[project_path]/admin/data/player/embed/$embed_folder/config.dat"));
					$embed_profile_domains=array_map('trim',explode(',',$embed_profile['embed_profile_domains']));
					foreach ($embed_profile_domains as $embed_profile_domain)
					{
						if (strpos($parsed_url['host'],$embed_profile_domain)===strlen($parsed_url['host'])-strlen($embed_profile_domain))
						{
							$player_data=$embed_profile;
							break 2;
						}
					}
				}
			}
		}
	}
} else {
	start_session();
	if ($_SESSION['status_id']==3 || has_premium_access_by_tokens(intval($_REQUEST['video_id'])))
	{
		$player_dir="$config[project_path]/admin/data/player/premium";
	} elseif ($_SESSION['user_id']>0)
	{
		$player_dir="$config[project_path]/admin/data/player/active";
	} else {
		$player_dir="$config[project_path]/admin/data/player";
	}
	if (is_file("$player_dir/config.dat"))
	{
		$player_data=@unserialize(file_get_contents("$player_dir/config.dat"));
	} else {
		$player_data=@unserialize(file_get_contents("$config[project_path]/admin/data/player/config.dat"));
	}
}

$adv_id=$_REQUEST['aid'];
if ($adv_id!='')
{
	$adv_code=trim($player_data["{$adv_id}_code"]);
	if (intval($_GET['cs_id'])>0 && $player_data["{$adv_id}_source"]>1)
	{
		$cs_info=get_content_source_info(intval($_GET['cs_id']));
		if ($cs_info)
		{
			$cs_field='custom'.($player_data["{$adv_id}_source"]-1);
			if ($cs_info[$cs_field]!='')
			{
				$adv_code=$cs_info[$cs_field];
			}
		}
	} elseif (strpos($player_data["{$adv_id}_source"],'spot_')!==false)
	{
		$adv_code = '';

		$spot_id = trim(substr($player_data["{$adv_id}_source"], 5));
		$spot_data_file = "$config[project_path]/admin/data/advertisements/spot_$spot_id.dat";
		if (is_file($spot_data_file))
		{
			$spot_info = unserialize(file_get_contents($spot_data_file), ['allowed_classes' => false]);
			if (is_array($spot_info))
			{
				$ads_list = array();
				foreach ($spot_info['ads'] as $ad)
				{
					if ($ad['is_active'] == 1)
					{
						$ads_list[] = $ad;
					}
				}

				$category_ids_in_context = array();
				foreach (explode(',', $_REQUEST['category_ids']) as $category_id)
				{
					if (intval($category_id) > 0)
					{
						$category_ids_in_context[intval($category_id)] = intval($category_id);
					}
				}
				if (count($category_ids_in_context) > 0)
				{
					$has_categorized_ads = false;
					foreach ($ads_list as $k => $ad)
					{
						if (@count($ad['category_ids']) > 0)
						{
							$should_delete_ad = true;
							foreach ($ad['category_ids'] as $ad_category_id)
							{
								if (isset($category_ids_in_context[$ad_category_id]))
								{
									$has_categorized_ads = true;
									$should_delete_ad = false;
									break;
								}
							}
							if ($should_delete_ad)
							{
								unset($ads_list[$k]);
							}
						}
					}

					if ($has_categorized_ads)
					{
						foreach ($ads_list as $k => $ad)
						{
							if (@count($ad['category_ids']) == 0)
							{
								unset($ads_list[$k]);
							}
						}
					}

					foreach ($ads_list as $k => $ad)
					{
						if (@count($ad['exclude_category_ids']) > 0)
						{
							$should_delete_ad = false;
							foreach ($ad['exclude_category_ids'] as $ad_category_id)
							{
								if (isset($category_ids_in_context[$ad_category_id]))
								{
									$should_delete_ad = true;
									break;
								}
							}
							if ($should_delete_ad)
							{
								unset($ads_list[$k]);
							}
						}
					}
				} else {
					foreach ($ads_list as $k => $ad)
					{
						if (@count($ad['category_ids']) > 0)
						{
							unset($ads_list[$k]);
						}
					}
				}

				if (count($ads_list) > 0)
				{
					$now_date = time();
					$now_time = explode(':', date("H:i"));
					$now_time = intval($now_time[0]) * 3600 + intval($now_time[1]) * 60;

					$ads = array();
					$ads_empty = array();

					foreach ($ads_list as $ad_info)
					{
						if (($ad_info['show_from_date'] != '0000-00-00' && strtotime($ad_info['show_from_date']) > $now_date) || ($ad_info['show_to_date'] != '0000-00-00' && strtotime($ad_info['show_to_date']) < $now_date))
						{
							continue;
						}
						if ($ad_info['show_from_time'] > 0 && $ad_info['show_to_time'] > 0)
						{
							if ($now_time < $ad_info['show_from_time'] || $now_time > $ad_info['show_to_time'])
							{
								continue;
							}
						}
						if (@count($ad_info['devices']) > 0)
						{
							if (!class_exists('Mobile_Detect'))
							{
								include_once("$config[project_path]/admin/include/mobiledetect/Mobile_Detect.php");
							}
							if (class_exists('Mobile_Detect'))
							{
								$mobiledetect = new Mobile_Detect();
								$ad_device_show = false;
								foreach ($ad_info['devices'] as $ad_device)
								{
									if ($ad_device_show)
									{
										break;
									}
									switch ($ad_device)
									{
										case 'pc':
											$ad_device_show = !$mobiledetect->isMobile();
											break;
										case 'tablet':
											$ad_device_show = $mobiledetect->isTablet();
											break;
										case 'phone':
											$ad_device_show = $mobiledetect->isMobile() && !$mobiledetect->isTablet();
											break;
									}
								}
								if (!$ad_device_show)
								{
									continue;
								}
							}
						}
						if (@count($ad_info['browsers']) > 0)
						{
							$current_browser = get_user_agent_code();
							if (!in_array($current_browser, $ad_info['browsers']))
							{
								continue;
							}
						}
						if (@count($ad_info['users']) > 0)
						{
							$ad_user_show = false;
							foreach ($ad_info['users'] as $ad_user)
							{
								if ($ad_user_show)
								{
									break;
								}
								switch ($ad_user)
								{
									case 'guest':
										$ad_user_show = intval($_SESSION['user_id']) < 1;
										break;
									case 'active':
										$ad_user_show = intval($_SESSION['status_id']) == 2;
										break;
									case 'premium':
										$ad_user_show = intval($_SESSION['status_id']) == 3 || has_premium_access_by_tokens(intval($_REQUEST['video_id']));
										break;
									case 'webmaster':
										$ad_user_show = intval($_SESSION['status_id']) == 6;
										break;
								}
							}
							if (!$ad_user_show)
							{
								continue;
							}
						}

						$countries = explode(',', $ad_info['countries']);
						if (count($countries) == 0 || (count($countries) == 1 && $countries[0] == ''))
						{
							$ads_empty[] = $ad_info['advertisement_id'];
						} else
						{
							foreach ($countries as $country_code)
							{
								if (strtolower(trim($country_code)) == strtolower($_SERVER['GEOIP_COUNTRY_CODE']))
								{
									$ads[] = $ad_info['advertisement_id'];
									break;
								}
							}
						}
					}

					if (count($ads) == 0)
					{
						$ads = $ads_empty;
					}

					if (count($ads) > 0)
					{
						$advertisement_id = $ads[mt_rand(0, count($ads) - 1)];
						$ad_info = $spot_info['ads'][$advertisement_id];
						if (isset($ad_info))
						{
							$adv_code = $ad_info['code'];
							$adv_code = str_replace("%URL%", "$config[project_url]/?action=trace&amp;id=$ad_info[advertisement_id]", $adv_code);
							if ($spot_info['template'] != '')
							{
								$adv_code = str_replace("%ADV%", $adv_code, $spot_info['template']);
							}

							if (intval($spot_info['is_debug_enabled']) == 1)
							{
								$ads_str = implode(',', $ads);
								file_put_contents("$config[project_path]/admin/logs/debug_ad_spot_$spot_id.txt", date("[Y-m-d H:i:s] ") . "Dynamically displayed advertising $ad_info[advertisement_id] / \"$ad_info[title]\" from $ads_str for URI: $_SERVER[REQUEST_URI], User: $_SESSION[username], Agent: $_SERVER[HTTP_USER_AGENT], Country: $_SERVER[GEOIP_COUNTRY_CODE]\n", FILE_APPEND | LOCK_EX);
							}
						}
					} else
					{
						if (intval($spot_info['is_debug_enabled']) == 1)
						{
							file_put_contents("$config[project_path]/admin/logs/debug_ad_spot_$spot_id.txt", date("[Y-m-d H:i:s] ") . "No advertising for URI: $_SERVER[REQUEST_URI], User: $_SESSION[username], Agent: $_SERVER[HTTP_USER_AGENT], Country: $_SERVER[GEOIP_COUNTRY_CODE]\n", FILE_APPEND | LOCK_EX);
						}
					}
				} else
				{
					if (intval($spot_info['is_debug_enabled']) == 1)
					{
						file_put_contents("$config[project_path]/admin/logs/debug_ad_spot_$spot_id.txt", date("[Y-m-d H:i:s] ") . "No advertising for URI: $_SERVER[REQUEST_URI], User: $_SESSION[username], Agent: $_SERVER[HTTP_USER_AGENT], Country: $_SERVER[GEOIP_COUNTRY_CODE]\n", FILE_APPEND | LOCK_EX);
					}
				}
			}
		}
	}

	$adv_bg=trim($player_data["{$adv_id}_bg"]);
	if (!$adv_bg)
	{
		$adv_bg='#000000';
	}
	$adv_position='absolute';
	if (intval($player_data["{$adv_id}_adaptive"])==1)
	{
		$adv_position='static';
	}

	$adv_container='_iframe_content';
	if ($adv_code=='')
	{
		$adv_container='_iframe_disabled';
	}

	if (is_file("$config[project_path]/admin/data/system/runtime_params.dat") && filesize("$config[project_path]/admin/data/system/runtime_params.dat")>0)
	{
		$runtime_params=unserialize(@file_get_contents("$config[project_path]/admin/data/system/runtime_params.dat"));
		foreach ($runtime_params as $param)
		{
			$var=trim($param['name']);
			$val=$_SESSION['runtime_params'][$var];
			if (strlen($val)==0)
			{
				$val=trim($param['default_value']);
			}
			if ($var<>'')
			{
				$adv_code=str_replace("%$var%",$val,$adv_code);
			}
		}
	}

	$head_code='';
	unset($head_matches);
	if (preg_match('|<head>(.*)</head>|is',$adv_code,$head_matches))
	{
		$head_code=str_replace("\n"," ",$head_matches[1]);
		$adv_code=preg_replace('|<head>(.*)</head>|is','',$adv_code);
	}
	echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/><meta name=\"robots\" content=\"noindex, nofollow\">$head_code</head><body style=\"background: $adv_bg; margin: 0; padding: 0\"><div id=\"$adv_container\" style=\"position: $adv_position\">$adv_code</div></body></html>";
}

function has_premium_access_by_tokens($video_id)
{
	if ($_SESSION['status_id']==2)
	{
		foreach ($_SESSION['content_purchased'] as $purchase)
		{
			if ($purchase['video_id']==$video_id)
			{
				return true;
			}
		}
	}
	return false;
}

function get_content_source_info($cs_id)
{
	global $config;

	$cache_dir="$config[project_path]/admin/data/engine/cs_info";
	$hash=md5($cs_id);

	if (is_file("$cache_dir/$hash[0]$hash[1]/$hash.dat") && time()-filectime("$cache_dir/$hash[0]$hash[1]/$hash.dat")<86400)
	{
		$data=unserialize(file_get_contents("$cache_dir/$hash[0]$hash[1]/$hash.dat"));
		if (is_array($data))
		{
			return $data;
		}
	}

	$result=sql_pr("select * from $config[tables_prefix]content_sources where content_source_id=?",$cs_id);

	if (mr2rows($result)>0)
	{
		$data=mr2array_single($result);

		if (!is_dir("$cache_dir")) {mkdir("$cache_dir",0777);chmod("$cache_dir",0777);}
		if (!is_dir("$cache_dir/$hash[0]$hash[1]")) {mkdir("$cache_dir/$hash[0]$hash[1]",0777);chmod("$cache_dir/$hash[0]$hash[1]",0777);}
		file_put_contents("$cache_dir/$hash[0]$hash[1]/$hash.dat",serialize($data),LOCK_EX);

		return $data;
	} else {
		return false;
	}
}
