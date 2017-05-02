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

use pocketmine\entity\Entity;
use pocketmine\Player;
use pocketmine\Server;

use pocketmine\math\Vector3;

use pocketmine\scheduler\Task;

use pocketmine\network\protocol\LevelEventPacket;

class BattleResultAnimation{

	private $animationQueue = 0,
			$cache = [],
			$progress = 0,
			$lastMemoryValue = 0,
			$main,
			$players = [],
			$result,
			$Task = [];

	public function __construct($main, $result, $message){
		$this->main = $main;
		$this->result = $result;
		$this->memory = "|";
		$this->memoryLimit = 100;//ゲージを表示する数
		$this->message = $message;
		$this->players = [];
		foreach($this->main->team->getBattleTeamMember() as $team_num => $members){
			foreach($members as $member => $status){
				$this->players[] = $member;
			}
		}
		$this->plus = 0.025;
		$this->minValue = min($this->result[1]['percentage'] ?? 0, $this->result[2]['percentage'] ?? 0);
		$this->toggleSpeed = [
			3 => $this->minValue * 0.7,//減速
			4 => $this->minValue * 0.8,//一旦停止してから加速
			7 => 1,
		];
		//$this->teamName = [1 => str_pad($this->result[1]['name'] ?? "", 9, " ", STR_PAD_LEFT), 2 => str_pad($this->result[2]['name'] ?? "", 9, " ", STR_PAD_RIGHT)];
		$this->AnimationSchedule();
	}

	public function close(){
		$this->TaskStop();
	}

