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
use pocketmine\entity\PrimedTNT;
use pocketmine\entity\Creeper;
use pocketmine\entity\Skeleton;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDespawnEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\entity\EntityExplodeEvent;

use pocketmine\event\entity\ProjectileHitEvent;

use pocketmine\event\inventory\InventoryOpenEvent;

use pocketmine\event\level\LevelLoadEvent;
use pocketmine\level\sound\SplashSound;

use pocketmine\event\player\PlayerBucketEmptyEvent;
use pocketmine\event\player\PlayerBucketFillEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\player\PlayerAnimationEvent;

use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\event\server\QueryRegenerateEvent;

use pocketmine\event\Listener;
use pocketmine\event\TranslationContainer;

use pocketmine\item\Item;

use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\level\particle\TerrainParticle;
use pocketmine\level\sound\LaunchSound;
use pocketmine\level\Location;
use pocketmine\level\Position;

use pocketmine\math\Math;
use pocketmine\math\Vector3;

use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ByteTag;

//use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\Info as ProtocolInfo;
use pocketmine\network\protocol\PlayerActionPacket;
//use pocketmine\network\protocol\SetEntityMotionPacket;

use pocketmine\utils\MainLogger;

use pocketmine\Player;
use pocketmine\Server;

class Event implements Listener{

	function __construct($main){
		$this->main = $main;
	}

	public function QueryRegenerateEvent(QueryRegenerateEvent $ev){
		$ev->setMaxPlayerCount(40);
	}

	public function onBlockBreak(BlockBreakEvent $event){
		$player = $event->getPlayer();
		if(!$player->isCreative()){
			$event->setCancelled(true);
			return false;
		}
		$block = $event->getBlock();
		if($block->getId() == 65 && $block->getDamage() == 0){
			$item = $event->getItem();
			if($item->getId() == 280){//stick
				return true;
			}else{
				$event->setCancelled(true);
				$player->sendTip("§e≫ 透明のはしごを破壊する場合は棒を持って行ってください");
				return false;
			}
		}
		return true;
	}

	public function onBlockPlace(BlockPlaceEvent $event){
		$player = $event->getPlayer();
		$user = $player->getName();
		$block = $event->getBlock();
		switch($block->getId()){
			case 46:
				$event->setCancelled(true);
				break;
			default:
				if(!$player->isOp() && !$this->main->canPaint($player)){
					$event->setCancelled(true);
				}
				break;
		}
	}

	public function onEntityDeath(EntityDeathEvent $event){
		$entity = $event->getEntity();
		if($this->main->dev == 2){
			if(Enemy::isEnemy($entity)){
				$color = 5;
				$level = Server::getInstance()->getDefaultLevel();
				$x = $entity->x;
				$y = $entity->y;
				$z = $entity->z;
				$paint = 7;//キル時の塗り範囲
				for($xx = -floor($paint/2); $xx < ceil($paint/2); $xx++){
					for($yy = -floor($paint/2); $yy < ceil($paint/2); $yy++){ 
						for($zz = -floor($paint/2); $zz < ceil($paint/2); $zz++){ 
							if($level->getBlockIdAt(floor($xx+$x), floor($yy+$y), floor($zz+$z)) === 35){
								//$pos_ar[] = [floor($xx+$x), floor($yy+$y), floor($zz+$z)];
								$level->setBlockDataAt(floor($xx+$x), floor($yy+$y), floor($zz+$z), $color);
							}
						}
					}			
				}
				$F = function($array){
					$array[0]->addParticle($array[1]);
				};
				$block = Block::get(35, $color);
				$radius = 3.3;
				$radius_1 = $radius/2; //球の半径
				//$radius_2 = $radius*1.25; //球の半径
				$radius_3 = $radius; //球の半径
				$p = new Vector3($x, $y, $z);
				$level->addSound(new SplashSound($p));
				for($yaw = 0; $yaw < 360; $yaw += 360/(M_PI*$radius)){

					for($pitch = 0; $pitch <360; $pitch += 360/(M_PI*$radius)){

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
						$p->x = $x+$xx*$radius_3;
						$p->y = $y+$yy*$radius_3;
						$p->z = $z+$zz*$radius_3;
						$particle_3 = new TerrainParticle($p, $block);
						Server::getInstance()->getScheduler()->scheduleDelayedTask(new LateDo($this, $F, [$level, $particle_3]),3);
	
					}
				}
				if(isset($this->main->killCount)){
					$this->main->killCount++;
					Enemy::killEnemyEvent($entity, $this->main->killCount, $this->main);
				}
				$entity->close();
			}
		}
	}

	public function onEntityDamage(EntityDamageEvent $event){
		if($event instanceof EntityDamageByEntityEvent){
			if(isset($event->getDamager()->player) && isset($event->getDamager()->ink)){
 				$canattack = $this->main->canAttack($event->getDamager()->player->getName(), $event->getEntity()->getName())['result'];
 				if($canattack){
					$event->getEntity()->attack(6, new EntityDamageByEntityEvent($event->getDamager()->player, $event->getEntity(), EntityDamageEvent::CAUSE_ENTITY_EXPLOSION, 6, 0));
					$event->setCancelled(true);
					return true;
				}else{
					$event->setCancelled(true);
				}
			}
		}
		if(Enemy::isEnemy($event->getEntity())){
			if($event->getCause() == EntityDamageEvent::CAUSE_FALL){
				$event->setCancelled(true);
				return false;
			}
			$ent = $event->getEntity();
			$ent->heal(0,new EntityRegainHealthEvent($ent,0,0));
			if(($event instanceof EntityDamageByEntityEvent) && ($event->getDamager() instanceof Player)){
				$ent->lastattack = $event->getDamager()->getName();
			}
			return true;
		}
		if(!($event->getEntity() instanceof Player)){
			$event->setCancelled(true);
			return false;
		}
		$s = $event->getEntity()->getName();//喰らった人
		if($this->main->team->getBattleTeamOf($s) && $this->main->checkFieldteleport() && $this->main->game !== 10){
			$event->setCancelled(true);
			return true;
		}
		switch($event->getCause()){
			case EntityDamageEvent::CAUSE_ENTITY_ATTACK:
			case EntityDamageEvent::CAUSE_ENTITY_EXPLOSION:
			case EntityDamageEvent::CAUSE_SUFFOCATION:
				/*if(isset($this->main->entityData[($entity = $event->getEntity())->getId()])){
					$entity->setNameTag($entity->getNameTag() + $event->getFinalDamage());
					$this->main->damageReset[$entity->getId()] = microtime(true);
					return true;
				}*/
				if(isset($this->main->Task['Respawn'][$s])){
					$event->setCancelled(true);
					return true;
				}
				/*
				if(isset($this->main->Squid_Standby[$damager])){
					$event->getDamager()->sendPopup("§b≫イカモードになっているので攻撃できません≪");
					$event->setCancelled(true);
					return false;
				}*/
				if($event instanceof EntityDamageByEntityEvent){
					if($event->getDamager() instanceof Player){
						$damager = $event->getDamager()->getName();//ダメージを与えた人
						$result = $this->main->canAttack($damager, $s);
						if(!$result['result']) $event->setCancelled(true);
						switch($result['reason']){
							case 1:
								return false;
							case 2:
								$event->getDamager()->sendPopup($this->main->lang->translateString("attackNotEnemy"));
								return false;
						}
						if($this->main->canAttack($event->getDamager()->getName(), $event->getEntity()->getName()) && $team = $this->main->team->getBattleTeamOf($s)){
							$field_data = $this->main->getBattleField($this->main->field);
							switch($field_data['name']){
								case 'イカ研究所-実験室A-':
									$range = 7;
									break;
								
								case 'ヤドカリ遺跡':
									$range = 24;
									break;

								case 'キダカ秘密基地':
									$range = 6;
									break;

								default:
									$range = 12;
									break;
							}
							$f = $field_data['start'][$team];
							$px = $event->getEntity()->x;
							$pz = $event->getEntity()->z;
							if(abs($px - $f[0]) < $range && abs($pz - $f[2]) < $range && $this->main->canPaint($event->getDamager())){
								$this->main->OnDeath($event->getEntity(), $event->getDamager(), null, EntityDamageEvent::CAUSE_FIRE);
								$event->setCancelled(true);
								return false;
							}
						}
						$check = function($ent1, $ent2){
							$level = Server::getInstance()->getDefaultLevel();
							$x1 = $ent1->x - 0.5;
							$y1 = $ent1->y+1.5;
							$z1 = $ent1->z - 0.5;

							$x2 = $ent2->x - 0.5;
							$y2 = $ent2->y+1.5;
							$z2 = $ent2->z - 0.5;

							$my = max($y1, $y2);

							$maxdist = max(abs($x2 - $x1), $my, abs($z2 - $z1));

							if($maxdist == 0 || sqrt(pow($x2 - $x1, 2) + pow($z2 - $z1, 2)) <= 1){

								return true;
							}

							$xdist = ($x2-$x1) / $maxdist;
							$zdist = ($z2-$z1) / $maxdist;

							for($times = 0; $times <= $maxdist; $times++){
								$bid = $level->getBlockIdAt(floor($x1 + $xdist * $times), floor($my), floor($z1 + $zdist * $times));
								if(!$this->main->w->canThrough($bid)){
									return false;
									break;
								}
							}

							return true;
						};



						if($check($event->getDamager(), $event->getEntity())){

							$weapon_num = Account::getInstance()->getData($damager)->getNowWeapon();
							switch($weapon_num){
								#ノックバックを無効化しないブキを追加する
								case Weapon::SPLATTERSHOT:
								case Weapon::SPLATTERSHOT_JR:
								case Weapon::SPLOOSH_O_MATIC:
								case Weapon::SPLATTERSHOT_PRO:
								case Weapon::GAL_96:
								case Weapon::GAL_52:
								case Weapon::DUAL_SQUELCHER:
								case Weapon::SPLASH_O_MATIC:
									break;

								default:
									//上で除外されなかったらノックバック無効化
									$event->setKnockBack(0);
							}
						}else{
							$event->setCancelled(true);
						}
						$power = Gadget::getCorrection($event->getDamager(), Gadget::POWER);
						$defence = Gadget::getCorrection($event->getEntity(), Gadget::DEFENCE);
						$dam = $power/$defence;
						if($event->getCause() == EntityDamageEvent::CAUSE_ENTITY_EXPLOSION){
							$dam /= Gadget::getCorrection($event->getEntity(), Gadget::BOMB_GUARD);
						}
						$event->setDamage(round($dam*$event->getFinalDamage()));
						if($event->getEntity()->getHealth()-$event->getFinalDamage() <= 0){
							if($event->getDamager() instanceof Player and $event->getEntity() instanceof Player){
								$dameger = $event->getDamager();
								$player = $event->getEntity();
								if($this->main->OnDeath($dameger, $player, "", $event->getCause())){
									$event->setCancelled(true);
								}
							}
						}
					}else if($event->getDamager() instanceof Entity){
						if($event->getEntity()->getHealth()-$event->getFinalDamage() <= 0){
							$dameger = $event->getDamager();
							$player = $event->getEntity();
							if($this->main->OnDeath($dameger, $player, "", $event->getCause())){
								$event->setCancelled(true);
							}
						}
					}
				}

				//無敵時間削除
				//if(!$event->isCancelled()){
					$ent = $event->getEntity();
					$ent->heal(0,new EntityRegainHealthEvent($ent,0,0));
				//}

				break;
			case EntityDamageEvent::CAUSE_FALL:
				//if($this->main->spawnedSquid($event->getEntity())){
					$event->setCancelled(true);
					return false;
				//}
				break;
		}
		if($event instanceof EntityDamageByEntityEvent){
			$event->setKnockBack(0);
		}
		return true;
	}

