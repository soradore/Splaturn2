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
鯖データは向こうで保管
ステータスはここで保管
*/

//0906
use pocketmine\Server;
use pocketmine\utils\MainLogger;
use pocketmine\network\Network;
use pocketmine\network\protocol\TransferPacket;

class StatusUnit{

	const SERVER_ONLINE     = 1;
	const SERVER_OFFLINE    = 2;
	const SERVER_PREPAREING = 3;
	const SERVER_EMERGENCY  = 4;

	const PUNISH_WARN = 3;
	const PUNISH_BAN  = 4;

	//StatusUnitのログイン制限のみで使用
	const STAR_OPC = 0b1000000;
	const STAR_OP  = 0b0100000;
	const STAR_BEH = 0b0010000;
	const STAR_DEV = 0b0001000;
	const STAR_MAP = 0b0000100;
	const STAR_ILU = 0b0000010;
	const STAR_MOV = 0b0000001;


	public  $sno;
	public  $sname;
	//public  $TeleportedPlayers = [];
	public  $loginRestriction = null;
	private $stages = [];
	private $servers = [];
	private $stars = [];
	private $starsBin = [];
	private $ops = [];
	private $devs = [];
	private $maps = [];
	private $movs = [];
	private $punish = [];
	private $ranks = [];
	private $warns = [];
	private $count = 0;
	private $m = null;

	public function __construct($m = null){
		$loadSNO_result = $this->loadSNO();
		$this->setOnline(Server::getInstance()->getMaxPlayers());
		$this->confirmStars();
		$this->confirmServers();
		$this->confirmPunishment();
		$this->confirmRanks();
		$this->confirmBattleField();
		$this->m = $m;
		if($loadSNO_result){
			$this->sname = $this->getThisServerName();
		}
	}

	public function setDataIntoDB($name, $data){
		if(Database::isConnected()){
			$data = serialize($data);
			$sql = "INSERT INTO savedata (name, data) VALUES ('".$name."', '".$data."') ON DUPLICATE KEY UPDATE data = '".$data."';";
			$result = Database::$mysqli->query($sql);
			return $result;
		}
	}

	public function show(){
		/*
		print_r($this->servers);
		print_r($this->stars);
		print_r($this->punish);
		print_r($this->warns);
		print_r($this->ranks);
		*/
		$out = 
			"servers\n".
			var_export($this->servers, true)."\n".
			"stars\n".
			var_export($this->stars, true)."\n".
			"punish\n".
			var_export($this->punish, true)."\n".
			"warns\n".
			var_export($this->warns, true)."\n".
			"ranks\n".
			var_export($this->ranks, true)."\n".
			"stages\n".
			var_export($this->stages, true);
		return $out;
	}

	public function refleshData(){
		$this->confirmServers();
		$this->confirmStars();
		$this->confirmPunishment();
		$this->confirmRanks();
		$this->confirmBattleField();
		$this->setOnline(Server::getInstance()->getMaxPlayers());
	}

	public function reloadServerStatus(){
		if($this->count %  3 == 0){
			$this->confirmServers();
			$this->confirmPunishment();
		}
		if($this->count % 12 == 0){
			$this->confirmStars();
			$this->confirmRanks();
			$this->confirmBattleField();
		}
		if($this->count % 24 == 0){
			$this->setOnline(Server::getInstance()->getMaxPlayers());
		}
		$this->count++;
	}

	/****
	@
	@ オンラインオフライン
	@
	****/

	/**
	 * プラグイン読み込み時にリセット
	 * @return bool
	 */
	public function resetOnlineStat(){
		Database::getInstance()->MySQLConnect();
		if(Database::isConnected() && $this->sno){
			$sql = "DELETE FROM onlinestatus_user WHERE s = '".$this->sno."';";
			$result = Database::$mysqli->query($sql);

			foreach(Server::getInstance()->getOnlinePlayers() as $player){
				$sql = "INSERT INTO onlinestatus_user (name, s) VALUES ('".strtolower($player->getName())."', '".$this->sno."');";
				$result = Database::$mysqli->query($sql);
			}
			return $result;
		}
		return false;
	}

