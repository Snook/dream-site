<?php

define('CSRF_Fail_Msg', "The submission was rejected as a possible security issue. If this was a legitimate submission please contact Dream Dinners support. This message can also be caused by a double submission of the same page.");


class CSRF
{


   public  $action='unspecified'; // action page the script is good for
   public  $life = 60; // minutes for which key is good
   public  $dd_csrf_token = false;
   private $sid; // session id of user
   private $useCart = false;

   function __construct($session_key = false, $action = false, $cart_key = false)
   {

      CLog::RecordDebugTrace("Constructing CSRF obj - session key = $session_key action = $action cart key = $cart_key", "CSRF", 1, "DEBUG", true);

   	  $sid = false;
   	  if ($session_key)
   	  {
   		 $sid = $session_key;
   	  }
   	  else
   	  {
   	  	$sid = $cart_key;
   	  	$this->useCart = true;
   	  }

   	  $this->sid  = preg_replace('/[^a-z0-9]+/i','',$sid);

      if ($action)
      	$this->action = $action;
      else
      	$this->action = $this->action;
   }

   public function csrfkey()
   {

      $key = md5(microtime() . $this->sid . rand());
      $stamp = time() + (60 * $this->life);
      $tokenObj = DAO_CFactory::create('csrf_tokens');
      if ($this->useCart)
      {
     	 $tokenObj->cart_id = $this->sid;
      }
      else
      {
      	 $tokenObj->session_id = $this->sid;
      }
      $tokenObj->token = $key;
      $tokenObj->expiration_time = $stamp;
      $tokenObj->action = $this->action;
      $tokenObj->insert();
      $this->dd_csrf_token = $key;

      CLog::RecordDebugTrace("Generate Key key = $key session key = {$this->sid} action = {$this->action} cart key = " . (!empty($this->cart_id) ? $this->cart_id : "none"), "CSRF", 1, "DEBUG", true);

     }

   public function checkcsrf($key)
   {
      $this->cleanOld();
      $cleanKey = preg_replace('/[^a-z0-9]+/','',$key);
      if (strcmp($key,$cleanKey) != 0)
      {
         CLog::RecordDebugTrace("Check CSRF - bad key = $key session key = {$this->sid} action = {$this->action} cart key = " . (!empty($this->cart_id) ? $this->cart_id : "none"), "CSRF", 1, "DEBUG", true);
         return false;
      }
      else
      {
      	$tokenObj = DAO_CFactory::create('csrf_tokens');
      	if ($this->useCart)
      	{
      		$tokenObj->query("select id from csrf_tokens where cart_id = '{$this->sid}'
      							and token = '$cleanKey' AND action = '$this->action'");
      	}
      	else
      	{
      		$tokenObj->query("select id from csrf_tokens where session_id = '{$this->sid}'
      		and token = '$cleanKey' AND (action = '$this->action' OR action = 'om_get_token')");
      	}

		if ($tokenObj->N <= 0)
		{
		    CLog::RecordDebugTrace("Check CSRF - didn't find  = $cleanKey session key = {$this->sid} action = {$this->action} cart key = " . (!empty($this->cart_id) ? $this->cart_id : "none"), "CSRF", 1, "DEBUG", true);

			return false;
		}
		else
		{
		    CLog::RecordDebugTrace("Check CSRF - FOUND (deleting first found) = $cleanKey session key = {$this->sid} action = {$this->action} cart key = " . (!empty($this->cart_id) ? $this->cart_id : "none"), "CSRF", 1, "DEBUG", true);

			CLog::Assert($tokenObj->N == 1, "Should only be 1 token per action/session");
			$tokenObj->fetch();
      		$tokenDeleterObj = DAO_CFactory::create('csrf_tokens');
      		$tokenDeleterObj->query("delete from csrf_tokens where id = {$tokenObj->id}");
      		return true;
		}
      }
   }

   static function updateSessionTokens($new_session_id, $old_session_id)
   {
       $tokenObj = DAO_CFactory::create('csrf_tokens');

       CLog::RecordDebugTrace("updating session keys - OLD = $old_session_id  NEW = $new_session_id", "CSRF", 1, "DEBUG", true);

      	$tokenObj->query("select id from csrf_tokens where session_id = '$old_session_id'");

      	while ($tokenObj->fetch())
      	{
      	    $tempObj = DAO_CFactory::create('csrf_tokens');
      	    $tempObj->query("update csrf_tokens set session_id = '$new_session_id' where id = $tokenObj->id");
      	}
   }

   private function cleanOld()
   {
   		// remove expired keys
   		$exp = time();
   		$tokenObj = DAO_CFactory::create('csrf_tokens');
   		$tokenObj->query("delete from csrf_tokens WHERE expiration_time < $exp");
    	return true;
    }

   public function logout()
   {
   		$tokenObj = DAO_CFactory::create('csrf_tokens');
   		$tokenObj->query("delete from csrf_tokens WHERE session_id = '{$this->sid}'");
    	return true;
   }

   static function validate($token, $action_id)
   {
       CLog::RecordDebugTrace("validating - Token = $token  action = $action_id", "CSRF", 1, "DEBUG", true);

       if (CUser::isLoggedIn())
       {
           $sessionKey = CBrowserSession::instance()->browser_session_key;

           if (empty($sessionKey))
           {
               // TODO:  not logged in so can't do much
               return false;
           }

           $tempCSRF = new CSRF($sessionKey, $action_id);
       }
       else
       {

           $cartKey = CBrowserSession::instance()->getCartKey();
           if (empty($cartKey))
           {
               // TODO:  no cart so can't do much
               return false;
           }

           $tempCSRF = new CSRF(false, $action_id, $cartKey);
       }

       return $tempCSRF->checkcsrf($token);

   }

   static function getNewToken($action)
   {
        CLog::RecordDebugTrace("Get New Token for action  action = $action", "CSRF", 1, "DEBUG", true);

       if (CUser::isLoggedIn())
       {
           $sessionKey = CBrowserSession::instance()->browser_session_key;
           if (empty($sessionKey))
           {
               // TODO:  not logged in so can't do much
               return;
           }

           $csrfObj = new CSRF($sessionKey, $action);
       }
       else
       {
           $cartKey = CBrowserSession::instance()->getCartKey();
           if (empty($cartKey))
           {
               // TODO:  no cart so can't do much
               return;
           }

           $csrfObj = new CSRF(false, $action, $cartKey);
       }

       $csrfObj->csrfkey();
       // there is now a key in the database for this form

       return $csrfObj->dd_csrf_token;

   }

}
?>
