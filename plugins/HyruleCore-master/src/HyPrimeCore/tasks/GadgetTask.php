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

namespace HyPrimeCore\tasks;

use HyPrimeCore\CoreMain;
use HyPrimeCore\cosmetics\gadgets\Gadget;
use HyPrimeCore\player\FakePlayer;
use pocketmine\Player;
use pocketmine\scheduler\Task;

class GadgetTask extends Task {

	/** @var null|Player */
	private $player;
	/** @var Gadget */
	private $gadget;
	/** @var int */
	private $timeout = 10;

	public function __construct(Gadget $cloak){
		$this->player = $cloak->getPlayer();
		$this->gadget = $cloak;
	}

	/**
	 * Actions to execute when run
	 *
	 * @param int $currentTick
	 *
	 * @return void
	 */
	public function onRun(int $currentTick){
		if($this->player instanceof FakePlayer){
			$this->gadget->onUpdate();

			return;
		}
		// Fail-safe
		try{
			if(CoreMain::get()->getPlayerData($this->player)->getGadgetData() != null){
				if(!$this->player->isOnline()){
					CoreMain::get()->getPlayerData($this->player)->setCurrentCloak(null);
					CoreMain::get()->getSchedulerForce()->cancelTask($this->getTaskId());

					return;
				}
				if(CoreMain::get()->getPlayerData($this->player)->getCloakData()->getType() !== $this->gadget->getType()){
					if($this->timeout === 0){
						CoreMain::get()->getSchedulerForce()->cancelTask($this->getTaskId());
					}
					$this->timeout--;

					return;
				}
				$this->gadget->onUpdate();
				$this->timeout = 10;
			}else{
				if($this->timeout === 0){
					CoreMain::get()->getSchedulerForce()->cancelTask($this->getTaskId());
				}
				$this->timeout--;
			}
		}catch(\Exception $e){
			$this->gadget->clear();
			CoreMain::get()->getSchedulerForce()->cancelTask($this->getTaskId());
		}
	}
}