	//trueをいれたらonlineに、falseいれたらofflineに
	public function setOnlineStat($name, $bool){
		Database::getInstance()->MySQLConnect();
		if(Database::isConnected() && $this->sno){
			$name = strtolower($name);
			if($bool){
				$sql = "INSERT INTO onlinestatus_user (name, s) VALUES ('".$name."', '".$this->sno."');";
			}else{
				$sql = "DELETE FROM onlinestatus_user WHERE name = '".$name."';";
			}
			$result = Database::$mysqli->query($sql);
			return $result;
		}
		return false;
	}

	//鯖落ちた時よう
	public function removeAllFromOnlineStat(){
		Database::getInstance()->MySQLConnect();
		if(Database::isConnected() && $this->sno){
			$sql = "DELETE FROM onlinestatus_user WHERE s = '".$this->sno."';";
			$result = Database::$mysqli->query($sql);
			return $result;
		}
		return false;
	}

	/****
	@
	@ サーバーナンバー & ログイン制限
	@
	****/

	function inputJSONAsArray($filename, $dir = false){
		if(!$dir) $dir = "./";
		//echo $dir;
		$output = "";
		$json_data = @file_get_contents($dir.$filename);
		if($json_data){
			$array_data = json_decode($json_data, true);
			return $array_data;
		}
		return false;
	}

	function outputJSONbyArray($array_data, $filename, $dir = "./", $escape = false){
		if(!$dir) $dir = $this->dir;
		if($escape){
			$json_data = json_encode($array_data, JSON_UNESCAPED_UNICODE);
		}else{
			$json_data = json_encode($array_data);
		}
		$result = file_put_contents($dir.$filename, $json_data);
		return $result == true;
	}

	public function loadSNO(){
		$file = "Splat_Settings.json";
		$data = $this->inputJSONAsArray($file);
		if(!empty($data['sno'])){
			$this->sno = $data['sno'];
			return true;
		}
		return false;
	}

	public function writeSNO($sno){
		$file = "Splat_Settings.json";
		$array_data = $this->inputJSONAsArray($file);
		$array_data['sno'] = $sno;
		$json_data = json_encode($array_data);
		$result = file_put_contents("./".$file, $json_data);
		if($result){
			$this->sno = $sno;
			return true;
		}
		return false;
	}

	public function setLoginRestriction($textColor = "§b", array $permData = null){
		if($permData === null){
			$file = "Splat_Settings.json";
			$data = $this->inputJSONAsArray($file);
			if(isset($data['automode'])){
				$data['loginrestriction'] = $data['automode'];
				unset($data['automode']);
				$this->outputJSONbyArray($data, $file);
			}
			if($this->m !== null && isset($data['loginrestriction'])){
				$permData = (array) $data['loginrestriction'];
			}else{
				return false;
			}
		}

		$value = 0;
		foreach($permData as $perm){
			switch(strtolower($perm)){
				case "beh":
					$value |= self::STAR_BEH;
				case "op":
					$value |= self::STAR_OP;
				case "opc":
					$value |= self::STAR_OPC;
					break;
				case "dev":
					$value |= self::STAR_DEV;
					break;
				case "map":
					$value |= self::STAR_MAP;
					break;
				case "op":
					$value |= self::STAR_OP;
					break;
				case "ilu":
					$value |= self::STAR_ILU;
					break;
				case "mov":
					$value |= self::STAR_MOV;
					break;
			}
		}

		$perms = [];
		if($value & self::STAR_OPC){
			$perms[] = "§copc";
		}
		if($value & self::STAR_DEV){
			$perms[] = "§6dev";
		}
		if($value & self::STAR_MAP){
			$perms[] = "§emap";
		}
		if($value & self::STAR_OP){
			$perms[] = "§aop";
		}
		if($value & self::STAR_BEH){
			$perms[] = "§bbeh";
		}
		if($value & self::STAR_ILU){
			$perms[] = "§dilu";
		}
		if($value & self::STAR_MOV){
			$perms[] = "§7mov";
		}
		$permsText = implode($textColor.", ", $perms).$textColor;
		

		if($this->loginRestriction === null && $value){
			MainLogger::getLogger()->notice($this->m->lang->translateString("loginRestriction", [$permsText]));
		}
		$this->loginRestriction = $value;
		foreach(Server::getInstance()->getOnlinePlayers() as $player){
			$check = $this->checkLoginRestriction($player->getName());
			if(!$check['result']){
				$msg =  $this->m->lang->translateString("loginRestriction.kick");
				$player->kick($check['message'], false);
			}
		}
		return $permsText;
	}

