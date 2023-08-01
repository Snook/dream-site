<?php
require_once("includes/CPageProcessor.inc");
require_once('includes/CMail.inc');

class processor_admin_helpdesk extends CPageProcessor
{

	function runFranchiseStaff()
	{
		$this->helpdeskProcessor();
	}

	function runFranchiseLead()
	{
		$this->helpdeskProcessor();
	}

	function runFranchiseManager()
	{
		$this->helpdeskProcessor();
	}

	function runHomeOfficeManager()
	{
		$this->helpdeskProcessor();
	}

	function runFranchiseOwner()
	{
		$this->helpdeskProcessor();
	}

	function runSiteAdmin()
	{
		$this->helpdeskProcessor();
	}

	function runEventCoordinator()
	{
		$this->helpdeskProcessor();
	}

	function runOpsLead()
	{
		$this->helpdeskProcessor();
	}

	function runOpsSupport()
	{
		$this->helpdeskProcessor();
	}

	function runDishwasher()
	{
		$this->helpdeskProcessor();
	}

	function curl_file_create($filename, $mimetype = '', $postname = '')
	{
		return "@$filename;filename=" . ($postname ? : basename($filename)) . ($mimetype ? ";type=$mimetype" : '');
	}

	function helpdeskProcessor()
	{
		if (!empty($_POST['op']))
		{
			// Get helpdesk form
			if ($_POST['op'] == 'get_helpdesk')
			{
				$tpl = new CTemplate();

				$tpl->assign('request_url', $_POST['request_url']);

				$helpdesk_form = $tpl->fetch('admin/subtemplate/form_helpdesk_itsupport.tpl.php');

				echo json_encode(array(
					'processor_success' => true,
					'processor_message' => 'Retrieved helpdesk form.',
					'helpdesk_form' => $helpdesk_form
				));
			}

			// submit helpdesk ticket
			if ($_POST['op'] == 'submit_ticket' && !empty($_POST['ticket_data']))
			{
				$form_values = array();
				parse_str($_POST['ticket_data'], $form_values);
				$form_values = array_map('trim', $form_values);

				$store_email = "N/A";
				if (!empty($form_values['store_id']) && is_numeric($form_values['store_id']))
				{

					$store = DAO_CFactory::create('store');
					$store->id = $form_values['store_id'];
					$store->find(true);

					$form_values['store_info'] = $store->toArray();

					$store_email = $form_values['store_info']['email_address'];
					$store_id = $store->id;
				}

				if (defined('USE_FRESHDESK_SUPPORT') && USE_FRESHDESK_SUPPORT)
				{
					$token = FRESH_DESK_API_KEY;
					$password = FRESH_DESK_API_PASSWORD;

					$attachment_file = false;
					if (isset($_FILES['email_attachment']) && $_FILES['email_attachment']['name'] != '')
					{
						$ext = explode('.', strtolower(basename($_FILES['email_attachment']['name'])));

						if ($_FILES['email_attachment']['error'])
						{

							echo json_encode(array(
								'processor_success' => false,
								'processor_message' => "Attachment error, file size may be too large. Limit " . CMail::SIZELIMIT / 1024 / 1024 . 'MB'
							));

							exit;
						}
						else if (!in_array($ext['1'], CMail::allowedExtensions()))
						{
							echo json_encode(array(
								'processor_success' => false,
								'processor_message' => 'Invalid file extension. (' . $ext['1'] . ')'
							));
							exit;
						}
						else
						{
							$attachment_file = $_FILES['email_attachment'];
						}
					}

					$debug_info = "N/A";
					if (!empty($_POST['debugInfo']))
					{
						$debug_info = $_POST['debugInfo'];
					}

					$connection = curl_init("https://dreamdinners.freshdesk.com/api/v2/tickets");

					CLog::Record("Files: " . print_r($_FILES, true));

					/*
					$data = array(
					    'helpdesk_ticket[email]' => $form_values['email_address'],
					    'helpdesk_ticket[subject]' => $form_values['subject'],
					    'helpdesk_ticket[description]' => $form_values['description'],
					    'helpdesk_ticket[priority]' => 2,
					    'helpdesk_ticket[source]' => 9,
					    'helpdesk_ticket[ticket_type]' => $form_values['ticket_type'],
					    'helpdesk_ticket[custom_field][store_number_160492]' => $store_email,
					    'helpdesk_ticket[custom_field][storeid_160492]' => $store_id,
					    'helpdesk_ticket[custom_field][phone_number_160492]' => $form_values['phone_number'],
                        'helpdesk_ticket[custom_field][preferred_contact_method_160492]' => $form_values['preferred_contact_type'],
					 	'helpdesk_ticket[custom_field][problem_url_160492]' => $form_values['problem_url'],
					 	'helpdesk_ticket[custom_field][browser_160492]' => $_SERVER['HTTP_USER_AGENT'],
						'helpdesk_ticket[custom_field][reported_from_url_160492]' => $_POST['reporting_url'],
						'helpdesk_ticket[custom_field][debug_information_160492]' => $debug_info,
					    'helpdesk_ticket[cc_email]' => $form_values['alt_contact']
=					);
*/

					$phone = preg_replace("/[^0-9]/", "", $form_values['phone_number']);

					$data = array(
						'email' => $form_values['email_address'],
						'subject' => $form_values['subject'],
						'description' => $form_values['description'],
						//  'type' => 'DreamDinners.com',
						'priority' => 2,
						'status' => 2,
						'source' => 9,
						'custom_fields' => array(
							'store_number' => $store_email,
							'storeid' => (int)$store_id,
							'phone_number' => (int)$phone,
							'reported_from_url' => substr($_POST['reporting_url'], 0, 255),
							'problem_url' => substr($form_values['problem_url'], 0, 255),
							'browser' => $_SERVER['HTTP_USER_AGENT'],
							'debug_information' => $debug_info,
							//	'category' => 'Support Requests',
							//	'subcategory' => 'Customer Service'

						)
					);

					if (!empty($form_values['alt_contact']))
					{
						$data['cc_emails'] = array($form_values['alt_contact']);
					}

					if ($attachment_file)
					{
						//    $data['attachments'][] = $this->curl_file_create($attachment_file['tmp_name'], $attachment_file['type'], $attachment_file['name']);
					}

					$data = json_encode($data);

					$header = array();
					$header[] = "Content-type: application/json";

					curl_setopt($connection, CURLOPT_POST, true);
					curl_setopt($connection, CURLOPT_HEADER, true);
					curl_setopt($connection, CURLOPT_USERPWD, "$token:$password");
					curl_setopt($connection, CURLOPT_POSTFIELDS, $data);
					curl_setopt($connection, CURLOPT_HTTPHEADER, $header);
					curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, false);

					CLog::Record("FD data : \r\n" . print_r($data, true));

					$response = curl_exec($connection);

					if ($response === false)
					{
						$error_response = curl_error($connection);

						CLog::Record("FS Error " . curl_error($connection));

						echo json_encode(array(
							'processor_success' => false,
							'processor_message' => 'The ticket could not be created. Please try again.'
						));

						exit;
					}

					$info = curl_getinfo($connection);

					// TODO: remove after new system is fulling vetted, say around March 2015
					CLog::Record("FS Response " . $response);
					CLog::Record("FS values " . print_r($form_values, true));
					CLog::Record("FS Curl Info " . print_r($info, true));

					if ($info['http_code'] == 201 || $info['http_code'] == 200)
					{
						echo json_encode(array(
							'processor_success' => true,
							'processor_message' => 'Ticket created.',
							'dd_toasts' => array(
								array('message' => 'Ticket created, a member of the support team will contact you shortly.')
							)
						));
					}
					else
					{
						CLog::Record("FS Error " . curl_error($connection));
						echo json_encode(array(
							'processor_success' => false,
							'processor_message' => 'The ticket could not be created. Please try again.'
						));
					}
				}
			}
		}
		else
		{
			echo json_encode(array(
				'processor_success' => false,
				'processor_message' => 'No operation.'
			));
		}
	}
}

?>