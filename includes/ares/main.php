<?php 
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	//------------------------------------------------------//
	function get_app_data($val)
	//------------------------------------------------------//
	{
		global $mysqli;
		$q = 'SELECT '.$val.' FROM apps WHERE id = "'.get_app_info('app').'" AND userID = '.get_app_info('main_userID');
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				return $row[$val];
		    }  
		}
	}
	
	//------------------------------------------------------//
	function get_lists_data($val, $lid)
	//------------------------------------------------------//
	{
		global $mysqli;
		$q = 'SELECT '.$val.' FROM lists WHERE app = "'.get_app_info('app').'" AND id = '.$lid;
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				return $row[$val];
		    }  
		}
	}
	
	//------------------------------------------------------//
	function get_ares_data($val)
	//------------------------------------------------------//
	{
		global $mysqli;
		$q = 'SELECT '.$val.' FROM ares WHERE id = '.mysqli_real_escape_string($mysqli, (int)$_GET['a']);
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				return $row[$val];
		    }  
		}
	}
	
	//------------------------------------------------------//
	function get_ares_type_name($val)
	//------------------------------------------------------//
	{
		$type = get_ares_data($val);
		switch($type)
		{
			case 1:
			return _('Drip campaign');
			break;
			
			case 2:
			return _('Sent annually based on').' <strong>'.get_ares_data('custom_field').'</strong>';
			break;
			
			case 3:
			return _('Sent once based on').' <strong>'.get_ares_data('custom_field').'</strong>';
			break;
		}
	}
	
	//------------------------------------------------------//
	function get_click_percentage($cid)
	//------------------------------------------------------//
	{
		global $mysqli;
		$clicks_join = '';
		$clicks_array = array();
		$clicks_unique = 0;
		
		$q = 'SELECT * FROM links WHERE ares_emails_id = '.$cid;
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
		    	$id = $row['id'];
				$link = $row['link'];
				$clicks = $row['clicks'];
				if($clicks!='')
					$clicks_join .= $clicks.',';				
		    }  
		}
		
		$clicks_array = explode(',', $clicks_join);
		$clicks_unique = count(array_unique($clicks_array));
		
		return $clicks_unique-1;
	}
	
	//------------------------------------------------------//
	function get_saved_data($val, $ares_email_id=0)
	//------------------------------------------------------//
	{
		global $mysqli;
		global $edit;
		
		$id = $ares_email_id==0 ? mysqli_real_escape_string($mysqli, (int)$_GET['ae']) : $ares_email_id;
		
		$q = 'SELECT '.$val.' FROM ares_emails WHERE id = '.$id;
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
		    	$value = $row[$val]=='' ? '' : stripslashes($row[$val]);
		    	
		    	//if title
		    	if($val == 'title' && !$edit)
		    	{
			    	//tags for subject
					preg_match_all('/\[([a-zA-Z0-9!#%^&*()+=$@._\-\:|\/?<>~`"\'\s]+),\s*fallback=/i', $value, $matches_var, PREG_PATTERN_ORDER);
					preg_match_all('/,\s*fallback=([a-zA-Z0-9!,#%^&*()+=$@._\-\:|\/?<>~`"\'\s]*)\]/i', $value, $matches_val, PREG_PATTERN_ORDER);
					preg_match_all('/(\[[a-zA-Z0-9!#%^&*()+=$@._\-\:|\/?<>~`"\'\s]+,\s*fallback=[a-zA-Z0-9!,#%^&*()+=$@._\-\:|\/?<>~`"\'\s]*\])/i', $value, $matches_all, PREG_PATTERN_ORDER);
					preg_match_all('/\[([^\]]+),\s*fallback=/i', $value, $matches_var, PREG_PATTERN_ORDER);
					preg_match_all('/,\s*fallback=([^\]]*)\]/i', $value, $matches_val, PREG_PATTERN_ORDER);
					preg_match_all('/(\[[^\]]+,\s*fallback=[^\]]*\])/i', $value, $matches_all, PREG_PATTERN_ORDER);
					$matches_var = $matches_var[1];
					$matches_val = $matches_val[1];
					$matches_all = $matches_all[1];
					for($i=0;$i<count($matches_var);$i++)
					{		
						$field = $matches_var[$i];
						$fallback = $matches_val[$i];
						$tag = $matches_all[$i];
						//for each match, replace tag with fallback
						$value = str_replace($tag, $fallback, $value);
					}
					$value = str_replace('[Name]', get_saved_data('from_name'), $value);
					$value = str_replace('[Email]', get_saved_data('from_email'), $value);
		    	}
				
				return $value;
		    }  
		}
	}
	
	//------------------------------------------------------//
	function ares_type()
	//------------------------------------------------------//
	{
		global $mysqli;
		global $aid;
			
		$q = 'SELECT type FROM ares WHERE id = '.$aid;
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				return $row['type'];
		    }  
		}
	}
	
	//------------------------------------------------------//
	function have_templates()
	//------------------------------------------------------//
	{
		global $mysqli;
		$q = 'SELECT COUNT(*) FROM template WHERE app = '.get_app_info('app').' AND userID = '.get_app_info('main_userID');
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				$rows = $row['COUNT(*)'];
		    }  
		    return $rows == 0 ? false : true;
		}
	}
	
	//------------------------------------------------------//
	function have_segments($list_id)
	//------------------------------------------------------//
	{
		global $mysqli;
		$q = 'SELECT COUNT(*) FROM seg WHERE app = '.get_app_info('app').' AND list = '.$list_id;
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				$rows = $row['COUNT(*)'];
		    }  
		    return $rows == 0 ? false : true;
		}
	}
?>