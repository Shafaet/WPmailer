<?php
	function base64_url_encode($input) {
		 return strtr(base64_encode($input), '+/=', '-_,');
	}

	function base64_url_decode($input) {
		 return str_replace("\\","",base64_decode(strtr($input, '-_,', '+/=')));
	}
	function getDisplayName($id)
	{
		return get_userdata($id)->display_name;
	}
	function validateSubject($subject)
	{
		if(strlen($subject)==0) return False;
		if(strlen($subject)>200) return False;
		return True;
	}
?>
