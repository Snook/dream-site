<?php
require_once('phplib/Mail.php');
require_once('includes/CTemplate.inc');
require_once('includes/ValidationRules.inc');

class CMail
{
	var $_mail;

	public $from_name;
	public $from_email;
	public $to_id = null;
	public $to_name;
	public $to_email; // csv list
	public $subject;
	public $body_html = null;
	public $body_text = null;
	public $cc_email = false; // csv list
	public $template_name = null;
	public $reply_email = false;
	public $attachment = false;
	public $bcc_email = false; // csv list
	public $attachmentbase64 = false;
	public $attachmentType = "octet-stream";

	// Attachment size limit in bytes, edit .htaccess when changing upload limit
	//const SIZELIMIT = '2097152'; // 2 MB
	//const SIZELIMIT = '8389000'; // 8 MB
	const SIZELIMIT = '10485760'; // 10 MB

	// Allowed attachment extensions
	static function allowedExtensions()
	{
		return array(
			'pdf',
			'doc',
			'docx',
			'xls',
			'xlsx',
			'txt',
			'png',
			'jpg',
			'gif'
		);
	}

	// mail
	//
	//   If safe mode is disabled, $params will be passed as the fifth argument to the
	//   PHP mail() function. If $params is an array, its elements will be joined as a
	//   space-delimited string.
	//
	//   SENDMAIL
	//       o$params["sendmail_path"] - Location of SENDMAIL on the filesystem. (default: /usr/bin/sendmail)
	//       o$params["sendmail_args"] - Additional parameters to pass to the sendmail program.
	//
	//   SMTP
	//       o$params["host"] - The server to connect. Default is localhost
	//       o$params["port"] - The port to connect. Default is 25
	//       o$params["auth"] - Whether or not to use SMTP authentication. Default is FALSE
	//       o$params["username"] - The username to use for SMTP authentication.
	//       o$params["password"] - The password to use for SMTP authentication.
	//

	function __construct()
	{
		if (MAIL_ENABLED)
		{
			global $mail_params;
			if ($mail_params)
			{
				$this->_mail = Mail::factory(MAIL_HANDLER, $mail_params);
			}
			else
			{
				$this->_mail = Mail::factory(MAIL_HANDLER);
			}

			if (empty($this->_mail))
			{
				return CLog::Record('E_MAIL::Could not connect to mail server.');
			}
		}
	}

