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

use pocketmine\entity\Attribute;
use pocketmine\entity\Entity;

use pocketmine\item\Item;

use pocketmine\math\Vector3;

use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\CompoundTag;

use pocketmine\utils\Config;

use pocketmine\utils\MainLogger;

use pocketmine\Player;

use pocketmine\Server;


class PlayerData{

	const DATA_VERSTION = 9;

	private $main,
			$player,
			$username = "",
			$iusername = "",
			$cheep = "",
			$version = 0,
			$pt = 0,
			$c = 0,
			$win = 0,
			$areac = 0,
			$areawin = 0,
			$rank = 1000,
			$weapon = 1,
			$weapons = [],
			$got = [],
			$gadgets = [],
			$auth = 0,
			$skinData = "",
			$skinId = "",
			$punish = "normal",
			//ここから一時的なデータ
			$inkConsumption = 0,
			$paintAmount = 0,
			$tank = [0, 0, 0],//現在,最大,追加された量
			$rate = 0,
			$fieldNum = -1,
			$color;


	public $buyWeaponCheck = [];
			

	private $debug = false;

	function __construct(Player $player, $main = null){
		$this->player = $player;
		$this->main = $main;
		if($player !== null){
			$this->username = $player->getName();
			$this->iusername = strtolower($this->username);
		}
		
		$this->load();

		if($player !== null){
			$this->skinData = $player->getSkinData();
			$this->skinId = $player->getSkinId();
		}
	}

	public function remove(){

	}

	function __destruct(){
		$this->remove();
	}

	public function __get($value){
		switch($value){
			case "username":
			case "iusername":
				return $this->{$value};
		}
	}

	public function setData($array){
		foreach($array as $key => $value){
			switch($key){
				case "inkConsumption":
				case "paintAmount":
				case "tank":
				case "rate":
				case "fieldNum":
				case "color":
					$this->{$key} = $value;
					break;
			}
		}
	}

	public function resetBattleData(){
		$this->inkConsumption = 0;
		$this->paintAmount = 0;
		$this->tank = [0, 0, 0];
		$this->rate = 0;
		$this->fieldNum = -1;
		$this->color = null;
	}

	/**
	 * データ読み込み
	 * @return bool
	 */
	public function load(){
		$data = $newData = $this->getNewData();

		$setData = function()use(&$data){
			foreach($data as $key => $value){
				$this->{$key} = $value;
			}
		};

		Database::getInstance()->MySQLConnect();

		/*
		echo "transfering data\n";
		$oldTable = "user_data";
		$newTable = "userdata";
		$updatecount = 0;
		$datacount = 0;
		$sql = "SELECT * FROM {$oldTable};";
		if($result = Database::$mysqli->query($sql)){
			while($row = $result->fetch_assoc()){
				$datacount++;
				$name = $row['name'];
				$data = json_decode($row['data'], true);
				$cheep = $row['cheep'];
				$skinData = addslashes($row['skintxt']);
				$auth = $row['auth'];
				$date = $row['date'];
				$skinId = isset($data->skinId) ? $data->skinId : (isset($data->skinName) ? $data->skinName : "");
				$ndata = [
					'cheep' => $cheep,
					'version' => $data['v'],
					'pt' => $data['pt'],
					'c' => $data['c'],
					'win' => $data['win'],
					'weapon' => $data['weapon'],
					'weapons' => $data['weapons'],
					'got' => $data['got'],
					'auth' => $auth,
					//'skinData' => $skinData,
					'skinId' => $skinId,
				];
				$data = Database::$mysqli->real_escape_string(serialize($data));
				$sql2 = "INSERT INTO {$newTable} (name, data, date, skindata) ".
						"VALUES ('{$name}', '{$data}', '{$date}', '{$skinData}');";
				//$sql3 = "DELETE FROM {$oldTable} WHERE name = '{$name}';";
				//if( ( $r2 = Database::$mysqli->query($sql2)) && Database::$mysqli->query($sql3)){
				if( $r2 = Database::$mysqli->query($sql2) ){
					$updatecount++;
				}else{
					echo Database::$mysqli->error."\n";
				}
				echo $datacount % 50 ? ($r2 ? "=" : "!") : $datacount."\n";
			}
		}
		echo $updatecount."/".$datacount;

		*/

		if(Database::isConnected() && $this->iusername !== ""){
			$sql = "SELECT data FROM userdata WHERE name = '{$this->iusername}';";
			if($result = Database::$mysqli->query($sql)){
				while($row = $result->fetch_assoc()){
					if($this->debug){
						echo strlen($row['data'])."\n";
						print_r($row);
					}
					if(!($data = unserialize($row['data']))){
						MainLogger::getLogger()->info("broken data found: {$this->iusername}");
						@mkdir("./brokendata");
						file_put_contents("./brokendata/{$this->iusername}.ser", $row['data']);
						$data = $newData;
					}
				}
				$result->free();

				
				$setData();
				$this->upgradeData();
			}else{
				//新規データの場合
				//データベースに書き込みする
				$setData();
				$this->save();
			}
		}else{
			$setData();
		}
		//ulsのぱにい取得(2017 3/10 とらすた)
		if(isset($this->main->UniLoginSystem)){
			$p = $this->main->UniLoginSystem->checkPunish($this->username, $this->player->getAddress());
			$this->punish = $p;
			//echo "PlayerData: load() ULSが見つかりmasita\n";
		}else{
			//echo "PlayerData: load() ULSが見つかりません\n";
		}
		return true;
	}

