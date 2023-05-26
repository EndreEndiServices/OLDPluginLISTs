<?php

namespace mcg76\skywars\portal;

use pocketmine\math\Vector3 as Vector3;
use pocketmine\level\Position;
use pocketmine\entity\Entity;
use pocketmine\Server;
use pocketmine\utils\Config;

/**
 * MCG76 Portal
 *
 * Copyright (C) 2014 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 @author minecraftgenius76@gmail.com
 *
 */

class Portal  {
	public $p1;
	public $p2;
	public $name;
	public $destination;
	public function __construct(Position $p1, Position $p2, $name, $destination) {
		$this->p1 = $p1;
		$this->p2 = $p2;
		$this->name = $name;
		$this->destination = $destination;
	}
	public function __toString() {
		return ( string ) $this->p1 . ( string ) $this->p2;
	}

	public function inside(Position $p) {
		//if ($p->getLevel ()->getName () == $this->p1->getLevel ()->getName ()) {
			return ($this->between ( $this->p1->x, $p->x, $this->p2->x ) and $this->between ( $this->p1->y, $p->y, $this->p2->y ) and $this->between ( $this->p1->z, $p->z, $this->p2->z ));
		//} else {
		//	return false;
		//}
	}
	public function between($l, $m, $r) {
		$lm = abs ( $l - $m );
		$rm = abs ( $r - $m );
		$lrm = $lm + $rm;
		$lr = abs ( $l - $r );
		// Server::getInstance()->broadcastMessage("lrm:".$lrm." lr:".$lr);
		return ($lrm <= $lr);
	}
	public function save($path) {
		$name = $this->destination;
		$data = new Config ( $path . "$name.yml", Config::YAML );
		//$data->set ( "pointLevel", $this->p1->getLevel()->getName());
		$data->set ( "pointLevel", $name);
		$data->set ( "point1X", round($this->p1->x));
		$data->set ( "point1Y", round($this->p1->y));
		$data->set ( "point1Z", round($this->p1->z));
		$data->set ( "point2X", round($this->p2->x));
		$data->set ( "point2Y", round($this->p2->y));
		$data->set ( "point2Z", round($this->p2->z));
//		$data->set ( "destinationLevel", $name);
		$data->set ( "destinationLevel", $this->destination);
		$data->set ( "destination", $this->destination);
// 		$data->set ( "destinationX", $this->destination->x );
// 		$data->set ( "destinationY", $this->destination->y );
// 		$data->set ( "destinationZ", $this->destination->z );
		$data->save ();
	}
	public function delete($path) {
		$name = $this->destination;
		@unlink ( $path . "$name.yml" );
	}
	public function teleport(Entity $e) {
		// $e->teleport($this->destination->getLevel()->getSafeSpawn());
		$e->teleport ( $this->destination );
	}
}