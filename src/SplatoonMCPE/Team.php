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

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\Server;

use pocketmine\math\Vector3;

use pocketmine\utils\MainLogger;

class Team{

	const NO_TEAM = 0;
	const RED     = 1;
	const ORANGE  = 2;
	const YELLOW  = 3;
	const GREEN   = 4;
	const AQUA    = 5;
	const BLUE    = 6;
	const PINK    = 7;
	const PURPLE  = 8;
	const BLACK   = 9;

	private $teamMaxPlayer = 4;//チームに参加できる最大人数

	public $minTeam = self::BLACK;//必ず存在しなければならないチーム

	private $list = null, $teams = null, $teamBattleTime = [], $teamEvent = [], $removeMemberData = [];
	public $canJoin = true, $member = [], $battleTeamNumber = null, $battleTeamMember = null;

	function __construct($main){
		$this->main = $main;
	}

	public function getMain(){
		return $this->main;
	}

	public function init(){
		if($this->teams === null){
			//チームのデータ
			$this->list = [
				self::NO_TEAM => [
					'name' => "no_team",
					'color' => "§f",
					'block_color' => 0,
					'rgb' => [255, 255, 255],
				],
				self::RED => [
					'name' => "red",
					'color' => "§c",
					'block_color' => 14,
					'rgb' => [255, 85, 85],
				],
				self::ORANGE => [
					'name' => "orange",
					'color' => "§6",
					'block_color' => 1,
					'rgb' => [255, 170, 0],
				],
				self::YELLOW => [
					'name' => "yellow",
					'color' => "§e",
					'block_color' => 4,
					'rgb' => [255, 255, 85],
				],
				self::GREEN => [
					'name' => "green",
					'color' => "§a",
					'block_color' => 5,
					'rgb' => [85, 255, 85],
				],
				self::AQUA => [
					'name' => "aqua",
					'color' => "§b",
					'block_color' => 3,
					'rgb' => [85, 255, 255],
				],
				self::BLUE => [
					'name' => "blue",
					'color' => "§9",
					'block_color' => 11,
					'rgb' => [85, 85, 255],
				],
				self::PINK => [
					'name' => "pink",
					'color' => "§d",
					'block_color' => 2,
					'rgb' => [255, 85, 255],
				],
				self::PURPLE => [
					'name' => "purple",
					'color' => "§5",
					'block_color' => 10,
					'rgb' => [170, 0, 170],
				],
				self::BLACK => [
					'name' => "black",
					'color' => "§8",
					'block_color' => 15,
					'rgb' => [0, 0, 0],
				]
			];
			for($i = self::NO_TEAM; $i <= $this->minTeam; $i++){
				//参加できるチームが格納される
				$this->teams[$i] = $this->list[$i];
			}

			foreach($this->teams as $team_num => $data){
				$this->teamColorBlockByBlock[$data['block_color']] = $team_num;
				$this->teamNumbyName[$data['name']] = $team_num;
			}
			$this->teamBattleTime = [
				0,
				0,
				0,
				0,
				0,
				0,
				0,
				0,
				0,
				0
			];
		}
	}

	public function getTeams(){
		return $this->teams;
	}

	/**
	 * 取得時に存在するチームを取得
	 * @return int
	 */
	public function getTeamCount(){
		//NO_TEAMを除く
		return count($this->teams) - 1;
	}

	/**
	 * チーム名を取得
	 * @param  int            $team_num
	 * @return string | false
	 */
	public function getTeamName($team_num){
		if($team_num){
			$name = $this->teams[$team_num]['name'] ?? $this->getMain()->lang->translateString("error");
			$color = $this->getTeamColor($team_num);
			return $color.$name;
		}
		return false;
	}

	/**
	 * チームのカラーコード(§aなど)を取得
	 * @param  int            $team_num
	 * @return string | false
	 */
	public function getTeamColor($team_num){
		if($team_num){
			$color = $this->teams[$team_num]['color'] ?? "";
			return $color;
		}
		return false;
	}