	public function getPanish(){
		return $this->punish;
	}

	/**
	 * データ再読み込み
	 * @return boolean
	 */
	public function reload(){
		$data = $newData = $this->getNewData();

		$setData = function()use(&$data){
			foreach($data as $key => $value){
				//echo $key." ";
				switch($key){
					case "weapon":
						if($this->main->team->getBattleTeamOf($this->username)){
							//echo "continue\n";
							continue 2;
						}
						break;
					case "skinData":
					case "skinId":
						//echo "continue\n";
						continue 2;
				}
				//echo "no continue\n";
				$this->{$key} = $value;
			}
		};

		Database::getInstance()->MySQLConnect();

		if(Database::isConnected() && $this->iusername !== ""){
			$sql = "SELECT data FROM userdata WHERE name = '{$this->iusername}';";
			if($result = Database::$mysqli->query($sql)){
				while($row = $result->fetch_assoc()){
					if($this->debug){
						echo strlen($row['data'])."\n";
						print_r($row);
					}
					if(!($data = unserialize($row['data']))){
						MainLogger::getLogger()->info("broken data found: {$this->iusername}");
						@mkdir("./brokendata");
						file_put_contents("./brokendata/{$this->iusername}.ser", $row['data']);
						$data = $newData;
					}
				}
				$result->free();

				
				$setData();
				$this->upgradeData();
			}else{
				$setData();
			}
		}else{
			$setData();
		}
		//ulsのぱにい取得(2017 3/10 とらすた)
		if(isset($this->main->UniLoginSystem)){
			$p = $this->main->UniLoginSystem->checkPunish($this->username, $this->player->getAddress());
			$this->punish = $p;
		}else{
			echo "PlayerData: Reload() ULSが見つかりません";
		}
		if(!Database::isConnected()){
			return false;
		}
		return true;
	}