	public function getLanguage(){
		$file = "Splat_Settings.json";
		$data = $this->inputJSONAsArray($file);
		return (!empty($data['lang'])) ? $data['lang'] : null;
	}

	public function setLanguage($lang){
		$file = "Splat_Settings.json";
		$array_data = $this->inputJSONAsArray($file);
		$array_data['lang'] = $lang;
		$json_data = json_encode($array_data);
		$result = file_put_contents("./".$file, $json_data);
		return $result == true;
	}

	/****
	@
	@ サーバーステータス関係
	@
	****/

	public function getSName(){
		return $this->servers[$this->sno]['name'] ?? false;
	}

	/**
	 * サーバーがオンラインかどうか
	 * @param  string  $s_no
	 * @return boolean
	 */
	public function getServerStatus($s_no){
		return (isset($this->servers[$s_no])) ? $this->servers[$s_no]['stat'] == self::SERVER_ONLINE : false;
	}

	/**
	 * 全サーバーにいるプレイヤーの人数を取得
	 * @return int
	 */
	public function getServerAllPlayerNumNow(){
		$cnt = 0;
		foreach($this->servers as $data){
			$cnt += $data['nowplayer'];
		}
		return $cnt;
	}

	/**
	 * 全サーバーのログインできる合計人数を取得
	 * @return int
	 */
	public function getServerAllPlayerNumMax(){
		$cnt = 0;
		foreach($this->servers as $data){
			if($data['stat'] == self::SERVER_ONLINE){
				$cnt += $data['maxplayer'];
			}
		}
		return $cnt;
	}

	/**
	 * サーバーIP,Portを取得
	 * @param  string $s_no
	 * @return array | false [IP, Port]
	 */
	public function getServerAP($s_no){
		return (isset($this->servers[$s_no]['address'])) ? [$this->servers[$s_no]['address'], $this->servers[$s_no]['port']] : false;
	}

	/**
	 * サーバー名を取得
	 * @param  string         $s_no
	 * @return string | false
	 */
	public function getServerName($s_no){
		return $this->servers[$s_no]['name'] ?? false;
	}

	public function getServerClosedReason($s_no){
		//return (isset($this->servers[$s_no][3])) ? $this->servers[$s_no][3] : false;
		return "";
	}

	/**
	 * サーバーのオンラインなどの通知をするかどうか
	 * @param  string        $s_no
	 * @return boolean | int
	 */
	public function getServerOnlinenNotification($s_no){
		return $this->servers[$s_no]['notify'] ?? false;
	}

	public function getThisServerName(){
		return ($this->sno && isset($this->servers[$this->sno]['name'])) ? $this->servers[$this->sno]['name'] : "(NO_NAME)";
	}

	/**
	 * サーバー状況一覧のテキストを取得
	 * @return string
	 */
	public function getServersStatusTxt(){
		$out = "";
		foreach($this->servers as $s_no => $d){
			$sname = "§e".str_pad($this->getServerName($s_no), 12, " ", STR_PAD_LEFT)." : ";
			$data = $this->servers[$s_no];
			$stat = "";
			switch($data['stat']){
				case self::SERVER_ONLINE:     $stat = "§aOnline"; break;
				case self::SERVER_PREPAREING: $stat = "§6Prepareing"; break;
				case self::SERVER_EMERGENCY:  $stat = "§eEMERGENCY"; break;
				case self::SERVER_OFFLINE:    $stat = "§cOffline"; break;
			}
			$out .= $sname.$stat."\n";
		}
		return $out;
	}

	/**
	 * サーバー状況を取得
	 * @param  string $s_no
	 * @return string
	 */
	public function getServerStatusTxt($s_no){
		if(isset($this->servers[$s_no])){
			$d = $this->servers[$s_no];
			$stat = "";
			if($this->getServerStatusIsFull($s_no)){
				$stat = "§b";
			}else{
				switch($d['stat']){
					case self::SERVER_ONLINE:     $stat = "§a"; break;
					case self::SERVER_OFFLINE:    $stat = "§c"; break;
					case self::SERVER_EMERGENCY:  $stat = "§e"; break;
					case self::SERVER_PREPAREING:
						$stat = "§6";
						$d['nowplayer'] = "-";
						$d['maxplayer'] = "-";
						break;
				}
			}
			return " ".$stat.$this->getServerName($s_no)."\n".
				"(".$d['nowplayer']." / ".$d['maxplayer'].")";
		}
		return false;
	}

