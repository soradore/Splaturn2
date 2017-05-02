<?php

/**
 * 
 *   _____       _       _                    
 *  / ____|     | |     | |                   
 * | (___  _ __ | | __ _| |_ _   _ _ __ _ __  
 *  \___ \| '_ \| |/ _` | __| | | | '__| '_ \ 
 *  ____) | |_) | | (_| | |_| |_| | |  | | | |
 * |_____/| .__/|_|\__,_|\__|\__,_|_|  |_| |_|
 *        | |                                 
 *        |_|                                 
 *
 * @author Splaturn開発チーム
 * @link http://splaturn.net/
 *                  
 */

namespace SplatoonMCPE;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\Server;

class StatusUnitCommand extends Command{

	public function __construct(PluginBase $main){
		parent::__construct("s");
		$this->setPermission("splatt.command.admin");
		$this->main = $main;
	}

	public function execute(CommandSender $sender, $commandlabel, array $args){
		if(!$this->main->isEnabled() || !$this->testPermission($sender)){
			return false;
		}
		if(empty($args[0])){
			$this->sendUsage($sender);
			return false;
		}
		$out = "";
		$user = $sender->getName();
		switch($args[0]){
			case "servers":
				$this->main->s->confirmServers();
				Command::broadcastCommandMessage($sender, $this->main->lang->translateString("command.s.serversDataUpdate"));
				return true;
			case "stars":
				$this->main->s->confirmStars();
				Command::broadcastCommandMessage($sender, $this->main->lang->translateString("command.s.starsDataUpdate"));
				return true;
			case "punish":
				$this->main->s->confirmPunishment();
				Command::broadcastCommandMessage($sender, $this->main->lang->translateString("command.s.punishDataUpdate"));
				return true;
			case "rank":
			case "ranks":
				$this->main->s->confirmRanks();
				Command::broadcastCommandMessage($sender, $this->main->lang->translateString("command.s.ranksDataUpdate"));
				return true;
			case "stage":
			case "field":
				$this->main->s->confirmBattleField();
				$this->main->FloatText(true);
				Command::broadcastCommandMessage($sender, $this->main->lang->translateString("command.s.fieldDataUpdate"));
				return true;
			//サーバーナンバーの設定
			case "sno":
				if(empty($args[1])){
					$this->sendUsage($sender);
					return false;
				}
				if($this->main->s->writeSNO($args[1])){
					Command::broadcastCommandMessage($sender, $this->main->lang->translateString("command.s.snoData.setting.success", [$args[1]]));
					$this->main->s->__construct($this->main);
					$this->main->setFloatText([4]);
					return true;
				}else{
					$out = $this->main->lang->translateString("command.s.snoData.setting.error");
				}
				break;
			case "lang":
				if($this->main->s->hasOp($user) !== 2 && $user !== "CONSOLE"){//opc以外は使えないように
					$sender->sendMessage($this->main->lang->translateString("command.notpermission"));
					return true;
				}
				if(empty($args[1])){
					$this->sendUsage($sender);
					return false;
				}
				if(isset($args[2]) and ($args[2] === "on" or $args[2] === "true" or $args[2] === "t" or $args[2] === "1")){
					$result = $this->main->lang->setLang($args[1]) and $this->main->s->setLanguage($args[1]);
				}else{
					if($this->main->s->getLanguage() == null){//既に設定されていないかどうか
						$result = $this->main->lang->setLang($args[1]);
					}else{
						$sender->sendMessage($this->main->lang->translateString("language.setting.failure"));
						return true;
					}
				}
				if($result){
					Command::broadcastCommandMessage($sender, $this->main->lang->translateString("language.setting.success"));
					$this->main->setData();
					return true;
				}else{
					$out = $this->main->lang->translateString("language.fileError");
				}
				break;
			case "se":
				if(isset($args[1])){
					if(($serverName = $this->main->s->getServerName($args[1])) !== false){
						$this->main->s->setEmergency($args[1]);
						$this->main->s->confirmServers();
						Command::broadcastCommandMessage($sender, $this->main->lang->translateString("command.s.setEmergency", [$serverName]));
					}else{
						$out = $this->main->lang->translateString("command.server.notfound");//んなもんねえよ
					}
				}else{
					$this->sendUsage($sender);
					return false;
				}
				break;
			case "show":
				$data = $this->main->s->show();
				$out = $this->main->lang->translateString("show");
				if(empty($args[0]) && $user == "CONSOLE"){
					echo $data;//コンソールの場合でコマンドのパラメーターになんか指定してなければechoで出力
				}else{
					$out .= "\n".$data;
				}
				break;
			case "info":
				$out = $this->main->lang->translateString("command.s.serverData.info", [$this->main->s->sname, $this->main->s->sno]);
				break;
			default:
				$this->sendUsage($sender);
				return false;
		}
		if($out !== ""){
			$sender->sendMessage($out);
		}
	}

