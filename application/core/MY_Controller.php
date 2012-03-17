<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Base controller for public controllers.
 * 
 * @package CI-Beam
 * @category Controller
 * @author Ardi Soebrata
 * 
 * @property CI_Config $config
 * @property CI_Loader $load
 * @property MY_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Email $email
 * @property CI_DB_active_record $db
 * @property CI_DB_forge $dbforge
 * @property CI_Table $table
 * @property CI_Session $session
 * @property CI_FTP $ftp
 * @property CI_Pagination $pagination
 * 
 * @property Template $template
 * @property Doctrine $doctrine
 * 
 */
class MY_Controller extends CI_Controller 
{
	/**
	 * View's Data
	 * 
	 * @var array 
	 */
	public $data = array();
	
	public function __construct()
	{
		parent::__construct();
		
		if ($this->auth->loggedin())
		{
			// get current user id
			$id = $this->auth->userid();

			// get user from database
			$user = $this->doctrine->em->find('auth\models\User', $id);
			$this->data['auth_user'] = array(
				'id'			=> $user->getId(),
				'first_name'	=> $user->getFirstName(),
				'last_name'		=> $user->getLastName(),
				'username'		=> $user->getUsername(),
				'email'			=> $user->getEmail(),
				'lang'			=> $user->getLang()
			);
			$this->session->set_userdata('lang', $this->data['auth_user']['lang']);
		}
		
		$languages = $this->config->item('languages');
		// Lang has already been set and is stored in a session
		$lang = $this->session->userdata('lang');
		// No Lang. Lets try some browser detection then
		if (empty($lang) and !empty( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ))
		{
			// explode languages into array
			$accept_langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);

			log_message('debug', 'Checking browser languages: '.implode(', ', $accept_langs));

			// Check them all, until we find a match
			foreach ($accept_langs as $lang)
			{
				// Turn en-gb into en
				$lang = substr($lang, 0, 2);

				// Check its in the array. If so, break the loop, we have one!
				if(in_array($lang, array_keys($languages)))
				{
					break;
				}
			}
		}
		// If no language has been worked out - or it is not supported - use the default (first language)
		if (empty($lang) or !in_array($lang, array_keys($languages)))
		{
			reset($languages);
			$lang = key($languages);
			$this->session->set_userdata('lang', $lang);
		}
		
		$this->config->set_item('language', $languages[$lang]['folder']);
	}
}