	public function onEntityDespawn(EntityDespawnEvent $event){
		if($event->getType() == 81){
			$entity = $event->getEntity();
			$player = $entity->shootingEntity;
			if($player instanceof Player){
				$user = $player->getName();
				if($this->main->team->getBattleTeamOf($user) and $this->main->isinPrepareBattle()){
					//$this->main->w->QuickBomb_hit($player, $entity);
				}
			}
		}
	}

	public function onEntityRegainHealth(EntityRegainHealthEvent $event){
		if(($player = $event->getEntity()) instanceof Player){
			$user = $player->getName();
			switch($event->getRegainReason()){
				case EntityRegainHealthEvent::CAUSE_SATURATION:
					if(($this->main->team->getBattleTeamOf($user) && $this->main->checkFieldteleport()) || isset($this->main->trypaintData['player'][$user])){
						//満腹度が一定値あるときに体力が自動で回復しないように
						$event->setCancelled(true);
					}
					break;
			}
		}
	}

	public function getStrictForce(Player $player){
		$pa = (array) $player;
		$startAction = $pa["\0*\0startAction"];
		$server = $pa["\0*\0server"];
		$diff = ($server->getTick() - $startAction);
		$p = $diff / 20;
		$f = (($p ** 2) + $p * 2) / 3 * 2;
		return $f;
	}

	public function onEntityShootBow(EntityShootBowEvent $event){
		if(($player = $event->getEntity()) instanceof Player){
			$user = $player->getName();
			$event->setCancelled(true);
			if($this->main->canPaint($player)){
				$playerData = Account::getInstance()->getData($user);
				$nw = $playerData->getNowWeapon();
				switch($nw){
					case Weapon::SPLAT_CHARGER:
					case Weapon::SPLAT_CHARGER_WAKAME:
					case Weapon::SPLAT_CHARGER_BENTO:
						$mf = 2;
						break;
					case Weapon::LITRE3K:
						$mf = 4;
						break;
					default:
						$mf = 2;
						break;
				}
				$force = ($this->getStrictForce($player) < $mf)? $this->getStrictForce($player) : $mf;
				$amount = $playerData->getInkConsumption()*(0.25+($force/$mf*0.75));
				if($playerData->canConsumeInk($amount)){
					/*if($event->getForce() < 2){
						$player->sendPopup($this->main->lang->translateString("chargeNotEnough"));
						return false;
					}*/
					if($playerData->getRate()){
						switch($nw){
							case Weapon::SPLAT_CHARGER:
							case Weapon::SPLAT_CHARGER_WAKAME:
							case Weapon::SPLAT_CHARGER_BENTO:
								$this->main->w->SplatCharger($player, $force);
								break;
							case Weapon::LITRE3K:
								$this->main->w->Litre3K($player, $force);
								break;
							case Weapon::CLASSIC_SQUIFFER:
								$this->main->w->ClassicSquiffer($player, $force);
								break;
							default:
								$this->main->w->SplatCharger($player, $force);
								break;
						}
						$player->getLevel()->addSound(new SplashSound($player, 100*($mf-$force)/$mf));
						$playerData->consumeInk($amount);
						$this->main->sendInkAmount($player);
					}else{
						$player->sendPopup("§bwait for charging");
						return false;
					}
				}else{
					$player->sendPopup($this->main->lang->translateString("inkShortage"));
					return false;
				}
			}
		}
	}

/*	public function onEntitySpawn(EntitySpawnEvent $event){
		$entity = $event->getEntity();
		if($entity::NETWORK_ID === PrimedTNT::NETWORK_ID){
			$entity->close();
		}
	}
*/
	public function onInventoryOpen(InventoryOpenEvent $event){
		$player = $event->getPlayer();
		$user = $player->getName();
		if($this->main->team->getBattleTeamOf($user)){
			switch($event->getInventory()->getType()->getDefaultTitle()){
				case "Chest":
				case "Double Chest":
				case "Furnace":
				case "Crafting":
				case "Enchant":
				case "Brewing":
				case "Anvil":
					$event->setCancelled(true);
					break;
			}
		}
	}

	public function onLevelLoad(LevelLoadEvent $event){
		$level = $event->getLevel();
		if($level->getName() === "splatt001"){
			Server::getInstance()->setDefaultLevel($level);
			$level->setTime(0);
			$level->stopTime();
		}
	}

	public function onBucketEmptyEvent(PlayerBucketEmptyEvent $event){
		$player = $event->getPlayer();
		$user = $player->getName();
		if(($this->main->team->getBattleTeamOf($user) && $this->main->checkFieldteleport()) || isset($this->main->trypaintData['player'][$user])){
			$event->setCancelled(true);
			return false;
		}
	}

	public function onBucketFillEvent(PlayerBucketFillEvent $event){
		$player = $event->getPlayer();
		$user = $player->getName();
		if(($this->main->team->getBattleTeamOf($user) && $this->main->checkFieldteleport()) || isset($this->main->trypaintData['player'][$user])){
			$event->setCancelled(true);
			return false;
		}
	}

	public function onPlayerChat(PlayerChatEvent $event){
		$player = $event->getPlayer();
		$user = $player->getName();
		if($this->main->mute){
			$player->sendMessage("§4現在このサーバー内でのチャットは禁止されています！！");
			$event->setCancelled(true);
		}else{
			$rec = $event->getRecipients();
			$event->setRecipients($this->main->getNonmutePlayers($rec,$user));
		}
		if($this->main->team->getBattleTeamOf($user) and $this->main->game == 10){
			$player->sendMessage("§4試合中のチャットは禁止です！");
			$event->setCancelled(true);
		}
		return true;//まだ書いただけなので無効化(05/07)
		$message = $event->getMessage();
		if(!isset($this->main->chatData[$user])){
			$this->main->chatData[$user] = ['message' => "", 'time' => 0, 'count' => 0];
		}
		$lastMessage = $this->main->chatData[$user]['message'];
		$lastTime = $this->main->chatData[$user]['time'];
		$spamCount = $this->main->chatData[$user]['count'];
		$timeDiff = microtime(true) - $lastTime;
		//$str_cnt = mb_strlen($message, "UTF-8");
		$limit = [
			'repeat' => 1.6,//前に発言した内容が同じである場合
			'cooldown' => 0.6,//次に発言ができるようになるまで
		];
		/*
		スパムとして判定するかどうかの基準
		$limit['cooldown']秒経過する前に投稿しようとした or 
		同じ内容を$limit['repeat']秒以内に投稿しようとした or
		Hack-Modのチャット内容と一致
		*/
		$detect_cnt = mb_substr_count($message, "spam", "UTF-8");
		if( $timeDiff <= $limit['cooldown'] || 
			($lastMessage === $message && $timeDiff <= $limit['repeat']) || 
			$detect_cnt >= 5){
			//★後でいろいろ調整してから有効化
			//$event->setCancelled(true);
			//$this->main->chatData[$user]['count'] += 1;
			//Todo: プレイヤーに規制されているとメッセージを送信(判定理由も記載)、回数によっては控えてとかって感じのメッセージも送るように。また判定された回数によって自動でぱにい警告処置(?)
			return false;
		}
		$this->main->chatData[$user] = ['message' => $message, 'time' => microtime(true), 'count' => ($spamCount > 0 ? $spamCount - mt_rand(0, 1) : 0)];
	}

