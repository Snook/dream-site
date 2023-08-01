<?php
require_once 'DAO.inc';

class CContactImport extends DAO
{
	static function getGoogleContactsArray($encoded_json)
	{
		$contacts = array();

		$json = json_decode($encoded_json);

		foreach ($json->feed->entry AS $contact_id => $contact)
		{
			if (!empty($contact->{'gd$email'}))
			{
				if (empty($contacts[$contact_id]))
				{
					$contacts[$contact_id] = array(
						'name' => $contact->title->{'$t'},
						'emails' => array()
					);
				}

				foreach ($contact->{'gd$email'} AS $id => $email)
				{
					$contacts[$contact_id]['emails'][$id] = $email->address;
				}
			}
		}

		return $contacts;
	}

	static function getMSGraphContactsArray($encoded_json)
	{
		$contacts = array();

		$json = json_decode($encoded_json);

		foreach ($json->value AS $contact_id => $contact)
		{
			if (!empty($contact->emailAddresses))
			{
				if (empty($contacts[$contact_id]))
				{
					$contacts[$contact_id] = array(
						'name' => $contact->displayName,
						'emails' => array()
					);
				}

				foreach ($contact->emailAddresses AS $id => $email)
				{
					$contacts[$contact_id]['emails'][$id] = $email->address;
				}
			}
		}

		return $contacts;
	}

	static function getReferredContactsArray($User)
	{
		$contacts = array();

		$referrals = DAO_CFactory::create('customer_referral');
		$referrals->query("SELECT 
			id, 
			referred_user_name, 
			referred_user_email 
			FROM `customer_referral` 
			WHERE referring_user_id = '" . $User->id . "' AND is_deleted = '0' 
			GROUP BY referred_user_email 
			ORDER BY referred_user_name, referred_user_email");

		while($referrals->fetch())
		{
			if ($referrals->referred_user_email != $User->primary_email)
			{
				if (empty($contacts[$referrals->id]))
				{
					$contacts[$referrals->id] = array(
						'name' => ((!empty($referrals->referred_user_name)) ? $referrals->referred_user_name : $referrals->referred_user_email),
						'emails' => array()
					);
				}

				$contacts[$referrals->id]['emails'][$referrals->id] = $referrals->referred_user_email;
			}
		}

		return $contacts;
	}
}
?>