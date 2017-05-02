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

use pocketmine\block\Block;

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

use pocketmine\level\particle\Particle;
use pocketmine\level\particle\BubbleParticle;
use pocketmine\level\particle\ItemBreakParticle;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\level\particle\InkParticle;
use pocketmine\level\particle\TerrainParticle;
use pocketmine\level\particle\DustParticle;
use pocketmine\level\particle\CriticalParticle;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\sound\LaunchSound;
use pocketmine\level\sound\ExplodeSound;
use pocketmine\level\sound\EndermanTeleportSound;
use pocketmine\level\sound\SplashSound;
use pocketmine\level\sound\AnvilFallSound;

use pocketmine\entity\Attribute;
use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\entity\Egg;
use pocketmine\entity\Creeper;
use pocketmine\entity\Skeleton;
use pocketmine\entity\PigZombie;
use pocketmine\entity\Ghast;
use pocketmine\entity\Human;
use pocketmine\entity\Projectile;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitEvent;

use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;

use pocketmine\network\protocol\AnimatePacket;

class Enemy{

	public static function killEnemyEvent($enemy, $killCount, $main){
		$level = Server::getInstance()->getDefaultLevel();
		Server::getInstance()->broadcastMessage("§a倒した数：".$killCount."体");
		if(isset($enemy->lastattack)){
			if(!isset($main->kc[$enemy->lastattack])){
				$main->kc[$enemy->lastattack]["kill"] = 0;
				$main->kc[$enemy->lastattack]["death"] = 0;
			}
			$main->kc[$enemy->lastattack]["kill"] += 1;
			$player = Server::getInstance()->getPlayer($enemy->lastattack);
			Server::getInstance()->broadcastMessage($player->getDisplayName()."§rが".$enemy->getNameTag()."§rを倒した！");
		}
		if($killCount == 100){
			foreach($level->getEntities() as $entity){
				if(!($entity instanceof Player)){
					$entity->close();
				}
			}
			$main->TimeTable();
			return true;
		}
		if($killCount%4 == 0){
		$level = Server::getInstance()->getDefaultLevel();
			$dif = ($killCount > 90)? 30 :floor($killCount/3);
			$r = floor($dif/2)+1;
			$mi = 4;
			switch($killCount){
				case 40:
					$pos = self::getRandomPos();
					Splatlon::summon($level, 128, 10, -197, 1);
					$pos1 = self::getRandomPos();
					Ambuffa::summon($level, $pos1[0], $pos1[1], $pos1[2], 8);
					$pos2 = self::getRandomPos();
					Ambuffa::summon($level, $pos2[0], $pos2[1], $pos2[2], 8);
					$pos3 = self::getRandomPos();
					Ambuffa::summon($level, $pos3[0], $pos3[1], $pos3[2], 8);
					$mi = 0;
				break;
				case 92:
					Splatlon::summon($level, 128, 10, -197, 15);
					$pos1 = self::getRandomPos();
					Brupse::summon($level, $pos1[0], $pos1[1], $pos1[2], 15);
					$pos2 = self::getRandomPos();
					Charpse::summon($level, $pos2[0], $pos2[1], $pos2[2], 15);
					$pos3 = self::getRandomPos();
					Swind::summon($level, $pos3[0], $pos3[1], $pos3[2], 15);
					$mi = 0;
				break;
				case 96:
				 $mi = 0;
				break;
			}
			for($i=0; $i < $mi; $i++){ 
				$pos = self::getRandomPos();
				$rr = mt_rand(1, $r);
				$lv = ($dif < 15)? mt_rand(0, floor($dif/3)) : mt_rand(floor($dif/3-1), 10);
				switch($rr){
					case 1:
					case 7:
					case 15://
						Nitron::summon($level, $pos[0], $pos[1], $pos[2], $lv);
					break;
					case 2:
					case 4:
					case 10:
					case 14://
						Charpse::summon($level, $pos[0], $pos[1], $pos[2], $lv);
					break;
					case 3:
					case 8:
					case 13://
						Brupse::summon($level, $pos[0], $pos[1], $pos[2], $lv);
					break;
					case 5:
					case 11:
						Ambuffa::summon($level, $pos[0], $pos[1], $pos[2], $lv);
					break;
					case 6:
					case 9:
					case 12://
					case 16://
						Swind::summon($level, $pos[0], $pos[1], $pos[2], $lv);
					break;
/*					case 12:
					case 13:
					case 14:
					case 15:
					case 16:
						Splatlon::summon($level, $pos[0], $pos[1], $pos[2], $lv);
					break;*/
					default:
						//Splatlon::summon($level, $pos[0], $pos[1], $pos[2], $lv);
					break;
				}
			}
			Server::getInstance()->broadcastMessage("§c再び敵が出現した！");
		}
	}

