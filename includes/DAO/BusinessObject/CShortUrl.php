<?php
require_once 'DAO/Short_url.php';

class CShortUrl extends DAO_Short_url
{

	function getPrettyUrl($full_url = false)
	{
		$short_url = null;

		switch ($this->page)
		{
			case 'location':
				$short_url = 'location/' . $this->short_url;
		}

		if(!empty($short_url))
		{
			$short_url = ($full_url ? HTTPS_BASE : WEB_BASE) . $short_url;
		}

		return $short_url;
	}
}
?>