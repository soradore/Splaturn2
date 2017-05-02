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

use pocketmine\block\Block;
use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\math\Vector3;
use pocketmine\utils\UUID;
use pocketmine\level\Position;
use pocketmine\level\particle\DestroyBlockParticle;

use pocketmine\scheduler\Task;

use pocketmine\network\protocol\AddPlayerPacket;
use pocketmine\network\protocol\MovePlayerPacket;
use pocketmine\network\protocol\RemoveEntityPacket;

class TPAnimation{

	private $animationEntity = [];
	public $snt = 1;
	public $animationQueue = 1;
	public $level;
	public $entityData = [];

	/**
	 * @param Main  $main 
	 * @param array $team
	 * @param array $pos
	 * @param array $blockdata   チームのインデックス->ブロックのデータ値を取得できるように
	 * @param array $weapondata  プレイヤーのブキを取得できるように
	 */
	function __construct($main, $team, $pos, $blockdata, $weapondata){
		$this->main = $main;
		$this->team = $team;
		$this->pos = $pos;
		$this->blockdata = $blockdata;
		$this->weapondata = $weapondata;
		$this->BattleAnimationSchedule();
	}

	public function Close(){
		foreach($this->Task as $taskName => $tasks){
			if($taskName === 'Animation'){
				foreach($tasks as $ani_task){
					Server::getInstance()->getScheduler()->cancelTask($ani_task->getTaskId());
				}	
			}else{
				Server::getInstance()->getScheduler()->cancelTask($tasks->getTaskId());
			}
		}
		$this->Task = [];
		$this->deleteClonePlayers();
		if($this->animationQueue < 6){
			$this->teleportSpawn();
		}
	}

	public function reverseTeam($int){
		if($int == 1){
			return 2;
		}elseif($int == 2){
			return 1;
		}
		return 0;
	}

	public function createClonePlayer($player, $x, $y, $z, $yaw = 0, $pitch = 0){
		$serverInstance = Server::getInstance();
		$user = $player->getName();
		$name = $player->getDisplayName();
		$level = $this->level;
		$skinData = $player->getSkinData();
		$skinId = $player->getSkinId();
		$eid = 503000 + $player->getId();
		$itemid = (isset($this->weapondata[$user])) ? $this->weapondata[$user] : [0, 0];
		$uuid = UUID::fromData($eid, $skinData, $name);
		$serverInstance->updatePlayerListData($uuid, $eid, $name, $skinId, $skinData);

		$pk = new AddPlayerPacket;
		$pk->uuid = $uuid;
		$pk->username = $name;
		$pk->eid = $eid;
		$pk->x = $x;
		$pk->y = $y;
		$pk->z = $z;
		$pk->speedX = 0;
		$pk->speedY = 0;
		$pk->speedZ = 0;
		$pk->yaw = $yaw;
		$pk->pitch = $pitch;
		$pk->item = Item::get($itemid[0], $itemid[1], 1);
		$pk->metadata = [
				Entity::DATA_FLAGS => [Entity::DATA_TYPE_BYTE, 0],
				Entity::DATA_AIR => [Entity::DATA_TYPE_SHORT, 300],
				Entity::DATA_FLAG_CAN_SHOW_NAMETAG => [Entity::DATA_TYPE_BYTE, 1],
				Entity::DATA_FLAG_SILENT => [Entity::DATA_TYPE_BYTE, 1],
				Entity::DATA_FLAG_IMMOBILE => [Entity::DATA_TYPE_BYTE, 1],
				Entity::DATA_OWNER_EID => [Entity::DATA_TYPE_LONG, -1],
				Entity::DATA_LEAD_HOLDER_EID => [Entity::DATA_TYPE_LONG, -1],
		];
		$players = $level->getChunkPlayers($pk->x >> 4, $pk->z >> 4);
		$this->hasSpawned[$eid] = $players;
		$serverInstance->broadcastPacket($players, $pk);

		$serverInstance->removePlayerListData($uuid);
		$this->entityData[$eid] = $uuid;
		$this->pos_ar[$eid] = [$x, $y, $z, $yaw, $pitch];
		return $eid;
	}
	