	public static function isEnemy($enemy){
		switch(true){
			case $enemy instanceof Creeper:
			case $enemy instanceof Skeleton:
			case ($enemy instanceof Human) && (!$enemy instanceof Player):
			case $enemy instanceof Ghast:
			case $enemy instanceof Swind:
			case $enemy instanceof Ambuffa:
				return true;
				break;
			
			default:
				return false;
				break;
		}
	}

	public static function getRandomPos(){
		$pos = [
		[149, 11, -207],
		[127, 15, -211],
		[107, 7, -203],
		[136, 15, -160],
		[151, 7, -177],
		[159, 7, -203],
		[99, 6, -226],
		[134, 10, -237],
		[98, 6, -236],
		[106, 9, -219],
		[138, 7, -214],
		[134, 7, -199]
		];
		shuffle($pos);
		return $pos[0];
	}

	/**
	 * Rateを取得
	 * @return bool
	 */
	public static function getRate($enemy){
		$now = microtime(true);
		return $enemy->cooltime <= $now;
	}

	/**
	 * クールタイムにする
	 */
	public static function setRate($enemy, $value){
		$now = microtime(true);
		$tick = $value;
		$enemy->cooltime = $now + $value / 20;
		return true;
	}

	//$entityにエイムを合わせる関数
	public static function lookAt($enemy, $target, $oversee = false){
		$x1 = $enemy->x;
		$y1 = $enemy->y;
		$z1 = $enemy->z;
		$x2 = $target->x;
		$y2 = $target->y;
		$z2 = $target->z;

		if(-$z2+$z1 == 0){
			return false;
		}

		$yaw = atan(($x2-$x1)/(-$z2+$z1))*180/M_PI;

		if((-$z2+$z1)/abs(-$z2+$z1) == 1){

			$yaw = $yaw+180;
		}

		$pitch = -1*atan(abs($y2-$y1)/sqrt(pow($x2-$x1,2)+pow($z2-$z1,2)))/(M_PI/180);

		if($y2-$y1 < 0){

			$pitch = -$pitch;

		}

		if(!$oversee && !Enemy::canLook($enemy, $target)){
			$yaw += mt_rand(-30, 30);
		}

		$enemy->yaw = $yaw;
		$enemy->pitch = $pitch;
	}

	public static function getFrontVector($enemy, $is3D = false, $yaw_p = 0){
		$yaw = $enemy->yaw+$yaw_p;
		$pitch = ($is3D)? $enemy->pitch : 0;
		$rad_y = $yaw/180*M_PI;
		$rad_p = ($pitch-180)/180*M_PI;
		return new Vector3(sin($rad_y)*cos($rad_p), sin($rad_p), -cos($rad_y)*cos($rad_p));
	}

	public static function walkFront($enemy, $vec = 0.25, $yawd = 0, $jump = 0.55){
		$rad = deg2rad($enemy->yaw+$yawd);
		$vx = -sin($rad);
		$vz = cos($rad);
		$walk = self::canWalk($enemy, $jump);
		if($walk){
			if($walk === 2){
				$enemy->motionY = $jump;
			}
			$enemy->motionX = $vx*$vec;
			$enemy->motionZ = $vz*$vec;	
		}
		$enemy->move($enemy->motionX, $enemy->motionY, $enemy->motionZ);
		return $walk;
	}

	/**
	 * 正面に歩けるかどうかチェック
	 * 0=>false
	 * 1=>true
	 * 2=>jump
	 */
	public static function canWalk($enemy, $jump){
		$level = Server::getInstance()->getDefaultLevel();
		$x = floor($enemy->x) + 0.5;
		$y = round($enemy->y);
		$z = floor($enemy->z) + 0.5;
		$dir = [0 => 270, 1 => 360, 2 => 90, 3 => 180];
		$yaw = $dir[$enemy->getDirection()];
		$Yaw_rad = deg2rad($yaw);
		$velX = -1 * sin($Yaw_rad);
		$velZ = cos($Yaw_rad);
		$x = floor($x + $velX);
		$z = floor($z + $velZ);
		if($level->getBlockIdAt($x, $y, $z) !== 0){
			if($jump && ($level->getBlockIdAt($x, $y+1, $z) === 0 || $level->getBlockIdAt($x, $y+1, $z) === 35)){
				return 2;
			}
			return 0;
		}
		return 1;
	}