	public function getServerStatusIsFull($s_no){
		if(isset($this->servers[$s_no])){
			$d = $this->servers[$s_no];
			return $d['maxplayer'] != 0 and $d['maxplayer'] - $d['nowplayer'] <= 0;
		}
		return false;
	}

	/**
	 * ぱにいをする
	 * @param  string           $name       対象者
	 * @param  string           $propounder 報告者
	 * @param  int              $judge      判断
	 * @param  string           $com        理由
	 * @return array                        ぱにいできたかどうかなどの情報
	 */
	public function sendPunishment($name, $propounder, $judge, $com = ""){
		$ac_url = 	"http://splaturn.net/lib/pmmp/punish_do.php?".
					"name=".$name."&prop=".$propounder."&judge=".$judge."&com=".urlencode($com);
		$result = $this->curlUnit($ac_url);
		if($result){
			/*
				$data = [
					120,//番号
					"ban処理を完了しました"
				]
			*/
			$data = json_decode($result, true);
			$sendResult = false;
			$msg = "";
			if(!empty($data)){
				if(!$data[0]){
					$msg = "処理中にエラーが発生しました: ".$data[1];
				}else{
					$msg = $data[1]."(http://splaturn.net/punish?no=".$data[0]." で確認可能)";
					$sendResult = true;
				}
			}else{
				//echo "ぱにい不正な受信データ:".$result;
				MainLogger::getLogger()->error("sendPunishment: ぱにいデータの送信に失敗しました (".$result.")");
				$msg = "鯖側でエラーが発生したようです(処理は終了しているかもしれませんのでサイトで確認をお願いします)";
			}
			return [$sendResult, $msg];
		}else{
			MainLogger::getLogger()->error("sendPunishment: ぱにいデータの送信に失敗しました");
			return [false, "送信失敗"];
		}
	}

	/**
	 * ぱにいされたユーザーのデータを取得
	 * @return array | false
	 */
	public function getPunishment(){
		Database::getInstance()->MySQLConnect();
		if(Database::isConnected()){
			$sql = "SELECT name,puno,judge FROM punishment WHERE judge = ".self::PUNISH_BAN." or judge = ".self::PUNISH_WARN.";";
			$result = Database::$mysqli->query($sql);
			if($result){
				$data = [];
				while($row = $result->fetch_assoc()){
					$judge = $row['judge'];
					$name = $row['name'];
					$puno = $row['puno'];
					if(!isset($data[$name])){
						$data[$name] = [$puno, $judge, 0];
					}else{
						//warnの重複用
						if($data[$name][1] < $judge){
							$data[$name][1] = $judge;
						}
					}
					if($judge == self::PUNISH_WARN){
						$data[$name][2] += 1;
					}
				}
				$result->free();
				return $data;
			}
		}
		return false;
	}

	/**
	 * ぱにいデータ(BAN,warn)のデータを更新
	 * @return string データを更新できたかどうか
	 */
	public function confirmPunishment(){
		$warn_mark = base64_decode("4pqg");
		$warn_limit = 3;//この数値以上であればbanと同じ扱いに
		$data = $this->getPunishment();
		/*
		$data = [
			'username' => [ぱにい番号, ジャッジ(3=warn, 4=ban), warn回数]
		];
		 */
		if($data){
			$newpunish = [];
			$newwarns = [];
			foreach($data as $name => $data){
				$name = strtolower($name);
				if(($data[1] == self::PUNISH_WARN and $data[2] >= $warn_limit) or $data[1] == self::PUNISH_BAN){
					//BANされている場合
					$newpunish[$name] = $data;
					continue;
				}
				if($data[1] == self::PUNISH_WARN){
					//Warnされている(警告の数がBANになる数を超えていない場合)
					$txt = str_repeat($warn_mark, $data[2]);//警告マーク
					$newwarns[$name] = $txt;
				}
			}
			$this->punish = $newpunish;
			$this->warns = $newwarns;
			if($this->m !== null){
				foreach(Server::getInstance()->getOnlinePlayers() as $player){
					$punish = $this->hasPunished($player);
					if(!$punish['result']){
						$player->kick($punish['message']);
					}else{
						$this->m->changeName($player);
					}
				}
			}
			MainLogger::getLogger()->debug("ぱにいデータ更新OK");
			return true;
		}else{
			MainLogger::getLogger()->notice("ぱにいデータがありません");
			return false;
		}
	}