	public function deleteClonePlayers(){
		foreach($this->animationEntity as $team => $m){
			foreach($m as $e){
				$pk = new RemoveEntityPacket;
				$pk->eid = $e;
				Server::getInstance()->broadcastPacket($this->hasSpawned[$e], $pk);
			}
		}
		$this->animationEntity = [];
	}

	//下から上がってくるアニメーション
	public function startSpawningClonePlayer(){
		$level = $this->level;
		$p = $this->pos[1];
		$p2 = $this->pos[2];
		switch($this->snt){
			case 1: case 2: case 3: case 4:
				$key  = $this->snt;
				$this->spawnCheck(1, $key);
				$this->spawnCheck(2, $key);
				break;
		}
		switch($this->snt){
			case 2: case 3: case 4: case 5:
				$key = $this->snt - 1;
				if($this->spawnCheck(1, $key)){
					$block    = new Block(35, $this->blockdata[1]);
					$particle = new DestroyBlockParticle(new Vector3($p[$key][0], $p[$key][1] + 0.5, $p[$key][2]), $block);
					$level->addParticle($particle);
				}
				if($this->spawnCheck(2, $key)){
					$block     = new Block(35, $this->blockdata[2]);
					$particle2 = new DestroyBlockParticle(new Vector3($p2[$key][0], $p2[$key][1] + 0.5, $p2[$key][2]), $block);
					$level->addParticle($particle2);
				}
				break;
		}
		switch($this->snt){
			case 3: case 4: case 5: case 6:
				$key = $this->snt - 2;
				if($this->spawnCheck(1, $key)){
					$block    = new Block(35, $this->blockdata[1]);
					$particle = new DestroyBlockParticle(new Vector3($p[$key][0], $p[$key][1] + 0.5, $p[$key][2]), $block);
					$level->addParticle($particle);
				}
				if($this->spawnCheck(2, $key)){
					$block     = new Block(35, $this->blockdata[2]);
					$particle2 = new DestroyBlockParticle(new Vector3($p2[$key][0], $p2[$key][1] + 0.5, $p2[$key][2]), $block);
					$level->addParticle($particle2);
				}
				break;
		}
		switch($this->snt){
			case 4: case 5: case 6: case 7:
				$key = $this->snt - 3;
				$pkey = $this->snt - 4;
				if($this->spawnCheck(1, $key)){
					$block    = new Block(35, $this->blockdata[1]);
					$particle = new DestroyBlockParticle(new Vector3($p[$key][0], $p[$key][1] + 0.5, $p[$key][2]), $block);
					$level->addParticle($particle);
				}
				if($this->spawnCheck(2, $key)){
					$block     = new Block(35, $this->blockdata[2]);
					$particle2 = new DestroyBlockParticle(new Vector3($p2[$key][0], $p2[$key][1] + 0.5, $p2[$key][2]), $block);
					$level->addParticle($particle2);
				}
				$e  = $this->animationEntity[1][$pkey] ?? false;
				$e2 = $this->animationEntity[2][$pkey] ?? false;
				if($e){
					$pk = new MovePlayerPacket;
					$pk->eid = $e;
					$pk->x = $p[$key][0];
					$pk->y = $p[$key][1] + 1.62;
					$pk->z = $p[$key][2];
					$pk->yaw = $this->pos_ar[$e][3];
					$pk->bodyYaw = $this->pos_ar[$e][3];
					$pk->pitch = $this->pos_ar[$e][4];
					$pk->mode = MovePlayerPacket::MODE_NORMAL;
					Server::getInstance()->broadcastPacket($this->hasSpawned[$e], $pk);
				}
				if($e2){
					$pk = new MovePlayerPacket;
					$pk->eid = $e2;
					$pk->x = $p2[$key][0];
					$pk->y = $p2[$key][1] + 1.62;
					$pk->z = $p2[$key][2];
					$pk->yaw = $this->pos_ar[$e2][3];
					$pk->bodyYaw = $this->pos_ar[$e2][3];
					$pk->pitch = $this->pos_ar[$e2][4];
					$pk->mode = MovePlayerPacket::MODE_NORMAL;
					Server::getInstance()->broadcastPacket($this->hasSpawned[$e2], $pk);
				}
				break;
		}
		switch($this->snt){
			case 5: case 6: case 7: case 8:
				$key  = $this->snt - 4;
				if($this->spawnCheck(1, $key)){
					$block     = new Block(35, $this->blockdata[1]);
					$particle  = new DestroyBlockParticle(new Vector3($p[$key][0], $p[$key][1] + 0.5, $p[$key][2]), $block);
					$level->addParticle($particle);
				}
				if($this->spawnCheck(2, $key)){
					$block     = new Block(35, $this->blockdata[2]);
					$particle2 = new DestroyBlockParticle(new Vector3($p2[$key][0], $p2[$key][1] + 0.5, $p2[$key][2]), $block);
					$level->addParticle($particle2);
				}
				break;
		}
		$this->snt += 1;
		if(10 < $this->snt){
			Server::getInstance()->getScheduler()->cancelTask($this->Task['spawningAnimation']->getTaskId());
			$this->snt = 1;
			$this->Task['Animation'][] = Server::getInstance()->getScheduler()->scheduleDelayedTask(new Animation($this), 20 * 3);
		}
	}