	public static function SplashBomb($enemy){
		$level = Server::getInstance()->getDefaultLevel();
		$color = 1;
		$block = Block::get(35, $color);
		$enemys = Server::getInstance()->getOnlinePlayers();
		$yaw = $enemy->yaw;
		$pitch = $enemy->pitch;
		$rad_y = $yaw/180*M_PI;
		$rad_p = ($pitch-180)/180*M_PI;
		$ent = self::spawnEntity("PrimedTNT", $enemy->getLevel(), $enemy->x+sin($rad_y)*cos($rad_p)-0.5, $enemy->y+1.5+sin($rad_p), $enemy->z-cos($rad_y)*cos($rad_p)-0.5);
		$speed = 0.9;
		$ent->setMotion(new Vector3(sin($rad_y)*cos($rad_p)*$speed, sin($rad_p)*$speed, -cos($rad_y)*cos($rad_p)*$speed));
		$F = function($array){
			Enemy::bomb($array[5], $array[0], $array[5]->x, $array[5]->y, $array[5]->z, $array[1], $array[2], $array[3], $array[4], $array[6]);
				$array[5]->close();
		};

		Server::getInstance()->getScheduler()->scheduleDelayedTask(new LateDo(null, $F, [$enemy, $block, 3.3, 40, $enemys, $ent, 5]), 40);

		$F_2 = function($array){
			$particle = new DestroyBlockParticle(new Vector3($array[1]->x, $array[1]->y+1, $array[1]->z), $array[2]);
			$array[0]->addParticle($particle);
		};
				
		Server::getInstance()->getScheduler()->scheduleDelayedTask(new LateDo(null, $F_2, [$level, $ent, $block]), 30);
		Server::getInstance()->getScheduler()->scheduleDelayedTask(new LateDo(null, $F_2, [$level, $ent, $block]), 20);
		return true;
	}

	public static function spawnEntity($meta, $level, $x, $y, $z, $custom_name = null){
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

		if(!is_null($custom_name)){
			$nbt->CustomName = new StringTag("CustomName", $custom_name);
		}

		$entity = Entity::createEntity($meta, $level, $nbt);
		if($entity instanceof Entity){
			$entity->spawnToAll();
			return $entity;
		}
		echo "Not Entity";
		return false;
	}

