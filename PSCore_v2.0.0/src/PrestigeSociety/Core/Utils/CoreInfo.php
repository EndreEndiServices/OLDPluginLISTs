<?php

namespace PrestigeSociety\Core\Utils;

class CoreInfo {

	const CREATOR = "Blazed and xBeastmode";
	const VERSION = "1.0.9";
	const COMPANY_NAME = "RivalCraftPE";
	const SERVER_IP = "prison.rivalcraft.us";
	const SERVER_PORT = "19312";

	/**
	 *
	 * @return string
	 *
	 */
	public function getCreator(){
		return self::CREATOR;
	}

	/**
	 *
	 * @return string
	 *
	 */
	public function getVersion(){
		return self::VERSION;
	}

	/**
	 *
	 * @return string
	 *
	 */
	public function getCompany(){
		return self::COMPANY_NAME;
	}

	/**
	 *
	 * @return string
	 *
	 */
	public function getServerIp(){
		return self::SERVER_IP;
	}

	/**
	 *
	 * @return string
	 *
	 */
	public function getServerPort(){
		return self::SERVER_PORT;
	}
}