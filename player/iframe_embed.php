<?php
/* Developed by Kernel Team.
   http://kernel-team.com
*/
require_once("../admin/include/setup.php");
require_once("../admin/include/functions_base.php");
require_once("../admin/include/functions.php");

$player_data=@unserialize(file_get_contents("$config[project_path]/admin/data/player/embed/config.dat"));
$player_template_file="$config[project_path]/admin/data/player/embed/config.tpl";
$player_error_file="$config[project_path]/admin/data/player/embed/error.tpl";
$player_profile_id='';

if ($player_data['black_list_countries']!='' && $_SERVER['GEOIP_COUNTRY_CODE']!='')
{
	$countries=array_map('trim',explode(',',$player_data['black_list_countries']));
	foreach ($countries as $country)
	{
		if (strtolower($country)==strtolower($_SERVER['GEOIP_COUNTRY_CODE']))
		{
			http_response_code(403);
			if ($player_data['player_replacement_html']!='')
			{
				echo $player_data['player_replacement_html'];
			} else {
				echo "$_SERVER[GEOIP_COUNTRY_CODE] country is blacklisted";
			}
			die;
		}
	}
}

if ($player_data['black_list_domains']!='' && $_SERVER['HTTP_REFERER']!='')
{
	$domains=array_map('trim',explode(',',$player_data['black_list_domains']));
	foreach ($domains as $domain)
	{
		$parsed_url=parse_url($_SERVER['HTTP_REFERER']);
		if ($parsed_url['host'])
		{
			if (strpos($parsed_url['host'],$domain)===strlen($parsed_url['host'])-strlen($domain))
			{
				http_response_code(403);
				if ($player_data['player_replacement_html']!='')
				{
					echo $player_data['player_replacement_html'];
				} else {
					echo str_replace("www.","",$parsed_url['host'])." host is blacklisted";
				}
				die;
			}
		}
	}
}

$embed_folders = get_contents_from_dir("$config[project_path]/admin/data/player/embed", 2);
foreach ($embed_folders as $embed_folder)
{
	if (is_file("$config[project_path]/admin/data/player/embed/$embed_folder/config.dat") && is_file("$config[project_path]/admin/data/player/embed/$embed_folder/config.tpl"))
	{
		$embed_profile = @unserialize(file_get_contents("$config[project_path]/admin/data/player/embed/$embed_folder/config.dat"));
		$embed_profile_domains = array_map('trim', explode(',', $embed_profile['embed_profile_domains']));
		foreach ($embed_profile_domains as $embed_profile_domain)
		{
			$found_profile = false;
			if ($_SERVER['HTTP_REFERER'] != '')
			{
				$parsed_url = parse_url($_SERVER['HTTP_REFERER']);
				if ($parsed_url['host'])
				{
					if (strpos($parsed_url['host'], $embed_profile_domain) === strlen($parsed_url['host']) - strlen($embed_profile_domain))
					{
						$found_profile = true;
					}
				} elseif ($embed_profile_domain == 'empty')
				{
					$found_profile = true;
				}
			} elseif ($embed_profile_domain == 'empty')
			{
				$found_profile = true;
			}

			if ($found_profile)
			{
				$player_data = $embed_profile;
				$player_template_file = "$config[project_path]/admin/data/player/embed/$embed_folder/config.tpl";
				$player_error_file = "$config[project_path]/admin/data/player/embed/$embed_folder/error.tpl";
				$player_profile_id = $embed_folder;
				break 2;
			}
		}
	}
}

$stats_params = @unserialize(@file_get_contents("$config[project_path]/admin/data/system/stats_params.dat"));
$device_type = 0;
if (intval($stats_params['collect_traffic_stats_devices']) == 1)
{
	$device_type = get_device_type();
}
file_put_contents("$config[project_path]/admin/data/stats/embed.dat", date("Y-m-d")."|$_SERVER[GEOIP_COUNTRY_CODE]|$_SERVER[HTTP_REFERER]|$_REQUEST[video_id]|$_SERVER[REMOTE_ADDR]|$device_type\r\n", LOCK_EX | FILE_APPEND);

start_session();
$website_ui_data=@unserialize(@file_get_contents("$config[project_path]/admin/data/system/website_ui_params.dat"));
$stats_params=@unserialize(@file_get_contents("$config[project_path]/admin/data/system/stats_params.dat"));