	/*
	 * ボムなどの爆発
	 * @param Entity $entity
	 * @param $enemy
	 * @param double $x
	 * @param double $y
	 * @param double $z
	 * @param Block $block (wool)
	 * @param double $radius
	 * @param int $power
	 * @param array $array (攻撃対象となるプレイヤー名の配列)
	 *  @param int $paint (塗り範囲)
	 */
	public static function bomb($entity, $enemy, $x, $y, $z, $block, $radius, $power, $array, $paint){

		$level = Server::getInstance()->getDefaultLevel();
		$radius_1 = $radius/2; //球の半径
		//$radius_2 = $radius*1.25; //球の半径
		$radius_3 = $radius; //球の半径
		$color = 1;
		
		$F = function($array){

			$array[0]->addParticle($array[1]);
		};
		$x = round($x, 0, PHP_ROUND_HALF_DOWN);
		$y = round($y, 0, PHP_ROUND_HALF_DOWN);
		$z = round($z, 0, PHP_ROUND_HALF_DOWN);
		$p = new Vector3($x, $y, $z);
		$particle_1 = new DestroyBlockParticle($p, $block);
		$level->addParticle($particle_1);
		$level->addSound(new ExplodeSound($p));

		for($xxx = -floor($paint/2); $xxx < ceil($paint/2); $xxx++){

			for($yyy = -floor($paint/2); $yyy < ceil($paint/2); $yyy++){ 
			
				for($zzz = -floor($paint/2); $zzz < ceil($paint/2); $zzz++){ 
				
					//$pos = new Vector3(floor($xxx+$x), floor($yyy+$y), floor($zzz+$z));
					
					if($level->getBlockIdAt(round($xxx+$x, 0, PHP_ROUND_HALF_DOWN), round($yyy+$y, 0, PHP_ROUND_HALF_DOWN), round($zzz+$z, 0, PHP_ROUND_HALF_DOWN)) === 35){
						
						//塗り
						//$level->setBlock($pos, $block);
						//$pos_ar[] = [floor($xxx+$x), floor($yyy+$y), floor($zzz+$z)];
						$level->setBlockDataAt(floor($xxx+$x), floor($yyy+$y), floor($zzz+$z), $color);
					}
					if(abs($yyy) <= round($paint/2, 0, PHP_ROUND_HALF_DOWN) && (abs($xxx) == round($paint/2, 0, PHP_ROUND_HALF_DOWN) || abs($zzz) == round($paint/2, 0, PHP_ROUND_HALF_DOWN)) && $level->getBlockIdAt(round($xxx*2+$x, 0, PHP_ROUND_HALF_DOWN), round($yyy+$y, 0, PHP_ROUND_HALF_DOWN), round($zzz*2+$z, 0, PHP_ROUND_HALF_DOWN)) === 35){
						//$pos_ar[] = [round($xxx*2+$x, 0, PHP_ROUND_HALF_DOWN), round($yyy+$y, 0, PHP_ROUND_HALF_DOWN), round($zzz*2+$z, 0, PHP_ROUND_HALF_DOWN)];
						$level->setBlockDataAt(round($xxx*2+$x, 0, PHP_ROUND_HALF_DOWN), round($yyy+$y, 0, PHP_ROUND_HALF_DOWN), round($zzz*2+$z, 0, PHP_ROUND_HALF_DOWN), $color);
					}
				}
			}			
		}
		//$result = $this->changeWoolsColor($level, $pos_ar, $color, $user);

		for($yaw = 0; $yaw < 360; $yaw += 360/(2*M_PI*$radius)){

			for($pitch = 0; $pitch <360; $pitch += 360/(2*M_PI*$radius)){

				$rad_y = $yaw/180*M_PI;
				$rad_p = ($pitch-180)/180*M_PI;
				$xx = sin($rad_y)*cos($rad_p);
				$yy = sin($rad_p);
				$zz = -cos($rad_y)*cos($rad_p);
				$p->x = $x+$xx*$radius_1;
				$p->y = $y+$yy*$radius_1;
				$p->z = $z+$zz*$radius_1;
				$particle_1 = new TerrainParticle($p, $block);
				$level->addParticle($particle_1);

/*				$p->x = $x+$xx*$radius_2;
				$p->y = $y+$yy*$radius_2;
				$p->z = $z+$zz*$radius_2;
				$particle_2 = new TerrainParticle($p, $block);
				Server::getInstance()->getScheduler()->scheduleDelayedTask(new lateDo($this, $F, [$level, $particle_2]),2);*/
					
				$p->x = $x+$xx*$radius_3;
				$p->y = $y+$yy*$radius_3;
				$p->z = $z+$zz*$radius_3;
				$particle_3 = new TerrainParticle($p, $block);
				Server::getInstance()->getScheduler()->scheduleDelayedTask(new LateDo(null, $F, [$level, $particle_3]),3);
	
			}
		}

		foreach($array as $en){
			if((!$enemy instanceof Entity) or (!$en instanceof Entity)){
				continue;
			}
			$distance = sqrt(pow($x - $en->x, 2) + pow($y - $en->y, 2) + pow($z - $en->z, 2));

			if($entity != $en && $enemy != $en && $distance <= $radius + $radius * 2 / 3){
				if(self::canAttack($entity, $en)){
					$dmg = floor($power - $power * 2 / 3 * ($distance / ($radius + $radius * 2/ 3)));
					$en->attack($dmg, new EntityDamageByEntityEvent($enemy, $en, EntityDamageEvent::CAUSE_ENTITY_EXPLOSION, $dmg, 0.2));
				}
			}
		}
	}

	public static function canLook($enemy, $player){
		if(!$player->hasEffect(Effect::INVISIBILITY)){
			return true;
		}else{
			return false;
		}
	}

	 /*
	 * 遮蔽物があって攻撃が通らない場合にfalse、無い場合はtrueを返す
	 * @param Entity $ent1 攻撃者側
	 * @param Entity $ent2 被攻撃者側
	 */
	public static function canAttack($ent1, $ent2){

		$level = Server::getInstance()->getDefaultLevel();
		$x1 = $ent1->x-0.5;
		$y1 = $ent1->y+1.5;
		$z1 = $ent1->z-0.5;

		$x2 = $ent2->x-0.5;
		$y2 = $ent2->y+1.5;
		$z2 = $ent2->z-0.5;

		$y = max($y1, $y2);
		$maxdist = max(abs($x2-$x1), abs($z2-$z1));

		if($maxdist == 0 || pow($x2-$x1, 2)+pow($z2-$z1, 2) <= 1){
			
			return true;
		}

		$xdist = ($x2-$x1)/$maxdist;
		$zdist = ($z2-$z1)/$maxdist;
		
		for($times = 0; $times <= $maxdist; $times++){

			$bid = $level->getBlockIdAt(floor($x1+$xdist*$times), $y, floor($z1+$zdist*$times));
			if(!self::canThrough($bid)){
				
				return false;
				break;
			}
		}
		return true;
	}

