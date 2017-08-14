<?php

/**
* @file
* Contains \Drupal\home_block\Form\HomeBlockForm
*/

namespace Drupal\contact_us\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Drupal\Component\Utility;
use Drupal\Core\Routing\TrustedRedirectResponse;

use Drupal\Core\Mail\MailManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;

class Contact_us extends FormBase{
	
   /**
	* {@inheritdoc}
	*/
	
	public function getFormId() {
		return 'drupal_contact_us_form';
	}
	
	/**
	* {@inheritdoc}
	*/
	
	public function buildForm(array $form, FormStateInterface $form_state) {		
		$form['full_name'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Full Name'),
			'#required' => TRUE,
		];
		$form['email'] = [
			'#type' => 'email',
			'#title' => $this->t('E-mail'),
			'#required' => TRUE,
		];
		$form['telephone'] = [
			'#type' => 'tel',
			'#title' => $this->t('Phone'),
			'#required' => TRUE,
		];
		
		$form['department'] = [
			'#type' => 'select',
			'#title' => $this->t('Select a Department'),
			'#options' => [
				'general' => $this->t('General Inquiries'),
				'sales' => $this->t('Sales Inquiries'),
				'channel' => $this->t('Channel Department'),
				'client' => $this->t('Client Support'),
			],
			'#required' => TRUE,
		];
		$form['text'] = [
			'#type' => 'textarea',
			'#title' => $this->t('Message'),
			'#required' => TRUE,
		];
		
		$form['actions'] = [
		  '#type' => 'actions',
		];
		
		$form['actions']['submit'] = [
		  '#type' => 'submit',
		  '#value' => $this->t('Submit'),
		];
		
		return $form;
	}
	
	/**
	* {@inheritdoc}
	*/
	
	public function validateForm(array &$form, FormStateInterface $form_state) {
		if (empty(trim($form_state->getValue('full_name')))) {
		  $form_state->setErrorByName('full_name', $this->t('Full Name can not be empty'));
		}
		
		if (empty(trim($form_state->getValue('email')))) {
		  $form_state->setErrorByName('email', $this->t('E-mail can not be empty'));
		}
		
		if(filter_var(trim($form_state->getValue('email')), FILTER_VALIDATE_EMAIL) === false){
			$form_state->setErrorByName('email', $this->t('Enter a valid E-mail address'));
		}
		
		$tele_phone = trim($form_state->getValue('telephone'));
		
		$replace_array = array("+", "-", "(", ")", ".", ",");
		$tele_phone = str_replace($replace_array, "", $tele_phone);
		
		if (empty($tele_phone)) {
			$form_state->setErrorByName('telephone', $this->t('Telephone can not be empty'));
		}
		
		if (!preg_match('/^[0-9]*$/', $tele_phone)) {
			$form_state->setErrorByName('telephone', $this->t('Only numeric values are allowed for telephone number'));
		}
		
		if (empty(trim($form_state->getValue('text')))) {
		  $form_state->setErrorByName('text', $this->t('Message can not be empty'));
		}
	}
	
	/**
	* {@inheritdoc}
	*/
	
	public function submitForm(array &$form, FormStateInterface $form_state) {
		
		//Process the submitted values
		$form_values = $form_state->getValues();
		
		//print_r($form_values); die;
		
		$mailManager = \Drupal::service('plugin.manager.mail');
		$module = 'contact_us';
		$key = 'send_mail';
		
		$from = $this->config('system.site')->get('mail');
		
		if($form_values['department'] == 'general') {
			$to = 'info@domainname.com';
			$params['message'] = "General Inquiries - Contact Us Submission:";
			$params['subject'] = "General Inquiries - Contact Us";
		} else if($form_values['department'] == 'sales'){
			$to = 'sales@domainname.com';
			$params['message'] = "Sales Inquiries - Contact Us Submission:";
			$params['subject'] = "Sales Inquiries - Contact Us";
		} else if($form_values['department'] == 'channel'){
			$to = 'channels@domainname.com';
			$params['message'] = "Channel Portal Inquiries - Contact Us Submission:";
			$params['subject'] = "Channel Portal Inquiries - Contact Us";
		} else {
			$to = 'support@domainname.com';
			$params['message'] = "Support Request - Contact Us Submission:";
			$params['subject'] = "Support Request - Contact Us";
		}
		$params['message'] .= "\r\n\r\n";
		
		$params['message'] .= $form_values['full_name'] . " has submitted a contact request:";
		$params['message'] .= "\r\nE-mail: " . $form_values['email'];
		$params['message'] .= "\r\nPhone: " . $form_values['telephone'];
		$params['message'] .= "\r\nMessage: " . $form_values['text'];
		
		$params['message'] .= "\r\n\r\n";
		$params['message'] .= "User agent: " . $_SERVER['HTTP_USER_AGENT'];
		$params['message'] .= "\r\nIP: " . gethostbyaddr($_SERVER['REMOTE_ADDR']);
		$params['message'] .= "\r\nSubmitted at: " . date("m/d/Y H:i:s");
		
		$params['message'] .= "\r\n\r\n";
		$params['message'] .= "Thank You";
		$params['message'] .= "\r\nSystem Automated Email";
		
		$language_interface = \Drupal::languageManager();
		$language_code = $language_interface->getDefaultLanguage()->getId();
		
		$send_now = TRUE;
		
		$result = $mailManager->mail($module, $key, $to, $language_code, $params, $from, $send_now);
		
		if ($result['result'] !== true){
			drupal_set_message(t('There was a problem sending your message and it was not sent.'), 'error');
		} else {
			drupal_set_message(t('Your message has been sent.'));
		}
	}
}