	/**
	 * データ保存
	 * @return bool
	 */
	public function save(){

		$saveResult = false;

		Database::getInstance()->MySQLConnect();

		if(Database::isConnected() && $this->iusername !== ""){

			$sql = "SELECT no, data FROM userdata WHERE name = '{$this->iusername}';";
		
			$queryResult = Database::$mysqli->query($sql);
		
			if($queryResult){

				$oldData = [];

				while($row = $queryResult->fetch_assoc()){
					$db_id = $row['no'];
					$oldData = unserialize($row['data']);
				}
				$queryResult->free();

				$data = [
					'cheep' => $this->cheep,
					'version' => $this->version,
					'pt' => $this->pt,
					'c' => $this->c,
					'win' => $this->win,
					'areac' => $this->areac,
					'areawin' => $this->areawin,
					'rank' => $this->rank,
					'weapon' => $this->weapon,
					'weapons' => $this->weapons,
					'got' => $this->got,
					'mlist' => $this->mlist,
					'gadgets' => $this->gadgets,
					'auth' => $this->auth,
					'skinId' => $this->skinId,
				];

				if(is_array($oldData)){
					$data += $oldData;
				}

				$data = Database::$mysqli->real_escape_string(serialize($data));

				if(empty($db_id) or !$db_id){
					//初回
					$sql = 	"INSERT INTO userdata".
							"(name, data, date) ".
							"VALUES ".
							"('{$this->iusername}', '{$data}', now());";
					$saveResult = Database::$mysqli->query($sql);
					MainLogger::getLogger()->debug("初回保存".$this->iusername);
				}else{
					//二回目以降
					$sql = 	"UPDATE userdata SET ".
							"data = '{$data}', ".
							"date = CURRENT_TIMESTAMP";
					MainLogger::getLogger()->debug("二回目以降保存".$this->iusername);

					/*if($this->skinData){//Not Save
						$sql .= ", skindata = '".Database::$mysqli->real_escape_string($this->skinData)."'";
					}*/

					$sql .= " WHERE no = ".$db_id.";";
					$saveResult = Database::$mysqli->query($sql);
				}
			}

			if($saveResult){
				if($this->debug){
					MainLogger::getLogger()->debug($this->iusername." saved");
				}

				return true;
			}else{
				MainLogger::getLogger()->info("could not save to mysqli");
				MainLogger::getLogger()->error(Database::$mysqli->error);
			}
		}

		return false;
	}

	/**
	 * 新規のデータを取得
	 * @return array
	 */
	private function getNewdata(){
		$data = [
			'cheep' => "よろしく ！",
			'version' => PlayerData::DATA_VERSTION,
			'pt' => 100,
			'c' => 0,
			'win' => 0,
			'weapon' => 1,
			'weapons' => [1 => [0, 0, [],[1,2,3]], 5 => [0, 0, [],[1,2,3]]],
			'got' => [],
			'mlist' => [],
			'gadgets' => [1,2,3],
			'auth' => 0,
			'skinData' => "",
			'skinId' => "",
			'areac' => 0,
			'areawin' => 0,
			'rank' => 1000,
		];
		return $data;
	}

	/**
	 * データをアップグレード
	 */
	private function upgradeData(){
		if($this->version < PlayerData::DATA_VERSTION){
			switch($this->version){
				case 1:
					unset($this->kill);
				case 2:
					$this->weapons = [1 => [0, 0]];
				case 3:
					$weapons = [];
					foreach($this->weapons as $weapon => $d){
						$weapons[$weapon] = [$d[0], $d[1], []];
					}
					$this->weapons = $weapons;
				case 4:
					if(!isset($this->weapons[5])){
						$this->weapons[5] = [0, 0, []];
					}
				case 5:
					$this->areac = 0;
					$this->areawin = 0;
					$this->rank = 1000;
				case 6:
					$weapons = [];
					foreach($this->weapons as $weapon => $d){
						$weapons[$weapon] = [$d[0], $d[1], $d[2],[1,2,3]];
					}
					$this->weapons = $weapons;
				case 7:
					$this->gadgets = [1,2,3];
				case 8:
					$this->mlist = [];
					$this->version = PlayerData::DATA_VERSTION;
					break;
			}
		}

		return true;
	}

	public function getPlayer(){
		return $this->player;
	}

	public function setPlayer(Player $player){
		$this->player = $player;
	}

	public function getMuteList(){
		return $this->mlist;
	}

	public function isMuteList($name){
		if(in_array($name, $this->mlist)){
			return true;
		}
		return false;
	}

	public function addMuteList($name){
		if($this->isMuteList($name)){
			return false;
		}else{
			$this->mlist[] = $name;
			return true;
		}
	}

	public function removeMuteList($name){
		if(in_array($name, $this->mlist)){
			if(($n = array_search($name, $this->mlist)) !== false){
				array_splice($this->mlist, $n, 1);
			}
			return true;
		}else{
			return false;
		}
	}

	/**
	 * ガジェットを取得
	 * @param $wnum 武器ナンバー
	 * @return int[]
	 */
	public function getGadgets($wnum){
		return $this->weapons[$wnum][3];
	}

