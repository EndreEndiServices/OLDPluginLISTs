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

namespace HyPrimeCore\cosmetics\cloaks;


use HyPrimeCore\CoreMain;
use HyPrimeCore\cosmetics\cloaks\type\CloakType;
use pocketmine\Player;

class CloakManager {

	/**
	 * @param Player $p
	 * @param int $type
	 */
	public static function equipCloak(Player $p, int $type){
		$pManager = CoreMain::get()->getPlayerData($p);
		if($pManager->getCloakData() !== null){
			CloakManager::unequipCloak($p);
		}

		$cloak = CloakType::getCloakById($p, $type);
		$pManager->setCurrentCloak($cloak);
		CoreMain::get()->savePlayerData($p, $pManager);
	}

	/**
	 * @param Player $p
	 */
	public static function unequipCloak(Player $p){
		if($p == null){
			return;
		}
		$pManager = CoreMain::get()->getPlayerData($p);
		$pManager->setCurrentCloak(null);
		CoreMain::get()->savePlayerData($p, $pManager);
	}

}