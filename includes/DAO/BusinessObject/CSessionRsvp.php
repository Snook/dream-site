<?php

require_once 'DAO/Session_rsvp.php';

class CSessionRsvp extends DAO_Session_rsvp
{
	public $DAO_user;
	public $DAO_session;
	public $DAO_booking;
	public $DAO_menu;
	public $DAO_store;

	function __construct()
	{
		parent::__construct();
	}

	function find_DAO_session_rsvp($n = false)
	{
		if ($this->_query["data_select"] === "*")
		{
			throw new Exception("When creating this object, second parameter in DAO_CFactory::create() needs to be 'true'");
		}

		$DAO_session = DAO_CFactory::create('session', true);
		$DAO_session->joinAddWhereAsOn(DAO_CFactory::create('menu', true));
		$DAO_session->joinAddWhereAsOn(DAO_CFactory::create('store', true));
		$this->joinAddWhereAsOn($DAO_session);
		$this->joinAddWhereAsOn(DAO_CFactory::create('user', true));
		$this->joinAddWhereAsOn(DAO_CFactory::create('booking', true), 'LEFT');

		return parent::find($n);
	}

	function send_reminder_email()
	{
		try
		{
			$Mail = new CMail();

			$Mail->to_name = $this->DAO_user->firstname . ' ' . $this->DAO_user->lastname;
			$Mail->to_email = $this->DAO_user->primary_email;
			$Mail->to_id = $this->DAO_user->id;
			$Mail->subject = 'Reminder: Your Event is Almost Here';
			$Mail->body_html = CMail::mailMerge('session_reminder/session_rsvp_reminder.html.php', $this);
			$Mail->body_text = CMail::mailMerge('session_reminder/session_rsvp_reminder.txt.php', $this);
			$Mail->template_name = 'session_rsvp_reminder';

			if (defined('CRON_TEST_MODE') && CRON_TEST_MODE)
			{
				CLog::Record('CRON_TEST: ' . print_r($this, true));
			}
			else
			{
				$Mail->sendEmail();
			}
		}
		catch (exception $e)
		{
			// Don't let one bad apple ruin the whole bunch
			// on the other hand a systemic problem will cause a lot of exceptions (thousands)
			CLog::RecordException($e);
		}
	}
}
?>