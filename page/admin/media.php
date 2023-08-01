<?php
require_once("includes/CPageAdminOnly.inc");

class page_admin_media extends CPageAdminOnly
{

	function runManufacturerStaff()
	{
		$this->runMedia();
	}

	function runFranchiseStaff()
	{
		$this->runMedia();
	}

	function runFranchiseLead()
	{
		$this->runMedia();
	}

	function runFranchiseManager()
	{
		$this->runMedia();
	}
	
	function runOpsLead()
	{
		$this->runMedia();
	}

	function runFranchiseOwner()
	{
		$this->runMedia();
	}

	function runHomeOfficeStaff()
	{
		$this->runMedia();
	}

	function runHomeOfficeManager()
	{
		$this->runMedia();
	}

	function runSiteAdmin()
 	{
 		$this->runMedia();
 	}

 	function runEventCoordinator() 
 	{
 	    $this->runMedia();
 	}
 	
 	function runMedia()
 	{
 		$tpl = CApp::instance()->template();

 		//$sc_user_id = '50582820'; // https://soundcloud.com/ddpodcast
 		$sc_user_id = '51431805'; // https://soundcloud.com/s-a-a-s-team
 		$tracks = json_decode(self::getCURLdata('http://api.soundcloud.com/users/' . $sc_user_id . '/tracks.json?client_id=8de45224cb28613c574f9ce860805b1e'));
 		$tpl->assign('soundcloud_tracks', $tracks);

 		//$youtube = json_decode(self::getCURLdata('http://gdata.youtube.com/feeds/api/users/dreamdinnersvideo/uploads?alt=json'));
 		//$tpl->assign('youtube_videos', $youtube);
 	}

 	private function getCURLdata($url)
 	{
 		$ch = curl_init();

 		curl_setopt($ch, CURLOPT_HEADER, 0);
 		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
 		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
 		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
 		curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
 		curl_setopt($ch, CURLOPT_URL, $url);

 		$result = curl_exec($ch);

 		curl_close($ch);

 		return $result;
 	}

}
?>