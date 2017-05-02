<?php

namespace SplatoonMCPE;

use pocketmine\Server;
use pocketmine\item\Item;
use pocketmine\entity\Entity;
use pocketmine\entity\Item as ItemEntity;
use pocketmine\network\protocol\AddItemEntityPacket;
use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\network\protocol\SetEntityDataPacket;

class ItemShop{
	
	# ページ番号 => ブキID[]
	private $pageToWeapons;
	# プレイヤーネーム => 選択中のページ
	private $selectedPage;
	# ショップの最大ページ番号
	private $maxPage;
	# [ショップ番号][ボタンの役割] => [x, y, z]
	private $buttonPos;
	# [ショップ番号][棚の番号] => [x, y, z]
	private $shopPos;
	# [x][y][z] => ボタンの役割
	private $buttonIndex;
	# [x][y][z] => ショップの棚の番号
	private $shopIndex;
	
	# ショップのエンティティを召喚する基本ID
	const ENTITY_ID = 3430000;
	
	public function __construct($main, $weapondata){
		$this->main = $main;
		$this->weapondata = $weapondata;
		
		# ページ移動のボタンの座標 1:前 2:次 3:5ページ前 4:5ページ後
		$this->buttonPos = [
			1 => [
				1 => [511, 9, -175],
				2 => [513, 9, -175],
				3 => [510, 9, -175],
				4 => [514, 9, -175]
			],
			2 => [
				1 => [530, 10, -159],
				2 => [532, 10, -159],
				3 => [529, 10, -159],
				4 => [533, 10, -159]
			]
			/*2 => [
				1 => [x, y, z],
				2 => [x, y, z],
				3 => [x, y, z],
				4 => [x, y, z]
			]*/
		];
		
		# ブキパネルのブキ陳列位置 1:真ん中 2:左 3:右
		$this->shopPos = [
			1 => [
				1 => [512, 10, -176],
				2 => [510, 10, -176],
				3 => [514, 10, -176]
			],
			2 => [
				1 => [531, 11, -160],
				2 => [529, 11, -160],
				3 => [533, 11, -160]
			]
			/*2 => [
				1 => [x, y, z],
				2 => [x, y, z],
				3 => [x, y, z]
			]*/
		];
		
		# $buttonPos を使いやすい形に変更
		$this->buttonIndex = [];
		foreach ($this->buttonPos as $posArray) {
			foreach ($posArray as $key => $pos) {
				$this->buttonIndex[$pos[0]][$pos[1]][$pos[2]] = $key;
			}
		}
		unset($posArray, $pos);
		
		# $shopPos を使いやすい形に変更
		$this->shopIndex = [];
		foreach ($this->shopPos as $posArray) {
			foreach ($posArray as $key => $pos) {
				$this->shopIndex[$pos[0]][$pos[1]][$pos[2]] = $key;
			}
		}
		unset($posArray, $pos);
		
		$this->_init();
	}
	
	# クラスの初期設定
	private function _init(){
		$this->pageToWeapons = [];
		$this->selectedPage = [];
		
		# アイテムID => ページ番号
		$idToPage = [];
		
		foreach($this->weapondata as $wnum => $ar){
			# 非売品だったらスキップ
			if($ar[4][2] === false) continue;
			
			# ブキのアイテムID
			$weapID = ($ar[1][0] * 100) +  $ar[1][1];
			
			# ページ番号のセット
			if(!isset($idToPage[$weapID])){
				$idToPage[$weapID] = count($idToPage) + 1;
				$this->pageToWeapons[$idToPage[$weapID]] = [];
			}
			
			$page = $idToPage[$weapID];
			$this->pageToWeapons[$page][] = $wnum;
		}
		
		$this->maxPage = count($idToPage)+1;
	}
	
	# プレイヤーの選択ページの移動
	public function selectPage($player,$block){

		$this->checkPageSetted($player);

		$name = $player->getName();
		$x = $block->x;
		$y = $block->y;
		$z = $block->z;
		# ボタンの番号
		$bIndex = $this->buttonIndex[$x][$y][$z] ?? null;
		# ブロックの座標がボタンなら
		if($bIndex !== null){
			#選択中のページ番号
			$nowPage = $this->selectedPage[$name];
			$move = 0;
			# ボタンの番号
			switch($bIndex){
				# 前
				case 1:
					$move = -1;
				break;
				
				# 後
				case 2:
					$move = 1;
				break;
				
				# 5ページ前
				case 3:
					$move = -5;
				break;
				
				# 5ページ後
				case 4:
					$move = 5;
				break;
				
				# なんかおかしかったら
				default:
					return false;
			}
			
			# 新しいページ番号
			$nextPage = $nowPage + $move;
			
			# ページ番号が正しくなければ
			if(!$this->isValidPage($nextPage)){
				# そんなページはないよ
				$player->sendPopup("§c".$this->main->lang->translateString('page.notexist'));
			}else{
				# ページ番号が正しければ
				# ページ更新
				$this->sendPage($player, $nextPage);
				$player->sendPopup($nextPage."/".($this->maxPage-1)." Page");
				$this->selectedPage[$name] = $nextPage;
			}
		}
	}
	