	/**
	 * 羊毛ブロックのデータ値を取得
	 * @param  int         $team_num
	 * @return int | false
	 */
	public function getTeamColorBlock($team_num){
		if($team_num){
			$color = $this->teams[$team_num]['block_color'] ?? 0;
			return $color;
		}
		return false;
	}

	/**
	 * 羊毛ブロックのデータ値(メタデータ)からチームの番号を取得
	 * @param  int         $color_num
	 * @return int | false
	 */
	public function getTeamNumByBlock($color_num){
		if($color_num){
			$team_num = $this->teamColorBlockByBlock[$color_num] ?? 0;
			return $team_num;
		}
		return false;
	}

	/**
	 * 色(RGB)を取得
	 * @param  int   $color_num
	 * @return array            default = [0(R), 0(G), 0(B)]
	 */
	public function getTeamColorRGB($color_num){
		if($color_num){
			$color = $this->teams[$color_num]['rgb'] ?? [0, 0, 0];
			return $color;
		}
		return false;
	}

	/**
	 * チーム名からチームの番号を取得
	 * @param  string      $team_name
	 * @return int | false
	 */
	public function getTeamNum($team_name){
		$team_name = strtolower($team_name);
		return $this->teamNumbyName[$team_name] ?? false;
	}

	public function getTeamEvent(){
		return $this->teamEvent;
	}

	public function getTeamBattleTime(){
		return $this->teamBattleTime;
	}

	public function getTeamMaxPlayer(){
		return $this->teamMaxPlayer;
	}

	/**
	 * チームを追加
	 * @return  boolean
	 */
	public function addTeam(){
		$team_num = count($this->teams);//次に追加するチームのkey
		$max_team = count($this->list);
		if($team_num < $max_team){
			$data = $this->list[$team_num];
			$this->teams[$team_num] = $data;
			$this->teamColorBlockByBlock[$data['block_color']] = $team_num;
			$this->teamNumbyName[$data['name']] = $team_num;
			$this->teamEvent['add'][$team_num] = time();
			$out = $this->getMain()->lang->translateString("team.add", [$this->getTeamName($team_num)]);
			Server::getInstance()->broadcastMessage($out);
			if(!$this->canJoin){
				$this->canJoin = true;
			}
			$this->getMain()->FloatText(true);
			return true;
		}
		return false;
	}

	/**
	 * チームを追加できるかどうか
	 * @return int チームを追加できる数
	 */
	public function canAddTeam(){
		$allcount = count(Server::getInstance()->getOnlinePlayers());
		$eachteam = $this->teamMaxPlayer;
		$teamcount = $this->getTeamCount();
		$max_team = count($this->list);
		if($teamcount < $max_team - 1){
			$result = 0;
			for($checkcnt = 1; $teamcount + $checkcnt <= 8; $checkcnt++){
				$team_num = $teamcount + $checkcnt;
				if($allcount >= $team_num * $eachteam){
					$canAdd = (isset($this->teamEvent['add'][$team_num])) ? (time() - $this->teamEvent['add'][$team_num] >= 120) : true;//チームを追加してから120秒経過しているか(何度も処理していないか) or まず1度も追加してない
					$canRemove = (isset($this->teamEvent['remove'][$team_num])) ? (time() - $this->teamEvent['remove'][$team_num] >= 150) : true;//解散してから150秒経過している or まず解散していない
					if($canAdd && $canRemove){
						$result++;
					}else{
						/*
						$add = $canAdd ? "true" : "false";
						$remove = $canRemove ? "true" : "false";
						MainLogger::getLogger()->debug("canAddTeam: team_num[{$team_num}] = (Addcheck = {$add}, Removecheck = {$remove})");
						*/
						if($result === 0){
							$result = ($this->getMain()->isinPrepareBattle()) ? -1 : 0;
						}
						MainLogger::getLogger()->debug("canAddTeam: result = {$result}");
						return $result;
					}
				}else{
					$result = ($result == 0 and $this->getMain()->isinPrepareBattle()) ? -1 : $result;
					MainLogger::getLogger()->debug("canAddTeam: result = {$result}");
					return $result;
				}
			}
			MainLogger::getLogger()->debug("canAddTeam: result = {$result}");
			return $result;
		}
		return 0;
	}

