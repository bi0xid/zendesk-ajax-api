<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 'On');

header('Content-type: application/json');

include('./vendor/autoload.php');

use Zendesk\API\HttpClient as ZendeskAPI;
use Symfony\Component\Yaml\Yaml;

$zendeskMTS = new zendeskMTS();

class zendeskMTS {
	private $subdomain = '';
	private $username  = '';
	private $token     = '';

	private $client;
	private $requester;

	private $credentials;
	private $customerMail;

	public function __construct() {
		$this->credentials = Yaml::parse(file_get_contents('./config.yml'))['credentials'];

		$this->client = new ZendeskAPI($this->credentials['subdomain'], $this->credentials['username']);
		$this->client->setAuth('basic', ['username' => $this->credentials['username'], 'token' => $this->credentials['token']]);

		$this->customerMail = $_GET['usermail'];
		$this->requester = new ZendeskAPI($this->subdomain, $this->customerMail);
		$this->requester->setAuth('basic', ['username' => $this->customerMail, 'token' => $this->token]);

		$this->init();
	}

	public function init() {
		switch ($_GET['action']) {
			case 'all':
				$this->getAllTickets();
				break;
			case 'by':
				$this->getAllCustomerTickets();
				break;
			case 'comments':
				$this->getTicketComments();
				break;
			case 'agents':
				$this->getAllAgents();
				break;
			case 'update':
				$this->updateTicket();
				break;
			case 'verified':
				$this->isRequesterVerified();
				break;
		}
	}

	/**
	 * [getAllTickets description]
	 * @return [object] [Get all the tickets for the actual subdomain]
	 */
	public function getAllTickets() {
		// TODO: Pagination, currently only send the page 1
		try {
			$tickets = $this->client->tickets()->findAll();
			echo json_encode($tickets);
		}
		catch (\Zendesk\API\Exceptions\ApiResponseException $e) {
			echo 'Please check your credentials in this file.';
		}
	}

	/**
	 * [isRequesterVerified description]
	 * @return boolean [Get if the customer is a verified requester]
	 */
	public function isRequesterVerified() {
		$customer = $this->getCustomerInfoByEmail();
		echo json_encode($customer->users[0]->verified);
	}

	/**
	 * [updateTicket description]
	 * @return [type] [description]
	 */
	public function updateTicket() {
		$ticketId      = $_GET['ticket_id'];
		$ticketComment = utf8_decode($_GET['ticket_comment']);

		$ticket = $this->requester->requests()->update($ticketId, array('comment' => array('body' => $_GET['ticket_comment'])));
		echo json_encode($ticket);
	}

	/**
	 * [getTicketComments description]
	 * @return [object] [All comments inside an array for the giving ticket ID]
	 */
	public function getTicketComments() {
		!isset($_GET['id']) && $this->error('ID of the ticket is missing.');

		// TODO: Pagination
		$customerId = $_GET['id'];
		$comments   = $this->client->tickets()->comments()->findAll(array('ticket_id' => $customerId));
		echo json_encode($comments);
	}

	/**
	 * [getAllAgents description]
	 * @return [object] [Information about all the agents for the subdomain]
	 */
	public function getAllAgents() {
		$admins = $this->client->users()->findAll(array('role' => 'admin'));
		$agents = $this->client->users()->findAll(array('role' => 'agent'));

		$result = array(
			'count' => count($admins->users) + count($agents->users),
			'users' => []
		);

		foreach ($admins->users as $admin) {
			$result['users'][] = $admin;
		}
		foreach ($agents->users as $agent) {
			$result['users'][] = $agent;
		}

		echo json_encode($result);
	}

	/**
	 * [getAllCustomerTickets description]
	 * @return [object] [All tickets of the for the customer]
	 */
	public function getAllCustomerTickets() {
		$customerInfo = $this->getCustomerInfoByEmail();
		$customerId   = $customerInfo->users[0]->id;

		$tickets = $this->client->users($customerId)->requests()->findAll();
		echo json_encode($tickets);
	}

	public function getCustomerInfoByEmail() {
		if($this->customerMail) {
	 		return $this->client->users()->search(array('query' => $this->customerMail));
		}
		else {
			return undefined;
		}
	}

	public function error($msg) {
		trigger_error($msg, E_USER_ERROR);
		die();
	}
}
