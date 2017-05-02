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

/*
	ゲーム中のプレイヤーに関する成績は全てここに

	set計では必ずstrtolowerいれること
*/

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\MainLogger;

class Account{
	//private static $api = null;
	private $data = [], $offlineData = [], $laterUnsetData = [];
	private $ability;
	private static $instance = null;

	public function __construct($main){
		self::$instance = $this;
		$this->main = $main;
		/*
		//何かのために残しておこう
		$json_dataFolder = Server::getInstance()->getPluginManager()->getPlugin("SplatoonMCPE")->getDataFolder()."/json/";
		if(!file_exists($json_dataFolder)) {
			@mkdir($json_dataFolder, 0744, true);
		}
		define('DIR',$json_dataFolder);
		*/
		$this->offlineData['-+*unknown*+-'] = new OfflinePlayerData("", $main);
	}

	public static function getInstance(){
		return self::$instance;
	}


	/*
	//何かのために残しておこう
	public function allintoMysql(){
		echo "called ".DIR;
		foreach(glob(DIR."*.json") as $filename){
			$jsondata = file_get_contents($filename);
			if($ar_data = json_decode($jsondata, true)){
				if(isset($ar_data['pt']) and $ar_data['pt'] != 100){
					$name = basename($filename, '.json');
					$this->saveData($name, $ar_data);
					echo "!";
				}
			}
		}
	}
	*/

	/**
	 * 読み込まれているデータを取得
	 * @return array
	 */
	public function getLoadedAllData(){
		return $this->data;
	}

	/**
	 * データが読み込まれているかどうか
	 * @param  string $user
	 * @return bool
	 */
	public function isLoaded($user){
		$user = strtolower($user);

		return isset($this->data[$user]);
	}

	/**
	 * アカウントデータを読み込む
	 * @param  Player | string $player
	 * @return bool
	 */
	public function loadData(Player $player = null){
		$user = strtolower($player->getName());

		if(isset($this->laterUnsetData[$user], $this->data[$user])){
			$this->data[$user]->setPlayer($player);
			$result = $this->data[$user]->reload();
			if($result === false){
				$player->notConnect = true;
			}
		}

		unset($this->laterUnsetData[$user]);

		if(isset($this->data[$user])){
			return true;
		}

		$this->data[$user] = new PlayerData($player, $this->main);
		if(!Database::isConnected()){
			return false;
		}
		return true;
	}

	/**
	 * データを取得
	 * @param  string | Player $user
	 * @return array
	 */
	public function getData($user){
		if($user instanceof Player){
			$player = $user;
			$user = strtolower($player->getName());
		}

		$user = strtolower($user);
		if(!isset($this->data[$user])){
			if(isset($player)){
				$this->loadData($player);
			}else{
				//throw new \RuntimeException("\"{$user}\" data is not loaded");
				MainLogger::getLogger()->warning("\"{$user}\" data is not loaded");
				$trace = debug_backtrace();
				//$ref = new \ReflectionClass($trace[0]);
				MainLogger::getLogger()->debug($trace[0]['file']."(line:".$trace[0]['line'].")");

				return $this->offlineData['-+*unknown*+-'];
			}
		}

		return $this->data[$user];
	}

	/**
	 * オフラインのデータを取得
	 * @param  string $user
	 * @return array
	 */
	public function getOfflineData($user){
		$user = strtolower($user);
		$now = time();
		if(!isset($this->offlineData[$user]) || $now - $this->offlineData[$user][1] > 120){
			$this->offlineData[$user] = [new OfflinePlayerData($user, $this->main), $now];
		}

		return $this->offlineData[$user][0];
	}

	/**
	 * データをセーブ
	 * @param  string  $user
	 * @param  bool    $unset
	 * @param  bool    $unsetNow 今すぐunsetするかどうか(falseの場合はunsetUnnecessaryData実行時に)
	 * @return bool
	 */
	public function saveData($user, $unset = true, $unsetNow = false){
		$user = strtolower($user);
		if(!isset($this->data[$user])){
			// throw new RuntimeException("\"{$user}\" data is not loaded");
			MainLogger::getLogger()->warning("\"{$user}\" data is not loaded");
			$trace = debug_backtrace();
			MainLogger::getLogger()->debug($trace[0]['file']."(line:".$trace[0]['line'].")");

			return null;
		}
		$save = $this->data[$user]->save();
		if($unset){
			if($unsetNow){
				unset($this->data[$user]);
			}else{
				$this->laterUnsetData[$user] = time();
			}
		}
		return $save;
	}