if (is_file("$config[project_path]/admin/data/system/runtime_params.dat") && filesize("$config[project_path]/admin/data/system/runtime_params.dat")>0)
{
	$runtime_params=unserialize(@file_get_contents("$config[project_path]/admin/data/system/runtime_params.dat"));
	foreach ($runtime_params as $param)
	{
		$var=trim($param['name']);
		if (isset($_GET[$var]) || isset($_POST[$var]) || isset($_COOKIE["kt_rt_$var"]))
		{
			$val=$_GET[$var];
			if ($val=='') {$val=$_POST[$var];}
			if ($val=='') {$val=$_COOKIE["kt_rt_$var"];}
			if ($var<>'' && $val<>'')
			{
				$_SESSION['runtime_params'][$var]=$val;
				if (isset($_GET[$var]) || isset($_POST[$var]))
				{
					$val_lifetime=intval($param['lifetime']);
					if ($val_lifetime==0)
					{
						$val_lifetime=360;
					}
					set_cookie("kt_rt_$var",$val,time()+$val_lifetime*86400);
				}
			}
		}
	}
}

$page_id='system_iframe_embed';
$block_id='video_view';
$object_id='video_view_system_iframe_embed';
$cache_time=86400;
if (isset($player_data['embed_cache_time']))
{
	$cache_time=intval($player_data['embed_cache_time']);
}
$config_params=array('var_video_id'=>'video_id','var_video_dir'=>'dir','embed_player_profile_id'=>$player_profile_id);

require_once("$config[project_path]/admin/include/setup_smarty_site.php");
require_once("$config[project_path]/blocks/$block_id/$block_id.php");

$smarty=new mysmarty_site();
$smarty->assign_by_ref("config",$config);

$pre_process_function="{$block_id}PreProcess";
if (function_exists($pre_process_function))
{
	$pre_process_function($config_params,$object_id);
}

$hash_function="{$block_id}GetHash";
$block_hash=$hash_function($config_params);
if (in_array($block_hash,array('nocache','runtime_nocache'))) {$is_no_cache=1;} else {$is_no_cache=0;}
if ($website_ui_data['WEBSITE_CACHING']==2)
{
	$is_no_cache=1;
}
if (in_array(trim($_REQUEST['skin']),array('black','white')))
{
	$block_hash="$block_hash|skin=".trim($_REQUEST['skin']);
}
if (in_array(trim($_REQUEST['autoplay']),array('true','false','1','0')))
{
	$block_hash="$block_hash|autoplay=".trim($_REQUEST['autoplay']);
}
$block_hash="$config[project_url]|$page_id|$object_id|$player_profile_id|$block_hash";

if ($config['project_url_scheme']=="https")
{
	$block_hash="https|$block_hash";
}
if ($config['device']<>"")
{
	$block_hash="$config[device]|$block_hash";
}
if ($config['relative_post_dates']=="true")
{
	$relative_post_date=0;
	if ($_SESSION['user_id']>0 && $_SESSION['added_date']<>'')
	{
		$registration_date=strtotime($_SESSION['added_date']);
		$relative_post_date=floor((time()-$registration_date)/86400)+1;
	}
	$block_hash="$relative_post_date|$block_hash";
}
$block_hash=md5($block_hash);

if ($cache_time>0 && $_SESSION['userdata']['user_id']<1 && $is_no_cache<>1)
{
	$smarty->caching=1;
	$smarty->cache_lifetime=$cache_time;

	if ($smarty->is_cached($player_template_file,$block_hash))
	{
		echo replace_runtime_params($smarty->fetch($player_template_file,$block_hash));
		die;
	}
}

require_once("$config[project_path]/admin/include/database_selectors.php");
include_once("$config[project_path]/admin/include/list_countries.php");

if (is_file("$config[project_path]/langs/default.php"))
{
	include_once("$config[project_path]/langs/default.php");
}
if ($config['locale']<>'' && is_file("$config[project_path]/langs/$config[locale].php"))
{
	include_once("$config[project_path]/langs/$config[locale].php");
}

$smarty->assign("list_countries",$list_countries['name']);
$smarty->assign("list_countries_codes",$list_countries['code']);
if (is_array($lang))
{
	$smarty->assign("lang",$lang);
}

$show_block_function="{$block_id}Show";
$show_result=$show_block_function($config_params,$object_id);
if ($show_result=='status_404')
{
	ob_end_clean();
	http_response_code(404);
	if (is_file($player_error_file))
	{
		echo replace_runtime_params($smarty->fetch($player_error_file));
	} elseif (is_file("$config[project_path]/404.html"))
	{
		echo @file_get_contents("$config[project_path]/404.html");
	} else {
		echo "The requested URL was not found on this server.";
	}
	die;
}
$smarty->assign('block_uid',$object_id);
echo replace_runtime_params($smarty->fetch($player_template_file,$block_hash));

