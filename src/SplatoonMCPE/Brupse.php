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
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\MobEquipmentPacket;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\Item as ItemItem;
use pocketmine\item\enchantment\Enchantment;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\ByteTag;

use pocketmine\entity\Entity;
use pocketmine\entity\Human;

class Brupse extends Splapse{

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
		$custom_name = "Brupse Lv.".$lv;
		if(!is_null($custom_name)){
			$nbt->CustomName = new StringTag("CustomName", $custom_name);
		}
		$entity = new Brupse($level, $nbt);
		$entity->lv = $lv;
		$entity->setMaxHealth(25+($lv*1));
		$entity->setHealth(25+($lv*1));
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
		$this->getInventory()->setItemInHand(ItemItem::get(369));
	}

	public function onUpdate($tick){
		if(!isset($this->lv)){
			$this->lv = 0;
		}
		if(Enemy::getRate($this)){
			if($this->target === false || (!Enemy::canLook($this, $this->target) && $this->lv <= 7)){
				$this->target = Enemy::searchTarget($this);
			}
			if($this->target !== false && ($disq = $this->distanceSquared($this->target)) <= 400){
				Enemy::lookAt($this, $this->target);
				Enemy::Octobrush($this, $this->y-1, 6);
				Enemy::setRate($this, 7);
				Enemy::walkFront($this, 0.2+(0.1*$this->lv), 0, 0.55);
			}else if($this->target !== false && $disq <= 725){
				Enemy::lookAt($this, $this->target);
				Enemy::splashBomb($this);
				Enemy::walkFront($this, 0.35);
				Enemy::setRate($this, 70);
			}else{
				Enemy::walkFront($this, 0.35, 0, 0.55);
				Enemy::Octobrush($this, $this->y-1, 6);
				Enemy::setRate($this, 7);
			}
		}else{
			$result = Enemy::walkFront($this, 0.35, 0, 0.55);
			if($result === 0){
				$this->yaw += mt_rand(0, 360);
			}else{				
				$this->yaw += mt_rand(-25, 25);
			}
			//Enemy::Octobrush($this, $this->y-1, 6);
			//Enemy::setRate($this, 7);
		}
		parent::onUpdate($tick);
	}
}