	public function unsetUnnecessaryData($time = null){
		foreach($this->laterUnsetData as $user => $time){
			$this->data[$user]->remove();
			unset($this->data[$user], $this->laterUnsetData[$user]);
		}
		$this->laterUnsetData = [];
	}

	public function unsetData($user){
		$user = strtolower($user);
		unset($this->data[$user]);
	}

	/**
	 * 読み込まれているプレイヤーのデータを保存
	 * @param  boolean $unset
	 * @return int
	 */
	public function saveAll($unset = false){
		$this->unsetUnnecessaryData();
		$count = 0;
		foreach($this->data as $user => $data){
			$save = $data->save();
			if($unset){
				unset($this->data[$user]);
			}
			if($save){
				$count++;
			}
		}

		return $count;
	}

	/**
	 * スキンデータの更新
	 */
	public function skinUpdate(){
		/*
		Database::getInstance()->MySQLConnect();
		$players = [];
		$sql = "SELECT skintxt,name FROM user_data";
		if($result = Database::$mysqli->query($sql)){
			while($row = $result->fetch_assoc()){
				$players[$row['name']] = $row['skintxt'];
			}
		}
		$hit = 0;
		$users = 0;
		$dir = "/home/splaturn/s/skintxt/";
		foreach(glob($dir."*.skintxt") as $filename){
			$users++;
			$user = pathinfo($filename, PATHINFO_FILENAME);
			$skin = file_get_contents($filename);
			$skin = Database::$mysqli->real_escape_string($skin);
			$user = strtolower($user);
			if(isset($players[$user]) && $players[$user] == null){
				$sql = "UPDATE user_data SET skintxt = '".$skin."' WHERE name = '".$user."';";
				$result = Database::$mysqli->query($sql);
				$hit++;
			}
		}
		echo $hit."/".$users."\n";*/

		/*$max = 0;
		$players = [];
		$skinsize = 64 * 32 * 4;
		$sql = "SELECT name, skintxt,LENGTH(skintxt) FROM user_data WHERE $skinsize > LENGTH(skintxt) AND LENGTH(skintxt) > 0;";
		//$sql2 = "";
		if($result = Database::$mysqli->query($sql)){
			while($row = $result->fetch_assoc()){
				$name = strtolower($row['name']);
				$max++;
				$players[$name] = $row['LENGTH(skintxt)'];
				//$sql2 .= "UPDATE user_data SET skintxt = '' WHERE name = '$name';";
			}
		}
		print_r($players);*/
	}

	/**
	 * ランキングデータの更新
	 * @param  string $user
	 * @param  int    $wno
	 * @param  int    $paint
	 * @return bool
	 */
	public function savePaint($user, $wno, $paint){
		Database::getInstance()->MySQLConnect();

		//echo $user.": w".$wno." ".$paint." \n";
		$sql = "INSERT INTO ranking_paint ".
			  "(name, w".$wno.") ".
			"VALUES ".
			  "('".$user."', ".$paint.") ".
			"ON DUPLICATE KEY UPDATE ".
			  "w".$wno." = w".$wno." + ".$paint.";";
		Database::$mysqli->query($sql);

		$sql = "INSERT INTO ranking_gamecount ".
			  "(name, w".$wno.") ".
			"VALUES ".
			  "('".$user."', 1) ".
			"ON DUPLICATE KEY UPDATE ".
			  "w".$wno." = w".$wno." + 1;";
		Database::$mysqli->query($sql);

		$sql = "INSERT INTO ranking_maxpaint ".
			  "(name, w".$wno.") ".
			"VALUES ".
			  "('".$user."', ".$paint.") ".
			"ON DUPLICATE KEY UPDATE ".
			  "w".$wno." = IF(w".$wno." > VALUES(w".$wno."), w".$wno.", VALUES(w".$wno."));";
		Database::$mysqli->query($sql);

		return true;
	}
}