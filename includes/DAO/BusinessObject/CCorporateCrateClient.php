<?php

	require_once 'DAO/Corporate_crate_client.php';


	/* ------------------------------------------------------------------------------------------------
	 *	Class: CCorporateCrateClient
	 *
	 *	Data:
	 *
	 *	Methods:
	 *		Create()
	 *
	 *  	Properties:
	 *
	 *
	 *	Description:
	 *
	 *
	 *	Requires:
	 *
	 * -------------------------------------------------------------------------------------------------- */


	class CCorporateCrateClient extends DAO_Corporate_crate_client
	{
		static $corporate_crate_client = array();

		static function getCorporateCrateDomainFromEmail($email)
		{
			$mailParts = explode("@", trim($email));
			$domain = $mailParts[1];

			return $domain;
		}

		static function isEmailAddressCorporateCrateEligible($email)
		{
			$domain = self::getCorporateCrateDomainFromEmail($email);

			$corpClients = DAO_CFactory::create('corporate_crate_client');
			$corpClients->query("select id from corporate_crate_client where triggering_domain = '$domain' and is_active = 1");

			if ($corpClients->N > 0)
			{
				return true;
			}

			return false;
		}


		static function getArrayOfAllClients()
		{
			$retVal = array();
			$corpClients = DAO_CFactory::create('corporate_crate_client');
			$corpClients->query("select * from corporate_crate_client where is_active = 1");
			while($corpClients->fetch())
			{
				$retVal[$corpClients->triggering_domain] = DAO::getCompressedArrayFromDAO($corpClients, true);
			}
			return $retVal;
		}


		static function corporateCrateClientDetails($email)
		{
			$domain = self::getCorporateCrateDomainFromEmail($email);

			if (!empty(self::$corporate_crate_client[$domain]))
			{
				return self::$corporate_crate_client[$domain];
			}

			$corpClients = DAO_CFactory::create('corporate_crate_client');
			$corpClients->query("select * from corporate_crate_client where triggering_domain = '$domain'");

			if (!$corpClients->fetch())
			{
				return false;
			}

			return self::$corporate_crate_client[$corpClients->triggering_domain] = $corpClients;
		}

	}

?>