	public function inputData($filename){
		Database::getInstance()->MySQLConnect();
		$data = [];
		if(Database::isConnected()){
			$sql = "SELECT data FROM savedata WHERE name = '".$filename."';";
			if($result = Database::$mysqli->query($sql)){
				while($row = $result->fetch_assoc()){
					$data = @unserialize($row['data']);
					if($data === false){
						$data = "";
					}
				}
				$result->free();
			}
		}
		return $data;
	}

	/**
	 * バトル情報をセーブ
	 * @return bool
	 */
	public function saveBattleData($mode, $stage, $data){
		Database::getInstance()->MySQLConnect();
		$data = Database::$mysqli->real_escape_string(serialize($data));
		$name = $this->getSName();
		if(Database::isConnected()){
			$sql = 	"INSERT INTO battledata".
							"(name, mode, stage, data, date) ".
							"VALUES ".
							"('{$name}', '{$mode}', '{$stage}', '{$data}', now());";
			$saveResult = Database::$mysqli->query($sql);
			if($saveResult){
				//if($this->debug){
					MainLogger::getLogger()->debug("battledata saved");
				//}
				return true;
			}else{
				MainLogger::getLogger()->info("could not save to mysqli");
				MainLogger::getLogger()->error(Database::$mysqli->error);
			}
		}
		return false;
	}


	/**
	 * ステージ情報の取得
	 * @return bool
	 */
	public function confirmBattleField(){
		$data = $this->inputData("3390");
		if($data){
			/*
				[
					"h" => [10,11],
					"s" => [
						1 => 50,
						2 => 50,
					], 
				],
				[
					"h" => [12,13],
					"s" => [
						12 => 60,
						11 => 40,
					], 
				],
			*/
			if(!empty($data)){
				$this->stages = $data;
				MainLogger::getLogger()->debug("ステージデータ更新OK");
				return true;
			}
		}
		MainLogger::getLogger()->error("ステージデータ取得失敗");
		return false;
	}

	/**
	 * サーバーデータを更新
	 * @return bool 更新できたかどうか
	 */
	public function confirmServers(){
		Database::getInstance()->MySQLConnect();
		if(Database::isConnected()){
			$sql = "SELECT * FROM onlinestatus_server ORDER BY n ASC;";
			$result = Database::$mysqli->query($sql);
			if($result){
				$d_prev = $this->servers;
				$servers = [];
				while($row = $result->fetch_assoc()){
					/*
					[
						'n'				 => "1",
						'sno'			 => "cent",
						'stat'			 => "1",
						'nowplayer'		 => "0",
						'maxplayer'		 => "50",
						'address'		 => "133.130.53.64",
						'port'			 => "19132",
						'name'			 => "CentralServer",
						'lastupdated'	 => "Y-m-d H:i:s",
						'notify'		 => "1",
					]
					*/
					$s = $row['sno'];
					unset($row['sno']);
					$servers[$s] = $row;
					if(isset($d_prev[$s])){
						switch($row['stat']){
							case self::SERVER_ONLINE:     $stat = "§aOnline"; break;
							case self::SERVER_PREPAREING: $stat = "§6Prepareing"; break;
							case self::SERVER_EMERGENCY:  $stat = "§eEMERGENCY"; break;
							case self::SERVER_OFFLINE:    $stat = "§cOffline"; break;
						}
						if($row['notify'] and $d_prev[$s]['stat'] !== $row['stat']){
							//$b_sname = $this->getServerName($s);
							$b_sname = $row['name'];
							$text = ($this->m !== null) ? $this->m->lang->translateString("serverStatus.update", [$b_sname,$stat]) : "§6≫ ".$b_sname."が".$stat."§6になりました！";
							Server::getInstance()->broadcastMessage($text);
						}
					}
				}
				$result->free();
				$this->servers = $servers;
				MainLogger::getLogger()->debug("サーバーデータ更新OK");
				return true;
			}else{
				MainLogger::getLogger()->error("サーバーデータ取得失敗");
			}
		}
		return false;
	}