	/**
	 * ロックオンする対象を探す
	 * 返り値 Player or bool
	 */
	public static function searchTarget($enemy, $disq = 800, $oversee = false){
		$x = $enemy->x;
		$y = $enemy->y;
		$z = $enemy->z;
		$target = false;
		$enemys = Server::getInstance()->getOnlinePlayers();
		foreach($enemys as $e){
			if(!$e instanceof Player || 
				Account::getInstance()->getData($e->getName())->getColor() !== 5 || 
				$e->getDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE) ||
				($e->hasEffect(Effect::INVISIBILITY) && !$oversee)
				){
				continue;
			}
			$distance_sq = pow($x - $e->x, 2) +  pow($y - $e->y, 2) + pow($z - $e->z, 2);

			if($distance_sq <= $disq){
				$target = $e;
				$disq = $distance_sq;
			}
		}
		return $target;
	}

	/**
	 * ブロックを攻撃が貫通できるかを返す
	 *
	 * @param int $blockId
	 */
	public static function canThrough($blockId){
		switch($blockId){
			case 0:
			case 8:
			case 9:
			case 10:
			case 11:
			case 50:
			case 52:
			case 101:
			case 208:
				return true;
			break;
			default:
				return false;
		}
	}

	/**
	 * チャージャー弾を発射
	 * @param Player  $player
	 * @param int     $range
	 */
	public static function chargerRight($enemy, $range){
		$yaw = $enemy->yaw;
		$pitch = $enemy->pitch;
		$yaw_rand = 0;
		$x = $enemy->x;
		$y = $enemy->y+1.5;
		$z = $enemy->z;
		$rad_y = ($yaw+$yaw_rand)/180*M_PI;
		$rad_p = ($pitch-180)/180*M_PI;
		$xx = sin($rad_y)*cos($rad_p);
		$yy = sin($rad_p);
		$zz = -cos($rad_y)*cos($rad_p);
		$level = Server::getInstance()->getDefaultLevel();
		$no_break = true;
		$r = 0;
		for($p = 0; $p <= $range; $p++){
			$sx = $x+$xx*$p;
			$sy = $y+$yy*$p;
			$sz = $z+$zz*$p;
			$bid = $level->getBlockIdAt(floor($sx), floor($sy), floor($sz));
			if(self::canThrough($bid)){
				$r = $p;
				$level->addParticle(new FlameParticle(new Vector3($sx, $sy, $sz)));
				/*if($p%2 == 0){
					
					$particle = new TerrainParticle(new Vector3($sx, $sy, $sz), $wool);

					$F = function () use ($level, $particle){
						$level->addParticle($particle);
					};

					Server::getInstance()->getScheduler()->scheduleDelayedTask(new LateDo($this, $F, []), $p/2);
				}*/
			}else{
				$r = $p;
				$no_break = false;
				break;
			}
		}
	}
	/**
	 * チャージャー弾を発射
	 * @param Player  $player
	 * @param int     $range
	 */
	public static function chargerShot($enemy, $range, $damage = 30, $yr = 0, $rr = 0.8, $type = 2){
		$fp = 1;
		$color = 1;
		$yaw = $enemy->yaw;
		$yaw_rand = mt_rand(-$yr, $yr);
		$pitch = $enemy->pitch;
		$x = $enemy->x;
		$y = $enemy->y+1.5;
		$z = $enemy->z;
		$rad_y = ($yaw+$yaw_rand)/180*M_PI;
		$rad_p = ($pitch-180)/180*M_PI;
		$xx = sin($rad_y)*cos($rad_p);
		$yy = sin($rad_p);
		$zz = -cos($rad_y)*cos($rad_p);
		$level = Server::getInstance()->getDefaultLevel();
		$wool = Block::get(35, $color);
		$no_break = true;
		$r = 0;
		for($p = 0; $p <= $range; $p++){
			$sx = $x+$xx*$p;
			$sy = $y+$yy*$p;
			$sz = $z+$zz*$p;
			$bid = $level->getBlockIdAt(floor($sx), floor($sy), floor($sz));
			if(self::canThrough($bid)){
				$r = $p;
				$level->addParticle(new TerrainParticle(new Vector3($sx, $sy, $sz), $wool));
				$level->addParticle(new CriticalParticle(new Vector3($sx, $sy, $sz), 1));
				/*if($p%2 == 0){
					
					$particle = new TerrainParticle(new Vector3($sx, $sy, $sz), $wool);

					$F = function () use ($level, $particle){
						$level->addParticle($particle);
					};

					Server::getInstance()->getScheduler()->scheduleDelayedTask(new LateDo($this, $F, []), $p/2);
				}*/
			}else{
				$r = $p;
				$no_break = false;
				break;
			}
		}
		self::orbitPaint(new Vector3(floor($x), floor($y), floor($z)), new Vector3(floor($x+$xx*$r), floor($y+$yy*$r), floor($z+$zz*$r)), 1, $level, $color, 2);
		self::endBullet(floor($x+$xx*$r), floor($y+$yy*$r), floor($z+$zz*$r), $level, $color);
		$members_all = Server::getInstance()->getOnlinePlayers();;
		foreach ($members_all as $player_v){
			if($player_v instanceof Player){
				$vx = $player_v->x;
				$vy = $player_v->y+1.5;
				$vz = $player_v->z;
				$dis = sqrt(pow($x-$vx,2)+pow($y-$vy,2)+pow($z-$vz,2));
				if($dis <= $r){
					if(sqrt(pow($x+$xx*$dis-$vx,2)+pow($y+$yy*$dis-$vy,2)+pow($z+$zz*$dis-$vz,2)) <= $rr){
						$knockback = 0;
						$player_v->attack($damage, new EntityDamageByEntityEvent($enemy, $player_v, EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK, $damage, $knockback));
					}
				}
			}
		}
		return true;
	}

	/**
	 * $startPosから$endPosまでの間を塗る
	 * @param  Vector3     $startPos
	 * @param  Vector3     $endPos
	 * @param  double      $paintedRate
	 * @param  Level       $level
	 * @param  int         $color
	 * @param  int         $c 
	 * @return int | false
	 */
	public static function orbitPaint($startPos, $endPos, $paintedRate, $level, $color, $type = 0){
		$per = 1;
		$distance = max(abs($endPos->x - $startPos->x), abs($endPos->z - $startPos->z));
		if($distance != 0){
			$x_dist = ($endPos->x - $startPos->x) / $distance;
			$z_dist = ($endPos->z - $startPos->z) / $distance;
			$y_high = max($endPos->y, $startPos->y - 2);
			$y_low  = min($endPos->y, $startPos->y - 2);
			//確実に一定数ブロックを塗るように変更
			$dis_ar = [];
			for($c = 0; $c <= $distance; $c++){
				$x = floor($startPos->x + $x_dist * $c);
				$z = floor($startPos->z + $z_dist * $c);
				for($height = floor($y_high + 1); $height >= $y_low - 7; $height--){
					if($level->getBlockIdAt($x, $height, $z) === 35){
						$dis_ar[] = [$x, $height, $z];
						if($type == 1){
							$dis_ar[] = [$x-1, $height, $z];
							$dis_ar[] = [$x+1, $height, $z];
							$dis_ar[] = [$x, $height, $z-1];
							$dis_ar[] = [$x, $height, $z+1];
						}else if($type == 2){
							if(mt_rand(0, 99) < pow($per, 2)*100){
								if(mt_rand(1, 2) != 1) $dis_ar[] = [$x-1, $height, $z];
								if(mt_rand(1, 2) != 1) $dis_ar[] = [$x+1, $height, $z];
								if(mt_rand(1, 2) != 1) $dis_ar[] = [$x, $height, $z-1];
								if(mt_rand(1, 2) != 1) $dis_ar[] = [$x, $height, $z+1];
							}
						}
						if($type !== 2){
							break;
						}
					}
				}
			}
			$block_cnt = count($dis_ar);
			// shuffle($dis_ar);
			self::mt_shuffle($dis_ar);
			$paintBlock_cnt = $block_cnt * $paintedRate;
			foreach($dis_ar as $pos){
				if($paintBlock_cnt-- > 0 && $level->getBlockIdAt($pos[0], $pos[1], $pos[2]) === 35){
					$level->setBlockDataAt($pos[0], $pos[1], $pos[2], $color);
				}
			}
		}
		return false;
	}

	public static function endBullet($x, $y, $z, $level, $color){
		for($xx = -1; $xx < 2; $xx++){ 
			for($yy = -1; $yy < 2; $yy++){ 
				for($zz = -1; $zz < 2; $zz++){ 
					if($level->getBlockIdAt($x+$xx, $y+$yy, $z+$zz) === 35){
						$level->setBlockDataAt($x+$xx, $y+$yy, $z+$zz, $color);
					}
				}
			}
		}
	}

	/**
	 * ホクサイ(振り下ろし)
	 * @param Player $enemy
	 * @param int    $y
	 * @param int    $power
	 */
	public static function Octobrush($enemy, $y, $power = 6){
		$color = 1;
		$level = Server::getInstance()->getDefaultLevel();

		$yaw = $enemy->yaw;
		$x = floor($enemy->x-sin(deg2rad($yaw))*2);
		$z = floor($enemy->z+cos(deg2rad($yaw))*2);
		//yaw1とyaw2とyaw3の値を変えると左右のインクの広がり方が変わります
		//Before = $yaw + 35 + rand(0, 8) - 4;
		$yaw1 = $yaw + 40 + rand(0, 8) - 4;
		$yaw2 = $yaw - 40 + rand(0, 8) - 4;
		$yaw3 = $yaw + rand(0, 8) - 4;
		$yaw4 = $yaw - rand(0, 8) - 4;

		$yaw1_rad = deg2rad($yaw1);
		$yaw2_rad = deg2rad($yaw2);
		$yaw3_rad = deg2rad($yaw3);
		$yaw4_rad = deg2rad($yaw4);
		$pitch = 0;
		$pitch_rad = deg2rad($pitch - 180);
		$pitch_cos = cos($pitch_rad);

		$xx1 = $x + (     (4 + rand(0, 2) - 1) * sin($yaw1_rad) * $pitch_cos);
		$xx2 = $x + (     (4 + rand(0, 2) - 1) * sin($yaw2_rad) * $pitch_cos);
		$xx3 = $x + (     (4 + rand(0, 2) - 1) * sin($yaw3_rad) * $pitch_cos);
		$xx4 = $x + (     (4 + rand(0, 2) - 1) * sin($yaw4_rad) * $pitch_cos);

		$zz1 = $z + (-1 * (4 + rand(0, 2) - 1) * cos($yaw1_rad) * $pitch_cos);
		$zz2 = $z + (-1 * (4 + rand(0, 2) - 1) * cos($yaw2_rad) * $pitch_cos);
		$zz3 = $z + (-1 * (4 + rand(0, 2) - 1) * cos($yaw3_rad) * $pitch_cos);
		$zz4 = $z + (-1 * (4 + rand(0, 2) - 1) * cos($yaw4_rad) * $pitch_cos);

		$pos_ar = [
			[$x,     $y, $z],
			[$x + 1, $y, $z],
			[$x - 1, $y, $z],
			[$x,     $y, $z + 1],
			[$x,     $y, $z - 1],

			[$xx1,     $y, $zz1],
			[$xx1 + 1, $y, $zz1],
			[$xx1 - 1, $y, $zz1],
			[$xx1,     $y, $zz1 + 1],
			[$xx1,     $y, $zz1 - 1],

			[$xx2,     $y, $zz2],
			[$xx2 + 1, $y, $zz2],
			[$xx2 - 1, $y, $zz2],
			[$xx2,     $y, $zz2 + 1],
			[$xx2,     $y, $zz2 - 1],

			[$xx3,     $y, $zz3],
			[$xx3 + 1, $y, $zz3],
			[$xx3 - 1, $y, $zz3],
			[$xx3,     $y, $zz3 + 1],
			[$xx3,     $y, $zz3 - 1],

			[$x,     $y+1, $z],
			[$x + 1, $y+1, $z],
			[$x - 1, $y+1, $z],
			[$x,     $y+1, $z + 1],
			[$x,     $y+1, $z - 1],

			[$xx1,     $y+1, $zz1],
			[$xx1 + 1, $y+1, $zz1],
			[$xx1 - 1, $y+1, $zz1],
			[$xx1,     $y+1, $zz1 + 1],
			[$xx1,     $y+1, $zz1 - 1],

			[$xx2,     $y+1, $zz2],
			[$xx2 + 1, $y+1, $zz2],
			[$xx2 - 1, $y+1, $zz2],
			[$xx2,     $y+1, $zz2 + 1],
			[$xx2,     $y+1, $zz2 - 1],

			[$xx3,     $y+1, $zz3],
			[$xx3 + 1, $y+1, $zz3],
			[$xx3 - 1, $y+1, $zz3],
			[$xx3,     $y+1, $zz3 + 1],
			[$xx3,     $y+1, $zz3 - 1],

			[$xx4,     $y+1, $zz4],
			[$xx4 + 1, $y+1, $zz4],
			[$xx4 - 1, $y+1, $zz4],
			[$xx4,     $y+1, $zz4 + 1],
			[$xx4,     $y+1, $zz4 - 1],
		];
		self::changeWoolsColor($level, $pos_ar, $color);
		$x_ar = [$x, $x + 1, $x - 1, $xx1, $xx1 + 1, $xx1 - 1, $xx2, $xx2 + 1, $xx2 - 1, $xx3, $xx3 + 1, $xx3 - 1, $xx4, $xx4 + 1, $xx4 - 1];
		$z_ar = [$z, $z + 1, $z - 1, $zz1, $zz1 + 1, $zz1 - 1, $zz2, $zz2 + 1, $zz2 - 1, $zz3, $zz3 + 1, $zz3 - 1, $zz4, $zz4 + 1, $zz4 - 1];
		self::Attack_range(new AxisAlignedBB(min($x_ar) - 0.5, $y - 0.5, min($z_ar) - 0.5, max($x_ar) + 0.5, $y + 3.3, max($z_ar) + 0.5), $power, $level, $enemy);
		$pk = new AnimatePacket();
		$pk->eid = $enemy->getId();
		$pk->action = 1;//ArmSwing
		Server::getInstance()->broadcastPacket($enemy->getViewers(), $pk);
		return true;
	}

	/**
	 * 羊毛の色をset
	 * @param  Level  $level
	 * @param  array  $pos_ar
	 * @param  int    $color_num
	 * @return int    ブロックの色を変えた数 | false
	 */
	public static function changeWoolsColor(Level $level = null, $pos_ar, $color_num){
		if(!$color_num){
			return false;
			$level = Server::getInstance()->getDefaultLevel();
		}

		if(!$level){
		}
		$amount = count($pos_ar);
		$cnt = 0;
		$blocks = [];
		foreach($pos_ar as $pos){
			if($level->getBlockIdAt(floor($pos[0]), floor($pos[1]), floor($pos[2])) === 35){
			//if($this->main->isWool($pos[0], $pos[1], $pos[2])){
				$level->setBlockDataAt(floor($pos[0]), floor($pos[1]), floor($pos[2]), $color_num);
				$blocks[] = [$pos[0], $pos[1], $pos[2]];
				$cnt++;
			}
		}
		return $cnt;
	}

	public static function Attack_range(AxisAlignedBB $aabb, $damage, Level $level, $enemy){
		$list =  $level->getNearbyEntities($aabb, self::isEnemy($enemy)? $enemy : null);
		foreach($list as $entity){
			if(self::isEnemy($enemy) && $entity instanceof Player){
				$ev = new EntityDamageByEntityEvent($enemy, $entity, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $damage, 0);
				$entity->attack($ev->getFinalDamage(), $ev);
			}
		}
		return true;
	}
	public static function mt_shuffle(array &$array){
		$array = array_values($array);
		for($i = count($array) - 1; $i > 0; --$i){
			//$j = mt_rand(0, $i);
			$j = random_int(0, $i);
			$tmp = $array[$i];
			$array[$i] = $array[$j];
			$array[$j] = $tmp;
		}
	}

	public static function canFloating($enemy, $hight){
		$level = Server::getInstance()->getDefaultLevel();
		$pos1 = new Vector3($enemy->x, $enemy->y-$hight+1, $enemy->z);
		$pos2 = new Vector3($enemy->x, $enemy->y-$hight-1, $enemy->z);
		var_dump($level->canBlockSeeSky($pos1));
		if($level->canBlockSeeSky($pos1)){
			if($level->canBlockSeeSky($pos2)){
				return -1;
			}else{
				return 0;
			}
		}else{
			return 1;
		}
	}

	public static function saveSkinData(Player $player){
		if($player instanceof Player){
			$path = __FILE__ ;
			$dir = dirname($path);
			$name = $player->getName();
			$fullPath = $dir.'/skins/'.$name.'.txt';
			$skinData = $player->getSkinData();
			$encode_skin = urlencode($skinData);
			file_put_contents($fullPath, $encode_skin);
			echo "Skin ID:".$player->getSkinId();
			return true;
		}
		return false;
	}

	public static function loadSkinData($skinName){
		$path = __FILE__ ;
		$dir = dirname($path);
		$fullPath = $dir.'/skins/'.$skinName.'.txt';
		$skinData = file_get_contents($fullPath);
		$decode_skin = urldecode($skinData);
		return $decode_skin;
	}
}