	private function spawnCheck($teamNum, $key){
		$playerKey = $key - 1;
		$pos = $this->pos[$teamNum];
		if(isset($this->team[$teamNum][$playerKey])){
			if(($player = Server::getInstance()->getPlayer($this->team[$teamNum][$playerKey])) instanceof Player){
				if(!isset($this->animationEntity[$teamNum][$playerKey])){
					$entityId = $this->createClonePlayer($player, $pos[$key][0], $pos[$key][1] - 2, $pos[$key][2], ($pos[0][3] + 180) % 360, -17.5);
					if($entityId){
						$this->animationEntity[$teamNum][$playerKey] = $entityId;
					}
				}
				return true;
			}elseif(isset($this->animationEntity[$teamNum][$playerKey])){
				$entityId = $this->animationEntity[$teamNum][$playerKey];
				$pk = new RemoveEntityPacket;
				$pk->eid = $entityId;
				Server::getInstance()->broadcastPacket($this->hasSpawned[$entityId], $pk);
				unset($this->animationEntity[$teamNum][$playerKey]);
			}
		}
		return false;
	}

	public function teleportPlayers($co){
		$level = $this->level;
		if($co){
			foreach($this->pos as $team => $m){
				$pos = $m[0];
				foreach($m as $pkey => $ppos){
					if(isset($this->team[$team][$pkey]) && ($p = Server::getInstance()->getPlayer($this->team[$team][$pkey])) instanceof Player){
						if($this->main->team->getBattleTeamOf($p->getName())){
							$p->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE, true);
							$p->sendData($p);
							foreach(Server::getInstance()->getOnlinePlayers() as $pl){
								$pl->hidePlayer($p);
							}
							$p->addEffect(Effect::getEffect(Effect::INVISIBILITY)->setDuration(150000)->setAmplifier(0)->setVisible(false));
							$position = new Position($pos[0], $pos[1], $pos[2], $level);
							$p->teleportImmediate($position, $pos[3], $pos[4]);
							$p->sendPosition($position, $pos[3], $pos[4]);
						}
					}
				}
			}
		}else{
			foreach($this->pos as $team => $m){
				$teamr = $this->reverseTeam($team);
				$pos = $this->pos[$teamr][0];
				foreach($m as $pkey => $ppos){
					if(isset($this->team[$team][$pkey]) && ($p = Server::getInstance()->getPlayer($this->team[$team][$pkey])) instanceof Player){
						if($this->main->team->getBattleTeamOf($p->getName())){
							foreach(Server::getInstance()->getOnlinePlayers() as $pl){
								$pl->hidePlayer($p);
							}
							$p->addEffect(Effect::getEffect(Effect::INVISIBILITY)->setDuration(150000)->setAmplifier(0)->setVisible(false));
							$position = new Position($pos[0], $pos[1], $pos[2], $level);
							$p->teleportImmediate($position, $pos[3], $pos[4]);
							$p->sendPosition($position, $pos[3], $pos[4]);
						}
					}
				}
			}
		}
	}

	public function getPlayerTeleportPosition($player){
		$level = $this->level;
		$user = $player->getName();
		$team = $this->main->team->getBattleTeamOf($user);
		$data = $this->team[$team];
		$pkey = array_search($user, $data);
		if($pkey === false) return false;
		switch($this->animationQueue){
			case 1:
			case 2:
			case 3:
				$pos = $this->pos[$team][0];
				$player->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE, true);
				$player->sendData($player);
				foreach(Server::getInstance()->getOnlinePlayers() as $pl){
					$pl->hidePlayer($player);
				}
				$player->addEffect(Effect::getEffect(Effect::INVISIBILITY)->setDuration(150000)->setAmplifier(0)->setVisible(false));
				$position = new Position($pos[0], $pos[1], $pos[2], $level);
				return $position;
			case 4:
			case 5:
			case 6:
				$teamr = $this->reverseTeam($team);
				$pos = $this->pos[$teamr][0];
				foreach(Server::getInstance()->getOnlinePlayers() as $pl){
					$pl->hidePlayer($player);
				}
				$player->addEffect(Effect::getEffect(Effect::INVISIBILITY)->setDuration(150000)->setAmplifier(0)->setVisible(false));
				$position = new Position($pos[0], $pos[1], $pos[2], $level);
				return $position;
			default:
				return false;
		}
	}

	public function teleportSpawn(){
		$level = $this->level;
		foreach($this->pos as $team => $m){
			$teamr = $this->reverseTeam($team);
			$pos = $this->pos[$teamr][0];
			foreach($m as $pkey => $ppos){
				if($m){
					$npkey = $pkey - 1;
					if(isset($this->team[$team][$npkey]) && ($p = Server::getInstance()->getPlayer($this->team[$team][$npkey])) instanceof Player){
						if($this->main->team->getBattleTeamOf($p->getName())){
							$p->teleport(new Position($ppos[0], $ppos[1], $ppos[2], $level), $pos[3], $pos[4]);
							foreach(Server::getInstance()->getOnlinePlayers() as $pl){
								$pl->showPlayer($p);
							}
							$p->removeEffect(Effect::INVISIBILITY);
						}
					}
				}
			}
		}
	}


	public function BattleAnimationSchedule(){
		switch($this->animationQueue){
			case 1:
				//テレポート
				$this->level = Server::getInstance()->getDefaultLevel();
				$this->teleportPlayers(true);
				$this->Task['Animation'][] = Server::getInstance()->getScheduler()->scheduleDelayedTask(new Animation($this), 20 * 2.5);
				break;
			case 2:
				//エンティティ作ってあげる
				$this->Task['spawningAnimation'] = Server::getInstance()->getScheduler()->scheduleRepeatingTask(new spawningAnimation($this), 2.5);
				break;
			case 3:
				//敵陣テレポート
				$this->level = Server::getInstance()->getDefaultLevel();
				$this->teleportPlayers(false);
				$this->deleteClonePlayers();
				$this->Task['Animation'][] = Server::getInstance()->getScheduler()->scheduleDelayedTask(new Animation($this), 20 * 2.5);
				break;
			case 4:
				//エンティティ作ってあげる
				$this->Task['spawningAnimation'] = Server::getInstance()->getScheduler()->scheduleRepeatingTask(new spawningAnimation($this), 2.5);
				break;
			case 5:
				$this->deleteClonePlayers();
				$this->teleportSpawn();
				$this->main->TimeTable();
				break;
		}
		$this->animationQueue ++;
	}
}

class spawningAnimation extends Task{

	public function __construct($owner){
		$this->owner = $owner;
	}

	public function getOwner(){
		return $this->owner;
	}

	public function onRun($tick){
		$this->getOwner()->startSpawningClonePlayer(true);
	}
}

class Animation extends Task{

	public function __construct($owner){
		$this->owner = $owner;
	}

	public function getOwner(){
		return $this->owner;
	}

	public function onRun($tick){
		$this->getOwner()->BattleAnimationSchedule();
	}
}