	public function getNowGadgets(){
		if($this->main->dev === 2){
			return [0, 0, 0];
		}
		return (isset($this->weapons[$this->weapon][3])) ? $this->weapons[$this->weapon][3] : [0, 0, 0];
	}

	public function setGadgets($wnum, $gads){
		return $this->weapons[$wnum][3] = $gads;
	}

	public function setGadget($wnum, $slot, $gadget){
		$this->weapons[$wnum][3][$slot] = $gadget;
	}

	public function getSaveGadget(){
		return (isset($this->gadgets[2])) ? $this->gadgets : [1, 2, 3];
	}

	public function setSaveGadget($gadgets){
		$this->gadgets = $gadgets;
	}

	/**
	 * ひとことを取得
	 * @return string
	 */
	public function getCheep(){
		return $this->cheep;
	}

	/**
	 * 所持しているポイントを取得
	 * @return int
	 */
	public function getPoint(){
		return $this->pt;
	}

	public function setPoint($pt){
		$this->pt = $pt;

		return true;
	}

	public function grantPoint($pt){
		$this->pt += $pt;

		return true;
	}

	/**
	 * ポイントを減らす
	 * ブキ購入に使用
	 * @param  int    $value 減らすポイント
	 * @return bool
	 */
	public function minusPoint($value){
		$point = $this->pt;
		$point -= $value;
		if($point < 0){
			return false;
		}
		$this->setPoint($point);

		return true;
	}

	/**
	 * レートを取得
	 * @return int
	 */
	public function getRank(){
		return $this->rank;
	}

	public function setRank($rank){
		$this->rank = $rank;
	}

	/**
	 * 勝利回数を取得
	 * @return int
	 */
	public function getWin(){
		return $this->win;
	}

	/**
	 * ガチエリア勝利回数を取得
	 * @return int
	 */
	public function getAreaWin(){
		return $this->areawin;
	}

	/**
	 * 勝利回数を+1増やす
	 */
	public function grantWin(){
		$this->win += 1;
	}

	/**
	 * エリア勝利回数を+1増やす
	 */
	public function grantAreaWin(){
		$this->areawin += 1;
	}

	/**
	 * 試合出場回数を取得
	 * @return int
	 */
	public function getCounter(){
		return $this->c;
	}

	/**
	 * ガチエリア出場回数を取得
	 * @return int
	 */
	public function getAreaCounter(){
		return $this->areac;
	}

	/**
	 * 試合出場回数を+1増やす
	 * @return int
	 */
	public function addCount(){
		$this->c += 1;
	}

	/**
	 * ガチエリア出場回数を+1増やす
	 * @return int
	 */
	public function addAreaCount(){
		$this->areac += 1;
	}

	/**
	 * 所持しているブキのデータを取得
	 * @return array
	 */
	public function getWeapons(){
		return $this->weapons;
	}

	/**
	 * 装備中のブキの番号を取得
	 * @return int
	 */
	public function getNowWeapon(){
		return $this->weapon;
	}

	/**
	 * 装備中のブキを変更
	 * @param  int    $w_num
	 * @return bool          装備できたか
	 */
	public function setNowWeapon($w_num){
		//if(isset($this->weapons[$w_num])){
			$this->weapon = $w_num;

			return true;
		//}
		//return false;
	}


	/**
	 * 持っているブキの中で一番高いレベルを取得
	 * @return int
	 */
	public function getMaxWeaponLevel(){
		$lv = 0;
		foreach($this->weapons as $w_num => $status){
			$lv = $status[0] > $lv ? $status[0] : $lv;
		}
		return $lv;
	}

	/**
	 * ブキを付与
	 * @param  int    $w_num ブキの番号
	 * @return bool
	 */
	public function giveWeapon(int $w_num){
		if($w_num){
			if(!isset($this->weapons[$w_num])){
				$this->weapons[$w_num] = [0, 0, [], [1,2,3]];

				ksort($this->weapons);
				return true;
			}
		}
		return false;
	}