function replace_runtime_params($page)
{
	global $config, $runtime_params, $storage;

	$page=trim($page);

	// advanced advertising
	$pos = strpos($page, '%KTV:');
	if ($pos !== false)
	{
		$result = '';
		$pos2 = 0;
		while ($pos !== false)
		{
			$length = strpos($page, '%', $pos + 1) + 1 - $pos;
			$token = substr($page, $pos + 5, $length - 6);
			$profile_id = $token;

			$ads = [];
			$ads_sorting = [];

			$profile_data_file = "$config[project_path]/admin/data/player/vast/vast_$profile_id.dat";
			$profile_info = null;
			if (is_file($profile_data_file))
			{
				$profile_info = @unserialize(file_get_contents($profile_data_file));
			}

			$seen_ads = [];
			if (is_array($profile_info) && is_array($profile_info['providers']))
			{
				if (trim($_COOKIE["kt_vast_$profile_id"]))
				{
					$seen_ads = explode(',', trim($_COOKIE["kt_vast_$profile_id"]));
				}

				$category_ids_in_context = [];
				foreach ($storage as $storage_info)
				{
					if (is_array($storage_info['categories']))
					{
						foreach ($storage_info['categories'] as $category_info)
						{
							if (intval($category_info['category_id']) > 0)
							{
								$category_ids_in_context[intval($category_info['category_id'])] = intval($category_info['category_id']);
							}
						}
					}
				}

				foreach ($profile_info['providers'] as $provider)
				{
					if (intval($provider['is_enabled']) == 0)
					{
						continue;
					}

					$show_ad_devices = false;
					$show_ad_categories = false;
					$show_ad_countries = false;
					$show_ad_referers = false;
					$skip_ad = false;

					if (array_count($provider['devices']) == 0)
					{
						$show_ad_devices = true;
					} else
					{
						if (!class_exists('Mobile_Detect'))
						{
							include_once "$config[project_path]/admin/include/mobiledetect/Mobile_Detect.php";
						}
						if (class_exists('Mobile_Detect'))
						{
							$mobiledetect = new Mobile_Detect();
							foreach ($provider['devices'] as $ad_device)
							{
								switch ($ad_device)
								{
									case 'pc':
										$show_ad_devices = !$mobiledetect->isMobile();
										break;
									case 'tablet':
										$show_ad_devices = $mobiledetect->isTablet();
										break;
									case 'phone':
										$show_ad_devices = $mobiledetect->isMobile() && !$mobiledetect->isTablet();
										break;
								}
								if ($show_ad_devices)
								{
									break;
								}
							}
						}
					}

					if (!$provider['categories'])
					{
						$show_ad_categories = true;
					} else
					{
						$categories = explode(',', $provider['categories']);
						foreach ($categories as $category_id)
						{
							if (in_array(trim($category_id), $category_ids_in_context))
							{
								$show_ad_categories = true;
								break;
							}
						}
					}

					if (!$provider['countries'])
					{
						$show_ad_countries = true;
					} else
					{
						$countries = explode(',', $provider['countries']);
						foreach ($countries as $country_code)
						{
							if (strtolower(trim($country_code)) == strtolower($_SERVER['GEOIP_COUNTRY_CODE']))
							{
								$show_ad_countries = true;
								break;
							}
						}
					}

					if (!$provider['referers'])
					{
						$show_ad_referers = true;
					} else
					{
						$referers = array_map('trim', explode("\n", $provider['referers']));
						foreach ($referers as $referer)
						{
							if ($referer)
							{
								if (is_url($referer))
								{
									$referer_host = str_replace('www.', '', trim(parse_url($referer, PHP_URL_HOST)));
									$current_referer_host = str_replace('www.', '', trim(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST)));
									if (strpos($current_referer_host, $referer_host) === 0)
									{
										$show_ad_referers = true;
										break;
									}
								} elseif (strpos($_SERVER['REQUEST_URI'], $referer) !== false)
								{
									$show_ad_referers = true;
									break;
								}
							}
						}
					}

					if ($provider['exclude_categories'])
					{
						$categories = explode(',', $provider['exclude_categories']);
						foreach ($categories as $category_id)
						{
							if (in_array(trim($category_id), $category_ids_in_context))
							{
								$skip_ad = true;
								break;
							}
						}
					}

					if ($provider['exclude_countries'])
					{
						$countries = explode(',', $provider['exclude_countries']);
						foreach ($countries as $country_code)
						{
							if (strtolower(trim($country_code)) == strtolower($_SERVER['GEOIP_COUNTRY_CODE']))
							{
								$skip_ad = true;
								break;
							}
						}
					}

					if ($provider['exclude_referers'])
					{
						$referers = array_map('trim', explode("\n", $provider['exclude_referers']));
						foreach ($referers as $referer)
						{
							if ($referer)
							{
								if (is_url($referer))
								{
									$referer_host = str_replace('www.', '', trim(parse_url($referer, PHP_URL_HOST)));
									$current_referer_host = str_replace('www.', '', trim(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST)));
									if (strpos($current_referer_host, $referer_host) === 0)
									{
										$skip_ad = true;
										break;
									}
								} elseif (strpos($_SERVER['REQUEST_URI'], $referer) !== false)
								{
									$skip_ad = true;
									break;
								}
							}
						}
					}

					if ($show_ad_devices && $show_ad_categories && $show_ad_countries && $show_ad_referers && !$skip_ad)
					{
						$ads[] = $provider;
						$ads_sorting[] = intval($provider['weight']);
					}
				}
			}

			array_multisort($ads_sorting, SORT_NUMERIC, SORT_DESC, $ads);

			$temp_ads = $ads;
			foreach ($temp_ads as $k => $provider)
			{
				if (in_array(md5($provider['url']), $seen_ads))
				{
					unset($temp_ads[$k]);
				}
			}
			if (count($temp_ads) == 0)
			{
				$temp_ads = $ads;
				$seen_ads = [];
			}

			if (count($temp_ads) > 0)
			{
				$provider = array_pop(array_reverse($temp_ads));
				$seen_ads[] = md5($provider['url']);

				set_cookie("kt_vast_$profile_id", trim(implode(',', $seen_ads), ', '), time() + 86400);

				$token = $provider['url'];
				if (intval($profile_info['is_debug_enabled']) == 1)
				{
					$seen_ads_count = count($seen_ads) - 1;
					file_put_contents("$config[project_path]/admin/logs/debug_vast_profile_$profile_id.txt", date("[Y-m-d H:i:s] ") . "Displayed VAST $token after $seen_ads_count displayed ads for URI: $_SERVER[REQUEST_URI], Agent: $_SERVER[HTTP_USER_AGENT], Country: $_SERVER[GEOIP_COUNTRY_CODE]\n", FILE_APPEND | LOCK_EX);
				}
				if ($provider['alt_url'])
				{
					$alternate_vasts = [];
					foreach (array_map('trim', explode("\n", $provider['alt_url'])) as $vast)
					{
						if ($vast)
						{
							$alternate_vasts[] = $vast;
						}
					}
					if (count($alternate_vasts) > 0)
					{
						$token .= '|' . implode('|', $alternate_vasts);
					}
				}
			} else
			{
				$token = '';
				if (intval($profile_info['is_debug_enabled']) == 1)
				{
					file_put_contents("$config[project_path]/admin/logs/debug_vast_profile_$profile_id.txt", date("[Y-m-d H:i:s] ") . "No VAST for URI: $_SERVER[REQUEST_URI], Agent: $_SERVER[HTTP_USER_AGENT], Country: $_SERVER[GEOIP_COUNTRY_CODE]\n", FILE_APPEND | LOCK_EX);
				}
			}

			$result .= substr($page, $pos2, $pos - $pos2) . $token;
			$pos2 = $pos + $length;
			$pos = strpos($page, '%KTV:', $pos + 1);
		}
		$result .= substr($page, $pos2);
		$page = $result;
	}

	if (is_array($runtime_params))
	{
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
				$val=str_replace("\"","&#34;",$val);
				$val=str_replace(">","&gt;",$val);
				$val=str_replace("<","&lt;",$val);
				$page=str_replace("%$var%",$val,$page);
			}
		}
	}

	$hotlink_data = @unserialize(file_get_contents("$config[project_path]/admin/data/system/hotlink_info.dat"));
	if (intval($hotlink_data['ENABLE_ANTI_HOTLINK'])==1 && intval($hotlink_data['ANTI_HOTLINK_TYPE']) == 1)
	{
		$lock_ips = explode(',', trim($_COOKIE['kt_ips']));
		if (!in_array($_SERVER['REMOTE_ADDR'], $lock_ips))
		{
			$lock_ips[] = $_SERVER['REMOTE_ADDR'];
		}

		set_cookie("kt_ips", trim(implode(',', $lock_ips), ', '), time() + 86400);

		$lock_ip = $_SERVER['REMOTE_ADDR'];
		if (!is_array($_SESSION['lock_ips']) || !isset($_SESSION['lock_ips'][$_SERVER['REMOTE_ADDR']]))
		{
			$_SESSION['lock_ips'][$_SERVER['REMOTE_ADDR']] = 1;
		}
		$pos = strpos($page, '/get_file/');
		if ($pos !== false)
		{
			$result = '';
			$pos2 = 0;
			while ($pos !== false)
			{
				$pos = strpos($page, '/', $pos + 10) + 1;
				$length = strpos($page, '/', $pos + 1) - $pos;
				$token = substr($page, $pos, $length);
				if ($length == 32)
				{
					$token .= substr(md5($token . $config['cv'] . $lock_ip), 0, 10);
				}

				$result .= substr($page, $pos2, $pos - $pos2) . $token;
				$pos2 = $pos + $length;
				$pos = strpos($page, '/get_file/', $pos + 1);
			}
			$result .= substr($page, $pos2, strlen($page) - $pos2);
			$page = $result;
		}
	}

	return $page;
}