	/**
	 * チームを解散
	 * @param  boolean $force 試合中などでも強制的にチームを解散するかどうか
	 * @return boolean
	 */
	public function removeTeam($force = false){
		$team_num = count($this->teams) - 1;//解散するチームのkey
		if($team_num > $this->minTeam){
			if(!$this->isBattleing($team_num, true) || $force){
				$teamName = $this->getTeamName($team_num);
				$data = $this->teams[$team_num];
				unset($this->teams[$team_num], $this->teamColorBlockByBlock[$data['block_color']], $this->teamNumbyName[$data['name']]);

				if(isset($this->member[$team_num])){
					foreach($this->member[$team_num] as $user => $t){
						if(($player = Server::getInstance()->getPlayer($user)) instanceof Player){
							$this->removeMember($user, true, $this->getMain()->checkFieldteleport(), $this->getMain()->checkFieldteleport());
							$this->getMain()->changeName($player);
							$player->sendMessage($this->getMain()->lang->translateString("team.remove.target"));
						}
					}
					unset($this->member[$team_num]);
				}
				$this->teamEvent['remove'][$team_num] = time();
				$out = $this->getMain()->lang->translateString("team.remove", [$teamName]);
				Server::getInstance()->broadcastMessage($out);
				$this->getMain()->FloatText(true);
				return true;
			}
		}
		return false;
	}

	/**
	 * チームを解散できるかどうか
	 * @param  bool $allplayerscheck trueならサーバーにいるプレイヤーの人数で、falseならチームに参加している人数で計算
	 * @return int                   チームを解散できる数
	 */
	public function canRemoveTeam($allplayerscheck = false){
		$tmax = $this->teamMaxPlayer;
		$teamc = $this->getTeamCount();
		$t_cnt = $teamc * $tmax;
		$now_cnt = 0;
		$player_cnt = count(Server::getInstance()->getOnlinePlayers());
		if(!empty($this->member)){
			foreach($this->member as $t){
				$now_cnt += count($t);
			}
			$checkcnt = $allplayerscheck ? floor($teamc - $player_cnt / $tmax) : floor($teamc - $now_cnt/$tmax);
			if($checkcnt){
				for($result = 0; $checkcnt > 0; $checkcnt--){
					$team_num = $teamc - $result;
					if(!$this->isBattleing($team_num, true)){
						$canRemove = (isset($this->teamEvent['remove'][$team_num])) ? (time() - $this->teamEvent['remove'][$team_num] >= 120) : true;//チームを解散してから120秒経過しているか(何度も処理していないか) or まず1度も解散してない
						$canAdd = (isset($this->teamEvent['add'][$team_num])) ? (time() - $this->teamEvent['add'][$team_num] >= 150) : false;//チームを追加してから150秒経過している(memo: issetがされていない場合 = orangeTeam)
						if($canRemove && $canAdd){
							$result++;
						}else{
							$remove = $canRemove ? "true" : "false";
							$add = $canAdd ? "true" : "false";
							//MainLogger::getLogger()->debug("canRemoveTeam: team_num[{$team_num}] = (Removecheck = {$remove}, Addcheck = {$add})");
							MainLogger::getLogger()->debug("canRemoveTeam: result = {$result}");
							return $result;
						}
					}else{
						//MainLogger::getLogger()->debug("canRemoveTeam: team_num[{$team_num}] = (isBattleing = true)");
						MainLogger::getLogger()->debug("canRemoveTeam: result = {$result}");
						return $result;
					}
				}
				MainLogger::getLogger()->debug("canRemoveTeam: result = {$result}");
				return $result;
			}
		}else{
			//MainLogger::getLogger()->debug("解散できるチームがありません");
		}
		return false;
	}