	# ブキのセット
	public function selectWeapon($player,$block){

		$this->checkPageSetted($player);

		$name = $player->getName();
		$x = $block->x;
		$y = $block->y;
		$z = $block->z;
		# 棚の番号
		$sIndex = $this->shopIndex[$x][$y][$z] ?? null;
		
		# ブロックの座標がショップなら
		if($sIndex !== null){
			$playerData = Account::getInstance()->getData($name);
			$weapons = array_keys($playerData->getWeapons());
			$page = $this->selectedPage[$name];
			$wNum = $this->pageToWeapons[$page][$sIndex-1] ?? null;
			
			# 存在しないブキなら終了
			if($wNum === null) return false;
			$wName = $this->main->w->getWeaponName($wNum);
			foreach($weapons as $weapon){
				# 既に持ってるブキなら
				if($wNum === $weapon){
					# ブキ変更
					if($this->main->canChangeWeapon()){
						$playerData->setNowWeapon($wNum);
						$player->sendPopup($this->main->lang->translateString("weapon.change", [$wName]));
					}
					return false;
				}
			}
			# 持ってないブキなら
			# 一度ブロックを押していてかつ4秒以内なら
			if(isset($playerData->buyWeaponCheck[$wNum]) and time() - $playerData->buyWeaponCheck[$wNum] <= 4){
				# 購入
				$playerData->buyWeapon($wNum);
				$playerData->buyWeaponCheck = [];
				$this->sendPage($player,$page);
			}else{
				$player->sendMessage($this->main->lang->translateString("weapon.buy.check", [$wName]));
				$playerData->buyWeaponCheck = [];
				$playerData->buyWeaponCheck[$wNum] = time();
			}
			return true;
		}
	}
	
	# ページ番号が有効か取得
	public function isValidPage($page){
		return 0 < $page and $page < $this->maxPage;
	}

	# パネルにブキを陳列
	public function sendPage($player, $page = null){
		$name = $player->getName();
		
		$this->checkPageSetted($player);
		
		if($page === null){
			$page = $this->selectedPage[$name];
		}
		
		# 購入チェックリセット
		$playerData = Account::getInstance()->getData($name);
		$playerData->buyWeaponCheck = [];
		
		$this->clear($player);
		
		# アイテムエンティティ召喚
		$flags = 0;
		$flags |= 0 << Entity::DATA_FLAG_INVISIBLE;
		$flags |= 1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG;
		$flags |= 1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG;
		$flags |= 1 << Entity::DATA_FLAG_IMMOBILE;
		foreach($this->pageToWeapons[$page] as $number => $weapon){
			$index = $number+1;
			$item = $this->weapondata[$weapon][1];
			foreach($this->shopPos as $key => $pos){
				$eid = self::ENTITY_ID + $number + (100 * $key);
				# アイテムをスポーンさせる
				$pk = new AddItemEntityPacket;
				$pk->eid = $eid;
				$pk->x = $pos[$index][0] + 0.5;
				$pk->y = $pos[$index][1] + 0.25;
				$pk->z = $pos[$index][2] + 0.5;
				$pk->item = Item::get($item[0],$item[1]);
				$player->dataPacket($pk);
				
				# アイテムにメタデータを付与する
				$pk = new SetEntityDataPacket;
				$pk->eid = $eid;
				$pk->metadata = [
					Entity::DATA_FLAGS => [
						Entity::DATA_TYPE_LONG, $flags
					],
					Entity::DATA_NAMETAG => [
						Entity::DATA_TYPE_STRING, $this->getDisplayName($player,$weapon)
					],
				];
				$player->dataPacket($pk);
			}
		}
	}
	
	# ブキの名前とかを取得
	private function getDisplayName($player,$weaponNumber){
		$data = $this->weapondata[$weaponNumber];
		$name = $data[0];
		$type = $data['type'];
		$sub = $this->main->w->getSubWeaponData($data['sub'])[0];
		switch($type){
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
		$result = "§a§o".$name."\n".$sub."§r"."\n";
		$result .= (!isset(Account::getInstance()->getData($player->getName())->getWeapons()[$weaponNumber])) ? "§b".$data[4][0]."pt" : $this->main->lang->translateString("weapon.alreadyObtained");
		return $result;
	}
	
	# パネルからブキを削除
	public function clear($player){

		$this->checkPageSetted($player);

		$name = $player->getName();
		$page = $this->selectedPage[$name];
		# アイテムエンティティ削除
		foreach($this->pageToWeapons[$page] as $number => $weapon){
			foreach($this->shopPos as $key => $pos){
				$pk = new RemoveEntityPacket;
				$pk->eid = self::ENTITY_ID + $number + (100 * $key);
				$player->dataPacket($pk);
			}
		}
	}

	# ULSでなんかエラー出るみたいなのの対策
	public function checkPageSetted($player){
		$name = $player->getName();
		if(!isset($this->selectedPage[$name])){
			$this->selectedPage[$name] = 1;
		}
	}
	
	# 全プレイヤーからアイテムを消す
	public function clearAll(){
		foreach(Server::getInstance()->getOnlinePlayers() as $player){
			$this->clear($player);
		}
	}
	
	# ページを読み込み直す
	public function reset($player){
		$this->sendPage($player);
	}
	
	# 全プレイヤーのページを読み込み直す
	public function resetAll(){
		foreach(Server::getInstance()->getOnlinePlayers() as $player){
			$this->reset($player);
		}
	}
}