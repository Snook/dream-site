<?php
	require_once( 'DAO/User_referral_source.php' );

	class CUserReferralSource extends DAO_User_referral_source
	{
		const SEARCH_ENGINE	= 'SEARCH_ENGINE';
		const WEB_AD		= 'WEB_AD';
		const PRESS_RELEASE	= 'PRESS_RELEASE';
		const WORD_OF_MOUTH	= 'WORD_OF_MOUTH';
		const NEWS_MAG		= 'NEWS_MAG';
		const DD_NEWSLETTER	= 'DD_NEWSLETTER';
		const SNEAK_PEEK	= 'SNEAK_PEEK';
		const INTRO_OFFER	= 'INTRO_OFFER';
		const EMPLOYER		= 'EMPLOYER';
		const WEBSITE		= 'WEBSITE';
		const CHURCH		= 'CHURCH';
		const SPECIAL_EVENT	= 'SPECIAL_EVENT';
		const CUSTOMER_REFERRAL = 'CUSTOMER_REFERRAL';
		const TV_OFFER = 'TV_OFFER';
		// added for dreamsite2
		const TASTE_EVENT = 'TASTE_EVENT';
		const SAW_STORE = 'SAW_STORE';
		const GROUPON = 'GROUPON';
		const DIRECT_MAIL = 'DIRECT_MAIL';
		const RADIO = 'RADIO';
		const TELEVISION = 'TELEVISION';
		const OTHER = 'OTHER';
		const FACEBOOK = 'FACEBOOK';
		const PINTEREST = 'PINTEREST';
		const INSTAGRAM = 'INSTAGRAM';
		const BLOG = 'BLOG';
		const TWITTER = 'TWITTER';
		const OTHER_SOCIAL_MEDIA = 'OTHER_SOCIAL_MEDIA';
		const YELP = 'YELP';
		const VIRTUAL_PARTY = 'VIRTUAL_PARTY';
		const SOCIAL_MEDIA = 'SOCIAL_MEDIA';

		public static function customerReferralText($source)
		{
			// Only needs strings that are different than contsant

			switch (strtoupper($source))
			{
				case WEB_AD:
					return 'Web Advertisement';
					break;
				case WORD_OF_MOUTH:
					return 'Friend';
					break;
				case NEWS_MAG:
					return 'Newspaper/Magazine';
					break;
				case DD_NEWSLETTER:
					return 'Newsletter';
					break;
				case INTRO_OFFER:
					return 'Menu Sampler';
					break;
				case TASTE_EVENT:
					return 'Taste, In-store Event or Party';
					break;
				case SAW_STORE:
					return 'Drove By and Was Curious';
					break;
				case GROUPON:
					return 'Groupon, Living Social or Online Coupon Site';
					break;
				default:
					return str_replace('_', " ", ucwords(strtolower($source)));
			}
		}

		public static function deleteCustomerReferral($userDAO)
		{
			$referrals = DAO_CFactory::create('user_referral_source');
			$referrals->user_id = $userDAO->id;
			$referrals->source = CUserReferralSource::CUSTOMER_REFERRAL;

			while($referrals->fetch())
			{
				$referrals->delete();
			}
		}

		public static function hasReferralSource($user_id)
		{

			if (empty($user_id))
				return false;

			$referrals = DAO_CFactory::create('user_referral_source');
			$referrals->user_id = $user_id;

			if ($referrals->find())
				return true;

			return false;
		}

		public static function getCustomerReferral($referralForUserID)
		{
			$referrals = DAO_CFactory::create('user_referral_source');
			$referrals->query("select urs.meta, CONCAT(u.firstname, ' ', u.lastname) as full_name, cr.timestamp_created as referral_date from user_referral_source urs
					join user u on urs.meta = u.primary_email
					left join customer_referral cr on cr.id = urs.customer_referral_id
					where urs.user_id = '" . $referralForUserID . "'
					and source = '" . CUserReferralSource::CUSTOMER_REFERRAL . "'
					and meta is not null and urs.is_deleted = 0 and cr.is_deleted = 0 order by urs.id desc" );

			if ($referrals->fetch())
			{
				$dateStr = (empty($referrals->referral_date) ? "" : " (" . CTemplate::dateTimeFormat($referrals->referral_date) . ")");

				// just get the first one .. should only be one
				return $referrals->full_name . $dateStr;
			}

			return false;
		}

		public static function insertSources( $idUser, $arSources, $inviting_user_id = false, $customer_referral_id = false )
		{
			if(!empty($arSources))
			{
				// first delete any existing sources
				$refSourceDelete = DAO_CFactory::create( 'user_referral_source' );
				$refSourceDelete->user_id = $idUser;
				$refSourceDelete->find();

				while ($refSourceDelete->fetch())
				{
					$refSourceDelete->delete();
				}

				foreach( $arSources as $type => $meta )
				{
					$refSource = DAO_CFactory::create( 'user_referral_source' );
					$refSource->user_id = $idUser;
					$refSource->source = $type;
					$refSource->meta = $meta;

					if ($type == self::WORD_OF_MOUTH)
					{
						$refSource->inviting_user_id = ($inviting_user_id ? $inviting_user_id : 'null');
					}
					else if ($type == self::CUSTOMER_REFERRAL)
					{
						$refSource->inviting_user_id = ($inviting_user_id ? $inviting_user_id : 'null');
						$refSource->customer_referral_id = ($customer_referral_id ? $customer_referral_id : 'null');
					}
					else
					{
						$refSource->inviting_user_id = 'null';
					}

					$refSource->insert();
				}
			}
		}

		public static function insertCustomerReferralSource( $idUser, $referrers_email, $inviting_user_id = false, $customer_referral_id = false )
		{

			$refSource = DAO_CFactory::create( 'user_referral_source' );
			$refSource->user_id = $idUser;
			$refSource->source = self::CUSTOMER_REFERRAL;
			$refSource->meta = $referrers_email;

			$refSource->inviting_user_id = ($inviting_user_id ? $inviting_user_id : 'null');
			$refSource->customer_referral_id = ($customer_referral_id ? $customer_referral_id : 'null');

			$refSource->insert();
		}

		public static function updateOrCreateCustomerReferral($User, $session_id, $referral_id)
		{
			$refSource = DAO_CFactory::create( 'user_referral_source' );
			$refSource->user_id = $User->id;
			$refSource->source = self::CUSTOMER_REFERRAL;


			$event = DAO_CFactory::create('session_properties');
			$event->query("select tsp.session_host, u.primary_email from session_properties tsp
					join user u on tsp.session_host = u.id where tsp.session_id = '" . $session_id . "'");
			$event->fetch();

			if ($refSource->find(true))
			{
				$refSource->meta = $event->primary_email;
				$refSource->inviting_user_id = $event->session_host;
				$refSource->customer_referral_id = $referral_id;
				$refSource->update();
			}
			else
			{
				$refSource->meta = $event->primary_email;
				$refSource->inviting_user_id = $event->session_host;
				$refSource->customer_referral_id = $referral_id;
				$refSource->insert();
			}

		}


		static function is_referral_V2_active()
		{
		    if (isset($_COOKIE['RSV2_Origination_code']))
		    {
		        return true;
		    }
		    else
		    {
		        return false;
		    }
		}


		static function is_invite_active()
		{
			if (isset($_COOKIE['IAF_last_access_time']))
			{
				$lastAccess = $_COOKIE['IAF_last_access_time'];
				if (time() - $lastAccess > 43200)
				{
					self::clear_invite();
					return false;
				}
				else if (isset($_COOKIE['IAF_inviting_user']) && isset($_COOKIE['IAF_inviting_user_id']))
				{
					return true;
				}
				else
				{
					self::clear_invite();
					return false;
				}
			}
			else
			{
				return false;
			}
		}

		static function is_invite2_active()
		{
			if (isset($_COOKIE['IAF2_origination_uid']))
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		static function clear_invite()
		{
			if (isset($_COOKIE['IAF_inviting_user']))
			{
				CBrowserSession::setValue('IAF_inviting_user', false);
			}
			if (isset($_COOKIE['IAF_inviting_user_id']))
			{
				CBrowserSession::setValue('IAF_inviting_user_id', false);
			}
			if (isset($_COOKIE['IAF_last_access_time']))
			{
				CBrowserSession::setValue('IAF_last_access_time', false);
			}
		}

		function processNewReferral($uid, $tpl)
		{
			$tpl = CApp::instance()->template();

			if (CUser::isLoggedIn())
			{
				CBrowserSession::instance()->ExpireSession();
			}

			$referral = DAO_CFactory::create('customer_referral');

			$referral->origination_uid = $uid;

			if (!$referral->find(true))
			{
				$tpl->assign('problem', 'referral_not_found');
			}
			else
			{
				$session_id = $referral->referrer_session_id;
				$inviting_user = $referral->inviting_user_name;
				$inviting_user_id = $referral->referring_user_id;
				$is_intro = $referral->referrers_order_is_sampler;

				CBrowserSession::setValueAndDuration('IAF2_inviting_user', $inviting_user, 86400 * 7);
				CBrowserSession::setValueAndDuration('IAF2_inviting_user_id', $inviting_user_id, 86400 * 7);
				CBrowserSession::setValueAndDuration('IAF2_origination_uid', $uid, 86400 * 7);

				if ($session_id)
				{
					$session = DAO_CFactory::create('session');
					$session->id = $session_id;

					$canForward = true;
					$canSaveStore = true;
					$canSaveSession = true;

					if ($session->find(true))
					{
						// TODO: Check that everything is still valid
						// * session is open
						// * seesion is not full

						$StoreObj = DAO_CFactory::create('store');
						$StoreObj->id = $session->store_id;
						if (!$StoreObj->find(true))
						{
							$tpl->assign('problem', 'no_store');
							$canForward = false;
							$canSaveStore = false;
						}
						else if (!$session->isOpen($StoreObj))
						{
							$tpl->assign('problem', 'session_closed');
							$canForward = false;
						}

						$standardSlotsAvailable = $session->getRemainingSlots();

						if ($is_intro && $session->getRemainingIntroSlots() <= 0)
						{
							$tpl->assign('problem', 'intro_session_full');
							$canForward = false;
							$canSaveSession = false;

							if ($standardSlotsAvailable > 0)
							{ // can link them to menu page if session has std slots
								$canSaveSession = true;
								$tpl->assign('conversion_link', HTTPS_SERVER . "/main.php?page=session_menu");
							}
						}

						if (!$is_intro && $standardSlotsAvailable <= 0)
						{
							$tpl->assign('problem', 'session_full');
							$canSaveSession = false;
							$canForward = false;
						}


						if ($canSaveSession || $canSaveStore)
						{
							$OrderSave = DAO_CFactory::create('orders');

							if ($canSaveSession) $OrderSave->addSession($session);
							if ($canSaveStore) $OrderSave->store_id = $session->store_id;
							CCart2::instance()->emptyCart();
							if ($canSaveSession) CCart2::instance()->addMenuId($session->menu_id);
							CCart2::instance()->addOrder($OrderSave);
						}

						if ($canForward)
						{
							if ($is_intro)
							{
								//TODO: trigger TV Offer logic?
								CApp::bounce('main.php?page=session_menu');
							}
							else
							{
								CApp::bounce('main.php?page=session_menu');
							}
						}
						else
						{
							$altLink = HTTPS_SERVER . "/main.php?page=session_menu";
							$tpl->assign('alt_link', $altLink);
						}
					}
					else
					{
						$tpl->assign('problem', 'session_not_found');
					}

				}
				else
				{
					$tpl->assign('problem', 'session_not_found');
				}
			}

			self::doError($tpl);
		}

		private function doError($tpl)
		{
			if ($tpl->problem == "session_closed")
			{
				$tpl->setErrorMsg("We&rsquo;re sorry, the session that you were invited to is closed. The date may have passed or the session may have been closed due to extenuating circumstances.
					Here you may view other sessions at your friend&rsquo;s Dream Dinners location.");
				CApp::bounce('main.php?page=session_menu');
			}
			else if ($tpl->problem == "session_full")
			{
				$tpl->setErrorMsg("We&rsquo;re sorry, the session that you were invited to is full. Here you may view other sessions at your friend&rsquo;s Dream Dinners location.");
				CApp::bounce('main.php?page=session_menu');
			}
			else if ($tpl->problem == "intro_session_full")
			{
				$tpl->setErrorMsg("You have clicked a link to our Meal Prep Starter Pack, available to first-time guests, and there are no more of these slots available during this session time.
					If you want to place a standard order during this same session time you can access the session here. Or, <a href='".$tpl->alt_link."'> Click Here</a> to view other introductory sessions at your friend&rsquo;s Dream Dinners location.");
				CApp::bounce('main.php?page=session_menu');
			}
			else if ($tpl->problem == "session_not_found")
			{
				$tpl->setErrorMsg("We&rsquo;re sorry, the session that you were invited to can not be found. Here you may view other sessions at your friend&rsquo;s Dream Dinners location.");
				CApp::bounce('main.php?page=session_menu');
			}
		}

	}
?>
