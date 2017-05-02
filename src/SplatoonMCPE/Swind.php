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
use pocketmine\network\protocol\AddEntityPacket;

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
use pocketmine\entity\FlyingAnimal;

use pocketmine\math\Vector3;

class Swind extends FlyingAnimal{
	const NETWORK_ID = 17;

	public $width = 0.95;
	public $length = 0.95;
	public $height = 0.95;

	public $highestY = 17;
	public $flySpeed = 0.4;
	
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
		$custom_name = "Swind Lv.".$lv;
		if(!is_null($custom_name)){
			$nbt->CustomName = new StringTag("CustomName", $custom_name);
		}
		$entity = new Swind($level, $nbt);
		$entity->lv = $lv;
		$entity->setMaxHealth(25+($lv*1));
		$entity->setHealth(25+($lv*1));
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
		$this->charge = 0;
	}

	public function onUpdate($tick){
		if(!isset($this->lv)){
			$this->lv = 0;
		}
		if(Enemy::getRate($this)){
			$this->flySpeed = 0.5;
			if($this->target === false || (!Enemy::canLook($this, $this->target) && $this->lv <= 7)){
				$this->target = Enemy::searchTarget($this);
			}
			if($this->target && ($disq = $this->distanceSquared($this->target)) <= 450){
				if($disq >= 85){
					Enemy::lookAt($this, $this->target);
					if($disq <= 350){
						Enemy::splashBomb($this);
					}
					$this->flyDirection = Enemy::getFrontVector($this, true, (($this->lv%2)*2-1)*75);
					$this->flySpeed = 0.75;
					Enemy::setRate($this, 30-($this->lv));
				}else{
					Enemy::lookAt($this, $this->target);
					$this->flyDirection = Enemy::getFrontVector($this, true);
					Enemy::chargerShot($this, 9, 6, 7, 1.35, 0);
					Enemy::setRate($this, 3);
					$this->flySpeed = 0.1;
				}

			}else if($this->target && $disq >= 800){
				$this->target = Enemy::searchTarget($this);
				//Enemy::walkFront($this, 0.4, 0, false);
				$this->flyDirection = Enemy::getFrontVector($this, true);
				Enemy::setRate($this, 30-($this->lv));
			}else if(!$this->target){
				$pitch = $this->pitch;
				$this->pitch = abs($this->pitch);
				Enemy::chargerShot($this, 9, 6, 7, 1.35, 0);
				$this->pitch = $pitch;
				$this->yaw += mt_rand(0, 360);
				$this->target = Enemy::searchTarget($this);
				$this->flyDirection = Enemy::getFrontVector($this, true);
				Enemy::setRate($this, 40);
			}else{
				Enemy::setRate($this, 60);
			}
		}
		parent::onUpdate($tick);
	}

	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->eid = $this->getId();
		$pk->type = Swind::NETWORK_ID;
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->speedX = $this->motionX;
		$pk->speedY = $this->motionY;
		$pk->speedZ = $this->motionZ;
		$pk->yaw = $this->yaw;
		$pk->pitch = $this->pitch;
		$pk->metadata = $this->dataProperties;
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}

	public function initEntity(){
		parent::initEntity();
		$this->setMaxHealth(35);
	}

	public function getName() : string{
		return "Swind";
	}
}