	/*
	 * $this->from_name = null sends a generic from Dream Dinners
	 */
	function sendEmail()
	{
		// scrub emails (csv list)
		$this->to_email = self::validateEmails($this->to_email);
		$this->cc_email = self::validateEmails($this->cc_email);
		$this->bcc_email = self::validateEmails($this->bcc_email);

		if (MAIL_TEST_MODE)
		{
			if (strstr($this->to_email, 'example.com'))
			{
				if (defined('MAIL_REDIRECT_EMAIL'))
				{
					$this->to_email = MAIL_REDIRECT_EMAIL;
				}
				else
				{
					$this->to_email = 'tester@dreamdinners.com';
				}
			}
		}

		$weHaveSome = strlen($this->to_email) + strlen($this->cc_email);

		$headers = array();

		if (!empty($this->from_name) && $this->from_name != 'Dream Dinners')
		{
			$headers['From'] = '"' . $this->from_name . ' (via Dream Dinners)" ';
		}
		else
		{
			$headers['From'] = '"Dream Dinners" ';
		}

		if (!empty($this->from_email) && strpos($this->from_email, '@dreamdinners.com'))
		{
			$headers['From'] .= '<' . $this->from_email . '>';
		}
		else
		{
			$headers['From'] .= '<do-not-reply@dreamdinners.com>';
		}

		if ($this->reply_email)
		{
			$headers['Reply-To'] = $this->reply_email;
		}
		else if (!strpos($this->from_email, '@dreamdinners.com'))
		{
			$headers['Reply-To'] = $this->from_email;
		}

		// was sending double To: headers
		// CES: 1/17/06. Put it back in. Problem may have been with the generic handler we were
		// using. Now using the "sendmail handler"

		// Note: The production environment is using the "sendmail" handler. When the send function is called:
		// $this->_mail->send( $toArray, $headers, $message );
		// recipients are passed in as the $toArray and in the Headers. This works find with sendmail. The sendmail handler uses the
		// recipients in calling the sendmail function (it does not use the PHP "mail" function). The "To" addresses in the headers may be required.
		// This code was adding the CC addresses to both the $toArray param and the headers. This is required if using SMTP handler.

		// 2 questions remain: Does sendmail requires all addresses (cc, bcc, to) in the toArray? ANSWER: Yes

		// It does require that cc addresses be in the headers- must "to" addresses be listed there also? ANSWER: No - but it seems to do no harm

		$headers['To'] = $this->to_email; // this appears to be unnecessary when using sendmail  but is harmless. It is required by other handlers

		if (!empty($this->cc_email))
		{
			$headers['CC'] = $this->cc_email;
		}

		if ($this->bcc_email)
		{
			$headers['BCC'] = $this->bcc_email;
		}

		$headers['Subject'] = $this->subject;
		$headers['X-Priority'] = "3";
		$headers['X-Mailer'] = "SendMailLib [V1]";

		if ($this->attachment || $this->attachmentbase64)
		{
			ini_set('memory_limit', '128M');

			if (!$this->attachmentbase64)
			{
				$name = basename($this->attachment['name']);
				$filename = basename($this->attachment['tmp_name']);
				$path = dirname($this->attachment['tmp_name']);
				$file = $path . '/' . $filename;
				$file_size = filesize($file);
				$handle = fopen($file, "r");
				$attach = fread($handle, $file_size);
				fclose($handle);
				$attachbase64 = base64_encode($attach);
				$attach = chunk_split($attachbase64);
			}
			else
			{
				$base64array = explode(',', $this->attachmentbase64);
				if (!empty($base64array[1]))
				{
					$base64 = $base64array[1];
				}
				else
				{
					$base64 = $base64array[0];
				}
				$fileextArr = explode('/', finfo_buffer(finfo_open(), base64_decode($base64), FILEINFO_MIME_TYPE));
				$fileext = $fileextArr[1];
				$name = 'file.' . $fileext;
				$attach = chunk_split($base64);
			}

			$random_hash = md5(date('r', time()));
			$alt_seperator = 'PHP-alt-' . $random_hash;
			$mixed_seperator = 'PHP-mixed-' . $random_hash;

			$headers['MIME-Version'] = "1.0";
			$headers['Content-Type'] = "multipart/mixed; boundary=\"$mixed_seperator\";";

			$headers[''] = "This is a MIME message. If you are reading this text, you\n" . "might want to consider changing to a mail reader that\n" . "understands how to properly display MIME multipart messages\n\n";

			$message = "--$mixed_seperator\n";
			$message .= "Content-Type: multipart/alternative; boundary=\"$alt_seperator\"\n\n";

			// Plain text section
			$message .= "--$alt_seperator\n";
			$message .= "Content-Type: text/plain; charset=\"UTF-8\"\n";        // Plain text section
			$message .= "Content-Transfer-Encoding: 7bit\n\n";    //7bit because it has no ascii characters
			$message .= $this->body_text . "\n\n";

			$message .= "--$alt_seperator\n";
			$message .= "Content-Type: text/html; charset=\"UTF-8\"\n";        // HTML section
			$message .= "Content-Transfer-Encoding: 7bit\n\n";    // must be 8bit cause it could have ascii characters
			$message .= $this->body_html . "\n\n";            // Encoding is diferent to help text only clients select the correct type.
			$message .= "--$alt_seperator--\n\n";




			$message .= "--$mixed_seperator\n";
			$message .= "Content-Type: application/{$this->attachmentType}; name=\"" . $name . "\"\n";
			$message .= "Content-Transfer-Encoding: base64\r\n";
			$message .= "Content-Disposition: attachment; filename=\"" . $name . "\"\n\n";
			$message .= $attach . "\n\n";
			$message .= "--$mixed_seperator--\n\n\n";
		}
		else
		{
			$seperator = 'dd12344321';

			// handle text vs rich email type
			if (!empty($this->body_text) && !empty($this->body_html))
			{
				// we have both text and html bodies - use multipart format

				$headers['MIME-Version'] = "1.0";
				$headers['Content-Type'] = "multipart/alternative; boundary=\"$seperator\";";

				$headers[''] = "This is a MIME message. If you are reading this text, you\n" . "might want to consider changing to a mail reader that\n" . "understands how to properly display MIME multipart messages\n\n";

				$message = "--$seperator\n";
				$message .= "Content-Type: text/plain; charset=\"UTF-8\"\n";        // Plain text section
				$message .= "Content-Transfer-Encoding: 7bit\n\n";    //7bit because it has no ascii characters
				$message .= $this->body_text . "\n\n";

				$message .= "--$seperator\n";
				$message .= "Content-Type: text/html; charset=\"UTF-8\"\n";        // HTML section
				$message .= "Content-Transfer-Encoding: 7bit\n\n";    // must be 8bit cause it could have ascii characters
				$message .= $this->body_html . "\n\n";            // Encoding is diferent to help text only clients select the correct type.

				$message .= "--$seperator--\n\n\n";
			}
			else
			{
				// We only have a single body, choose based on text or html

				if (!empty($this->body_html))
				{
					// do the html version
					$headers['MIME-Version'] = "1.0";
					$headers['Content-Type'] = "text/html; charset=\"UTF-8\"\n";
					$message = $this->body_html . "\n\n";
					/*
					 For now both parts must be supplied when sending attachments

					if(isset($attach))
					{
						$message .= "--$seperator\n";
						$message .= "Content-Type: application/octet-stream; name=\"".$name."\"\r\n";
						$message .= "Content-Transfer-Encoding: base64\r\n";
						$message .= "Content-Disposition: attachment; filename=\"".$name."\"\r\n\r\n";
						$message .= $attach . "\n\n";
					}
					*/
				}
				else
				{
					// do the text version
					$headers['MIME-Version'] = "1.0";
					$headers['Content-Type'] = "text/plain; charset=\"UTF-8\"\n";
					$message = $this->body_text . "\n\n";
					/*
					 *  For now both parts must be supplied when sending attachments
					if(isset($attach))
					{
						$message .= "--$seperator\n";
						$message .= "Content-Type: application/octet-stream; name=\"".$name."\"\r\n";
						$message .= "Content-Transfer-Encoding: base64\r\n";
						$message .= "Content-Disposition: attachment; filename=\"".$name."\"\r\n\r\n";
						$message .= $attach . "\n\n";
					}
					*/
				}
			}
		}

		if (MAIL_ENABLED)
		{
			$send_to = array();

			$send_to[] = $this->to_email;

			if (!empty($this->cc_email))
			{
				$send_to[] = $this->cc_email;
			}

			if (!empty($this->bcc_email))
			{
				$send_to[] = $this->bcc_email;
			}

			if ($weHaveSome)
			{
				$this->_mail->send($send_to, $headers, $message);
			}
		}

		// if the email is coming from the system, and the toId is null, then it is
		// probably being sent to the current user.
		if (($this->to_id == null) && ($this->from_email == null))
		{
			$this->to_id = CUser::getCurrentUser()->id;
		}

		CLog::RecordEmail($this->to_id, $this->to_email, ($this->from_email ? CUser::getCurrentUser()->id : null), $this->from_email, $this->template_name . '::' . $this->subject);

		unset($headers);
	}

