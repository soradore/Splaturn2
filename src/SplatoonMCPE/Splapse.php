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

class Splapse extends Human{

	protected $gravity = 0.16;

	public function onUpdate($tick){
		if($this instanceof Human){
			if($this->attackingTick > 0){
				$this->attackingTick--;
			}
			if(!$this->isAlive() and $this->hasSpawned){
				++$this->deadTicks;
				if($this->deadTicks >= 20){
					$this->despawnFromAll();
				}
				return true;
			}
			if($this->isAlive()){

				$this->motionY -= $this->gravity;

				$this->move($this->motionX, $this->motionY, $this->motionZ);

				$friction = 1 - $this->drag;

				if($this->onGround and (abs($this->motionX) > 0.00001 or abs($this->motionZ) > 0.00001)){
					$friction = $this->getLevel()->getBlock($this->temporalVector->setComponents((int) floor($this->x), (int) floor($this->y - 1), (int) floor($this->z) - 1))->getFrictionFactor() * $friction;
				}

				$this->motionX *= $friction;
				$this->motionY *= 1 - $this->drag;
				$this->motionZ *= $friction;

				if($this->onGround){
					$this->motionY *= -0.5;
				}

				$this->updateMovement();
			}
		}
		parent::entityBaseTick();
		$grandParent = get_parent_class(get_parent_class($this));
		return $grandParent::onUpdate($tick);
	}

	public function getDrops(){
		$drops = [];
		return $drops;
	}
}