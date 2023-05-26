<?php

namespace solo\SOLOBanStuff;

use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;

use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\inventory\CraftItemEvent;

class SOLOBanStuff extends PluginBase implements Listener {

  public $config;
  public $configData;

  public $cacheConfig;
  public $cacheData;

  public $blocklist = [];
  public $blockcount = [];

  public $dayConfig;
  public $dayData;

  public function onEnable() {
     @mkdir ($this->getDataFolder());
		
		$this->config = new Config ( $this->getDataFolder () . "Banned.yml", Config::YAML, [
      "block-count" => 3,
      "block-time" => 3,
      "craft-msg" => "§c이 아이템은 조합이 불가능합니다.",
      "item-msg" => "§c이 아이템은 사용할 수 없습니다.",
      "command-msg" => "입력하신 명령어는 사용할 수 없습니다.",
      "item" => [],
      "craft" => [],
      "chat" => [],
      "command" => []
      ] );
		$this->configData = $this->config->getAll ();

     $this->cacheConfig = new Config ( $this->getDataFolder () . "cache.yml", Config::YAML,[
     "block-list" => [],
     "block-count" => []
     ]);
     $this->cacheData = $this->cacheConfig->getAll();
     $this->blocklist = $this->cacheData["block-list"];
     $this->blockcount = $this->cacheData["block-count"];

     $this->dayConfig = new Config($this->getDataFolder()."day.yml", Config::YAML);
     $this->dayData = $this->dayConfig->getAll();

		$this->getServer()->getPluginManager()->registerEvents($this, $this);

     if(!isset($this->dayData[date("Y-m-d")])){
       $this->blockcount = [];
       $this->dayData[date("Y-m-d")] = true;
       $this->dayConfig->setAll($this->dayData);
       $this->dayConfig->save();
     }
  }

    public function onCraft(CraftItemEvent $event) :array{
        $results = $event->getRecipe()->getResults();
        foreach($results as $result){
            if(isset($this->configData['craft'][$result->getId().':'.$result->getDamage()])) {
                if($event->getPlayer()->isOp())
                    return true;
                $event->getPlayer()->sendPopup($this->configData['craft-msg']);
                $event->setCancelled();
                return true;
            }
        }
    }

  public function onPlace(BlockPlaceEvent $event) {
    if(isset($this->configData['item'][$event->getBlock()->getId().':'.$event->getBlock()->getDamage()])) {
      if($event->getPlayer()->isOp())
        return;
      $event->getPlayer()->sendPopup($this->configData['item-msg']);
      $event->setCancelled();
      return;
    }
  }

  public function onInteract(PlayerInteractEvent $event) {
    if(isset($this->configData['item'][$event->getItem()->getId().':'.$event->getItem()->getDamage()])) {
      if($event->getPlayer()->isOp())
        return;
      $event->getPlayer()->sendPopup($this->configData['item-msg']);
      $event->setCancelled();
      return;
    }
  }

  public function onChat(PlayerChatEvent $event){
    if(isset($this->blocklist[strtolower($event->getPlayer()->getName())])) {
      $name = strtolower($event->getPlayer()->getName());
      $block = $this->blocklist[$name];
      if(time() > $block) {
        unset($this->blocklist[$name]);
        return;
      } else {
        $remain = floor(($block - time())/60)+1;
        $event->getPlayer()->sendMessage("§b§o[ 알림 ] §7".$remain."분 동안 채팅을 사용할 수 없습니다.");
        $event->setCancelled();
      }
    }
  }

  public function onCommandPreprocess(PlayerCommandPreprocessEvent $event) {
    if(substr($event->getMessage(), 0, 1) == '/') {
      if($event->getPlayer()->isOp()) return;
      $cmd = substr($event->getMessage(), 1);
      foreach(array_keys($this->configData['command']) as $key)
        if(isset(explode($key, $cmd)[1])) {
          $event->getPlayer()->sendMessage("§b§o[ 알림 ] §7".$this->configData['command-msg']);
          $event->setCancelled();
          return;
        }
    }
    $msg = str_replace(['1', '2', '3', '0', 'ㅣ', 'ㅡ', '.', ' '], ['','','','','','','',''], $event->getMessage());
    foreach($this->configData['chat'] as $k => $v) {
      if(isset(explode($k, $msg)[1])) {
        $name = strtolower($event->getPlayer()->getName());
        $blockcount = $this->configData["block-count"];
        $blocktime = $this->configData["block-time"];
        if(!isset($this->blockcount[$name])){
          $this->blockcount[$name] = 1;
        } else {
          $this->blockcount[$name] += 1;
        }
        if( $this->blockcount[$name] == $blockcount ) {
          $this->blocklist[$name] = time() + ($blocktime*60);
          $event->getPlayer()->sendMessage("§b§o[ 알림 ] §7비속어를 지속적으로 사용하셔서 채팅을 ". $blocktime ."분간 사용하실 수 없습니다. ( 총 주의 수 : ". $this->blockcount[$name]." ) ");
          $event->setCancelled();
          return;

        } else if( $this->blockcount[$name] > $blockcount ) {
          $exceed = $blocktime + $this->blockcount[$name] - $blockcount + 1 ;
          $event->getPlayer()->sendMessage("§b§o[ 알림 ] §7주의 수가 ".$blockcount."번을 초과하여 채팅 사용금지 시간이 ".$exceed."분으로 늘어납니다. ( 총 주의 수 : ".$this->blockcount[$name]." )");
          $this->blocklist[$name] = time() + ($exceed*60);
          $event->setCancelled();
          return;
        }

        $event->getPlayer()->sendMessage("§b§o[ 알림 ] §7비속어를 사용하셔서 주의를 받으셨습니다. ( 총 주의 수 : ".$this->blockcount[$name]." )");
        $event->getPlayer()->sendMessage("§b§o[ 알림 ] §7주의를 ".$this->configData["block-count"]."번 받을 시 채팅을 ".$this->configData["block-time"]."분간 사용할 수 없습니다.");
        $event->setCancelled();
        return;
      }
    }
  }


