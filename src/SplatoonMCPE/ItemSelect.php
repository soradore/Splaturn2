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

use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\entity\Item as ItemEntity;
use pocketmine\math\Vector3;

use pocketmine\level\particle\FloatingTextParticle;

use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\AddItemEntityPacket;
use pocketmine\network\protocol\AddPlayerPacket;
use pocketmine\network\protocol\LevelEventPacket;
use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\network\protocol\SetEntityDataPacket;

use pocketmine\utils\UUID;

class ItemSelect{

	public function __construct($main, $weapondata, $lang){
		$this->main = $main;
		$this->weapondata = $weapondata;
		$this->setLang($lang);

		$selectButton = [
			1 => [
				[542,9,-159],
				[544,9,-159],
			],
			2 => [
				[539,8,-106],
				[539,8,-104],
			],
			3 => [
				[491,9,-106],
				[491,9,-108],
			],
			4 => [
				[492,9,-150],
				[492,9,-152],
			],
		];
		$this->maxPerPage = 5;//1ページに表示できるアイテムの数
		$selectItem = [
			1 => [
				[541,10,-160],
				[542,10,-160],
				[543,10,-160],
				[544,10,-160],
				[545,10,-160],
			],
			2 => [
				[540,9,-107],
				[540,9,-106],
				[540,9,-105],
				[540,9,-104],
				[540,9,-103],
			],
			3 => [
				[490,10,-105],
				[490,10,-106],
				[490,10,-107],
				[490,10,-108],
				[490,10,-109],
			],
			4 => [
				[491,10,-149],
				[491,10,-150],
				[491,10,-151],
				[491,10,-152],
				[491,10,-153],
			],
		];
		$this->floatText = [
			'name' => [//Y+1.5
				[543, 11.5, -160],
				[540, 10.5, -105],
				[490, 11.5, -107],
				[491, 11.5, -151],
			],
			'weapon' => [//Y-1.5
				#現在のブキ
				[543, 8.5, -160],
				[540, 7.5, -105],
				[490, 8.5, -107],
				[491, 8.5, -151],
			],
			'button' => [
				/*
				X => [
					[前へ],
					[次へ],
				],
				*/
				1 => [
					[542,9,-160],
					[544,9,-160],
				],
				2 => [
					[540,8,-106],
					[540,8,-104],
				],
				3 => [
					[490,9,-106],
					[490,9,-108],
				],
				4 => [
					[491,9,-150],
					[491,9,-152],
				],
			],
		];
		$this->selectButton = $selectButton;
		$this->selectButtonIndex = $this->changeArrayPosToIndex($selectButton);
		$this->selectItem = $selectItem;
		$this->selectItemIndex = $this->changeArrayPosToIndex($selectItem);
		$this->playerIndex = [];
	}

	public function setLang($lang){
		//$this->lang = $lang;
		$this->text = [
			'page.notexist' => $lang->translateString("weapon.selectPageNotexist"),
			'back' => $lang->translateString("back"),
			'next' => $lang->translateString("next"),
			'equipped' => $lang->translateString("equipped"),
			'sub_equipped' => $lang->translateString("sub_equipped"),
			'gadget1' => $lang->translateString("gadget1"),
			'gadget2' => $lang->translateString("gadget2"),
			'gadget3' => $lang->translateString("gadget3"),
		];
	}

	public function setWeaponsData($data){
		$this->weapondata = $data;
	}

	// [$x, $y, $z]を[$x][$y][$z]にかえる
	private function changeArrayPosToIndex($array){
		$d = [];
		foreach($array as $ar){
			$cnt = 1;
			foreach($ar as $w){
				$d[$w[0]][$w[1]][$w[2]] = $cnt;
				$cnt ++;
			}
		}
		return $d;
	}

	private function getweaponName($wno){
		return isset($this->weapondata[$wno]) ? $this->weapondata[$wno][0] : "";
	}

	/**
	 * ページ切り替え
	 * @param  Player $player
	 * @param  Block  $block
	 * @return bool
	 */
	public function selectPage($player, $block){
		$name = $player->getName();
		$x = $block->x;
		$y = $block->y;
		$z = $block->z;
		if(isset($this->selectButtonIndex[$x][$y][$z])){
			$nowPage = $this->playerIndex[$name];
			$type = $this->selectButtonIndex[$x][$y][$z];
			switch($type){
				case 1:
					$nextPage = $nowPage - 1; break;
				case 2:
					$nextPage = $nowPage + 1; break;
			}
			if($this->canShowMenu($name, $nextPage)){
				$this->buttonSound($player, $x, $y, $z, $type - 1);
				$this->removeMenu($player);
				$this->playerIndex[$name] = $nextPage;
				$this->sendMenu($player);
				$playerData = Account::getInstance()->getData($name);
				$weapons = $playerData->getWeapons();
				$maxPage = ceil( count($weapons) / $this->maxPerPage );
				$player->sendPopup($nextPage."/".$maxPage." Page");
				$this->floatingTextColorChange($player);
				return true;
			}else{
				//$this->buttonSound($player, $x, $y, $z, false);
				$player->sendPopup("§c".$this->text['page.notexist']);
				return true;
			}
		}
		return true;
	}