	public function onPlayerDeath(PlayerDeathEvent $event){
		$player = $event->getEntity();
		$user = $player->getName();
		if($this->main->game == 10 and ($battle_team = $this->main->team->getBattleTeamOf($user))){
			$event->setKeepInventory(true);
			$this->main->AddscattersItem($player);
			$cause = $player->getLastDamageCause();
			if($cause instanceof EntityDamageByEntityEvent){
				$params = [
					$player->getDisplayName()
				];
				$damager = $cause->getDamager();
				if($damager instanceof Player){
					$damagerName = $damager->getName();
					$message = "death.attack.player";
					$params[] = $damager->getDisplayName()."(".$this->main->w->getWeaponName(Account::getInstance()->getData($damagerName)->getNowWeapon()).")";

					$event->setDeathMessage('');
					$players = Server::getInstance()->getOnlinePlayers();
					//試合に参加or観戦してる人にのみメッセージ表示するやつ
					foreach($players as $p){
						$name = $p->getName();
						if($this->main->team->getBattleTeamOf($name) or isset($this->main->view[$name]) or isset($this->main->cam[$name])){
							$p->sendMessage(new TranslationContainer($message, $params));
						}
					}
					
					$playerData = Account::getInstance()->getData($damagerName);//ここからキル時の塗り
					$color = $playerData->getColor();
					$level = $player->getLevel();
					$x = $player->x;
					$y = $player->y;
					$z = $player->z;
					$paint = 9;//キル時の塗り範囲
					$pos_ar = [];
					for($xx = -floor($paint/2); $xx < ceil($paint/2); $xx++){
						for($yy = -floor($paint/2); $yy < ceil($paint/2); $yy++){ 
							for($zz = -floor($paint/2); $zz < ceil($paint/2); $zz++){ 
								if($level->getBlockIdAt(floor($xx+$x), floor($yy+$y), floor($zz+$z)) === 35){
									$pos_ar[] = [floor($xx+$x), floor($yy+$y), floor($zz+$z)];
								}
							}
						}			
					}
					$this->main->w->changeWoolsColor($level, $pos_ar, $color, $damagerName, false);
				}
			}
			if($cause->getCause() !== EntityDamageEvent::CAUSE_SUICIDE){//自殺でなければ
/*				$playerData = Account::getInstance()->getData($user);
				$ink = $playerData->getInk();
				$tank = $playerData->getInkTank();
				$percentage = round($ink / $tank);
				if(!($percentage <= 0.1)){
					$color = $playerData->getColor();
					$level = $player->getLevel();
					$x = $player->x;
					$y = $player->y;
					$z = $player->z;
					$pos_ar = ($percentage >= 0.5) ? [
						[$x, $y, $z],
						[$x + 1, $y, $z],
						[$x + 1, $y + 1, $z],
						[$x + 1, $y - 1, $z],
						[$x + 1, $y + 2, $z],
						[$x + 1, $y - 2, $z],
						[$x + 1, $y + 1, $z + 1],
						[$x + 1, $y - 1, $z + 1],
						[$x + 1, $y + 2, $z + 1],
						[$x + 1, $y - 2, $z + 1],
						[$x + 1, $y + 1, $z - 1],
						[$x + 1, $y - 1, $z - 1],
						[$x + 1, $y + 2, $z - 1],
						[$x + 1, $y - 2, $z - 1],
						[$x + 1, $y + 1, $z + 2],
						[$x + 1, $y - 1, $z + 2],
						[$x + 1, $y + 2, $z + 2],
						[$x + 1, $y - 2, $z + 2],
						[$x + 1, $y + 1, $z - 2],
						[$x + 1, $y - 1, $z - 2],
						[$x + 1, $y + 2, $z - 2],
						[$x + 1, $y - 2, $z - 2],
						[$x + 1, $y, $z + 1],
						[$x + 1, $y, $z - 1],
						[$x + 1, $y, $z + 2],
						[$x + 1, $y, $z - 2],
						[$x - 1, $y, $z],
						[$x - 1, $y + 1, $z],
						[$x - 1, $y - 1, $z],
						[$x - 1, $y + 2, $z],
						[$x - 1, $y - 2, $z],
						[$x - 1, $y + 1, $z + 1],
						[$x - 1, $y - 1, $z + 1],
						[$x - 1, $y + 2, $z + 1],
						[$x - 1, $y - 2, $z + 1],
						[$x - 1, $y + 1, $z - 1],
						[$x - 1, $y - 1, $z - 1],
						[$x - 1, $y + 2, $z - 1],
						[$x - 1, $y - 2, $z - 1],
						[$x - 1, $y + 1, $z + 2],
						[$x - 1, $y - 1, $z + 2],
						[$x - 1, $y + 2, $z + 2],
						[$x - 1, $y - 2, $z + 2],
						[$x - 1, $y + 1, $z - 2],
						[$x - 1, $y - 1, $z - 2],
						[$x - 1, $y + 2, $z - 2],
						[$x - 1, $y - 2, $z - 2],
						[$x - 1, $y, $z + 1],
						[$x - 1, $y, $z - 1],
						[$x - 1, $y, $z + 2],
						[$x - 1, $y, $z - 2],
						[$x + 2, $y, $z],
						[$x + 2, $y + 1, $z],
						[$x + 2, $y - 1, $z],
						[$x + 2, $y + 2, $z],
						[$x + 2, $y - 2, $z],
						[$x + 2, $y + 1, $z + 1],
						[$x + 2, $y - 1, $z + 1],
						[$x + 2, $y + 2, $z + 1],
						[$x + 2, $y - 2, $z + 1],
						[$x + 2, $y + 1, $z - 1],
						[$x + 2, $y - 1, $z - 1],
						[$x + 2, $y + 2, $z - 1],
						[$x + 2, $y - 2, $z - 1],
						[$x + 2, $y, $z + 1],
						[$x + 2, $y, $z - 1],
						[$x + 2, $y, $z + 2],
						[$x + 2, $y, $z - 2],
						[$x - 2, $y, $z],
						[$x - 2, $y + 1, $z],
						[$x - 2, $y - 1, $z],
						[$x - 2, $y + 2, $z],
						[$x - 2, $y - 2, $z],
						[$x - 2, $y + 1, $z + 1],
						[$x - 2, $y - 1, $z + 1],
						[$x - 2, $y + 2, $z + 1],
						[$x - 2, $y - 2, $z + 1],
						[$x - 2, $y + 1, $z - 1],
						[$x - 2, $y - 1, $z - 1],
						[$x - 2, $y + 2, $z - 1],
						[$x - 2, $y - 2, $z - 1],
						[$x - 2, $y, $z + 1],
						[$x - 2, $y, $z - 1],
						[$x, $y + 1, $z],
						[$x, $y + 1, $z + 1],
						[$x, $y + 1, $z - 1],
						[$x, $y + 1, $z + 2],
						[$x, $y + 1, $z - 2],
						[$x, $y - 1, $z],
						[$x, $y - 1, $z + 1],
						[$x, $y - 1, $z - 1],
						[$x, $y - 1, $z + 2],
						[$x, $y - 1, $z - 2],
						[$x, $y + 2, $z],
						[$x, $y + 2, $z + 1],
						[$x, $y + 2, $z - 1],
						[$x, $y - 2, $z],
						[$x, $y - 2, $z + 1],
						[$x, $y - 2, $z - 1],
						[$x, $y, $z + 1],
						[$x, $y, $z - 1],
						[$x, $y, $z + 2],
						[$x, $y, $z - 2]
					] : [
						[$x, $y, $z],
						[$x + 1, $y, $z],
						[$x + 1, $y + 1, $z],
						[$x + 1, $y - 1, $z],
						[$x + 1, $y + 1, $z + 1],
						[$x + 1, $y - 1, $z + 1],
						[$x + 1, $y + 1, $z - 1],
						[$x + 1, $y - 1, $z - 1],
						[$x + 1, $y, $z + 1],
						[$x + 1, $y, $z - 1],
						[$x - 1, $y, $z],
						[$x - 1, $y + 1, $z],
						[$x - 1, $y - 1, $z],
						[$x - 1, $y + 1, $z + 1],
						[$x - 1, $y - 1, $z + 1],
						[$x - 1, $y + 1, $z - 1],
						[$x - 1, $y - 1, $z - 1],
						[$x - 1, $y, $z + 1],
						[$x - 1, $y, $z - 1],
						[$x, $y + 1, $z],
						[$x, $y + 1, $z + 1],
						[$x, $y + 1, $z - 1],
						[$x, $y - 1, $z],
						[$x, $y - 1, $z + 1],
						[$x, $y - 1, $z - 1],
						[$x, $y, $z + 1],
						[$x, $y, $z - 1]
					];
					$this->main->w->changeWoolsColor($level, $pos_ar, $color, $user, false);
				}*/
				if($player->getMaxHealth() < 30){
					$player->setMaxHealth($player->getMaxHealth() + 2);
				}
			}
		}else{
			$message = $event->getDeathMessage();
			$player->sendMessage($message);
			$message = $this->main->getServer()->getLanguage()->translate($message);
			MainLogger::getLogger()->info($message);
			$event->setDeathMessage("");
		}
		$event->setDrops([]);
		#Seat
		$this->main->seat->stand($player);
	}

	public function onPlayerDropItem(PlayerDropItemEvent $event){
		$event->setCancelled(true);
	}

	public function onPlayerExhaust(PlayerExhaustEvent $event){
		//満腹度が減少しないように
		$event->setCancelled(true);
	}

