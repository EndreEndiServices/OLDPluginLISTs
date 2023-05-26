<?php

namespace MXSB\GreenWix\commands;

use MXSB\GreenWix\Main;
use pocketmine\math\Vector3;
use pocketmine\{command\Command, command\CommandSender, level\Position, Player, utils\TextFormat, event\TranslationContainer};
use pocketmine\Server;

class SBcommand extends Command
{

	/** @var null $main */
	protected $plugin = NULL;

    /**
     * @param object $main
     *
     * @return void
     */
    public function __construct(Main $main)
    {
    	parent::__construct('sb', 'SkyBlock', "/sb [create, home]", ['island', 'is', 'skyblock']);
    	$this->setPermission('mxsb.sb');
    	$this->plugin = $main;
    	$this->setUsage("§e/sb опция");
    }

    /**
     * @param CommandSender $player
     * @param string $label
     * @param array $args []
     *
     * @return boolean
     */
    public function execute(CommandSender $player, $label, array $args): bool
    {
    	if (!isset($args[0]) || $args[0] == "help") {            
    		$commands = ["help" => "%sb.help.help%", "create" => "%sb.help.create%", "home" => "%sb.help.home%", "deleteisland" => "%sb.help.deleteisland%", "kick" => "%sb.help.kick%", "close" => "%sb.help.close%", "setpoint" => "%sb.help.setpoint%", "respawn" => "%sb.help.setpoint%", "helpers" => "%sb.help.helpers%", "tp §8<§b%sb.msg108%§8>" => "%sb.help.tp%", "friend §8<§b%sb.msg108%§8>" => "%sb.help.friend%", "§ayes§8/§cno §8<§b%sb.msg108%§8>" => "%sb.help.yesno%", "degrade §8<§b%sb.msg108%§8>" => "%sb.help.degrade%", "leave" => "%sb.help.leave%", "leader §8<§b%sb.msg108%§8>" => "%sb.help.leader%", "chat" => "%sb.help.chat%", "reset" => "%sb.help.reset%"];
    		foreach ($commands as $command => $description){
                //$desc = $this->plugin->lang->translate(new TranslationContainer($description));
    			$player->sendMessage(TextFormat::YELLOW . "§l§a- §8/§bsb {$command}§8: " . TextFormat::WHITE . $description);
    		}
    	} elseif ($args[0] == "install") {
    		$this->plugin->test($args[1], $player);
    	} elseif ($args[0] == "create") {
    		$this->plugin->islandM->createIsland($player);
    	} elseif ($args[0] == "home") {
    		$this->plugin->islandM->toIsland($player);
    	} elseif ($args[0] == "setspawn") {
    		$this->plugin->islandM->getIsland($player)->setSpawn(new Vector3($player->x, $player->y, $player->z));
    	} elseif ($args[0] == "deleteisland") {
            $this->plugin->islandM->deleteIsland($player);
        } elseif ($args[0] == "tp") {
            if (isset($args[1])){
                $island = $this->plugin->islandM->getIsland($args[1]);
                if($island == null){
                    $player->addTitle("§c§l»§f§l Острова не существует§c§l «§r");
                    return true;
                }
                if ($island->isLocked()){
                    $player->addTitle("§c§l»§f§l Остров игрока закрыт§c§l «§r");
                } else{
                 $vec = $island->getPoint();
                 $sb = Server::getInstance()->getLevelByName("sev");
                 $player->teleport(new Position((float) $vec[0], (float) $vec[1], (float) $vec[2], $sb));
             
             $player->addTitle("§a»§f§l Вы телепортированы на остров §a«§r");

         }
     } else{
        $player->addTitle(("§a»§f§l Использование§8:"), ("§8/§bsb tp §8<§bигрок§8>"));
    }
}
return true;
}

}