	/**
	 * チームに参加
	 * @param    int          $team_num
	 * @param    string       $user
	 * @param    bool         $force        default = false 強制的にチームに参加するかどうか
	 * @param    bool         $run_isReady  default = true  試合を開始できるかのチェックを実行するかどうか
	 * @return   int | false                                参加できたときはチームのインデックスを返す
	 */
	public function addMember($team_num, $user, $force = false, $run_isReady = true){
		if($team_num){//0の可能性がある(?)
			$teamMaxPlayer = $this->teamMaxPlayer;
			if(!$this->getTeamOf($user)){
				if($force || empty($this->member[$team_num]) || count($this->member[$team_num]) < $teamMaxPlayer){
					$this->member[$team_num][$user] = 1;
					//$this->getMain()->setFloatText([0]);
					//if($run_isReady) $this->getMain()->isReady();
					return $team_num;
				}else{
					//チームが満員の場合
					$result = $this->canAddTeam();
					switch($result){
						case -1:
							//制限中の表示に切り替え
							if($this->canJoin){
								$this->canJoin = false;
								$this->getMain()->FloatText(true);
							}
							break;
						default:
							for($i = $result; $i > 0; $i--){
								$this->addTeam();
							}
					}
				}
			}
		}
		return false;
	}

	/**
	 * チームに参加(参加するチームを指定しない)
	 * @param   string      $user
	 * @param   boolean     $removeCheck default = true 解散できるかのチェックをするかどうか
	 * @param   boolean     $run_isReady default = true 試合を開始できるかのチェックをするかどうか
	 * @return  int | false
	 */
	public function addMemberAuto($user, $removeCheck = true, $run_isReady = true){
		if($removeCheck){
			for($cnt = $this->canRemoveTeam(true); $cnt > 0; $cnt--){
				$this->removeTeam();//チーム解散
			}
		}

		//一番人数の少ないチームに入れる
		$teamMaxPlayer = $this->teamMaxPlayer;
		$t_num = 1;//最終的に入れるチーム番号
		$data = [];//入れるチームのデータ
		if($this->getTeamCount() == 2){
			$cnt1 = !empty($this->member[1]) ? count($this->member[1]) : 0;
			$cnt2 = !empty($this->member[2]) ? count($this->member[2]) : 0;
			$t_num = ($cnt1 == $cnt2) ? mt_rand(1, 2) : (($cnt1 < $cnt2) ? 1 : 2);
		}else{
			foreach($this->teams as $n => $d){
				if($n == 0) continue;
				//↓現在、そのチームにいる人数
				$cnt = !empty($this->member[$n]) ? count($this->member[$n]) : 0;
				//空き人数
				$now_num = $teamMaxPlayer - $cnt;
				//↓満員じゃない場合 & 試合中ではない
				if(0 < $now_num and !$this->isBattleing($n, true)){
					$data[$n] = $cnt;
				}
			}
			if($data == null){
				//入れるチームがない場合
				$result = $this->canAddTeam();
				switch($result){
					case -1:
						//制限中の表示に切り替え
						if($this->canJoin){
							$this->canJoin = false;
							$this->getMain()->FloatText(true);
						}
						break;
					default:
						for($i = $result; $i > 0; $i--){
							$this->addTeam();//チーム追加
						}
				}
				return false;
			}
			//参加している人数が少ないチームを選ぶ
			$min_team = min($data);
			$teams = array_keys($data, $min_team);
			$t_num = $teams[array_rand($teams)];
		}
		$r = $this->addMember($t_num, $user, false, $run_isReady);
		if($r){
			return $r;
		}
		return false;
	}

