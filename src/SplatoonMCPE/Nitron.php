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
use pocketmine\entity\Creeper;

use pocketmine\block\Block;

class Nitron extends Creeper{

	public $width = 0.6;
	public $length = 0.6;
	public $height = 1.8;

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
		$custom_name = "Nitron Lv.".$lv;
		if(!is_null($custom_name)){
			$nbt->CustomName = new StringTag("CustomName", $custom_name);
		}
		$entity = new Nitron($level, $nbt);
		$entity->lv = $lv;
		$entity->setMaxHealth(15+($lv*2));
		$entity->setHealth(15+($lv*2));
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
			if($this->target === false || (!Enemy::canLook($this, $this->target) && $this->lv <= 7)){
				$this->target = Enemy::searchTarget($this);
			}
			if($this->target !== false && ($disq = $this->distanceSquared($this->target)) <= 290){
				if($this->lv >= 8 && $disq <= 45){
					if($this->charge >= 3){
						Enemy::bomb($this, $this, $this->x, $this->y, $this->z, Block::get(35, 1), 3.5, 45, Server::getInstance()->getOnlinePlayers(), 7);
						$this->charge = 0;
						Enemy::setRate($this, 50);
					}else{
						$level = Server::getInstance()->getDefaultLevel();
						$level->addParticle(new DestroyBlockParticle($this, Block::get(35, 1)));
						$this->charge++;
						Enemy::setRate($this, 4);
					}
				}else{
					$this->charge = 0;
					Enemy::lookAt($this, $this->target);
					$this->pitch += ($this->pitch < 0)? (sqrt($disq+1)-6)/2 : 0;
					Enemy::splashBomb($this);
					Enemy::setRate($this, 50-($this->lv*2));
					Enemy::walkFront($this, 0.4);
				}
			}else if($this->target && $disq <= 525){
				Enemy::lookAt($this, $this->target);
				Enemy::walkFront($this);
			}else if($this->target){
				$this->yaw = mt_rand(0, 360);
				Enemy::Octobrush($this, $this->y-1, 6);
				Enemy::setRate($this, 70-$this->lv);
				$this->target = Enemy::searchTarget($this);
				Enemy::walkFront($this, 0.4);
			}
		}else{
			$result = Enemy::walkFront($this, 0.25, 0, 0.55);
			if($result === 0){
				$this->yaw += mt_rand(0, 360);
			}else{				
				$this->yaw += mt_rand(-25, 25);
			}
		}
		parent::onUpdate($tick);
	}
}