	/**
	 * 権限データ更新
	 * @return bool データを更新できたかどうか
	 */
	public function confirmStars(){
		$data = $this->inputData("starplayers");
		if(!empty($data)){
			$newstars = [];
			$newstarsBin = [];
			$ops = [];
			$devs = [];
			$maps = [];
			$movs = [];
			foreach($data as $name => $vir){
				$name = strtolower($name);
				$txt = "";
				$bin = 0;
				foreach($vir as $title){
					switch($title){
						case 'beh':
							$txt .= "§b★";
							$ops[$name] = 0;
							$bin |= self::STAR_BEH;
							break;
						case 'op':
							$txt .= "§a★";
							$ops[$name] = 1;
							$bin |= self::STAR_OP;
							break;
						case 'nop':
							$txt .= "";
							$ops[$name] = 1;
							$bin |= self::STAR_OP;
							break;
						case 'opc':
							$txt .= "§c★";
							$ops[$name] = 2;
							$bin |= self::STAR_OPC;
							break;
						case 'dev':
							$txt .= "§6★";
							$devs[$name] = 1;
							$bin |= self::STAR_DEV;
							break;
						case 'map':
							$txt .= "§e★";
							$maps[$name] = 1;
							$bin |= self::STAR_MAP;
							break;
						case 'ilu':
							$txt .= "§d★";
							$bin |= self::STAR_ILU;
							break;
						case 'mov':
							$txt .= "§7★";
							$movs[$name] = 1;
							$bin |= self::STAR_MOV;
							break;
					}
				}
				$newstars[$name] = $txt;
				$newstarsBin[$name] = $bin;
			}
			$this->ops = $ops;
			$this->devs = $devs;
			$this->maps = $maps;
			$this->movs = $movs;
			$this->stars = $newstars;
			$this->starsBin = $newstarsBin;
			if($this->m !== null){
				foreach(Server::getInstance()->getOnlinePlayers() as $player){
					if($this->m->checkPermission($player)) $this->m->changeName($player);
				}
			}
			MainLogger::getLogger()->debug("権限データ更新OK");
			return true;
		}
		MainLogger::getLogger()->error("権限データ取得失敗");
		return false;
	}

	private function getWeaponRanks($wno, $kindof, $max = 20){
		$data = [];
		Database::getInstance()->MySQLConnect();
		$column = "w".$wno;
		$sql = "select name, ".$column." from ranking_".$kindof." ".
			"order by ".$column." desc limit 0,".$max.";";
		if($result = Database::$mysqli->query($sql)){
			$key = 1;
			while($row = $result->fetch_assoc()){
				if(0 < $row[$column]){
					$data[$key++] = $row['name'];
				}
			}
			$result->free();
			if(!empty($data)){
				return $data;
			}
		}
		return false;
	}

	/**
	 * ランキングデータ更新
	 * @return bool データを更新できたかどうか
	 */
	public function confirmRanks(){
		$newranks = [];
		$marks = [
			'paint'		 => json_decode('"\u2654"'),
			'gamecount'	 => json_decode('"\u2655"'),
			'maxpaint'	 => json_decode('"\u2659"')
		];
		$wdata = $this->inputData("weaponsdata");
		if($wdata){
			foreach($wdata as $wno => $wd){
				if($wd[4][2]){
					foreach($marks as $rankName => $code){
						if($rankOfWeapon = $this->getWeaponRanks($wno, $rankName)){
							foreach($rankOfWeapon as $rankNum => $user){
								$user = strtolower($user);
								switch(true){
									case $rankNum === 1:
										$color = "§c";
										break;
									case $rankNum === 2:
										$color = "§6";
										break;
									case $rankNum === 3:
										$color = "§e";
										break;
									case $rankNum >=  4 && $rankNum <=  5:
										$color = "§a";
										break;
									case $rankNum >=  6 && $rankNum <= 10:
										$color = "§b";
										break;
									default:
										$color = "§f";
										break;
								}
								if(!isset($newranks[$user][$wno])){
									$newranks[$user][$wno] = "";
								}
								$newranks[$user][$wno] .= $color.$code;
							}
						}
					}
				}
			}
		}else{
			MainLogger::getLogger()->error("武器データ取得失敗");
		}
		if(!$newranks){
			MainLogger::getLogger()->error("ランキングデータ取得失敗");
			return false;
		}
		$this->ranks = $newranks;

		if($this->m !== null){
			foreach(Server::getInstance()->getOnlinePlayers() as $player){
				$this->m->changeName($player);
			}
		}
		MainLogger::getLogger()->debug("ランキングデータ更新OK");
		return true;
	}

/*
	ユーザーの権限について
*/