	/**
	 * ブキ切り替え
	 * @param  Player $player
	 * @param  Block  $block
	 * @return bool
	 */
	public function selectWeapon(Player $player, $block){
		$name = $player->getName();
		$x = $block->x;
		$y = $block->y;
		$z = $block->z;
		$playerData = Account::getInstance()->getData($name);
		$weapons = array_keys($playerData->getWeapons());
		if(isset($this->selectItemIndex[$x][$y][$z])){
			$page = $this->playerIndex[$name];
			$data = array_chunk($weapons, $this->maxPerPage);
			$place = $this->selectItemIndex[$x][$y][$z];
			if(isset($data[$page - 1][$place - 1])){
				$wno = $data[$page - 1][$place - 1];
				$weaponName = $this->getweaponName($wno);
				if($weaponName){
					return $wno;
				}
			}
		}
		return false;
	}

	/**
	 * アイテムなどをセット
	 * @param Player  $player
	 * @param boolean $join   ログイン時はtrue
	 */
	public function set(Player $player, $join = true){
		$name = $player->getName();
		if(!isset($this->playerIndex[$name])){
			$this->playerIndex[$name] = 1;
		}
		$this->sendMenu($player);
		$this->addFloatingTextParticle($player, true);
		if($join){
			$this->sendButton($player);
		}else{
			$this->floatingTextColorChange($player);
		}
	}

	public function reset($player){
		$this->removeMenu($player);
		$this->sendMenu($player);
		$this->floatingTextColorChange($player);
	}

	/**
	 * アイテムなどを非表示に
	 * @param  PLayer  $player
	 * @param  boolean $quit   ログアウト時はtrue
	 */
	public function remove($player, $quit = true){
		$name = $player->getName();
		if($quit){
			unset($this->playerIndex[$name]);
		}else{
			$this->removeMenu($player);
			$this->removeFloatingText($player);
		}
	}

	/**
	 * 指定したページが存在するかどうか
	 * @param  string  $name
	 * @param  int     $page
	 * @return boolean
	 */
	private function canShowMenu($name, $page){
		$playerData = Account::getInstance()->getData($name);
		$weapons = $playerData->getWeapons();
		$maxPage = ceil( count($weapons) / $this->maxPerPage );
		if(0 < $page && $page <= $maxPage){
			return true;
		}
		return false;
	}

	private function sendMenu($player){
		$name = $player->getName();
		$page = $this->playerIndex[$name];
		$playerData = Account::getInstance()->getData($name);
		$weapons = array_keys($playerData->getWeapons());
		$data = array_chunk($weapons, $this->maxPerPage);
		if(!isset($data[$page - 1])){
			$data[$page - 1] = [];
		}
		foreach($data[$page - 1] as $pos_num => $itemIndex){
			if(isset($this->weapondata[$itemIndex])){
				foreach($this->selectItem as $unit => $p){
					$pos = $p[$pos_num];
					$item = $this->weapondata[$itemIndex][1];
					$entityId = ($pos_num + 1 + 650900002) + 100 * $unit;
					$pk = new AddItemEntityPacket;
					$pk->item = Item::get($item[0], $item[1]);
					$pk->x = $pos[0] + 0.5;
					$pk->y = $pos[1] + 0.25;
					$pk->z = $pos[2] + 0.5;
					$pk->eid = $entityId;
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
				}
			}
		}
	}

	private function removeMenu($player){
		$maxcnt = $this->maxPerPage;
		foreach($this->selectItem as $unit => $p){
			$cnt = 1;
			while($maxcnt >= $cnt){
				$pk = new RemoveEntityPacket;
				$pk->eid = ($cnt + 650900002) + 100 * $unit;
				$player->dataPacket($pk);
				$cnt ++;
			}
		}
	}

	private function sendButton($player){
		$name = $player->getName();
		$page = $this->playerIndex[$name];
		$back = (($this->canShowMenu($name, $page - 1)) ? "§f" : "§8")." <\n".$this->text['back'];
		$next = (($this->canShowMenu($name, $page + 1)) ? "§f" : "§8")." >\n".$this->text['next'];
		foreach($this->floatText['button'] as $unit => $ar){
			$cnt = 1;
			foreach($ar as $p){
				$x = $p[0] + 0.5;
				$y = $p[1] - 0.2;
				$z = $p[2] + 0.5;
				$pos = new Vector3($x, $y, $z);
				/*if(isset($this->textParticle[$name][$ar][$p])){
					$this->textParticle[$name][$ar][$p]->setInvisible(false);
				}else{
					$this->textParticle[$name][$ar][$p] = new FloatingText($this->main, $pos, "", $title);
				}
				$this->textParticle[$name][$ar][$p]->addText([$player]);*/
				$cnt ++;
			}
		}
	}

