<?php

namespace minecombat\gun;

class Pistol extends Gun{

	public function __construct(){
		$this->maxAmmoCount = 8;
	}

	public function getDamage($distance){
		return 2; // TODO change damage by distance
	}

	public function getName(){
		return "Pistol";
	}

	public function getReloadTime(){
		return 10;
	}

	public function getMagazineReloadTime(){
		return 50;
	}
}