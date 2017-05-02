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

use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\level\Location;
use pocketmine\level\Explosion;
use pocketmine\level\MovingObjectPosition;
use pocketmine\level\format\FullChunk;
use pocketmine\level\particle\DestroyBlockParticle;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\ByteTag;

use pocketmine\entity\Entity;
use pocketmine\entity\Effect;
use pocketmine\entity\CaveSpider;

use pocketmine\block\Block;

class Ambuffa extends CaveSpider{

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
		$custom_name = "Ambuffa Lv.".$lv;
		if(!is_null($custom_name)){
			$nbt->CustomName = new StringTag("CustomName", $custom_name);
		}
		$entity = new Ambuffa($level, $nbt);
		$entity->lv = $lv;
		$entity->setMaxHealth(15+($lv*1));
		$entity->setHealth(15+($lv*1));
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
		$this->ambush();
	}

	public function onUpdate($tick){
		if(!isset($this->lv)){
			$this->lv = 0;
		}
		if(Enemy::getRate($this)){
			if($this->target === false || (!Enemy::canLook($this, $this->target) && $this->lv <= 7)){
				$this->target = Enemy::searchTarget($this, 100);
			}
			if($this->target !== false && ($disq = $this->distanceSquared($this->target)) <= 100){
				if($this->charge){
					Enemy::lookAt($this, $this->target);
					Enemy::chargerShot($this, 10, 6, 3, 1.15, 0);
					Enemy::setRate($this, 2);
					Enemy::walkFront($this, 0.15);
				}else{
					$this->surprise();
					$this->charge = true;
				}
			}else if($this->target){
				$this->ambush();
				$this->target = false;
				$this->charge = false;
				Enemy::setRate($this, 20);
			}else{
				$level = Server::getInstance()->getDefaultLevel();
				if($level->getBlockDataAt($this->x, $this->y-1, $this->z) === 5 && !$this->charge){
					if($this->hasEffect(Effect::INVISIBILITY)){
						$this->removeEffect(Effect::INVISIBILITY);
					}
					$this->charge = true;
					Enemy::setRate($this, 100);
				}else if(!$this->hasEffect(Effect::INVISIBILITY)){
					$this->ambush();
					$this->charge = false;
					Enemy::setRate($this, 20);
				}
			}
		}
		parent::onUpdate($tick);
	}

	public function ambush(){
		Enemy::bomb($this, $this, $this->x, $this->y, $this->z, Block::get(35, 1), 3.5, 5, Server::getInstance()->getOnlinePlayers(), 7);
		if(!$this->hasEffect(Effect::INVISIBILITY)){
		//$this->spawnToAll();
			$this->addEffect(Effect::getEffect(Effect::INVISIBILITY)->setDuration(150000)->setAmplifier(0)->setVisible(false));//透明化
		}
		//$this->despawnFromAll();
	}

	public function surprise(){
		$level = Server::getInstance()->getDefaultLevel();
		$level->addParticle(new DestroyBlockParticle($this, Block::get(35, 1)));
		if($this->hasEffect(Effect::INVISIBILITY)){
			$this->removeEffect(Effect::INVISIBILITY);
		}
	}
}