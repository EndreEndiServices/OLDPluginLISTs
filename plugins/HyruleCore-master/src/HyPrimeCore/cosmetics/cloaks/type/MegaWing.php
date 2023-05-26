<?php
/**
 * BSD 3-Clause License
 *
 * Copyright (c) 2017-2018, larryTheCoder, Hyrule Minigame Division
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * - Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * - Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

namespace HyPrimeCore\cosmetics\cloaks\type;


use HyPrimeCore\cosmetics\cloaks\ParticleCloak;
use HyPrimeCore\player\FakePlayer;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\Particle;
use pocketmine\math\Vector3;
use pocketmine\Player;

class MegaWing extends ParticleCloak {

	private $degreesLeft = 30;
	private $degreesRight = 150;
	private $wingFlapSpeed = 4;
	private $startOffset = 30;
	private $stopOffset = 20;
	private $degreesForward = false;
	private $distanceBetweenParticles = 0.1;
	private $distanceFromPlayer = 0.2;
	private $startingY = 0.3;
	private $particleCoordinates = [];

	public function __construct(Player $player){
		parent::__construct($player, 4, CloakType::SUPERWING);
		$coordinates = [["-,-,-,-,x,x,x,-,-,-"], ["-,-,-,x,x,x,x,x,-,-"], ["-,-,x,x,x,x,x,x,x,-"], ["-,x,x,x,x,x,x,x,x,-"], ["x,x,x,x,x,x,x,x,x,x"], ["x,x,x,x,x,x,x,x,x,x"], ["x,x,x,x,x,x,x,x,x,x"], ["x,x,x,x,x,x,x,x,x,x"], ["-,-,x,x,x,x,x,x,x,x"], ["-,-,-,x,x,x,x,x,x,x"], ["-,-,-,x,x,x,x,x,x,x"], ["-,-,-,-,x,x,x,x,x,x"], ["-,-,-,-,x,x,x,x,x,x"], ["-,-,-,-,-,x,x,x,x,-"], ["-,-,-,-,-,x,x,x,x,-"], ["-,-,-,-,-,-,x,x,x,-"], ["-,-,-,-,-,-,x,x,x,-"], ["-,-,-,-,-,-,-,x,x,-"], ["-,-,-,-,-,-,-,-,x,-"]];
		$yValue = $this->startingY + $this->distanceBetweenParticles * count($coordinates) - $this->distanceBetweenParticles;
		$particleCoordinates = [];
		$this->degreesLeft = -$this->startOffset;
		$this->degreesRight = -$this->startOffset - 180;
		foreach($coordinates as $cords){
			$split = explode(",", $cords[0]);
			$xValue = $this->distanceFromPlayer;
			$yValue -= $this->distanceBetweenParticles;
			for($j = 0; $j !== count($split); ++$j){
				if($split[$j] === '-'){
					$xValue += $this->distanceBetweenParticles;
				}else{
					$xValue += $this->distanceBetweenParticles;
					$coordinates = [$xValue, $yValue];
					$particleCoordinates[] = $coordinates;
				}
			}
		}
		$this->particleCoordinates = $particleCoordinates;
	}

	public function getPermissionNode(): string{
		return "core.cloak.superwing";
	}

	public function onUpdate(): void{
		$this->degreesLeft = ($this->degreesForward ? ($this->degreesLeft + $this->wingFlapSpeed) : ($this->degreesLeft - $this->wingFlapSpeed));
		$this->degreesRight = ($this->degreesForward ? ($this->degreesRight - $this->wingFlapSpeed) : ($this->degreesRight + $this->wingFlapSpeed));
		if($this->degreesLeft >= -$this->startOffset){
			$this->degreesForward = false;
		}
		if($this->degreesLeft <= $this->stopOffset - 90){
			$this->degreesForward = true;
		}
		foreach($this->particleCoordinates as $coordinate){
			$x = $coordinate[0];
			$y = $coordinate[1];
			$this->spawnParticle($this->getPlayer(), new FlameParticle(new Vector3()), $x, $this->degreesLeft, $y);
			$this->spawnParticle($this->getPlayer(), new FlameParticle(new Vector3()), $x, $this->degreesRight, $y);
		}
	}

	/**
	 * @param FakePlayer|Player $player
	 * @param Particle $particle
	 * @param float $x
	 * @param int $degrees
	 * @param float $y
	 */
	public function spawnParticle($player, Particle $particle, float $x, int $degrees, float $y){
		$loc = clone $player->getLocation();
		$angle = $loc->getYaw() + $degrees;
		$yaw = $angle * 3.141592653589793 / 180.0;
		$vec = new Vector3(cos($yaw) * $x, $y, sin($yaw) * $x);
		$vec2 = $loc->add($vec);
		$particle->setComponents($vec2->x, $vec2->y, $vec2->z);
		$this->addParticle($particle);
	}
}