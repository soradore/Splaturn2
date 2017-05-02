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

use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\level\Location;
use pocketmine\level\Explosion;
use pocketmine\level\MovingObjectPosition;
use pocketmine\level\format\FullChunk;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\ByteTag;

use pocketmine\entity\Entity;
use pocketmine\entity\human;

use pocketmine\item\Item as ItemItem;

use pocketmine\block\Block;

class Charpse extends Splapse{

	public static function summon($level, $x, $y, $z, $lv = null){
		if(is_null($lv)){
			$lv = mt_rand(0, 10);
		}
		$nbt = new CompoundTag("", [
			"Pos" => new ListTag("Pos", [
				new DoubleTag("", $x + 0.5),
				new DoubleTag("", $y),
				new DoubleTag("", $z + 0.5)
			]),
			"Motion" => new ListTag("Motion", [
				new DoubleTag("", 0),
				new DoubleTag("", 0),
				new DoubleTag("", 0)
			]),
			"Rotation" => new ListTag("Rotation", [
				new FloatTag("", lcg_value() * 360),
				new FloatTag("", 0)
			]),
		]);
		$custom_name = "Charpse Lv.".$lv;
		if(!is_null($custom_name)){
			$nbt->CustomName = new StringTag("CustomName", $custom_name);
		}
		$entity = new Charpse($level, $nbt);
		$entity->lv = $lv;
		$entity->setMaxHealth(15+($lv*1));
		$entity->setHealth(15+($lv*1));
		$entity->setSkin(Enemy::loadSkinData('splapse_girl'), 'Standard_CustomSlim');
		if($entity instanceof Entity){
			$entity->spawnToAll();
			return $entity;
		}
		echo "Not Entity";
		return false;
	}

	public function __construct(Level $level, CompoundTag $nbt){
		parent::__construct($level, $nbt);
		$this->cooltime = 0;
		$this->target = false;
		$this->charge = false;
		$item = ItemItem::get(261, 386);
		$this->getInventory()->setItemInHand($item);
	}

	public function onUpdate($tick){
		if(!isset($this->lv)){
			$this->lv = 0;
		}
		if(Enemy::getRate($this)){
			if($this->target === false || (!Enemy::canLook($this, $this->target) && $this->lv <= 7)){
				$this->target = Enemy::searchTarget($this);
			}
			if($this->charge){
				Enemy::chargerShot($this, 30);
				Enemy::setRate($this, 60-($this->lv*3));
				$this->charge = false;
				Enemy::walkFront($this, 0.15);
				$this->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_ACTION, true);
				parent::onUpdate($tick);
				return true;
			}
			if($this->target !== false && ($disq = $this->distanceSquared($this->target)) <= 900){
				if($this->lv >= 7 && $disq <= 100){
					Enemy::lookAt($this, $this->target);
					$this->pitch += ($this->pitch < 0)? (sqrt($disq+1)-6)/2 : 0;
					Enemy::splashBomb($this);
					Enemy::setRate($this, 60-($this->lv*2));
					$this->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_ACTION, false);
				}else{
					Enemy::lookAt($this, $this->target);
					Enemy::setRate($this, 13-($this->lv));
					$this->charge = true;
					Enemy::chargerRight($this, 30);
					Enemy::walkFront($this, 0.15, 180);
					$this->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_ACTION, true);
				}

			}else if($this->target && $disq <= 1000){
				Enemy::walkFront($this, 0.3);
			}else if($this->target){
				$this->yaw = mt_rand(0, 360);
				$this->target = Enemy::searchTarget($this);
			}else{
				Enemy::setRate($this, 60);
			}
		}
		parent::onUpdate($tick);
	}
}