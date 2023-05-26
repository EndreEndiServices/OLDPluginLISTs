<?php

namespace Logger;

use pocketmine\scheduler\AsyncTask;

/**
 * Slack messages logic
 * Contains Slack channel info, message to send
 */
class Slack extends AsyncTask
{
	protected $channel		= '#warnings';
	protected $webhookUrl	= 'https://hooks.slack.com/services/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';

	protected $fromUsername = 'LBCore Logger';
	protected $fromIcon	 = ':ghost:';
	
	private $message;
	
	public function __construct($server, $message) {
		$this->message =  '*' . $server . '*: ' . $message;
	}
		

	public function onRun() {
		$data = array(
			'channel'		=> $this->channel,
			'username'		=> $this->fromUsername,
			'icon_emoji'	=> $this->fromIcon,
			'text'			=> $this->message
		);
		$data = 'payload=' . json_encode($data);
		
		$ch = curl_init();
		
		curl_setopt_array($ch, array(
			CURLOPT_URL				=> $this->webhookUrl,
			CURLOPT_POST			=> true,
			CURLOPT_POSTFIELDS		=> $data,
			CURLOPT_RETURNTRANSFER	=> true,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSL_VERIFYPEER => 0
		));		
		curl_exec($ch);
		curl_close($ch);		
	}	
}
