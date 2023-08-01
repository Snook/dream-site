<?php
require_once( 'includes/CMail.inc');
require_once( 'includes/CTemplate.inc');
require_once( 'includes/ValidationRules.inc');


class plate_points_mail_handlers
{

	/*	function send( $fromName,
						$fromEmail,
						$toName,
						$toEmail,
						 $subject,
						  $htmlMessage = null,
						   $textMessage = null,
						    $signature = '',
						     $cc = '',
						      $toId = null,
						       $templateName = null,
						        $replyTo = false,
						         $attachment = false,
						          $bcc = false )

	*/


	static function sendOptOutEmail($data)
	{

		$Mail = new CMail();

		$contentsText = CMail::mailMerge('platepoints/dream_rewards_opt_out_confirm.txt.php', $data);
		$contentsHtml = CMail::mailMerge('platepoints/dream_rewards_opt_out_confirm.html.php', $data);

		$Mail->send(null,
				null,
				$data['userObj']->firstname,
				$data['userObj']->primary_email,
				"PLATEPOINTS Opt-Out Confirmation",
				$contentsHtml,
				$contentsText,
				'',
				'',
				$data['userObj']->id,
				'dream_rewards_opt_out_confirm');

	}



	static function sendPlatePointslevelUpEmail($data)
	{
	    
	    /*
		$Mail = new CMail();

		$subjectLine = "Congrats! You are a PLATEPOINTS ";

		switch($data['program_summary']['current_level']['level'])
		{
			case 'chef':
				$contentsText = CMail::mailMerge('platepoints/badge_chef_level_up.txt.php', $data);
				$contentsHtml = CMail::mailMerge('platepoints/badge_chef_level_up.html.php', $data);
				$subjectLine .= "Chef";
				break;
			case 'station_chef':
				$contentsText = CMail::mailMerge('platepoints/badge_station_chef_level_up.txt.php', $data);
				$contentsHtml = CMail::mailMerge('platepoints/badge_station_chef_level_up.html.php', $data);
				$subjectLine .= "Station Chef";
				break;
			case 'sous_chef':
				$contentsText = CMail::mailMerge('platepoints/badge_sous_chef_level_up.txt.php', $data);
				$contentsHtml = CMail::mailMerge('platepoints/badge_sous_chef_level_up.html.php', $data);
				$subjectLine .= "Sous Chef";
				break;
			case 'head_chef':
				$contentsText = CMail::mailMerge('platepoints/badge_head_chef_level_up.txt.php', $data);
				$contentsHtml = CMail::mailMerge('platepoints/badge_head_chef_level_up.html.php', $data);
				$subjectLine .= "Head Chef";
				break;
			case 'executive_chef':
				$contentsText = CMail::mailMerge('platepoints/badge_executive_chef_level_up.txt.php', $data);
				$contentsHtml = CMail::mailMerge('platepoints/badge_executive_chef_level_up.html.php', $data);
				$subjectLine .= "Executive Chef";
				break;
			default:

				CLog::RecordIntense('invalid platepoints level email requested', 'ryan.snook@dreamdinners.com,evan.lee@dreamdinners.com');
				return;

		}

		$Mail->send(null,
				null,
				$data['userObj']->firstname,
				$data['userObj']->primary_email,
				$subjectLine,
				$contentsHtml,
				$contentsText,
				'',
				'',
				$data['userObj']->id,
				'level_up');
    */
	    
	    CLog::RecordEmail($data['userObj']->id, $data['userObj']->primary_email, null, null, "NOT SENT - RECORD ONLY - TYPE: PlatePoints Level Up - " . $data['program_summary']['current_level']['level']);
	    

	}


	static function sendPlatePointsWelcomeEmail($data)
	{

		$Mail = new CMail();

		$contentsText = CMail::mailMerge('platepoints/welcome_platepoints.txt.php', $data);
		$contentsHtml = CMail::mailMerge('platepoints/welcome_platepoints.html.php', $data);

		$Mail->send(null,
				null,
				$data['userObj']->firstname,
				$data['userObj']->primary_email,
				"Welcome to PLATEPOINTS",
				$contentsHtml,
				$contentsText,
				'',
				'',
				$data['userObj']->id,
				'credit_notify_platepoints');


	}

	static function sendPlatePointsCreditRewardEmail($data)
	{
	    /*
	    
		$Mail = new CMail();

		$contentsText = CMail::mailMerge('platepoints/credit_notify_platepoints.txt.php', $data);
		$contentsHtml = CMail::mailMerge('platepoints/credit_notify_platepoints.html.php', $data);

		$Mail->send(null,
			null,
			$data['userObj']->firstname,
			$data['userObj']->primary_email,
			"Congratulations! You have earned PLATEPOINTS Dinner Dollars",
			$contentsHtml,
			$contentsText,
			'',
			'',
			$data['userObj']->id,
			'credit_notify_platepoints');
			
		*/
	    
	    CLog::RecordEmail($data['userObj']->id, $data['userObj']->primary_email, null, null, "NOT SENT - RECORD ONLY - TYPE: PlatePoints Credit Award");
	    
	}


	static function sendPlatePointsExpiringCreditWarningEmail($data)
	{
	    /*
		$Mail = new CMail();

		$contentsText = CMail::mailMerge('platepoints/credit_expire_platepoints.txt.php', $data);
		$contentsHtml = CMail::mailMerge('platepoints/credit_expire_platepoints.html.php', $data);

		$Mail->send(null,
				null,
				$data->firstname,
				$data->primary_email,
				"Don't delay, your PLATEPOINTS Dinner Dollars are expiring soon!",
				$contentsHtml,
				$contentsText,
				'',
				'',
				$data->user_id,
				'credit_notify_platepoints');
		*/
	    
	    CLog::RecordEmail($data->user_id, $data->primary_email, null, null, "NOT SENT - RECORD ONLY - TYPE: Expiring Dinner Dollars");
	    
	    
	}


	static function sendPlatePointsBirthdayRewardEmail($data)
	{
/*
		$Mail = new CMail();

		$contentsText = CMail::mailMerge('platepoints/birthday_platepoints.txt.php', $data);
		$contentsHtml = CMail::mailMerge('platepoints/birthday_platepoints.html.php', $data);

		$Mail->send(null,
				null,
				$data['userObj']->firstname,
				$data['userObj']->primary_email,
				"It's Your Birthday!",
				$contentsHtml,
				$contentsText,
				'',
				'',
				$data['userObj']->id,
				'birthday_platepoints');
*/	
			CLog::RecordEmail($data->user_id, $data->primary_email, null, null, "NOT SENT - RECORD ONLY - TYPE: Birthday Reward");
				
				
	}

}

?>