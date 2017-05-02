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
 *         (32ki, kusutohu1128, tomotomo, 0929hitoshi, moyasan, trasta)
 * @link http://splaturn.net/
 *                  
 */

namespace SplatoonMCPE;

use pocketmine\Player;
use pocketmine\Server;

class Entry {

	private $entrylist = [];
	private $preentrylist = [];

	function __construct($main){
		$this->main = $main;
	}

	/**
	 * エントリーしているかどうか取得
	 */
	public function isEntry($user){
		return in_array($user, $this->entrylist);
	}

	/**
	 * 仮エントリーしているかどうか取得
	 */
	public function isPreEntry($user){
		return in_array($user, $this->preentrylist);
	}


	/**
	 * エントリー何番目かを取得
	 */
	public function getEntry($user){
		return array_search($user, $this->entrylist)+1;
	}

	/**
	 * エントリーできるかどうか取得
	 */
	public function canEntry($user){
		return ($this->main->team->getTeamOf($user) == 0);
	}

	/**
	 * エントリーしている人数を取得
	 */
	public function getEntryNum(){
		return count($this->entrylist)+count($this->preentrylist);
	}

	public function isReady(){
		if(($this->main->game == 1 || $this->main->error) && !$this->main->gamestop){
			$next = $this->main->game + 1;
			if(!isset($this->main->Task['game'][$next]) and $this->getEntryNum() > 1){
				$sec = ($this->getEntryNum() > 3) ? 5 : 15;
				$this->main->Task['game'][$next] = $this->main->getServer()->getScheduler()->scheduleDelayedTask(new TimeScheduler($this->main), 20 * $sec);
			}
		}
	}

	/**
	 *エントリーの順番確定
	 */
	public function PreintoEntry(){
		shuffle($this->preentrylist);
		$this->entrylist = array_merge($this->entrylist, $this->preentrylist);
		foreach ($this->preentrylist as $user) {
			$player = $this->main->getServer()->getPlayer($user);
			if($player instanceof Player){
				$player->sendMessage($this->getEntry($user)."番目にエントリーした！");
			}
		}
		$this->preentrylist = [];
	}

	/**
	 * エントリー追加
	 */
	public function addEntry($user, $run_isReady = true){
		$playerData = Account::getInstance()->getData($user);
		if($playerData->getMaxWeaponLevel() < $this->main->needLv){
			return "このサーバーはイカした子しかできないんだよね～\nもっとやりこんでから来てよ キミにはまだ早い\nそうだね...どれかのブキをレベル".$this->main->needLv."にしてからまた来てよ";
		}
		if($this->isEntry($user)){
			return $this->getEntry($user)."番目にエントリーしてるじゃなイカ";
		}
		if($this->isPreEntry($user)){
			return "エントリー済みじゃなイカ シャッフル中…";
		}
		if($this->main->game == 17 || $this->main->game == 1){
			$this->preentrylist[] = $user;
			if($run_isReady){
				$this->isReady();
			}
			return "エントリー完了！ シャッフル中…";
		}
		$this->entrylist[] = $user;
		if($run_isReady){
			$this->isReady();
		}
		return "エントリー完了！ ".count($this->entrylist)."番目";
	}

	/**
	 * エントリー解除
	 */
	public function removeEntry($user){
		if(!$this->isEntry($user) and !$this->isPreEntry($user)){
			return false;
		}
		while(true){
			if(($n = array_search($user, $this->entrylist)) !== false){
				array_splice($this->entrylist, $n, 1);
			}else{
				break;
			}
			if(($n = array_search($user, $this->preentrylist)) !== false){
				array_splice($this->preentrylist, $n, 1);
			}else{
				break;
			}
		}
		return true;
	}

	/**
	 * 試合メンバー選出
	 */
	public function choiceBattleMember(){
		if(count($this->entrylist) < 2){
			return false;
			//エントリー人数が足りない
		}
		$many = (count($this->entrylist) % 2 == 0) ? count($this->entrylist) : count($this->entrylist)-1 ;
		if($many > 8){
			$many = 8;
		}
		$battlemember = array_slice($this->entrylist, 0, $many, true);
		array_splice($this->entrylist, 0, $many);
		$colors_1 = [1,2,3,4];
		$colors_2 = [5,6,7,8];
		shuffle($colors_1);
		shuffle($colors_2);
		//shuffle($battlemember);
		if($this->main->area['mode']){
			shuffle($battlemember);
		}else{
			$this->getMatching($battlemember, $many);
		}
		$n = 1;
		foreach ($battlemember as $user) {
			if($n%2 == 0){
				$s = $this->main->team->addMember($colors_1[0], $user, true);
			}else{
				$s = $this->main->team->addMember($colors_2[0], $user, true);
			}
			$n++;
			$player = $this->main->getServer()->getPlayer($user);
			if($player instanceof Player){
				if(isset($this->main->trypaintData['player'][$user])){
					$this->main->TryPaint($player);
					$player->sendMessage($this->main->lang->translateString("trypaint.close"));
				}
				$this->main->changeName($player);
			}
		}
		return [$colors_1[0], $colors_2[0]];
	}

	/**
	 * 試合の平等マッチング選出
	 * @param $members
	 * @param $many
	 */
	public function getMatching($members, $many){
		$ms = function ($usera, $userb){
			$pda = Account::getInstance()->getData($usera);
			$pdb = Account::getInstance()->getData($userb);
			if($this->main->area['mode']){
				$wina = $pda->getAreaWin()+1;
				$winb = $pdb->getAreaWin()+1;
				$coa = $pda->getAreaCounter()+1;
				$cob = $pdb->getAreaCounter()+1;
			}else{
				$wina = $pda->getWin()+1;
				$winb = $pdb->getWin()+1;
				$coa = $pda->getCounter()+1;
				$cob = $pdb->getCounter()+1;
			}
			$wina /= $coa;
			$winb /= $cob;
			$wina *= $pda->getMaxWeaponLevel();
			$winb *= $pdb->getMaxWeaponLevel();
			return ($wina-$winb);
		};
		usort($members, $ms);
		$high = array_slice($members, 0, ($many/2));
		$low = array_slice($members, ($many/2), ($many/2));
		shuffle($high);
		shuffle($low);
		$result = array_merge($high, $low);
		return $result;
	}
}