	/**
	 * 指定したユーザーの★のデータを取得
	 * @param  string   $user
	 * @return string | false
	 */
	public function hasStar($user){
		$user = strtolower($user);
		return $this->stars[$user] ?? false;
	}

	/**
	 * 指定したユーザー名がOPかどうか
	 * @param  string   $user
	 * @return int | false      OPC => 2, OP => 1, beh => 0
	 */
	public function hasOp($user){
		$user = strtolower($user);
		return $this->ops[$user] ?? false;
	}

	public function hasDev($user){
		$user = strtolower($user);
		return isset($this->devs[$user]);
	}

	public function hasMap($user){
		$user = strtolower($user);
		return isset($this->maps[$user]);
	}

	public function hasMov($user){
		$user = strtolower($user);
		return isset($this->movs[$user]);
	}

	/**
	 * プレイヤーがBANされていないか
	 * @param  Player $player [description]
	 * @return array  ['result' => bool(trueならBANされていない), 'message' => string]
	 */
	public function hasPunished($user){
		$user = strtolower($user);
		$data =  $this->punish[$user] ?? null;
		$return = ['result' => true, 'message' => "no reason"];
		if($data){
			switch($data[1]){
				case StatusUnit::PUNISH_BAN:
					$message = $this->m->lang->translateString("login.failure.banned", [$data[0]]);
					$return = ['result' => false, 'message' => $message];
					break;
				case StatusUnit::PUNISH_WARN:
					$message = $this->m->lang->translateString("login.failure.warnConstantValue", [$data[0]]);
					$return = ['result' => false, 'message' => $message];
					break;
			}
		}
		return $return;
	}

	public function hasWarn($user){
		$user = strtolower($user);
		return $this->warns[$user] ?? false;
	}

	public function getRank($user, $wno){
		$user = strtolower($user);
		return $this->ranks[$user][$wno] ?? "";
	}

	public function getStagedata(){
		return $this->stages;
	}

	public function setStagedata($data, $time = -1){
		if($time < 0){
			$this->stages = $data;
			return true;
		}else{
			if(isset($this->stages[$time])){
				$this->stages[$time] = $data;
				return true;
			}else{
				return false;
			}
		}
	}

	/**
	 * ステージを選ぶ
	 * @return int  
	 */
	public function chooseField(){
		$now = date("G");
		if(isset($this->stages[$now])){
			$target = rand(1, 100);
			$stage_cnt = count($this->stages[$now]['s']);
			foreach($this->stages[$now]['s'] as $stageno => $value){
				if($stage_cnt === 1 or $target <= $value){
					return $stageno;
				}else{
					$target -= $value;
				}
			}
		}
		return 13;
	}

