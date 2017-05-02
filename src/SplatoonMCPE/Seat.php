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

use pocketmine\entity\Entity;
use pocketmine\entity\Item as ItemEntity;

use pocketmine\math\Vector3;

use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\network\protocol\SetEntityLinkPacket;

class Seat{

	private $seats = [];

	const ENTITY_ID = 23647500;

	/**
	 * 座っているかどうか
	 * @param  int  $id プレイヤーのID
	 * @return bool
	 */
	public function seated($id){
		return isset($this->seats[$id]);
	}

	/**
	 * 座る
	 * @param  Player $player
	 */
	public function seat(Player $player){
		//一時的に無効
		return false;

		$id = $player->getId();
		if(isset($this->seats[$id]) or !$player->isOnGround()) return true;//既に座っている or 空中にいる場合処理しない
		$level = $player->level;
		$player->setMotion(new Vector3(0, 0, 0));

		$entityid = self::ENTITY_ID + $id;
		$pk_Entity = new AddEntityPacket;
		$pk_Entity->eid = $entityid;
		$pk_Entity->type = ItemEntity::NETWORK_ID;
		$pk_Entity->x = $player->x;
		//$pk_Entity->y = $player->y - 0.245;
		$pk_Entity->y = $player->y - 0.235;
		$pk_Entity->z = $player->z;
		$pk_Entity->speedX = 0;
		$pk_Entity->speedY = 0;
		$pk_Entity->speedZ = 0;
		$pk_Entity->yaw = $player->yaw;
		$pk_Entity->pitch = 0;
		$pk_Entity->metadata = [
			Entity::DATA_FLAGS => [Entity::DATA_TYPE_BYTE, 32],
			Entity::DATA_AIR => [Entity::DATA_TYPE_SHORT, 300],
			Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, ""],
			Entity::DATA_SHOW_NAMETAG => [Entity::DATA_TYPE_BYTE, 1],
			Entity::DATA_SILENT => [Entity::DATA_TYPE_BYTE, 0],
			Entity::DATA_NO_AI => [Entity::DATA_TYPE_BYTE, 1]
		];
		$player->dataPacket($pk_Entity);

		$pk_Link = new SetEntityLinkPacket;
		$pk_Link->from = $entityid;
		$pk_Link->to = $player->getId();
		$pk_Link->type = 1;

		$this->seats[$id] = [
			'entity' => $pk_Entity,
			'link' => $pk_Link,
			'players' => [],
			'yaw' => $player->yaw,
		];

		//これは座るプレイヤーにのみ
		$pk = new SetEntityLinkPacket;
		$pk->from = $entityid;
		$pk->to = 0;
		$pk->type = 1;
		$player->dataPacket($pk);

		$player->sendTip("§bジャンプボタンで立つ");
		$player->resetFallDistance();

		$this->addSeatEntity($player, $player->getViewers());
		return true;
	}

	/**
	 * 立つ
	 * @param  Player $player
	 */
	public function stand(Player $player){
		//一時的に無効
		return true;

		$id = $player->getId();
		if(isset($this->seats[$id])){
			$data = $this->seats[$id];

			$entityid = self::ENTITY_ID + $id;
			$pk = new SetEntityLinkPacket;
			$pk->from = $entityid;
			$pk->to = 0;
			$pk->type = 0;
			$player->dataPacket($pk);

			$pk = new RemoveEntityPacket;
			$pk->eid = $entityid;
			$player->dataPacket($pk);

			$this->removeSeatEntity($player, $data['players']);

			unset($this->seats[$id]);
		}
		return true;
	}

	/**
	 * Seatのエンティティをスポーン
	 * @param Player $player  座っているプレイヤー
	 * @param array  $targets エンティティをスポーンさせる相手
	 */
	public function addSeatEntity(Player $player, $targets){
		//一時的に無効
		return false;

		$id = $player->getId();
		if(isset($this->seats[$id])){
			$data = $this->seats[$id];
			$entityid = self::ENTITY_ID + $id;
			$pk_Entity = $data['entity'];
			$pk_Link = $data['link'];
			foreach($targets as $p){
				if($p instanceof Player && !isset($data['players'][$p->getLoaderId()])){
					$p->dataPacket($pk_Entity);
					$p->dataPacket($pk_Link);
					$this->seats[$id]['players'][$p->getLoaderId()] = $p;
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * Seatのエンティティをデスポーン
	 * @param Player $player  座っているプレイヤー
	 * @param array  $targets エンティティをデスポーンさせる相手
	 */
	public function removeSeatEntity(Player $player, $targets){
		//一時的に無効
		return false;
		
		$id = $player->getId();
		if(isset($this->seats[$id])){
			$data = $this->seats[$id];
			$entityid = self::ENTITY_ID + $id;
			$pk = new SetEntityLinkPacket;
			$pk->from = $entityid;
			$pk->to = 0;
			$pk->type = 0;

			$pk = new RemoveEntityPacket;
			$pk->eid = $entityid;
			foreach($targets as $p){
				if($p instanceof Player && isset($data['player'][$p->getLoaderId()])){
					$pk = new RemoveEntityPacket;
					$pk->eid = $entityid;
					$p->dataPacket($pk);
				}
				unset($this->seats[$id]['player'][$p->getLoaderId()]);
			}
		}
		return true;
	}
}