	public function onPlayerInteract(PlayerInteractEvent $event){
		$hand_id = $event->getItem()->getID();
		if($hand_id){
			$player = $event->getPlayer();
			$user = $player->getName();
			$block = $event->getBlock();
			$x = $event->getBlock()->x;
			$y = $event->getBlock()->y;
			$z = $event->getBlock()->z;
			$team_num = $this->main->team->getTeamOf($user);
			$color = $this->main->team->getTeamColorBlock($team_num);
			//Lobbyにいるかどうか
			$locationIsResp = !(($this->main->team->getBattleTeamOf($user) && $this->main->checkFieldteleport()) || isset($this->main->trypaintData['player'][$user]) || isset($this->main->reconData[$user]));
			switch($hand_id){
				case 46://TNT
					switch($event->getAction()){
						/*
						case 1://ブロック設置
							if($this->main->game == 10 and ($b_team = $this->main->team->getBattleTeamOf($user))){
								$team_num = $this->main->team->getTeamOf($user);
								$tank = $this->main->team->battleTeamMember[$b_team][$user][2];
								$amount = $tank * $this->main->getSubWeaponData(2)[2];
								if($this->main->canConsumeInk($b_team, $user, $amount)){
									$eid = Entity::$entityCount++;
									$this->main->tnt_data[$eid] = ['name' => $user, 'pos' => $block];
									$pk = new AddEntityPacket();
									$pk->eid = $eid;
									$pk->type = 65;
									$pk->x = $block->x;
									$pk->y = $block->y + 1.75;
									$pk->z = $block->z;
									$pk->speedY = 0.2;
									$pk->metadata = [];
									Server::broadcastPacket(Server::getInstance()->getOnlinePlayers(), $pk);
									$this->main->getServer()->getScheduler()->scheduleDelayedTask(new TNTExplode($this->main, $eid), 20*3);//4秒後に爆発処理を実行
									$player->getInventory()->removeItem(Item::get(46, 0, 1));//TNTを1個消費
									$this->main->setInk($user, $this->main->team->battleTeamMember[$b_team][$user][0] - $amount);
									$this->main->sendInkAmount($player);
									return true;
								}else{
									$player->sendPopup($this->main->lang->translateString("inkShortage"));
									return true;
								}
							}
							break;
						*/
						case 3://空中長押し
							if($this->main->game == 10 and ($b_team = $this->main->team->getBattleTeamOf($user))){
								$result = $this->main->w->SplashBom($player);
								if($result !== false){
									$this->main->sendInkAmount($player);
									return true;
								}else{
									$player->sendPopup($this->main->lang->translateString("inkShortage"));
									return true;
								}
							}
							break;
					}
					break;
				case 332://雪玉
					if($event->getAction() == 3){//雪玉投げてたら
						if($this->main->team->getBattleTeamOf($user)){
							if($this->main->game == 10){
								$result = $this->main->w->QuickBom_shoot($player);
								if($result !== false){
									//$this->main->sendInkAmount($player);
								}else{
									$event->setCancelled(true);
									$player->sendPopup($this->main->lang->translateString("inkShortage"));
								}
							}else{
								$event->setCancelled(true);//試合中でなかったらキャンセル
							}
						}
					}
					break;
				case 260://りんご
					$block_id = $block->getId();
					$player->sendMessage($block->x." ".$block->y." ".$block->z." ".$block_id);
					break;
				case 340://本
					$block_id = $block->getId();
					switch($block_id){
						/*case 12:
							Nitron::summon($player->getLevel(), $x, $y+2, $z);
						break;*/
						case 246://赤黒曜石
							$playerData = Account::getInstance()->getData($user);
							$give_pt = 500;
							$this->main->tpr($player);
							$playerData->grantPoint($give_pt);
							$player->sendMessage("§2クリアおめでとう！クリア記念§e".$give_pt."pt§2獲得！");
						break;
						case 153://ネザー鉱石
							$playerData = Account::getInstance()->getData($user);
							$give_pt = 250;
							$this->main->tpr($player);
							$playerData->grantPoint($give_pt);
							$player->sendMessage("§2クリアおめでとう！クリア記念§e".$give_pt."pt§2獲得！");
						break;

						case 19:
							$playerData = Account::getInstance()->getData($user);
							$price = 3000;
							$check_sec = 4;
							if(isset($playerData->buyWeaponCheck[0]) and time() - $playerData->buyWeaponCheck[0] <= $check_sec){//1度ブロックを押している、なおかつ4秒以内に押していたら処理するように
								if($playerData->minusPoint($price)){
									$gad = Gadget::resetAllGadget($player);
									$player->sendMessage("§2ガジェットを");
									$player->sendMessage("§eガジェット1§2:§a".$this->main->lang->translateString(Gadget::getGadgetName($gad[0])));
									$player->sendMessage("§eガジェット2§2:§a".$this->main->lang->translateString(Gadget::getGadgetName($gad[1])));
									$player->sendMessage("§eガジェット3§2:§a".$this->main->lang->translateString(Gadget::getGadgetName($gad[2])));
									$player->sendMessage("§2に変更しました");
									$player->sendMessage("§2代金として§e".$price."pt§2頂きました");
									$player->sendMessage("§2残り:§e".$playerData->getPoint()."pt");
									$this->main->itemselect->addFloatingTextParticle($player);
									$playerData->buyWeaponCheck = [];
									return true;
								}else{
									$nowpt = $playerData->getPoint();
									$pt = $price - $nowpt;
									$player->sendMessage($this->main->lang->translateString("weapon.buy.failure.pointShortage", [$pt]));
								}
								$playerData->buyWeaponCheck = [];
							}else{//1回目の処理
								$player->sendMessage("§e".$price."pt§2使ってガジェットを全て付け替えます");
								$player->sendMessage("§2付け替える場合はもう一度ブロックをタップしてください");								
								$playerData->buyWeaponCheck = [];
								$playerData->buyWeaponCheck[0] = time();
							}
						break;
						case 25:
							$playerData = Account::getInstance()->getData($user);
							$price = 5000;
							$check_sec = 4;
							if(isset($playerData->buyWeaponCheck[0]) and time() - $playerData->buyWeaponCheck[0] <= $check_sec){//1度ブロックを押している、なおかつ4秒以内に押していたら処理するように
								if($playerData->minusPoint($price)){
									$gad = Gadget::resetAllGadgetSpecial($player);
									$player->sendMessage("§2ガジェットを");
									$player->sendMessage("§eガジェット1§2:§a".$this->main->lang->translateString(Gadget::getGadgetName($gad[0])));
									$player->sendMessage("§eガジェット2§2:§a".$this->main->lang->translateString(Gadget::getGadgetName($gad[1])));
									$player->sendMessage("§eガジェット3§2:§a".$this->main->lang->translateString(Gadget::getGadgetName($gad[2])));
									$player->sendMessage("§2に変更しました");
									$player->sendMessage("§2代金として§e".$price."pt§2頂きました");
									$player->sendMessage("§2残り:§e".$playerData->getPoint()."pt");
									$this->main->itemselect->addFloatingTextParticle($player);
									$playerData->buyWeaponCheck = [];
									return true;
								}else{
									$nowpt = $playerData->getPoint();
									$pt = $price - $nowpt;
									$player->sendMessage($this->main->lang->translateString("weapon.buy.failure.pointShortage", [$pt]));
								}
								$playerData->buyWeaponCheck = [];
							}else{//1回目の処理
								$player->sendMessage("§e".$price."pt§2使ってガジェットを全て付け替えます");
								$player->sendMessage("§2付け替える場合はもう一度ブロックをタップしてください");								
								$playerData->buyWeaponCheck = [];
								$playerData->buyWeaponCheck[0] = time();
							}
						break;
						case 146:
							$playerData = Account::getInstance()->getData($user);
							$gad = Gadget::getGadgetsData($player);
							$newGad = Gadget::changeSaveGadget($player);
							$player->sendMessage("§e".$this->main->lang->translateString($this->main->w->getWeaponName($playerData->getNowWeapon()))."§2のガジェットを");
							$player->sendMessage("§eガジェット1§2:§a".$this->main->lang->translateString(Gadget::getGadgetName($gad[0]))." §2=> §a".$this->main->lang->translateString(Gadget::getGadgetName($newGad[0])));
							$player->sendMessage("§eガジェット2§2:§a".$this->main->lang->translateString(Gadget::getGadgetName($gad[1]))." §2=> §a".$this->main->lang->translateString(Gadget::getGadgetName($newGad[1])));
							$player->sendMessage("§eガジェット3§2:§a".$this->main->lang->translateString(Gadget::getGadgetName($gad[2]))." §2=> §a".$this->main->lang->translateString(Gadget::getGadgetName($newGad[2])));
							$player->sendMessage("§2に変更しました");
							$this->main->itemselect->addFloatingTextParticle($player);
							return true;
						break;
						case 120:
							$playerData = Account::getInstance()->getData($user);
							$sg = $playerData->getSaveGadget();
							$player->sendMessage("§2保管されているガジェット");
							$player->sendMessage("§eガジェット1§2:§a".$this->main->lang->translateString(Gadget::getGadgetName($sg[0])));
							$player->sendMessage("§eガジェット2§2:§a".$this->main->lang->translateString(Gadget::getGadgetName($sg[1])));
							$player->sendMessage("§eガジェット3§2:§a".$this->main->lang->translateString(Gadget::getGadgetName($sg[2])));
							return true;
						break;
						case 20:
						case 52:
							if($locationIsResp){
								$playerData = Account::getInstance()->getData($user);
								$weapons = $playerData->getWeapons($user);

								$before = $playerData->getNowWeapon();
								$w_num = $this->main->itemselect->selectWeapon($player, $block);
								if($w_num && $before != $w_num && ((!$this->main->team->getBattleTeamOf($user)) || $this->main->canChangeWeapon())){
									if(isset($weapons[$w_num])){
										$playerData->setNowWeapon($w_num);
										$weaponName = $this->main->w->getWeaponName($w_num);
										$msg = $this->main->lang->translateString("weapon.change", [$weaponName]);
										$player->sendPopup($msg);
										$this->main->itemselect->addFloatingTextParticle($player);
										$this->main->changeName($player);

										return true;
									}
								}
								
								$this->main->shop->selectWeapon($player,$block);

								/*foreach($this->main->w->getWeaponsDataAll() as $weapon_num => $data){
									if($data[4][2] === false) continue;
									if(	$x === $data['pos']['x'] &&
										$y === $data['pos']['y'] &&
										$z === $data['pos']['z']){
										if(isset($this->main->warn[$user])){
											if($this->main->warn[$user]['time'] + ($this->main->warn[$user]['count'] - 10) <= microtime(true)){
												$this->main->warn[$user]['time'] = microtime(true);
											}else{
												$out = $this->main->lang->translateString("regulated.block");
												$player->sendMessage($out);
												return true;
											}
										}else{
											$this->main->warn[$user]['count'] = 0;
											$this->main->warn[$user]['time'] = microtime(true);
										}
										$weaponName = $data[0];
										$check_sec = 4;
										if(isset($playerData->buyWeaponCheck[$weapon_num]) and time() - $playerData->buyWeaponCheck[$weapon_num] <= $check_sec){//1度ブロックを押している、なおかつ4秒以内に押していたら処理するように
											$result = $playerData->BuyWeapon($weapon_num);
											$playerData->buyWeaponCheck = [];
											if(!$result) $this->main->warn[$user]['count']++;
										}else{//1回目の処理
											if(!isset($weapons[$weapon_num])){
												$player->sendMessage($this->main->lang->translateString("weapon.buy.check", [$weaponName]));
												$playerData->buyWeaponCheck = [];
												$playerData->buyWeaponCheck[$weapon_num] = time();
											}else{
												if($before !== $weapon_num){
													$playerData->setNowWeapon($weapon_num);
													$weaponName = $this->main->w->getWeaponName($weapon_num);
													$msg = $this->main->lang->translateString("weapon.change", [$weaponName]);
													$player->sendPopup($msg);
													$this->main->itemselect->addFloatingTextParticle($player);
													$this->main->changeName($player);
												}
												$this->main->warn[$user]['count']++;
											}
										}
										return true;
									}
								}*/
							}
							break;
						case 57:
							if($locationIsResp){
								//チームから抜けるかを確認する処理
								$check_sec = 2;//連続で押したと判定する基準の時間
								if((isset($this->main->quitCheck[$user]) && microtime(true) <= $this->main->quitCheck[$user])){
									$this->main->entry->removeEntry($user);
									$this->main->setFloatText([0]);
									$out = "エントリーを解除した";
									$this->main->changeName($player);
									unset($this->main->quitCheck[$user]);
								}else{
									if($this->main->entry->isEntry($user) or $this->main->entry->isPreEntry($user)){
										$this->main->quitCheck[$user] = microtime(true) + $check_sec;
										$out = "本当にエントリーを解除しますか？";
									}else{
										$out = $this->main->lang->translateString("command.quit.failure");
									}
								}
							}else{
								$out = $this->main->lang->translateString("command.quit.failure");
							}
							$player->sendMessage($out);
							break;
						case 133:
							$out = "";
							/*if($this->main->dev == 2){

								//割り込みさせたいけど上手くいかない by moyasan
								
								$this->main->team->addMember(4, $user, true);
								$this->main->team->setBattleTeamMember();
								$this->main->team->setBattleTeam(2);
								$this->main->changeName($player);
								//$this->main->OnDeath(null, $player);
								$out = "途中参戦しました";
								$this->main->TpTeamBattleField(false);
								$this->main->giveWeapon($player, -1);
								$this->main->InkChargeStart(2);
								$player->sendMessage($out);
								Server::getInstance()->broadcastMessage("§b途中参戦者がいたため、全員をリスポーン地点に転送します");
								break;
							}*/
							if($locationIsResp){
								if(isset($this->main->warn[$user]['count'])){
									if($this->main->warn[$user]['time'] + ($this->main->warn[$user]['count'] - 3) * 0.75 <= microtime(true)){
										$this->main->warn[$user]['count'] ++;
										$this->main->warn[$user]['time'] = microtime(true);
									}else{
										$out = $this->main->lang->translateString("regulated.block");
										$player->sendMessage($out);
										return true;
									}
								}else{
									$this->main->warn[$user]['count'] = 0;
									$this->main->warn[$user]['time'] = microtime(true);
								}
								if($this->main->entry->canEntry($user)){
									$out = $this->main->entry->addEntry($user);
									$this->main->setFloatText([0]);
									$this->main->changeName($player);
								}else{
									$out = "エントリーできません";
								}
							}else{
								//$team_name = $this->main->team->getTeamName($team_num);
								//$out = "すでにエントリーしてるじゃなイカ";
							}
							$player->sendMessage($out);
							break;
						case 41:
							if($locationIsResp){
								$out = $this->main->getAccountStatus($user);
								$player->sendMessage($out);
							}
							break;
						case 152:
							if($locationIsResp){
								$this->main->TryPaint($player);
							}
							break;
						case 247:
							if($locationIsResp){
								$this->main->GameWatching($player);
							}
							break;
						case 77://石のボタン
						case 143://木のボタン
							if($locationIsResp){
								$this->main->itemselect->selectPage($player, $block);
								$this->main->shop->selectPage($player,$block);
							}
							break;
					}
					break;
				case 288://羽
					if(!$this->main->team->getBattleTeamOf($user) || !$this->main->checkFieldteleport()){
						if(!isset($this->main->trypaintData['player'][$user])){
							if(isset($this->main->view[$user])){
								$this->main->GameWatching($player, false, false);
							}
							if($event->getAction() === 3){//長押しかどうか
								$pos = new Vector3(507, 9, -79);
								$player->teleport($pos);
							}else{
								$this->main->tpr($player);
								$player->sendPopup("§a".$this->main->lang->translateString("tpr.respawn"));
							}
						}else{
							$this->main->tpr($player);
							$player->sendPopup("§a".$this->main->lang->translateString("tpr.respawn"));
						}
					}elseif($this->main->game == 10 && !isset($this->main->tprCheckData[($iuser = strtolower($user))]) && $player->getDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE) == false){
						$this->main->tprCheckData[$iuser] = microtime(true) + 2;
						$player->sendTip("§b".$this->main->lang->translateString("tpr.standby"));
						$player->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE, true);
						$player->sendData($player);
					}
					break;
				case 347://時計
					if(isset($this->main->trypaintData['player'][$player->getName()])){
						$data = $this->main->trypaintData['player'][$player->getName()];
						if($data[3][0] > 0){
							$check_sec = 2;
							if((microtime(true) - $data[3][2]) <= $check_sec){
								$this->main->TryPaint_FieldReset($data[1]);
								$data[3][0] -= 1;
								$player->sendPopup($this->main->lang->translateString("trypaint.fieldReset.success"));
								if(!$data[3][0]){
									$tag = new CompoundTag("", []);
									$tag->display = new CompoundTag("display", [
										"Name" => new StringTag("Name", $this->main->lang->translateString("itemName.fieldReset"))
									]);
									$player->getInventory()->removeItem(Item::get(347, 0, 1, $tag));
								}
							}else{
								$data[3][2] = microtime(true);
								$limit = $data[3][1];
								$now = $data[3][0];
								$player->sendPopup($this->main->lang->translateString("trypaint.fieldReset.confirm", [$now, $limit]));
							}
						}
						$this->main->trypaintData['player'][$player->getName()] = $data;
					}
					break;
				case 351://染料
					if(isset($this->main->trypaintData['player'][$player->getName()])){
						$meta = $event->getItem()->getDamage();
						if($meta !== 0){
							$player->getInventory()->clearAll();
							$this->main->giveWeapon($player, $meta+3);
							$item_watch = Item::get(347, 0, 1);
							$item_watch->setCustomName($this->main->lang->translateString("itemName.fieldReset"));
							$inventory = $player->getInventory();
							$inventory->addItem($item_watch);
						}
					}
					break;
			}
		}
		return true;
	}

	public function onPlayerItemConsume(PlayerItemConsumeEvent $event){
		$player = $event->getPlayer();
		$user = $player->getName();
		$item = $event->getItem();
		if(($this->main->team->getBattleTeamOf($user) && $this->main->checkFieldteleport()) || isset($this->main->trypaintData['player'][$user]) && $item->getId() === 325 && $item->getDamage() === 1){
			$event->setCancelled(true);
			return false;
		}
	}

	public function onPlayerItemHeld(PlayerItemHeldEvent $event){
		$player = $event->getPlayer();
		$user = $player->getName();
		if(isset($this->main->trypaintData['player'][$user]) || ($this->main->dev && $this->main->team->getBattleTeamOf($user))){
			$handItem = $event->getItem();
			$id = $handItem->getId();
			$damage = $handItem->getDamage();
			$weapon_num = $this->main->w->getWeaponNumFromItemID($id, $damage);
			$playerData = Account::getInstance()->getData($user);
			if($weapon_num != 0 && $playerData->getNowWeapon() !== $weapon_num){
				$this->main->w->applyEffect($player, $weapon_num, false);
				$weapon_data = $this->main->w->getWeaponData($weapon_num);
				$weapon_level = $playerData->getNowWeaponLevel($user);
				$rate = 0.002;
				$max_lv = 50;
				//$plus_tank = $weapon_level >= $max_lv ? $max_lv * $weapon_data[3] * $rate : $weapon_level * $weapon_data[3] * $rate;
				//$plus_tank = $plus_tank < 10 ? 0 : floor($plus_tank / 10) * 10;//10の位を切り下げる
				$plus_tank = 0;
				$ink = $playerData->getInk();
				$tank = $playerData->getInkTank();
				$exp = $ink / $tank;
				$newTank = $weapon_data[3] + $plus_tank;
				$newTankData = [$newTank * $exp, $newTank, $plus_tank];
				$playerData->setNowWeapon($weapon_num);
				$playerData->setData([
					'inkConsumption' => $weapon_data[2],
					'tank' => $newTankData,
					'rate' => 0,
				]);
			}
		}
	}

	public function onPlayerJoin(PlayerJoinEvent $event){
		$joinMessage = $event->getJoinMessage();
		$serverInstance = Server::getInstance();
		$serverInstance->broadcastPopup($serverInstance->getLanguage()->translateString($joinMessage->getText(), $joinMessage->getParameters()));
		$event->setJoinMessage("");

		$player = $event->getPlayer();
		$user = $player->getName();
		if(isset($player->notConnect)) $player->sendMessage("§4データベースへの接続に失敗したため、一時的に新規データが渡されました\n§4管理者へ報告お願いします");
		$field_data = $this->main->getBattleField($this->main->field);
		if(!($this->main->BattleResultAnimation instanceof BattleResultAnimation && isset($field_data['cam'][4]))){
			$player->setGamemode(Player::ADVENTURE);
		}

		$this->main->s->setOnlineStat($user, true);
		$this->main->s->setNow(count($serverInstance->getOnlinePlayers()));
		$battleTeam = $this->main->team->getBattleTeamOf($user);

		$player->sendMessage($this->main->lang->translateString("joinMessage.gameServer", [$this->main->s->getThisServerName()]));
		$this->main->FloatText(false, $player);
		$this->main->team->cancelLaterRemoveMember($user);
		/*$this->main->attribute[strtolower($user)] = new AttributeManager($player);
		$this->main->attribute[strtolower($user)]->init();*/
		$this->main->ResetStatus($player, !($battleTeam && $this->main->checkFieldteleport()));
		if($battleTeam && $this->main->checkFieldteleport()){
			if($this->main->game != 10){
				$player->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE, true);
				$player->sendData($player);
			}else{
				$player->setGamemode(Player::SURVIVAL);	
				$this->main->hideEnemysNametag($player,true);
			}
		}else{
			//$this->main->itemCase->set($player);
			$this->main->shop->sendPage($player);
			$this->main->itemselect->set($player);
		}
	}

	public function onPlayerKick(PlayerKickEvent $event){
		$player = $event->getPlayer();
		$user = $player->getName();
		//OPであれば満員でも入れるように
		if($this->main->s->hasOp($user) and $event->getReason() === 'disconnectionScreen.serverFull'){
			$event->setCancelled(true);
		}
	}

	public function onPlayerPreLogin(PlayerPreLoginEvent $event){
		$player = $event->getPlayer();
		$user = $player->getName();
		$punish = $this->main->s->hasPunished($user);
		if(!$punish['result']){
			$event->setKickMessage($punish['message']);
			$event->setCancelled(true);
			return false;
		}
		if($this->main->s->loginRestriction){
			$check = $this->main->s->checkLoginRestriction($user);
			if(!$check['result']){
				$event->setKickMessage($check['message']);
				$event->setCancelled(true);
				return false;
			}
		}
		if($this->main->op_only){
			if(!$player->isOp()){
				$event->setKickMessage("メンテナンス中...\nOP以外は入室出来ません");
				$event->setCancelled(true);
			}
		}
		$result = Account::getInstance()->loadData($player);
		if($result === false){
			$player->notConnect = true;
		}
		$playerData = Account::getInstance()->getData($user);
		if($playerData->getMaxWeaponLevel() < $this->main->needLv){
			$event->setKickMessage("このサーバーはイカした子しかできないんだよね～\nもっとやりこんでから来てよ キミにはまだ早い\nそうだね...どれかのブキをレベル".$this->main->needLv."にしてからまた来てよ");
			$event->setCancelled(true);
		}
	}

	public function onPlayerQuit(PlayerQuitEvent $event){
		$serverInstance = Server::getInstance();
		$player = $event->getPlayer();
		$user = $player->getName();
		$this->main->entry->removeEntry($user);
		$this->main->setFloatText([0]);
		if(isset($event) and $player->getName() != "" and $player->spawned !== false and $event->getQuitMessage() != ""){
			$quitMessage = $event->getQuitMessage();
			$serverInstance->broadcastPopup($serverInstance->getLanguage()->translateString($quitMessage->getText(), $quitMessage->getParameters()));
			$event->setQuitMessage("");
		}
		if($player->loggedIn){//ログイン失敗時などに処理をしないように(JoinEventが発生してないプレイヤーは処理しない)
			$this->main->team->removeMember($user, !($this->main->team->getBattleTeamOf($user) or $this->main->isinPrepareBattle()));
			if(isset($this->main->trypaintData['player'][$user])){
				$this->main->TryPaint($player, false, false);
			}
			$this->main->a->saveData($user, true, false);
			//if(($key = array_search($user, $this->main->s->TeleportedPlayers)) !== false) unset($this->main->s->TeleportedPlayers[$key]);
			$this->main->s->setNow(count($serverInstance->getOnlinePlayers()) - 1);
			$this->main->s->setOnlineStat($user, false);
			//$this->main->itemCase->remove($player, true);
			$this->main->shop->clear($player);
			$this->main->itemselect->remove($player);
			$this->main->seat->stand($player);
		}
		unset(
			$this->main->view[$user],
			$this->main->cam[$user],
			//$this->main->attribute[strtolower($user)],
			$this->main->quitCheck[$user]
		);
	}

	public function onPlayerRespawn(PlayerRespawnEvent $event){
		$player = $event->getPlayer();
		$user = $player->getName();
		$player->removeAllEffects();
		$playerData = Account::getInstance()->getData($user);
		//$player->usedChunks = [];
		if(isset($this->main->cam[$user])){
			$this->main->Cam_c($player, [], false, false);
		}
		if(isset($this->main->reconData[$user])){
			$this->main->Recon($player, 0, false, false);
		}
		if($this->main->game === 10){
			$this->main->ShowMikataStatus([$player]);
		}
		if($this->main->checkFieldteleport() and ($team = $this->main->team->getBattleTeamOf($user))){
			$field_data = $this->main->getBattleField($this->main->field);
			if($this->main->TPanimation instanceof TPanimation){
				$pos = $this->main->TPanimation->getPlayerTeleportPosition($player);
				$player->sendPosition($pos);
				$event->setRespawnPosition($pos);
			}elseif($this->main->BattleResultAnimation instanceof BattleResultAnimation && isset($field_data['cam'][4])){
				$player->setGamemode(Player::SPECTATOR);
				$pos = $field_data['cam'][4];
				$yaw = $pos[3] ?? null;
				$pitch = $pos[4] ?? null;
				$position = new Location($pos[0], $pos[1], $pos[2], $yaw, $pitch, $this->main->getLevelByBattleField($this->main->field));
				$event->setRespawnPosition($position);
				$player->sendPosition($position, $yaw, $pitch);
				$player->setRotation($yaw, $pitch);
				$player->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE, true);
				$player->sendData($player);
				$player->setAllowFlight(false);
			}else{
				$f = $field_data['start'][$team];
				$number = $this->main->team->battleTeamMember[$team][$user];
				$plus_pos = (isset($this->main->tweakPosition[$number])) ? $this->main->tweakPosition[$number] : [0, 0];
				$event->setRespawnPosition(new Location($f[0] + $plus_pos[0], $f[1], $f[2] + $plus_pos[1], $f[3], $f[4], $this->main->getLevelByBattleField($this->main->field)));
			}
			$playerData->fillInk($user);
			switch(true){
				case $this->main->dev:
					$giveType = 3;
					break;
				default:
					$giveType = 0;
			}
			if($this->main->dev == 2){
				$giveType = -1;
			}
			$this->main->giveWeapon($player, $giveType);
			$this->main->ResetStatus($player, false);
			if($this->main->game === 10){
				//移動不可に&インク回復開始
				$player->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE, true);
				$player->sendData($player);
				$this->main->Task['Respawn'][$user] = $this->main->getServer()->getScheduler()->scheduleRepeatingTask(new Respawn($this->main, $player), 1);
				unset($this->main->tprCheckData[strtolower($user)]);
				if($this->main->gamestop){
					$player->addEffect(Effect::getEffect(Effect::BLINDNESS)->setDuration(6000*20)->setAmplifier(0)->setVisible(false));
				}
			}
		}else{
			if(isset($this->main->trypaintData['player'][$user])){
				$try_num = $this->main->trypaintData['player'][$user][1];
				$field_data = $this->main->battle_field['try'][$try_num];
				$try_num = $this->main->trypaintData['player'][$user][1];
				$pos_ar = $field_data['start'];
				$event->setRespawnPosition(new Location($pos_ar[0], $pos_ar[1], $pos_ar[2], $pos_ar[3], $pos_ar[4], $player->getLevel()));
				$playerData->fillInk($user);
				$this->main->giveWeapon($player, 2);
				$this->main->ResetStatus($player, false);
				$this->main->sendInkAmount($player);
			}else{
				$this->main->delAllItem($player);
				$player->getInventory()->addItem(Item::get(340), Item::get(288));
				//$event->setRespawnPosition(new Position(-110, 13, 120, $player->getLevel()));
				$loc = new Location($this->main->lobbyPos[0], $this->main->lobbyPos[1], $this->main->lobbyPos[2], 180, 0, $player->getLevel());
				$event->setRespawnPosition($loc);
				$player->teleport($loc);
				$this->main->checkPermission($player);
			}
		}
		$this->main->changeName($player);

		/*//鯖移動したプレイヤーをデスポーンするコード(移動時にdespawnしても新しくきたプレイヤーには見えてしまうため)
		foreach($this->main->s->TeleportedPlayers as $key => $name){
			if(($player = $this->main->getServer()->getPlayer($name)) instanceof Player){
				$player->despawnFromAll();//プレイヤーだったらですぽん
			}else{
				unset($this->main->s->TeleportedPlayers[$key]);//プレイヤーオブジェクトじゃなかったらunset
			}
		}*/
	}

	/*
	public function onPlayerToggleSneak(PlayerToggleSneakEvent $event){
		$player = $event->getPlayer();
		$user = $player->getName();
		if($this->main->team->getBattleTeamOf($user) and $this->main->isinPrepareBattle()){
			if($event->isSneaking()){
				$this->main->Squid_Standby[$user] = true;
				//$this->main->getServer()->getScheduler()->scheduleDelayedTask(new Sneakoff($this->main, $player), 3);
				$player->setSneaking(false);
				$player->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_SNEAKING, false);
				$player->sendData($player);
			}else{
				if(isset($this->main->Squid_Standby[$user])) unset($this->main->Squid_Standby[$user]);
				//$event->setCancelled(true);
			}
		}
	}

	public function Sneakoff($player){
		$player->setSneaking(false);
		// $player->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_SNEAKING, false);
	}
	*/

	#Seat
	public function onPlayerToggleSneak(PlayerToggleSneakEvent $event){
		$player = $event->getPlayer();
		$user = $player->getName();
		if($player->pitch > 87 && $event->isSneaking()){
			if((!$this->main->team->getBattleTeamOf($user) || !$this->main->checkFieldteleport()) && !isset($this->main->view[$user])){
				$this->main->seat->seat($player);
			}
		}
	}

	public function onDataPacketReceive(DataPacketReceiveEvent $event){
		$packet = $event->getPacket();
		$player = $event->getPlayer();
		$user = $player->getName();
		switch($packet::NETWORK_ID){
			case ProtocolInfo::INTERACT_PACKET:
				if($player->spawned === false or !$player->isAlive() /*or $player->blocked*/ or $player->getGamemode() === Player::VIEW){
					break;
				}
				$target = $player->level->getEntity($packet->target);
				if($target instanceof Player and $target->isAlive()){
					$s = $target->getName();//喰らった人;
					$damager = $player->getName(); //そのダメージを与えた人
					if((!$this->main->team->getBattleTeamOf($damager) or !$this->main->checkFieldteleport()) && !$this->main->canAttack($user, $damager)['result']){
						$player->sendPopup($this->main->getAccountStatus($s, true));
						return true;
					}
					if($this->main->team->getBattleTeamOf($damager)){
						//物理攻撃なんてなかった
						$event->setCancelled(true);
						return false;
					}
				}
				break;
			//アイテム使用
			case ProtocolInfo::USE_ITEM_PACKET:
				$playerData = (Account::getInstance()->isLoaded($user)) ? Account::getInstance()->getData($user) : null;
				$hand_item = $player->getInventory()->getItemInHand();
				$hand_id = $hand_item->getID();
				$level = $player->getLevel();
				$w = ($playerData === null) ? null : $playerData->getNowWeapon();
				$x = $packet->x;
				$y = $packet->y;
				$z = $packet->z;
				if($packet->face === 0xff){
					#空中ながおしで動作するアイテム
					switch($hand_id){
						case 340://本
							$player->sendPopup("ブキの変更方法は変わりました。\n§aブキ変更パネル§f(§aWeapon equipment panel§f)で変えることができます");
							break;
						case 261://スプラチャージャー
							if($this->main->canPaint($player)){
								$amount = $playerData->getInkConsumption();
								if(!$playerData->canConsumeInk($amount)){
									$player->sendPopup($this->main->lang->translateString("inkShortage"));
									$event->setCancelled(true);
									return false;
								}
							}
							break;
						case 292://デュアルスイーパー
						case 267://ロングブラスター
							if(($this->main->team->getBattleTeamOf($user) && $this->main->checkFieldteleport()) || isset($this->main->trypaintData['player'][$user])){
								//WEditの処理が動かないように
								$event->setCancelled(true);
								return false;
							}
							break;
						case 346://スプラアンブレラ
							if($this->main->canPaint($player)){
								if($playerData->getRate()){
									$result = $this->main->w->SplatUmbrella($player, false);
									if($result !== false){
										$this->main->sendInkAmount($player);
									}else{
										$player->sendPopup($this->main->lang->translateString("inkShortage"));
									}
								}else{
									$player->sendPopup("§bwait for charging");
								}
							}
							break;
						case 264://スプラッシュボム
							if($this->main->canPaint($player)){
								if($playerData->getRate()){
									$result = $this->main->w->SplashBomb($player);
									if($result !== false){
										$this->main->sendInkAmount($player);
									}else{
										$player->sendPopup($this->main->lang->translateString("inkShortage"));
									}
								}else{
									$player->sendPopup("§bwait for charging");
								}
							}
							break;
						case 378://クイックボム
							if($this->main->canPaint($player)){
								if($playerData->getRate()){
									$result = $this->main->w->QuickBomb($player);
									if($result !== false){
										$this->main->sendInkAmount($player);
									}else{
										$player->sendPopup($this->main->lang->translateString("inkShortage"));
									}
								}else{
									$player->sendPopup("§bwait for charging");
								}
							}
							break;
						case 337://キューバンボム
							if($this->main->canPaint($player)){
								if($playerData->getRate()){
									$result = $this->main->w->SuckerBomb($player);
									if($result !== false){
										$this->main->sendInkAmount($player);
									}else{
										$player->sendPopup($this->main->lang->translateString("inkShortage"));
									}
								}else{
									$player->sendPopup("§bwait for charging");
								}
							}
							break;
						case 318://ノックボム
							if($this->main->canPaint($player)){
								if($playerData->getRate()){
									$result = $this->main->w->KnockBomb_shoot($player);
									if($result !== false){
										$this->main->sendInkAmount($player);
									}else{
										$player->sendPopup($this->main->lang->translateString("inkShortage"));
									}
								}else{
									$player->sendPopup("§bwait for charging");
								}
							}
							break;
						case 376://イカスミボール
							if($this->main->canPaint($player)){
								if($playerData->getRate()){
									$result = $this->main->w->InkBall_shoot($player);
									if($result !== false){
										$this->main->sendInkAmount($player);
									}else{
										$player->sendPopup($this->main->lang->translateString("inkShortage"));
									}
								}else{
									$player->sendPopup("§bwait for charging");
								}
							}
							break;
						case 265://ポイントセンサー
							if($this->main->canPaint($player)){
								if($playerData->getRate()){
									$result = $this->main->w->PointSensor_shoot($player);
									if($result !== false){
										$this->main->sendInkAmount($player);
									}else{
										$player->sendPopup($this->main->lang->translateString("inkShortage"));
									}
								}else{
									$player->sendPopup("§bwait for charging");
								}
							}
							break;
					}
				}else{
					#ブロックタップで動作するアイテム
					switch($hand_id){
						case 289://トラップ
							if($this->main->canPaint($player)){
								if($playerData->getRate()){
									if($level->getBlockIdAt($x, $y+1, $z) === 0 && $level->getBlockIdAt($x, $y, $z) === 35){
										$team_num = $this->main->team->getTeamOf($user);
										$team = array_map(array(Server::getInstance(), 'getPlayer'), $this->main->team->getTeamMember($team_num, true));
										$team = array_filter($team);
										if($team_num === 0) $team[] = $player;
										$result = $this->main->w->setTrap($player, $x, $y, $z, $team);
										if($result !== false){
											$this->main->sendInkAmount($player);
										}else{
											$player->sendPopup($this->main->lang->translateString("inkShortage"));
										}
									}else{
										$player->sendPopup($this->main->lang->translateString("cannotPlace"));
									}
								}else{
									$player->sendPopup("§bwait for charging");
								}
							}
							break;
						case 341://スプリンクラー
							if($x === 0 && $y === 0 && $z === 0){
								//投げ
								if($this->main->canPaint($player)){
									if($playerData->getRate()){
										$result = $this->main->w->sprinkler_shoot($player);
										if($result !== false){
											$this->main->sendInkAmount($player);
										}else{
											$player->sendPopup($this->main->lang->translateString("inkShortage"));
										}
									}else{
										$player->sendPopup("§bwait for charging");
									}
								}
							}else{
								//置き
								if($this->main->canPaint($player)){
									if($playerData->getRate()){
										//if($level->getBlockIdAt($x, $y, $z) === 35 && $level->getBlockIdAt($x, $y+1, $z) === 0){
										$place = $this->main->w->getPlaceSprinkler($level, $x, $y, $z, $packet->face);
										if($place !== false){
											$result = $this->main->w->spawnSprinkler($player, $x, $y, $z, $place, $packet->face);
											if($result !== false){
												$this->main->sendInkAmount($player);
											}else{
												$player->sendPopup($this->main->lang->translateString("inkShortage"));
											}
										}else{
											$player->sendPopup($this->main->lang->translateString("cannotPlace"));
										}
									}else{
										$player->sendPopup("§bwait for charging");
									}
								}
							}
							break;
						case 407:
							if($this->main->canPaint($player)){
								if($playerData->getRate()){
									$result = $this->main->w->spawnChaseBomb($player, $level, $player->x, $player->y, $player->z, ($x === 0 && $y === 0 && $z === 0));
									if($result !== false){
										$this->main->sendInkAmount($player);
									}else{
										$player->sendPopup($this->main->lang->translateString("inkShortage"));
									}
								}else{
									$player->sendPopup("§bwait for charging");
								}
							}
							break;
						case 264://スプラッシュボム
							if($this->main->canPaint($player)){
								if($playerData->getRate()){
									$result = $this->main->w->SplashBomb($player);
									if($result !== false){
										$this->main->sendInkAmount($player);
									}else{
										$player->sendPopup($this->main->lang->translateString("inkShortage"));
									}
								}else{
									$player->sendPopup("§bwait for charging");
								}
							}
							break;
						case 378://クイックボム
							if($this->main->canPaint($player)){
								if($playerData->getRate()){
									$result = $this->main->w->QuickBomb($player);
									if($result !== false){
										$this->main->sendInkAmount($player);
									}else{
										$player->sendPopup($this->main->lang->translateString("inkShortage"));
									}
								}else{
									$player->sendPopup("§bwait for charging");
								}
							}
							break;
						case 337://キューバンボム
							if($this->main->canPaint($player)){
								if($playerData->getRate()){
									$result = $this->main->w->SuckerBomb($player);
									if($result !== false){
										$this->main->sendInkAmount($player);
									}else{
										$player->sendPopup($this->main->lang->translateString("inkShortage"));
									}
								}else{
									$player->sendPopup("§bwait for charging");
								}
							}
							break;
						case 318://ノックボム
							if($this->main->canPaint($player)){
								if($playerData->getRate()){
									$result = $this->main->w->KnockBomb_shoot($player);
									if($result !== false){
										$this->main->sendInkAmount($player);
									}else{
										$player->sendPopup($this->main->lang->translateString("inkShortage"));
									}
								}else{
									$player->sendPopup("§bwait for charging");
								}
							}
							break;
						case 376://イカスミボール
							if($this->main->canPaint($player)){
								if($playerData->getRate()){
									$result = $this->main->w->InkBall_shoot($player);
									if($result !== false){
										$this->main->sendInkAmount($player);
									}else{
										$player->sendPopup($this->main->lang->translateString("inkShortage"));
									}
								}else{
									$player->sendPopup("§bwait for charging");
								}
							}
							break;
						case 265://ポイントセンサー
							if($this->main->canPaint($player)){
								if($playerData->getRate()){
									$result = $this->main->w->PointSensor_shoot($player);
									if($result !== false){
										$this->main->sendInkAmount($player);
									}else{
										$player->sendPopup($this->main->lang->translateString("inkShortage"));
									}
								}else{
									$player->sendPopup("§bwait for charging");
								}
							}
							break;
						case 388://アシッドボール
							if($this->main->canPaint($player)){
								if($playerData->getRate()){
									$result = $this->main->w->AcidBall_shoot($player);
									if($result !== false){
										$this->main->sendInkAmount($player);
									}else{
										$player->sendPopup($this->main->lang->translateString("inkShortage"));
									}
								}else{
									$player->sendPopup("§bwait for charging");
								}
							}
							break;
						case 369://ホクサイ
							if($this->main->canPaint($player)){
								if($playerData->getRate()){
									$result = $this->main->w->Octobrush_T($player, $y);
									if($result !== false){
										$playerData->consumeInk($playerData->getInkConsumption());
										$this->main->sendInkAmount($player);
										$level->addSound(new SplashSound($player));
									}else{
										$player->sendPopup($this->main->lang->translateString("inkShortage"));
									}
								}else{
									$player->sendPopup("§bwait for charging");
								}
							}
							break;
						case 352://ウィレム
							if($this->main->canPaint($player)){
								if($playerData->getRate()){
									$result = $this->main->w->Willem_T($player, $y);
									if($result !== false){
										$playerData->consumeInk($playerData->getInkConsumption());
										$this->main->sendInkAmount($player);
										$level->addSound(new SplashSound($player));
									}else{
										$player->sendPopup($this->main->lang->translateString("inkShortage"));
									}
								}else{
									$player->sendPopup("§bwait for charging");
								}
							}
							break;
						case 280://パブロ(タップ)
							if($this->main->canPaint($player)){
								$result = $this->main->w->Pablo_T($player, $player->x, $y, $player->z);
								if($result !== false){
									$playerData->consumeInk($playerData->getInkConsumption());
									$this->main->sendInkAmount($player);
									$level->addSound(new SplashSound($player));
								}else{
									$player->sendPopup($this->main->lang->translateString("inkShortage"));
								}
							}
							break;
						case 270://スプラローラー
							if($this->main->canPaint($player)){
								if($playerData->getRate()){
									$result = $this->main->w->SplatRoller_T($player, $y);
									if($result !== false){
										$playerData->consumeInk($playerData->getInkConsumption());
										$this->main->sendInkAmount($player);
										$level->addSound(new SplashSound($player));
									}else{
										$player->sendPopup($this->main->lang->translateString("inkShortage"));
									}
								}else{
									$player->sendPopup("§bwait for charging");
								}
							}
							break;
						case 274://ダイナモローラー
							if($this->main->canPaint($player)){
								if($playerData->getRate()){
									$result = $this->main->w->DynamoRoller_T($player, $x, $y, $z);
									if($result !== false){
										$playerData->consumeInk($playerData->getInkConsumption());
										$this->main->sendInkAmount($player);
										$level->addSound(new SplashSound($player));
									}else{
										$player->sendPopup($this->main->lang->translateString("inkShortage"));
									}
								}else{
									$player->sendPopup("§bwait for charging");
								}
							}
							break;
						case 346://スプラアンブレラ
							if($this->main->canPaint($player)){
								if($playerData->getRate()){
									$result = $this->main->w->SplatUmbrella($player, true, $x, $y, $z);
									if($result !== false){
										$playerData->consumeInk($playerData->getInkConsumption());
										$this->main->sendInkAmount($player);
									}else{
										$player->sendPopup($this->main->lang->translateString("inkShortage"));
									}
								}else{
									$player->sendPopup("§bwait for charging");
								}
							}
							break;
						case 325://バケットスロッシャー
							if($this->main->canPaint($player)){
								switch($hand_item->getDamage()){
									case 0://バケットスロッシャー
										if($playerData->getRate()){
											$result = $this->main->w->Slosher($player, $x, $y, $z);
											if($result !== false){
												$playerData->consumeInk($playerData->getInkConsumption());
												$this->main->sendInkAmount($player);
												$level->addSound(new SplashSound($player));
											}else{
												$player->sendPopup($this->main->lang->translateString("inkShortage"));
											}
										}else{
											$player->sendPopup("§bwait for charging");
										}
										break;
									case 1://ヒッセン
										if($playerData->getRate()){
											$result = $this->main->w->BrushWasher($player, $x, $y, $z);
											if($result !== false){
												$playerData->consumeInk($playerData->getInkConsumption());
												$this->main->sendInkAmount($player);
												$level->addSound(new SplashSound($player));
											}else{
												$player->sendPopup($this->main->lang->translateString("inkShortage"));
											}
										}else{
											$player->sendPopup("§bwait for charging");
										}
										break;
									case 8://スクリュースロッシャー
										//Todo 実装
										break;
								}
							}
							break;
						case 281://スプラドル
							if($this->main->canPaint($player)){
								if($playerData->getRate()){
									$result = $this->main->w->SplatLadle($player, $x, $y, $z);
									if($result !== false){
										$playerData->consumeInk($playerData->getInkConsumption());
										$this->main->sendInkAmount($player);
										$level->addSound(new SplashSound($player));
									}else{
										$player->sendPopup($this->main->lang->translateString("inkShortage"));
									}
								}else{
									$player->sendPopup("§bwait for charging");
								}
							}
							break;
						case 258://バレルスピナー
							if($this->main->canPaint($player)){
								if($playerData->getRate()){
									$result = $this->main->w->HeavySplatling_Charge($player);
									if($result !== false){
										//$this->main->sendInkAmount($player);
									}else{
										$player->sendPopup($this->main->lang->translateString("inkShortage"));
									}
								}/*else{
									$player->sendPopup("§bwait for charging");
								}*/
							}
							break;
						case 275://スプラスピナー
							if($this->main->canPaint($player)){
								if($playerData->getRate()){
									$result = $this->main->w->MiniSplatling_Charge($player);
									if($result !== false){
										//$this->main->sendInkAmount($player);
									}else{
										$player->sendPopup($this->main->lang->translateString("inkShortage"));
									}
								}/*else{
									$player->sendPopup("§bwait for charging");
								}*/
							}
							break;
						case 286://ハイドラント
							if($this->main->canPaint($player)){
								if($playerData->getRate()){
									$result = $this->main->w->HydraSplatling_Charge($player);
									if($result !== false){
										//$this->main->sendInkAmount($player);
									}else{
										$player->sendPopup($this->main->lang->translateString("inkShortage"));
									}
								}/*else{
									$player->sendPopup("§bwait for charging");
								}*/
							}
							break;
						//鍬とかシャベルが土を耕したりする処理をしないように
						case 256://シャープマーカー
						case 269://ボールドマーカー
						case 273://L3リールガン
						case 277://.52ガロン
						case 284://.96ガロン
						case 290://わかばシューター
						case 291://スプラシューター
						case 292://デュアルスイーパー
						case 293://プロモデラーPG
						case 294://プライムシューター
						case 267://ロングブラスター
							if(($this->main->team->getBattleTeamOf($user) && $this->main->checkFieldteleport()) || isset($this->main->trypaintData['player'][$user])){
								$event->setCancelled(true);
							}
							break;
					}
				}
				break;
			case ProtocolInfo::PLAYER_ACTION_PACKET:
				switch($packet->action){
					case PlayerActionPacket::ACTION_START_BREAK:
						$playerData = (Account::getInstance()->isLoaded($user)) ? Account::getInstance()->getData($user) : null;
						$hand_id = $player->getInventory()->getItemInHand()->getID();
						$level = $player->getLevel();
						$w = ($playerData === null) ? null : $playerData->getNowWeapon();
						$x = $packet->x;
						$y = $packet->y;
						$z = $packet->z;
						#ブロックを破壊しようとしたら動作するアイテム
						switch($hand_id){
							case 270://スプラローラー
								if($this->main->canPaint($player)){
									$result = $this->main->w->SplatRoller($player, $x, $y, $z);
									if($result !== false){
										$playerData->consumeInk(floor($playerData->getInkConsumption()/3));
										$this->main->sendInkAmount($player);
									}else{
										$player->sendPopup($this->main->lang->translateString("inkShortage"));
									}
								}
								break;
							case 274://ダイナモローラー
								if($this->main->canPaint($player)){
									$result = $this->main->w->DynamoRoller($player, $x, $y, $z);
									if($result !== false){
										$playerData->consumeInk(floor($playerData->getInkConsumption()/3));
										$this->main->sendInkAmount($player);
									}else{
										$player->sendPopup($this->main->lang->translateString("inkShortage"));
									}
								}
								break;
							case 280://パブロ
								if($this->main->canPaint($player)){
									$result = $this->main->w->Pablo($player, $x, $y, $z);
									if($result !== false){
										$playerData->consumeInk(floor($playerData->getInkConsumption()/3));
										$this->main->sendInkAmount($player);
									}else{
										$player->sendPopup($this->main->lang->translateString("inkShortage"));
									}
								}
								break;
							case 369://ホクサイ
								if($this->main->canPaint($player)){
									$result = $this->main->w->Octobrush($player, $x, $y, $z);
									if($result !== false){
										$playerData->consumeInk(floor($playerData->getInkConsumption()/3));
										$this->main->sendInkAmount($player);
									}else{
										$player->sendPopup($this->main->lang->translateString("inkShortage"));
									}
								}
								break;
							case 352://ウィレム
								if($this->main->canPaint($player)){
									$result = $this->main->w->Willem($player, $x, $y, $z);
									if($result !== false){
										$playerData->consumeInk(floor($playerData->getInkConsumption()/3));
										$this->main->sendInkAmount($player);
									}else{
										$player->sendPopup($this->main->lang->translateString("inkShortage"));
									}
								}
								break;
							case 292://デュアルスイーパー
							case 267://ロングブラスター
								if($this->main->team->getBattleTeamOf($user)) $event->setCancelled(true);
								break;
						}
						break;
					
					case PlayerActionPacket::ACTION_START_SNEAK:
						if(isset($this->main->view[$user])){
							$speed = $this->main->getSpeed($player, true)*0.1;
							$this->main->setSpeed($player, $speed);
						}
						break;
					case PlayerActionPacket::ACTION_STOP_SNEAK:
						if(isset($this->main->view[$user])){
							$speed = $this->main->getSpeed($player, true)*1.5;
							$this->main->setSpeed($player, $speed);
						}
						break;
					case PlayerActionPacket::ACTION_JUMP:
						$recon = false;
						if(($this->main->team->getBattleTeamOf($user) or isset($this->main->trypaintData['player'][$user])) and (($inv = $player->getInventory()) && ($inv->getItemInHand()->getID() === 351 || $inv->getItemInHand()->getID() === 0)) or ($recon = isset($this->main->reconData[$user]))){
							if(($plusY = $this->main->getSideWool($player, !$recon))){
								if(!$recon){
									if(!$this->main->spawnedSquid($player)){
										$this->main->SpawnToSquid($player);	
									}
									$this->main->MoveSquid($player);
								}
								$motion = $player->getMotion();
								//$x = $motion->x;
								$x = 0;
								//$y = $plusY * 0.175;
								//$y = $plusY * 0.2;
								$y = 0.3 + ($plusY - 1) * 0.175;
								// $z = $motion->z;
								$z = 0;
								/*
								$pk = new SetEntityMotionPacket();
								$pk->entities[] = [0, 0, 1.2, 0];
								*/
								$player->resetFallDistance();
								$player->onGround = true;
								$player->setMotion(new Vector3($x, $y, $z));
							}
						}
						#Seat
						if($this->main->seat->seated($player->getId())){
							$this->main->seat->stand($player);
							$event->setCancelled(true);
						}
						$x = floor($player->x);
						$y = floor($player->y);
						$z = floor($player->z);
						if($recon && $player->getLevel()->getBlockIdAt($x, $y-1, $z) === 33){
							$yaw = $player->yaw;
							$rad = $yaw/180*M_PI;
							$xx = -sin($rad);
							$zz = cos($rad);
							$mot = new Vector3($xx*1.25, 0.8, $zz*1.25);
							$player->setMotion($mot);
							$color = 0;
							$pos = new Vector3($x, $y+1, $z);
							$player->getLevel()->addParticle(new DestroyBlockParticle($pos, Block::get(35, $color)));
						}
						break;
				}
			break;
			case ProtocolInfo::MOVE_PLAYER_PACKET:
				//MoveEventとおなじ
				//ゲーム中なら
				if($this->main->game == 10){
					//チームを取得
					$player_bt = $this->main->team->getBattleTeamOf($user);
					//バトル中のチームのメンバーだったら
					if($player_bt){
						//座標とか取得
						$x = Math::floorFloat($packet->x);
						$y = Math::floorFloat($packet->y - 1.62 - 0.08);
						$z = Math::floorFloat($packet->z);
						$this->main->PlayerPositionCheck($player, true, $x, $y, $z);
						/*
						//ここからすり抜けのコード
						if($this->main->spawnedSquid($player)){
							$px = $packet->x;
							$py = $packet->y - $player->getEyeHeight();
							$pz = $packet->z;
							if($player->level->getBlockIdAt(floor($px), floor($py - 2), floor($pz)) === 52){
								$player->setMotion(new Vector3(0, -0.25, 0));//足元がスポナーならすり抜け
							}
							$playerYaw = $player->yaw + 180;
							//$velX =      sin($playerYaw / 180 * M_PI);
							//$velZ = -1 * cos($playerYaw / 180 * M_PI);
							$sideBlockId = $player->level->getBlockIdAt(floor($velX + $px), $py, floor($velZ + $pz));
							if($sideBlockId === 101 || $sideBlockId === 52){
								//$player->setMotion(new Vector3($velX * 1.5, 0, $velZ * 1.5));//正面(足の高さ)が鉄柵かスポナーならすり抜け
								$player->teleport(new Vector3($px + $velX * 1.5, $py, $pz + $velZ * 1.5));
							}
						}
						*/
					}
				}
				if(isset($this->main->trypaintData['player'][$user])){
					$x = Math::floorFloat($packet->x);
					$y = Math::floorFloat($packet->y - 1.62 - 0.08);
					$z = Math::floorFloat($packet->z);
					$this->main->PlayerPositionCheck($player, true, $x, $y, $z);
				}
				break;
		}
	}

	public function onDataPacketSend(DataPacketSendEvent $event){
		$packet = $event->getPacket();
		$player = $event->getPlayer();
		switch($packet::NETWORK_ID){
			#Seat
			case ProtocolInfo::ADD_PLAYER_PACKET:
				if(($p = $player->getLevel()->getEntity((int)$packet->eid)) instanceof Player){
					$this->main->seat->addSeatEntity($p, [$player]);
				}
				break;
			#Seat
			//case ProtocolInfo::SetEntityDataPacket:
				//break;
			case ProtocolInfo::REMOVE_ENTITY_PACKET:
				if(($p = $player->getLevel()->getEntity((int)$packet->eid)) instanceof Player){
					$this->main->seat->removeSeatEntity($p, [$player]);
				}
				break;
			# クラフトレシピ削除
			case ProtocolInfo::CRAFTING_DATA_PACKET:
				$packet->clean();
				break;
		}
	}

	public function EntityExplodeEvent(EntityExplodeEvent $event){
		$event->setCancelled(true);
	}

	public function ProjectileHitEvent(ProjectileHitEvent $event){

		$ent = $event->getEntity();

		if(isset($ent->ink)){
			//スプリンクラーの弾

			$x = $ent->x;
			$y = $ent->y;
			$z = $ent->z;
			$player = $ent->player;
			$user = $player->getName();
			$playerData = Account::getInstance()->getData($user);
			$color = $playerData->getColor();
			$level = $ent->level;
			$pos_ar = [];
			for($xx = -1; $xx < 2; $xx++){

				for($yy = -1; $yy < 2; $yy++){ 
			
					for($zz = -1; $zz < 2; $zz++){ 
				
						//$pos = new Vector3(floor($xx+$x), floor($yy+$y), floor($zz+$z));
						if($level->getBlockIdAt(floor($xx+$x), floor($yy+$y), floor($zz+$z)) !== 0){
							$pos_ar[] = [floor($xx+$x), floor($yy+$y), floor($zz+$z)];
						}
					}
				}			
			}
			$this->main->w->changeWoolsColor($level, $pos_ar, $color, $user, false);
		}

		if(isset($ent->bombType)){

			switch($ent->bombType){

				case 1:
				//キューバンボム

					$bomb = $this->main->w->spawnEntity("LavaSlime", $ent->player->getLevel(), floor($ent->x), floor($ent->y+1), floor($ent->z));
					$bomb->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_NO_AI, true);
					$F = function($array){
						$array[0]->bomb($array[6], $array[1], $array[6]->x, $array[6]->y, $array[6]->z, $array[2], $array[3], $array[4], $array[5], $array[7]);
						$array[6]->close();
					};

					$this->main->getServer()->getScheduler()->scheduleDelayedTask(new LateDo($this, $F, [$this->main->w, $ent->player, $ent->block, 3.5, 40, $this->main->w->getAllBattleMembers(), $bomb, 7]), 40);

					$F_2 = function($array){

						$array[0]->addParticle($array[1]);
					};

					$particle = new DestroyBlockParticle(new Vector3($ent->x, $ent->y, $ent->z), $ent->block);
					$this->main->getServer()->getScheduler()->scheduleDelayedTask(new LateDo($this, $F_2, [$ent->player->getLevel(), $particle]), 30);
					$this->main->getServer()->getScheduler()->scheduleDelayedTask(new LateDo($this, $F_2, [$ent->player->getLevel(), $particle]), 20);
					$ent->close();
				break;

				case 2:
				//クイックボム

					$this->main->w->bomb($ent, $ent->player, $ent->x, $ent->y, $ent->z, $ent->block, 3, 16, $this->main->w->getAllBattleMembers(), 5);
					$ent->close();
				break;

				case 3:
				//アシッドボール

					$this->main->w->acidBall($ent, $ent->player, $ent->x, $ent->y, $ent->z, 4, $this->main->w->getAllBattleMembers());
					$ent->close();
				break;

				case 4:
				//ノックボム

					$this->main->w->knockBomb($ent, $ent->player, $ent->x, $ent->y, $ent->z, $ent->block, 2.8, 8, $this->main->w->getAllBattleMembers(), 5);
					$ent->close();
				break;
				case 5:
				//イカスミボール
					$this->main->w->InkBall($ent, $ent->player, $ent->x, $ent->y, $ent->z, 4, $this->main->w->getAllBattleMembers());
					$ent->close();
				break;
				case 6:
				//投げたスプリンクラー
					$player = $ent->player;
					$level = $player->getLevel();
					$x = floor($ent->x);
					$y = floor($ent->y);
					$z = floor($ent->z);
					$face = $this->main->w->getFaceFromPos($level, $x, $y, $z);
					$ark_place = $this->main->w->getArkPlaceSprinkler($level, $x, $y, $z, $face);
					if($face !== false && $ark_place !== false){
						$this->main->w->spawnSprinkler($player, $ark_place[0], $ark_place[1], $ark_place[2], [$x, $y, $z], $face, false);
					}else{
						$player->sendPopup($this->main->lang->translateString("cannotPlace"));
					}
					$ent->close();
				break;
				case 7:
				//ポイントセンサー
					$this->main->w->PointSensor($ent, $ent->player, $ent->x, $ent->y, $ent->z, 5, $this->main->w->getAllBattleMembers());
					$ent->close();
				break;

			}
		}
	}
}