	static function mailMerge($filename, $tokens)
	{
		$templateEngine = new CTemplate();

		$templateEngine->assign($tokens);

		$contents = $templateEngine->render('/email/' . $filename);

		return $contents;
	}

	// use if_filename if it exists else use optional else_filename
	static function mailMergeIfElese($if_filename, $else_filename, $tokens)
	{
		$templateEngine = new CTemplate();

		$templateEngine->assign($tokens);

		$file = $templateEngine->findFile('template', '/email/' . $if_filename);

		if ($file)
		{
			$contents = $templateEngine->render('/email/' . $if_filename);
		}
		else
		{
			$contents = $templateEngine->render('/email/' . $else_filename);
		}

		return $contents;
	}

	// DBenson (david.benson@dreamdinners.com)
	// 2007-07-25
	//
	// Takes a csv list of email addresses and screens out anything invalid
	//
	// Returns a csv string of only the valid email addresses
	// ---------------------------------------------------------------------------------------

	static function validateEmails($szEmails)
	{
		$arEmails = explode(',', $szEmails);
		$arValidEmails = array();
		foreach ($arEmails as $szMail)
		{
			$szMail = trim($szMail);
			if (ValidationRules::validateEmail($szMail))
			{
				$arValidEmails[] = $szMail;
			}
		}

		return implode(',', $arValidEmails);
	}

	/**
	 * @deprecated send() has been changed to object sendEmail()
	 *
	 * object setup helper for migration
	 */
	function send($fromName = null, $fromEmail = null, $toName = null, $toEmail = null, $subject = null, $htmlMessage = null, $textMessage = null, $signature = null, $cc = null, $toId = null, $templateName = null, $replyTo = false, $attachment = false, $bcc = false, $attachmentbase64 = false)
	{
		if (!empty($fromName))
		{
			$this->from_name = $fromName;
		}

		if (!empty($fromEmail))
		{
			$this->from_email = $fromEmail;
		}

		if (!empty($toName))
		{
			$this->to_name = $toName;
		}

		if (!empty($toEmail))
		{
			$this->to_email = $toEmail;
		}

		if (!empty($subject))
		{
			$this->subject = $subject;
		}

		if (!empty($htmlMessage))
		{
			$this->body_html = $htmlMessage;
		}

		if (!empty($textMessage))
		{
			$this->body_text = $textMessage;
		}

		if (!empty($cc))
		{
			$this->cc_email = $cc;
		}

		if (!empty($toId))
		{
			$this->to_id = $toId;
		}

		if (!empty($templateName))
		{
			$this->template_name = $templateName;
		}

		if (!empty($replyTo))
		{
			$this->reply_email = $replyTo;
		}

		if (!empty($attachment))
		{
			$this->attachment;
		}

		if (!empty($bcc))
		{
			$this->bcc_email = $bcc;
		}

		if (!empty($attachmentbase64))
		{
			$this->attachmentbase64 = $attachmentbase64;
		}

		$this->sendEmail();
	}

}

?>