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
use pocketmine\utils\Config;
use pocketmine\utils\MainLogger;

class OfflinePlayerData extends PlayerData{

	function __construct($name = null, $main = null){
		$this->main = $main;
		if($name !== null){
			$this->username = $name;
			$this->iusername = strtolower($this->username);
		}
		
		$this->load();
	}

	/**
	 * ブキを購入
	 * @param  int $w_num
	 * @return bool       購入できたかどうか
	 */
	public function BuyWeapon($w_num){

		return false;
	}
}