	private function sendUsage($sender){
		if($this->usageMessage !== ""){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));
		}
	}
}

class BanCommand extends Command{

	public function __construct(PluginBase $main){
		parent::__construct("pban");
		$this->setPermission("splatt.command.admin");
		$this->main = $main;
	}

	public function execute(CommandSender $sender, $commandlabel, array $args){
		if(!$this->main->isEnabled() || !$this->testPermission($sender)){
			return false;
		}
		$out = "";
		$user = $sender->getName();
		if(count($args) >= 2){
			$name = array_shift($args);
			$com = trim(implode(" ", $args));
			$result = $this->main->s->sendPunishment($name, $user, StatusUnit::PUNISH_BAN, $com);
			$out = $result[1];
			if($result[0]){
				Command::broadcastCommandMessage($sender, $this->main->lang->translateString("command.pban.success.operator", [$name, $com]));
				if(($player = Server::getInstance()->getPlayer($name)) instanceof Player){
					$this->main->s->confirmPunishment();
				}
			}
			$sender->sendMessage($out);
			return true;
		}
		$this->sendUsage($sender);
		return false;
	}

	private function sendUsage($sender){
		if($this->usageMessage !== ""){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));
		}
	}
}

class WarnCommand extends Command{

	public function __construct(PluginBase $main){
		parent::__construct("pwarn");
		$this->setPermission("splatt.command.admin");
		$this->main = $main;
	}

	public function execute(CommandSender $sender, $commandlabel, array $args){
		if(!$this->main->isEnabled() || !$this->testPermission($sender)){
			return false;
		}
		$out = "";
		$user = $sender->getName();
		if(count($args) >= 2){
			$name = array_shift($args);
			$com = trim(implode(" ", $args));
			$result = $this->main->s->sendPunishment($name, $user, StatusUnit::PUNISH_WARN, $com);
			$out = $result[1];
			if($result[0]){
				Command::broadcastCommandMessage($sender, $this->main->lang->translateString("command.pwarn.success.operator", [$name, $com]));
				if(($player = Server::getInstance()->getPlayer($name)) instanceof Player){
					$player->sendMessage($this->main->lang->translateString("command.pwarn.success.target", [$com]));
					$this->main->s->confirmPunishment();
				}
			}
			$sender->sendMessage($out);
			return true;
		}
		$this->sendUsage($sender);
		return false;
	}

	private function sendUsage($sender){
		if($this->usageMessage !== ""){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));
		}
	}
}

class SetModeCommand extends Command{

	public function __construct(PluginBase $main){
		parent::__construct("setmode");
		$this->setPermission("splatt.command.admin");
		$this->main = $main;
	}

	public function execute(CommandSender $sender, $commandlabel, array $args){
		if(!$this->main->isEnabled() || !$this->testPermission($sender)){
			return false;
		}
		if(count($args) >= 1){
			if($args[0] === "off"){
				if($this->main->s->loginRestriction){
					Command::broadcastCommandMessage($sender, $this->main->lang->translateString("command.setmode.off"));
					$this->main->s->loginRestriction = 0;
					return true;
				}else{
					$out = $this->main->lang->translateString("command.setmode.isNot");
					$sender->sendMessage($out);
					return true;
				}
			}else{
				$args = array_values(array_unique($args));
				$perm = $this->main->s->setLoginRestriction("§7", $args);
				if($perm === false){
					if($this->main->s->loginRestriction){
						Command::broadcastCommandMessage($sender, $this->main->lang->translateString("command.setmode.off"));
						$this->main->s->loginRestriction = 0;
						return true;
					}else{
						$out = $this->main->lang->translateString("command.setmode.isNot");
						$sender->sendMessage($out);
						return true;
					}
				}else{
					Command::broadcastCommandMessage($sender, $this->main->lang->translateString("loginRestriction", [$perm]));
					return true;
				}
			}
		}else{
			$this->sendUsage($sender);
			return false;
		}
		return false;
	}

	private function sendUsage($sender){
		if($this->usageMessage !== ""){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));
		}
	}
}