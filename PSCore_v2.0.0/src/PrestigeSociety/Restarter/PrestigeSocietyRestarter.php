<?php

namespace PrestigeSociety\Restarter;
class PrestigeSocietyRestarter {

	/** @var int */
	public $time = 0;

	/**
	 *
	 * PrestigeSocietyRestarter constructor.
	 *
	 * @param $time
	 *
	 */
	public function __construct($time){
		$this->time = $time;
	}

	/**
	 *
	 * to string
	 *
	 * @return string
	 *
	 */
	public function __toString(){
		return ($this->toHours() . ":" . $this->toMinutes() . ":" . $this->toSeconds());
	}

	/**
	 *
	 * @API
	 *
	 * @return float
	 *
	 */
	public function toHours(){
		return floor($this->time / 3600);
	}

	/**
	 *
	 * @API
	 *
	 * @return float
	 *
	 */
	public function toMinutes(){
		return floor(($this->time / 60) - (floor($this->time / 3600) * 60));
	}

	/**
	 *
	 * @API
	 *
	 * @return float
	 *
	 */
	public function toSeconds(){
		return floor($this->time % 60);
	}

	/**
	 *
	 * @API
	 *
	 * @param int $time
	 *
	 */
	public function addTime($time){
		$this->time += $time;
	}

	/**
	 *
	 * @API
	 *
	 * @param int $time
	 *
	 */
	public function subtractTime($time){
		$this->time -= $time;
	}

	/**
	 *
	 * @API
	 *
	 * @param int $time
	 *
	 */
	public function divideTime($time){
		$this->time /= $time;
	}

	/**
	 *
	 * @API
	 *
	 * @param int $time
	 *
	 */
	public function multiplyTime($time){
		$this->time *= $time;
	}

	/**
	 *
	 * @API
	 *
	 * @return int
	 *
	 */
	public function getTime(){
		return $this->time;
	}

	/**
	 *
	 * @API
	 *
	 * @param int $time
	 *
	 */
	public function setTime($time){
		$this->time = $time;
	}
}