	/**
	 * プレイヤーがログインできるかどうか(ログイン制限時のチェック処理)
	 * @param  Player $player
	 * @return array ['result' => bool(trueならログインできる), 'message' => string]
	 */
	public function checkLoginRestriction($user){
		$result = ['result' => true, 'message' => ""];
		$permsText = "";

		$starValue = $this->starsBin[strtolower($user)] ?? 0;

		if($starValue & $this->loginRestriction){
			return $result;
		}
		$perms = [];
		if($this->loginRestriction & self::STAR_OPC){
			$perms[] = $this->m->lang->translateString("loginCheck.opc");
		}
		if($this->loginRestriction & self::STAR_DEV){
			$perms[] = $this->m->lang->translateString("loginCheck.dev");
		}
		if($this->loginRestriction & self::STAR_MAP){
			$perms[] = $this->m->lang->translateString("loginCheck.map");
		}
		if($this->loginRestriction & self::STAR_OP){
			$perms[] = $this->m->lang->translateString("loginCheck.op");
		}
		if($this->loginRestriction & self::STAR_BEH){
			$perms[] = $this->m->lang->translateString("loginCheck.beh");
		}
		if($this->loginRestriction & self::STAR_ILU){
			$perms[] = $this->m->lang->translateString("loginCheck.ilu");
		}
		if($this->loginRestriction & self::STAR_MOV){
			$perms[] = $this->m->lang->translateString("loginCheck.mov");
		}
		$permsText = implode($this->m->lang->translateString("loginCheck.or"), $perms);

		$message = $this->m->lang->translateString("login.failure.restrictions", [$permsText."§f"]);
		$result = ['result' => false, 'message' => $message];
		return $result;
	}

/*
	通信 データ送信
*/

	private function setStatDB($txt, $sno = false){
		Database::getInstance()->MySQLConnect();
		if(Database::isConnected() && ($sno || $this->sno)){
			$sno = $sno ?:$this->sno;
			$sql = 	"UPDATE onlinestatus_server SET ".
					$txt.", lastupdated = now() ".
					"WHERE sno = '".$sno."';";
			$result = Database::$mysqli->query($sql);
			return $result;
		}
		return false;
	}

	public function setOnline($max_num){
		$result = $this->setStatDB("stat = 1, maxplayer = ".$max_num);
		if(!$result && $this->sno){
			MainLogger::getLogger()->error(__FUNCTION__.": 設定失敗");
		}
		return $result;
	}

	public function setOffline(){
		$result = $this->setStatDB("stat = 2, nowplayer = 0");
		if(!$result && $this->sno){
			MainLogger::getLogger()->error(__FUNCTION__.": 設定失敗");
		}
		return $result;
	}

	public function setEmergency($s = false){
		$result = $this->setStatDB("stat = 4", $s);
		if(!$result){
			MainLogger::getLogger()->error(__FUNCTION__.": 設定失敗");
		}
		return $result;
	}

	/**
	 * 現在のサーバー人数を反映
	 * @param int $num
	 */
	public function setNow($num){
		$result = $this->setStatDB("nowplayer = ".$num);
		if(!$result && $this->sno){
			MainLogger::getLogger()->error(__FUNCTION__.": 設定失敗");
		}
		return $result;
	}

	//基本！
	//サーバーとの通信関数
	public function curlUnit($url){
		$huee = curl_init($url);
		curl_setopt($huee, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($huee, CURLOPT_HTTPHEADER, ["User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:12.0) Gecko/20100101 Firefox/12.0 Splaturn"]);
		curl_setopt($huee, CURLOPT_TIMEOUT, 5);
		$response = curl_exec($huee);
		(int) $httpcode = curl_getinfo($huee, CURLINFO_HTTP_CODE);
		curl_close($huee);
		//サーバー側での不具合エスケープ
		if($httpcode){
			if($httpcode < 300){
				//結果を返す
				return $response;
			}else{
				MainLogger::getLogger()->error(__FUNCTION__.": Error - $httpcode (URL: $url)");
			}
		}
		//404などだったら
		return false;
	}

/*
	飛ばす
*/

	/**
	 * 別のサーバーへ移動
	 * @param  Player $player
	 * @param  string $s_no
	 * @return bool 移動できたかどうか
	 */
	public function gotoPlay($player, $s_no){
		if(isset($this->servers[$s_no])){
			$packet = new TransferPacket();
			$packet->address = $this->servers[$s_no]['address'];
			$packet->port = $this->servers[$s_no]['port'];
			$player->dataPacket($packet);
			/*//ここからバグ対策のコード
			$this->TeleportedPlayers[] = $player->getName();
			$player->despawnFromAll();
			Server::getInstance()->getPluginManager()->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_USERS, $player);
			Server::getInstance()->getPluginManager()->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $player);*/
			return true;
		}else{
			MainLogger::getLogger()->debug(__FUNCTION__.": 設定されていないSNO ({$s_no})");
		}
		return false;
	}
}