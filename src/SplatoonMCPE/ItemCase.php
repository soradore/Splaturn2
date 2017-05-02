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

use SplatoonMCPE\FloatingText;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\math\Vector3;
use pocketmine\network\protocol\AddItemEntityPacket;
use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\network\protocol\SetEntityDataPacket;
use pocketmine\Player;
use pocketmine\Server;

class ItemCase{

	private $data = [];

	const ENTITY_ID = 7500000;

	function __construct($main){
		$this->main = $main;
	}

	public function set(Player $player){
		$user = strtolower($player->getName());
		$level = $player->level;
		foreach($this->main->w->getWeaponsDataAll() as $w_num => $w_data){
			if($w_data[4][2] === false) continue;
			if(!isset($this->data[$user][$w_num]['spawn'])){
				$weapon_name = $w_data[0];
				switch($w_data['type']){
					case Weapon::TYPE_SHOOTER:
						$type_name = "\n§eシュータータイプ";
					break;
					case Weapon::TYPE_ROLLER:
						$type_name = "\n§eローラータイプ";
					break;
					case Weapon::TYPE_CHARGER:
						$type_name = "\n§eチャージャータイプ";
					break;
					case Weapon::TYPE_SLOSHER:
						$type_name = "\n§eスロッシャータイプ";
					break;
					case Weapon::TYPE_SPLATLING:
						$type_name = "\n§eスピナータイプ";
					break;
					default:
						$type_name = "\n§e？？？タイプ";
					break;
				}
				$subweapon_name = $this->main->w->getSubWeaponData($w_data['sub'])[0];
				$subweapon_name .= $type_name;
				$pos = $w_data['pos'];
				$pt = $w_data[4][0];

				$entityId = self::ENTITY_ID + $w_num;

				$pk = new AddItemEntityPacket;
				$pk->eid = $entityId;
				$item = $w_data[1];
				$pk->item = Item::get($item[0], $item[1]);
				$pk->x = $pos['x'] + 0.5;
				$pk->y = $pos['y'];
				$pk->z = $pos['z'] + 0.5;
				$player->dataPacket($pk);

				//Thanks famima65536!!!!
				$flags = 0;//Don't Remove!!
				$flags |= 1 << Entity::DATA_FLAG_IMMOBILE;

				$pk = new SetEntityDataPacket();
				$pk->eid = $entityId;
				$pk->metadata = [
					Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags]
				];
				$player->dataPacket($pk);

				$title = "§a§o".$weapon_name."\n".$subweapon_name."§r";
				$playerData = Account::getInstance()->getData($user);
				$text = (!isset($playerData->getWeapons()[$w_num])) ? "§b".$w_data[4][0]."pt" : $this->main->lang->translateString("weapon.alreadyObtained");
				if(isset($this->data[$user][$w_num]['text'])){
					$textParticle = $this->data[$user][$w_num]['text'];
					$textParticle->setInvisible(false);
					$textParticle->setTitle($title);
					$textParticle->setText($text);
				}else{
						$textParticle = new FloatingText($this->main, new Vector3($pos['x'] + 0.5, $pos['y'] + 1.75, $pos['z'] + 0.5), $text, $title);
				}
				$textParticle->addText([$player]);
				$this->data[$user][$w_num]['text'] = $textParticle;
				$this->data[$user][$w_num]['spawn'] = true;
			}
		}
	}

	public function setAll(){
		foreach(Server::getInstance()->getOnlinePlayers() as $player){
			$this->set($player);
		}
	}

	public function remove($player, $unset = false){
		$level = $player->level;
		$user = strtolower($player->getName());
		foreach($this->main->w->getWeaponsDataAll() as $w_num => $w_data){
			if($w_data[4][2] === false) continue;
			if(isset($this->data[$user][$w_num]['spawn'])){

				$pk = new RemoveEntityPacket;
				$pk->eid = self::ENTITY_ID + $w_num;
				$player->dataPacket($pk);

				$textParticle = $this->data[$user][$w_num]['text'];
				$textParticle->setInvisible(true);
				$textParticle->addText([$player]);
				$this->data[$user][$w_num]['text'] = $textParticle;

				unset($this->data[$user][$w_num]['spawn']);
			}
		}
		if($unset){
			unset($this->data[$user]);
		}
	}

	public function removeAll(){
		foreach(Server::getInstance()->getOnlinePlayers() as $player){
			$this->remove($player);
		}
	}

	public function reset($player){
		$this->remove($player);
		$this->set($player);
	}

	public function resetAll(){
		foreach(Server::getInstance()->getOnlinePlayers() as $player){
			$this->remove($player);
			$this->set($player);
		}
	}
}