	/**
	 * 装備中のブキのレベルを取得
	 * @return int
	 */
	public function getNowWeaponLevel(){
		$now = $this->weapon;
		return $this->weapons[$now][0] ?? 0;
	}

	/**
	 * 装備中のブキの経験値を取得
	 * @return int
	 */
	public function getNowWeaponExp(){
		$now = $this->weapon;
		return $this->weapons[$now][1] ?? 0;
	}

	/**
	 * ポイントを経験値に交換
	 * @param  int    $w_num
	 * @param  mixed  $exp
	 * @return mixed
	 */
	public function pointToExp($w_num, $exp){
		$pt = $exp * 3;
		if($pt <= $this->pt){
			$this->minusPoint($pt);
			$this->giveExp($w_num, $exp);

			return $exp;
		}

		return false;
	}

	/**
	 * 経験値を付与
	 * @param  int $exp
	 * @return int | false レベルアップした場合そのレベルの数値を返す
	 */
	public function giveExp($exp){
		$result = false;

		$w_num = $this->getNowWeapon();
		$nowlv = $this->weapons[$w_num][0];
		$newexp = $this->weapons[$w_num][1] + $exp;

		$newlv = $nowlv;//最終的にあげるレベルが代入される
		$try_newlv = $nowlv + 1;

		while((($try_newlv * 2) ** 2) <= $newexp){
			$result = ++$newlv;
			++$try_newlv;
		}
		
		if($exp < 0) $newlv = floor(sqrt($newexp/4));
		
		$this->weapons[$w_num][0] = $newlv;
		$this->weapons[$w_num][1] = $newexp;
		return $result;
	}

	public function saveGet($a){
		if(isset($this->got[$a])){
			$this->got[$a] = 1;

			return true;//まだゲットしてない
		}

		return false;//もうゲットしてる
	}

	/**
	 * スキンデータを保存
	 * @param  string $data
	 */
	public function saveSkin($data){
		$this->skinData = $data;
	}

	/**
	 * スキンデータを取得
	 * @return string
	 */
	public function getSkin(){
		return $this->skinData;
	}

	/**
	 * スキンIDを保存
	 * @param  string $name
	 */
	public function saveSkinId($id){
		$this->skinId = $id;
	}

	/**
	 * スキンIDを取得
	 * @return string
	 */
	public function getSkinId(){
		return $this->skinId;
	}

	/**
	 * 呪文の認証状態を保存
	 * @return int
	 */
	public function saveAuth($value){
		$this->auth = $value;
	}

	/**
	 * 呪文認証済みか取得
	 * @return int
	 */
	public function getAuth(){
		return $this->auth;
	}

	/**
	 * インク消費量を取得
	 */
	public function getInkConsumption(){
		$cons = $this->inkConsumption;
		$cons *= Gadget::getCorrection($this->player, Gadget::RATE_MAIN);
		return $cons;
	}

	/**
	 * インク消費量を設定
	 */
	public function setInkConsumption($value){
		$this->inkConsumption = $value;
	}

	/**
	 * 現在のインク残量を取得
	 */
	public function getInk(){
		return $this->tank[0];
	}

	/**
	 * インク残量を設定
	 */
	public function setInk($amount){
		if($amount < 0){
			$amount = 0;
		}
		$tank = $this->tank[1];
		$amount = ($amount < $tank) ? $amount : $tank;//tankの量を超えないように
		$this->tank[0] = $amount;
		return true;
	}

	/**
	 * インクタンクの量を取得
	 */
	public function getInkTank(){
		return $this->tank[1];
	}

	/**
	 * インクのタンク量を設定
	 */
	public function setInkTank($amount){
		if($amount < 0){
			$amount = 0;
		}
		$this->tank[1] = $amount;
	}

	public function getPlusTank(){
		return $this->tank[2];
	}

	/**
	 * インクを100%に回復
	 */
	public function fillInk(){
		$maxAmount = $this->getInkTank();
		$this->setInk($maxAmount);
	}

	/**
	 * インクを回復
	 * @param  int    $amount 回復する量
	 */
	public function stockInk($amount){
		$now = $this->getInk();
		$saved = $now + $amount;
		return $this->setInk($saved);
	}

