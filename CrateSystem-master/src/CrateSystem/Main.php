<?php

declare(strict_types = 1);

namespace CrateSystem;

use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\utils\Config;
use pocketmine\utils\Textformat as C;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

#utils
use onebone\economyapi\EconomyAPI;

#Crates
use Crates\Common;
use Crates\Vote;
use Crates\Rare;
use Crates\Mythic;
use Crates\Legendary;

#Shops
use Shop\CommonShop;
use Shop\VoteShop;
use Shop\RareShop;
use Shop\MythicShop;
use Shop\LegendaryShop;

#Commands
use Commands\buykey;
use Commands\key;

#Forms
use Forms\CustomForm;
use Forms\SimpleForm;
use Forms\Form;

class Main extends PluginBase implements Listener{

    public $formCount = 0;

	public $forms = [];

    public function onEnable(){
        $this->onConfigs();
        $this->onCommands();
		$this->onEvents();
		$this->onUtils();
        $this->getLogger()->info(C::GREEN . "Enabled.");
    }

    public function onDisable(){
        $this->getLogger()->info(C::RED . "Disabled.");
    }

    private function onConfigs(){
		@mkdir($this->getDataFolder());
		$this->saveResource("Common.yml");
		$this->saveResource("Vote.yml");
		$this->saveResource("Rare.yml");
		$this->saveResource("Mythic.yml");
		$this->saveResource("Legendary.yml");
		$this->saveResource("config.yml");
		$this->saveResource("Shop.yml");
		$this->CommonShop = new CommonShop($this);
		$this->VoteShop = new VoteShop($this);
		$this->RareShop = new RareShop($this);
		$this->MythicShop = new MythicShop($this);
		$this->LegendaryShop = new LegendaryShop($this);
		$this->cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
    }

    private function onCommands(){
		$this->getServer()->getCommandMap()->register("buykey", new buykey("buykey", $this));
        $this->getServer()->getCommandMap()->register("key", new key("key", $this));
    }

    private function onEvents(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	
	private function onUtils(){
		$this->ecoapi = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
		if($this->ecoapi !== true){
			$this->getLogger()->info(C::YELLOW . "Running EconomyAPI Plugin for CrateSystem.");
		}
    }

	public function createCustomForm(callable $function = null) : CustomForm {
		$this->formCount++;
		$form = new CustomForm($this->formCount, $function);
		if($function !== null){
			$this->forms[$this->formCount] = $form;
		}
		return $form;
	}

	public function createSimpleForm(callable $function = null) : SimpleForm {
		$this->formCount++;
		$form = new SimpleForm($this->formCount, $function);
		if($function !== null){
			$this->forms[$this->formCount] = $form;
		}
		return $form;
	}

	public function onPacketReceived(DataPacketReceiveEvent $ev) : void {
		$pk = $ev->getPacket();
		if($pk instanceof ModalFormResponsePacket){
			$player = $ev->getPlayer();
			$formId = $pk->formId;
			$data = json_decode($pk->formData, true);
			if(isset($this->forms[$formId])){
				$form = $this->forms[$formId];
				if(!$form->isRecipient($player)){
					return;
				}
				$callable = $form->getCallable();
				if(!is_array($data)){
					$data = [$data];
				}
				if($callable !== null) {
					$callable($ev->getPlayer(), $data);
				}
				unset($this->forms[$formId]);
				$ev->setCancelled();
			}
		}
	}

	public function onPlayerQuit(PlayerQuitEvent $ev){
		$player = $ev->getPlayer();
		foreach($this->forms as $id => $form){
			if($form->isRecipient($player)){
				unset($this->forms[$id]);
				break;
			}
		}
	}

	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{

        if($sender instanceof Player){

			$this->Common = new Common($this);
			$this->Vote = new Vote($this);
			$this->Rare = new Rare($this);
			$this->Mythic = new Mythic($this);
			$this->Legendary = new Legendary($this);

        $form = $this->createSimpleForm(function (Player $sender, array $data){
            $result = $data[0];
            if ($result === null){
            }
            switch ($result){
                case 1:
                    //common
                    $this->Common->Start($sender);
					break;
				case 2:
                    //vote
                    $this->Vote->Start($sender);
					break;
				case 3:
                    //rare
                    $this->Rare->Start($sender);
					break;
				case 4:
                    //mythic
                    $this->Mythic->Start($sender);
					break;
				case 5:
                    //legendary
                    $this->Legendary->Start($sender);
                    break;
                }
            });

            $form->setTitle("§9Crates");
            $form->setContent("§eYou need key to open any crate!");

            $form->addButton("§fExit");
			$form->addButton("§aCommon", 1);
			$form->addButton("§cVote", 2);
			$form->addButton("§6Rare", 3);
			$form->addButton("§5Mythic", 4);
			$form->addButton("§9Legendary", 5);

            $form->sendToPlayer($sender);
        }else{
            $sender->SendMessage("§cYou are not In-Game.");
        }
        return true;
    }
}