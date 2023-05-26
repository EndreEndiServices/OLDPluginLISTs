<?php

namespace ISRAPIL;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\CallbackTask;
use onebone\economyapi\EconomyAPI;
use pocketmine\utils\Config;

class MainClass extends PluginBase implements Listener {
	
	public $db;
	public $config;
	
	public function onEnable(){
		@mkdir($this->getDataFolder());
		$this->db = new \SQLite3($this->getDataFolder()."bank.db");
        $this->db->exec("CREATE TABLE IF NOT EXISTS bank(player TEXT NOT NULL, bank INT NOT NULL)");
		$this->config = new Config($this->getDataFolder().'config.yml', Config::YAML, [
			'messages' => [
			   'SUCCESSFULLY_DEPOSIT' => '§6Вы успешно пополнили счет банка на §b%MONEY%$§6!', 'SUCCESSFULLY_WITDRAW' => '§6Вы успешно вывели §b%MONEY%$ §6из банка!', 'MYMONEY' => '§6На твоём счёту в банке:§b %MONEY%$', 'NOT_NUMBER' => "§6Кол-во должно состоять из цифр!", 'NO_BANK_MONEY' => '§cУ вас не достаточно денег в банке!', 'NO_MONEY' => '§cУ вас не достаточно денег!', 'HELP' => '§e-----------------------------§r\n§b/bank money §6- Просмотр банковского счета.§r\n§b/bank withdraw <money> §6- Снять деньги с банковского счета.§r\n§b/bank deposit <money> §6- Положить деньги в банковский счет.§r\n§e-----------------------------'
			],
			'settings' => [
			   'PERCENTAGE_OF_AMOUNT' => 5, 'UPDATE_TIME_SECOND' => 60
			]
		]);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "bankUPDATE")), $this->config->getNested("settings.UPDATE_TIME_SECOND") * 20);
		$this->getLogger()->info("\n\n§eПлагин на банк был §cвключен!\n§eПлагин писал: §cИсрапил Ахмедов§6(§cvk.com/israpil14§6)\n§eПериод выдачи:§c ".($this->config->getNested("settings.UPDATE_TIME_SECOND") * 20)."\n§eПроцентов от суммы: §c".$this->config->getNested("settings.PERCENTAGE_OF_AMOUNT")."\n\n");
	}
	
	
	public function onDisable(){
		$this->getLogger()->info("\n\n§eПлагин на банк был §cвыключен!\n§eПлагин писал: §cИсрапил Ахмедов§6(§cvk.com/israpil14§6)\n\n");
		$this->db->close();
	}
	
	public function bankUPDATE(){
		foreach($this->getServer()->getOnlinePlayers() as $player){
			if($this->myBankMoney($player->getName()) != 0){
				$amount = $this->myBankMoney($player->getName()) * $this->config->getNested("settings.PERCENTAGE_OF_AMOUNT") / 100;
				$this->addBankMoney($player->getName(), round($amount));
				echo "1";
			}
		}
	} 
	
	public function onJoin(PlayerJoinEvent $e){
		$name = strtolower($e->getPlayer()->getName());
		if(!($this->db->query("SELECT * FROM bank WHERE player = '$name'")->fetchArray(SQLITE3_ASSOC))){
			$this->db->query("INSERT INTO bank (player, bank) VALUES ('$name', '0')");
		}
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
		if($cmd->getName() == "bank"){
			if(isset($args[0])){
				if($args[0] == "money" or $args[0] == "mymoney"){
					
					$sender->sendMessage(str_replace('%MONEY%', $this->myBankMoney($sender->getName()), $this->config->getNested("messages.MYMONEY")));

				}elseif($args[0] == "withdraw") {
					
					if(!isset($args[1])){
						$sender->sendMessage($this->config->getNested("messages.NOT_NUMBER"));
						return false;
					}
					
					if(!is_numeric($args[1])){
						$sender->sendMessage($this->config->getNested("messages.NOT_NUMBER"));
						return false;
					}
					
					if($this->myBankMoney($sender->getName()) >= $args[1]){
						$sender->sendMessage(str_replace('%MONEY%', $args[1], $this->config->getNested("messages.SUCCESSFULLY_WITDRAW")));
						$this->addMoney($sender, $args[1]);
						$this->reduceBankMoney($sender->getName(), $args[1]);
					} else {
						$sender->sendMessage($this->config->getNested("messages.NO_BANK_MONEY"));
					}
				
				}elseif($args[0] == "deposit"){
					
					if(!isset($args[1])){
						$sender->sendMessage($this->config->getNested("messages.NOT_NUMBER"));
						return false;
					}
					
					if(!is_numeric($args[1])){
						$sender->sendMessage($this->config->getNested("messages.NOT_NUMBER"));
						return false; 
					}
					
					if($this->myMoney($sender->getName()) >= $args[1]){
						$sender->sendMessage(str_replace('%MONEY%', $args[1], $this->config->getNested("messages.SUCCESSFULLY_DEPOSIT")));
						$this->reduceMoney($sender, $args[1]);
						$this->addBankMoney($sender->getName(), $args[1]);
					} else {
						$sender->sendMessage($this->config->getNested("messages.NO_MONEY"));
					}
					
				}else{
					$sender->sendMessage($this->config->getNested("messages.HELP"));
				}
				
			}else{
				$sender->sendMessage($this->config->getNested("messages.HELP"));
			}
		}
	}

	public function myBankMoney(string $name){
		$name = strtolower($name);
        $result = $this->db->query("SELECT bank FROM bank WHERE player = '$name'")->fetchArray(SQLITE3_ASSOC);
        return $result["bank"];
    }
	

    public function addBankMoney(string $player, $bank){
		$name = strtolower($player);
        $this->db->query("UPDATE bank SET bank=bank+$bank WHERE player = '$name';");
    }
	
	public function reduceBankMoney(string $player, $bank){
		$name = strtolower($player);
        $this->db->query("UPDATE bank SET bank=bank-$bank WHERE player = '$name';");
	}
	
	
	public function myMoney($player){
        return EconomyAPI::getInstance()->myMoney($player);
    }

	
    public function addMoney($player, $money){
		EconomyAPI::getInstance()->addMoney($player, $money);
    } 
	

    public function reduceMoney($player, $money){
		EconomyAPI::getInstance()->reduceMoney($player, $money);
    } 
}