	/**
	 * インクを消費
	 * @param  int    $amount 消費する量
	 */
	public function consumeInk($amount){
		$now = $this->getInk();
		$saved = $now - $amount;
		return $this->setInk($saved);
	}

	/**
	 * インクを消費できるかどうか
	 * @param  int    $amount 消費量
	 * @return bool
	 */
	public function canConsumeInk($amount){
		$value = $this->getInk() - $amount;
		return (0 <= $value);
	}

	/**
	 * Rateを取得(ブキを使えるか(撃てるか)どうか)
	 * @return bool
	 */
	public function getRate(){
		$now = microtime(true);
		return $this->rate <= $now;
	}

	/**
	 * クールタイムにする
	 */
	public function setRate($i = false){
		static $weaponsClassExist;

		if($weaponsClassExist === null){
			$weaponsClassExist = isset($this->main->w);
		}
		if(!$weaponsClassExist){
			return false;
		}
		if($i === false){
			$now = microtime(true);
			$tick = $this->main->w->getWeaponData($this->weapon)[5];
			$this->rate = $now + $this->main->w->getWeaponData($this->weapon)[5] / 20;
			return true;
		}else{
			$now = microtime(true);
			$tick = $i;
			$this->rate = $now + $i / 20;
			return true;
		}
	}

	/**
	 * Rateを打てる状態に
	 */
	public function resetRate(){
		$this->rate = microtime(true);
		return true;
	}

	/**
	 * 塗った量を取得
	 * @return int
	 */
	public function getPaintAmount(){
		return $this->paintAmount;
	}

	/**
	 * 塗った量を変更
	 * @param int $value
	 */
	public function setPaintAmount($value){
		return $this->paintAmount = $value;
	}

	/**
	 * 塗った量を追加
	 * @param int $value
	 */
	public function addPaintAmount($value){
		$this->paintAmount += $value;
	}

	/**
	 * 羊毛を塗る色を取得
	 * @return int
	 */
	public function getColor(){
		return $this->color;
	}

	/**
	 * 羊毛を塗る色を設定
	 * @param  int $color
	 */
	public function setColor($color){
		$this->color = $color;
	}

	/**
	 * 現在いるフィールド番号を取得
	 * @return int
	 */
	public function getFieldNum(){
		return $this->fieldNum;
	}

	/**
	 * フィールド番号を設定
	 * @param int $num
	 */
	public function setFieldNum($num){
		$this->fieldNum = $num;
	}

	/**
	 * ブキを購入
	 * @param  int $w_num
	 * @return bool       購入できたかどうか
	 */
	public function BuyWeapon($w_num){
		static $classExist;

		if($classExist === null){
			$classExist = isset($this->main->w, $this->main->lang, /*$this->main->itemCase,*/ $this->main->itemselect);
		}
		if(!$classExist){
			return false;
		}

		if(($weaponData = $this->main->w->getWeaponData($w_num)) != null){
			if(!isset($this->getWeapons()[$w_num])){
				$nowlv = $this->getMaxWeaponLevel();
				$needlv = $weaponData[4][1];
				//レベルが足りているか
				if($needlv <= $nowlv){
					if($this->minusPoint($weaponData[4][0])){
						$weaponName = $this->main->w->getweaponName($w_num);
						$this->giveWeapon($w_num);
						$this->player->sendMessage($this->main->lang->translateString("weapon.buy.success", [$weaponName]));
						//$this->main->itemCase->reset($this->player);
						$this->main->shop->reset($this->player);
						$this->main->itemselect->reset($this->player);

						return true;
					}else{
						$nowpt = $this->getPoint();
						$weaponpt = $weaponData[4][0];
						$pt = $weaponpt - $nowpt;
						$this->player->sendMessage($this->main->lang->translateString("weapon.buy.failure.pointShortage", [$pt]));
					}
				}else{
					$this->player->sendMessage($this->main->lang->translateString("weapon.buy.failure.levelShortage", [$weaponData[0], $needlv]));
				}
			}else{
				$this->player->sendMessage($this->main->lang->translateString("weapon.buy.failure.already", [$weaponData[0]]));
			}
		}

		return false;
	}
}