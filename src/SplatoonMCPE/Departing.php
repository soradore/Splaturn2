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

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\network\protocol\BlockEventPacket;
use pocketmine\network\protocol\UpdateBlockPacket;
use pocketmine\network\protocol\LevelSoundEventPacket;

use pocketmine\scheduler\Task;

class Departing{

	const INSTRUMENT_PIANO = 0;
	const INSTRUMENT_BASS_DRUM = 1;
	const INSTRUMENT_CLICK = 2;
	const INSTRUMENT_TABOUR = 3;
	const INSTRUMENT_BASS = 4;

	/**
	 * フィールドテレポート前の処理を開始
	 * @param Main    $main
	 * @param mixed   $time  テレポートまでの時間
	 * @param boolean $sound 移動直前に音楽を鳴らすかどうか
	 */
	function __construct($main, $time, $sound = true){
		$this->main = $main;
		$this->time = $time;
		$this->players = [];
		$teams = $this->main->team->getBattleTeamMember();
		foreach($teams as $team => $members){
			foreach($members as $member => $status){
				$this->players[] = $member;
			}
		}
		$this->setSoundData();
		
		/*
		$bpm = 153;
		$beat = [
			'molecule' => 4,
			'denominator' => 8,
		];*/
		;
		$bpm = 124;
		$beat = [
			'molecule' => 16,
			'denominator' => 16,
		];
		$bps = 60 / $bpm / (4 / ($beat['molecule'] / $beat['denominator']));//Beats Per Second
		$soundTime = $bps * max(count($this->soundData), count($this->soundData2));
		$this->Task = Server::getInstance()->getScheduler()->scheduleRepeatingTask(new DepartingTask($this, $time, $soundTime, $bps), 1);
	}



	public function setSoundData(){
		/*$this->soundData = [
			13, false, 6, false, 6, false, 10, false,
			13, false, 6, false, 6, false, 10, false,
			13, false, 5, false, 5, false, 10, false,
			13, false, 5, false, 5, false, 10, false,
			6, 5, 6, 8, 10, 6, 10, 13,
			18, false, false, false, false, false, false, false,
		];
		$this->soundData2 = [
			false, 1, false, 1, false, 1, false, 1,
			false, 1, false, 1, false, 1, false, 1,
			false, 1, false, 1, false, 1, false, 1,
			false, 1, false, 1, false, 1, false, 1,
		];*/
		$this->soundData = [
			15, false, 10, false, 17, false, 10, false, 19, false, 15, false, 22, false, 15, false,
			27, false, false, false, false, false, 19, 22, 27, false, false, false, false, false, false, false
		];
		$this->soundData2 = [
			false, false, false, false, false, false, false, false, false, false, false, false, false, false, false, false,
			3, false, false, false, 5, false, false, false, 3, false, false, false, false, false, false, false
		];
	}

	public function getPlayers(){
		$players = [];
		$serverInstance = Server::getInstance();
		foreach($this->players as $member){
			if(($player = $serverInstance->getPlayer($member)) instanceof Player){
				$players[] = $player;
			}
		}
		return $players;
	}

function sendNoteBlockSound($pitch, $instrument = 0){
	foreach($this->getPlayers() as $player){
	/*$dir = $player->getDirectionVector();
	$x = $player->x + $dir->x;
	$y = $player->y + $dir->y;
	$z = $player->z + $dir->z;*/
		$x = $player->x;
		$y = $player->y;
		$z = $player->z;
		if($y > 256 or $y < 0){
			//鳴らせない
			continue;
		}
		$pk = new LevelSoundEventPacket;
		$pk->sound = LevelSoundEventPacket::SOUND_NOTE;
		$pk->x = $x;
		$pk->y = $y;
		$pk->z = $z;
		$pk->extraData = $instrument;
		$pk->pitch = $pitch;
		$pk->unknownBool = false;//?
		$pk->unknownBool2 = false;//?
		$player->dataPacket($pk);
	}
}

	public function NoteblockSound($pitch, $instrument = self::INSTRUMENT_PIANO){
		foreach($this->getPlayers() as $player){
			$dir = $player->getDirectionVector();
			$x = $player->x + $dir->x;
			$y = $player->y + $dir->y;
			$z = $player->z + $dir->z;

			if($y > 256 or $y < 0){
				//鳴らせない
				continue;
			}

			//一度Noteblockに変える
			$pk = new UpdateBlockpacket;
			$pk->x = $x;
			$pk->z = $z;
			$pk->y = $y;
			$pk->blockId = 25;
			$pk->blockData = 0;
			$pk->flags = UpdateBlockPacket::FLAG_NONE;

			$pk2 = new BlockEventPacket;
			$pk2->x = $x;
			$pk2->y = $y;
			$pk2->z = $z;
			$pk2->case1 = $instrument;
			$pk2->case2 = $pitch + (12 * 1);

			$fullBlock = Server::getInstance()->getDefaultLevel()->getFullBlock($x, $y, $z);
			
			//鳴らしたあと戻す
			$pk3 = new UpdateBlockpacket;
			$pk3->x = $x;
			$pk3->z = $z;
			$pk3->y = $y;
			$pk3->blockId = $fullBlock >> 4;
			$pk3->blockData = $fullBlock & 0xf;
			$pk3->flags = UpdateBlockPacket::FLAG_NONE;

			$player->dataPacket($pk);
			$player->dataPacket($pk2);
			$player->dataPacket($pk3);
		}
	}

	public function sendEXP($value){
		foreach($this->getPlayers() as $player){
			if(!isset($this->main->trypaintData['player'][$player->getName()])){
				$player->setXpProgress($value);
			}
		}
	}

	/**
	 * タスクを停止
	 * @param  boolean $proceed default = trueゲーム進行処理を行うかどうか
	 */
	public function close($proceed = true){
		if(isset($this->Task)){
			Server::getInstance()->getScheduler()->cancelTask($this->Task->getTaskId());
			unset($this->Task);
			$this->sendEXP(0);
			if($proceed){
				$this->main->TimeTable();
			}
		}
	}
}

class DepartingTask extends Task{

	public function __construct($owner, $time, $soundTime, $bps){
		$this->owner = $owner;
		$this->time = $time;
		$this->max = $this->time * 20;
		$this->soundStart = floor($this->max - ($soundTime - 1) * 20);
		$this->soundTick = floor($bps * 20);

		$this->count = 0;
	}

	public function getOwner(){
		return $this->owner;
	}

	public function onRun($tick){
		if($this->count && $this->count % $this->soundTick === 0){
			$countValue = ($this->count - $this->soundStart) / $this->soundTick;
			if($countValue >= 0){
				if(isset($this->getOwner()->soundData[$countValue]) || isset($this->getOwner()->soundData2[$countValue])){
					$pitch = $this->getOwner()->soundData[$countValue] ?? false;
					if($pitch !== false){
						$this->getOwner()->sendNoteBlockSound($pitch);
					}
					$pitch2 = $this->getOwner()->soundData2[$countValue] ?? false;
					if($pitch2 !== false){
						$this->getOwner()->sendNoteBlockSound($pitch2);
					}
				}
			}
		}
		$expValue = 1 - $this->count / $this->max;
		$this->getOwner()->sendEXP($expValue);

		$this->count++;
		if($this->max <= $this->count){
			$this->getOwner()->close();
		}
	}
}