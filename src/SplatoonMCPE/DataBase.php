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

use pocketmine\utils\MainLogger;

class DataBase{

	private static $instance = null;
	public static $mysqli, $driver;
	private $mysqlConnectConfirm = 5;
	private $mysqlLastConfirmationTime = 0;
	private $mysqlFirstConnection = true;

	public function __construct($m = null){
		self::$instance = $this;
		$this->MySQLConnect();
	}

	public function __destruct(){
		if(self::$mysqli instanceof \mysqli){
			self::$mysqli->close();
			self::$mysqli = null;
		}
	}

	public static function getInstance(){
		return self::$instance;
	}

	/**
	 * データベースへ接続
	 */
	public function MySQLConnect(){
		try{
			if(time() - $this->mysqlLastConfirmationTime >= 300){
				if(self::$mysqli instanceof \mysqli){
					self::$mysqli->close();
					self::$mysqli = null;
				}
				goto connect;
			}else{
				$this->mysqlLastConfirmationTime = time();
			}
			return false;
		}catch(Throwable $e){
			MainLogger::getLogger()->error(__FUNCTION__."(): ".$e->getMessage());
			if($this->mysqlConnectConfirm){
				goto connect;
			}
		}

		connect:
		$mysqli = new \mysqli('localhost', 'root', 'password', 'root');// replaced to dummy data: to prevent leaking user data
		if($mysqli->connect_errno){
			MainLogger::getLogger()->error("db connect failed: ".$mysqli->connect_error."(".$mysqli->connect_errno.")");
			$this->mysqlConnectConfirm--;
			self::$mysqli = null;
			return false;
		}else{
			$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 3);
			self::$mysqli = $mysqli;
			MainLogger::getLogger()->debug("db ".($this->mysqlFirstConnection ? "" : "Re")."connected");
			$this->mysqlFirstConnection = false;
			$this->mysqlLastConfirmationTime = time();
			$driver = new \mysqli_driver();
			$driver->reconnect = true;
			//$driver->report_mode = MYSQLI_REPORT_ALL;
			self::$driver = $driver;
			return true;
		}
		return false;
	}

	/**
	 * 接続中か確認
	 * @return boolean
	 */
	public static function isConnected(){
		return isset(self::$mysqli);
	}
}