	/**
	 * ボタンのテキストパーティクルを更新
	 * @param  Player  $player
	 * @return boolean
	 */
	public function floatingTextColorChange($player){
		$name = $player->getName();
		$page = $this->playerIndex[$name];
		$back = (($this->canShowMenu($name, $page - 1)) ? "§f" : "§8")." <\n".$this->text['back'];
		$next = (($this->canShowMenu($name, $page + 1)) ? "§f" : "§8")." >\n".$this->text['next'];
		//foreach($this->selectButton as $unit => $ar){
		foreach($this->floatText['button'] as $unit => $ar){
			$cnt = 1;
			foreach($ar as $p){
				$title = ($cnt == 1) ? $back : $next;
				$x = $p[0] + 0.5;
				$y = $p[1] - 0.2;
				$z = $p[2] + 0.5;
				$pos = new Vector3($x, $y, $z);
				/*if(isset($this->textParticle[$name][$ar][$p])){
					$this->textParticle[$name][$ar][$p]->setInvisible(false);
				}else{
					$this->textParticle[$name][$ar][$p] = new FloatingText($this->main, $pos, "", $title);
				}
				$this->textParticle[$name][$ar][$p]->addText([$player]);*/
				$cnt++;
			}
		}
	}

	public function removeFloatingText($player){
		$user = $player->getName();
		//foreach($this->selectButton as $unit => $ar){
		foreach($this->floatText['button'] as $unit => $ar){
			foreach($ar as $p){
				/*if(isset($this->textParticle[$user][$ar][$p])){
					$this->textParticle[$user][$ar][$p]->setInvisible(true);
					$this->textParticle[$user][$ar][$p]->addText([$player]);
				}*/
			}
		}
		foreach($this->floatText as $key => $pos_ar){
			foreach($pos_ar as $key_2 => $p){
				if(isset($this->textParticle[$user][$key][$key_2])){
					$this->textParticle[$user][$key][$key_2]->setInvisible(true);
					$this->textParticle[$user][$key][$key_2]->addText([$player]);
				}
			}
		}
	}

	public function addFloatingTextParticle(Player $player, $first = false){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$nowWeapon = $playerData->getNowWeapon();
		$gad = Gadget::getGadgetsData($player);
		$titleData = [
			'name' => "§l§aWeapon equipment panel",

			'weapon' => "§e".$this->text['equipped']."§f: §a".$this->getweaponName($nowWeapon)."\n§e".$this->text['sub_equipped']."§f:§a".$this->main->w->getSubWeaponName($this->main->w->getSubWeaponNumFromWeapon($nowWeapon))."\n§e".$this->text['gadget1']."§f:§a".$this->main->lang->translateString(Gadget::getGadgetName($gad[0]))."\n§e".$this->text['gadget2']."§f:§a".$this->main->lang->translateString(Gadget::getGadgetName($gad[1]))."\n§e".$this->text['gadget3']."§f:§a".$this->main->lang->translateString(Gadget::getGadgetName($gad[2]))
		];
		foreach($this->floatText as $title => $pos_ar){
			if($title === "button") continue;
			foreach($pos_ar as $key => $p){
				$pos = new Vector3($p[0] + 0.5, $p[1], $p[2] + 0.5);
				switch($title){
					case "name":
						if($first){
							if(isset($this->textParticle[$user][$title][$key])){
								$this->textParticle[$user][$title][$key]->setInvisible(false);
							}else{
								$this->textParticle[$user][$title][$key] = new FloatingText($this->main, $pos, "", $titleData[$title]);
							}
							$this->textParticle[$user][$title][$key]->addText([$player]);
						}
						break;
					case "weapon":
						if(isset($this->textParticle[$user][$title][$key])){
							$this->textParticle[$user][$title][$key]->setTitle($titleData[$title]);
							$this->textParticle[$user][$title][$key]->setInvisible(false);
						}else{
							$this->textParticle[$user][$title][$key] = new FloatingText($this->main, $pos, "", $titleData[$title]);
						}
						$this->textParticle[$user][$title][$key]->addText([$player]);
						break;
				}
			}
		}
	}

	/**
	 * ボタンのサウンドをプレイヤーに鳴らす
	 * @param  Player  $player
	 * @param  int     $x
	 * @param  int     $y
	 * @param  int     $z
	 * @param  mixed   $type
	 */
	public function buttonSound(Player $player, $x, $y, $z, $type){
		$pk = new LevelEventPacket;
		$pk->evid = 3500;
		$pk->x = $x;
		$pk->y = $y;
		$pk->z = $z;
		//$pk->data = $type ? 0 : 1000;
		$pk->data = $type ? 600 : 500;
		$player->dataPacket($pk);
	}
}