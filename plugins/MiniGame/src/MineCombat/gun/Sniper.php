<?php

namespace minecombat\gun;

class Sniper extends Gun{

	public function __construct(){
		$this->maxAmmoCount = 8;
	}

	public function getDamage($distance){
		return 20; // TODO reduce damage by distance
	}

	public function getName(){
		return "Sniper";
	}

	public function getReloadTime(){
		return 40;
	}

	public function getMagazineReloadTime(){
		return 80;
	}
}