	/**
	 * チームから抜ける
	 * @param  string  $user
	 * @param  bool    $now_remove default = true  今すぐチームから抜けるかどうか falseの場合、試合終了後に抜ける処理が実行される
	 * @param  bool    $respawn    default = false リスポにテレポートするかどうか
	 * @param  bool    $reset      default = true  プレイヤーのステータスをリセットするか
	 * @return bool
	 */
	public function removeMember($user, $now_remove = true, $respawn = false, $reset = true){
		$team = $this->getTeamOf($user);
		if($team){
			if($now_remove){
				unset($this->member[$team][$user]);
				if($bteam_num = $this->getBattleTeamOf($user)){
					unset($this->battleTeamMember[$bteam_num][$user]);
					$this->getMain()->w->removeBattleMember($user);
					Account::getInstance()->getData($user)->resetBattleData();
					if(($player = Server::getInstance()->getPlayer($user)) instanceof Player){
						foreach(Server::getInstance()->getOnlinePlayers() as $p){
							$p->showPlayer($player);
						}
						if($reset){
							$player->setAllowFlight(false);
							$player->setGamemode(Player::SURVIVAL);
							$player->setMaxHealth(20);
							$player->setHealth(20);
							$player->extinguish();
							$this->getMain()->delAllItem($player);
							$player->removeAllEffects();
							$player->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE, 1);
							$player->sendData($player);
							$this->getMain()->ResetStatus($player);
							$this->getMain()->DespawnToSquid($player);
						}
						if($respawn){
							$lobbyPos = $this->getMain()->lobbyPos;
							$zinti = new Vector3($lobbyPos[0], $lobbyPos[1], $lobbyPos[2]);
							$player->teleport($zinti);
							$id = Item::get(340);
							$id2 = Item::get(288);
							$player->getInventory()->addItem($id, $id2);
							$this->getMain()->itemselect->set($player, false);
							//$this->getMain()->itemCase->set($player);
							$this->getMain()->shop->sendPage($player);
						}
					}
				}
				$this->getMain()->setFloatText([0]);
			}else{
				$this->removeMemberData[$user] = 1;
			}
			return true;
		}
		return false;
	}

	/**
	 * 試合中にログアウトしたプレイヤーをチームから抜けさせる
	 */
	public function laterRemoveMember(){
		foreach($this->removeMemberData as $user => $v){
			$this->removeMember($user, true);
			if(($player = Server::getInstance()->getPlayer($user)) instanceof Player){
				$this->getMain()->changeName($player);
			}
		}
		$this->removeMemberData = [];
	}

	public function cancelLaterRemoveMember($user){
		unset($this->removeMemberData[$user]);
	}

	/**
	 * チームに入れる最大人数を変更 - バグがあるかもしれないので非推奨
	 * @param  int $num 変更後の人数
	 */
	public function changeTeamMaxPlayer($num){
		$be = $this->teamMaxPlayer;//変更前の最大人数
		$now = ($this->teamMaxPlayer = $num);//変更後の最大人数
		$n = $be - $now;
		if($be > $now){
			$count = $this->getTeamCount();
			for($team = 1; $team <= $count; $team++){
				for($i = 1; $i <= $n; $i++){
					if(isset($this->member[$team])){
						$members = array_keys($this->member[$team]);
						$playernum = ($be - $i);//チームに入った順で一番最後の人を抜けさせる
						if(isset($members[$playernum])){
							$user = $members[$playernum];
							$this->removeMember($user, true);
							if(($player = Server::getInstance()->getPlayer($user)) instanceof Player){
								$this->getMain()->changeName($player);
								$player->sendMessage(
									"§cチームに参加できる人数が{$now}人になったため、自動的にチームを抜けました\n".
									"§c別のグループに参加しなおしてください"
								);
							}
						}
					}
				}
			}
		}
		if($be != $now){
			$out = "§d≫ チームに参加できる人数が".$now."人になりました";
			Server::getInstance()->broadcastMessage($out);
			$this->getMain()->FloatText(true);
		}
	}

	/**
	 * チームのメンバーをシャッフルするべきかどうか
	 * @return bool
	 */
	public function canMemberShuffle(){
		$team_c = $this->getTeamCount();
		$c = 0;
		foreach($this->teamBattleTime as $team_num => $ct){
			if(isset($this->teams[$team_num]) and $team_num){
				$c += $ct;
			}
		}
		return $c >= 4 * $team_c;
	}

	public function allMemberShuffle($force = false){
		if($force or (!$this->getMain()->isinPrepareBattle() && $this->getMain()->game !== 4)){
			$this->BattlecountReset();
			Server::getInstance()->broadcastMessage($this->getMain()->lang->translateString("team.shuffle"));
			$member_data = [];
			$member_team = [];
			foreach($this->member as $team_num => $data){
				$members = array_keys($data);
				foreach($members as $member){
					$member_data[] = $member;
					$member_team[$member] = $this->getTeamOf($member);
					$this->removeMember($member, true);
				}
			}
			shuffle($member_data);
			foreach($member_data as $member){
				$be_team = $member_team[$member];
				$af_team = $this->addMemberAuto($member, false, false);
				if(($player = Server::getInstance()->getPlayer($member)) instanceof Player){
					if($af_team){
						if($be_team != $af_team){
							$out = $this->getMain()->lang->translateString("team.shuffle.success", [$this->getTeamName($be_team), $this->getTeamName($af_team)]);
						}else{
							$out = $this->getMain()->lang->translateString("team.shuffle.notMove", [$this->getTeamName($af_team)]);
						}
					}else{
						$out = $this->getMain()->lang->translateString("team.shuffle.moveFailed");
					}
					$player->sendMessage($out);
					$this->getMain()->changeName($player);
				}
			}
			return true;
		}
		return false;
	}

	public function removeAllMember($force = false){
		if($force or (!$this->getMain()->isinPrepareBattle() && $this->getMain()->game !== 4)){
			$this->BattlecountReset();
			foreach($this->member as $team_num => $data){
				$members = array_keys($data);
				foreach($members as $member){
					$this->removeMember($member, true);
				}
			}
			//Server::getInstance()->broadcastMessage($this->getMain()->lang->translateString("team.removeAllMember"));
			return true;
		}
		return false;
	}

	public function BattlecountReset(){
		$this->teamBattleTime = [
			0,
			0,
			0,
			0,
			0,
			0,
			0,
			0,
			0,
			0
		];
		return true;
	}

	/**
	 * 試合回数をリセットすべきかどうか
	 * @return boolean 最小値と最大値の差が $diff 以上ならtrueを返す
	 */
	public function canBattlecountReset(){
		$diff = 4;
		$c = [];
		foreach($this->teamBattleTime as $team_num => $ct){
			if(isset($this->teams[$team_num]) and $team_num){
				$c[$team_num] = $ct;
			}
		}
		return (max($c) - min($c) >= $diff);
	}

	public function getTeamNumFromBattleTeamNum($num){
		foreach($this->battleTeamNumber as $team_num => $battle_num){
			if((int) $battle_num === (int) $num){
				return $team_num;
			}
		}
		return false;
	}

	public function getBattleTeamFromTeamNum($team_num){
		return isset($this->battleTeamNumber[$team_num]) ? $this->battleTeamNumber[$team_num] : false;
	}

	/**
	 * チームのメンバーを取得する
	 * @param  int     $n
	 * @param  boolean $array default = false trueの場合配列で、falseの場合文字列で返す
	 * @return string
	 */
	public function getTeamMember($n, $array = false){
		$membersArray = empty($this->member[$n]) ? [] : array_keys($this->member[$n]);
		if($array){
			return $membersArray;
		}else{
			$txt = $membersArray == null ?
				$this->getMain()->lang->translateString("noMember") :
				implode(", ", $membersArray);
			return "§2≫ ". $this->getTeamName($n) . ": ".$txt;
		}
	}

	/**
	 * 指定したユーザーが参加しているチームのインデックス取得
	 * @param  string $user
	 * @return int          参加していない場合0を返す
	 */
	public function getTeamOf($user){
		foreach($this->teams as $num => $data){
			if(isset($this->member[$num][$user])){
				return $num;
			}
		}
		return 0;
	}

	/**
	 * バトルチームの中からユーザーが戦っているチームの番号を取得
	 * @param  string $user
	 * @return int
	 */
	public function getBattleTeamOf($user){
		if(isset($this->battleTeamMember[1][$user])){
			return 1;
		}elseif(isset($this->battleTeamMember[2][$user])){
			return 2;
		}
		return 0;
	}

	/**
	 * 試合中のメンバーを取得
	 * @return array | false
	 */
	public function getBattleTeamMember(){
		return (isset($this->battleTeamMember)) ? $this->battleTeamMember : false;
	}


	/**
	 * 全チームのステータスを取得
	 * @return string
	 */
	public function getAllTeamStatus(){
		$out = "";
		foreach($this->teams as $n => $d){
			if($n != 0){
				$cnt = !empty($this->member[$n]) ? count($this->member[$n]) : 0;
				$is_battleing = $this->isBattleing($n);
				if(!$is_battleing){
					$status = $cnt >= $this->teamMaxPlayer ? $this->getMain()->lang->translateString("team.reserved") : $this->getMain()->lang->translateString("team.available");
				}else{
					$status = $this->getMain()->lang->translateString("team.inBattle");
				}
				$out .= $this->getMain()->lang->translateString("team.status", [str_pad($this->getTeamName($n), 9), $cnt, $status])."\n";
			}
		}
		$out = substr($out, 0, -1);
		return $out;
	}

	/**
	 * 指定したチームが試合中かどうか
	 * @param  int     $team_num
	 * @param  boolean $removeCheck チーム解散の確認時の場合はtrueを指定してください
	 * @return boolean
	 */
	public function isBattleing($team_num, $removeCheck = false){
		return isset($this->battleTeamNumber[$team_num]) && ($removeCheck || $this->getMain()->game >= 6);
	}

	/**
	 * チームにいる人数の合計を取得
	 * @return int
	 */
	public function getAllTeamMemberCount(){
		$now_cnt = 0;
		foreach($this->member as $t){
			$now_cnt += count($t);
		}
		return $now_cnt;
	}

	/**
	 * 試合するチームの番号を設定
	 * @param int $num
	 */
	public function setBattleTeam($num){
		if(!isset($this->battleTeamMember[1])){
			$this->battleTeamMember[1] = $this->member[$num];
			$this->battleTeamNumber[$num] = 1;
			$this->teamBattleTime[$num] += 1;
			return true;
		}elseif(!isset($this->battleTeamMember[2])){
			$this->battleTeamMember[2] = $this->member[$num];
			$this->battleTeamNumber[$num] = 2;
			$this->teamBattleTime[$num] += 1;
			return true;
		}else{
			return false;
		}
	}

	//フィールド転送までの間ブキを変更できるので、ブキデータがずれたりしないように後で保存するように変更(0929hitoshi 10/21)
	public function setBattleTeamMember(){
		$members = [];
		for($i = 1; $i <= 2; $i++){
			//$num = array_search($i, $this->battleTeamNumber);
			//$this->battleTeamMember[$i] = $this->changeArrayForBattle($num);
			//$array = $this->changeArrayForBattle($num);
			$array = $this->changeArrayForBattle($i);
			$count = 0;
			foreach($array as $user => $data){
				$this->battleTeamMember[$i][$user] = $count;//メンバーの順番をset(フィールドテレポート時に使う)
				Account::getInstance()->getData($user)->setData($data);
				$count++;
				$members[$i][$count] = $user;
			}
		}
		$this->getMain()->w->setBattleMember($members);
	}

	//チームnum投げると、セットする形式にして返してくれる
	//試合チームの番号を投げるとデータを投げるように
	public function changeArrayForBattle($num){
		if(isset($this->battleTeamMember[$num])){
			$return_ar = [];
			foreach($this->battleTeamMember[$num] as $user => $num){
				$team_num = $this->getTeamOf($user);
				$color = $this->getTeamColorBlock($team_num);
				$playerData = Account::getInstance()->getData($user);
				$weapon_num = $playerData->getNowWeapon($user);
				$weapon_data = $this->getMain()->w->getWeaponData($weapon_num);
				$weapon_level = $playerData->getNowWeaponLevel($user);
				$rate = 0.002;
				$max_lv = 50;
				//$plus_tank = $weapon_level >= $max_lv ? $max_lv * $weapon_data[3] * $rate : $weapon_level * $weapon_data[3] * $rate;
				//10の位を切り下げる
				//$plus_tank = $plus_tank < 10 ? 0 : floor($plus_tank / 10) * 10;
				$plus_tank = 0;

				$tank = $weapon_data[3] + $plus_tank;
				$return_ar[$user] = [
					'inkConsumption' => $weapon_data[2],
					'tank' => [$tank, $tank, $plus_tank],
					'paintAmount' => 0,
					'rate' => 0,
					'fieldNum' => 0,
					'color' => $color
				];
			}
			return $return_ar;
		}else{
			return [];
		}
	}

	/**
	 * 試合するチームを選出
	 * @return array | false
	 */
	public function decideTeams(){
		$selectedTeams = [];
		if($this->canBattlecountReset()){
			$this->BattlecountReset();
		}

		$battleTimeList = [];
		$memberNumList = [];

		foreach($this->teamBattleTime as $teamNum => $battleTime){
			if($teamNum && isset($this->teams[$teamNum]) && !empty($this->member[$teamNum])){
				$battleTimeList[$teamNum] = $battleTime;
				$memberNumList[$teamNum] = count($this->member[$teamNum]);
			}
		}

		if($battleTimeList == null){
			return false;
		}

		for($i = $this->getTeamCount(); $i > 0; $i--){

			#---1st---#

			//一番戦っていないチームを選択
			$candidacy = array_keys($battleTimeList, min($battleTimeList));
			if($candidacy){
				$selectedTeams[0] = $this->mt_array_rand_value($candidacy);
				$membercnt1 = $memberNumList[$selectedTeams[0]];

				//何度も選ばれないように
				unset($battleTimeList[$selectedTeams[0]], $memberNumList[$selectedTeams[0]]);
			}else{
				return false;
			}

			#---2nd---#

			//1チーム目と同じ人数のチームがあるかどうか
			if($candidacy = array_keys($memberNumList, $membercnt1)){
				$candidacy2 = [];
				foreach($candidacy as $n){
					$candidacy2[$n] = $battleTimeList[$n];
				}

				$selectedTeams[1] = $this->mt_array_rand_value(array_keys($candidacy2, min($candidacy2)));

				return $selectedTeams;
			}else{
				if(!$battleTimeList){
					return false;
				}
				$candidacy = $battleTimeList;
				asort($candidacy);
				foreach($candidacy as $teamNum => $battleTime){
					$selectedTeams[1] = $teamNum;
					$membercnt2 = $memberNumList[$teamNum];

					//人数に偏りがないか
					//1チーム目と人数が同じ、または差が1人であれば決定
					if(abs($membercnt1 - $membercnt2) <= (($membercnt1 + $membercnt2) % 2)){
						return $selectedTeams;
					}
				}
			}
		}

		return false;
	}

	private function mt_array_rand_value(array $array, $num = 1){
		static $max;
		if(!$max){
			$max = mt_getrandmax() + 1;
		}
		$num = (int) $num;
		$count = count($array);
		if($num <= 0 || $count < $num){
			return null;
		}
		foreach($array as $key => $value){
			if(!$num){
				break;
			}
			if(mt_rand() / $max < $num / $count){
				//memo: ここがkeyの場合はarray_randと同じ動作になる
				$retval[] = $value;
				--$num;
			}
			--$count;
		}
		return !isset($retval[1]) ? $retval[0] : $retval;
	}
}