	/**
	 * フィールド上空へプレイヤーをテレポート
	 */
	public function teleportTopField(){
		$field_data = $this->main->getBattleField($this->main->getFieldNumber());
		if(isset($field_data['cam'][4])){
			$pos = $field_data['cam'][4];
			$position = new Vector3($pos[0], $pos[1], $pos[2]);
			$yaw = $pos[3] ?? null;
			$pitch = $pos[4] ?? null;
			foreach($this->getPlayers() as $player){
				$player->setGamemode(Player::SPECTATOR);
				$player->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE, true);
				$player->sendData($player);
				$player->setAllowFlight(false);
				$player->teleportImmediate($position, $yaw, $pitch);
				$player->sendPosition($position, $yaw, $pitch);
				$player->setRotation($yaw, $pitch);
			}
			return true;
		}
		return false;
	}

	/**
	 * 試合メンバーのプレイヤーオブジェクトを配列で取得
	 * @return Player[]
	 */
	private function getPlayers(){
		$re = [];
		foreach($this->players as $username){
			if(($player = Server::getInstance()->getPlayer($username)) instanceof Player){
				$re[] = $player;
			}
		}
		return $re;
	}

	/**
	 * ゲージ増加速度調整を自動で行う
	 */
	public function phaseMonitoring(){
		$this->progress += $this->plus;
		$this->PaintResultGauge($this->progress);
		if($this->toggleSpeed[$this->animationQueue] <= $this->progress){
			$i = 1;
			while(isset($this->toggleSpeed[$this->animationQueue + $i]) && $this->toggleSpeed[$this->animationQueue + $i] <= $this->progress){
				$i+=1;
			}
			$this->AnimationSchedule($i);
		}
	}

	/**
	 * 塗りのゲージを表示する
	 * @param float $progress maxを1とした数値
	 */
	private function PaintResultGauge($progress){
		if($progress >= 1){
			$progress = 1;
		}
		$gauge = [];
		$this->result;
		$restValue = $this->memoryLimit;
		$maxValue = 0;
		if(isset($this->cache[md5($progress)])){
			$cacheData = $this->cache[md5($progress)];
			$out = $cacheData[0];
			$maxValue = $cacheData[1];
		}else{
			foreach($this->result as $team_num => $data){
				//$value = $data['percentage'] * $progress;
				$value = $progress > $data['percentage'] ? $data['percentage'] : $progress;
				$value2 = $progress > $data['percentage2'] ? $data['percentage2'] : $progress;
				$gaugeValue = floor($this->memoryLimit * $value);
				$restValue -= $gaugeValue;
				$gauge[$team_num] = [str_repeat($data['color'].$this->memory, $gaugeValue), $value2];
				$maxValue = $maxValue < $gaugeValue ? $gaugeValue : $maxValue;
			}
			$percentage1 = str_pad(sprintf("%.1F", (isset($gauge[1][1]) ? floor($gauge[1][1] * 1000) / 10 : 0))."%%", 5, " ", STR_PAD_RIGHT);
			$percentage2 = str_pad(sprintf("%.1F", (isset($gauge[2][1]) ? floor($gauge[2][1] * 1000) / 10 : 0))."%%", 5, " ", STR_PAD_LEFT);
			//$out = $this->teamName[1] . " " . ($gauge[1][0] ?? "") . str_repeat("§7".$this->memory, $restValue) . ($gauge[2][0] ?? "") . " " . $this->teamName[2];
			$out = $percentage1 . "  " . ($gauge[1][0] ?? "") . str_repeat("§7".$this->memory, $restValue) . ($gauge[2][0] ?? "") . "§f  " . $percentage2;
			$this->cache[md5($progress)] = [$out, $maxValue];
		}
		if($this->lastMemoryValue != $maxValue){
			$shouldPlaySound = true;
			$this->lastMemoryValue = $maxValue;
		}else{
			$shouldPlaySound = false;
		}
		foreach(Server::getInstance()->getOnlinePlayers() as $player){
			$player->sendPopup($out);
			if($shouldPlaySound){
				$this->gaugeIncreaseSound($player, $maxValue);
			}
		}
	}

	/**
	 * ゲージ増加のサウンドを鳴らす
	 * @param  Player $player
	 * @param  mixed  $value
	 */
	private function gaugeIncreaseSound(Player $player, $value){
		$pk = new LevelEventPacket;
		$pk->evid = 1030;
		$pk->x = $player->x;
		$pk->y = $player->y;
		$pk->z = $player->z;
		//$pk->data = 500 + (5 * $value);
		$pk->data = 570 + (5 * $value);
		$player->dataPacket($pk);
	}

	public function AnimationSchedule($plus = 1){
		$this->TaskStop();
		$queue = $this->animationQueue += $plus;
		switch($queue){
			case 1:
				$this->teleportTopField();
			case 2:
				$this->PaintResultGauge(0);
				$this->Task['Animation'][$queue] = Server::getInstance()->getScheduler()->scheduleDelayedTask(new ResultScheduleTask($this), 15);
				break;
			case 3:
				$this->Task['IncreaseGauge'][$queue] = Server::getInstance()->getScheduler()->scheduleRepeatingTask(new IncreaseGauge($this), 8);
				break;
			case 4:
				$this->Task['IncreaseGauge'][$queue] = Server::getInstance()->getScheduler()->scheduleRepeatingTask(new IncreaseGauge($this), 12);
				break;
			case 5:
			case 6:
				$this->PaintResultGauge($this->progress);
				$this->Task['Animation'][$queue] = Server::getInstance()->getScheduler()->scheduleDelayedTask(new ResultScheduleTask($this), 15);//20くらいになるとゲージの表示が消えるので2回にわける
				break;
			case 7:
				$this->Task['IncreaseGauge'][$queue] = Server::getInstance()->getScheduler()->scheduleRepeatingTask(new IncreaseGauge($this), 2);
				break;
			case 9://ここで試合結果送信
				Server::getInstance()->broadcastMessage($this->message);
			case 8:
			case 10:
			case 11:
				$this->PaintResultGauge(1);
				$this->Task['Animation'][$queue] = Server::getInstance()->getScheduler()->scheduleDelayedTask(new ResultScheduleTask($this), 15);
				break;
			case 12:
				//終了の処理する
				$this->animationQueue = 0;
				$this->progress = 0;
				$this->cache = [];
				$this->main->TimeTable();
				break;
		}
	}

	private function TaskStop(){
		foreach($this->Task as $taskName => $tasks){
			foreach($tasks as $taskNum => $task){
				Server::getInstance()->getScheduler()->cancelTask($task->getTaskId());
			}
		}
		$this->Task = [];
	}
}

class ResultScheduleTask extends Task{

	public function __construct($owner){
		$this->owner = $owner;
	}

	public function getOwner(){
		return $this->owner;
	}

	public function onRun($tick){
		$this->getOwner()->AnimationSchedule();
	}
}

class IncreaseGauge extends Task{

	public function __construct($owner){
		$this->owner = $owner;
	}

	public function getOwner(){
		return $this->owner;
	}

	public function onRun($tick){
		$this->getOwner()->phaseMonitoring();
	}
}