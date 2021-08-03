<?php
/* Developed by Kernel Team.
   http://kernel-team.com
*/
header("Content-Type: application/json");

require_once '../admin/include/setup.php';
require_once '../admin/include/functions_base.php';

if ($_REQUEST['embed']=='true')
{
	$player_data=@unserialize(file_get_contents("$config[project_path]/admin/data/player/embed/config.dat"));
	$player_url="$config[content_url_other]/player/embed";
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
							$player_url="$config[content_url_other]/player/embed/$embed_folder";
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
		$player_url="$config[content_url_other]/player/premium";
	} elseif ($_SESSION['user_id']>0)
	{
		$player_dir="$config[project_path]/admin/data/player/active";
		$player_url="$config[content_url_other]/player/active";
	} else {
		$player_dir="$config[project_path]/admin/data/player";
		$player_url="$config[content_url_other]/player";
	}
	if (is_file("$player_dir/config.dat"))
	{
		$player_data=@unserialize(file_get_contents("$player_dir/config.dat"));
	} else {
		$player_data=@unserialize(file_get_contents("$config[project_path]/admin/data/player/config.dat"));
		$player_url="$config[content_url_other]/player";
	}
}

$banners=array();
for ($i=1;$i<=4;$i++)
{
	if ($player_data["enable_float$i"])
	{
		$time=$player_data["float{$i}_time"];
		$duration=$player_data["float{$i}_duration"];
		$pos=$player_data["float{$i}_location"];
		$size=$player_data["float{$i}_size"];
		$width=$player_data["float{$i}_size_width"];
		$height=$player_data["float{$i}_size_height"];
		$file="$player_url/{$player_data["float{$i}_file"]}";
		if ($player_data["float{$i}_file_source"]>1)
		{
			if (intval($_GET['cs_id'])>0)
			{
				if (!isset($cs_info))
				{
					$cs_info=get_content_source_info(intval($_GET['cs_id']));
				}
				if ($cs_info)
				{
					$cs_file_field='custom_file'.($player_data["float{$i}_file_source"]-1);
					if ($cs_info[$cs_file_field]!='')
					{
						$file="$config[content_url_content_sources]/$cs_info[content_source_id]/$cs_info[$cs_file_field]";
					}
				}
			}
		}
		$url=$player_data["float{$i}_url"];
		if ($player_data["float{$i}_url_source"]==2 && intval($_GET['cs_id'])>0)
		{
			$url="$config[project_url]/redirect_cs.php?id=$_GET[cs_id]";
		} elseif (is_file("$config[project_path]/admin/data/system/runtime_params.dat") && filesize("$config[project_path]/admin/data/system/runtime_params.dat")>0)
		{
			start_session();
			$runtime_params=unserialize(@file_get_contents("$config[project_path]/admin/data/system/runtime_params.dat"));
			foreach ($runtime_params as $param)
			{
				$var=trim($param['name']);
				$val=trim($_GET[$var]);
				if (strlen($val)==0)
				{
					$val=$_SESSION['runtime_params'][$var];
				}
				if (strlen($val)==0)
				{
					$val=trim($param['default_value']);
				}
				if ($var<>'')
				{
					$url=str_replace("%$var%",urlencode($val),$url);
				}
			}
		}
		$banners[]=array(
			'time'=>$time,
			'src'=>$file,
			'position'=>$pos,
			'width'=>(intval($size)==1?$width:""),
			'height'=>(intval($size)==1?$height:""),
			'duration'=>$duration,
			'url'=>$url,
		);
	}
}

echo json_encode($banners);

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