	public function onDisable() {
    $this->cacheData["block-list"] = $this->blocklist;
    $this->cacheData["block-count"] = $this->blockcount;
    $this->cacheConfig->setAll($this->cacheData);
    $this->cacheConfig->save();
	}

  public function save() {
    $this->config->setAll($this->configData);
    $this->config->save();
  }


  public function msg(CommandSender $sender, $msg) {
    $sender->sendMessage("§b§o[ 알림 ] §7".$msg);
  }

	public function onCommand(CommandSender $sender, Command $command, $label, array $args) :bool {
    switch($command->getName()) {
      case "아이템밴":
        $mode = "item";
        $kor = "아이템밴";
        break;
      case "조합밴":
        $mode = "craft";
        $kor = "조합밴";
        break;
      case "채팅밴":
        $mode = "chat";
        $kor = "채팅밴";
        break;
      case "명령어밴":
        $mode = "command";
        $kor = "명령어밴";
        break;
      default:
        return true;
    }
    if(!isset($args[0])) {
      $this->msg($sender, "/아이템밴 [추가/제거/목록]");
      $this->msg($sender, "/조합밴 [추가/제거/목록]");
      $this->msg($sender, "/채팅밴 [추가/제거/목록]");
      $this->msg($sender, "/명령어밴 [추가/제거/목록]");
       return true;
    }
    switch($args[0]) {
      case "추가":
        if(!isset($args[1])) {
          $this->msg($sender, "사용법 : /".$kor." ".$args[0]." [ ~ ]");
          return true;
        }
        if($mode == "item"||$mode == "craft") {
          if(count(explode(':', $args[1])) > 2 || !is_numeric(str_replace(':', '', $args[1]))) {
            $this->msg($sender, "사용법 : /".$kor." ".$args[0]." [아이템코드]");
            return true;
          }
          if( count(explode(':', $args[1])) == 1 )
            $tar = $args[1].':0';
          else
            $tar = $args[1];
        } else if ($mode == "command") {
          $t = $args;
          unset($t[0]);
          $tar = implode(' ', $t);
        } else {
          $tar = $args[1];
        }
        if(isset($this->configData[$mode][$tar])){
          $this->msg($sender, "이미 목록에 있습니다.");
          return true;
        }
        $this->configData[$mode][$tar] = true;
        $this->save();
        $this->msg($sender, "성공적으로 ".$args[0]."하였습니다.");
        return true;

      case "제거":
      case "삭제":
        if(!isset($args[1])) {
          $this->msg($sender, "사용법 : /".$kor." ".$args[0]." [ ~ ]");
          return true;
        }
        if($mode == "item"||$mode == "craft") {
          if(count(explode(':', $args[1])) > 2 || !is_numeric(str_replace(':', '', $args[1]))) {
            $this->msg($sender, "사용법 : /".$kor." ".$args[0]." [아이템코드]");
            return true;
          }
          if( count(explode(':', $args[1])) == 1 )
            $tar = $args[1].':0';
          else
            $tar = $args[1];
        } else if ($mode == "command") {
          $t = $args;
          unset($t[0]);
          $tar = implode(' ', $t);
        } else {
          $tar = $args[1];
        }
        if(!isset($this->configData[$mode][$tar])){
          $this->msg($sender, "입력하신것은 목록에 없습니다.");
          return true;
        }
        unset($this->configData[$mode][$tar]);
        $this->save();
        $this->msg($sender, "성공적으로 ".$args[0]."하였습니다.");
        return true;

      case "목록":
      case "리스트":
        $output = "§7";
        foreach(array_keys($this->configData[$mode]) as $k){
          $output .= "<".$k."> ";
        }
        $this->msg($sender, "====== ".$kor." 목록 ( ".count($this->configData[$mode])."개 ) ======");
        $sender->sendMessage($output);
        return true;
    }
    return false;
  }//함수 괄호
}//클래스 괄호

?>