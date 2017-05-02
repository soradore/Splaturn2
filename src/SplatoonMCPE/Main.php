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

use SplatoonMCPE\FloatingText;

use pocketmine\block\Block;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\entity\Attribute;
use pocketmine\entity\Effect;
use pocketmine\entity\Entity;

use pocketmine\event\entity\EntityDamageEvent;

use pocketmine\item\Item;

use pocketmine\level\particle\Particle;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\level\particle\TerrainParticle;
use pocketmine\level\particle\DustParticle;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\level\particle\CriticalParticle;
//use pocketmine\level\particle\InkParticle;
use pocketmine\level\sound\LaunchSound;
use pocketmine\level\sound\SplashSound;
use pocketmine\level\sound\ExplodeSound;
use pocketmine\level\sound\AnvilFallSound;
use pocketmine\level\Level;
use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\level\weather\Weather;

use pocketmine\math\Math;
use pocketmine\math\Vector3;

use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\ListTag;

use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\AddItemEntityPacket;
use pocketmine\network\protocol\UpdateAttributesPacket;
//use pocketmine\network\protocol\MoveEntityPacket;
use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\network\protocol\SetEntityDataPacket;
use pocketmine\network\protocol\SetEntityMotionPacket;
use pocketmine\network\protocol\TransferPacket;
//use pocketmine\network\protocol\UpdateBlockPacket;
use pocketmine\network\protocol\BossEventPacket;
use pocketmine\network\protocol\LevelEventPacket;
use pocketmine\event\player\PlayerChatEvent;

use pocketmine\event\Listener;
use pocketmine\event\TranslationContainer;

use pocketmine\plugin\PluginBase;

use pocketmine\scheduler\PluginTask;

use pocketmine\utils\MainLogger;

use pocketmine\Player;
use pocketmine\Server;


class Main extends PluginBase{

	//public  $attribute = [];
	public  $mute = false;
	public  $BattleResultAnimation = null;
	public  $cam = [];
	public  $chatData = [];
	public  $count_time = 0;
	public  $start_time = 0;
	public  $dev = false;
	public $error = 0;
	public  $field = 0;
	public  $game = 1;
	public  $gamestop = false;
	private $leftCheck = [];
	public  $lobbyPos = [532.5, 8, -107.5];
	public  $quitCheck = [];
	public  $reconData = [];
	private $scattersItem = [];
	private $squids = [];
	//private $Squid_Standby = [];
	public  $Task = [];
	private $textParticle;
	private $Timelimit;
	public  $Tips = true;
	//private $tnt_data = [];
	public  $TPanimation = null;
	public  $tprCheckData = [];
	private $unfinished = false;
	public  $view = [];
	private $waterLevel = false;
	public  $warn = [];
	private $winteam = [];
	private $tag = false;
	private $tagTeams = [];

	private $hasSpawn = false;
	private $scanBattleField_data = "";

	private $woolsBlockArray = [];
	private $splatWoolsArray = [];

	public $mute_personal = [];
	public $op_only = false;
	public $needLv = 0;

	private	$list_kaomoji = [
				"_(┐「ε:)_",
				"(*´q｀*)",
				"⊂('ω'⊂ )))Σ≡",
				"(*´ω｀*)",
				"(´･ω･｀)",
				"_(⌒(_´･ω･` )_",
				"(´*ω*｀)",
				"\(* ･ω･ *)/",
				"(´,,･ω･,,｀)",
			];

	public  $area = [
		'mode' => false,
		'extra' => [
			'state' => false,
			'winteam' => 0,
			'time' => 0
		],
		'count' => [
			1 => [
				'c' => 100, //カウントダウン
				'p' => 0 //ペナルティタイム
			],
			2 => [
				'c' => 100, //カウントダウン
				'p' => 0 //ペナルティタイム
			],
		],
		'history' => [//一つ前の確保履歴
			'team' => 0,
			'start' => 0,
			'end' => 0
		],
		'area' => [//現在のエリア別の確保状況
			1 => 0,
			2 => 0
		],
		'areaall' => 0,//確保状況
		'wool' => [],//
		'wools' => []
	];

	public $tweakPosition = [
				0 => [-0.5, -0.5],
				1 => [-0.5,  0.5],
				2 => [ 0.5, -0.5],
				3 => [ 0.5,  0.5]
	];
	public $trypaintData = [
		'player' => [
		],
		'status' => [
			501	 => true, 502	 => true, 503	 => true, 504	 => true, 505	 => true,
			506	 => true, 507	 => true, 508	 => true, 509	 => true, 510 => true,
			511	 => true, 512	 => true, 513	 => true, 514	 => true,
		]
	];

	public function onLoad(){
		$this->getServer()->getCommandMap()->registerAll("splaturn", [
			new StatusUnitCommand($this),
			new BanCommand($this),
			new WarnCommand($this),
			new SetModeCommand($this)
		]);
	}
	
	public function onEnable(){
		date_default_timezone_set('Asia/Tokyo');

		//2017/3/16 エラー出るので移動
		if($this->getServer()->getPluginManager()->getPlugin("UniLoginSystem") != null){
			$this->UniLoginSystem = $this->getServer()->getPluginManager()->getPlugin("UniLoginSystem");
			$this->getLogger()->info("§6ULSが見つかりました");
		}else{
			$this->getLogger()->warning("ULSが見つかりません");
		}
		$this->getServer()->expEnabled = false;
		$this->getServer()->getPluginManager()->registerEvents(new Event($this), $this);
		$this->db = new DataBase();
		$this->a = new Account($this);
		$this->s = new StatusUnit($this);
		$this->lang = new Lang(__DIR__."/lang/", $this->s->getLanguage());
		$this->w = new Weapon($this);
		$this->team = new Team($this);
		$this->entry = new Entry($this);
		$this->team->init();
		$this->setData(true);
		$this->itemselect = new ItemSelect($this, $this->w->getWeaponsDataAll(), $this->lang);
		$this->itemCase = new ItemCase($this);
		$this->seat = new Seat;
		$this->Task['Tips']     = $this->getServer()->getScheduler()->scheduleRepeatingTask(new Random($this), 20*75);
		$this->s->setLoginRestriction();
		$this->s->resetOnlineStat();
		$this->CreativeItemdelete();
		$this->w->setWeaponsDataAllIntoDB();
		$this->updateStage();
		$this->shop = new ItemShop($this, $this->w->getWeaponsDataAll());

		$num = 40;
		$serverInstance = Server::getInstance();
		$serverInstance->setConfigInt("max-players", $num);
		$property = (new \ReflectionClass($serverInstance))->getProperty("maxPlayers");
		$property->setAccessible(true);
		$property->setValue($serverInstance, $num);

/*
		$level = $this->getServer()->getLevelByName("splatt001");
		$level->setTime(0);
		$level->stopTime();
		$level->getWeather()->setCanCalculate(false);
*/
		switch(Server::getInstance()->getPort()){
			case 19133:
				$this->area['mode'] = true;
				$this->setNeedLv(10);
			break;
/*			case 19142:
				$this->area['mode'] = false;
				$this->setNeedLv(20);
			break;*/
			default :
				$this->area['mode'] = false;
				$this->setNeedLv(0);
			break;
		}
	}

	public function onDisable(){
		foreach($this->trypaintData['player'] as $user => $data){
			if(($player = Server::getInstance()->getPlayer($user)) instanceof Player){
				$this->TryPaint($player, false, false);
			}
		}
		$this->a->saveAll(true);

		if($this->s->sno !== 1){//GameServer1
			if($this->s->getServerStatus(1)){//GameServer1
				$s_ap = $this->s->getServerAP(1);//GameServer1
				$address = $s_ap[0];
				$port = $s_ap[1];
				foreach($this->getServer()->getOnlinePlayers() as $player){
					if($player instanceof Player){
						$packet = new TransferPacket();
						$packet->address = $address;
						$packet->port = $port;
						$player->dataPacket($packet);
					}
				}
			}
		}

		$this->TPanimationEnd();
		$this->s->setOffline();
		$this->s->removeAllFromOnlineStat();
	}

	/**
	 * テキスト関連のデータを設定
	 * @param bool $first サーバー起動時の場合true default = false
	 */
	public function setData($first = false){
		$this->battle_field = [
			0 => [
				'level'			 => "splatt001",
			],
			//かいわれ城下町 問題なし
			1 => [
				'name'			 => $this->lang->translateString("battle-field.1.name"),
				'author'		 => "32ki, evers",
				'level'			 => "splatt001",
				'start'			 => [1 => [290, 14, 41, 0, 0], 2 => [289, 14, 118, 180, 0]],
				'scan'			 => [1 => [309, 10, 121], 2 => [269, 13, 37]],
				'respawn-view'	 => [1 => [290, 15, 46, 180, 30], 2 => [289, 15, 113, 0, 30]],
				'comment'		 => $this->lang->translateString("battle-field.1.comment"),
				'color'			 => 0,
				'sec'			 => 5,
				'spawn-radius'	 => 2,
				'view'			 => [289.5, 24, 79.5],
				'cam'			 => [4 => [289.5, 46, 79.5, 270, 90], 5 => [273.5, 16, 105.5, 225, 25]],
				'recon'			 => false,
			],
			//マグマ農園 こわれている
			2 => [
				'name'			 => $this->lang->translateString("battle-field.2.name"),
				'author'		 => "Gonbe34, NO_NAMEo16",
				'level'			 => "splatt001",
				'start'			 => [1 => [-0.5, 16.5, 284.5, 315, 0], 2 => [43.5, 16.5, 330.5, 135, 0]],
				'scan'			 => [1 => [49, 10, 335], 2 => [-6, 14, 279]],
				'respawn-view'	 => [1 => [2.5, 17, 287.5, 135, 0], 2 => [40.5, 17, 327.5, 315, 0]],
				'comment'		 => $this->lang->translateString("battle-field.2.comment"),
				'color'			 => 0,
				'sec'			 => 5,
				'spawn-radius'	 => 1.5,
				'view'			 => [11.5, 21, 317.5],
				'cam'			 => [4 => [21.5, 40, 307.5, 180, 90]],
				'recon'			 => false,
			],

			//チカ水ドーム 問題なし
			4 => [
				'name'			 => $this->lang->translateString("battle-field.4.name"),
				'author'		 => "Arutairu",
				'level'			 => "splatt001",
				'start'			 => [1 => [111.5, 7, 293.5, 270, 0], 2 => [151.5, 7, 293.5, 90, 0]],
				'scan'			 => [1 => [152, 12, 280], 2 => [110, 3, 306]],
				'respawn-view'	 => [1 => [114.5, 8.75, 293.5, 90, 25], 2 => [148.5, 8.75, 293.5, 270, 25]],
				'comment'		 => $this->lang->translateString("battle-field.4.comment"),
				'color'			 => 7,
				'sec'			 => 5,
				'biome'			 => 6,//Swampland
				'spawn-radius'	 => 1.25,
				'view'			 => [125, 16, 297],
				'cam'			 => [4 => [131.5, 27, 293.5, 180, 90], 5 => [110.5, 16, 280.5, 315, 25], 6 => [152.5, 16, 306.5, 135, 25]],
				'recon'			 => false,
			],

			//螺旋トルネード そんざいしない
			/*6 => [
				'name'			 => $this->lang->translateString("battle-field.6.name"),
				'author'		 => "SHIN_0731",
				'level'			 => "splatt001",
				'start'			 => [1 => [-114.5, 13, 58.5, 270, 0], 2 => [-87.5, 13, 20.5, 90 ,0]],
				'scan'			 => [1 => [-79, 10, 19], 2 => [-124, 16, 59]],
				'respawn-view'	 => [1 => [-111.5, 14.5, 58.5, 90, 25], 2 => [-90.5, 14.5, 20.5, 270, 25]],
				'comment'		 => $this->lang->translateString("battle-field.6.comment"),
				'color'			 => 0,
				'sec'			 => 5,
				'spawn-radius'	 => 1.25,
				'view'			 => [-101, 22, 39.5],
				'cam'			 => [4 => [-101, 44, 39.5, 180, 90], 5 => [-95.5, 26, 56.5, 145, 50], 6 => [-106.5, 26, 22.5, 325, 50]],
				'recon'			 => true,
			],*/
			//ミソノメ油田 そんざいしない
			/*7 => [
				'name'			 => $this->lang->translateString("battle-field.7.name"),
				'author'		 => "music0343",
				'level'			 => "splatt001",
				'start'			 => [1 => [-13, 12.5, 162, 90, 0], 2 => [-13, 12.5, 132, 90, 0]],
				'scan'			 => [1 => [-12, 11, 164], 2 => [-55, 4, 129]],
				'respawn-view'	 => [1 => [-16, 13.75, 162, 270, 17.5], 2 => [-16, 13.75, 132, 270, 17.5]],
				'comment'		 => $this->lang->translateString("battle-field.7.comment"),
				'color'			 => 7,
				'sec'			 => 5,
				'spawn-radius'	 => 1.5,
				'view'			 => [-32.5, 23, 147],
				'cam'			 => [4 => [-32.5, 44, 147, 270, 90], 5 => [-22, 22, 158.5, 135, 50], 6 => [-22.5, 22, 133.5, 45, 50]],
				'recon'			 => true,
			],*/
			//エフィラ図書館 問題なし
			8 => [
				'name'			 => $this->lang->translateString("battle-field.8.name"),
				'author'		 => "moyasan",
				'level'			 => "splatt001",
				//'start'			 => [1 => [301, 7, 167, 90, 0], 2 => [272, 7, 146, 270, 0]],
				'start'			 => [1 => [520, 23, 151, 45, 0], 2 => [459, 23, 145, 225, 0]],
				//'scan'			 => [1 => [268, 5, 143], 2 => [304, 21, 169]],
				'scan'			 => [1 => [456, 5, 97], 2 => [522, 19, 198]],
				//'respawn-view'	 => [1 => [297,8,167,270,15], 2 => [276,8,146,90,15]],
				'respawn-view'	 => [1 => [518.75, 23.7, 152.25, 225, 20], 2 => [460.25, 23.7, 143.75, 45, 20]],
				'comment'		 => $this->lang->translateString("battle-field.8.comment"),
				'color'			 => 12,
				'sec'			 => 5,
				'biome'			 => false,
				'spawn-radius'	 => 1.5,
				'time-limit'	 => 180,
				//'view'			 => [287,24,156],
				'view'			 => [489.5, 28, 138.5],
				//'cam'			 => [4 => [287, 24, 156, 270, 90], 5 => [270.5, 15, 168.5, 225, 25], 6 => [302.5, 20, 168.5, 135, 25]],
				'cam'			 => [4 => [489.5, 55, 148, 90, 90], 5 => [457.5, 34.5, 197.5, 225, 30], 6 => [488, 21, 115, 345, 35]],
				'recon'			 => false,
			],
			//張りぼてパーキング 問題なし
			9 => [
				'name'			 => $this->lang->translateString("battle-field.9.name"),
				'author'		 => "messii, Mossun281, 32ki, NO_NAMEo16, trasta",
				'level'			 => "splatt001",
				'start'			 => [1 => [387, 22, -99, 90, 0], 2 => [339, 22, -115, 270, 0]],
				'scan'			 => [1 => [383, 4, -92], 2 => [342, 16, -122]],
				'respawn-view'	 => [1 => [383, 23.25, -99, 270, 7.5], 2 => [343, 23.25, -115, 90, 7.5]],
				'comment'		 => $this->lang->translateString("battle-field.9.comment"),
				'color'			 => 7,
				'sec'			 => 5,
				'spawn-radius'	 => 1.5,
				'view'			 => [362.5, 28, -106.5],
				'cam'			 => [4 => [362.5, 40, -106, 0, 90]],
				'recon'			 => false,
			],
			//メガロパフォレスト 問題なし
			10 => [
				'name'			 => $this->lang->translateString("battle-field.10.name"),
				'author'		 => "moyasan",
				'level'			 => "splatt001",
				'start'			 => [1 => [259, 7, 212, 270, 0], 2 => [294, 7, 223, 90, 0]],
				'scan'			 => [1 => [241, 4, 231], 2 => [311, 12, 203]],
				'respawn-view'	 => [1 => [262, 8.75, 212, 90, 15], 2 => [290, 8.75, 223, 270, 15]],
				'comment'		 => $this->lang->translateString("battle-field.10.comment"),
				'color'			 => 12,
				'sec'			 => 5,
				'spawn-radius'	 => 1.5,
				'view'			 => [276.5, 27, 217.5],
				'cam'			 => [4 => [276.5, 35, 217.5, 0, 180], 5 => [246, 16.5, 207, 310, 20], 6 => [291.5, 24, 226.5, 125, 30]],
				'recon'			 => false,
			],
			//バッタモール こわれている
			11 => [
				'name'			 => $this->lang->translateString("battle-field.11.name"),
				'author'		 => "32ki, coconatsu, 0929hitoshi",
				'level'			 => "splatt001",
				'start'			 => [1 => [98.5, 16, -285.5, 90, 0], 2 => [-39.5, 16, -293.5, 270, 0]],
				'scan'			 => [1 => [-43, 16, -311], 2 => [101, 6, -269]],
				'respawn-view'	 => [1 => [94, 18.5, -285.5, 270, 12.5], 2 => [-35, 18.5, -293.5, 90, 12.5]],
				'comment'		 => $this->lang->translateString("battle-field.11.comment"),
				'color'			 => 0,
				'sec'			 => 5,
				'time-limit'	 => 150,
				'spawn-radius'	 => 2,
				'view'			 => [29.5, 26, -289.5],
				'cam'			 => [4 => [29.5, 58, -289.5, 0, 90]],
				'recon'			 => false,
			],
			//ハイ鉱山 問題なし
			12 => [
				'name'			 => $this->lang->translateString("battle-field.12.name"),
				'author'		 => "32ki, 0929hitoshi",
				'level'			 => "splatt001",
				'start'			 => [1 => [212, 13, -236, 270, 0], 2 => [189, 13, -257, 90, 0]],
				'scan'			 => [1 => [178, 4, -274], 2 => [222,15,-220]],
				'respawn-view'	 => [1 => [215, 14.5, -236, 90, 10], 2 => [186, 14.5, -257, 270, 10]],
				'comment'		 => $this->lang->translateString("battle-field.12.comment"),
				'color'			 => 8,
				'sec'			 => 5,
				'time-limit'	 => 150,
				'spawn-radius'	 => 1.5,
				'view'			 => [200.5, 27, -246.5],
				'cam'			 => [4 => [200.5, 38.5, -246.5, 90, 90]],
				'recon'			 => false,
			],
			//Nハギパーク 問題なし
			13 => [
				'name'			 => $this->lang->translateString("battle-field.13.name"),
				'author'		 => "32ki, popopo3390, coconatsu",
				'level'			 => "splatt001",
				'start'			 => [1 => [136.5, 15, -153.5, 180, 0], 2 => [118.5, 15, -263.5, 0, 0]],
				'scan'			 => [1 => [162, 16, -152], 2 => [92, 4, -266]],
				'area'			 => [
					1 => [[116, 5, -222],[123, 6, -213]], 
					2 => [[131, 5, -205], [138, 6, -196]]
				],
				'respawn-view'	 => [1 => [136.6, 17, -156.5, 0, 22.5], 2 => [118.5, 17, -260.5, 180, 22.5]],
				'comment'		 => $this->lang->translateString("battle-field.13.comment"),
				'color'			 => 7,
				'sec'			 => 5,
				'time-limit'	 => 180,
				'spawn-radius'	 => 1.5,
				'view'			 => [127.5, 27, -208.5],
				'cam'			 => [4 => [127.5, 50, -208.5, 90, 90]],
				'recon'			 => true,
			],
			//ハニハニリゾート&マグマ こわれている&そんざいしない
			/*14 => [
				'name'			 => $this->lang->translateString("battle-field.14.name"),
				'author'		 => "32ki",
				'level'			 => "splatt001",
				'start'			 => [1 => [-133, 16, 385, 240, 0], 2 => [-56, 16, 336, 60, 0]],
				'scan'			 => [1 => [-136, 4, 389], 2 => [-54, 20, 331]],
				'respawn-view'	 => [1 => [-130.5, 18, 382.5, 45, 22.5], 2 => [-58.5, 18, 338.5, 225, 22.5]],
				'comment'		 => $this->lang->translateString("battle-field.14.comment"),
				'color'			 => 0,
				'sec'			 => 5,
				'time-limit'	 => 150,
				'spawn-radius'	 => 1.5,
				'view'			 => [-94.5, 27, 360.5],
				'cam'			 => [4 => [-94.5, 65, 360.5, 180, 90]],
				'recon'			 => true,
			],*/
			//ボルボックス地下鉄道 問題なし
			15 => [
				'name'			 => $this->lang->translateString("battle-field.15.name"),
				'author'		 => "nuyoy, moyasan",
				'level'			 => "splatt001",
				'start'			 => [1 => [256.5, 24, 289.5, 360, 0], 2 => [190.5, 24, 426.5, 180, 0]],
				'scan'			 => [1 => [184, 5, 429], 2 => [262, 23, 286]],
				'respawn-view'	 => [1 => [256.5, 26, 292.5, 180, 20], 2 => [190.5, 26, 423.5, 360, 20]],
				'comment'		 => $this->lang->translateString("battle-field.15.comment"),
				'color'			 => 7,
				'sec'			 => 5,
				'time-limit'	 => 150,
				'spawn-radius'	 => 2,
				'view'			 => [223.5, 27, 358],
				'cam'			 => [4 => [223.5, 29, 358.5, 270, 90]],
				'biome'			 => false,
				'recon'			 => false,
			],
			//プルテウス地底火山 問題なし
			16 => [
				'name'			 => $this->lang->translateString("battle-field.16.name"),
				'author'		 => "moyasan",
				'level'			 => "splatt001",
				'start'			 => [1 => [398, 20, 217, 45, 0], 2 => [365, 20, 333, 225, 0]],
				'scan'			 => [1 => [352, 8, 208], 2 => [410, 29, 341]],
				'respawn-view'	 => [1 => [396.8, 22.5, 218.2, 225, 20], 2 => [366.2, 22.5, 331.8, 45, 20]],
				'comment'		 => $this->lang->translateString("battle-field.16.comment"),
				'color'			 => 7,
				'sec'			 => 5,
				'time-limit'	 => 150,//仮
				'spawn-radius'	 => 1.5,
				'cam'			 => [4 => [381.5, 26.5, 275, 270, 90]],
				'biome'			 => 8,//Nether
				'recon'			 => false,
			],
			//骨組みパーキング そんざいしない
			/*17 => [
				'name'			 => $this->lang->translateString("battle-field.17.name"),
				'author'		 => "32ki, nuyoy, 0929hitoshi, moyasan",
				'level'			 => "splatt001",
				'start'			 => [1 => [-169, 42.5, -182, 180, 0], 2 => [-160, 42.5, -285, 0, 0]],
				'scan'			 => [1 => [-136, 6, -288], 2 => [-194, 41, -180]],
				'respawn-view'	 => [1 => [-169, 44.5, -185, 0, 40], 2 => [-160, 44.5, -282, 180, 40]],
				'comment'		 => $this->lang->translateString("battle-field.17.comment"),
				'color'			 => 7,
				'sec'			 => 5,
				'time-limit'	 => 150,
				'spawn-radius'	 => 1.5,
				'view'			 => [-164.5, 55, -233.5],
				'cam'			 => [4 => [-164.5, 70.5, -233.5, 90, 90]],
				'recon'			 => true,
			],*/
			//まないた平原 そんざいしない
			/*18 => [
				'name'			 => $this->lang->translateString("battle-field.0.name"),//前は0だったので！！(0929hitoshi)
				'author'		 => "",
				'level'			 => "splatt001",
				'start'			 => [1 => [-36, 16.5, 27, 330,0], 2 => [-9, 16.5, 102, 150, 0]],
				'scan'			 => [1 => [-40, 9, 105], 2 => [-6, 28, 23]],
				'respawn-view'	 => [1 => [-34, 18, 29, 150, 15], 2 => [-11, 18, 100, 330, 15]],
				'comment'		 => $this->lang->translateString("battle-field.0.comment"),
				'color'			 => 0,
				'sec'			 => 5,
				'time-limit'	 => 150,
				'spawn-radius'	 => 1.5,
				'recon'			 => false,
			],*/
			//(もやさんのマップ) そんざいしない
			/*19 => [
				'name'			 => $this->lang->translateString("battle-field.19.name"),
				'author'		 => "moyasan",
				'level'			 => "splatt001",
				'start'			 => [1 => [620, 14, 93, 270, 0], 2 => [647, 14, 50, 90, 0]],
				'scan'			 => [1 => [611, 4, 36], 2 => [655, 12, 106]],
				'respawn-view'	 => [1 => [622.5, 16.25, 93, 90, 25], 2 => [644.5, 16.25, 50, 270, 25]],
				'comment'		 => $this->lang->translateString("battle-field.19.comment"),
				'color'			 => 8,
				'sec'			 => 5,
				'time-limit'	 => 150,
				'spawn-radius'	 => 1.5,
				'view'			 => [633.5, 17, 71.5],
				'cam'			 => [4 => [633.5, 28.5, 71.5, 90, 90]],
				'recon'			 => false,
			],*/
			//
			/*20 => [
				'name'			 => $this->lang->translateString("battle-field.20.name"),
				'author'		 => "moyasan",
				'level'			 => "splatt001",
				'start'			 => [1 => [587, 29, 246, 180, 0], 2 => [587, 29, 129, 0, 0]],
				'scan'			 => [1 => [561, 4, 126], 2 => [588, 27, 248]],
				'respawn-view'	 => [1 => [587, 30.5, 243, 0, 15], 2 => [587, 30.5, 132, 180, 15]],
				'comment'		 => $this->lang->translateString("battle-field.20.comment"),
				'color'			 => 12,
				'sec'			 => 5,
				'time-limit'	 => 150,
				'spawn-radius'	 => 1.5,
				'view'			 => [587, 34, 187.5],
				'cam'			 => [4 => [587, 60, 187.5, 90, 90]],
				'recon'			 => false,
			],*/
			//クリオネ海底神殿
			21 => [
				'name'			 => $this->lang->translateString("battle-field.21.name"),
				'author'		 => "moyasan",
				'level'			 => "splatt001",
				'start'			 => [1 => [506, 24, -357, 180, 0], 2 => [483, 24, -438, 0, 0]],
				'scan'			 => [1 => [470, 34, -442], 2 => [518, 10, -354]],
				'area'			 => [
					1 => [[486, 15, -403],[502, 15, -393]], 
					//2 => [[485, 15, -404],[503, 15, -392]]
				],
				'respawn-view'	 => [1 => [506, 26, -361, 360, 35], 2 => [483, 26, -434, 180, 35]],
				'comment'		 => $this->lang->translateString("battle-field.21.comment"),
				'color'			 => 0,
				'sec'			 => 5,
				'spawn-radius'	 => 2,
				'view'			 => [494.5, 32, -399.5],
				'cam'			 => [4 => [506, 26, -361, 0, 0], 5 => [482, 26, -434, 0, 0]],
				'recon'			 => false,
			],

			//メガロパフォレスト奥地
			22 => [
				'name'			 => $this->lang->translateString("battle-field.22.name"),
				'author'		 => "moyasan",
				'level'			 => "splatt001",
				'start'			 => [1 => [-247, 27, -112, 270, 0], 2 => [-138, 27, -112, 90, 0]],
				'scan'			 => [1 => [-264, 37, -128], 2 => [-123, 5, -45]],
				'respawn-view'	 => [1 => [-244, 28, -112, 90, 25], 2 => [-142, 28, -112, 270, 25]],
				'comment'		 => $this->lang->translateString("battle-field.22.comment"),
				'area'			 => [
					1 => [[-197, 17, -102],[-189, 17, -92]], 
				],
				'color'			 => 12,
				'sec'			 => 5,
				'spawn-radius'	 => 1.5,
				'view'			 => [-193, 39, -96],
				'cam'			 => [4 => [-193, 61, -96, 180, 90], 5 => [-63.5, 19, -24.5, 225, 30], 6 => [-71.5, 18, -49.5, 45, 30]],
				'recon'			 => true,
			],

			//ビントロ
			23 => [
				'name'			 => $this->lang->translateString("battle-field.23.name"),
				'author'		 => "moyasan",
				'level'			 => "splatt001",
				'start'			 => [1 => [-59, 31, -122, 180, 20], 2 => [-59, 31, -239, 0, 20]],
				'scan'			 => [1 => [-91, 38, -249], 2 => [-28, 6, -113]],
				'respawn-view'	 => [1 => [-59.5, 32, -235, 0, 25], 2 => [-59.5, 32, -127, 180, 25]],
				'comment'		 => $this->lang->translateString("battle-field.23.comment"),
				'area'			 => [
					1 => [[-50, 11, -178],[-39, 11, -172]], 
					2 => [[-80, 11, -190], [-69, 11, -184]]
				],
				'color'			 => 12,
				'sec'			 => 5,
				'spawn-radius'	 => 1.5,
				'view'			 => [-59, 36, -180],
				'cam'			 => [4 => [-59.5, 60, -180, 90, 90], 5 => [-59.5, 60, -180, 90, 90], 6 => [-59.5, 60, -180, 90, 90]],
				'recon'			 => true,
			],

			//イカダモキャンプ場
			24 => [
				'name'			 => $this->lang->translateString("battle-field.24.name"),
				'author'		 => "stars",
				'level'			 => "splatt001",
				'start'			 => [1 => [42, 20, -34, 270, 20], 2 => [151, 20, -58, 90, 20]],
				'scan'			 => [1 => [39, 13, -15], 2 => [155, 22, -78]],
				'respawn-view'	 => [1 => [44, 21, -34, 90, 0], 2 => [149, 21, -58, 270, 0]],
				'comment'		 => $this->lang->translateString("battle-field.24.comment"),
				'area'			 => [
					1 => [[87, 18, -49],[93, 18, -40]], 
					2 => [[99, 18, -53], [105, 18, -44]]
				],
				'color'			 => 12,
				'sec'			 => 5,
				'time-limit'	 => 120,
				'spawn-radius'	 => 2,
				'view'			 => [96, 30, -45],
				'cam'			 => [4 => [96, 50, -45, 90, 90], 5 => [42, 50, -34, 90, 90], 6 => [151, 20, -58, 90, 90]],
				'recon'			 => true,
			],

			//ぷぷぷさんのマップ
			/*x => [
				'name'			 => "",
				'author'		 => "pupupu",
				'level'			 => "splatt001",
				'start'			 => [1 => [x, y, z, yaw, pitch], 2 => [x, y, z, yaw, pitch]],
				'scan'			 => [1 => [x, y, z], 2 => [x, y, z]],
				'respawn-view'	 => [1 => [x, y, z, yaw, pitch], 2 => [x, y, z, yaw, pitch]],
				'comment'		 => "",
				'color'			 => 0,
				'sec'			 => 5,
				'time-limit'	 => 120,
				'spawn-radius'	 => 2,
				'view'			 => [x, y, z],//試合観戦でテレポートする座標 (必須ではない)
				'cam'			 => [4 => [x, y, z, yaw, pitch], 5 => [x, y, z, yaw, pitch], 6 => [x, y, z, yaw, pitch]],//4=>フィールド中央, 5 => 地点A, 6 => 地点B (必須ではない)
				'recon'			 => false,
			],*/

			//セカライン
			25 => [
				'name'			 => $this->lang->translateString("battle-field.25.name"),
				'author'		 => "stars",
				'level'			 => "splatt001",
				'start'			 => [1 => [ -144, 16, -341, 270, 20], 2 => [-26, 16, -386, 90, 20]],
				'scan'			 => [1 => [-150, 19, -331], 2 => [-21, 5, -397]],
				'respawn-view'	 => [1 => [-142, 17, -341, 90, 0], 2 => [-28, 17, -386, 270, 0]],
				'comment'		 => $this->lang->translateString("battle-field.25.comment"),
				'area'			 => [
					1 => [[-90, 5, -371],[-80, 5, -357]]
				],
				'color'			 => 7,
				'sec'			 => 5,
				'time-limit'	 => 150,
				'spawn-radius'	 => 2,
				'view'			 => [-85, 23, -364],
				'cam'			 => [4 => [-85, 35, -364, 90, 90], 5 => [-144, 16, -341, 90, 90], 6 => [-26, 16, -386, 90, 90]],
				'recon'			 => true,
			],

			//シロザメ宮殿
			26 => [
				'name'			 => $this->lang->translateString("battle-field.26.name"),
				'author'		 => "nuyoy",
				'level'			 => "splatt001",
				'start'			 => [1 => [333, 22, -588, 270, 0], 2 => [216, 22, -584, 90, 0]],
				'scan'			 => [1 => [335, 30, -621], 2 => [211, 6, -543]],
				'respawn-view'	 => [1 => [330, 23, -588, 90, 0], 2 => [218, 23, -584, 270, 0]],
				'comment'		 => $this->lang->translateString("battle-field.26.comment"),
				'area'			 => [
					1 => [[266, 7, -590],[282, 7, -583]]
				],
				'color'			 => 8,
				'sec'			 => 5,
				'time-limit'	 => 120,
				'spawn-radius'	 => 2,
				'view'			 => [274, 26, -587],
				'cam'			 => [4 => [274, 33, -587, 90, 90], 5 => [333, 22, -588, 90, 90], 6 => [216, 22, -584, 90, 90]],
				'recon'			 => false,
			],

			//ヤドカリ
			27 => [
				'name'			 => $this->lang->translateString("battle-field.27.name"),
				'author'		 => "moyasan",
				'level'			 => "splatt001",
				'start'			 => [1 => [50, 17, -566, 0, 20], 2 => [62, 17, -428, 180, 20]],
				'scan'			 => [1 => [34, 16, -575], 2 => [77, 8, 420]],
				'respawn-view'	 => [1 => [50, 18, -564, 180, 0], 2 => [62, 18, -430, 0, 0]],
				'comment'		 => $this->lang->translateString("battle-field.27.comment"),
				'area'			 => [
					1 => [[50, 9, -504],[61, 9, -491]]
				],
				'color'			 => 8,
				'sec'			 => 5,
				'time-limit'	 => 150,
				'spawn-radius'	 => 2,
				'view'			 => [55, 21, -498],
				'cam'			 => [4 => [55, 50, -498, 90, 90], 5 => [50, 17, -566, 90, 90], 6 => [62, 17, -428, 90, 90]],
				'recon'			 => true,
			],

			//〆アジ
			28 => [
				'name'			 => $this->lang->translateString("battle-field.28.name"),
				'author'		 => "stars",
				'level'			=> "splatt001",
				'start'			 => [1 => [199, 29, -748, 180, 20], 2 => [205, 29, -871, 0, 20]],
				'scan'			 => [1 => [180, 12, -876], 2 => [223, 27, -744]],
				'respawn-view'	 => [1 => [199, 30, -752, 0, 0], 2 => [205, 30, -868, 180, 0]],
				'comment'		 => $this->lang->translateString("battle-field.28.comment"),
				'area'			 => [
					1 => [[ 190, 12, -813],[ 213, 12, -807]]
				],
				'color'			 => 7,
				'sec'			 => 5,
				'time-limit'	 => 150,
				'spawn-radius'	 => 2,
				'view'			 => [202, 43, -811],
				'cam'			 => [4 => [202, 45, -811, 0, -90], 5 => [199, 30, -752, 180, 0], 6 => [205, 30, -871, 0, 0]],
				'recon'			 => true,
			],

			//プラティ
			29 => [
				'name'			 => $this->lang->translateString("battle-field.29.name"),
				'author'		 => "tsukinomiya1206",
				'level'			=> "splatt001",
				'start'			 => [1 => [117, 23, -629, 180, 20], 2 => [164, 23, -700, 0, 20]],
				'scan'			 => [1 => [110, 8, -706], 2 => [170, 23, -624]],
				'respawn-view'	 => [1 => [117, 24, -633, 0, 20], 2 => [164, 24, -697, 180, 0]],
				'comment'		 => $this->lang->translateString("battle-field.29.comment"),
				'area'			 => [
					1 => [[ 136, 9, -671],[ 144, 9, -659]]
				],
				'color'			 => 8,
				'sec'			 => 5,
				'time-limit'	 => 150,
				'spawn-radius'	 => 2,
				'view'			 => [140, 29.3, -665],
				'cam'			 => [4 => [140, 29, -665, 0, -90], 5 => [117, 26, -629, 180, 0], 6 => [164, 26, -700, 0, 0]],
				'recon'			 => true,
			],

			//骨組み再現
			30 => [
				'name'			 => $this->lang->translateString("battle-field.30.name"),
				'author'		 => "fresh1925",
				'level'			=> "splatt001",
				'start'			 => [1 => [-458, 47.6, -300, 270, 20], 2 => [-355, 47.6, -291, 90, 20]],
				'scan'			 => [1 => [-461, 10, -325], 2 => [-353, 46, -267]],
				'respawn-view'	 => [1 => [-455, 49, -300, 90, 20], 2 => [-359,49, -291, 270, 20]],
				'comment'		 => $this->lang->translateString("battle-field.30.comment"),
				'area'			 => [
					1 => [[ -404, 13, -317],[ -395, 13, -312]],
					2 => [[ -419, 13, -280],[ -410, 13, -275]]
				],
				'color'			 => 7,
				'sec'			 => 5,
				'time-limit'	 => 150,
				'spawn-radius'	 => 2,
				'view'			 => [-407, 56, -296],
				'cam'			 => [4 => [-406.5, 67, -295.5, 0, -90], 5 => [-458, 49, -300, 270, 0], 6 => [-355, 49, -291, 90, 0]],
				'recon'			 => true,
			],

			//イカ研究所
			31 => [
				'name'			 => $this->lang->translateString("battle-field.31.name"),
				'author'		 => "moyasan",
				'level'			=> "splatt001",
				'start'			 => [1 => [262, 19, -411, 180, 20], 2 => [234, 19, -357, 0, 20]],
				'scan'			 => [1 => [229, 8, -423], 2 => [266, 17, -346]],
				'respawn-view'	 => [1 => [262, 20, -415, 0, 280], 2 => [234, 20, -353, 180, 0]],
				'comment'		 => $this->lang->translateString("battle-field.31.comment"),
				'area'			 => [
					1 => [[ 241, 8, -388],[ 254, 8, -381]]
				],
				'color'			 => 0,
				'sec'			 => 5,
				'time-limit'	 => 150,
				'spawn-radius'	 => 2,
				'view'			 => [247, 26, -385],
				'cam'			 => [4 => [247, 27, -385, 0, -90], 5 => [262, 21, -411, 180, 0], 6 => [234, 21, -357, 0, 0]],
				'recon'			 => true,
			],

			//ブラックバスストリート
			32 => [
				'name'			 => $this->lang->translateString("battle-field.32.name"),
				'author'		 => "yotuball",
				'level'			=> "splatt001",
				'start'			 => [1 => [-92, 17, -587, 0, 20], 2 => [-55, 17, -530, 180, 20]],
				'scan'			 => [1 => [-100, 5, -590], 2 => [-48, 16, -528]],
				'respawn-view'	 => [1 => [-92, 18, -584, 180, 0], 2 => [-55, 18, -534, 0, 0]],
				'comment'		 => $this->lang->translateString("battle-field.32.comment"),
				'area'			 => [
					1 => [[ -71, 5, -563],[ -63, 5, -558]],
					2 => [[ -85, 5, -560],[ -77, 5, -555]]
				],
				'color'			 => 8,
				'sec'			 => 5,
				'time-limit'	 => 120,
				'spawn-radius'	 => 2,
				'view'			 => [-74, 24, -559],
				'cam'			 => [4 => [-74, 30, -559, 0, -90], 5 => [-92, 18, -587, 0, 0], 6 => [-55, 18, -530, 180, 0]],
				'recon'			 => true,
			],

			//イソギンチャク団地
			33 => [
				'name'			 => $this->lang->translateString("battle-field.33.name"),
				'author'		 => "yotuball",
				'level'			=> "splatt001",
				'start'			 => [1 => [-271, 19, -467, 0, 20], 2 => [-282, 19, -393, 180, 20]],
				'scan'			 => [1 => [-302, 4, -470], 2 => [-251, 18, -390]],
				'respawn-view'	 => [1 => [-271, 20, -464, 180, 0], 2 => [-282, 20, -397, 0, 0]],
				'comment'		 => $this->lang->translateString("battle-field.33.comment"),
				'area'			 => [
					1 => [[ -289, 12, -435],[ -283, 12, -426]],
					2 => [[ -271, 12, -435],[ -265, 12, -426]]
				],
				'color'			 => 0,
				'sec'			 => 5,
				'time-limit'	 => 120,
				'spawn-radius'	 => 2,
				'view'			 => [-277, 27, -430],
				'cam'			 => [4 => [-277, 30, -430, 0, -90], 5 => [-271, 20, -467, 0, 0], 6 => [-282, 20, -397, 180, 0]],
				'recon'			 => true,
			],

			//新シロザメ
			34 => [
				'name'			 => $this->lang->translateString("battle-field.34.name"),
				'author'		 => "nuyoy",
				'level'			=> "splatt001",
				'start'			 => [1 => [369, 27, -290, 270, 20], 2 => [481, 27, -301, 90, 20]],
				'scan'			 => [1 => [372, 9, -323], 2 => [477, 25, -269]],
				'respawn-view'	 => [1 => [373, 28, -290, 90, 0], 2 => [477, 28, -301, 270, 0]],
				'comment'		 => $this->lang->translateString("battle-field.34.comment"),
				'area'			 => [
					1 => [[ 430, 9, -309],[ 438, 9, -301]],
					2 => [[ 411, 9, -291],[ 419, 9, -283]]
				],
				'color'			 => 8,
				'sec'			 => 5,
				'time-limit'	 => 150,
				'spawn-radius'	 => 2,
				'view'			 => [425, 36, -296],//試合観戦でテレポートする座標 (必須ではない)
				'cam'			 => [4 => [425, 38, -296, 0, -90], 5 => [369, 28, -290, 270, 0], 6 => [481, 28, -301, 90, 0]],
				'recon'			 => false,
			],

			//真ミソノメ
			35 => [
				'name'			 => $this->lang->translateString("battle-field.35.name"),
				'author'		 => "Asagi0343",
				'level'			=> "splatt001",
				'start'			 => [1 => [565, 17.5, -827, 225, 20], 2 => [565, 17.5, -902, 315, 20]],
				'scan'			 => [1 => [561, 9, -908], 2 => [671, 28, -822]],
				'respawn-view'	 => [1 => [568, 19, -830, 45, 0], 2 => [568, 19, -899, 135, 20]],
				'comment'		 => $this->lang->translateString("battle-field.35.comment"),
				'area'			 => [
					1 => [[ 619, 23, -872],[ 629, 23, -858]]
				],
				'color'			 => 8,
				'sec'			 => 5,
				'time-limit'	 => 180,
				'spawn-radius'	 => 2,
				'view'			 => [616, 36, -865],
				'cam'			 => [4 => [616, 40, -865, 0, -90], 5 => [518, 19, -827, 225, 0], 6 => [518, 19, -902, 315, 0]],
				'recon'			 => true,
			],

			//アイナメ
			36 => [
				'name'			 => $this->lang->translateString("battle-field.36.name"),
				'author'		 => "stars",
				'level'			=> "splatt001",
				'start'			 => [1 => [683, 15, 82, 270, 20], 2 => [847, 15, 85, 90, 20]],
				'scan'			 => [1 => [680, 6, 52], 2 => [849, 13, 114]],
				'respawn-view'	 => [1 => [687, 16, 82, 90, 0], 2 => [843, 16, 85, 270, 0]],
				'comment'		 => $this->lang->translateString("battle-field.36.comment"),
				'area'			 => [
					1 => [[ 777, 6, 99],[ 784, 6, 108]],
					2 => [[ 745, 6, 58],[ 752, 6, 67]]
				],
				'color'			 => 7,
				'sec'			 => 5,
				'time-limit'	 => 120,
				'spawn-radius'	 => 2,
				'view'			 => [765, 33, 83],
				'cam'			 => [4 => [765, 37, 83, 0, -90], 5 => [683, 16, 82, 270, 0], 6 => [847, 16, 85, 90, 0]],
				'recon'			 => false,
			],

			//ガンガゼ監獄跡地
			37 => [
				'name'			 => $this->lang->translateString("battle-field.37.name"),
				'author'		 => "moyasan",
				'level'			=> "splatt001",
				'start'			 => [1 => [1067, 20, -56, 180, 20], 2 => [1064, 20, -209, 0, 20]],
				'scan'			 => [1 => [1026, 5, -213], 2 => [1104, 21, -53]],
				'respawn-view'	 => [1 => [1067, 21, -61, 0, 0], 2 => [1064, 21, -204, 180, 0]],
				'comment'		 => $this->lang->translateString("battle-field.37.comment"),
				'area'			 => [
					1 => [[ 1058, 5, -138],[ 1072, 5, -128]]
				],
				'color'			 => 7,
				'sec'			 => 5,
				'time-limit'	 => 120,
				'spawn-radius'	 => 2,
				'view'			 => [1065.5, 26, -132.5],
				'cam'			 => [4 => [1065.5, 26, -132.5, 0, -90], 5 => [1067, 21, -56, 180, 0], 6 => [1064, 21, -209, 0, 0]],
				'recon'			 => true,
			],

			//フロックス
			38 => [
				'name'			 => $this->lang->translateString("battle-field.38.name"),
				'author'		 => "tsukinomiya1206",
				'level'			=> "splatt001",
				'start'			 => [1 => [667, 14, 239, 45, 20], 2 => [598, 14, 311, 225, 20]],
				'scan'			 => [1 => [594, 5, 235], 2 => [670, 12, 314]],
				'respawn-view'	 => [1 => [664, 15, 242, 225, 20], 2 => [601, 15, 308, 45, 20]],
				'comment'		 => $this->lang->translateString("battle-field.38.comment"),
				'area'			 => [
					1 => [[ 627, 5, 269],[ 638, 5, 280]]
				],
				'color'			 => 12,
				'sec'			 => 5,
				'time-limit'	 => 120,
				'spawn-radius'	 => 2,
				'view'			 => [630, 20, 319],
				'cam'			 => [4 => [633, 30, 275, 0, -90], 5 => [667, 15, 239, 45, 0], 6 => [598, 15, 311, 225, 0]],
				'recon'			 => true,
			],

			//トビウオ
			39 => [
				'name'			 => $this->lang->translateString("battle-field.39.name"),
				'author'		 => "hakurou_v",
				'level'			=> "splatt001",
				'start'			 => [1 => [498, 15.5, 619, 270, 20], 2 => [597, 15.5, 619, 90, 20]],
				'scan'			 => [1 => [494, 8, 596], 2 => [601, 18, 640]],
				'respawn-view'	 => [1 => [500, 16, 619, 90, 0], 2 => [595, 16, 619, 270, 0]],
				'comment'		 => $this->lang->translateString("battle-field.39.comment"),
				'area'			 => [
					1 => [[ 541, 18, 614],[ 553, 18, 622]]
				],
				'color'			 => 0,
				'sec'			 => 5,
				'time-limit'	 => 120,
				'spawn-radius'	 => 2,
				'view'			 => [547.5, 22, 606.5],
				'cam'			 => [4 => [548, 25, 619, 0, -90], 5 => [498, 16, 619, 270, 0], 6 => [597, 16, 619, 90, 0]],
				'recon'			 => false,
			],

			//キヌバリ
			40 => [
				'name'			 => $this->lang->translateString("battle-field.40.name"),
				'author'		 => "yotuba",
				'level'			=> "splatt001",
				'start'			 => [1 => [607, 41, 806, 0, 20], 2 => [565, 41, 976, 180, 20]],
				'scan'			 => [1 => [533, 15, 800], 2 => [638, 39, 981]],
				'respawn-view'	 => [1 => [607, 42, 810, 180, 0], 2 => [565, 42, 972, 0, 0]],
				'comment'		 => $this->lang->translateString("battle-field.40.comment"),
				'area'			 => [
					1 => [[ 580, 15, 885],[ 591, 15, 896]]
				],
				'color'			 => 8,
				'sec'			 => 5,
				'time-limit'	 => 150,
				'spawn-radius'	 => 2,
				'view'			 => [586, 65, 891],
				'cam'			 => [4 => [586, 72, 891, 0, -90], 5 => [607, 42, 806, 0, 0], 6 => [565, 42, 976, 180, 0]],
				'recon'			 => false,
			],

			//キダカ
			41 => [
				'name'			 => $this->lang->translateString("battle-field.41.name"),
				'author'		 => "moyasan",
				'level'			=> "splatt001",
				'start'			 => [1 => [637, 31.5, -269, 90, 20], 2 => [715, 31.5, -251, 270, 20]],
				'scan'			 => [1 => [622, 5, -278], 2 => [729, 30, -243]],
				'respawn-view'	 => [1 => [635, 33, -269, 270, 0], 2 => [717, 33, -251, 90, 0]],
				'comment'		 => $this->lang->translateString("battle-field.41.comment"),
				'area'			 => [
					1 => [[ 671, 5, -266],[ 680, 5, -255]]
				],
				'color'			 => 12,
				'sec'			 => 5,
				'time-limit'	 => 180,
				'spawn-radius'	 => 2,
				'view'			 => [676, 25, -261],
				'cam'			 => [4 => [676, 26, -261, 0, 90], 5 => [637, 33, -269, 90, 0], 6 => [715, 33, -251, 270, 0]],
				'recon'			 => true,
			],

			//クリオネ海底神殿
			42 => [
				'name'			 => $this->lang->translateString("battle-field.42.name"),
				'author'		 => "moyasan",
				'level'			=> "splatt001",
				'start'			 => [1 => [722, 18, 16, 180, 20], 2 => [725, 18, -55, 0, 20]],
				'scan'			 => [1 => [693, 7, -57], 2 => [753, 16, 17]],
				'respawn-view'	 => [1 => [722, 19, 12, 0, 0], 2 => [725, 19, -51, 180, 0]],
				'comment'		 => $this->lang->translateString("battle-field.42.comment"),
				'area'			 => [
					1 => [[ 703, 7, -22],[ 710, 7, -12]],
					2 => [[ 736, 7, -28],[ 743, 7, -18]]
				],
				'color'			 => 0,
				'sec'			 => 5,
				'time-limit'	 => 150,
				'spawn-radius'	 => 2,
				'view'			 => [721, 24, -14],
				'cam'			 => [4 => [750, 25, -20, 0, 90], 5 => [722, 19, 16, 180, 0], 6 => [725, 19, -55, 0, 0]],
				'recon'			 => true,
			],

			//ササカマ
			43 => [
				'name'			 => $this->lang->translateString("battle-field.43.name"),
				'author'		 => "Zmix00, scyphas",
				'level'			=> "splatt001",
				'start'			 => [1 => [876, 21.5, 259, 90, 20], 2 => [809, 21.5, 234, 270, 20]],
				'scan'			 => [1 => [807, 10, 200], 2 => [877, 23, 292]],
				'respawn-view'	 => [1 => [872, 23, 259, 270, 0], 2 => [813, 23, 234, 90, 0]],
				'comment'		 => $this->lang->translateString("battle-field.43.comment"),
				'area'			 => [
					1 => [[ 836, 15, 233],[ 847, 15, 238]],
					2 => [[ 837, 15, 254],[ 848, 15, 259]]
				],
				'color'			 => 7,
				'sec'			 => 5,
				'time-limit'	 => 150,
				'spawn-radius'	 => 2,
				'view'			 => [842, 40, 246],
				'cam'			 => [4 => [842, 35, 246, 0, 90], 5 => [876, 23, 259, 90, 0], 6 => [809, 23, 234, 270, 0]],
				'recon'			 => true,
			],

			//クロダイ
			44 => [
				'name'			 => $this->lang->translateString("battle-field.44.name"),
				'author'		 => "yotuba, hhokkun",
				'level'			=> "splatt001",
				'start'			 => [1 => [788.5, 26, -136.5, 0, 20], 2 => [783.5, 26, 8.5, 180, 20]],
				'scan'			 => [1 => [762, 12, -139], 2 => [809, 24, 10]],
				'respawn-view'	 => [1 => [788.5, 27, -133, 180, 0], 2 => [783.5, 27, 5, 0, 0]],
				'comment'		 => $this->lang->translateString("battle-field.44.comment"),
				'area'			 => [
					1 => [[ 778, 18, -70],[ 793, 18, -59]]
				],
				'color'			 => 0,
				'sec'			 => 5,
				'time-limit'	 => 180,
				'spawn-radius'	 => 2,
				'view'			 => [799, 21, -53],//試合観戦でテレポートする座標 (必須ではない)
				'cam'			 => [4 => [786, 28, -65, 0, 90], 5 => [788.5, 27, -136.5, 0, 0], 6 => [783.5, 27, 8.5, 180, 0]],
				'recon'			 => true,
			],

			//試し塗りのフィールドデータ
			'try' => [
				501 => [
					'level'	 => "splatt001",
					'start'	 => [116.5,7,316.5,90,0],
					'scan'	 => [1 => [85,6,311], 2 => [119,18,329]],
					'color'	 => 7,
				],
				502 => [
					'level'	 => "splatt001",
					'start'	 => [153.5,7,316.5,90,0],
					'scan'	 => [1 => [122,6,311], 2 => [156,18,329]],
					'color'	 => 7,
				],
				503 => [
					'level'	 => "splatt001",
					'start'	 => [116.5,7,337.5,90,0],
					'scan'	 => [1 => [85,6,332], 2 => [119,18,350]],
					'color'	 => 7,
				],
				504 => [
					'level'	 => "splatt001",
					'start'	 => [153.5,7,337.5,90,0],
					'scan'	 => [1 => [122,6,332], 2 => [156,18,350]],
					'color'	 => 7,
				],
				505 => [
					'level'	 => "splatt001",
					'start'	 => [116.5,7,358.5,90,0],
					'scan'	 => [1 => [85,6,353], 2 => [119,18,413]],
					'color'	 => 7,
				],
				506 => [
					'level'	 => "splatt001",
					'start'	 => [153.5,7,358.5,90,0],
					'scan'	 => [1 => [122,6,353], 2 => [156,18,413]],
					'color'	 => 7,
				],
				507 => [
					'level'	 => "splatt001",
					'start'	 => [116.5,7,379.5,90,0],
					'scan'	 => [1 => [85,6,374], 2 => [119,18,392]],
					'color'	 => 7,
				],
				508 => [
					'level'	 => "splatt001",
					'start'	 => [153.5,7,379.5,90,0],
					'scan'	 => [1 => [122,6,374], 2 => [156,18,392]],
					'color'	 => 7,
				],
				509 => [
					'level'	 => "splatt001",
					'start'	 => [116.5,7,400.5,90,0],
					'scan'	 => [1 => [85,6,395], 2 => [119,18,413]],
					'color'	 => 7,
				],
				510 => [
					'level'	 => "splatt001",
					'start'	 => [153.5,7,400.5,90,0],
					'scan'	 => [1 => [122,6,395], 2 => [156,18,413]],
					'color'	 => 7,
				],
				511 => [
					'level'	 => "splatt001",
					'start'	 => [116.5,7,421.5,90,0],
					'scan'	 => [1 => [85,6,416], 2 => [119,18,434]],
					'color'	 => 7,
				],
				512 => [
					'level'	 => "splatt001",
					'start'	 => [153.5,7,421.5,90,0],
					'scan'	 => [1 => [122,6,416], 2 => [156,18,434]],
					'color'	 => 7,
				],
				513 => [
					'level'	 => "splatt001",
					'start'	 => [116.5,7,442.5,90,0],
					'scan'	 => [1 => [85,6,437], 2 => [119,18,455]],
					'color'	 => 7,
				],
				514 => [
					'level'	 => "splatt001",
					'start'	 => [153.5,7,442.5,90,0],
					'scan'	 => [1 => [122,6,437], 2 => [156,18,455]],
					'color'	 => 7,
				],
				/*
				マグマ農園がある方が奇数、地下鉄マップがあるほうが偶数
				スキャンの1はマグマ農園、地下水ドーム側から測ってます(2は逆)
				奇数 => [
					'level'	 => "splatt001",
					'start'	 => [116.5,7,.5,90,0],
					'scan'	 => [1 => [85,6,], 2 => [119,18,]],
					'color'	 => 7,
				],
				偶数 => [
					'level'	 => "splatt001",
					'start'	 => [153.5,7,.5,90,0],
					'scan'	 => [1 => [122,6,], 2 => [156,18,]],
					'color'	 => 7,
				],
				*/
			],
			/*
			New field template
			-1 => [
				'name'			 => $this->lang->translateString("battle-field.番号.name"),
				'author'		 => "username",
				'level'			=> "splatt001",
				'start'			 => [1 => [x, y, z, yaw, pitch], 2 => [x, y, z, yaw, pitch]],
				'scan'			 => [1 => [x, y, z], 2 => [x, y, z]],
				'respawn-view'	 => [1 => [x, y, z, yaw, pitch], 2 => [x, y, z, yaw, pitch]],
				'comment'		 => $this->lang->translateString("battle-field.番号.comment"),
				'area'			 => [
					1 => [[ x, y, z],[ x, y, z]]
				],
				'color'			 => 0,//リセットする色の数値 0~15
				'sec'			 => 5,//フィールドのスキャンをする間隔 5 or 10
				'time-limit'	 => 120,//フィールドが大きい場合は150
				'spawn-radius'	 => 2,//リスポの半径(回るパーティクルの半径)
				'view'			 => [x, y, z],//試合観戦でテレポートする座標 (必須ではない)
				'cam'			 => [4 => [x, y, z, yaw, pitch], 5 => [x, y, z, yaw, pitch], 6 => [x, y, z, yaw, pitch]],//4=>フィールド中央, 5 => 地点A, 6 => 地点B (必須ではない)
				'recon'			 => false,//さんぽができるかどうか
			],
			*/
			/*
			オスカー城
			もっとも広いまっぷを駆け巡れ。低スペック端末が火を噴くでげそ
			*/
			/*
			サーバーのワールド内にあるやつ
			x => [
				'name'		 => "",
				'author'	 => "Mossun281",
				'level'		 => "splatt001",
				'start'		 => [1 => [189,11,-63,180,0], 2 => [188,11,-26,0,0]],
				'scan'		 => [1 => [176,0,-20], 2 => [200,0,-70]],
				'comment'	 => "no comment",
				'color'		 => 0,
				'sec'		 => 5,
			],
			x => [
				'name'		 => "ふええ洞窟",//仮名
				'author'	 => "Villager",
				'level'		 => "splatt001",
				'start'		 => [1 => [340.5,7,112.5,315,0], 2 => [340.5,7,159.5,135,0]],
				'scan'		 => [1 => [389,4,110], 2 => [338,9,161]],
				'comment'	 => "",
				'color'		 => 8,
				'sec'		 => 5,
			],
			x => [
				'name'		 => "",//アロワナモール再現?
				'author'	 => "mfmfneko, tomotomo0822, yotuba, Mossun281, 0929hitoshi",
				'level'		 => "splatt001",
				'start'		 => [1 => [-22,14.5,-86,90,0], 2 => [-104,14.5,-83,270,0]],
				'scan'		 => [1 => [-25,8,-68], 2 => [-102,13,-102]],
				'comment'	 => "",
				'color'		 => 7,
				'sec'		 => 5,
			],
			x => [//フィールド内に複数看板あり
				'name'		 => "",
				'author'	 => "???",
				'level'		 => "splatt001",
				'start'		 => [1 => [-157.5,10,149.5,90,0], 2 => [-100,10,149.5,270,0]],
				'scan'		 => [1 => [-162,8,134], 2 => [-97,16,164]],
				'comment'	 => "",
				'color'		 => 0,
				'sec'		 => 5,
			],
			*/
		];

		for($i = 1, $maxNum = $this->w->getWeaponAmount(); $i <= $maxNum; $i++){
			$weaponsName[$i] = $this->lang->translateString("weaponName.{$i}");
		}
		$this->w->setWeaponsName($weaponsName);

		$subweap_name = [
			1		 =>	$this->lang->translateString("sub-weaponName.1"),
			2		 => $this->lang->translateString("sub-weaponName.2"),
   			3        => $this->lang->translateString("sub-weaponName.3"),
		    4        => $this->lang->translateString("sub-weaponName.4"),
      		5        => $this->lang->translateString("sub-weaponName.5"),
      		6        => $this->lang->translateString("sub-weaponName.6"),
      		7        => $this->lang->translateString("sub-weaponName.7")
		];
		$this->w->setSubWeaponsName($subweap_name);

		$this->randomchat = [
			"",//0
			$this->lang->translateString("randomTip.1"),
			$this->lang->translateString("randomTip.2"),
			$this->lang->translateString("randomTip.3"),
			$this->lang->translateString("randomTip.4"),
			$this->lang->translateString("randomTip.5"),
			$this->lang->translateString("randomTip.6"),
			$this->lang->translateString("randomTip.7"),
			$this->lang->translateString("randomTip.8"),
			$this->lang->translateString("randomTip.9"),
			$this->lang->translateString("randomTip.10"),
			$this->lang->translateString("randomTip.11"),
			$this->lang->translateString("randomTip.12"),
			$this->lang->translateString("randomTip.13"),
			$this->lang->translateString("randomTip.14"),
			$this->lang->translateString("randomTip.15"),
			$this->lang->translateString("randomTip.16"),
		];
		$commandMap = Server::getInstance()->getCommandMap();
		foreach($this->getDescription()->getCommands() as $cmdName => $data){
			$baseTxt = "command.{$cmdName}.description";
			$description = $this->lang->translateString($baseTxt);
			//翻訳されているかどうか、コマンドが存在するかチェック
			if($baseTxt !== $description && ($command = $commandMap->getCommand($cmdName)) instanceof Command){
				$command->setDescription($description);
				$command->setUsage($this->lang->translateString("command.{$command}.usage"));
			}
		}

		if(!$first){
			$this->FloatText(false);
			//$this->itemCase->resetAll();
			$this->shop->resetAll();
			$this->itemselect->setLang($this->lang);
			$this->itemselect->setWeaponsData($this->w->getWeaponsDataAll());
			foreach($this->getServer()->getOnlinePlayers() as $player){
				$this->itemselect->floatingTextColorChange($player);
				$this->itemselect->addFloatingTextParticle($player);
			}
		}
	}

	public function onCommand(CommandSender $s, Command $c, $label, array $a){
		$out = "";
		$user = $s->getName();
		switch($label){
			////////
			//　テスト用
				case "test":
					switch($a[0]){
						case 1:
							Command::broadcastCommandMessage($s, "水位下げ");
							$this->changeFieldForKusoStart();
						break;
						case 2:
							Command::broadcastCommandMessage($s, "水位上げ");
							$this->changeFieldForKusoLast();
						break;
						case 3:
							Command::broadcastCommandMessage($s, "スキャン完了");
							$this->getKuso();
						break;
					}
					break;
				case "test2":
					$members = [
						"moya4"	 => null,
						"43ki"		 => null,
						"53ki"		 => null,
						"63ki"		 => null,
						"73ki"		 => null,
						"83ki"		 => null,
						"trasta334"		 => null
					];
					foreach($members as $member => $team_num){
						$this->entry->addEntry($member);
					}
					$this->setFloatText([0]);
					Command::broadcastCommandMessage($s, $this->lang->translateString("command.test.32kicorps.add"));
					break;
				case "dev":
					if(!isset($a[0])) return false;
					switch($a[0]){
						case "saveskin":
							if(count($a) == 2){
								$skinName = $a[1];
								$savePlayer = Server::getInstance()->getPlayer($skinName);
								$result = Enemy::saveSkinData($savePlayer);
								if($result === false){
									echo "そのプレイヤーは存在しません";
								}else{
									echo "スキンをセーブしました";
								}
							}
							break;
						case "point":
							if(count($a) == 2){
								$point = (int)$a[1];
								if($s instanceof Player){
									$user = $s->getName();
									$playerData = Account::getInstance()->getData($user);
									$playerData->grantPoint($point);
									$s->sendMessage($point."ポイント追加 ");
								}
							}else if(count($a) === 3){
								$point = (int)$a[1];
								$pl = Server::getInstance()->getPlayer($a[2]);
								if($pl instanceof Player){
									$user = $pl->getName();
									$playerData = Account::getInstance()->getData($user);
									$playerData->grantPoint($point);
									$s->sendMessage($point."ポイント追加 ");
									$pl->sendMessage($point."ポイント追加 ");
								}else if($s instanceof Player){
									$s->sendMessage('§4そのプレイヤーは存在しません');
								}else{
									echo '§4そのプレイヤーは存在しません';
								}
							}
							break;
						case "wp":
							if(count($a) == 2){
								$point = (int)$a[1];
								if($s instanceof Player){
									$user = $s->getName();
									$playerData = Account::getInstance()->getData($user);
									$lv = $playerData->giveExp($point);
									$s->sendMessage($point."武器ポイント追加 ");
								}
							}
							break;
						case "mute":
							if($this->mute){
								$this->mute = false;
								$this->getServer()->broadcastMessage("サーバー内のチャット機能を有効にしました");								
							}else{
								$this->mute = true;
								$this->getServer()->broadcastMessage("サーバー内のチャット機能を無効にしました");
							}
								break;
						case "start":
							$this->w->stopMoveTask();
							$this->stopRepeating();
							if(isset($this->Task['PositionCheck'])) $this->getServer()->getScheduler()->cancelTask($this->Task['PositionCheck']->getTaskId());
							if(isset($this->Task['game'])){
								foreach($this->Task['game'] as $task){
									$this->getServer()->getScheduler()->cancelTask($task->getTaskId());//TimeSchedulerが動いてたときの対策(/gend対策)
								}
							}
							Command::broadcastCommandMessage($s, $this->lang->translateString("command.dev.start"));
							$this->dev = true;
							$this->game = 1;
							$this->TimeTable();
							break;
						case "pve":
							$this->w->stopMoveTask();
							$this->stopRepeating();
							if(isset($this->Task['PositionCheck'])) $this->getServer()->getScheduler()->cancelTask($this->Task['PositionCheck']->getTaskId());
							if(isset($this->Task['game'])){
								foreach($this->Task['game'] as $task){
									$this->getServer()->getScheduler()->cancelTask($task->getTaskId());//TimeSchedulerが動いてたときの対策(/gend対策)
								}
							}
							Command::broadcastCommandMessage($s, $this->lang->translateString("command.dev.start"));
							$this->dev = 2;
							$this->game = 1;
							$this->TimeTable();
							break;
						case "area":
							$lv = (isset($a[1])) ? $a[1] : 0;
							if(!$this->area['mode']){
								$this->area['mode'] = true;
								$this->setNeedLv($lv);
								$this->getServer()->broadcastMessage("ガチマッチに設定しました");
							}else{
								$this->area['mode'] = false;
								$this->setNeedLv($lv);
								$this->getServer()->broadcastMessage("レギュラーマッチに設定しました");
							}
							$this->getServer()->broadcastMessage("参加必須レベルを".$lv."に設定しました");
							break;
						case "lv":
							$lv = (isset($a[1])) ? $a[1] : 0;
								$this->setNeedLv($lv);
								$this->getServer()->broadcastMessage("参加必須レベルを".$lv."に設定しました");
							break;
						case "map":
							$m = count($a);
							if($m > 0){
								$st = "";
								$st_ar = [];
								for ($i=1; $i < $m; $i++) { 
									$st_ar[intval($a[$i])] = floor(100/$m)+1;
									$st = $st.$a[$i]." ";
								}
								$data = [
									"h" => [date('G'), date('G')],
									"s" => $st_ar
								];
								if($this->s->setStagedata($data, date('G'))){
									$this->setFloatText([6]);
									Command::broadcastCommandMessage($s, "ステージを".$st."に変更しました");
								}else{
									if($s instanceof Player){
										$s->sendMessage("失敗");
									}
								}
							}
							break;
						case "mapall":
							$m = count($a);
							if($m > 0){
								$data = [];
								$st = "";
								$st_ar = [];
								for ($i=1; $i < $m; $i++) { 
									$st_ar[intval($a[$i])] = floor(100/$m)+1;
									$st = $st.$a[$i]." ";
								}
								for($t = 0; $t <24; $t++){
									$data[] = [
										"h" => [$t, $t],
										"s" => $st_ar
									];
								}
								if($this->s->setStagedata($data)){
									$this->setFloatText([6]);
									Command::broadcastCommandMessage($s, "全てのステージを".$st."に変更しました");
								}else{
									if($s instanceof Player){
										$s->sendMessage("失敗");
									}
								}
							}
							break;
						case "us":
							$this->updateStage();
							$this->setFloatText([6]);
							Command::broadcastCommandMessage($s, "ステージ情報を更新しました。");
							break;
						case "rank":
							if(count($a) == 2){
								$user = $a[1];
								$player = $this->getServer()->getPlayer($user);
								if($player instanceof Player){
									$user = $player->getName();
									$playerData = Account::getInstance()->getData($user);
									if($s instanceof Player){
										$s->sendMessage($user." ウデマエ ".$playerData->getRank());
									}
								}
							}
							break;
						case "end":
							if(!$this->dev) return false;
							Command::broadcastCommandMessage($s, $this->lang->translateString("command.dev.end"));
							$cnt = 0;
							if($this->game === 3){
								$cnt = 4;
							}
							if($this->game >= 4 && $this->game <= 9){
								$cnt = 3;
							}
							if($this->game == 10){
								$cnt = 2;
							}
							if($this->game == 11){
								$cnt = 1;
							}
							for(; $cnt > 0; $cnt--){
								$this->TimeTable();
							}
							$this->dev = false;
							return true;
							break;
						case "field-reset":
							if($this->dev and isset($this->field)){
								$this->resetBattleField($this->field);
								Command::broadcastCommandMessage($s, $this->lang->translateString("command.dev.fieldReset"));
							}else{
								$out = $this->lang->translateString("command.dev.fieldReset.failure");
							}
							break;
						case "only":
							if(!$this->op_only){
								$out = "opのみ入室を許可しました";
								$this->op_only = true;
							}else{
								$out = "誰でも入室可能にしました";
								$this->op_only = false;
							}
							break;
					}
					break;
				//座標確認
				case "xyz":
					if($s instanceof Player){
						$out = sprintf("X: % 6s, Y: % 5s, Z: %6s\nYaw: % 3s, Pitch: %3s", floor($s->x), floor($s->y), floor($s->z), $s->yaw, $s->pitch);
					}
					break;
				//撮影などの視点移動用
				case "cam":
					if(isset($this->view[$user])){
						$this->GameWatching($s, false, false);
					}
					if(isset($this->reconData[$user])){
						$this->Recon($s, 0, false, false);
					}
					return $this->Cam_c($s, $a);
					break;
			////////
			//　管理
				case "setf":
					if(empty($a[0])) return false;
					$f = $a[0];
					if($this->getBattleField($f) != null && $f != 18){
						$this->nextfield = $f;
						Command::broadcastCommandMessage($s, $this->lang->translateString("command.setf.success", [$this->getBattleField($f)['name']]));
						return true;
					}else{
						$out = $this->lang->translateString("field.notFound");
					}
					break;
				//チーム関係
				case "t":
					if(count($a) >= 1){
						switch($a[0]){
							case "add":
								if($this->s->hasOp($user) === false && !$s->isOp()){
									$s->sendMessage($this->lang->translateString("command.notPermission"));
									return true;
								}
								if($this->team->addTeam()){
									Command::broadcastCommandMessage($s, $this->lang->translateString("command.t.add.success"));
									return true;
								}else{
									$out = $this->lang->translateString("command.t.add.failure");
								}
								break;
							case "remove":
								if(!$s->isOp()){
									$s->sendMessage($this->lang->translateString("command.notPermission"));
									return true;
								}
								$force = (count($a) >= 2 and ($a[1] === "on" or $a[1] === "true" or $a[1] === "t" or $a[1] === "1"));
								$result = $this->team->removeTeam($force);
								if($result){
									Command::broadcastCommandMessage($s, $this->lang->translateString("command.t.remove.success"));
									return true;
								}else{
									$out = $this->lang->translateString("command.t.remove.failure");
								}
								break;
							case "allquit"://全メンバー解散
								if(!$s->isOp()){
									$s->sendMessage($this->lang->translateString("command.notPermission"));
									return true;
								}
								$force = (count($a) >= 2 and ($a[1] === "on" or $a[1] === "true" or $a[1] === "t" or $a[1] === "1"));
								$result = $this->team->removeallMember($force);
								if($result){
									Command::broadcastCommandMessage($s, $this->lang->translateString("command.t.allQuit.success"));
									return true;
								}else{
									$out = $this->lang->translateString("command.t.allQuit.failure");
								}
								break;
							case "shuffle"://メンバーシャッフル
								if(!$s->isOp()){
									$s->sendMessage($this->lang->translateString("command.notPermission"));
									return true;
								}
								$force = (count($a) >= 2 and ($a[1] === "on" or $a[1] === "true" or $a[1] === "t" or $a[1] === "1"));
								$result = $this->team->allMembershuffle($force);
								if($result){
									Command::broadcastCommandMessage($s, $this->lang->translateString("command.t.shuffle.success"));
									return true;
								}else{
									$out = $this->lang->translateString("command.t.shuffle.failure");
								}
								break;
							/*
							case "max":
								$this->team->changeTeamMaxPlayer($a[1]);
								break;
							*/
							case "event"://チーム追加、解散した時間を確認
								if(!$s->isOp()){
									$s->sendMessage($this->lang->translateString("command.notPermission"));
									return true;
								}
								$teamEvent = $this->team->getTeamEvent();
								$teamName = [
									3 => "§eyellow",
									4 => "§agreen",
									5 => "§baqua",
									6 => "§9blue",
									7 => "§dpink",
									8 => "§5purple"
								];
								$out = "Team event\n§a> add§f";
								if(isset($teamEvent['add'])){
									$addData = $teamEvent['add'];
									ksort($addData);
									foreach($addData as $team_num => $time){
										$out .= "\n   ".str_pad($teamName[$team_num], 9)."§f : ".date("m/d H:i:s", $time);
									}
								}
								$out .= "\n§c> remove§f";
								if(isset($teamEvent['remove'])){
									$removeData = $teamEvent['remove'];
									ksort($removeData);
									foreach($removeData as $team_num => $time){
										$out .= "\n   ".str_pad($teamName[$team_num], 9)."§f : ".date("m/d H:i:s", $time);
									}
								}
								break;
							case "count-reset":
							case "cr":
							case "ct"://試合回数リセット
								if(!$s->isOp()){
									$s->sendMessage($this->lang->translateString("command.notPermission"));
									return true;
								}
								$this->team->BattlecountReset();
								Command::broadcastCommandMessage($s, $this->lang->translateString("command.t.gameCountReset"));
								return true;
								break;
							case "count-check":
							case "c":
							case "count":
							case "cv"://試合回数確認
								if(!$s->isOp()){
									$s->sendMessage($this->lang->translateString("command.notPermission"));
									return true;
								}
								$out = $this->lang->translateString("command.gameCountList");
								foreach($this->team->getTeamBattleTime() as $team_num => $count){
									if($this->team->getTeamColor($team_num)){
										$out .= "\n".str_pad($this->team->getTeamName($team_num), 9)."§f : ".$count;
									}
								}
								break;
							default:
								return false;
						}
					}
					break;
				//別のサバに飛ばす。
				//params 0 = なし→セントラルへ
				//params 0 = int / そのサーバーナンバーのサーバーへ 
				case "tpalls":
					
					if(!empty($a[0])){
						$sno = $a[0];
					}else{
						$sno = 1;//Not cent Yes GameServer1
					}
					if(($ServerName = $this->s->getServerName($sno)) !== false){
						Command::broadcastCommandMessage($s, $this->lang->translateString("command.tpalls.sender", [$ServerName]));
						foreach($this->getServer()->getOnlinePlayers() as $player){
							if($player instanceof Player){
								$this->s->gotoPlay($player, $sno);
							}
						}
						return true;
					}else{
						$out = $this->lang->translateString("command.tpalls.serverNotFound");
					}
					break;
				//ランダムメッセージを発する
				//params 0 = int ($this->randomの何番目か)
				case "random":
					if(count($a) == 1){
						switch($a[0]){
							case "start":
								if($this->Tips){
									$out = $this->lang->translateString("command.random.error.alreadyStart");
								}else{
									$this->Tips = true;
									$this->Task['Tips'] = $this->getServer()->getScheduler()->scheduleRepeatingTask(new Random($this), 20*75);
									Command::broadcastCommandMessage($s, $this->lang->translateString("command.random.start"));
									return true;
								}
								break;
							case "stop":
								if($this->Tips){
									$this->Tips = false;
									$task = $this->Task['Tips']->getTaskId();
									$this->getServer()->getScheduler()->cancelTask($task);
									Command::broadcastCommandMessage($s, $this->lang->translateString("command.random.stop"));
									return true;
								}else{
									$out = $this->lang->translateString("command.random.error.alreadyStop");
								}
								break;
							default:
								$num = (int) $a[0];
								if($num <= count($this->randomchat) - 1){
									$out = "GO";
									$s->sendMessage($out);
									$this->randomBroad($num);
									return true;
								}else{
									$out = $this->lang->translateString("command.random.messageNotFound");
								}
						}
					}else{
						$this->randomBroad();
					}
					break;
				case "oc"://op同士の会話
					if(count($a) === 0) return false;
					$message = "[".($s instanceof Player ? $s->getDisplayName() : $s->getName())." -> OP] ".implode(' ', $a);
					Server::getInstance()->getLogger()->info($message);
					foreach(Server::getInstance()->getOnlinePlayers() as $player){
						if($player->isOp()){
							$player->sendMessage($message);
						}
					}
					break;
				case "del"://村人などのエンティティを削除
					Command::broadcastCommandMessage($s, $this->lang->translateString("command.del.start"));
					$count = 0;
					$level = $this->getServer()->getDefaultLevel();
					foreach($level->getEntities() as $entity){
						if(!($entity instanceof Player)){
							$entity->close();
							$count++;
						}
					}
					Command::broadcastCommandMessage($s, $this->lang->translateString("command.del.end", [$count]));
					break;
				//ゲーム開始
				case "gready":
					if($this->game == 1){
						$this->gamestop = false;
						if($this->TimeTable()){
							Command::broadcastCommandMessage($s, $this->lang->translateString("command.gready.success"));
						}else{
							$out = $this->lang->translateString("command.gready.error");
						}
					}else{
						$out = $this->lang->translateString("command.gready.error");
					}
					break;
				//ゲーム強制停止、ほかのゲーム管理のコマンドも効かなくする
				case "gstop":
					if($this->gamestop){
						//ゲームを再開(自動でゲーム進行をする)
						Command::broadcastCommandMessage($s, $this->lang->translateString("command.gstop.restart"));
						$this->gamestop = false;
						if(isset($this->Task['game'][3])){
							foreach($this->Task['game'] as $task){
								$id = $task->getTaskId();
								$this->getServer()->getScheduler()->cancelTask($id);
							}
						}
						if($this->game === 10){
							foreach($this->team->getBattleTeamMember() as $team => $members){
								foreach($members as $member => $number){
									if(($player = $this->getServer()->getPlayer($member)) instanceof Player){
										if($player->hasEffect(Effect::BLINDNESS)) $player->removeEffect(Effect::BLINDNESS);
									}
								}
							}
							$time = (($this->count_time - time()) + 60) * 20;
							foreach($this->view as $name => $value){
								if(($player = $this->getServer()->getPlayer($name)) instanceof Player){
									$player->removeEffect(Effect::JUMP);
									$player->removeEffect(Effect::FATIGUE);
									//エフェクトつけなおす
									$player->addEffect(Effect::getEffect(Effect::JUMP)->setDuration($time)->setAmplifier(130)->setVisible(false));
									$player->addEffect(Effect::getEffect(Effect::FATIGUE)->setDuration($time)->setAmplifier(5)->setVisible(false));
								}
							}
							$this->startGame();
						}else{
							$this->TimeTable();
						}
					}else{
						//ゲームを停止
						Command::broadcastCommandMessage($s, $this->lang->translateString("command.gstop.success"));
						$this->gamestop = true;
						if($this->game === 10){
							$this->stopGame();
							foreach($this->view as $name => $value){
								if(($player = $this->getServer()->getPlayer($name)) instanceof Player){
									Effect::getEffect(Effect::JUMP)->setDuration(6000*20)->setAmplifier(130)->setVisible(false)->add($player, true);
									Effect::getEffect(Effect::FATIGUE)->setDuration(6000*20)->setAmplifier(5)->setVisible(false)->add($player, true);
								}
							}
							foreach($this->team->getBattleTeamMember() as $team => $members){
								foreach($members as $member => $number){
									if(($player = $this->getServer()->getPlayer($member)) instanceof Player){
										$player->addEffect(Effect::getEffect(Effect::BLINDNESS)->setDuration(6000*20)->setAmplifier(0)->setVisible(false));
									}
								}
							}
						}
					}
					break;
				//ゲーム強制終了
				case "gend":
					$msg = $this->lang->translateString("command.gend.success");
						foreach($this->getServer()->getOnlinePlayers() as $player){
							$userx = $player->getName();
							$pt = ($this->team->getBattleTeamOf($userx))? 1000 : 500;
							$playerData = Account::getInstance()->getData($userx);
							$playerData->grantPoint($pt);
							$player->sendMessage("§3ゲームが途中で終了したため§e".$pt."pt§3差し上げます");
						}
					if($this->game >= 10){
						if($this->gamestop){
							$out = 	$this->lang->translateString("command.gend.error");
						}else{

							Command::broadcastCommandMessage($s, $msg);
							$this->getServer()->broadcastMessage("§3≫ ".$msg);
							if($this->game == 10){
								$this->unfinished = true;
								$this->Task['game']['end'] = $this->getServer()->getScheduler()->scheduleRepeatingTask(new GameEnd($this), 1);
							}
						}
					}else{
						Command::broadcastCommandMessage($s, $msg);
						//$out = $this->lang->translateString("command.game.gend.error.notGame");
						$this->getServer()->broadcastMessage("§3≫ ".$msg);
						$this->GameEnd();
					}
					break;
				case "gskip":
					if(!$this->gamestop){						
						if($this->TimeTable()){
							Command::broadcastCommandMessage($s, $this->lang->translateString("command.gskip.success"));
							return true;
						}else{
							$out = $this->lang->translateString("command.gskip.error");
						}
					}else{
						$out = $this->lang->translateString("command.gskip.error");
					}
					break;
				//みんなをリスぽに強制集合！
				case "tprall":
					foreach($this->getServer()->getOnlinePlayers() as $player){
						$this->tpr($player);
					}
					Command::broadcastCommandMessage($s, $this->lang->translateString("commnad.tprall.success"));
					return true;
					break;
				//リスぽに強制転送
				//param 0 なし→自分を転送
				//param 0 <name> そのプレイヤーを強制テレポート(OPがないとつかえない)
				case "tpr":
					if(count($a) == 1){
						//誰かをテレポートさせるにはOPが必要
						if($s->isOp()){
							$player = $this->getServer()->getPlayer($a[0]);
							if($player instanceof Player){
								$this->tpr($player);
								Command::broadcastCommandMessage($s, $this->lang->translateString("command.tpr.success.admin", [$player->getDisplayName()]));
							}else{
								$out = $this->lang->translateString("command.playerNotFound");
							}
						}else{
							$out = $this->lang->translateString("command.notPermission");
						}
					}else{
						if($s instanceof Player){
							$out = $this->tpr($s) ? $this->lang->translateString("tpr.respawn") : $this->lang->translateString("tpr.gameStartPoint");
						}else{
							return false;
						}
					}
					break;
				//武器を上げるお
				//params 0 = <name>
				//params 1 = int ($this->weaponNameの何番目か)
				case "givew":
					if(count($a) == 2){
						$user = $a[0];
						$weapon_num = (int) $a[1];
						if($this->w->getWeaponData($weapon_num) != null){
							$player = $this->getServer()->getPlayer($user);
							if($player instanceof Player){
								$user = $player->getName();
								$playerData = Account::getInstance()->getData($user);
								$playerData->giveWeapon($weapon_num);
								$namae = $this->w->getweaponName($weapon_num);
								Command::broadcastCommandMessage($s, $this->lang->translateString("command.givew.success.admin", [$user, $namae]));
								$player->sendMessage($this->lang->translateString("command.givew.success.target", [$namae]));
								//$this->itemCase->reset($player);
								$this->shop->reset($player);
								$this->itemselect->reset($player);
								return true;
							}
						}else{
							$out = $this->lang->translateString("command.givew.error.noSuchWeapon");
						}
					}else{
						return false;
					}
					break;
				//その時鯖にいるプレイヤーの、このプラグインに関するデータをセーブ
				case "dsave":
					$this->a->saveAll();
					Command::broadcastCommandMessage($s, $this->lang->translateString("command.dsave.success"));
					break;
			////////
			//　ユーザーが使うやつ
				//ふええ
				case "huee":
					if(!isset($this->warn[$user])){
						$this->warn[$user]['count'] = 0;
						$this->warn[$user]['time'] = microtime(true);
					}
					if($s instanceof Player){
						if($this->mute){
							$s->sendMessage("§4現在このサーバー内でのチャットは禁止されています！！");
							return false;
						}
						if($this->warn[$user]['time'] + 1.5 * ($this->warn[$user]['count'] - 5) <= microtime(true)){
							$list_kaomoji = $this->list_kaomoji;
							$t = mt_rand(0, count($list_kaomoji) - 1);
							$this->getServer()->broadcastMessage($s->getDisplayName()." : ".$this->lang->translateString("command.huee", [$list_kaomoji[$t]]),$this->getNonmutePlayers());
							$this->warn[$user]['count'] ++;
							$this->warn[$user]['time'] = microtime(true);
						}else{
							$s->sendMessage($this->lang->translateString("regulated.command"));
						}
					}else{
						$s->sendMessage($this->lang->translateString("command.UnavailableNotPlayer"));
					}
					return true;
					break;
					//とらすた
				case "trasta":
					if(!isset($this->warn[$user])){
						$this->warn[$user]['count'] = 0;
						$this->warn[$user]['time'] = microtime(true);
					}
					if($s instanceof Player){
						if($this->mute){
							$s->sendMessage("§4現在このサーバー内でのチャットは禁止されています！！");
							return false;
						}
						if($this->warn[$user]['time'] + 1.5 * ($this->warn[$user]['count'] - 5) <= microtime(true)){
							$list_kaomoji = $this->list_kaomoji;
							$t = mt_rand(0, count($list_kaomoji) - 1);
							$this->getServer()->broadcastMessage($s->getDisplayName()." : §3とらすたぁ".$list_kaomoji[$t],$this->getNonmutePlayers());
							$this->warn[$user]['count'] ++;
							$this->warn[$user]['time'] = microtime(true);
						}else{
							$s->sendMessage($this->lang->translateString("regulated.command"));
						}
					}else{
						$s->sendMessage($this->lang->translateString("command.UnavailableNotPlayer"));
					}
					return true;
					break;
				//流行る
				case "hayaru":
					if($s instanceof Player){
							if($this->mute){
								$s->sendMessage("§4現在このサーバー内でのチャットは禁止されています！！");
								return false;
							}
						if((!isset($this->hayaru['time']) or time() - $this->hayaru['time'] >= 30) or $this->hayaru['player'] !== $user){
							//$this->getServer()->broadcastMessage("<".$s->getDisplayName()."> ".$this->lang->translateString("command.hayaru"),$this->getNonmutePlayers());
							$list_kaomoji = $this->list_kaomoji;
							$t = mt_rand(0, count($list_kaomoji) - 1);
							$this->getServer()->getPluginManager()->callEvent($ev = new PlayerChatEvent($s, $this->lang->translateString("command.hayaru", [$list_kaomoji[$t]]) ));
							if(!$ev->isCancelled()){
								$this->getServer()->broadcastMessage($this->getServer()->getLanguage()->translateString($ev->getFormat(), [
									$ev->getPlayer()->getDisplayName(),
									$ev->getMessage()
								]), $this->getNonmutePlayers());
								$this->hayaru = [
									'time' => time(),
									'player' => $user,
									'hayaranai' => false
								];
							}
						}else{
							$s->sendMessage($this->lang->translateString("command.hayaru.continuous"));
						}
					}else{
						$s->sendMessage($this->lang->translateString("command.UnavailableNotPlayer"));
					}
					return true;
					break;
				//流行らない
				case "hayaranai":
					if($s instanceof Player){
						if(isset($this->hayaru['time']) and time() - $this->hayaru['time'] <= 15){
							if(isset($this->hayaru['hayaranai']) and !$this->hayaru['hayaranai']){
								if($user !== $this->hayaru['player']){
									$this->getServer()->getPluginManager()->callEvent($ev = new PlayerChatEvent($s, $this->lang->translateString("command.hayaranai", [$list_kaomoji[$t]]) ));
									//$this->getServer()->broadcastMessage("<".$s->getDisplayName()."> ".$this->lang->translateString("command.hayaranai"),$this->getNonmutePlayers());
									$this->hayaru['hayaranai'] = true;
									if(!$ev->isCancelled()){
										$this->getServer()->broadcastMessage($this->getServer()->getLanguage()->translateString($ev->getFormat(), [
											$ev->getPlayer()->getDisplayName(),
											$ev->getMessage()
										]), $this->getNonmutePlayers());
										$this->hayaru['hayaranai'] = true;
									}
								}else{
									$s->sendMessage($this->lang->translateString("command.hayaranai.sameTarget"));
								}
							}else{
								$s->sendMessage($this->lang->translateString("command.hayaru.notFound"));
							}
						}else{
							$s->sendMessage($this->lang->translateString("command.hayaru.notFound"));
						}
					}else{
						$s->sendMessage($this->lang->translateString("command.UnavailableNotPlayer"));
					}
					return true;
					break;
				//ミュート
				case "mute":
					if($s instanceof Player){
						if(!empty($a)){
							switch($a[0]){
								case 'on':
									$this->mute_personal[$user] = true;
									$s->sendMessage('全体ミュートしました');
									$this->changeName($s);
									break;
								case 'off':
									unset($this->mute_personal[$user]);
									$s->sendMessage('全体ミュートを解除しました');
									$this->changeName($s);
									break;
								case 'list':
									$playerData = Account::getInstance()->getData($user);
									$s->sendMessage($playerData->getMuteList().'をミュートしています');
									break;
								case 'add':
									if(isset($a[1])){
										$playerData = Account::getInstance()->getData($user);
										$playerData->addMuteList($a[1]);
									}else{
										$s->sendMessage('名前を入力してください');
									}
									break;
								case 'remove':
									if(isset($a[1])){
										$playerData = Account::getInstance()->getData($user);
										if(!$playerData->removeMuteList($a[1])){
											$s->sendMessage('リストにない名前です');
										}
									}else{
										$s->sendMessage('名前を入力してください');
									}
									break;
								default:
									return false;
							}
						}else{
							return false;
						}
					}
					return true;
					break;
				case "cal":
					if($s instanceof Player){
						if(isset($a[1])){
							$playerData = Account::getInstance()->getData($user);
							$weap_num = $playerData->getNowWeapon();
							$name = $this->w->getWeaponName($weap_num);
							$power = (1+($a[0]*0.1))/(1+($a[1]*0.1));
							$dam = $this->w->getAttackDamage($weap_num);
							$damage = round($dam*$power);
							$hearts = $damage/2;
							$ds = ($this->w->getWeaponData($weap_num)[5] === 0) ? 1 : $this->w->getWeaponData($weap_num)[5];
							$dps = $damage*(20/$ds);
							$s->sendMessage("ブキ名 : ".$name);
							$s->sendMessage("ダメージ補正 : ".$power);
							$s->sendMessage("ダメージ量 : ♥".$hearts);
							$s->sendMessage("確定数 : ".ceil(20/$damage)." ～ ".ceil(30/$damage));
							$s->sendMessage("DPS : ♥".$dps."d/s");
						}else{
							$s->sendMessage("/cal <攻撃強化ガジェットの数> <相手の防御強化ガジェットの数>");
						}
					}
					return true;
					break;
				//ブキ再配布
				case "weap":
					if(($this->team->getBattleTeamOf($user) and $this->isinPrepareBattle()) or isset($this->trypaintData['player'][$user])){
						//チームに入っていて、ブキが配布されるべき時
						$out = 	$this->lang->translateString("command.weap.success");
						switch(true){
							case $this->dev:
								$giveType = 3;
								break;
							case isset($this->trypaintData['player'][$user]):
								$givetype = 2;
								break;
							default:
								$giveType = 0;
						}
						if($this->dev == 2){
							$giveType = -1;
						}
						$this->giveWeapon($s, $giveType);
					}else{
						$out = $this->lang->translateString("command.weap.failure");
					}
					break;
				//本を再配布
				case "book":
					if(!$this->team->getBattleTeamOf($user) || !$this->checkFieldteleport()){
						//本は試合中には再配布できないように
						$out = $this->lang->translateString("command.book.success");
						$id = Item::get(340);
						$s->getInventory()->addItem($id);
					}else{
						$out = $this->lang->translateString("command.book.failure");
					}
					break;
				//チーム状況をコマンドで確認
				case "team":
					$out = $this->lang->translateString("command.team.message", [$this->team->getAllTeamStatus()]);
					break;
				case "join":
					if($this->entry->canEntry($user)){
						$out = $this->entry->addEntry($user);
						$this->setFloatText([0]);
						$this->changeName($s);
					}else{
						$out = "エントリーできません";
					}
					break;
				//エントリーから抜ける
				case "quit":
						$result = $this->entry->removeEntry($user);
						if($result){
							$this->setFloatText([0]);
							$out = "エントリーを解除しました";
							$this->changeName($s);
						}else{
							$out = "エントリーしてません";
						}
					break;
/*				case "shop":
					if($s instanceof Player){
						if(empty($a[0])) return false;
						if($this->w->canSellWeapons($a[0])){
							$this->BuyWeapon($s, $a[0]);
						}else{
							return false;
						}
					}else{
						$out = $this->lang->translateString("command.UnavailableNotPlayer");
					}
					break;*/
				//試合観戦
				case "view":
					if($s instanceof Player){
						if(isset($this->cam[$user])){
							$this->Cam_c($s, [], false, false);
						}
						if(isset($this->reconData[$user])){
							$this->Recon($s, 0, false, false);
						}
						$this->GameWatching($s);
						return true;
					}else{
						$out = $this->lang->translateString("command.UnavailableNotPlayer");
					}
					break;
				//チームのメンバー同士のチャット
				case "tc":
					/*if($this->mute){
						$s->sendMessage("§4現在このサーバー内でのチャットは禁止されています！！");
						return false;
					}*/
					$team_num = $this->team->getTeamOf($user);
					if($team_num){
						$team_name = $this->team->getTeamName($team_num)."§f";
						if(count($a) > 0){
							$message = implode(' ', $a);
							$members = $this->team->getTeamMember($team_num, true);
							$msg = "[{$s->getDisplayName()} -> {$team_name}] $message";
							foreach($members as $member){
								if(($player = $this->getServer()->getPlayer($member)) instanceof Player){
									if(!isset($this->mute_personal[$user]))
										$player->sendMessage($msg);
								}
							}
							MainLogger::getLogger()->info($msg);
							return true;
						}
					}else{
						$out = $this->lang->translateString("command.tc.failure");
					}
					break;
				//チームメンバーのブキ確認
				case "tm":
					$team_num = $this->team->getTeamOf($user);
					if($team_num){
						$message = "§2-----現在のメンバー-----\n";
						$members = $this->team->getTeamMember($team_num, true);
						foreach($members as $member){
							if(($player = $this->getServer()->getPlayer($member)) instanceof Player){
								$u = $player->getName();
								$playerData = $this->a->getData($u);
								$weapon = $playerData->getNowWeapon($u);
								$weapon_name = $this->w->getweaponName($weapon);
								$subweapon = $this->w->getSubWeaponNumFromWeapon($weapon);
								$subweap_name = $this->w->getSubWeaponName($subweapon);
								$message .= $player->getDisplayName()." : ".$weapon_name."(".$subweap_name.")\n";
							}
						}
						$s->sendMessage($message);
						return true;
					}else{
						$out = $this->lang->translateString("command.tm.failure");
					}
					break;
				//ステータス確認(金ブロックと同様)
				case "st":
					if($s instanceof Player){
						$out = $this->getAccountStatus($user);
					}else{
						$out = $this->lang->translateString("command.UnavailableNotPlayer");
					}
					break;
				case "recon":
					if($s instanceof Player){
						if(isset($a[0])){
							if($a[0] === "help"){
								//ふぃーるどの番号確認できるように
								$out = $this->lang->translateString("command.recon.fieldList.title")."\n";
								$count = 0;
								foreach($this->battle_field as $field_num => $fieldData){
									if(isset($fieldData['recon']) && $fieldData['recon']){
										$out .= (($count % 2 === 0) ? "\n" : "").$this->lang->translateString("command.recon.fieldList.text", [$field_num, $fieldData['name']])."§f  ";
										$count++;
									}
								}
							}else{
								$this->Recon($s, $a[0]);
								return true;
							}
						}elseif(isset($this->reconData[$user])){
							$this->Recon($s, 0);
							return true;
						}else{
							$out = $this->lang->translateString("command.recon.tips");
						}
					}else{
						$out = $this->lang->translateString("command.UnavailableNotPlayer");
					}
					break;
				case "tr":
					$this->TryPaint($s);
					break;
		}
		if($out !== ""){
			$s->sendMessage($out);
		}
		return true;
	}

	/**
	 * さんぽをする/やめる
	 * @param Player  $player
	 * @param int     $fieldNum    さんぽしたいフィールド
	 * @param boolean $teleport    処理内でテレポートを実行させるかどうか        default = true
	 * @param boolean $sendMessage 処理内でプレイヤーにメッセージを送信するかどうか default = true
	 */
	public function Recon($player, $fieldNum, $teleport = true, $sendMessage = true){
		$user = $player->getName();
		if(!($player instanceof Player)) return false;
		$con = function()use($player, $user){
			if(isset($this->trypaintData['player'][$user])){
				$this->TryPaint($player, false, false);
			}
			if(isset($this->view[$user])){
				$this->GameWatching($player, false, false);
			}
			if(isset($this->cam[$user])){
				$this->Cam_c($player, [], false, false);
			}
		};
		if(isset($this->reconData[$user]) && $fieldNum == 0){
			unset($this->reconData[$user]);
			if($teleport){
				$this->tpr($player);
				if($sendMessage) $player->sendMessage($this->lang->translateString("tpr.respawn"));
			}
			return true;
		}else{
			if($this->field != $fieldNum || $this->game < 4){
				if(($fieldData = $this->getBattleField($fieldNum)) !== null){
					if(isset($fieldData['recon']) && $fieldData['recon']){
						$con();
						$pos = $fieldData['start'][mt_rand(1, 2)];
						$player->teleport(new Location($pos[0], $pos[1], $pos[2], $pos[3], $pos[4], $player->getLevel()), $pos[3], $pos[4]);
						$this->reconData[$user] = $fieldNum;
						if($sendMessage) $player->sendMessage($this->lang->translateString("command.recon.success", [$fieldData['name']]));
						return true;
					}else{
						if($sendMessage) $player->sendMessage($this->lang->translateString("command.recon.unSupported"));
					}
				}else{
					if($sendMessage){
						$player->sendMessage($this->lang->translateString("field.notFound"))."\n".$this->lang->translateString("command.recon.tips");
					}
				}
			}else{
				if($sendMessage) $player->sendMessage($this->lang->translateString("field.isBattleing"));
			}
		}
		return false;
	}

	/**
	 * 試合を開始前に試合をするフィールドをさんぽしているプレイヤーがいるかチェック
	 */
	public function ReconCheck(){
		foreach($this->reconData as $user => $fieldNum){
			if($this->field == $fieldNum){
				if(($player = $this->getServer()->getPlayer($user)) instanceof Player){
					$this->Recon($player, 0, true, false);
				}else{
					unset($this->reconData[$user]);
				}
			}
		}
	}

	/**
	 * 観戦を開始/終了
	 * @param Player  $player
	 * @param boolean $teleport    処理内でテレポートを実行させるかどうか        default = true
	 * @param boolean $sendMessage 処理内でプレイヤーにメッセージを送信するかどうか default = true
	 */
	public function GameWatching($player, $teleport = true, $sendMessage = true){
		$out = "";
		$g = $this->game;
		$user = $player->getName();
		if(!($player instanceof Player)) return false;
		$con = function()use($player, $user){
			if(isset($this->trypaintData['player'][$user])){
				$this->TryPaint($player, false, false);
			}
			if(isset($this->reconData[$user])){
				$this->Recon($player, 0, false, false);
			}
			if(isset($this->cam[$user])){
				$this->Cam_c($player, [], false, false);
			}
		};
		if(!isset($this->view[$user])){
			if($g == 10){
				$fielddata = $this->getBattleField($this->field);
				if(isset($fielddata['view'])){
					if(!$this->team->getBattleTeamOf($user)){
						$con();
						$this->seat->stand($player);
						$this->view[$player->getName()] = true;
						$out = $this->lang->translateString("gamewatching.success");
						$time = ($this->gamestop || $this->dev) ? 6000*20 : (($this->count_time - time()) + 60) * 20;
						//if(!$player->hasEffect(Effect::JUMP)) $player->addEffect(Effect::getEffect(Effect::JUMP)->setDuration($time)->setAmplifier(130)->setVisible(false));//ジャンプ無効化
						if(!$player->hasEffect(Effect::FATIGUE)) $player->addEffect(Effect::getEffect(Effect::FATIGUE)->setDuration($time)->setAmplifier(5)->setVisible(false));//ブロック破壊無効化(応急処置)
						if($teleport){
							$zinti = new Vector3($fielddata['view'][0], $fielddata['view'][1], $fielddata['view'][2]);
							$player->teleport($zinti);
						}
					}else{
						if($sendMessage) $player->sendMessage($this->lang->translateString("gamewatching.failure.battle"));
						return false;
					}
				}else{
					if($sendMessage) $player->sendMessage($this->lang->translateString("gamewatching.failure.field"));
					return false;
				}
			}else{
				if($sendMessage) $player->sendMessage($this->lang->translateString("gamewatching.failure.now"));
				return false;
			}
		}else{
			if($teleport) $this->tpr($player);
			$out = $this->lang->translateString("tpr.respawn");
			unset($this->view[$user]);
			if($player->hasEffect(Effect::JUMP)) $player->removeEffect(Effect::JUMP);
			if($player->hasEffect(Effect::FATIGUE)) $player->removeEffect(Effect::FATIGUE);
			$this->setSpeed($player, true);
		}
		if($sendMessage) $player->sendMessage($out);
		return true;
	}

	public function updateStage(){
		$data = [];
		for($t = 0; $t <24; $t++){
			$stages = [13, 22, 23, 24, 25, 27, 28, 29, 30, 31, 32, 33, 35, 37, 38, 41, 42, 43, 44];
			shuffle($stages);
			$data[] = [
				"h" => [$t,$t],
				"s" => [
					$stages[0] => 33,
					$stages[1] => 33,
					$stages[2] => 34,
				],
			];
		}
		$this->s->setStagedata($data);
		echo "ステージ情報更新";
	}

	/**
	 * 試合観戦中の全員をリスポにテレポート
	 */
	public function Watch_end(){
		foreach($this->view as $name => $value){
			if(($player = $this->getServer()->getPlayer($name)) instanceof Player){
				$this->tpr($player);
				if($player->hasEffect(Effect::JUMP)) $player->removeEffect(Effect::JUMP);
				if($player->hasEffect(Effect::FATIGUE)) $player->removeEffect(Effect::FATIGUE);
			}
		}
		$this->view = [];
	}

	/**
	 * 動画撮影者向けの視点移動
	 * @param Player  $player
	 * @param array   $a           コマンドの引数をそのままつっこむ
	 * @param boolean $teleport    処理内でテレポートを実行させるかどうか        default = true
	 * @param boolean $sendMessage 処理内でプレイヤーにメッセージを送信するかどうか default = true
	 */
	public function Cam_c($player, $a, $teleport = true, $sendMessage = true){
		$user = $player->getName();
		if(isset($this->cam[$user]) && count($a) === 0){
			if($teleport){
				$this->tpr($player);
				if($sendMessage) $player->sendMessage($this->lang->translateString("tpr.respawn"));
			}
			$player->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE, false);
			$player->sendData($player);
			$player->setGamemode(Player::ADVENTURE);
			$this->delAllItem($player);
			$player->getInventory()->addItem(Item::get(340), Item::get(288));
			unset($this->cam[$user]);
			return true;
		}
		if(!($player instanceof Player)){
			if($sendMessage) $player->sendMessage($this->lang->translateString("command.UnavailableNotPlayer"));
			return true;
		}
		if($this->team->getBattleTeamOf($user)){
			if($sendMessage) $player->sendMessage($this->lang->translateString("command.cam.failure.battle"));
			return true;
		}
		$con = function()use($player, $user){
			if(isset($this->view[$user])){
				$this->GameWatching($player, false, false);
			}
			if(isset($this->trypaintData['player'][$user])){
				$this->TryPaint($player, false, false);
			}
			if(isset($this->reconData[$user])){
				$this->Recon($player, 0, false, false);
			}	
		};
		$hasOp = $this->s->hasOp($user);
		$hasMov = $this->s->hasMov($user);
		if(!$hasOp && !$hasMov){
			if($sendMessage) $player->sendMessage($this->lang->translateString("command.notPermission"));
			return true;
		}
		if(count($a) == 0) return false;
		if(count($a) == 1){//フィールド指定がされてない場合(移動地点は指定されている場合)
			$field = $this->field ?: ((!empty($this->nextfield)) ? $this->nextfield : false);
			if($field === false){
				if($sendMessage) $player->sendMessage($this->lang->translateString("command.cam.failure.field"));
				return true;
			}
			$pos_num = $a[0];
		}elseif(count($a) >= 2){
			if(!$hasOp && $sendMessage){
				$player->sendMessage($this->lang->translateString("command.cam.failure.fieldSelectNotPerm"));
				return true;
			}
			$field = $a[0];
			$pos_num = $a[1];
		}
		if(($field_data = $this->getBattleField($field)) != null and $field != 0){
			$con();
			$field_name = $field_data['name'];
			switch($pos_num){
				case 1:
					$tp_name = $this->lang->translateString("fieldPos.1");
					$pos = (isset($field_data['respawn-view'][1])) ? $field_data['respawn-view'][1] : $field_data['start'][1];
					break;
				case 2:
					$tp_name = $this->lang->translateString("fieldPos.2");
					$pos = (isset($field_data['respawn-view'][2])) ? $field_data['respawn-view'][2] : $field_data['start'][2];
					break;
				case 3:
					if(isset($field_data['view'])){
						$tp_name = $this->lang->translateString("fieldPos.3", [$field_name]);
						$pos = $field_data['view'];
					}else{
						if($sendMessage) $player->sendMessage($this->lang->translateString("command.cam.failure.viewUnsupported", [$field_name]));
						return true;
					}
					break;
				case 4:
					$tp_name = $this->lang->translateString("fieldPos.4");
					break;
				case 5:
					$tp_name = $this->lang->translateString("fieldPos.5");
					break;
				case 6:
					$tp_name = $this->lang->translateString("fieldPos.6");
					break;
				default:
					if($sendMessage) $player->sendMessage($this->lang->translateString("command.cam.failure.posUnsuported", [$field_name, " '".$pos_num."' "]));
					return true;
			}
			if($pos_num >= 4){
				if(isset($field_data['cam'][$pos_num])){
					$pos = $field_data['cam'][$pos_num];
				}else{
					if($sendMessage) $player->sendMessage($this->lang->translateString("command.cam.failure.posUnsuported", [$field_name, $tp_name]));
					return true;
				}
			}
			$this->seat->stand($player);
			$position = new Vector3($pos[0], $pos[1], $pos[2]);
			$yaw = $pos[3] ?? null;
			$pitch = $pos[4] ?? null;
			$player->teleportImmediate($position, $yaw, $pitch);
			$player->sendPosition($position, $yaw, $pitch);
			$player->setRotation($yaw, $pitch);
			$space = str_repeat(" ", mb_strlen($field_name, "UTF-8") / 2.5);
			if($sendMessage) $player->sendTip($this->lang->translateString("command.cam.success", [$field_name, $space, $tp_name]));
			$this->cam[$user] = $player->getGamemode();
			if(!$player->isSpectator()){
				$player->setGamemode(Player::SPECTATOR);
				$player->getInventory()->clearAll();
			}
			if(!$hasOp){
				$player->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE, true);
				$player->sendData($player);
				$player->setAllowFlight(false);
			}
			return true;
		}else{
			$out = $this->lang->translateString("field.notFound");
		}
		if($sendMessage) $player->sendMessage($out);
		return false;
	}

	public function setNeedLv($lv = 0){
		$this->needLv = $lv;
	}

	/**
	 * フィールドにいるcamを使ってる人をリスポにテレポート
	 */
	public function Camtpr(){
		foreach($this->cam as $user => $gamemode){
			if(($player = $this->getServer()->getPlayer($user)) instanceof Player){
				$this->tpr($player);
				$player->setGamemode(Player::ADVENTURE);
				$player->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE, false);
				$player->sendData($player);
			}
		}
		$this->cam = [];
	}

	/**
	 * 試し塗りを開始/終了
	 * @param Player  $player
	 * @param boolean $teleport    テレポートするかどうか      default = true
	 * @param boolean $sendMessage メッセージ送信をするかどうか default = true
	 */
	public function TryPaint(Player $player, $teleport = true, $sendMessage = true){
		$user = $player->getName();
		$con = function()use($player, $user){
			if(isset($this->view[$user])){
				$this->GameWatching($player, false, false);
			}
			if(isset($this->cam[$user])){
				$this->Cam_c($player, [], false, false);
			}
			if(isset($this->reconData[$user])){
				$this->Recon($player, 0, false, false);
			}
		};
		if(isset($this->trypaintData['player'][$user])){
			if($sendMessage) $player->sendMessage($this->lang->translateString("trypaint.quit"));
			$try_num = $this->trypaintData['player'][$user][1];
			$playerData = Account::getInstance()->getData($user);
			$playerData->setNowWeapon($this->trypaintData['player'][$user][4]);
			$player->removeAllEffects();
			$player->setMaxHealth(20);
			$player->setHealth(20);
			$player->extinguish();
			$player->setGamemode(Player::ADVENTURE);
			$this->ResetStatus($player, true);
			if(($inventory = $player->getInventory()) != null){
				$inventory->clearAll();
				$inventory->addItem(Item::get(340), Item::get(288));
				$inventory->sendContents($player);
			}
			$pos = new Vector3($this->lobbyPos[0], $this->lobbyPos[1], $this->lobbyPos[2]);
			if($teleport) $player->teleport($pos);
			if(isset($this->Task['TryPaintTask'][$user])){
				Server::getInstance()->getScheduler()->cancelTask($this->Task['TryPaintTask'][$user]->getTaskId());
				unset($this->Task['TryPaintTask'][$user]);
			}
			$this->w->CloseAllArrow($try_num);
			$this->w->resetFieldData($try_num);
			$this->trypaintData['status'][$try_num] = true;
			unset($this->trypaintData['player'][$user]);
		}else{
			if($this->team->getBattleTeamOf($user) && !$this->canChangeWeapon()){
				if($sendMessage) $player->sendMessage($this->lang->translateString("trypaint.battleNow"));
				return true;
			}
			if($this->team->getTeamOf($user)){
				if($sendMessage) $player->sendMessage($this->lang->translateString("trypaint.battleReady"));
				return true;
			}
			foreach($this->trypaintData['status'] as $try_num => &$status){
				if($status){
					$con();
					$limit = 120;//試し塗りできる制限時間
					$now_members = count($this->trypaintData['player']) + 1;
					$max_members = count($this->trypaintData['status']);
					$availablePer = 1 - $now_members / $max_members;
					switch(true){
						case $availablePer >= 0.5:
							$limit = 180;
							break;
						case $availablePer >= 0.2:
							$limit = 150;
							break;
						default:
							$limit = 120;
							break;
					}
					$status = false;
					$this->TryPaint_FieldReset($try_num);
					$field_data = $this->battle_field['try'][$try_num];
					$this->w->setFieldData($field_data, $try_num);
					#メンバーとして追加
					$members = $this->w->getBattleTeamMember();
					$members[0][] = $user;
					$this->w->setBattleMember($members);
					if($team_num = $this->team->getTeamOf($user)){
						$color = $this->team->getTeamColorBlock($team_num);
					}else{
						$color_ar = [14, 1, 4, 5, 3, 11, 2, 10];
						shuffle($color_ar);
						$color = $color_ar[0];
					}
					$playerData = Account::getInstance()->getData($user);
					$this->trypaintData['player'][$user] = [time() + $limit, $try_num, $color, [10, 10, 0], $playerData->getNowWeapon()];
					$weapon_num = $playerData->getNowWeapon();
					$weapon_data = $this->w->getWeaponData($weapon_num);
					$weapon_level = $playerData->getNowWeaponLevel();
					$rate = 0.002;
					$max_lv = 50;
					//$plus_tank = $weapon_level >= $max_lv ? $max_lv * $weapon_data[3] * $rate : $weapon_level * $weapon_data[3] * $rate;
					//$plus_tank = $plus_tank < 10 ? 0 : floor($plus_tank / 10) * 10;//10の位を切り下げる
					$plus_tank = 0;
					$tank = $weapon_data[3] + $plus_tank;
					$playerData->setData([
						'inkConsumption' => $weapon_data[2],
						'tank' => [$tank, $tank, $plus_tank],
						'paintAmount' => 0,
						'rate' => 0,
						'fieldNum' => $try_num,
						'color' => $color
					]);
					$pos_ar = $field_data['start'];
					if($teleport) $player->teleport(new Vector3($pos_ar[0], $pos_ar[1], $pos_ar[2]), $pos_ar[3], $pos_ar[4]);
					$player->setHealth(20);
					$this->ResetStatus($player, false);
					$player->extinguish();
					$player->setGamemode(Player::SURVIVAL);
					$this->seat->stand($player);
					$this->giveWeapon($player, 2);
					/*$tag = new CompoundTag("", []);
					$tag->display = new CompoundTag("display", [
						"Name" => new StringTag("Name", $this->lang->translateString("itemName.fieldReset"))
					]);*/

					$inventory = $player->getInventory();
					$item_watch = Item::get(347, 0, 1);
					$item_watch->setCustomName($this->lang->translateString("itemName.fieldReset"));
					$inventory->addItem($item_watch);
					$this->sendInkAmount($player);
					if($sendMessage) $player->sendMessage($this->lang->translateString("trypaint.start", [$limit]));
					$this->Task['TryPaintTask'][$user] = $this->getServer()->getScheduler()->scheduleRepeatingTask(new TryPaintTask($this, $player), 1);
					//MainLogger::getLogger()->debug("Try painting start (".$try_num." => ".$user.")");
					//$this->CreateHuman();
					return true;
				}
			}
			#試し塗り不可 (空いているフィールドがない)
			$player->sendMessage($this->lang->translateString("trypaint.failure.full"));
		}
		return false;
	}

	public function TryPaint_FieldReset($try_num){
		if(isset($this->battle_field['try'][$try_num])){
			$field_data = $this->battle_field['try'][$try_num];
			$this->w->setFieldData($field_data, $try_num);
			#フィールドの色リセット
			$level = $this->getServer()->getDefaultLevel();
			$scanpos = $field_data['scan'];
			$resetColor = $field_data['color'];
			$sx = min($scanpos[1][0], $scanpos[2][0]);
			$sy = min($scanpos[1][1], $scanpos[2][1]);
			$sz = min($scanpos[1][2], $scanpos[2][2]);
			$ex = max($scanpos[1][0], $scanpos[2][0]);
			$ey = max($scanpos[1][1], $scanpos[2][1]);
			$ez = max($scanpos[1][2], $scanpos[2][2]);
			for($x = $sx; $x <= $ex; ++$x){
				for($y = $sy; $y <= $ey; ++$y){
					for($z = $sz; $z <= $ez; ++$z){
						if($level->getBlockIdAt($x, $y, $z) == 35){
							$level->setBlockDataAt($x, $y, $z, $resetColor);
						}
					}
				}
			}
		}
	}

	public function TryPaint_TimeCheck(Player $player){
		$user = $player->getName();
		if(isset($this->trypaintData['player'][$user])){
			$limit = $this->trypaintData['player'][$user][0];
			$time = $limit - time();
			if($time <= 0){
				$this->TryPaint($player, true, false);
				$player->sendMessage($this->lang->translateString("trypaint.finish"));
				return true;
			}
			$minutes = floor($time / 60);
			$seconds = abs($time) % 60;
			$message = "§l§7".sprintf("%d:%02d", $minutes, $seconds);
			$player->sendTip($message);
			$this->PlayerPositionCheck($player);
			/*
			#Todo
			HumanのEntityだしてどれだけのダメージ与えてるかネームタグで表示
			EntityDamageEvent使ってダメージ受けたらネームタグ変更
			Task使って(ここ使う)約2秒後に体力戻す or Entity再スポーンとかも実装(?)
			*/
			/*foreach($this->entityData as $eid => $entity){
				if(empty($this->damageReset[$eid])) continue;
				if(microtime(true) - $this->damageReset[$eid] >= 5){//2秒がいいかもな
					if($entity->isAlive()){
						$entity->setNameTag("0");
						$entity->setHealth(20);
					}else{
						//$entity->respawnToAll();
						//$entity->setNameTag("0");
						$entity->close();
						$this->CreateHuman();
						unset($this->damageReset[$eid]);
						unset($this->entityData[$eid]);
					}
				}
			}*/
		}else{
			if(isset($this->Task['TryPaintTask'][$user])){
				Server::getInstance()->getScheduler()->cancelTask($this->Task['TryPaintTask'][$user]->getTaskId());
				unset($this->Task['TryPaintTask'][$user]);
			}
		}
	}



	/**
	 * アイテムを落とす
	 * @param Player $player
	 */
	public function AddscattersItem($player){
		$user = $player->getName();
		$level = $player->getlevel();
		if($this->game == 10 and ($battle_team = $this->team->getBattleTeamOf($user))){
			$team = $this->team->getTeamOf($user);
			if(!isset($this->scattersItem[$user]['ink'], $this->scattersItem[$user]['weapon'])){
				$pk_i = new AddItemEntityPacket;
				$pk_i->eid = $this->scattersItem[$user]['ink'] = Entity::$entityCount++;
				$pk_i->item = Item::get(351);
				$pk_i->x = $player->x;
				$pk_i->y = $player->y + 0.25;
				$pk_i->z = $player->z;
				$pk_i->speedX = mt_rand(-10, 10)/100;
				$pk_i->speedY = 0.1;
				$pk_i->speedZ = mt_rand(-10, 10)/100;

				$pk_w = new AddItemEntityPacket;
				$pk_w->eid = $this->scattersItem[$user]['weapon'] = Entity::$entityCount++;
				$weapon_num = Account::getInstance()->getData($user)->getNowWeapon();
				$item = $this->w->getWeaponItemId($weapon_num);
				$pk_w->item = Item::get($item[0], $item[1]);
				$pk_w->x = $player->x;
				$pk_w->y = $player->y + 0.25;
				$pk_w->z = $player->z;
				$pk_w->speedX = mt_rand(-10, 10)/100;
				$pk_w->speedY = 0.1;
				$pk_w->speedZ = mt_rand(-10, 10)/100;
				$players = $level->getChunkPlayers($pk_i->x >> 4, $pk_i->z >> 4);
				Server::getInstance()->broadcastPacket($players, $pk_i);
				Server::getInstance()->broadcastPacket($players, $pk_w);
				$this->scattersItem[$user]['players'] = $players;
				return true;
			}
			return false;
		}
	}

	/**
	 * 落としたアイテムをばいばい
	 * @param Player $player
	 */
	public function RemovescattersItem($player){
		$user = $player->getName();
		$level = Server::getInstance()->getDefaultLevel();
		if($level !== null && isset($this->scattersItem[$user]['ink'])){
			$pk_i = new RemoveEntityPacket;
			$pk_i->eid = $this->scattersItem[$user]['ink'];

			$pk_w = new RemoveEntityPacket;
			$pk_w->eid = $this->scattersItem[$user]['weapon'];

			$players = $this->scattersItem[$user]['players'];
			Server::getInstance()->broadcastPacket($players, $pk_i);
			Server::getInstance()->broadcastPacket($players, $pk_w);
			unset($this->scattersItem[$user]);
			return true;
		}
		return false;
	}

	public function AllRemovescattersItem(){
		foreach($this->scattersItem as $user => $items){
			foreach($items as $key => $eid){
				if($key !== 'players'){
					$pk = new RemoveEntityPacket;
					$pk->eid = $eid;
					Server::getInstance()->broadcastPacket($items['players'], $pk);
				}
			}
		}
		$this->scattersItem = [];
	}

	/*public function CreateHuman(){
		$pos = [-21.5,11,43.5];
		$name = "0";
		$level = Server::getInstance()->getDefaultLevel();
		$SkinData = file_get_contents("../skintxt/#splaturn_boy.skintxt");
		$SkinName = "Standard_Custom";
		$nbt = new CompoundTag;
		$nbt->Pos = new EnumTag("Pos", [
			new DoubleTag("", $pos[0]),
			new DoubleTag("", $pos[1]),
			new DoubleTag("", $pos[2])
		]);
		$motion = new Vector3(0, 0, 0);
		$nbt->Motion = new EnumTag("Motion", [
			new DoubleTag("", 0),
			new DoubleTag("", 0),
			new DoubleTag("", 0)
		]);
		$nbt->Rotation = new EnumTag("Rotation", [
			new FloatTag("", 270),
			new FloatTag("", 0)
		]);
		$nbt->Health = new ShortTag("Health", 20);
		$nbt->NameTag = new StringTag("name", $name);
		$nbt->Invulnerable = new ByteTag("Invulnerable", 1);
		$nbt->CustomTestTag = new ByteTag("CustomTestTag", 1);
		$nbt->Skin = new CompoundTag("Skin", [
			"Data" => new StringTag("Data", $SkinData),
			"Name" => new StringTag("Name", $SkinName)
		]);
		$e = Entity::createEntity("Human", $level->getChunk($pos[0] >> 4, $pos[2] >> 4), $nbt);
		$e->spawnToAll();
		$this->entityData[$e->getId()] = $e;
		return true;
	}*/

	/**
	 * プレイヤーを攻撃できるかどうか(resultがtrueなら攻撃できる)
	 * @param  Entity $d 攻撃した相手
	 * @param  Entity $s 攻撃された相手
	 * @return array ['result' => bool, 'reason' => 0]
	 */
	public function canAttack($d, $s){
		$return = ['result' => false, 'reason' => 0];
		//reason = [0 => no reason, 1 => battle does not 2 => not the enemy]
		if($this->game == 10){
			$dt = $this->team->getBattleTeamOf($d);
			$st = $this->team->getBattleTeamOf($s);
			if($dt !== 0 and $st !== 0){
				//return $dt != $st ? true : false;
				$bool = ($dt != $st);
				$return['result'] = $bool;
				if(!$bool) $return['reason'] = 2;
			}else{
				$return = ['result' => false, 'reason' => 1];
			}
		}else{
			$return = ['result' => false, 'reason' => 1];
		}
		return $return;
	}

	/**
	* 即時復活関数
	* @param Player $damager
	* @param Player $player
	* @param String $mes $damagerが存在しなかったときのメッセージ
	* @param Cause $cause ダメージのコース
	*/
	public function OnDeath($damager, $player, $mes = null, $cause = EntityDamageEvent::CAUSE_ENTITY_ATTACK){
		if(!($damager instanceof Player)){
			if(Enemy::isEnemy($damager)){
				$this->deathCount++;
				if(!isset($this->kc[$player->getName()])){
					$this->kc[$player->getName()]["kill"] = 0;
					$this->kc[$player->getName()]["death"] = 0;
				}
				$this->kc[$player->getName()]["death"] += 1;
				$message = "death.attack.player";
				$params = [
					$player->getDisplayName(),
					$damager->getNameTag()
				];
				$death_message = (new TranslationContainer($message, $params));
				Server::getInstance()->broadcastMessage($death_message);
				$color = 1;
				$level = Server::getInstance()->getDefaultLevel();
				$x = $player->x;
				$y = $player->y;
				$z = $player->z;
				$paint = 7;//キル時の塗り範囲
				for($xx = -floor($paint/2); $xx < ceil($paint/2); $xx++){
					for($yy = -floor($paint/2); $yy < ceil($paint/2); $yy++){ 
						for($zz = -floor($paint/2); $zz < ceil($paint/2); $zz++){ 
							if($level->getBlockIdAt(floor($xx+$x), floor($yy+$y), floor($zz+$z)) === 35){
								//$pos_ar[] = [floor($xx+$x), floor($yy+$y), floor($zz+$z)];
								$level->setBlockDataAt(floor($xx+$x), floor($yy+$y), floor($zz+$z), $color);
							}
						}
					}			
				}
				Server::getInstance()->broadcastMessage("§c倒された回数：".$this->deathCount."回");
				$F = function($array){
					$array[0]->addParticle($array[1]);
				};
				$block = Block::get(35, $color);
				$radius = 3.3;
				$radius_1 = $radius/2; //球の半径
				//$radius_2 = $radius*1.25; //球の半径
				$radius_3 = $radius; //球の半径
				$p = new Vector3($x, $y, $z);
				$level->addSound(new SplashSound($p));
				for($yaw = 0; $yaw < 360; $yaw += 360/(M_PI*$radius)){

					for($pitch = 0; $pitch <360; $pitch += 360/(M_PI*$radius)){

						$rad_y = $yaw/180*M_PI;
						$rad_p = ($pitch-180)/180*M_PI;
						$xx = sin($rad_y)*cos($rad_p);
						$yy = sin($rad_p);
						$zz = -cos($rad_y)*cos($rad_p);
						$p->x = $x+$xx*$radius_1;
						$p->y = $y+$yy*$radius_1;
						$p->z = $z+$zz*$radius_1;
						$particle_1 = new TerrainParticle($p, $block);
						$level->addParticle($particle_1);
						$p->x = $x+$xx*$radius_3;
						$p->y = $y+$yy*$radius_3;
						$p->z = $z+$zz*$radius_3;
						$particle_3 = new TerrainParticle($p, $block);
						Server::getInstance()->getScheduler()->scheduleDelayedTask(new LateDo($this, $F, [$level, $particle_3]),3);
	
					}
					$damager->target = false;
				}
			}
			$user = $player->getName();
			if($this->game == 10 and ($team = $this->team->getBattleTeamOf($user))){
				$this->AddscattersItem($player);
				$params = [
					$player->getDisplayName()
				];
//				$damagerName = $damager->getName();
				$message = $mes;
				//$damager->getInventory()->getgetItemInHand()->getId();
//				$params[] = $damager->getDisplayName()."(".$this->w->getWeaponName(Account::getInstance()->getData($damagerName)->getNowWeapon()).")";
				$death_message = (new TranslationContainer($message, $params));
				//$this->getServer()->broadcastMessage($death_message);

				$players = Server::getInstance()->getOnlinePlayers();
				//試合に参加or観戦してる人にのみメッセージ表示するやつ
				foreach($players as $p){
					$name = $p->getName();
					if($this->team->getBattleTeamOf($name) or isset($this->view[$name]) or isset($this->cam[$name])){
						$p->sendMessage($death_message);
					}
				}

/*				$playerData = Account::getInstance()->getData($damagerName);//ここからキル時の塗り
				$color = $playerData->getColor();
				$level = $player->getLevel();
				$x = $player->x;
				$y = $player->y;
				$z = $player->z;
				$paint = 9;//キル時の塗り範囲
				$pos_ar = [];
				for($xx = -floor($paint/2); $xx < ceil($paint/2); $xx++){
					for($yy = -floor($paint/2); $yy < ceil($paint/2); $yy++){ 
						for($zz = -floor($paint/2); $zz < ceil($paint/2); $zz++){ 
							if($level->getBlockIdAt(floor($xx+$x), floor($yy+$y), floor($zz+$z)) === 35){
								$pos_ar[] = [floor($xx+$x), floor($yy+$y), floor($zz+$z)];
							}
						}
					}			
				}
				$this->w->changeWoolsColor($level, $pos_ar, $color, $damagerName, false);
				if($player->getMaxHealth() < 30){
					$player->setMaxHealth($player->getMaxHealth() + 2);
				}
				*/
				$player->setHealth($player->getMaxHealth());

				$playerData = Account::getInstance()->getData($user);
				$player->removeAllEffects();
				$field_data = $this->getBattleField($this->field);
				if($this->TPanimation instanceof TPanimation){
					$pos = $this->TPanimation->getPlayerTeleportPosition($player);
					$player->sendPosition($pos);
					$player->teleport($pos);
				}elseif($this->BattleResultAnimation instanceof BattleResultAnimation && isset($field_data['cam'][4])){
					$player->setGamemode(Player::SPECTATOR);
					$pos = $field_data['cam'][4];
					$yaw = $pos[3] ?? null;
					$pitch = $pos[4] ?? null;
					$position = new Location($pos[0], $pos[1], $pos[2], $yaw, $pitch, $this->getLevelByBattleField($this->field));
					$player->teleport($position);
					$player->sendPosition($position, $yaw, $pitch);
					$player->setRotation($yaw, $pitch);
					$player->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE, true);
					$player->sendData($player);
					$player->setAllowFlight(false);
				}else{
					$f = $field_data['start'][$team];
					$number = $this->team->battleTeamMember[$team][$user];
					$plus_pos = (isset($this->tweakPosition[$number])) ? $this->tweakPosition[$number] : [0, 0];
					$player->teleport(new Location($f[0] + $plus_pos[0], $f[1], $f[2] + $plus_pos[1], $f[3], $f[4], $this->getLevelByBattleField($this->field)));
				}
				$playerData->fillInk($user);
				switch(true){
					case $this->dev:
						$giveType = 3;
						break;
					default:
						$giveType = 0;
				}
				if($this->dev == 2){
					$giveType = -1;
				}
				$this->giveWeapon($player, $giveType);
				$this->ResetStatus($player, false);
				//移動不可に&インク回復開始
				$player->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE, true);
				$player->sendData($player);
				$this->Task['Respawn'][$user] = $this->getServer()->getScheduler()->scheduleRepeatingTask(new Respawn($this, $player), 1);
				unset($this->tprCheckData[strtolower($user)]);
				if($this->gamestop){
					$player->addEffect(Effect::getEffect(Effect::BLINDNESS)->setDuration(6000*20)->setAmplifier(0)->setVisible(false));
				}

			}else{ 
				return false;
			}
			#Seat
			$this->seat->stand($player);
			return true;			
		}else{
			$damagerName = $damager->getName();
			$user = $player->getName();
			$playerData = Account::getInstance()->getData($damagerName);//ここからキル時の塗り
			if($this->game == 10 and ($team = $this->team->getBattleTeamOf($user))){
				$this->AddscattersItem($player);
				$params = [
					$player->getDisplayName()
				];
				$damagerName = $damager->getName();
				$message = "death.attack.player";
				if($cause == EntityDamageEvent::CAUSE_ENTITY_ATTACK){
					$nowweap = Account::getInstance()->getData($damagerName)->getNowWeapon();
					$weapdata = $this->w->getWeaponData($nowweap);
					$weapType = $weapdata['type'];
					if($weapType == 3){
						$level = $player->getLevel();
						$as = new AnvilFallSound(new Vector3($damager->x, $damager->y, $damager->z));
						$level->addSound($as, [$damager]);
					}
					$weaponName = $this->w->getWeaponName($nowweap);
					if($playerData->getNowWeaponLevel() >= 30){
						switch($nowweap){
							case Weapon::SPLATTERSHOT_PRO:
							case Weapon::SPLATTERSHOT_PRO_BERRY:
							case Weapon::SPLATTERSHOT_PRO_COLLABO:
								$weaponName = "§e✬*ﾟ§f".$weaponName;
								break;
							case Weapon::SPLATTERSHOT_JR_SAKURA:
								$weaponName = "§d❀§f".$weaponName;
								break;
							case Weapon::SPLATTERSHOT_JR_MOMIJI:
								$weaponName = "§c☘§f".$weaponName;
								break;
							case Weapon::SPLATTERSHOT_JR:
								$weaponName = "§a☘§f".$weaponName;
								break;
							case Weapon::DELTA_SQUELCHER_M:
							case Weapon::DELTA_SQUELCHER_T:
							case Weapon::DELTA_SQUELCHER_N:
								$weaponName = "§3δ§f".$weaponName;
								break;
							case Weapon::WILLEM:
							case Weapon::WILLEM_HEW:
							case Weapon::WILLEM_DECAYED:
								$weaponName = "§3Ψ§f".$weaponName;
								break;
							case Weapon::SPLATTERSHOT:
							case Weapon::SPLATTERSHOT_COLLABO:
							case Weapon::SPLATTERSHOT_WASABI:
								$weaponName = "§2〆§f".$weaponName;
								break;
							case Weapon::DUAL_SQUELCHER:
							case Weapon::DUAL_SQUELCHER_CUSTOM:
								$weaponName = "§c》《§f".$weaponName;
								break;
							case Weapon::L3_NOZZLENOSE:
							case Weapon::L3_NOZZLENOSE_D:
							case Weapon::T3_NOZZLENOSE:
							case Weapon::T3_NOZZLENOSE_D:
							case Weapon::T3_NOZZLENOSE_P:
								$weaponName = "§bж§f".$weaponName;
								break;
							case Weapon::GAL_96:
							case Weapon::GAL_96_DECO:
								$weaponName = "§8✡§f".$weaponName;
								break;
							case Weapon::SPLASH_O_MATIC:
							case Weapon::SPLASH_O_MATIC_NEO:
							case Weapon::SPLASH_O_MATIC_TORA:
								$weaponName = "§7＃§f".$weaponName;
								break;
							case Weapon::SLOSHER:
							case Weapon::SLOSHER_DECO:
							case Weapon::SLOSHER_SODA:
							case Weapon::BRUSHWASHER:
							case Weapon::SPLAT_LADLE:
							case Weapon::SPLAT_LADLE_CUSTOM:
							case Weapon::SPLAT_LADLE_NECRO:
								$weaponName = "§d∠§6*｡ﾟ§f".$weaponName;
								break;
							case Weapon::SPLOOSH_O_MATIC_SEVEN:
								$weaponName = "§4§o７§f".$weaponName;
								break;
							case Weapon::HEAVY_SPLATLING:
							case Weapon::HEAVY_SPLATLING_DECO:
							case Weapon::HEAVY_SPLATLING_REMIX:
							case Weapon::MINI_SPLATLING:
							case Weapon::MINI_SPLATLING_REPAIR:
							case Weapon::MINI_SPLATLING_COLLABO:
							case Weapon::HYDRA_SPLATLING:
							case Weapon::HYDRA_SPLATLING_CUSTOM:
								$weaponName = "§7=€§6*｡ﾟ§r".$weaponName;
								break;
							case Weapon::RAPID_BLASTER:
							case Weapon::RAPID_BLASTER_DECO:
								$weaponName = "§dᙏ§r".$weaponName;
								break;
							case Weapon::LUNA_BLASTER:
							case Weapon::LUNA_BLASTER_NEO:
								$weaponName = "§l§e☪§r".$weaponName;
								break;
							case Weapon::AEROSPRAY_PG:
							case Weapon::AEROSPRAY_MG:
							case Weapon::AEROSPRAY_RG:
								$weaponName = "§a§l∂§r".$weaponName;
								break;
							case Weapon::OCTOBRUSH_COMET:
							case Weapon::BRUSHWASHER_METEOR:
							case Weapon::GAL_52_VEGA:
							case Weapon::GAL_96_SPICA:
							case Weapon::DUAL_SQUELCHER_GEMINI:
							case Weapon::L3_NOZZLENOSE_ALTAIR:
							case Weapon::RAPID_BLASTER_SIRIUS:
							case Weapon::LUNA_BLASTER_MERCURY:
							case Weapon::HOT_BLASTER_LIBRA:
							case Weapon::HYDRA_SPLATLING_REGULUS:
								$weaponName = "§b✬*ﾟ§f".$weaponName;
								break;

							default:
								$weaponName = "ᔦꙬᔨ".$weaponName;
							break;
						}
						if($playerData->getNowWeaponLevel() >= 50){
							$weaponName = "§e♔§r".$weaponName;
							if($playerData->getNowWeaponLevel() >= 75){
								$weaponName = "§6♔§r".$weaponName;
								if($playerData->getNowWeaponLevel() >= 100){
									$weaponName = "§c♔§r".$weaponName;
								}
							}
						}
					}
				}else if($cause == EntityDamageEvent::CAUSE_SUFFOCATION){
					$weaponName = "ステージギミック";
				}else if($cause == EntityDamageEvent::CAUSE_FIRE){
					$weaponName = "§4†天罰†§f"; 
				}else{
					$weaponName = $this->w->getSubWeaponName($this->w->getSubWeaponNumFromWeapon(Account::getInstance()->getData($damagerName)->getNowWeapon()));
					if($playerData->getNowWeaponLevel() >= 30){
						$weaponName = "ᔦꙬᔨ".$weaponName;
						if($playerData->getNowWeaponLevel() >= 50){
							$weaponName = "§e♔§r".$weaponName;
							if($playerData->getNowWeaponLevel() >= 75){
								$weaponName = "§6♔§r".$weaponName;
								if($playerData->getNowWeaponLevel() >= 100){
									$weaponName = "§c♔§r".$weaponName;
								}
							}
						}
					}
				}

				$params[] = $damager->getDisplayName()."(".$weaponName.")";
				$death_message = (new TranslationContainer($message, $params));
				//$this->getServer()->broadcastMessage($death_message);
				$players = Server::getInstance()->getOnlinePlayers();
				foreach($players as $p){
					$name = $p->getName();
					if($this->team->getBattleTeamOf($name) or isset($this->view[$name]) or isset($this->cam[$name])){
						$p->sendMessage($death_message);
						if($this->team->getBattleTeamOf($name) == $this->team->getBattleTeamOf($player->getName()) && Gadget::getCorrection($p, Gadget::NECRO_PAINT)){
							$playerData = Account::getInstance()->getData($name);//ここからキル時の塗り
							$playerData->stockInk($playerData->getInkTank()*0.2);
							$this->sendInkAmount($player);
							$color = $playerData->getColor();
							$level = $p->getLevel();
							$x = $p->x;
							$y = $p->y;
							$z = $p->z;
							$paint = 5;//塗り範囲
							$pos_ar = [];
							for($xx = -floor($paint/2); $xx < ceil($paint/2); $xx++){
								for($yy = -floor($paint/2); $yy < ceil($paint/2); $yy++){ 
									for($zz = -floor($paint/2); $zz < ceil($paint/2); $zz++){ 
										if($level->getBlockIdAt(floor($xx+$x), floor($yy+$y), floor($zz+$z)) === 35){
											$pos_ar[] = [floor($xx+$x), floor($yy+$y), floor($zz+$z)];
										}
									}
								}			
							}
							$this->w->changeWoolsColor($level, $pos_ar, $color, $name, false);
						}
					}
				}
				$playerData = Account::getInstance()->getData($damagerName);//ここからキル時の塗り
				$color = $playerData->getColor();
				$level = $player->getLevel();
				$x = $player->x;
				$y = $player->y;
				$z = $player->z;
				$paint = 7+Gadget::getCorrection($damager, Gadget::HOTARU)+Gadget::getCorrection($player, Gadget::HOTARU);//キル時の塗り範囲
				$playerData->stockInk($playerData->getInkTank()*Gadget::getCorrection($damager, Gadget::AORI));
				$this->sendInkAmount($player);
				$pos_ar = [];
				for($xx = -floor($paint/2); $xx < ceil($paint/2); $xx++){
					for($yy = -floor($paint/2); $yy < ceil($paint/2); $yy++){ 
						for($zz = -floor($paint/2); $zz < ceil($paint/2); $zz++){ 
							if($level->getBlockIdAt(floor($xx+$x), floor($yy+$y), floor($zz+$z)) === 35){
								$pos_ar[] = [floor($xx+$x), floor($yy+$y), floor($zz+$z)];
							}
						}
					}			
				}
				$this->w->changeWoolsColor($level, $pos_ar, $color, $damagerName, false);

				//キルエフェクト
				$F = function($array){
					$array[0]->addParticle($array[1]);
				};
				$block = Block::get(35, $color);
				$radius = 3.3;
				$radius_1 = $radius/2; //球の半径
				//$radius_2 = $radius*1.25; //球の半径
				$radius_3 = $radius; //球の半径
				$p = new Vector3($x, $y, $z);
				$level->addSound(new SplashSound($p));
				for($yaw = 0; $yaw < 360; $yaw += 360/(M_PI*$radius)){

					for($pitch = 0; $pitch <360; $pitch += 360/(M_PI*$radius)){

						$rad_y = $yaw/180*M_PI;
						$rad_p = ($pitch-180)/180*M_PI;
						$xx = sin($rad_y)*cos($rad_p);
						$yy = sin($rad_p);
						$zz = -cos($rad_y)*cos($rad_p);
						$p->x = $x+$xx*$radius_1;
						$p->y = $y+$yy*$radius_1;
						$p->z = $z+$zz*$radius_1;
						$particle_1 = new TerrainParticle($p, $block);
						$level->addParticle($particle_1);

/*						$p->x = $x+$xx*$radius_2;
						$p->y = $y+$yy*$radius_2;
						$p->z = $z+$zz*$radius_2;
						$particle_2 = new TerrainParticle($p, $block);
						Server::getInstance()->getScheduler()->scheduleDelayedTask(new lateDo($this, $F, [$level, $particle_2]),2);*/
					
						$p->x = $x+$xx*$radius_3;
						$p->y = $y+$yy*$radius_3;
						$p->z = $z+$zz*$radius_3;
						$particle_3 = new TerrainParticle($p, $block);
						Server::getInstance()->getScheduler()->scheduleDelayedTask(new LateDo($this, $F, [$level, $particle_3]),3);
	
					}
				}
				if($player->getMaxHealth() < 30 && !Gadget::getCorrection($player, Gadget::IDATEN)){
					$player->setMaxHealth($player->getMaxHealth() + 2);
				}
				$player->setHealth($player->getMaxHealth());

				if(Gadget::getCorrection($player, Gadget::REGRET)){
					$this->w->setSensor($player, $damager);
				}

				$playerData = Account::getInstance()->getData($user);
				$player->removeAllEffects();
				$field_data = $this->getBattleField($this->field);
				if($this->TPanimation instanceof TPanimation){
					$pos = $this->TPanimation->getPlayerTeleportPosition($player);
					$player->sendPosition($pos);
					$player->teleport($pos);
				}elseif($this->BattleResultAnimation instanceof BattleResultAnimation && isset($field_data['cam'][4])){
					$player->setGamemode(Player::SPECTATOR);
					$pos = $field_data['cam'][4];
					$yaw = $pos[3] ?? null;
					$pitch = $pos[4] ?? null;
					$position = new Location($pos[0], $pos[1], $pos[2], $yaw, $pitch, $this->getLevelByBattleField($this->field));
					$player->teleport($position);
					$player->sendPosition($position, $yaw, $pitch);
					$player->setRotation($yaw, $pitch);
					$player->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE, true);
					$player->sendData($player);
					$player->setAllowFlight(false);
				}else{
					$f = $field_data['start'][$team];
					$number = $this->team->battleTeamMember[$team][$user];
					$plus_pos = (isset($this->tweakPosition[$number])) ? $this->tweakPosition[$number] : [0, 0];
					$player->teleport(new Location($f[0] + $plus_pos[0], $f[1], $f[2] + $plus_pos[1], $f[3], $f[4], $this->getLevelByBattleField($this->field)));
				}
				$playerData->fillInk($user);
				switch(true){
					case $this->dev:
						$giveType = 3;
						break;
					default:
						$giveType = 0;
				}
				if($this->dev == 2){
					$giveType = -1;
				}
				$this->giveWeapon($player, $giveType);
				$this->ResetStatus($player, false);
				//移動不可に&インク回復開始
				$player->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE, true);
				$player->sendData($player);
				$this->Task['Respawn'][$user] = $this->getServer()->getScheduler()->scheduleRepeatingTask(new Respawn($this, $player), 1);
				unset($this->tprCheckData[strtolower($user)]);
				if($this->gamestop){
					$player->addEffect(Effect::getEffect(Effect::BLINDNESS)->setDuration(6000*20)->setAmplifier(0)->setVisible(false));
				}

			}else{ 
				return false;
			}
			#Seat
			$this->seat->stand($player);
			return true;
		}
	}
	/**
	 * OPの付与、剥奪処理を実行
	 * @param  Player $player
	 * @return bool   権限付与/剥奪処理をしたかどうか
	 */
	public function checkPermission($player){
		$result = false;
		$user = $player->getName();
		if($this->s->hasOp($user) and !$player->isOp()){
			$player->sendMessage("§7You are now op!");
			$player->setOp(true);
			$result = true;
		}
		if(!$this->s->hasOp($user) and $player->isOp()){
			$player->sendMessage("§7You are no longer op!");
			$player->setOp(false);
			$result = true;
		}

		$player->addAttachment($this, "splatt.command.dev", $this->s->hasDev($user));
		$player->addAttachment($this, "splatt.command.mov", ($this->s->hasOp($user) || $this->s->hasMov($user)));
		$perms = [
			"pocketmine.command.op.take",
			"pocketmine.command.op.give",
		];
		foreach($perms as $name){
			$player->addAttachment($this, $name, false);
		}
		$hasOp = $this->s->hasOp($user);
		switch(true){
			case $hasOp === 0://OP候補
				$perms = [
					"pocketmine.command.list" => true,
					"splatt.command.admin+" => false,
					"splatt.command.behavior" => true,
				];
				foreach($perms as $name => $value){
					$player->addAttachment($this, $name, $value);
				}
				break;
			case $hasOp === 1://OP
				$perms = [
					"pocketmine.command.defaultgamemode" => false,
					"pocketmine.command.difficulty" => false,
					"pocketmine.command.reload" => false,
					"pocketmine.command.save.disable" => false,
					"pocketmine.command.save.enable" => false,
					"pocketmine.command.setworldspawn" => false,
					"pocketmine.command.spawnpoint" => false,
					"pocketmine.command.time.add" => false,
					"pocketmine.command.time.set" => false,
					"pocketmine.command.time.start" => false,
					"pocketmine.command.time.stop" => false,
					"splatt.command.admin+" => false,
				];
				foreach($perms as $name => $value){
					$player->addAttachment($this, $name, $value);
				}
				break;
			case $hasOp === 2://OPC
				break;
			default:
				break;

		}
		return $result;
	}

	/**
	 * リスポーン地点にテレポート
	 * @param  Player  $player
	 * @return boolean         (Lobbyのリスポーン地点にテレポートした場合trueを、試合開始地点の場合false)
	 */
	public function tpr($player){
		$this->seat->stand($player);
		$user = $player->getName();
		if($this->checkFieldteleport() and ($team = $this->team->getBattleTeamOf($user))){
			$mapno = $this->field;
			$field_data = $this->getBattleField($mapno);
			$f = $field_data['start'][$team];
			$this->changeName($player);
			if($this->TPanimation instanceof TPanimation){
				$pos = $this->TPanimation->getPlayerTeleportPosition($player);
				$player->teleport($pos);
				$player->sendPosition($pos);
			}elseif($this->BattleResultAnimation instanceof BattleResultAnimation && isset($field_data['cam'][4])){
				$player->setGamemode(Player::SPECTATOR);
				$pos = $field_data['cam'][4];
				$position = new Vector3($pos[0], $pos[1], $pos[2]);
				$yaw = $pos[3] ?? null;
				$pitch = $pos[4] ?? null;
				$player->teleport($position, $yaw, $pitch);
				$player->sendPosition($position, $yaw, $pitch);
				$player->setRotation($yaw, $pitch);
				$player->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE, true);
				$player->sendData($player);
				$player->setAllowFlight(false);
			}else{
				$number = $this->team->battleTeamMember[$team][$user];
				$plus_pos = (isset($this->tweakPosition[$number])) ? $this->tweakPosition[$number] : [0, 0];
				$player->teleport(new Location($f[0] + $plus_pos[0], $f[1], $f[2] + $plus_pos[1], $f[3], $f[4], $this->getLevelByBattleField($mapno)), $f[3], $f[4]);
			}
			switch(true){
				case $this->dev:
					$giveType = 3;
					break;
				default:
					$giveType = 0;
			}
			if($this->dev == 2){
				$giveType = -1;
			}
			$this->giveWeapon($player, $giveType);
			return false;
		}else{
			if(isset($this->view[$user])){
				$this->GameWatching($player, false, false);
			}
			if(isset($this->cam[$user])){
				$this->Cam_c($player, [], false, false);
			}
			if(isset($this->reconData[$user])){
				$this->Recon($player, 0, false, false);
			}
			if(isset($this->trypaintData['player'][$user])){
				$try_num = $this->trypaintData['player'][$user][1];
				$field_data = $this->battle_field['try'][$try_num];
				$try_num = $this->trypaintData['player'][$user][1];
				$pos_ar = $field_data['start'];
				$player->teleport(new Location($pos_ar[0], $pos_ar[1], $pos_ar[2], $pos_ar[3], $pos_ar[4], $player->getLevel()));
			}else{
				$this->delAllItem($player);
				$id = Item::get(340);
				$id2 = Item::get(288);
				$player->getInventory()->addItem($id, $id2);
				$player->teleport(new Position($this->lobbyPos[0], $this->lobbyPos[1], $this->lobbyPos[2], $player->getLevel()));
				return true;
			}
		}
	}

	public function startTprCheck(){
		$this->Task['tprCheck'] = Server::getInstance()->getScheduler()->scheduleRepeatingTask(new tprCheckTask($this), 1);
	}

	public function stopTprCheck(){
		if(isset($this->Task['tprCheck'])){
			Server::getInstance()->getScheduler()->cancelTask($this->Task['tprCheck']->getTaskId());
			unset($this->Task['tprCheck']);
		}
	}

	public function tprCheck(){
		foreach($this->tprCheckData as $user => $time){
			if(microtime(true) >= $time){
				if($this->game == 10 && ($player = Server::getInstance()->getPlayer($user)) instanceof Player && !isset($this->Task['Respawn'][$user])){
					$player->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE, false);
					$player->sendData($player);
					$this->tpr($player);
				}
				unset($this->tprCheckData[$user]);
			}
		}
	}

	/**
	 * プレイヤーが羊毛を塗れるかどうか
	 * @param  Player  $player
	 * @return boolean
	 */
	public function canPaint($player){
		$user = $player->getName();
		return (($this->team->getBattleTeamOf($user) && $this->game == 10) || isset($this->trypaintData['player'][$user])) && $player->getDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE) == false;
	}

	public function BattlePlayersPositionCheck(){
		$teams = $this->team->getBattleTeamMember();
		foreach($teams as $team => $members){
			foreach($members as $member => $number){
				if(($player = $this->getServer()->getPlayer($member)) instanceof Player){
					$this->PlayerPositionCheck($player);
				}
			}
		}
	}

	//プレイヤーの座標チェック
	public function PlayerPositionCheck(Player $player, $damage = false, $x = null, $y = null, $z = null){
		$this->w->ChargingCheck($player, $player->getInventory()->getItemInHand()->getId());
		if($player->getDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE)) return true;
		$removeAllEffect = true;
		$user = $player->getName();
		$player_bt = $this->team->getBattleTeamOf($user);
		//座標とか取得
		if($x === null){
			$x = Math::floorFloat($player->x);
			$y = Math::floorFloat($player->y - 0.08);
			$z = Math::floorFloat($player->z);
		}
		if(!$this->dev && !isset($this->trypaintData['player'][$user])){
			if(!isset($this->leftCheck[$user]['tick']) or !isset($this->leftCheck[$user]['x']) or (abs($this->leftCheck[$user]['x'] - $x != 0 or $this->leftCheck[$user]['y'] - $y != 0 or $this->leftCheck[$user]['z'] - $z != 0) or $this->leftCheck[$user]['yaw'] - $player->yaw != 0 or $this->leftCheck[$user]['pitch'] - $player->pitch != 0)){
				$this->leftCheck[$user] = [
					'x'		 => $x,
					'y'		 => $y,
					'z'		 => $z,
					'yaw'	 => $player->yaw,
					'pitch'	 => $player->pitch,
					'tick'	 => 0,
				];
			}else{
				//$decisionTick = 900;//900 = 45sec
				$decisionTick = 3500;//何故かキックまでとても短いので
				$this->leftCheck[$user]['tick'] +=5;
				//Debug//
				//$remainingTick = $decisionTick - $this->leftCheck[$user]['tick'];
				//$color = ($remainingTick >= 400) ? "§a" : ($remainingTick >= 100 ? "§6" : "§c");
				//$player->sendPopup($color.sprintf("%.2F", $remainingTick / 20) ." sec");
				/////////
				if($this->leftCheck[$user]['tick'] >= $decisionTick){
					$this->leftCheck[$user]['tick'] = 0;
					$player->getLevel()->addParticle(new DestroyBlockParticle(new Vector3($player->x, $player->y + 0.7, $player->z), new Block(35, $this->team->getTeamColorBlock($this->team->getTeamOf($user)))));
					$message = $this->lang->translateString("battleLeft");
					$player->kick($message, false);
					return true;
				}
			}
		}
		$level = $player->getLevel();
		$id = $level->getBlockIdAt($x, $y, $z);
		$playerData = Account::getInstance()->getData($user);
		$weapon_num = $playerData->getNowWeapon();
		/*
		if($this->getSideWool($player, $player_bt)){
			$pk = new UpdateBlockPacket();
			$pk->records[] = [$x, $z, $y + 2, 106, 0, UpdateBlockPacket::FLAG_NONE];
			$player->dataPacket($pk);
			// echo "e";
		}
		*/
		//空中の場合は処理スキップ
		if($id == 0 && ($inv = $player->getInventory()) && ($inv->getItemInHand()->getID() === 351 || $inv->getItemInHand()->getID() === 0)) return true;
		switch ($id) {
			case 33:
				$yaw = $player->yaw;
				$rad = $yaw/180*M_PI;
				$xx = -sin($rad);
				$zz = cos($rad);
				$mot = new Vector3($xx*1.25, 0.8, $zz*1.25);
				$player->setMotion($mot);
				$color = $playerData->getColor();
				$pos = new Vector3($x, $y+1, $z);
				$level->addParticle(new DestroyBlockParticle($pos, Block::get(35, $color)));
				break;
			case 8:
			case 9:
				$this->OnDeath(null, $player, "death.attack.drown", null);
				break;
			
			case 10:
			case 11:
				$this->OnDeath(null, $player, "death.attack.lava", null);
				break;
		}

		if($id == 35){
			//試し塗りしてる場合用
			if(isset($this->trypaintData['player'][$user])){
				$block_color = $level->getBlockDataAt($x, $y, $z);
				$player_color = $this->trypaintData['player'][$user][2];
				if($block_color == $player_color){
					if($player->hasEffect(Effect::SLOWNESS)){
						$player->removeEffect(Effect::SLOWNESS);
					}
					if(($inv = $player->getInventory()) && ($inv->getItemInHand()->getID() === 351 || $inv->getItemInHand()->getID() === 0)){//イカスミ持ってたら
					//if(isset($this->Squid_Standby[$user])){
						$this->MoveSquid($player);
						//$playerData->stockInk(5);
						$playerData->stockInk(ceil(7 * Gadget::getCorrection($player, Gadget::INK_HEAL)));
						$this->sendInkAmount($player);
						return true;//処理を終わらせる
					}
				}
				$removeAllEffect = false;
				goto notWool;
			}


			$block_color = $level->getBlockDataAt($x, $y, $z);//まず羊毛の色
			$block_team = $this->team->getTeamNumByBlock($block_color);//チームの番号get
			$block_bt = isset($this->team->battleTeamNumber[$block_team]) ? $this->team->battleTeamNumber[$block_team] : false;
			/*
			//壁のぼりの遺産
			//$player->resetFallDistance();
			//$player->onGround = true;
			$player->setAllowFlight(true);

			//$player->move($x, $y + 2.1, $z);
			//$player->sendPosition = new Vector3($x, $y + 2.1, $z);

			$pk = new MoveEntityPacket();
			$pk->x = $x;
			$pk->y = $y + 2.1;
			$pk->z = $z;
			$pk->bodyYaw = $player->yaw;
			$pk->pitch = $player->pitch;
			$pk->yaw = $player->yaw;
			Server::broadcastPacket(Server::getInstance()->getOnlinePlayers(), $pk);

			echo $x." ".$z." ".($y + 2)." ";
			$pk = new SetEntityMotionPacket();
			$pk->entities[] = [0, $x, $y + 2, $z];
			$player->dataPacket($pk->setChannel(Network::CHANNEL_MOVEMENT));
			//echo $x." ".$z." ".($y + 2)." ";
			*/
			if($block_color){
				if($block_bt){
					//羊毛の色が違ったら(敵の色なら)
					if($block_bt != $player_bt){
						if($this->spawnedSquid($player)) $this->DespawnToSquid($player);//いかちゃんさよなら
						if(!$player->hasEffect(Effect::SLOWNESS)){
							$player->addEffect(Effect::getEffect(Effect::SLOWNESS)->setDuration(150000)->setAmplifier(1)->setVisible(false));//移動速度低下
						}
						$this->setSpeed($player, ($this->getSpeed($player, true) * 0.5 *Gadget::getCorrection($player, Gadget::SAFE_SHOES)));
						if($damage && (mt_rand(0, 10) == 0 || Gadget::getCorrection($player, Gadget::SPIKE)) && $player->getHealth() > 10){
							//$ev = new EntityDamageEvent($player, EntityDamageEvent::CAUSE_MAGIC, 0.2);
							//$player->attack($ev->getFinalDamage(), $ev);//魔法のダメージ
							$player->setHealth($player->getHealth()-1);
						}
						return true;//ここで処理終了(移動速度低下が消えるので)
					}else{
						//羊毛の色が同じだったら
						$playerData = Account::getInstance()->getData($user);
						//$playerData->stockInk(1);//インク自動回復
						if($player->hasEffect(Effect::SLOWNESS)){
							$player->removeEffect(Effect::SLOWNESS);
						}
						if(($inv = $player->getInventory()) && ($inv->getItemInHand()->getID() === 351 || $inv->getItemInHand()->getID() === 0) ){//イカスミ持ってたら
						//if(isset($this->Squid_Standby[$user])){
							$this->MoveSquid($player);
							//$playerData->stockInk(5);
							$playerData->stockInk(ceil(7 * Gadget::getCorrection($player, Gadget::INK_HEAL)));
							$this->sendInkAmount($player);
							return true;//処理を終わらせる
						}
					}
				}
			}
		}
		//羊毛の上に立ってない場合(上の二つに当てはまらない場合)
		notWool:
		if($player->hasEffect(Effect::SLOWNESS)) $player->removeEffect(Effect::SLOWNESS);
		//$this->w->applyEffect($player, $weapon_num, !$this->dev && $removeAllEffect);
		$this->w->applyEffect($player, $weapon_num, !$this->dev);
		if($this->spawnedSquid($player)) $this->DespawnToSquid($player);//いかちゃんさよなら
	}

	/**
	 * Playerの足元と目の前にWoolがあるかどうか (イカモードの壁のぼりに使用)
	 * @param  Player      $player
	 * @param  bool        $woolCheck    default = true
	 * @return int | false                              ジャンプする高さ
	 */
	public function getSideWool(Player $player, $woolCheck = true){
		$user = $player->getName();
		$x = floor($player->x) + 0.5;
		//$y = Math::floorFloat($player->y);
		$y = round($player->y);
		$z = floor($player->z) + 0.5;
		//$playerYaw = $player->yaw;
		$dir = [0 => 270, 1 => 360, 2 => 90, 3 => 180];
		$playerYaw = $dir[$player->getDirection()];
		$playerPitch = $player->pitch;

		//$velX = -1 * round(sin($playerYaw / 180 * M_PI));
		//$velZ = round(cos($playerYaw / 180 * M_PI));
		//$velX = round(-1 * sin($playerYaw / 180 * M_PI));
		//$velZ = round(cos($playerYaw / 180 * M_PI));
		$Yaw_rad = deg2rad($playerYaw);
		$velX = -1 * sin($Yaw_rad);
		$velZ = cos($Yaw_rad);
		$x = floor($x + $velX);
		$z = floor($z + $velZ);

		//$block = $player->getLevel()->getBlock(new Vector3($player->getX() + $velX, $player->getY(), $player->getZ() + $velZ));
		//echo $block->getID();
		//echo " ".$velX."_".$velZ." \n";
		//$block_team = $this->team->getTeamNumByBlock($block->getDamage());//チームの番号get
		$blockdata = $player->getLevel()->getBlockDataAt($x, $y, $z);
		if($woolCheck){
			if(($player_bt = $this->team->getBattleTeamOf($user))){
				$block_team = $this->team->getTeamNumByBlock($blockdata);//チームの番号get
				$block_bt = isset($this->team->battleTeamNumber[$block_team]) ? $this->team->battleTeamNumber[$block_team] : false;
				if($block_bt == $player_bt){
					for($plusy = 1; $plusy <= 15; $plusy++){
						if(($player->getLevel()->getBlockIdAt($x, $y + $plusy, $z) != 35) or
						//  !($blockdata = $player->getLevel()->getBlockDataAt($x, $y + $plusy, $z))) return $plusy;//ジャンプするブロックの高さを返す
						  !($blockdata = $player->getLevel()->getBlockDataAt($x, $y + $plusy, $z))) break;
						$block_team = $this->team->getTeamNumByBlock($blockdata);//チームの番号get
						$block_bt = isset($this->team->battleTeamNumber[$block_team]) ? $this->team->battleTeamNumber[$block_team] : false;
						//if($block_bt != $player_bt) return $plusy;
						if($block_bt != $player_bt) break;
					}
					//return 0;
					return $plusy > 1 ? $plusy : 0;
				}else{
					return false;
				}
			}elseif(isset($this->trypaintData['player'][$user])){
				$player_color = $this->trypaintData['player'][$user][2];
				$block_color = $blockdata;
				if($block_color == $player_color){
					for($plusy = 1; $plusy <= 15; $plusy++){
						if(($player->getLevel()->getBlockIdAt($x, $y + $plusy, $z) != 35) or
						//  !($block_color = $player->getLevel()->getBlockDataAt($x, $y + $plusy, $z))) return $plusy;//ジャンプするブロックの高さを返す
						  !($block_color = $player->getLevel()->getBlockDataAt($x, $y + $plusy, $z))) break;//ジャンプするブロックの高さを返す
						//if($block_color != $player_color) return $plusy;
						if($block_color != $player_color) break;
					}
					//return 0;
					return $plusy > 1 ? $plusy : 0;
				}
			}
		}else{
			for($plusy = 0; $plusy <= 15; $plusy++){
				if(($player->getLevel()->getBlockIdAt($x, $y + $plusy, $z) != 35)){
					break;//ジャンプするブロックの高さを返す
				}
			}
			//return 0;
			return $plusy > 1 ? $plusy : 0;
		}
		/*
		//もやさんのをさんこうにつくったやつ
		//使うかもしれないので保管
		$px = $player->getX();
		$py = $player->getY();
		$pz = $player->getZ();
		$playerYaw = $player->yaw;
		$playerPitch = $player->pitch;
		$velX = sin($playerYaw / 180 * M_PI) * cos(($playerPitch - 180) / 180 * M_PI);
		$velZ = -1 * cos($playerYaw / 180 * M_PI) * cos(($playerPitch - 180) / 180 * M_PI);
		$level = $player->getLevel();
		$b = $level->getBlock(new Vector3($velX + $px, $py - 1, $velZ + $pz));
		if($b->getID() == 35){
			//echo "ようもう";
			echo " ".$velX."_".$velZ." \n";
			$block_team = $this->team->getTeamNumByBlock($b->getDamage());//チームの番号get
			$block_bt = isset($this->team->battleTeamNumber[$block_team]) ? $this->team->battleTeamNumber[$block_team] : false;
			if($block_bt == $player_bt){
				echo "true\n";
				return true;
			}
		}
		*/
		return false;
	}

	public function setXpProgress($player, $exp){
		$pk = new UpdateAttributesPacket();
		$pk->entries[] = new InkTank($exp);	
		$pk->entityId = $player->getId();
		$player->dataPacket($pk);
	}

	/**
	 * 現在のインク量をプレイヤーに送信
	 * @param Player $player
	 */
	public function sendInkAmount($player){
		$user = $player->getName();
		//$team = $this->team->getBattleTeamOf($user);
		//if($team){
			$playerData = Account::getInstance()->getData($user);
			$ink = $playerData->getInk();
			$tank = $playerData->getInkTank();
			$exp = round($ink / $tank, 3);//ゲージの最大を1としたときの数値
			$this->setXpProgress($player, $exp);//タンク(バー)
			return true;
		//}
		return false;
	}

	/**
	 * インク不足のメッセージを送信
	 * @param Player $player
	 */
	public function Inkshortage($player){
		$player->sendPopup($this->lang->translateString("inkShortage"));
	}

	/**
	 * 試合開始前に実行される
	 * @param int $amount 1~100
	 */
	public function PlayersInkCharge($amount){
		$teams = $this->team->getBattleTeamMember();
		foreach($teams as $team => $members){
			foreach($members as $member => $status){
				if(($player = $this->getServer()->getPlayer($member)) instanceof Player){
					$this->setXpProgress($player, $amount / 100);
				}
			}
		}
	}

	/**
	 * プレイヤーリスポーン時に実行される
	 * @param Player $player
	 * @param int    $amount 1~100
	 */
	public function PlayerInkCharge(Player $player, $amount, $max){
		$user = $player->getName();
		if($this->team->getBattleTeamOf($user)){
			//$HP = ceil($player->getMaxHealth() * $amount / 100);
			//$player->setHealth($HP == 0 ? 1 : $HP);
			$HP = (($value = ceil($player->getMaxHealth() * $amount / $max)) % 2 == 0) ? $value : $value + 1;
			$player->setHealth($HP == 0 ? 2 : $HP);
			$this->setXpProgress($player, $amount / $max);
			if($amount >= $max){
				$player->sendPopup("§aFully charged");
				$player->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE, false);
				$player->sendData($player);
				$this->RemovescattersItem($player);
			}elseif(($first = $amount === 1.5) || $amount % 30 === 0){
				$player->sendPopup("§bCharging...");
			}
		}
	}

	public function InkChargeStart($sec = 5){
		$this->Task['Inkcharge'] = $this->getServer()->getScheduler()->scheduleRepeatingTask(new Inkcharge($this, $sec), 1);
	}

	public function InkChargeStop($fullCharge = true){
		if(isset($this->Task['Inkcharge'])){
			Server::getInstance()->getScheduler()->cancelTask($this->Task['Inkcharge']->getTaskId());
			unset($this->Task['Inkcharge']);
			if($fullCharge) $this->PlayersInkCharge(100);
		}
	}

	public function stopRespawnTask(){
		if(!isset($this->Task['Respawn'])) return true;
		foreach($this->Task['Respawn'] as $user => $task){
			Server::getInstance()->getScheduler()->cancelTask($task->getTaskId());
		}
		$this->Task['Respawn'] = [];
	}

	/**
	 * 満腹度とか経験値とかリセット
	 * @param Player  $player
	 * @param boolean $dash   ダッシュできるようにするかどうか
	 */
	public function ResetStatus($player, $dash = true){
		$this->setXpProgress($player, 0);

		$moveSpeed = $player->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED);
		$moveSpeed->setDefaultValue(Weapon::MOVEMENT_SPEED_DEFAULT_VALUE);
		$moveSpeed->setValue($moveSpeed->getDefaultValue());
		if($dash){
			$player->getAttributeMap()->getAttribute(Attribute::HUNGER)->setMaxValue(20);
			$player->setFood(20);
		}else{
			$player->setFood(6);
			$player->getAttributeMap()->getAttribute(Attribute::HUNGER)->setMaxValue(6);
		}
		$player->sendAttributes(true);
	}

	public function AllResetStatus(){
		foreach($this->getServer()->getOnlinePlayers() as $player){
			$this->ResetStatus($player);
		}
	}


	public function setSpeed($player, $speed){
		if($speed === true){
			//通常の移動速度に変える
			$moveSpeed = $player->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED);
			return $moveSpeed->setValue($moveSpeed->getDefaultValue());
		}
		return $player->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED)->setValue($speed);
	}

	public function getSpeed($player, $getDefault = false){
		return $getDefault ? $player->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED)->getDefaultValue() : $player->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED)->getValue();
	}


	//イカちゃん
	public function spawnedSquid($player){
		return isset($this->squids[$player->getId()]);
	}

	public function SpawnToSquid($player){
		$user = $player->getName();
		$this->squids[$player->getId()] = 1;//EIDは記録しなくていいため
		/*
		$eid = mt_rand(90001,99999);
		$pk = new AddEntityPacket();
		$pk->type = 17;
		$pk->metadata = [];
		$pk->x = $player->getX();
		$pk->y = $player->getY();
		$pk->z = $player->getZ();
		$pk->eid = $eid;
		$pk->yaw = $player->getYaw();
		$pk->pitch = $player->getPitch();
		Server::broadcastPacket(Server::getInstance()->getOnlinePlayers(), $pk);
		$this->squids[$player->getId()] = $eid;
		*/
		//エフェクト追加
		if(!$player->hasEffect(Effect::INVISIBILITY)){
			$player->addEffect(Effect::getEffect(Effect::SPEED)->setDuration(150000)->setAmplifier(2)->setVisible(false));//移動速度上昇
			//$s = $this->getSpeed($player) * 1.5;
			$s = Weapon::MOVEMENT_SPEED_DEFAULT_VALUE * 1.5 *Gadget::getCorrection($player, Gadget::IKA_SPEED);
			$this->setSpeed($player, $s);
			$player->addEffect(Effect::getEffect(Effect::REGENERATION)->setDuration(150000)->setAmplifier(3)->setVisible(false));//再生能力
			$player->addEffect(Effect::getEffect(Effect::INVISIBILITY)->setDuration(150000)->setAmplifier(0)->setVisible(false));//透明化
		}
		$sen = $this->w->getSensor($player);
		if($sen){
			$this->w->setSensor($sen, $player, true);
		}

	}

	public function DespawnToSquid($player){
		if(isset($this->squids[$player->getId()])){
			$user = $player->getName();
			//↓パーティクルにはいらないので
			/*
				$pk = new RemoveEntityPacket();
				$pk->eid = $this->squids[$player->getId()];
				Server::broadcastPacket(Server::getInstance()->getOnlinePlayers(), $pk);
			*/
			unset($this->squids[$player->getId()]);
			//エフェクト削除
			if($player->hasEffect(Effect::INVISIBILITY)){
				$player->removeEffect(Effect::SPEED);
				$this->setSpeed($player, true);
				$player->removeEffect(Effect::REGENERATION);
				$player->removeEffect(Effect::INVISIBILITY);
			}
			$sen = $this->w->getSensor($player);
			if($sen){
				$this->w->setSensor($sen, $player, true);
			}
		}
	}

	public function MoveSquid($player){
		$user = $player->getName();
		if(isset($this->squids[$player->getId()])){
			/*
				$eid = $this->squids[$player->getId()];
				$pk = new MoveEntityPacket();
				$pk->entities = [[$eid, $player->getX(), $player->getY(), $player->getZ(), $player->getYaw(), $player->getYaw(), $player->getPitch()]];
				Server::broadcastPacket(Server::getInstance()->getOnlinePlayers(), $pk);
			*/
			/*
				$r = 0;
				$g = 0;
				$b = 0;
				$InkParticle = new DustParticle($player,$r, $g, $b);
			*/
			//↓インク
			//$InkParticle = new InkParticle($player);
			if(isset($this->trypaintData['player'][$user])){
				$color = $this->trypaintData['player'][$user][2];
			}else{
				$team_num = $this->team->getTeamOf($player->getName());
				$color = $this->team->getTeamColorBlock($team_num);
			}
			$pos = new Vector3($player->x, $player->y+0.5, $player->z);
			//$InkParticle = new DestroyBlockParticle($pos, new Block(35, $color));
			$playerData = Account::getInstance()->getData($user);
			$level = $player->getLevel();
			if(!$playerData->getRate() || $this->dev == 2 || Gadget::getCorrection($player, Gadget::BOMB_MASTER)){
				for($i = 1; $i <= 8; $i++){
					$pk = new LevelEventPacket;
					//$pk->evid = LevelEventPacket::EVENT_PARTICLE_DESTROY;
					$pk->evid = LevelEventPacket::EVENT_ADD_PARTICLE_MASK | Particle::TYPE_TERRAIN & 0xFFF;
					$pk->x = $player->x;
					$pk->y = $player->y + 0.425;
					$pk->z = $player->z;
					//$pk->data = 35 + ($color << 12);
					$pk->data = (($color << 8) | 35);
					$level->addChunkPacket($pk->x >> 4, $pk->z >> 4, $pk);
				}
			}
			if($this->w->getSensor($player)){
				$part = $level->addParticle(new CriticalParticle($pos, 3));
			}
			return true;
		}else{
			//初回だったらスポーンさせる
			$this->SpawnToSquid($player);
			return false;
		}
	}

	public function DespawnAllSquid(){
		/*
		foreach($this->squids as $pid => $eid){
			$pk = new RemoveEntityPacket();
			$pk->eid = $eid;
			Server::broadcastPacket(Server::getInstance()->getOnlinePlayers(), $pk);
			unset($this->squids[$pid]);
		}
		*/
		$this->squids = [];
	}

/*\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
	羊毛関係
\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\*/

	//タップしたブロックがチームの色のものかどうか
	public function isTeamWoolColor($level, $x, $y, $z, $user){
		if($this->game == 10){
			if($level->getBlockIdAt($x, $y, $z) == 35){
				$target_meta = $level->getBlockDataAt($x, $y, $z);
				if($target_meta){
					$team_num = $this->team->getTeamNumByBlock($target_meta);
					$b_bteam_num = $this->getBattleTeamFromTeamNum($team_num);
					$now_bteam_num = $this->team->getBattleTeamOf($user);
					return $b_bteam_num == $now_bteam_num;
				}
			}
		}
		return false;
	}

	/**
	 * 指定した座標のブロックが羊毛かどうか(試し塗りのフィールド/している人には使えない)
	 * @param  int     $x
	 * @param  int     $y
	 * @param  int     $z
	 * @return boolean
	 */
	public function isWool($x, $y, $z){
		return isset($this->woolsBlockArray[$x][$y][$z]);
	}

/*\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
	グループ関係
\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\*/

	/**
	 * プレイヤーの名前を更新
	 * @param  Player $player
	 * @return
	 */
	public function changeName($player){
		//ネームタグとかの変更
		//順番入れ替えるな
		$user   = $player->getName();
		$team   = $this->team->getTeamOf($user);
		$signal = $this->s->hasStar($user);
		$playerData = Account::getInstance()->getData($user);
		$rank   = $this->s->getRank($user, $playerData->getNowWeapon());
		$warn   = ($playerData->getPanish() == "warn") ? base64_decode("4pqg") : "";
		$entry_color = ($this->entry->isEntry($user)) ? "§f" : "§7";
		$team_color = $this->team->getTeamColor($team);
		$mute_p = isset($this->mute_personal[$user])? "§f[§1×§f]" : "";
		$newname = "§f" . $warn . $signal . $entry_color . $team_color . $user. "§f" . $rank . "§f". $mute_p ."§f";

		$player->setNameTag($newname);
		$player->setDisplayName($newname);
	}

	/**
	 * ユーザーのステータスを取得
	 * @param  string $user
	 * @param  bool   $others  他人のステータスかどうか
	 * @return string
	 */
	public function getAccountStatus($user, $others = false){
		$user = strtolower($user);
		$data = $this->a->getData($user);
		$player = Server::getInstance()->getPlayer($user);
		if($others){
			return $this->lang->translateString("accountStatus.message.others", [$user, $data->getPoint(), $this->w->getweaponName($data->getNowWeapon()), $data->getCounter(), $data->getWin(), $data->getCheep()]);
		}else{
			$weapons = "";
			$weapon_cnt = count($data->getWeapons());
			$nowWeapon = $data->getNowWeapon();
			$gadgets = Gadget::getGadgetsData($player);
			$g1_name = $this->lang->translateString(Gadget::getGadgetName($gadgets[0]));
			$g2_name = $this->lang->translateString(Gadget::getGadgetName($gadgets[1]));
			$g3_name = $this->lang->translateString(Gadget::getGadgetName($gadgets[2]));
			$weapons .= "§e".$this->w->getweaponName($nowWeapon)." ".$data->getNowWeaponLevel()."Lv(".$data->getNowWeaponExp()."EXP)\n"."§aガジェット1：§e".$g1_name."\n"."§aガジェット2：§e".$g2_name."\n"."§aガジェット3：§e".$g3_name."\n";
/*			foreach($data->getWeapons() as $wn => $d){
				$now = $nowWeapon == $wn ? "§6E§f " : "  ";
				$weapons .= "  ".$now."§e".$this->w->getweaponName($wn)." ".$d[0]."Lv(".$d[1]."EXP)".(($cnt % 2 === 0 || $weapon_cnt === $cnt) ? "\n" : "  ");
				$cnt++;
			}*/
			return $this->lang->translateString("accountStatus.message", [$user, $data->getPoint(), $weapons, $data->getCounter(), $data->getWin(), $data->getAreaCounter(), $data->getAreaWin()]);
		}
	}

	public function addAreaParticle($tick){
		$mapno = $this->field;
		$level = $this->getLevelByBattleField($mapno);
		$f = $this->getBattleField($mapno);
		$areapos = $f['area'];
		foreach($areapos as $area_num => $pos){
			$color = $this->team->getTeamColorRGB($this->area['area'][$area_num]);
			$sx = $pos[0][0];
 			$sy = $pos[0][1]+1;
			$sz = $pos[0][2];
			$ex = $pos[1][0]+1;
			$ey = $pos[1][1]+1.5;
			$ez = $pos[1][2]+1;
			$ox = ($sx+$ex)/2;
			$oz = ($sz+$ez)/2;
			$oy = $ey+6;

			for($y = $oy; $y <= $oy+3; $y ++){
				$pos = new Vector3($ox , $y, $oz);
				$particle = new DustParticle($pos, $color[0], $color[1], $color[2]);
				$level->addParticle($particle);
			}

			for($x = $sx; $x <= $ex; $x += 0.5){
				for($y = $sy; $y <= $ey; $y++){
					$pos = new Vector3($x , $y+0.5, $sz);
					$particle = new DustParticle($pos, $color[0], $color[1], $color[2]);
					$level->addParticle($particle);
					$pos_r = new Vector3($x , $y+0.5, $ez);
					$particle_r = new DustParticle($pos_r, $color[0], $color[1], $color[2]);
					$level->addParticle($particle_r);
				}
			}

			for($z = $sz; $z <= $ez; $z += 0.5){
				for($y = $sy; $y <= $ey; $y++){
					$pos = new Vector3($sx , $y+0.5, $z);
					$particle = new DustParticle($pos, $color[0], $color[1], $color[2]);
					$level->addParticle($particle);
					$pos_r = new Vector3($ex , $y+0.5, $z);
					$particle_r = new DustParticle($pos_r, $color[0], $color[1], $color[2]);
					$level->addParticle($particle_r);
				}
			}
		}

	}

	public function addTeamColorParticle($tick){
		$mapno = $this->field;
		$level = $this->getLevelByBattleField($mapno);
		foreach($this->team->battleTeamNumber as $team_num => $battle_num){
			$f = $this->getBattleField($mapno);
			$color = $this->team->getTeamColorRGB($team_num);
			$p = $f['start'][$battle_num];
			$m = isset($f['spawn-radius']) ? $f['spawn-radius'] : 1.5;
			$density = 32;//一周あたりのパーティクル数
			$round    = $tick * (360 / $density);
			$y = $tick % 40 / 15;
			$sin = -$m * sin(deg2rad($round));
			$cos =  $m * cos(deg2rad($round));
			$pos = new Vector3($p[0] + $sin, $p[1] + $y, $p[2] + $cos);
			$particle = new DustParticle($pos, $color[0], $color[1], $color[2]);
			$level->addParticle($particle);

			$pos_R = new Vector3($p[0] - $sin, $p[1] + $y, $p[2] - $cos);
			$particle_R = new DustParticle($pos_R, $color[0], $color[1], $color[2]);
			$level->addParticle($particle_R);
		}
	}

/*\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
	テキストぱーてぃくるー
\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\*/

	public function randomBroad($r = false){
		$cnt = count($this->randomchat) - 1;
		if(!$r){
			$r = rand(1, $cnt);
		}
		$out = "§b≫ ".$this->randomchat[$r];
		$this->getServer()->broadcastMessage($out);
		return true;
	}

	/**
	 * まとめてテキストパーティクルを更新する
	 * @param boolean $update                 テキスト更新の場合true,サーバーに入ったときはfalse
	 * @param Player   $player default = null 特定のプレイヤーのみに送信する
	 */
	public function FloatText($update = false, $player = null){
		$players = ($player == null) ? null : [$player];
		$text_num = ($update) ? [0, 1, 2, 6] : range(0, 7);
		$this->setFloatText($text_num, $players);
	}

	/**
	 * 特定のテキストパーティクルを更新(addParticle)する
	 * @param int      $text_num
	 * @param Player[] $players  default = null プレイヤーを指定することで特定のプレイヤーのみに反映させる
	 */
	public function setFloatText($text_num, $players = null){
		$level = $this->getLevelByBattleField(0);
		foreach($text_num as $id){
			switch($id){
				case 0:
					$title = $this->lang->translateString("floatText.0.title");
					$text = $this->entry->getEntryNum()."人";
					if(empty($this->textParticle[0])){
						$x = 532.5;
						$y = 12.5;
						$z = -134.5;
						$pos = new Vector3($x, $y, $z);
						$textParticle = new FloatingText($this, $pos, $text, $title);
						$this->textParticle[0] = $textParticle;
						$this->textParticle[0]->addText($players);
					}else{
						$this->textParticle[0]->setTitle($title);
						$this->textParticle[0]->setText($text);
						$this->textParticle[0]->addText($players);
					}
					break;
				case 1:
					$title = ((!$this->team->canJoin) ? $this->lang->translateString("floatText.1.title.restricted") : $this->lang->translateString("floatText.1.title.available"))."§f";
					$text = $this->lang->translateString("floatText.1.text");
					if(empty($this->textParticle[1])){
						$x = 527.25;
						$y = 12.8;
						$z = -135.5;
						$pos = new Vector3($x, $y, $z);
						$textParticle = new FloatingText($this, $pos, $text, $title);
						$this->textParticle[1] = $textParticle;
						$this->textParticle[1]->addText($players);
					}else{
						$this->textParticle[1]->setTitle($title);
						$this->textParticle[1]->setText($text);
						$this->textParticle[1]->addText($players);
					}
					break;
				case 2:
					$title = ((!$this->team->canJoin) ? $this->lang->translateString("floatText.1.title.restricted") : $this->lang->translateString("floatText.1.title.available"))."§f";
					$text = $this->lang->translateString("floatText.1.text");
					if(empty($this->textParticle[2])){
						$x = 537.75;
						$y = 12.8;
						$z = -135.5;
						$pos = new Vector3($x, $y, $z);
						$textParticle = new FloatingText($this, $pos, $text, $title);
						$this->textParticle[2] = $textParticle;
						$this->textParticle[2]->addText($players);
					}else{
						$this->textParticle[2]->setTitle($title);
						$this->textParticle[2]->setText($text);
						$this->textParticle[2]->addText($players);
					}
					break;
				case 3:
					//ルール
					$title = $this->lang->translateString("floatText.3.title");
					$text = $this->lang->translateString("floatText.3.text");
					if(empty($this->textParticle[3])){
						$x = 521.5;
						$y = 10.5;
						$z = -102.5;
						$pos = new Vector3($x, $y, $z);
						$textParticle = new FloatingText($this, $pos, $text, $title);
						$this->textParticle[3] = $textParticle;
						$this->textParticle[3]->addText($players);
					}else{
						$this->textParticle[3]->setTitle($title);
						$this->textParticle[3]->setText($text);
						$this->textParticle[3]->addText($players);
					}
					break;
				case 4:
					$title = "§l§b".$this->s->getSName();
					$text = "";
					if(empty($this->textParticle[4])){
						$x = 532.5;
						$y = 8;
						$z = -108.5;
						$pos = new Vector3($x, $y, $z);
						$textParticle = new FloatingText($this, $pos, $text, $title);
						if(!$this->s->getSName()) $textParticle->setInvisible(true);
						$this->textParticle[4] = $textParticle;
						$this->textParticle[4]->addText($players);
					}else{
						$this->textParticle[4]->setTitle($title);
						$this->textParticle[4]->setInvisible(!$this->s->getSName());
						$this->textParticle[4]->addText($players);
					}
					break;
				case 5:
					//ブキ購入方法説明
					$title = $this->lang->translateString("floatText.5.title");
					$text = $this->lang->translateString("floatText.5.text");
					if(empty($this->textParticle[5])){
						$x = 532;
						$y = 10;
						$z = -146;
						$pos = new Vector3($x, $y, $z);
						$textParticle = new FloatingText($this, $pos, $text, $title);
						$this->textParticle[5] = $textParticle;
						$this->textParticle[5]->addText($players);
					}else{
						$this->textParticle[5]->setTitle($title);
						$this->textParticle[5]->setText($text);
						$this->textParticle[5]->addText($players);
					}
					break;
				case 6:
					//マップ
					$title = $this->lang->translateString("floatText.6.title");
					$text = $this->lang->translateString("floatText.6.text");
					$now = date("G");
					$stages = array_slice($this->s->getStagedata(), $now, 4);
					if($stages !== null){
						foreach($stages as $data){
							$stage_names = [];
							foreach($data['s'] as $field_num => $percentage){
								$stage_names[] = isset($this->battle_field[$field_num]['name']) ? $this->battle_field[$field_num]['name'] : "[NO_NAME]";
							}
							$timecolor = ($data['h'][0] <= $now && $now <= $data['h'][1]) ? "§b" : "";
							$text .= "\n".$timecolor.$data['h'][0].":00 - ".$data['h'][1].":59§f | §a".implode("§f".$this->lang->translateString("comma")." §a", $stage_names)."§f";
						}
					}else{
						$text = "now loading...";
					}
					if(empty($this->textParticle[6])){
						$x = 540.5;
						$y = 9;
						$z = -121.5;
						$pos = new Vector3($x, $y, $z);
						$textParticle = new FloatingText($this, $pos, $text, $title);
						$this->textParticle[6] = $textParticle;
						$this->textParticle[6]->addText($players);
					}else{
						$this->textParticle[6]->setTitle($title);
						$this->textParticle[6]->setText($text);
						$this->textParticle[6]->addText($players);
					}	
					break;
					/*
				case 7:
					$title = "§l§6Weapons SHOP";
					$text = "";
					if(empty($this->textParticle[7])){
						$x = 532;
						$y = 10;
						$z = -146;
						$pos = new Vector3($x, $y, $z);
						$textParticle = new FloatingText($this, $pos, $text, $title);
						$this->textParticle[7] = $textParticle;
						$this->textParticle[7]->addText($players);
					}else{
						$this->textParticle[7]->addText($players);
					}
					break;
					*/
			}
		}
	}

	public function hideEnemysNametag($player,$hide = true){
		$teams = $this->team->getBattleTeamMember();
		if($teams === false) return;
		$pTeam = $this->team->getBattleTeamOf($player->getName());
		if($pTeam === 0) return;
		foreach($teams as $team => $members){
			foreach($members as $mem => $number){
				if(($member = $this->getServer()->getPlayer($mem)) instanceof Player){
					$mTeam = $this->team->getBattleTeamOf($member->getName());
					if($mTeam != $pTeam){
						$pk = new SetEntityDataPacket();
						$pk->eid = $member->getId();
						$flags = 0;
						if($hide){
							@$flags |= 0 << Entity::DATA_FLAG_INVISIBLE;
							@$flags |= 0 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG;
							@$flags |= 0 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG;
							$pk->metadata = [
								Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
								Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, ""],
								Entity::DATA_LEAD_HOLDER_EID => [Entity::DATA_TYPE_LONG,-1]
							];
						}else{
							@$flags |= 0 << Entity::DATA_FLAG_INVISIBLE;
							@$flags |= 1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG;
							@$flags |= 1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG;
							$pk->metadata = [
								Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
								Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, ""],
								Entity::DATA_LEAD_HOLDER_EID => [Entity::DATA_TYPE_LONG,-1]
							];
						}
						$player->dataPacket($pk);
					}
				}
			}
		}
	}

	public function allHideEnemysNametag($hide = true){
		$teams = $this->team->getBattleTeamMember();
		if($teams === false) return;
		foreach($teams as $team => $members){
			foreach($members as $member => $number){
				if(($player = $this->getServer()->getPlayer($member)) instanceof Player){
					$this->hideEnemysNametag($player,$hide);
				}
			}
		}
	}

/*\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
	スケジュール内使用
\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\*/

	public function isinPrepareBattle(){
		switch($this->game){
			case 6:
			case 7:
			case 8:
			case 9:
			case 10:
			case 11:
				return true;
			break;
		}
		return false;
	}

	/**
	 * フィールドにテレポートされる段階かどうか
	 * @param  boolean       $checkpos
	 * @return boolean | int
	 */
	public function checkFieldteleport($checkpos = false){
		if($checkpos){
			switch($this->game){
				case 6:
				case 7:
				case 8:
				case 9:
					return 2;
				case 10:
				case 11:
				case 12:
				case 13:
					return 1;
				default: 
					return false;
			}
		}else{
			switch($this->game){
				case 6:
				case 7:
				case 8:
				case 9:
				case 10:
				case 11:
				case 12:
				case 13:
					return true;
				default:
					return false;
			}
		}
	}

	/**
	 * ブキを変更できる段階かどうか(フィールド移動後～リザルト送信前ではないか)
	 * @return boolean
	 */
	public function canChangeWeapon(){
		return !($this->game >= 6 && $this->game <= 16);
	}

	/**
	 * ゲームを開始できる人数に達しているかどうか
	 * @return bool
	 */
	public function isReady(){
		if(($this->game == 1 || $this->error) && !$this->gamestop){
			$next = $this->game + 1;
			$all_cnt = 0;
			foreach($this->team->getTeams() as $n => $d){
				if($n === 0) continue;
				$cnt = !empty($this->team->member[$n]) ? count($this->team->member[$n]) : 0;
				$all_cnt += $cnt;
			}
			$max = $this->team->getTeamMaxPlayer();
			if($all_cnt >= $max){
				$this->TimeTable();
				return true;
			}elseif($all_cnt >= $max / 2){
				if(!isset($this->Task['game'][$next])){
					switch($next){
						case 2:
						case 5:
							$sec = 30;
							break;
						default:
							$sec = 20;
					}
					$this->Task['game'][$next] = $this->getServer()->getScheduler()->scheduleDelayedTask(new TimeScheduler($this), 20 * $sec);
					//MainLogger::getLogger()->debug("Start the game after ".$sec." seconds");
				}
			}
		}
		return false;
	}

	//フィールドのデータ取得
	public function getBattleField($field){
		return $this->battle_field[$field] ?? null;
	}



	//フィールド番号与えるとレベルオブジェクト返す
	public function getLevelByBattleField($field){
		$name = $this->battle_field[$field]['level'];
		return Server::getInstance()->getLevelByName($name);
	}

	/*public function TpTeamLobby(){
		$this->PlayersMoveCancel(false);
	}*/

	/**
	 * フィールドへテレポート
	 * @param boolean $animation テレポートのアニメーションをするかどうか
	 */
	public function TpTeamBattleField($animation = true){
		$mapno = $this->field;
		$teams = $this->team->getBattleTeamMember();
		if($teams){
			$level = $this->getLevelByBattleField($mapno);
			$p = $this->getBattleField($mapno)['start'];
			if(!isset($this->getBattleField($mapno)['respawn-view']) && $animation) $animation = false;
			foreach($teams as $team => $members) {
				foreach($members as $member => $number){
					if(($player = $this->getServer()->getPlayer($member)) instanceof Player){
						if(isset($this->trypaintData['player'][$player->getName()])){
							$this->TryPaint($player, false, false);
						}
						$player->setGamemode(Player::SURVIVAL);
						if(!$animation){
							$plus_pos = (isset($this->tweakPosition[$number])) ? $this->tweakPosition[$number] : [0, 0];
							$zinti = new Location($p[$team][0] + $plus_pos[0], $p[$team][1], $p[$team][2] + $plus_pos[1], $p[$team][3], $p[$team][4]);
							$player->teleport($zinti, $p[$team][3], $p[$team][4]);
						}
						$player->setHealth(20);
						$this->ResetStatus($player, false);
						$player->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE, true);
						$player->sendData($player);
						$player->extinguish();
						$this->seat->stand($player);
						$this->itemselect->remove($player, false);
						//$this->itemCase->remove($player);
						$this->shop->clear($player);
					}
				}
			}
			if($animation){
				$blockdata = [];
				foreach($this->team->battleTeamNumber as $team_num => $battleteam_num){
					$blockdata[$battleteam_num] = $this->team->getTeamColorBlock($team_num);
				}
				$animationData = $this->getAnimationData();
				$this->TPanimation = new TPAnimation($this, $animationData[0], $animationData[1], $blockdata, $animationData[2]);
			}
			return true;
		}
		return false;
	}

	public function TPanimationEnd(){
		if($this->TPanimation !== null){
			$this->TPanimation->Close();
			$this->TPanimation = null;
		}
	}

	public function getAnimationData(){
		$posData = [];
		$playerData = [];
		$weaponData = [];
		$this->getBattleField($this->field);
		$field = $this->getBattleField($this->field);

		$fieldpos = $this->getBattleField($this->field)['start'];
		foreach($this->team->battleTeamMember as $t => $d){
			foreach($d as $user => $h){
				$weapon_num = $this->a->getData($user)->getNowWeapon();
				$weaponData[$user] = $this->w->getWeaponItemId($weapon_num);
				$playerData[$t][] = $user;
			}
			$posData[$t][0] = [$field['respawn-view'][$t][0], $field['respawn-view'][$t][1], $field['respawn-view'][$t][2], $field['respawn-view'][$t][3], 0];
			for($i = 1; $i <= 4; $i++){
				$key = $i - 1;
				$tpos = $this->tweakPosition[$key];
				$posData[$t][$i] = [$fieldpos[$t][0] + $tpos[0], $fieldpos[$t][1], $fieldpos[$t][2] + $tpos[1]];
			}
		}
		return [$playerData, $posData, $weaponData];
	}

	/**
	 * 試合メンバーのプレイヤーを動ける or 動けないように
	 * @param bool $value 動けないようにするかどうか
	 */
	public function PlayersMoveCancel($value){
		$teams = $this->team->getBattleTeamMember();
		if($teams){
			foreach($teams as $team => $members){
				foreach($members as $member => $number){
					if(($player = $this->getServer()->getPlayer($member)) instanceof Player){
						$player->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE, $value);
						$player->sendData($player);
					}
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * プレイヤーが動けない状態かどうか
	 * @param  Player  $player
	 * @return boolean
	 */
	public function canPlayerMoveCancel($player){
		return $player->getDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE);
	}

	/**
	 * 試合するメンバー全員にブキを配布
	 * @param  int $giveType
	 */
	public function giveWeaponForBattle($giveType = 0){
		$teams = $this->team->getBattleTeamMember();
		if($teams){
			foreach ($teams as $team => $members) {
				foreach ($members as $member => $status){
					if(($player = $this->getServer()->getPlayer($member)) instanceof Player){
						$player->getInventory()->clearAll();
						$this->giveWeapon($player, $giveType);
					}
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * ブキを配布
	 * @param  Player  $player
	 * @param  int     $giveType default = 0 0 = プレイヤーが装備中のブキのみ配布    1 = プレイヤーが購入済みのブキをすべて配布
	 *                                       2 = 販売中のブキをすべて配布(確タイプ配布用アイテムに変更) 3 = 未実装などを含むすべてのブキ
	 */
	public function giveWeapon($player, $giveType = 0){
		$user = $player->getName();
		$inventory = $player->getInventory();
		switch($giveType){
			case -1:
			//PvE
				//$player->getInventory()->clearAll();
				//$book = Item::get(340, 0);
				//$inventory->addItem($book);
				$inventory->setItem(0, Item::get(0));
				$shooter = Item::get(291, 0);
				$shooter->setCustomName("とぁーんシューター\n§aTank : 500");
				$inventory->setItem(1, $shooter);
				$slosher = Item::get(325, 0);
				$slosher->setCustomName("とぁーんスロッシャー\n§aTank : 500");
				$inventory->setItem(2, $slosher);
				$splash = Item::get(264, 0);
				$splash->setCustomName("スプラッシュボム\n§a消費 : 350");
				$inventory->setItem(3, $splash);
				$quick = Item::get(378, 0);
				$quick->setCustomName("クイックボム\n§a消費 : 200");
				$inventory->setItem(4, $quick);
			break;
			case 4:
			case 5:
			case 6:
			case 7:
				$max_num = $this->w->getWeaponAmount();
				$playerData = $this->a->getData($user);
				$item_ink = Item::get(351);
				$inventory->setItem(0, $item_ink);
				$weapons = $playerData->getWeapons();
				for($weapon = 1; $weapon <= $max_num; $weapon+=1){
					if($this->w->canTryPaint($weapon) && $this->w->canSellWeapons($weapon)){
						switch($this->w->weaponType($weapon)){
							case Weapon::TYPE_ROLLER:
								if($giveType != 4) continue 2;
							break;

							case Weapon::TYPE_SHOOTER:
								if($giveType !== 5) continue 2;
							break;

							case Weapon::TYPE_CHARGER:
							case Weapon::TYPE_SPLATLING:
								if($giveType !== 6) continue 2;
							break;

							case Weapon::TYPE_SLOSHER:
								if($giveType !== 7) continue 2;
							break;
						}
						$id = $this->w->getWeaponItemId($weapon);
						$weapon_name = $this->w->getweaponName($weapon);
						
						$weapon_data = $this->w->getWeaponData($weapon);
						$weapon_level = $weapons[$weapon][0] ?? 0;
						$weapon_exp = $weapons[$weapon][1] ?? 0;
						$rate = 0.002;
						$max_lv = 50;
						//$plus_tank = $weapon_level >= $max_lv ? $max_lv * $weapon_data[3] * $rate : $weapon_level * $weapon_data[3] * $rate;
						//$plus_tank = $plus_tank < 10 ? 0 : floor($plus_tank / 10) * 10;//10の位を切り下げる
						$plus_tank = 0;
						$plustank = ($plus_tank) > 0 ? " §a+{$plus_tank}§f" : "";
						$weapon_tank = $weapon_data[3].$plustank;

						/*$tag = new CompoundTag("", []);
						$tag->display = new CompoundTag("display", [
							"Name" => new StringTag("Name", $weapon_name."\n§r§aLevel:§f {$weapon_level}\n§aExp:§f {$weapon_exp}\n§aTank:§f {$weapon_tank}")
						]);*/

						$item_weapon = Item::get($id[0], $id[1], 1);
						$item_weapon->setCustomName($weapon_name."\n§r§aLevel:§f {$weapon_level}\n§aExp:§f {$weapon_exp}\n§aTank:§f {$weapon_tank}");
						$inventory->addItem($item_weapon);
						//$inventory->setItem($weapon, $item_weapon);
						//$inventory->setHotbarSlotIndex($weapon, $weapon);
						//$this->sendInkAmount($player, true);
					}
				}

				$sub_max_num = $this->w->getSubWeaponAmount();
				for($subweapon = 1; $subweapon <= $sub_max_num; $subweapon++){
					$subweap_name = $this->w->getSubWeaponName($subweapon);
					$subweap_data = $this->w->getSubWeaponDataFromWeapon($subweapon);
					$sub_id = $this->w->getSubWeaponItemId($subweapon);
					/*$tag = new CompoundTag("", []);
					$tag->display = new CompoundTag("display", [
						"Name" => new StringTag("Name", $subweap_name)
					]);*/
					$item_sub = Item::get($sub_id[0], $sub_id[1], 1);
					$item_sub->setCustomName($subweap_name);
					$inventory->addItem($item_sub);
				}
				//矢がないと弓を放つことができないので
				$inventory->addItem(Item::get(262, 0, 1));
			case 2:
				$dye1 = Item::get(351, 1);
				$dye1->setCustomName("ローラータイプのブキをテスト");
				$inventory->addItem($dye1);
				$dye2 = Item::get(351, 2);
				$dye2->setCustomName("シュータータイプのブキをテスト");
				$inventory->addItem($dye2);
				$dye3 = Item::get(351, 3);
				$dye3->setCustomName("スピナー・チャージャータイプのブキをテスト");
				$inventory->addItem($dye3);
				$dye4 = Item::get(351, 4);
				$dye4->setCustomName("スロッシャータイプのブキをテスト");
				$inventory->addItem($dye4);
			break;
			case 3:
			case 1:
				$item_ink = Item::get(351);
				$inventory->setItem(0, $item_ink);
				$max_num = $this->w->getWeaponAmount();
				$playerData = $this->a->getData($user);
				$weapons = $playerData->getWeapons();
				for($weapon = 1; $weapon <= $max_num; $weapon+=1){
					if($this->w->canTryPaint($weapon)){
						switch($giveType){
							case 2:
								if(!$this->w->canSellWeapons($weapon)){
									continue 2;
								}
								break;
							case 1:
								if(!isset($weapons[$weapon])){
									continue 2;
								}
								break;
						}
						$id = $this->w->getWeaponItemId($weapon);
						$weapon_name = $this->w->getweaponName($weapon);
						
						$weapon_data = $this->w->getWeaponData($weapon);
						$weapon_level = $weapons[$weapon][0] ?? 0;
						$weapon_exp = $weapons[$weapon][1] ?? 0;
						$rate = 0.002;
						$max_lv = 50;
						//$plus_tank = $weapon_level >= $max_lv ? $max_lv * $weapon_data[3] * $rate : $weapon_level * $weapon_data[3] * $rate;
						//$plus_tank = $plus_tank < 10 ? 0 : floor($plus_tank / 10) * 10;//10の位を切り下げる
						$plus_tank = 0;
						$plustank = ($plus_tank) > 0 ? " §a+{$plus_tank}§f" : "";
						$weapon_tank = $weapon_data[3].$plustank;

						/*$tag = new CompoundTag("", []);
						$tag->display = new CompoundTag("display", [
							"Name" => new StringTag("Name", $weapon_name."\n§r§aLevel:§f {$weapon_level}\n§aExp:§f {$weapon_exp}\n§aTank:§f {$weapon_tank}")
						]);*/

						$item_weapon = Item::get($id[0], $id[1], 1);
						$item_weapon->setCustomName($weapon_name."\n§r§aLevel:§f {$weapon_level}\n§aExp:§f {$weapon_exp}\n§aTank:§f {$weapon_tank}");
						$inventory->addItem($item_weapon);
						//$inventory->setItem($weapon, $item_weapon);
						//$inventory->setHotbarSlotIndex($weapon, $weapon);
						//$this->sendInkAmount($player, true);
					}
				}

				$sub_max_num = $this->w->getSubWeaponAmount();
				for($subweapon = 1; $subweapon <= $sub_max_num; $subweapon++){
					$subweap_name = $this->w->getSubWeaponName($subweapon);
					$subweap_data = $this->w->getSubWeaponDataFromWeapon($subweapon);
					$sub_id = $this->w->getSubWeaponItemId($subweapon);
					/*$tag = new CompoundTag("", []);
					$tag->display = new CompoundTag("display", [
						"Name" => new StringTag("Name", $subweap_name)
					]);*/
					$item_sub = Item::get($sub_id[0], $sub_id[1], 1);
					$item_sub->setCustomName($subweap_name);
					$inventory->addItem($item_sub);
				}
				//矢がないと弓を放つことができないので
				$inventory->addItem(Item::get(262, 0, 1));
				break;
			default:
				$playerData = $this->a->getData($user);
				$weapon = $playerData->getNowWeapon($user);
				$id = $this->w->getWeaponItemId($weapon);
				$weapon_name = $this->w->getweaponName($weapon);
				$weapon_level = $playerData->getNowWeaponLevel();
				$weapon_exp = $playerData->getNowWeaponExp();
				$plustank = ($plus = $playerData->getPlusTank()) > 0 ? " §a+{$plus}§f" : "";
				$weapon_tank = $this->w->getWeaponData($weapon)[3].$plustank;
				//$item_ink = Item::get(351);
				$item_ink = Item::get(0);

				/*$tag = new CompoundTag("", []);
				$tag->display = new CompoundTag("display", [
					"Name" => new StringTag("Name", $weapon_name."\n§r§aLevel:§f {$weapon_level}\n§aExp:§f {$weapon_exp}\n§aTank:§f {$weapon_tank}")
				]);*/

				$subweapon = $this->w->getSubWeaponNumFromWeapon($weapon);
				$subweap_name = $this->w->getSubWeaponName($subweapon);
				$subweap_data = $this->w->getSubWeaponDataFromWeapon($subweapon);
				$sub_id = $this->w->getSubWeaponItemId($subweapon);
				$item_sub = Item::get($sub_id[0], $sub_id[1], 1);
				$item_sub->setCustomName($subweap_name);
				$inventory->setItem(2, $item_sub);
				//$inventory->setHotbarSlotIndex(2, 2);
				
				$item_weapon = Item::get($id[0], $id[1], 1);
				$item_weapon->setCustomName($weapon_name."\n§r§aLevel:§f {$weapon_level}\n§aExp:§f {$weapon_exp}\n§aTank:§f {$weapon_tank}");
				$inventory->setItem(0, $item_ink);
				$inventory->setItem(1, $item_weapon);
				//$inventory->setHotbarSlotIndex(0, 0);
				//$inventory->setHotbarSlotIndex(1, 1);
				if($id[0] == 261){
					//チャージャーの場合矢を配布
					$inventory->setItem(4, Item::get(262, 0, 1));
				}
				//$inventory->sendContents($player);
				$this->w->applyEffect($player, $weapon, false);
				//$this->sendInkAmount($player, true);
				$inventory->setItem(3, Item::get(288));
		}
		$inventory->sendContents($player);
		return true;
	}

	/**
	 * フィールドスキャンのタスクを開始
	 * @param  int $time 試合時間
	 */
	public function startRepeating($time){
		if($this->area['mode']){
			$this->Task['Scanner'] = $this->getServer()->getScheduler()->scheduleRepeatingTask(new AreaScanner($this), 12);
			$this->Task['Count'] = $this->getServer()->getScheduler()->scheduleRepeatingTask(new Count($this), 20 * 10);
		}else if($this->dev !== 2){
			$this->Task['Scanner'] = $this->getServer()->getScheduler()->scheduleRepeatingTask(new Scanner($this), 20);
		}
		$this->count_time = time() + $time;
		$this->start_time = time();
	}

	/**
	 * スキャンのタスクを停止
	 * @return boolean
	 */
	public function stopRepeating(){
		if(isset($this->Task['Count'])){
			$this->getServer()->getScheduler()->cancelTask($this->Task['Count']->getTaskId());
			unset($this->Task['Count']);
		}
		if(isset($this->Task['Scanner'])){
			$this->getServer()->getScheduler()->cancelTask($this->Task['Scanner']->getTaskId());//スキャンのTaskを停止
			unset($this->Task['Scanner']);
			return true;
		}
		return false;
	}

	public function getMikataStatus(){
		$status = "\n\n"; //最初に空白を入れるのは隠れて見えないため
		$teammember = $this->team->getBattleTeamMember();
		$battle = false;
		foreach($teammember as $team => $member){

			$mikatastatus = "";
			$space = 0;
			$color = $this->team->getTeamColor($this->team->getTeamNumFromBattleTeamNum($team));
			$mikatastatus .= $color;
			foreach($member as $name => $flag){
				$mikatastatus .= "ᔦ";
				$mikatastatus .= "§r";
				if(($player = $this->getServer()->getPlayer($name))){
					if($this->canPlayerMoveCancel($player)){
						$mikatastatus .= "§8"."××"."§r";
					}else{
						$mikatastatus .= "§f"."Ꙭ"."§r";
						$space++;
					}
				}else{
					$mikatastatus .= "§8"."××"."§r";
				}

				$mikatastatus .= $color;
				$mikatastatus .= "ᔨ";
			}

			$mikatastatus .= "§r";//Reset

			if($team === 1){//
				$status .= $mikatastatus;

				if($space > 0){
					$status .= str_repeat(" ", $space);
				}

				if(count($member) < 4){
					$status .= str_repeat("    ", $this->team->getTeamMaxPlayer() - count($member));
				}

				$status .= "    ".$this->getCountTime()."    ";
			}else{
				if(count($member) < 4){
					$status .= str_repeat("    ", $this->team->getTeamMaxPlayer() - count($member));
				}

				if($space > 0){
					$status .= str_repeat(" ", $space);
				}

				$status .= $mikatastatus;
			}
		}
		$status .= "\n   ".str_repeat("    ", 5);//最初の３つの空白はずれ直し(?)
		return $status;
	}

	public function ShowMikataStatus($players, $console = false, $att = true){
		$txt = $this->getMikataStatus();//最初に空白を入れるのは隠れて見えないため
		$txt .= $this->scanBattleField_data;

		if($console){
			$m = explode("\n", $txt);
			foreach($m as $msg){
				MainLogger::getLogger()->info($msg);
			}	
		}

		if($this->hasSpawn){
			$pk = new SetEntityDataPacket();
			$pk->metadata = [
				Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $txt]
			];
			$pk->eid = 810000;
			Server::getInstance()->broadcastPacket($players, $pk);

			if($att){
				$pk = new UpdateAttributesPacket();
				$pk->entries[] = new CountTimeData($this->count_time, $this->start_time);
				$pk->entityId = 810000;
			Server::getInstance()->broadcastPacket($players, $pk);
			}

			$pk = new BossEventPacket();
			$pk->eid = 810000;
			$pk->type = 0;
			Server::getInstance()->broadcastPacket($players, $pk);
		}else{
			$this->hasSpawn = true;

			$pk = new AddEntityPacket();
			$pk->eid = 810000;
			$pk->type = 32;
			$pk->yaw = 0;
			$pk->pitch = 0;
			$pk->metadata = [
				Entity::DATA_FLAGS => [Entity::DATA_TYPE_BYTE, 0],
				Entity::DATA_AIR => [Entity::DATA_TYPE_SHORT, 300],
				Entity::DATA_FLAG_INVISIBLE => [Entity::DATA_TYPE_BYTE, 1],
				Entity::DATA_FLAG_CAN_SHOW_NAMETAG => [Entity::DATA_TYPE_BYTE, 1],
				Entity::DATA_FLAG_SILENT => [Entity::DATA_TYPE_BYTE, 1],
				Entity::DATA_FLAG_IMMOBILE => [Entity::DATA_TYPE_BYTE, 1],
				Entity::DATA_OWNER_EID => [Entity::DATA_TYPE_LONG, -1],
				Entity::DATA_LEAD_HOLDER_EID => [Entity::DATA_TYPE_LONG, -1],
				Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $txt],
			];
			$pk->x = 0;
			$pk->y = 0;
			$pk->z = 0;
					
			Server::getInstance()->broadcastPacket($players, $pk);
		
			$pk = new BossEventPacket(); 
			$pk->eid = 810000;
			$pk->type = 0;
			Server::getInstance()->broadcastPacket($players, $pk);
		}
	}

	public function DespawnMikataStatus(){
		$pk = new RemoveEntityPacket();
		$pk->eid = 810000;
		Server::getInstance()->broadcastPacket(Server::getInstance()->getOnlinePlayers(), $pk);

		$this->hasSpawn = false;
		$this->scanBattleField_data = "";
	}

	/**
	 * 現在の試合状況を送信(試合時間がオーバーしているかチェックも行う)
	 * @param  boolean $force_broadcast default = false trueを指定した場合、強制的にメッセージを送信する
	 */
	public function broadcastScan(){
		
		if(!$this->gamestop){
			$sec = (empty($this->getBattleField($this->field)['sec'])) ? 5 : $this->getBattleField($this->field)['sec'];
			$limit = $this->count_time;
			$time = $this->dev ? time() - $limit : $limit - time();
			if(!$this->dev and $time <= 0){
				if($time < 0) MainLogger::getLogger()->info("Finish! (time left: ".$time." sec)");

				$players = [];
				foreach(Server::getInstance()->getPluginManager()->getPermissionSubscriptions(Server::BROADCAST_CHANNEL_USERS) as $permissible){
					if($permissible instanceof Player and $permissible->hasPermission(Server::BROADCAST_CHANNEL_USERS)){
						if(!isset($this->trypaintData['player'][$permissible->getName()])){//試し塗り中の制限時間のメッセージと競合しないように
							$players[spl_object_hash($permissible)] = $permissible; // do not send messages directly, or some might be repeated
						}
					}
				}

				$this->DespawnMikataStatus();
				return $this->TimeTable();
			}else{
				$txt = $this->getMikataStatus();//最初に空白を入れるのは隠れて見えないため
				if($time % $sec === 0 || $this->scanBattleField_data === ""){
					$this->scanBattleField_data = $this->scanBattleField($this->field);
				}
				$txt .= $this->scanBattleField_data;
				

				$players = [];
				foreach(Server::getInstance()->getPluginManager()->getPermissionSubscriptions(Server::BROADCAST_CHANNEL_USERS) as $permissible){
					if($permissible instanceof Player and $permissible->hasPermission(Server::BROADCAST_CHANNEL_USERS)){
						if(!isset($this->trypaintData['player'][$permissible->getName()])){//試し塗り中の制限時間のメッセージと競合しないように
							$players[spl_object_hash($permissible)] = $permissible; // do not send messages directly, or some might be repeated
						}
					}
				}

				if($this->hasSpawn){
					$pk = new SetEntityDataPacket();
					$pk->metadata = [
						Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $txt]
					];
					$pk->eid = 810000;
					Server::getInstance()->broadcastPacket($players, $pk);

					$pk = new UpdateAttributesPacket();
					$pk->entries[] = new CountTimeData($this->count_time, $this->start_time);
					$pk->entityId = 810000;
					Server::getInstance()->broadcastPacket($players, $pk);
		
					$pk = new BossEventPacket();
					$pk->eid = 810000;
					$pk->type = 0;
					Server::getInstance()->broadcastPacket($players, $pk);
				}else{
					$this->hasSpawn = true;

					$pk = new AddEntityPacket();
					$pk->eid = 810000;
					$pk->type = 53;
					$pk->yaw = 0;
					$pk->pitch = 0;
					$pk->metadata = [
						Entity::DATA_FLAGS => [Entity::DATA_TYPE_BYTE, 0],
						Entity::DATA_AIR => [Entity::DATA_TYPE_SHORT, 300],
						Entity::DATA_FLAG_INVISIBLE => [Entity::DATA_TYPE_BYTE, 1],
						Entity::DATA_FLAG_CAN_SHOW_NAMETAG => [Entity::DATA_TYPE_BYTE, 1],
						Entity::DATA_FLAG_SILENT => [Entity::DATA_TYPE_BYTE, 1],
						Entity::DATA_FLAG_IMMOBILE => [Entity::DATA_TYPE_BYTE, 1],
						Entity::DATA_OWNER_EID => [Entity::DATA_TYPE_LONG, -1],
						Entity::DATA_LEAD_HOLDER_EID => [Entity::DATA_TYPE_LONG, -1],
						Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $txt],
					];
					$pk->x = 0;
					$pk->y = 0;
					$pk->z = 0;
					
					Server::getInstance()->broadcastPacket($players, $pk);
		
					$pk = new BossEventPacket(); 
					$pk->eid = 810000;
					$pk->type = 0;
					Server::getInstance()->broadcastPacket($players, $pk);
				}

				if(($time % ($sec * 3)) === 0){
					$m = explode("\n", $txt);
					foreach($m as $msg){
						MainLogger::getLogger()->info($msg);
					}
				}
				if(($time % 10) === 0){
					$this->allHideEnemysNametag(true);
				}
				//水位を変更
				if($this->field == 14 && ($this->dev ? $time >= 90 : $time <= 70) && !$this->waterLevel){
					$this->changeFieldForKusoStart();
				}
			}
		}else{
			$this->count_time += 1;
		}
	}

	/**
	 * 現在の試合状況を送信(試合時間がオーバーしているかチェックも行う)
	 * @param  boolean $force_broadcast default = false trueを指定した場合、強制的にメッセージを送信する
	 */
	public function AreabroadcastScan($tick){
		if(!$this->gamestop){
			$players = [];
			foreach(Server::getInstance()->getPluginManager()->getPermissionSubscriptions(Server::BROADCAST_CHANNEL_USERS) as $permissible){
				if($permissible instanceof Player and $permissible->hasPermission(Server::BROADCAST_CHANNEL_USERS)){
					if(!isset($this->trypaintData['player'][$permissible->getName()])){//試し塗り中の制限時間のメッセージと競合しないように
						$players[spl_object_hash($permissible)] = $permissible; // do not send messages directly, or some might be repeated
					}
				}
			}
			$limit = $this->count_time;
			$sec = (empty($this->getBattleField($this->field)['sec'])) ? 5 : $this->getBattleField($this->field)['sec'];
			$time = $this->dev ? time() - $limit : $limit - time();
			if(!$this->dev and $time <= 0){
				//if($time < 0) MainLogger::getLogger()->info("Finish! (time left: ".$time." sec)");

				//延長戦判定
				if(!$this->area['extra']['state']){
					$team1num = array_search(1, $this->team->battleTeamNumber);
					$team2num = array_search(2, $this->team->battleTeamNumber);
					$team1count = $this->area['count'][$team1num]['c'];
					$team2count = $this->area['count'][$team2num]['c'];
					$team = ($team1count == $team2count) ? $this->area['areaall'] : ($team1count < $team2count) ? $team1num : $team2num;//勝っているチーム
					if($this->area['areaall'] != 0 and $team != $this->area['areaall']){
						//延長戦突入
						$this->area['extra']['state'] = true;
						$this->area['extra']['winteam'] = $team;
						$this->area['extra']['time'] = time();
						$this->getServer()->broadcastMessage("延長戦突入！");
					}else{
						//ゲーム終了
						$this->DespawnMikataStatus();
						return $this->TimeTable();
					}
				}else{//延長戦の時
					$team1num = array_search(1, $this->team->battleTeamNumber);
					$team2num = array_search(2, $this->team->battleTeamNumber);
					$team1count = $this->area['count'][$team1num]['c'];
					$team2count = $this->area['count'][$team2num]['c'];
					$team = ($team1count == $team2count) ? $this->area['extra']['winteam'] : ($team1count < $team2count) ? $team1num : $team2num;//勝っているチーム
					if($team != $this->area['extra']['winteam']){//カウントが逆転したら
						//ゲーム終了
						$this->area['count'][$team]['c']--;
						$this->DespawnMikataStatus();
						return $this->TimeTable();
					}
					$this->scanBattleField_data = $this->scanFieldArea($this->field);
					if($this->scanBattleField_data == false){
						//ゲーム終了
						$this->DespawnMikataStatus();
						return $this->TimeTable();
					}
					if($this->area['areaall'] == 0 and time()-$this->area['extra']['time'] > 10){//カウントストップが10秒を超えたとき
						//ゲーム終了
						$this->DespawnMikataStatus();
						return $this->TimeTable();
					}
					//カウント
					$securing = $this->area['areaall'];
					if($securing != 0){
						if($this->area['count'][$securing]['p'] > 0){
							$this->area['count'][$securing]['p']--;
						}else{
							if($this->area['count'][$securing]['c'] > 1){
								$this->area['count'][$securing]['c']--;
							}else{
								//ノックアウト
								$this->area['count'][$securing]['c']--;
								$this->DespawnMikataStatus();
								return $this->TimeTable();
							}
						}
					}
					$this->ShowMikataStatus($players);
				}

			}else{
				$this->scanBattleField_data = $this->scanFieldArea($this->field);
				//カウント
				$securing = $this->area['areaall'];
				if($securing != 0){
					if($this->area['count'][$securing]['p'] > 0){
						$this->area['count'][$securing]['p']--;
					}else{
						if($this->area['count'][$securing]['c'] > 1){
							$this->area['count'][$securing]['c']--;
						}else{
							//ノックアウト
							$this->area['count'][$securing]['c']--;
							$this->DespawnMikataStatus();
							return $this->TimeTable();
						}
					}
				}

				$console = (($time % ($sec * 3)) === 0);
				$att = (($time % $sec) == 0);
				//プレイヤーに状況を送信
				$this->ShowMikataStatus($players, $console, $att);
				if(($time % 10) === 0){
					$this->allHideEnemysNametag(true);
				}

			}
		}else{
			//$this->count_time += 1;
		}
	}

	public function getKuso(){
		$field = 14;
		$bf = $this->getBattleField($field);
		$level = $this->getServer()->getLevelByName($bf['level']);
		if(isset($bf['name'])){
			if(!file_exists(__DIR__."/woolsdata/")) mkdir(__DIR__."/woolsdata/");
			$file_name = __DIR__."/woolsdata/".$field."_1.json";
			$json = file_exists($file_name) ? file_get_contents($file_name) : false;
			if($json !== false){
				$woolsBlockArray = json_decode($json, true);
			}else{
				$pos = $bf['scan'];
				$sx = min($pos[1][0], $pos[2][0]);
				$sy = min($pos[1][1], $pos[2][1]);
				$sz = min($pos[1][2], $pos[2][2]);
				$ex = max($pos[1][0], $pos[2][0]);
				$ey = max($pos[1][1], $pos[2][1]);
				$ez = max($pos[1][2], $pos[2][2]);
				$i = 0;
				$woolsBlockArray = [];
				for($x = $sx; $x <= $ex; ++$x){
					for($y = $sy; $y <= $ey; ++$y){
						for($z = $sz; $z <= $ez; ++$z){
							if($level->getBlockIdAt($x, $y, $z) == 35 and $level->getBlockDataAt($x, $y, $z) == 0){
								//座標の位置にインデックスを振る
								$woolsBlockArray[$x][$y][$z] = $i;
								$i++;
							}
						}
					}
				}
				$woolsBlockArray['cnt'] = $i;
				$woolsBlockJSON = json_encode($woolsBlockArray);
				file_put_contents($file_name, $woolsBlockJSON);
				MainLogger::getLogger()->debug("スキャンファイル保存完了 保存先(".$file_name.")");
			}
			return $woolsBlockArray;
		}
	}

	/*
		移動元→移動先
		移動元にあるブロックは+2すると移動先
		移動先に移動したら、移動元にあるブロックを消す

		##移動元にようもうがあったら消さない
		移動先-2にようもうがある　かつ　移動先にすでに羊毛がある場合は消さない
	*/

	public function changeFieldForKusoStart(){
		$new_color = 8;
		$out = $this->lang->translateString("waterLevelDown");
		$this->getServer()->broadcastMessage($out);
		$new = $this->getKuso();
		$level = $this->getLevelByBattleField(14);
		unset($new['cnt']);
		foreach($new as $x => $d){
			krsort($d);
			foreach($d as $y => $e){
				foreach($e as $z => $index){
					if(isset($this->woolsBlockArray[$x][$y][$z])){
						$newy = $y + 2;//移動先の座標
						$index = $this->woolsBlockArray[$x][$y][$z];
						//その場所の色
						if(isset($this->splatWoolsArray[$index]) and $this->splatWoolsArray[$index] != 0){
							$colorIndex = $this->splatWoolsArray[$index];
							//$color = $this->team->getTeamColorBlock($this->splatWoolsArray[$index]);
							$color = $this->team->getTeamColorBlock(array_search($colorIndex, $this->team->battleTeamNumber));
						}else{
							$color = $new_color;
						}
						$level->setBlockIdAt($x, $y, $z, 0);
						$level->setBlockDataAt($x, $y, $z, 0);
						unset($this->woolsBlockArray[$x][$y][$z]);
						//塗る
						$level->setBlockIdAt($x, $newy, $z, 35);
						$level->setBlockDataAt($x, $newy, $z, $color);
						$this->woolsBlockArray[$x][$newy][$z] = $index;
					}
				}
			}
		}
		$this->waterLevel = true;
	}

	public function changeFieldForKusoLast(){
		$new = $this->getKuso();
		$level = $this->getLevelByBattleField(14);
		unset($new['cnt']);
		foreach($new as $x => $d){
			ksort($d);
			foreach($d as $y => $e){
				foreach($e as $z => $index){
					$oldy = $y + 2;//戻す座標はy
					$this->woolsBlockArray[$x][$y][$z] = $index;
					$level->setBlockIdAt($x, $y, $z, 35);
					$level->setBlockDataAt($x, $y, $z, 0);
					if(!isset($new[$x][$oldy][$z])){//セットされてたら消すな
						$level->setBlockIdAt($x, $oldy, $z, 0);
						$level->setBlockDataAt($x, $oldy, $z, 0);
						unset($this->woolsBlockArray[$x][$oldy][$z]);
					}
				}
			}
		}
	}

	//scan(11/10)
	public function getFieldWoolsData($field, $level = false){
		$bf = $this->getBattleField($field);
		if(!$level) $level = $this->getServer()->getLevelByName($bf['level']);
		if(isset($bf['name'])){
			if(!file_exists(__DIR__."/woolsdata/")) mkdir(__DIR__."/woolsdata/");
			$file_name = __DIR__."/woolsdata/".$field.".json";
			$json = file_exists($file_name) ? file_get_contents($file_name) : false;
			if($json !== false){
				$woolsBlockArray = json_decode($json, true);
			}else{
				$pos = $bf['scan'];
				$sx = min($pos[1][0], $pos[2][0]);
				$sy = min($pos[1][1], $pos[2][1]);
				$sz = min($pos[1][2], $pos[2][2]);
				$ex = max($pos[1][0], $pos[2][0]);
				$ey = max($pos[1][1], $pos[2][1]);
				$ez = max($pos[1][2], $pos[2][2]);
				$i = 0;
				$woolsBlockArray = [];
				for($x = $sx; $x <= $ex; ++$x){
					for($y = $sy; $y <= $ey; ++$y){
						for($z = $sz; $z <= $ez; ++$z){
							if($level->getBlockIdAt($x, $y, $z) == 35){
								//座標の位置にインデックスを振る
								$woolsBlockArray[$x][$y][$z] = $i;
								$i++;
							}
						}
					}
				}
				$woolsBlockArray['cnt'] = $i;
				$woolsBlockJSON = json_encode($woolsBlockArray);
				file_put_contents($file_name, $woolsBlockJSON);
				MainLogger::getLogger()->debug("スキャンファイル保存完了 保存先(".$file_name.")");
			}
			return $woolsBlockArray;
		}
	}

	//scan(11/20)
	public function scanBattleFieldFirst($field, $level = false){
		$this->woolsBlockArray = $this->getFieldWoolsData($field, $level);
		for($i = 0; $i <= $this->woolsBlockArray['cnt']; $i++){
			// 初期化
			$this->splatWoolsArray[$i] = 0;
		}
	}

	/**
	 * フィールドをスキャン、塗った割合を取得
	 * @param  int    $field
	 * @return string
	 */
	public function scanBattleField($field){
		$cnt_all = 0;
		$cnt = [];
		foreach($this->splatWoolsArray as $n){
			if(!isset($cnt[$n])) $cnt[$n] = 0;
			$cnt[$n] ++;
			$cnt_all ++;
		}
		$txt = "";
		foreach($cnt as $index => $c){
			$percentage = round( $c / $cnt_all * 100 );
			$team_num = array_search($index, $this->team->battleTeamNumber);
			if($team_num){
				$percentage = ($this->canHidePaint()) ? "--" : $percentage;
				$txt .= $this->team->getTeamColor($team_num).$percentage."% §r";
			}
		}
		return $txt;
	}

	/**
	 * エリアをスキャン、塗った割合を取得
	 * @param  int    $field
	 * @return string
	 */
	public function scanFieldArea($field){
		//$cnt_all = 0;
		//$cnt = [];
		$cap = 0;
		$bf = $this->getBattleField($field);
		$areapos = $bf['area'];
		$level = $this->getServer()->getLevelByName($bf['level']);
		foreach($areapos as $area_num => $pos){
			/*foreach($this->area['wool'][$area_num] as $dex => $n){
				if(!isset($cnt[$n])) $cnt[$n] = 0;
				$cnt[$n] ++;
				$cnt_all ++;
			}*/
			$cnt = $this->area['wools'][$area_num];
			$cnt_all = $cnt['all'];
			foreach($cnt as $index => $c){
				if($index == 'all' or $index == 0){
					continue;
				}
				$percentage = round( $c / $cnt_all * 100 );
				$team_num = array_search($index, $this->team->battleTeamNumber);
				if($team_num){
					if($this->area['area'][$area_num] == 0){//そのエリアの状態が中立のとき
						if($percentage > 65){
							//ガチエリアを取った
							$this->area['area'][$area_num] = $team_num;
							$cap = 1;
							$level->addSound(new ExplodeSound(new Vector3($bf['area'][$area_num][0][0], $bf['area'][$area_num][0][1], $bf['area'][$area_num][0][2])));
							//エリア全塗
							$this->PaintArea($bf, $area_num, $team_num);
						}
					}else if($this->area['area'][$area_num] == $team_num){//そのエリアの状態が$team_numのとき
						if($percentage < 50){
							//ガチエリアがもとに戻された
							$this->area['area'][$area_num] = 0;
							$cap = 2;
						}
					}
				}
			}
		}

		//echo var_dump($this->area['wools']);
		//echo var_dump($this->area['area']);

		$all = $this->area['areaall'];
		if(count($this->area['area']) == 1){
			$tm = $this->area['area'][1];
			if($all != $tm){
				$this->area['areaall'] = $tm;
				if($tm == 0){
					//カウントストップした！
					$this->area['history']['end'] = $this->area['count'][$this->area['history']['team']]['c'];
					//$this->getServer()->broadcastMessage("カウントストップした！");
					$players = Server::getInstance()->getOnlinePlayers();
					foreach($players as $p){
						$name = $p->getName();
						if($this->team->getBattleTeamOf($name) or isset($this->view[$name]) or isset($this->cam[$name])){
							$p->sendMessage("カウントストップした！");
						}
					}
					//延長戦判定
					if($this->area['extra']['state']){
						//ストップ時間
						$this->area['extra']['time'] = time();
					}
				}else{
					//ガチエリアを奪った！
					//$this->getServer()->broadcastMessage($this->team->getTeamName($tm)." ガチエリアを奪った！");
					$players = Server::getInstance()->getOnlinePlayers();
					foreach($players as $p){
						$name = $p->getName();
						if($this->team->getBattleTeamOf($name) or isset($this->view[$name]) or isset($this->cam[$name])){
							$p->sendMessage($this->team->getTeamName($tm)." ガチエリアを奪った！");
						}
					}
					//延長戦判定
					if($this->area['extra']['state']){
						$this->area['extra']['time'] = time();
						if($this->area['extra']['winteam'] == $tm){
							//試合終了
							return false;
						}
					}
					$btn = $this->area['history']['team'];
					if($btn != 0 and $btn != $tm and $this->area['count'][$btn]['p'] == 0){
						//ペナルティタイム加算
						$this->area['count'][$btn]['p'] += floor(($this->area['history']['start']-$this->area['history']['end'])*3/4);
					}
					$this->area['history']['team'] = $tm;
					$this->area['history']['start'] = $this->area['count'][$tm]['c'];
				}
			}
		}else{
			$tm_1 = $this->area['area'][1];
			$tm_2 = $this->area['area'][2];
			$out = 0;
			if($tm_1 == $tm_2){
				$out = $tm_1;
			}
			if($out != $all){
				$this->area['areaall'] = $out;
				if($out == 0){
					//カウントストップした！
					$this->area['history']['end'] = $this->area['count'][$this->area['history']['team']]['c'];
					//$this->getServer()->broadcastMessage("カウントストップした！");
					$players = Server::getInstance()->getOnlinePlayers();
					foreach($players as $p){
						$name = $p->getName();
						if($this->team->getBattleTeamOf($name) or isset($this->view[$name]) or isset($this->cam[$name])){
							$p->sendMessage("カウントストップした！");
						}
					}
					//延長戦判定
					if($this->area['extra']['state']){
						//ストップ時間
						$this->area['extra']['time'] = time();
					}
				}else{
					//ガチエリアを奪った！
					$this->getServer()->broadcastMessage($this->team->getTeamName($out)." ガチエリアを奪った！");
					$players = Server::getInstance()->getOnlinePlayers();
					foreach($players as $p){
						$name = $p->getName();
						if($this->team->getBattleTeamOf($name) or isset($this->view[$name]) or isset($this->cam[$name])){
							$p->sendMessage($this->team->getTeamName($tm)." ガチエリアを奪った！");
						}
					}
					//延長戦判定
					if($this->area['extra']['state']){
						$this->area['extra']['time'] = time();
						if($this->area['extra']['winteam'] == $out){
							//試合終了
							return false;
						}
					}
					$btn = $this->area['history']['team'];
					if($btn != 0 and $btn != $out and $this->area['count'][$btn]['p'] == 0){
						//ペナルティタイム加算
						$this->area['count'][$btn]['p'] += floor(($this->area['history']['start']-$this->area['history']['end'])*3/4);
					}
					$this->area['history']['team'] = $out;
					$this->area['history']['start'] = $this->area['count'][$out]['c'];
				}
			}
		}

		/*if($cap == 1){
			if(count($this->area['area']) == 1 or ($this->area['area'][1] != 0 and $this->area['area'][1] == $this->area['area'][2])){
				$tn = $this->area['area'][1];//奪ったチーム
				$this->area['areaall'] = $tn;//ガチエリアを奪った！奪われた！
				$this->getServer()->broadcastMessage($this->team->getTeamName($tn)." ガチエリアを奪った！");
				$btn = $this->area['history']['team'];
				if($btn != 0 and $btn != $tn){
					//ペナルティタイム加算
					$this->area['count'][$btn]['p'] += floor(($this->area['history']['start']-$this->area['history']['end'])*3/4);
				}
				$this->area['history']['team'] = $tn;
				$this->area['history']['start'] = $this->area['count'][$tn]['c'];
			}
		}else if($cap == 2){
			if($this->area['areaall'] != 0){
				$this->area['history']['end'] = $this->area['count'][$this->area['history']['team']]['c'];
				$this->area['areaall'] = 0;//もとに戻された！戻した！
				$this->getServer()->broadcastMessage("カウントストップした！");
			}
		}*/
		$txt = "";
		foreach($this->area['count'] as $team => $value){
			$txt .= "§f".$this->team->getTeamColor($team).$value['c']."+".$value['p']." §r";
		}
		foreach($areapos as $area_num => $pos){
			$txt .= "\n   ".str_repeat("    ", 5);
			$txt .= "§f".$this->team->getTeamColor($this->area['area'][$area_num])."⚜";
		}
		return $txt;
	}

	/**
	 * エリアを奪った時に奪ったチームで塗る
	 * 
	 */
	public function PaintArea($bf, $area_num, $team_num){
		$pos = $bf['area'][$area_num];
		$blocks = [];
		$level = $this->getServer()->getLevelByName($bf['level']);
		for($x = $pos[0][0]; $x <= $pos[1][0]; ++$x){
			for($y = $pos[0][1]; $y <= $pos[1][1]; ++$y){
				for($z = $pos[0][2]; $z <= $pos[1][2]; ++$z){
					if($level->getBlockIdAt($x, $y, $z) === 35){
						$level->setBlockDataAt($x, $y, $z, $this->team->getTeamColorBlock($team_num));
						$blocks[] = [$x, $y, $z];
					}
				}
			}
		}
		$change_cnt = $this->changeWoolsindex($blocks, "", $team_num);
	}

	/**
	 * 塗った割合を非表示にするかどうか
	 * @return bool
	 */
	public function canHidePaint(){
		$limit = $this->count_time;
		if($this->dev) return false;
		$time = $limit - time();
		return $time <= 60;//ここの60を変更することで非表示にする時間を設定できる
	}

	/**
	 * 試合の残り時間を取得
	 * @return string
	 */
	public function getCountTime(){
		$limit = $this->count_time;
		$time = ($this->dev) ? time() - $limit : $limit - time();
		//if($time >= 6000) return "§l§7--:--§r\n";
		$minutes = floor($time / 60);
		$seconds = abs($time) % 60;
		return "§l§7".sprintf("%d:%02d", $minutes, $seconds)."§r";
	}

	/**
	 * フィールドをリセット
	 * @param  int   $field フィールドのID
	 */
	public function resetBattleField($field){
		$bf = $this->getBattleField($field);
		$this->w->setFieldData($bf, 0);
		$level = $this->getServer()->getLevelByName($bf['level']);
		$pos = $bf['scan'];
		$sx = min($pos[1][0], $pos[2][0]);
		$sy = min($pos[1][1], $pos[2][1]);
		$sz = min($pos[1][2], $pos[2][2]);
		$ex = max($pos[1][0], $pos[2][0]);
		$ey = max($pos[1][1], $pos[2][1]);
		$ez = max($pos[1][2], $pos[2][2]);

		$fielddata = $this->getBattleField($field);
		$color_num = $fielddata['color'];
		for($x = $sx; $x <= $ex; ++$x){
			for($y = $sy; $y <= $ey; ++$y){
				for($z = $sz; $z <= $ez; ++$z){
					if($level->getBlockIdAt($x, $y, $z) == 35){
						$level->setBlockDataAt($x, $y, $z, $color_num);
					}
				}
			}
		}
		$this->scanBattleFieldFirst($field, $level);
		if(isset($bf['unload'])){
			foreach($bf['unload'] as $num => $pos){
				$sx = min($pos[1][0], $pos[2][0]);
				$sz = min($pos[1][1], $pos[2][1]);
				$ex = max($pos[1][0], $pos[2][0]);
				$ez = max($pos[1][1], $pos[2][1]);
				for($x = $sx; $x <= $ex; ++$x){
					for($z = $sz; $z <= $ez; ++$z){
						$level->unloadChunk($x >> 4, $z >> 4, true, false);
					}
				}
			}
		}
		if($this->field == 14){
			$this->changeFieldForKusoLast();
		}
	}

	public function changeWoolindex($x, $y, $z, $user){
		if($battleTeam = $this->team->getBattleTeamOf($user)){
			if(isset($this->woolsBlockArray[$x][$y][$z])){
				$n = $this->woolsBlockArray[$x][$y][$z];
				$wt = $this->splatWoolsArray[$n];
				if($wt != $battleTeam){
					$this->splatWoolsArray[$n] = $battleTeam;
					if(isset($this->area['wool'][1][$n])){
						if($wt != 0){
							$this->area['wools'][1][$wt]--;
						}
						$this->area['wools'][1][$battleTeam]++;
						$this->area['wool'][1][$n] = $battleTeam;
					}
					if(isset($this->area['wool'][2][$n])){
							if($wt != 0){
								$this->area['wools'][2][$wt]--;
							}
							$this->area['wools'][2][$battleTeam]++;
						$this->area['wool'][2][$n] = $battleTeam;
					}
				}
				$this->leftCheck[$user]['tick'] = 0;
				return true;
			}
		}
		return false;
	}

	public function changeWoolsindex($pos_ar, $user, $team = null){
		if($battleTeam = $this->team->getBattleTeamOf($user) or ($team != null and isset($this->team->battleTeamNumber[$team]))){
			if($team != null) $battleTeam = $this->team->battleTeamNumber[$team];
			$count = 0;
			foreach($pos_ar as $pos){
				$x = $pos[0];
				$y = $pos[1];
				$z = $pos[2];
				if(isset($this->woolsBlockArray[$x][$y][$z])){
					$n = $this->woolsBlockArray[$x][$y][$z];
					$wt = $this->splatWoolsArray[$n];
					if($wt != $battleTeam){
						if(!isset($this->area['wools'][1][$battleTeam])){
							$this->area['wools'][1][$battleTeam] = 0;
						}
						if(!isset($this->area['wools'][2][$battleTeam])){
							$this->area['wools'][2][$battleTeam] = 0;
						}
						if(isset($this->area['wool'][1][$n])){
							if($wt != 0){
								$this->area['wools'][1][$wt]--;
							}
							$this->area['wools'][1][$battleTeam]++;
							$this->area['wool'][1][$n] = $battleTeam;
						}
						if(isset($this->area['wool'][2][$n])){
							if($wt != 0){
								$this->area['wools'][2][$wt]--;
							}
							$this->area['wools'][2][$battleTeam]++;
							$this->area['wool'][2][$n] = $battleTeam;
						}
						$this->splatWoolsArray[$n] = $battleTeam;
						$count++;
					}
				}
			}
			if($count > 0) $this->leftCheck[$user]['tick'] = 0;
			return $count;
		}
		return false;
	}

	/**
	 * フィールドの試合時間を取得
	 * @return int
	 */
	public function getTimeLimit(){
		$default = 120;
		if($this->area['mode']) return 180;
		return (isset($this->battle_field[$this->field]['time-limit'])) ? $this->battle_field[$this->field]['time-limit'] : $default;
	}

	/**
	 * インベントリをクリア
	 * @param  Player $player falseの場合サーバーにいる全プレイヤーを対象にする
	 * @return
	 */
	public function delAllItem($player = false){
		if(!$player){
			foreach($this->getServer()->getOnlinePlayers() as $player){
				$inventory = $player->getInventory();
				$inventory->clearAll();
				$inventory->sendContents($player);
			}
		}else{
			$inventory = $player->getInventory();
			$inventory->clearAll();
			$inventory->sendContents($player);
		}
	}

	/**
	 * 試合していたプレイヤーをリスポへテレポート
	 * @return true
	 */
	public function TpTeamLobby(){
		$level = $this->getLevelByBattleField(0);
		$teams = $this->team->getBattleTeamMember();
		$count = 1;
		foreach ($teams as $team => $members) {
			foreach ($members as $member => $status){
				$player = $this->getServer()->getPlayer($member);
				if($player instanceof Player){
					$zinti = new Vector3($this->lobbyPos[0], $this->lobbyPos[1], $this->lobbyPos[2]);
					switch($count){
						case 1:
							$zinti = $zinti->add(-1.5, 0, 0);
							break;
						case 2:
							$zinti = $zinti->add(1.5, 0, 0);
							break;
						case 3:
							$zinti = $zinti->add(0, 0, -1.5);
							break;
						case 4:
							$zinti = $zinti->add(0, 0, 1.5);
							break;
						case 5:
							$zinti = $zinti->add(-1, 0, 1);
							break;
						case 6:
							$zinti = $zinti->add(1, 0, -1);
							break;
						case 7:
							$zinti = $zinti->add(1, 0, 1);
							break;
						case 8:
							$zinti = $zinti->add(-1, 0, -1);
							break;
					}
					$count++;
					$player->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE, false);
					$player->setAllowFlight(false);
					$player->setGamemode(Player::ADVENTURE);
					$this->delAllItem($player);
					$player->removeAllEffects();
					$player->setMaxHealth(20);
					$player->setHealth(20);
					$player->extinguish();
					$player->teleport($zinti);
					$this->ResetStatus($player, true);
					$player->getInventory()->addItem(Item::get(340), Item::get(288));
					$player->sendData($player);
					$this->itemselect->set($player, false);
					//$this->itemCase->set($player);
					$this->shop->sendPage($player);
				}
			}
		}
		return true;
	}

	/**
	 * 試合参加した人全員のインベントリクリア
	 * @return true
	 */
	public function delAllBattleItem(){
		$teams = $this->team->getBattleTeamMember();
		foreach($teams as $team => $members){
			foreach($members as $member => $number){
				if(($player = $this->getServer()->getPlayer($member)) instanceof Player){
					$inventory = $player->getInventory();
					$inventory->clearAll();
					$inventory->sendContents($player);
				}
			}
		}
		return true;
	}

	public function startGame(){
		$this->startTprCheck();
		if(!isset($this->Task['PositionCheck'])){
			$this->Task['PositionCheck'] = $this->getServer()->getScheduler()->scheduleRepeatingTask(new PositionCheck($this), 1);
		}
		//$this->w->startMoveTask();
		$this->PlayersMoveCancel(false);
		Server::getInstance()->getScheduler()->scheduleDelayedTask(new HideEnemysNametag($this),100);
	}

	public function stopGame(){
		$this->PlayersMoveCancel(true);
		$this->stopTprCheck();
		$this->w->stopMoveTask(0);
		if(isset($this->Task['PositionCheck'])){
			$this->getServer()->getScheduler()->cancelTask($this->Task['PositionCheck']->getTaskId());
			unset($this->Task['PositionCheck']);
		}
		$this->allHideEnemysNametag(false);
	}

	/**
	 * エリアデータをリセット
	 */
	public function SetAreaData($teams){
		$this->area['history'] = [//一つ前の確保履歴
			'team' => 0,
			'start' => 100,
			'end' => 100
		];
		$this->area['extra'] = [
			'state' => false,
			'winteam' => 0,
			'time' => 0
		];
		$this->area['wools'] = [];
		$this->area['area'] = [];
		$bf = $this->getBattleField($this->field);
		foreach($bf['area'] as $s => $pos){
			$this->area['area'][$s] = 0;
		}
		$this->area['areaall'] = 0;
		$this->area['wool'] = [];
		$areapos = $bf['area'];
		foreach($areapos as $area_num => $pos){
			$count = 0;
			$this->area['wool'][$area_num] = [];
			$sx = $pos[0][0];
			$sy = $pos[0][1];
			$sz = $pos[0][2];
			$ex = $pos[1][0];
			$ey = $pos[1][1];
			$ez = $pos[1][2];
			for($x = $sx; $x <= $ex; ++$x){
				for($y = $sy; $y <= $ey; ++$y){
					for($z = $sz; $z <= $ez; ++$z){
						if(isset($this->woolsBlockArray[$x][$y][$z])){
							$n = $this->woolsBlockArray[$x][$y][$z];
							$this->area['wool'][$area_num][$n] = 0;
							$count++;
						}
					}
				}
			}
			$this->area['wools'][$area_num] = [];
			$this->area['wools'][$area_num]['all'] = $count;
		}
		$this->area['count'] = [];
		foreach($teams as $index => $team_num){
			$this->area['count'][$team_num] =[
				'c' => 100, //カウントダウン
				'p' => 0 //ペナルティタイム
			];
			if(isset($this->area['wools'][1])){
				$this->area['wools'][1][$index] = 0;
			}
			if(isset($this->area['wools'][2])){
				$this->area['wools'][2][$index] = 0;
			}
		}
	}

	/**
	 * バトル内部データなどをリセット
	 */
	public function Reset(){
		$this->AllRemovescattersItem();
		//$this->AllResetStatus();
		$this->team->battleTeamNumber = [];
		$this->team->battleTeamMember = [];
		$this->team->canJoin = true;
		$this->count_time = 0;
		$this->start_time = 0;
		$this->DespawnAllSquid();
		$this->field = 0;
		$this->error = 0;
		$this->game = 1;
		$this->leftCheck = [];
		$this->splatWoolsArray = [];
		//$this->Squid_Standby = [];
		$this->Task['game'] = [];
		$this->Timelimit = 0;
		//$this->tnt_data = [];
		$this->tprCheckData = [];
		$this->unfinished = false;
		$this->warn = [];
		$this->waterLevel = false;
		$this->winteam = [];
		$this->woolsBlockArray = [];
		$this->w->resetBattleMember(1, 2);
		$this->w->resetFieldData(0);
		$this->team->removeAllMember(true);//チーム解散
		$this->a->unsetUnnecessaryData();
		foreach($this->getServer()->getOnlinePlayers() as $player){
			$this->changeName($player);
		}
		if($this->mute){
			if($this->area['mode']){
				$this->area['mode'] = false;
				$this->getServer()->broadcastMessage("§b>>次はナワバリバトルです");
			}else{
				$this->getServer()->broadcastMessage("§b>>次はガチエリアです");
				$this->area['mode'] = true;
			}
		}
	}

	public function getFieldNumber(){
		return $this->field;
	}

	/**
	 * 試合結果を発表
	 * @param  boolean $animation default = true 試合結果のアニメーションをするかどうか
	 */
	public function announceBattleResults($animation = true){
		if($this->unfinished && !$this->dev){
			if($animation){
				$this->Task['game'][$this->game + 1] = $this->getServer()->getScheduler()->scheduleDelayedTask(new TimeScheduler($this), 20*4);
			}
			return false;
		}
		$cnt_all = 0;
		$txt = "";
		$b_count = [];
		foreach($this->splatWoolsArray as $n){
			if(!isset($b_count[$n])) $b_count[$n] = 0;
			$b_count[$n] ++;
			$cnt_all ++;
		}
		$team1num = array_search(1, $this->team->battleTeamNumber);
		$team2num = array_search(2, $this->team->battleTeamNumber);
		$team1_cnt = $b_count[1] ?? 0;
		$team2_cnt = $b_count[2] ?? 0;
		$teams_cnt = $team1_cnt + $team2_cnt;

		$data = [
			1 => [],
			2 => []
		];
		$members1 = $this->team->getTeamMember($team1num, true);
		$data[1]['color'] = $team1num;
		$data[1]['per'] = floor($team1_cnt / $cnt_all);
		foreach($members1 as $member){
			$playerData = $this->a->getData($member);
			$weapon = $playerData->getNowWeapon($member);
			$data[1]['players'][$member] = $weapon;
		}
		$members2 = $this->team->getTeamMember($team2num, true);
		$data[2]['color'] = $team2num;
		$data[2]['per'] = floor($team2_cnt / $cnt_all);
		foreach($members2 as $member){
			$playerData = $this->a->getData($member);
			$weapon = $playerData->getNowWeapon($member);
			$data[2]['players'][$member] = $weapon;
		}

		$data = [
			1 => [
				'num'	=> $team1num,
				'name'	=> $this->team->getTeamName($team1num),
				'color'	=> $this->team->getTeamColor($team1num),
				'count' => $team1_cnt,
				'percentage' => $teams_cnt > 0 ? $team1_cnt / $teams_cnt : 0,//両チームの合計を基準とした数値をだす
				'percentage2' => $team1_cnt / $cnt_all,//マップ全体を基準にした数値
			],
			2 => [
				'num'	=> $team2num,
				'name'	=> $this->team->getTeamName($team2num),
				'color'	=> $this->team->getTeamColor($team2num),
				'count' => $team2_cnt,
				'percentage' => $teams_cnt > 0 ? $team2_cnt / $teams_cnt : 0,
				'percentage2' => $team2_cnt / $cnt_all,
			]
		];

		$opponentExist = ($data[1]['count'] and $data[2]['count']);
		$this->winteam = ($team1_cnt === $team2_cnt || !$opponentExist) ? false : (($data[1]['count'] < $data[2]['count']) ? [2, $data[2]['name']] : [1, $data[1]['name']]);
		if($opponentExist || $this->dev){
			$this->s->saveBattleData(0, $this->field, $data);
			$percentage1 = floor($data[1]['count'] / $cnt_all * 100);
			$percentage2 = floor($data[2]['count'] / $cnt_all * 100);
		
			$out = 	$this->lang->translateString("result.team", [str_pad($data[1]['name'], 9), str_pad($data[1]['count'], 5), str_pad($percentage1, 2, " ", STR_PAD_LEFT)])."\n".
					$this->lang->translateString("result.team", [str_pad($data[2]['name'], 9), str_pad($data[2]['count'], 5), str_pad($percentage2, 2, " ", STR_PAD_LEFT)])."\n";

			if($this->dev){
				if($this->winteam !== false && $animation){
					$this->BattleResultAnimation = new BattleResultAnimation($this, $data, $out);
				}else{
					$this->getServer()->broadcastMessage($out);
					if($animation){
						$this->Task['game'][$this->game + 1] = $this->getServer()->getScheduler()->scheduleDelayedTask(new TimeScheduler($this), 20*4);
					}
				}
				return true;
			}
		}else{
			//チームのどちらかがいない(塗っていない)とき
			$this->winteam = false;
			$out = $this->lang->translateString("result.team.absence")."\n";
		}
		if($this->winteam){
			$out .= $this->lang->translateString("timeTable.13.win", [$this->winteam[1]]);
		}else{
			$out .= $this->lang->translateString("timeTable.13.draw");
		}
		if($this->winteam && $animation){
			$this->BattleResultAnimation = new BattleResultAnimation($this, $data, $out);
		}else{
			$this->getServer()->broadcastMessage($out);
			if($animation){
				$this->Task['game'][$this->game + 1] = $this->getServer()->getScheduler()->scheduleDelayedTask(new TimeScheduler($this), 20*4);
			}
		}
	}

	/**
	 * ガチエリア試合結果を発表
	 * @param  boolean $animation default = true 試合結果のアニメーションをするかどうか
	 */
	public function announceAreaBattleResults($animation = true){
		if($this->unfinished && !$this->dev){
			if($animation){
				$this->Task['game'][$this->game + 1] = $this->getServer()->getScheduler()->scheduleDelayedTask(new TimeScheduler($this), 20*4);
			}
			return false;
		}
		$cnt_all = 100;
		$txt = "";
		$team1num = array_search(1, $this->team->battleTeamNumber);
		$team2num = array_search(2, $this->team->battleTeamNumber);
		$team1_cnt = $cnt_all-$this->area['count'][$team1num]['c'];
		$team2_cnt = $cnt_all-$this->area['count'][$team2num]['c'];
		if($team1_cnt == $cnt_all){
			$team2_cnt = 0;
		}
		if($team2_cnt == $cnt_all){
			$team1_cnt = 0;
		}
		$teams_cnt = $team1_cnt + $team2_cnt;


		$data = [
			1 => [],
			2 => []
		];
		$members1 = $this->team->getTeamMember($team1num, true);
		$data[1]['color'] = $team1num;
		$data[1]['per'] = $team1_cnt;
		foreach($members1 as $member){
			$playerData = $this->a->getData($member);
			$weapon = $playerData->getNowWeapon($member);
			$data[1]['players'][$member] = $weapon;
		}
		$members2 = $this->team->getTeamMember($team2num, true);
		$data[2]['color'] = $team2num;
		$data[2]['per'] = $team2_cnt;
		foreach($members2 as $member){
			$playerData = $this->a->getData($member);
			$weapon = $playerData->getNowWeapon($member);
			$data[2]['players'][$member] = $weapon;
		}

		$data = [
			1 => [
				'num'	=> $team1num,
				'name'	=> $this->team->getTeamName($team1num),
				'color'	=> $this->team->getTeamColor($team1num),
				'count' => $team1_cnt,
				'percentage' => $teams_cnt > 0 ? $team1_cnt / $teams_cnt : 0,//両チームの合計を基準とした数値をだす
				'percentage2' => $team1_cnt / $cnt_all,//マップ全体を基準にした数値
			],
			2 => [
				'num'	=> $team2num,
				'name'	=> $this->team->getTeamName($team2num),
				'color'	=> $this->team->getTeamColor($team2num),
				'count' => $team2_cnt,
				'percentage' => $teams_cnt > 0 ? $team2_cnt / $teams_cnt : 0,
				'percentage2' => $team2_cnt / $cnt_all,
			]
		];

		//$opponentExist = ($data[1]['count'] and $data[2]['count']);
			$opponentExist = true;
		$this->winteam = ($team1_cnt === $team2_cnt) ? false : (($data[1]['count'] < $data[2]['count']) ? [2, $data[2]['name']] : [1, $data[1]['name']]);
		if($opponentExist || $this->dev){
			$this->s->saveBattleData(1, $this->field, $data);
			$percentage1 = floor($data[1]['count'] / $cnt_all * 100);
			$percentage2 = floor($data[2]['count'] / $cnt_all * 100);
		
			$out = 	$this->lang->translateString("arearesult.team", [str_pad($data[1]['name'], 9), str_pad($data[1]['count'], 5)])."\n".
					$this->lang->translateString("arearesult.team", [str_pad($data[2]['name'], 9), str_pad($data[2]['count'], 5)])."\n";

			if($this->dev){
				if($this->winteam !== false && $animation){
					$this->BattleResultAnimation = new BattleResultAnimation($this, $data, $out);
				}else{
					$this->getServer()->broadcastMessage($out);
					if($animation){
						$this->Task['game'][$this->game + 1] = $this->getServer()->getScheduler()->scheduleDelayedTask(new TimeScheduler($this), 20*4);
					}
				}
				return true;
			}
		}else{
			//チームのどちらかがいない(塗っていない)とき
			$this->winteam = false;
			$out = $this->lang->translateString("result.team.absence")."\n";
		}
		if($this->winteam){
			$out .= $this->lang->translateString("timeTable.13.win", [$this->winteam[1]]);
		}else{
			$out .= $this->lang->translateString("timeTable.13.draw");
		}
		if($this->winteam && $animation){
			$this->BattleResultAnimation = new BattleResultAnimation($this, $data, $out);
		}else{
			$this->getServer()->broadcastMessage($out);
			if($animation){
				$this->Task['game'][$this->game + 1] = $this->getServer()->getScheduler()->scheduleDelayedTask(new TimeScheduler($this), 20*4);
			}
		}
	}


	public function announceMVP(){
		if($this->unfinished){
			return false;
		}
		$out = "§2≫ -*.=★ §fMVP Results§2 ★=.*- ≪\n";
		$teams = $this->team->getBattleTeamMember();
		$team_cnt = 0;
		foreach($teams as $team => $members){
			$mvp = [];
			$member_cnt = 0;
			foreach ($members as $member => $number){
				if(($player = Server::getInstance()->getPlayer($member)) instanceof Player){
					$data = $this->a->getData($member);
					if(!$mvp){
						$mvp = [$player, $data];
					}else{
						$mvp = ($mvp[1]->getPaintAmount() <= $data->getPaintAmount()) ? [$player, $data] : $mvp;
					}
					$member_cnt++;
				}
			}
			if($member_cnt > 1){
				if(isset($mvp[0])){
					$team_cnt++;
					$teamname = $this->team->getTeamName(array_search($team, $this->team->battleTeamNumber));
					$weaponName = $this->w->getweaponName($mvp[1]->getNowWeapon());
					$earn = floor($mvp[1]->getPaintAmount() * 0.1);
					$st = $this->team->getTeamColor(array_search($team, $this->team->battleTeamNumber));
					$out .= "  ".$this->lang->translateString("result.mvp", [ucfirst($teamname), $mvp[0]->getDisplayName(), $weaponName, $st, $mvp[1]->getPaintAmount().$st, $earn.$st])."\n";
					$mvp[1]->grantPoint(100);
					$mvp[0]->sendMessage($this->lang->translateString("result.mvp.target"));
				}
			}
		}
		$out .= "§2≫ -*.=★・.':*+.-.+*:'.・★=.*- ≪";
		if($team_cnt){
			$this->getServer()->broadcastMessage($out);
		}
	}

	//結果をアナウンス、戦績格納
	public function announcePlayerResults(){
		if($this->unfinished){
			return false;
		}
		$teams = $this->team->getBattleTeamMember();
		$winteam_num = $this->winteam[0];

		//塗ったブロック数に応じてkickと、データ確報のためのarrayを作る
		$block_cnt = 25;
		$members_data = [];
		$member_cnt = [1 => 0, 2 => 0];
		foreach($teams as $team => $members){
			foreach ($members as $member => $number){
				if(($player = Server::getInstance()->getPlayer($member)) instanceof Player){
					//放置などでいないプレイヤーを除く
					$playerData = $this->a->getData($member);
					if($playerData->getPaintAmount() >= $block_cnt){
						$members_data[$player->getName()] = $team;
						$member_cnt[$team]+=1;
					}else{
						//放置キック
						$message = $this->lang->translateString("battleLeft");
						$player->close($player->getLeaveMessage(), $message);
					}
				}
			}
		}
		foreach($members_data as $member => $team){
			//ラストの試合結果発表
			$playerData = $this->a->getData($member);
			$player = $this->getServer()->getPlayer($member);
			$blank = ($c = $this->team->getTeamMaxPlayer() - $member_cnt[$team]) > 0 ? $c : 0;
			unset($c);
			$gen_exp = 5;
			$gen_pt = 500;
			$win_exp = $winteam_num == $team ? 6 : 0;
			$win_pt = $winteam_num == $team ? 300 : 0;
			$fil_exp = ceil($playerData->getPaintAmount() / 200);
			$fil_pt = floor($playerData->getPaintAmount() * 0.1);
			$sum_exp = $gen_exp + $win_exp + $fil_exp;
			$sum_pt = $gen_pt + $win_pt + $fil_pt;
			$out = $this->lang->translateString("result.player", [
				str_pad($gen_exp, 4, " ", STR_PAD_LEFT), str_pad($gen_pt, 4, " ", STR_PAD_LEFT),
				str_pad($win_exp, 4, " ", STR_PAD_LEFT), str_pad($win_pt, 4, " ", STR_PAD_LEFT),
				str_pad($fil_exp, 4, " ", STR_PAD_LEFT), str_pad($fil_pt, 4, " ", STR_PAD_LEFT),
				str_pad($sum_exp, 4, " ", STR_PAD_LEFT), str_pad($sum_pt, 4, " ", STR_PAD_LEFT)
			]);

			//レベル上げ
			$moto = $playerData->getNowWeaponLevel();
			if($winteam_num == $team){
				$playerData->grantWin();
			}
			$playerData->grantPoint($sum_pt);
			$playerData->addCount();
			$this->a->savePaint($member, $playerData->getNowWeapon(), $playerData->getPaintAmount());
			$lv = $playerData->giveExp($sum_exp);
			if($lv != false){
				$out .= "\n".$this->lang->translateString("weapon.levelUp", [$moto, $lv]);
				$lvup_bonus = ($lv-$moto)*2500*(1+$lv/10);
				$out .= "\n§2レベルアップボーナス：§e".$lvup_bonus."pt§2獲得";
				$playerData->grantPoint($lvup_bonus);
			}
			$player->sendMessage($out);
		}
		$this->a->saveAll();
		return true;
	}


	//結果をアナウンス、戦績格納
	public function announceAreaPlayerResults(){
		if($this->unfinished){
			return false;
		}
		$teams = $this->team->getBattleTeamMember();
		$winteam_num = $this->winteam[0];

		//塗ったブロック数に応じてkickと、データ確報のためのarrayを作る
		$block_cnt = 25;
		$members_data = [];
		$member_cnt = [1 => 0, 2 => 0];
		$sum_rank = [1 => 0, 2 => 0];
		foreach($teams as $team => $members){
			foreach ($members as $member => $number){
				if(($player = Server::getInstance()->getPlayer($member)) instanceof Player){
					//放置などでいないプレイヤーを除く
					$playerData = $this->a->getData($member);
					if($playerData->getPaintAmount() >= $block_cnt){
						$members_data[$player->getName()] = $team;
						$member_cnt[$team]+=1;
						$sum_rank[$team] += $playerData->getRank();
					}else{
						//放置キック
						$message = $this->lang->translateString("battleLeft");
						$player->close($player->getLeaveMessage(), $message);
					}
				}
			}
		}
		foreach($members_data as $member => $team){
			//ラストの試合結果発表

			$playerData = $this->a->getData($member);
			$player = $this->getServer()->getPlayer($member);

			$teamnum = array_search($team, $this->team->battleTeamNumber);
			$team_cnt = 100-$this->area['count'][$teamnum]['c'];
			$cnt_pt = $team_cnt*15;
			$cnt_exp = floor($team_cnt*0.15);
			$win_pt = ($winteam_num == $team) ? 1500 : 0;
			$win_exp = ($winteam_num == $team) ? 15 : 0;
			$sum_pt = $cnt_pt+$win_pt;
			$sum_exp = $cnt_exp+$win_exp;
			$player->sendMessage("§2---ポイント獲得---");
			$player->sendMessage("§2".$team_cnt."カウント×15pt => §e".$cnt_pt."pt   §e".$cnt_exp."§2exp");
			$player->sendMessage("§2勝利ボーナス§e".$win_pt."pt   §e".$win_exp."§2exp");
			$player->sendMessage("§2------------");
			$player->sendMessage("§2計§e".$sum_pt."pt   §e".$sum_exp."exp§e獲得");
			$playerData->grantPoint($sum_pt);

			//レート計算
			if($member_cnt[1] == $member_cnt[2]){//メンバーの数が等しい時
				$av = ($team == 1) ? floor($sum_rank[2]/$member_cnt[2]) : floor($sum_rank[1]/$member_cnt[1]);
				$myrank = $playerData->getRank();
				$p = $myrank;
				if($winteam_num == $team){
					$p += 10;
					$p += floor(($av-$myrank)*0.05);
					$playerData->setRank($p);
				}else{
					$p -= 10;
					$p += floor(($myrank-$av)*0.05);
					$playerData->setRank($p);
				}
			}

			if($winteam_num == $team){
				$playerData->grantAreaWin();
			}

			$moto = $playerData->getNowWeaponLevel();
			$lv = $playerData->giveExp($sum_exp);
			if($lv != false){
				$player->sendMessage("".$this->lang->translateString("weapon.levelUp", [$moto, $lv]));
				$lvup_bonus = ($lv-$moto)*2500*(1+$lv/10);
				$player->sendMessage("§2レベルアップボーナス：§e".$lvup_bonus."pt§2獲得");
				$playerData->grantPoint($lvup_bonus);
			}

			$playerData->addAreaCount();
			$this->a->savePaint($member, $playerData->getNowWeapon(), $playerData->getPaintAmount());
		}
		$this->a->saveAll();
		return true;
	}


/*\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
	タイムスケジューラ
\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\*/

	/**
	 * ゲームを進行
	 */
	public function TimeTable(){
		if($this->gamestop) return false;
		$out = "";
		$this->game++;
		$c = $this->game;
		if(isset($this->Task['game'][$c])){
			$this->getServer()->getScheduler()->cancelTask($this->Task['game'][$c]->getTaskId());
			unset($this->Task['game'][$c]);
		}

		if($c == ($this->dev ? 12 : 15) && isset($this->Task['game']['skip'])){#(/gend使用時の)スキップするタスクを停止
			$this->getServer()->getScheduler()->cancelTask($this->Task['game']['skip']->getTaskId());
			unset($this->Task['game']['skip']);
		}
		if($this->dev == 2){
			switch($c){
				case 2:
					$this->Reset();
					$this->s->refleshData();
				case 3:
					$this->game = 3;
					if($this->Tips){
						$this->Tips = false;
						$this->getServer()->getScheduler()->cancelTask($this->Task['Tips']->getTaskId());
					}
					$out = "";
					if(empty($this->field)){
						$this->field = 13;
						if(!empty($this->nextfield)){
							$this->field = $this->nextfield;
							$this->nextfield = 0;
						}
					}
					$fielddata = $this->getBattleField($this->field);
					$this->ReconCheck();
					$this->resetBattleField($this->field);
					$out .= $this->lang->translateString("timeTable.3", [$fielddata['name'], $fielddata['comment'], $fielddata['author']]);
					$this->getServer()->broadcastMessage($out);
					$time = 10;
					$this->Task['game'][4] = $this->getServer()->getScheduler()->scheduleDelayedTask(new TimeScheduler($this), $time * 20);
					break;
				case 4:
					$out = "";
					$this->team->member = [];
					$this->team->BattlecountReset();
					//$rand_team = range(1, $this->team->getTeamCount());

					$rand_team = [2, 4];
					//shuffle($rand_team);
					$teams_check = 0;
					foreach(Server::getInstance()->getOnlinePlayers() as $player){
						//$te = $rand_team[$teams_check % 2];
						//$teams_check++;
						$te = 4;
						$this->team->addMember($te, $player->getName(), true);
						$this->changeName($player);
					}
					if(empty($this->team->member[$rand_team[0]])) $this->team->member[$rand_team[0]] = [];
					if(empty($this->team->member[$rand_team[1]])) $this->team->member[$rand_team[1]] = [];
					$out .= $this->lang->translateString("timeTable.4");

					$teams = [$rand_team[0], $rand_team[1]];

					foreach($teams as $index => $team_num){
						$this->team->setBattleTeam($team_num);
						$out .= "\n".$this->team->getTeamMember($team_num);
					}
					$this->getServer()->broadcastMessage($out);
				case 6:
					//テレポート
					$this->team->setBattleTeamMember();
					$this->TpTeamBattleField(true);
					$this->giveWeaponForBattle(-1);
					//$this->getServer()->broadcastMessage("timeTable.6");
					$this->getServer()->broadcastMessage("§aミッション：敵を100体倒せ！");
					$this->InkChargeStart(2);
				case 7:
				case 8:
				case 9:
					//Ready?
					$this->game = 9;
					$this->TPanimationEnd();
					$out = $this->lang->translateString("timeTable.7");
					$this->getServer()->broadcastMessage($out);
					break;
				case 10:
					$this->InkChargeStop(true);
					$this->Timelimit = $time = 0;
					$out = $this->lang->translateString("timeTable.10", ["---"]);
					$this->getServer()->broadcastMessage($out);
					$this->startRepeating($time);
					$this->startGame();
					$level = Server::getInstance()->getDefaultLevel();
					for($i=0; $i < 4; $i++){ 
						$pos = Enemy::getRandomPos();
						Nitron::summon($level, $pos[0], $pos[1], $pos[2], 0);
					}
					for($i=0; $i < 2; $i++){ 
						$pos = Enemy::getRandomPos();
						Charpse::summon($level, $pos[0], $pos[1], $pos[2], 0);
					}
					for($i=0; $i < 1; $i++){ 
						$pos = Enemy::getRandomPos();
						Brupse::summon($level, $pos[0], $pos[1], $pos[2], 0);
					}
					for($i=0; $i < 1; $i++){ 
						$pos = Enemy::getRandomPos();
						Ambuffa::summon($level, $pos[0], $pos[1], $pos[2], 0);
					}
					$this->killCount = 0;
					$this->deathCount = 0;
					$this->kc = [];
					$this->starttime = microtime(true);
					break;
				case 11:
					$now = microtime(true);
					$this->ti = $now - $this->starttime;
					$hun = floor($this->ti/60);
					$byou = $this->ti%60;
					$this->getServer()->broadcastMessage("§bクリアタイム：".$hun."分".$byou."秒");
					$this->stopGame();
					$this->stopRepeating();
					$this->stopRespawnTask();
					$this->DespawnMikataStatus();
					$out = $this->lang->translateString("timeTable.11");
					$this->getServer()->broadcastMessage($out);
					$this->Task['game'][$c + 1] = $this->getServer()->getScheduler()->scheduleDelayedTask(new TimeScheduler($this), 20*4);
					
					break;
				case 12:
					//$this->delAllBattleItem();
				case 13:
					$this->TpTeamLobby();
					//$this->announceBattleResults(false);
					foreach(Server::getInstance()->getOnlinePlayers() as $player){
						if($this->team->getTeamOf($player->getName()) !== 0){
							$player->sendTitle("§aミッションコンプリート！");
							$user = $player->getName();
							$playerData = Account::getInstance()->getData($user);
							$playerData->grantPoint(10000);
							$player->sendMessage("§a成功報酬：§e10000pt§a獲得！");
							if($this->ti < 900){
								$playerData->grantPoint(5000);
								$player->sendMessage("§bクリアタイム15分未満：スピードクリア達成");							
								$player->sendMessage("§aスピードクリアボーナス：§e5000pt§a獲得！");							
							}
							$player->sendMessage("§aキル：".$this->kc[$user]["kill"]);
							$player->sendMessage("§cデス：".$this->kc[$user]["death"]);
						}else{
							$player->sendMessage("§aミッションコンプリート！");
						}
					}
				case 14:
				case 15:
				case 16:
					$this->team->member = [];
					$this->team->laterRemoveMember();
					$this->Watch_end();
					$this->Camtpr();
					$this->Reset();
					$this->PlayersMoveCancel(false);
					$this->FloatText(true);
					//$this->itemCase->resetAll();
					$this->shop->resetAll();
					$msg = $this->lang->translateString("command.dev.end");
					$this->getServer()->broadcastMessage("§2≫ ".$msg);
					foreach(Server::getInstance()->getOnlinePlayers() as $player){
						$this->changeName($player);
					}
					$this->dev = false;
					if(!$this->Tips){
						$this->Tips = true;
						$this->Task['Tips'] = $this->getServer()->getScheduler()->scheduleRepeatingTask(new Random($this), 20*75);
					}
					Server::getInstance()->getDefaultLevel()->unloadChunks(true);
					break;
			}
		}else if($this->dev){
			switch($c){
				case 2:
					$this->Reset();
					$this->s->refleshData();
				case 3:
					$this->game = 3;
					if($this->Tips){
						$this->Tips = false;
						$this->getServer()->getScheduler()->cancelTask($this->Task['Tips']->getTaskId());
					}
					$out = "";
					if(empty($this->field)){
						$this->field = 13;
						if(!empty($this->nextfield)){
							$this->field = $this->nextfield;
							$this->nextfield = 0;
						}
					}
					$fielddata = $this->getBattleField($this->field);
					$this->ReconCheck();
					$this->resetBattleField($this->field);
					$out .= $this->lang->translateString("timeTable.3", [$fielddata['name'], $fielddata['comment'], $fielddata['author']]);
					$this->getServer()->broadcastMessage($out);
					$time = 10;
					$this->Task['game'][4] = $this->getServer()->getScheduler()->scheduleDelayedTask(new TimeScheduler($this), $time * 20);
					break;
				case 4:
					$out = "";
					$this->team->member = [];
					$this->team->BattlecountReset();
					$rand_team = range(1, $this->team->getTeamCount());
					shuffle($rand_team);
					$teams_check = 0;
					foreach(Server::getInstance()->getOnlinePlayers() as $player){
						$te = $rand_team[$teams_check % 2];
						$teams_check++;
						$this->team->addMember($te, $player->getName(), true);
						$this->changeName($player);
					}
					if(empty($this->team->member[$rand_team[0]])) $this->team->member[$rand_team[0]] = [];
					if(empty($this->team->member[$rand_team[1]])) $this->team->member[$rand_team[1]] = [];
					$out .= $this->lang->translateString("timeTable.4");

					$teams = [$rand_team[0], $rand_team[1]];

					foreach($teams as $index => $team_num){
						$this->team->setBattleTeam($team_num);
						$out .= "\n".$this->team->getTeamMember($team_num);
					}
					$this->getServer()->broadcastMessage($out);
				case 6:
					//テレポート
					$this->team->setBattleTeamMember();
					$this->TpTeamBattleField(false);
					$this->giveWeaponForBattle(3);
					$this->getServer()->broadcastMessage("timeTable.6");
					$this->InkChargeStart(2);
				case 7:
				case 8:
				case 9:
					//Ready?
					$this->game = 9;
					$this->TPanimationEnd();
					$out = $this->lang->translateString("timeTable.7");
					$this->getServer()->broadcastMessage($out);
					break;
				case 10:
					$this->InkChargeStop(true);
					$this->Timelimit = $time = 0;
					$out = $this->lang->translateString("timeTable.10", ["---"]);
					$this->getServer()->broadcastMessage($out);
					$this->startRepeating($time);
					$this->startGame();
					break;
				case 11:
					$this->stopGame();
					$this->stopRepeating();
					$this->stopRespawnTask();
					$this->DespawnMikataStatus();
					$out = $this->lang->translateString("timeTable.11");
					$this->getServer()->broadcastMessage($out);
					$this->Task['game'][$c + 1] = $this->getServer()->getScheduler()->scheduleDelayedTask(new TimeScheduler($this), 20*4);
					
					break;
				case 12:
					//$this->delAllBattleItem();
				case 13:
					$this->TpTeamLobby();
					$this->announceBattleResults(false);
				case 14:
				case 15:
				case 16:
					$this->team->member = [];
					$this->team->laterRemoveMember();
					$this->Watch_end();
					$this->Camtpr();
					$this->Reset();
					$this->PlayersMoveCancel(false);
					$this->FloatText(true);
					//$this->itemCase->resetAll();
					$this->shop->resetAll();
					$msg = $this->lang->translateString("command.dev.end");
					$this->getServer()->broadcastMessage("§2≫ ".$msg);
					foreach(Server::getInstance()->getOnlinePlayers() as $player){
						$this->changeName($player);
					}
					$this->dev = false;
					if(!$this->Tips){
						$this->Tips = true;
						$this->Task['Tips'] = $this->getServer()->getScheduler()->scheduleRepeatingTask(new Random($this), 20*75);
					}
					Server::getInstance()->getDefaultLevel()->unloadChunks(true);
					break;
			}
		}else if($this->area['mode']){
			switch($c){
				case 2:
					$this->s->refleshData();
					$this->FloatText(true);
					if($this->entry->getEntryNum() < 2){
						$this->game = 1;
						$this->Task['game'] = [];
						return false;
					}
					$this->entry->PreintoEntry();
					$this->getServer()->broadcastMessage($this->lang->translateString("timeTable.2"));
					$this->Task['game'][$c + 1] = $this->getServer()->getScheduler()->scheduleDelayedTask(new TimeScheduler($this), 20*3);
					break;
				case 3:
					//フィールド決定
					if($this->entry->getEntryNum() > 1){
						if($this->error){
							$this->error = 0;
							$this->game = 2;
							$this->Task['game'][$c] = $this->getServer()->getScheduler()->scheduleDelayedTask(new TimeScheduler($this), 20*3);
							return false;
						}
						$teamcnt = $this->entry->getEntryNum();
						//フィールドを選択
						if(empty($this->field)){
							if(empty($this->nextfield)){
								$this->field = $this->s->chooseField();
							}else{
								$this->field = $this->nextfield;
								$this->nextfield = 0;
							}
						}
						$fielddata = $this->getBattleField($this->field);
						$out .= $this->lang->translateString("timeTable.3", [$fielddata['name'], $fielddata['comment'], $fielddata['author']]);
						$this->getServer()->broadcastMessage($out);
						/*
						試合するチームの人数に空きがない = 10秒
						空きあり + 人数差なし        = 15秒
						人数差あり                 = 20秒
						*/
						$time = ($teamcnt == 8) ? 10 : 15;
						$this->Task['game'][$c + 1] = $this->getServer()->getScheduler()->scheduleDelayedTask(new TimeScheduler($this), $time * 20);
					}else{
						$this->game = 2;
						if(time() - $this->error >= 30){
							$out = $this->lang->translateString("timeTable.error.bias");
							$this->getServer()->broadcastMessage($out);
							$this->error = time();
							return true;
						}
						return false;
					}
					break;
				case 4:
					//試合するフィールドの羊毛をリセット、試合メンバー決定
					$this->ReconCheck();
					$this->resetBattleField($this->field);
					$teams = $this->entry->choiceBattleMember();
					if($teams and 2 == count($teams)){
						if($this->error){
							$this->error = 0;
							$this->game = 3;
							$this->Task['game'][$c] = $this->getServer()->getScheduler()->scheduleDelayedTask(new TimeScheduler($this), 20*15);
							return false;
						}
						$this->SetAreaData($teams);
						$out .= $this->lang->translateString("timeTable.4");
						foreach($teams as $index => $team_num){
							$this->team->setBattleTeam($team_num);
							$out .= "\n".$this->team->getTeamMember($team_num);
							$members = $this->team->getTeamMember($team_num, true);
							$mes = "";
							foreach($members as $member){
								$playerData = $this->a->getData($member);
								$weapon = $playerData->getNowWeapon($member);
								$weapon_name = $this->w->getweaponName($weapon);
								$subweapon = $this->w->getSubWeaponNumFromWeapon($weapon);
								$subweap_name = $this->w->getSubWeaponName($subweapon);
								$mes .= "\n".$member." ".$weapon_name."(".$subweap_name.")";
							}
							foreach($members as $member){
								$player = $this->getServer()->getPlayer($member);
								if($player instanceof Player){
									$player->sendMessage($mes);
								}
							}
						}
						$this->getServer()->broadcastMessage($out);
					}else{
						$this->game = 3;
						if(time() - $this->error >= 30){
							$out = $this->lang->translateString("timeTable.error.bias");
							$this->getServer()->broadcastMessage($out);
							$this->error = time();
							return true;
						}
						return false;
					}
				case 5:
					$this->game = 5;
					//(音楽終了後に自動で進行します)
					$this->Departing = new Departing($this, 18, true);
					break;
				case 6:
					$this->Departing->close(false);
					//フィールドにテレポート
					$this->team->setBattleTeamMember();
					$this->TpTeamBattleField(true);
					$this->giveWeaponForBattle(0);
					$this->getServer()->broadcastMessage($out);
					$this->setFloatText([0]);
					break;
				case 7:
				case 8:
				case 9:
					//Ready?
					$this->game = 9;
					$this->TPanimationEnd();
					$this->InkChargeStart(3);
					$out = $this->lang->translateString("timeTable.7");

					$players = $this->getServer()->getOnlinePlayers();
					foreach($players as $player){
						if($this->team->getTeamOf($player->getName()) !== 0){
							$player->sendTitle("§cReady?");
						}else{
							$player->sendMessage($out);
						}
					}
					//$this->getServer()->broadcastMessage($out);

					break;
				case 10:
					//スタート
					$this->InkChargeStop(true);
					$time = $this->getTimeLimit();
					$this->Timelimit = $time;
					$out = $this->lang->translateString("timeTable.10", [$time]);
					$players = $this->getServer()->getOnlinePlayers();
					foreach($players as $player){
						if($this->team->getTeamOf($player->getName()) !== 0){
							$player->sendTitle("§cGO!","(制限時間: " . $time . "秒)", 0);
						}else{
							$player->sendMessage($out);
						}
					}
					//$this->getServer()->broadcastMessage($out);
					$this->startRepeating($time);
					$this->startGame();
					break;
				case 11:
					//試合終了
					$this->stopGame();
					$this->stopRepeating();
					$this->stopRespawnTask();
					$this->DespawnMikataStatus();
					$out = $this->lang->translateString("timeTable.11");
					$players = $this->getServer()->getOnlinePlayers();
					foreach($players as $player){
						if($this->team->getTeamOf($player->getName()) !== 0){
							$player->sendTitle("§cFINISH!");
						}else{
							$player->sendMessage($out);
						}
					}
					//$this->getServer()->broadcastMessage($out);
					$this->Task['game'][$c + 1] = $this->getServer()->getScheduler()->scheduleDelayedTask(new TimeScheduler($this), 20*4);
					break;
				case 12:
					if($this->unfinished){
						$out = $this->lang->translateString("result.discontinuation");
					}else{
						$out = $this->lang->translateString("timeTable.12");
					}
					//$this->delAllBattleItem();
					$this->getServer()->broadcastMessage($out);
					$this->Task['game'][$c + 1] = $this->getServer()->getScheduler()->scheduleDelayedTask(new TimeScheduler($this), 20*2);
					break;
				case 13:
					//結果発表
					$this->announceAreaBattleResults(true);
					break;
				case 14:
					//リスポにテレポート
					$this->BattleResultAnimation = null;
					$this->TpTeamLobby();
					$this->Task['game'][$c + 1] = $this->getServer()->getScheduler()->scheduleDelayedTask(new TimeScheduler($this), 20*4);
					break;
				case 15:
					//試合を観戦していた人をテレポート、MVPを発表
					$this->Watch_end();
					$this->Camtpr();
					//$this->announceMVP();
					$this->Task['game'][$c + 1] = $this->getServer()->getScheduler()->scheduleDelayedTask(new TimeScheduler($this), 20*2.5);
					$this->PlayersMoveCancel(false);
					break;
				case 16:
					//試合をしていたプレイヤーにリザルトを送信
					#デバッグ用
					$this->announceAreaPlayerResults();
					$this->Task['game'][$c + 1] = $this->getServer()->getScheduler()->scheduleDelayedTask(new TimeScheduler($this), 20*3);
					break;
				case 17:
					//試合データなどをリセット
					$this->Reset();
					$this->FloatText(true);
					//$this->itemCase->resetAll();
					$this->shop->resetAll();
					if(!$this->isReady()){
						$msg = $this->lang->translateString("timeTable.15.wait");
						$this->getServer()->broadcastMessage("§2≫ ".$msg);
					}
					Server::getInstance()->getDefaultLevel()->unloadChunks(true);
					break;
			}
		}else{
			switch($c){
				case 2:
					$this->s->refleshData();
					$this->FloatText(true);
					if($this->entry->getEntryNum() < 2){
						//チームに参加している合計人数が2人未満の場合、1に戻す
						$this->game = 1;
						$this->Task['game'] = [];
						return false;
					}
					$this->entry->PreintoEntry();
					$this->getServer()->broadcastMessage($this->lang->translateString("timeTable.2"));
					$this->Task['game'][$c + 1] = $this->getServer()->getScheduler()->scheduleDelayedTask(new TimeScheduler($this), 20*3);
					break;
				case 3:
					//フィールド決定
					//$notice_empty = "";
					if($this->entry->getEntryNum() > 1){
						if($this->error){
							$this->error = 0;
							$this->game = 2;
							$this->Task['game'][$c] = $this->getServer()->getScheduler()->scheduleDelayedTask(new TimeScheduler($this), 20*3);
							return false;
						}
						$teamcnt = $this->entry->getEntryNum();
						//フィールドを選択
						if(empty($this->field)){
							if(empty($this->nextfield)){
								$this->field = $this->s->chooseField();
							}else{
								$this->field = $this->nextfield;
								$this->nextfield = 0;
							}
						}
						$fielddata = $this->getBattleField($this->field);
						$out .= $this->lang->translateString("timeTable.3", [$fielddata['name'], $fielddata['comment'], $fielddata['author']]);
						$this->getServer()->broadcastMessage($out);
						/*
						試合するチームの人数に空きがない = 10秒
						空きあり + 人数差なし        = 15秒
						人数差あり                 = 20秒
						*/
						$time = ($teamcnt == 8) ? 10 : 15;
						$this->Task['game'][$c + 1] = $this->getServer()->getScheduler()->scheduleDelayedTask(new TimeScheduler($this), $time * 20);
					}else{
						$this->game = 2;
						if(time() - $this->error >= 30){
							$out = $this->lang->translateString("timeTable.error.bias");
							$this->getServer()->broadcastMessage($out);
							$this->error = time();
							return true;
						}
						return false;
					}
					break;
				case 4:
					//試合するフィールドの羊毛をリセット、試合メンバー決定
					$this->ReconCheck();
					$this->resetBattleField($this->field);
					$teams = $this->entry->choiceBattleMember();
					if($teams and 2 == count($teams)){
						if($this->error){
							$this->error = 0;
							$this->game = 3;
							$this->Task['game'][$c] = $this->getServer()->getScheduler()->scheduleDelayedTask(new TimeScheduler($this), 20*15);
							return false;
						}
						$out .= $this->lang->translateString("timeTable.4");
						foreach($teams as $index => $team_num){
							$this->team->setBattleTeam($team_num);
							$out .= "\n".$this->team->getTeamMember($team_num);
							$members = $this->team->getTeamMember($team_num, true);
							$mes = "";
							foreach($members as $member){
								$playerData = $this->a->getData($member);
								$weapon = $playerData->getNowWeapon($member);
								$weapon_name = $this->w->getweaponName($weapon);
								$subweapon = $this->w->getSubWeaponNumFromWeapon($weapon);
								$subweap_name = $this->w->getSubWeaponName($subweapon);
								$mes .= "\n".$member." ".$weapon_name."(".$subweap_name.")";
							}
							foreach($members as $member){
								$player = $this->getServer()->getPlayer($member);
								if($player instanceof Player){
									$player->sendMessage($mes);
								}
							}
						}


						$this->getServer()->broadcastMessage($out);
						//$time = 15;
						//$time = 5;
						//$time = 12;
						//$this->Task['game'][$c + 1] = $this->getServer()->getScheduler()->scheduleDelayedTask(new TimeScheduler($this), 20*$time);
					}else{
						$this->game = 3;
						if(time() - $this->error >= 30){
							$out = $this->lang->translateString("timeTable.error.bias");
							$this->getServer()->broadcastMessage($out);
							$this->error = time();
							return true;
						}
						return false;
					}
				case 5:
					$this->game = 5;
					//(音楽終了後に自動で進行します)
					$this->Departing = new Departing($this, 18, true);
					break;
				case 6:
					$this->Departing->close(false);
					//フィールドにテレポート
					$this->team->setBattleTeamMember();
					$this->TpTeamBattleField(true);
					$this->giveWeaponForBattle(0);
					$this->getServer()->broadcastMessage($out);
					$this->setFloatText([0]);
					break;
				case 7:
				case 8:
				case 9:
					//Ready?
					$this->game = 9;
					$this->TPanimationEnd();
					$this->InkChargeStart(3);
					$out = $this->lang->translateString("timeTable.7");
					$players = $this->getServer()->getOnlinePlayers();
					foreach($players as $player){
						if($this->team->getTeamOf($player->getName()) !== 0){
							$player->sendTitle("§cReady?");
						}else{
							$player->sendMessage($out);
						}
					}
					//$this->getServer()->broadcastMessage($out);
					break;
				case 10:
					//スタート
					$this->InkChargeStop(true);
					$time = $this->getTimeLimit();
					$this->Timelimit = $time;
					$out = $this->lang->translateString("timeTable.10", [$time]);
					$players = $this->getServer()->getOnlinePlayers();
					foreach($players as $player){
						if($this->team->getTeamOf($player->getName()) !== 0){
							$player->sendTitle("§cGO!","(制限時間: " . $time . "秒)");
						}else{
							$player->sendMessage($out);
						}
					}
					//$this->getServer()->broadcastMessage($out);
					$this->startRepeating($time);
					$this->startGame();
					break;
				case 11:
					//試合終了
					$this->stopGame();
					$this->stopRepeating();
					$this->stopRespawnTask();
					$this->DespawnMikataStatus();
					$out = $this->lang->translateString("timeTable.11");
					$players = $this->getServer()->getOnlinePlayers();
					foreach($players as $player){
						if($this->team->getTeamOf($player->getName()) !== 0){
							$player->sendTitle("§cFINISH!");
						}else{
							$player->sendMessage($out);
						}
					}
					//$this->getServer()->broadcastMessage($out);
					$this->Task['game'][$c + 1] = $this->getServer()->getScheduler()->scheduleDelayedTask(new TimeScheduler($this), 20*4);
					break;
				case 12:
					if($this->unfinished){
						$out = $this->lang->translateString("result.discontinuation");
					}else{
						$out = $this->lang->translateString("timeTable.12");
					}
					//$this->delAllBattleItem();
					$this->getServer()->broadcastMessage($out);
					$this->Task['game'][$c + 1] = $this->getServer()->getScheduler()->scheduleDelayedTask(new TimeScheduler($this), 20*2);
					break;
				case 13:
					//結果発表
					$this->announceBattleResults(true);
					break;
				case 14:
					//リスポにテレポート
					$this->BattleResultAnimation = null;
					$this->TpTeamLobby();
					$this->Task['game'][$c + 1] = $this->getServer()->getScheduler()->scheduleDelayedTask(new TimeScheduler($this), 20*4);
					break;
				case 15:
					//試合を観戦していた人をテレポート、MVPを発表
					$this->Watch_end();
					$this->Camtpr();
					$this->announceMVP();
					$this->Task['game'][$c + 1] = $this->getServer()->getScheduler()->scheduleDelayedTask(new TimeScheduler($this), 20*2.5);
					$this->PlayersMoveCancel(false);
					break;
				case 16:
					//試合をしていたプレイヤーにリザルトを送信
					#デバッグ用
					$this->announcePlayerResults();
					$this->Task['game'][$c + 1] = $this->getServer()->getScheduler()->scheduleDelayedTask(new TimeScheduler($this), 20*3);
					break;
				case 17:
					//試合データなどをリセット
					$this->Reset();
					$this->FloatText(true);
					//$this->itemCase->resetAll();
					$this->shop->resetAll();
					if(!$this->isReady()){
						$msg = $this->lang->translateString("timeTable.15.wait");
						$this->getServer()->broadcastMessage("§2≫ ".$msg);
					}
					Server::getInstance()->getDefaultLevel()->unloadChunks(true);
					break;
			}
		}
		return true;
	}

	/**
	 * ゲームを終了する (試合が開始していない場合、開始する前の状態に戻す)
	 */
	public function GameEnd(){
		if($this->game < 10){
			switch($this->game){
				case 9:
				case 8:
				case 7:
					$this->InkChargeStop(true);
				case 6:
					$this->TPanimationEnd();
					//$this->delAllBattleItem();
					$this->TpTeamLobby($this->field);
				case 5:
				case 4:
					if(isset($this->Departing)){
						$this->Departing->close(false);
					}
					break;
			}
			$this->team->laterRemoveMember();
			$this->Watch_end();
			$this->Camtpr();
			$this->Reset();
			$this->FloatText(true);
			//$this->itemCase->resetAll();
			$this->shop->resetAll();
			$this->PlayersMoveCancel(false);
			$this->Task['game'][$this->game + 1] = $this->getServer()->getScheduler()->scheduleDelayedTask(new TimeScheduler($this, true), 20 * 3);
		}else{
			$this->Task['game']['skip'] = $this->getServer()->getScheduler()->scheduleRepeatingTask(new TimeScheduler($this), 15);
		}
	}

	/**
	 * ミュートしていないプレイヤーを取得
	 *
	 * @param Player[] $players
	 */
	public function getNonmutePlayers($players = null, $name = null){
		if(!is_array($players)){
			$players = Server::getInstance()->getOnlinePlayers();
		}
		if($name == null){
			foreach($players as $index => $player){
				if(isset($this->mute_personal[$player->getName()])){
					unset($players[$index]);
				}
			}
		}else{
			foreach($players as $index => $player){
				if($player instanceof Player){
					$user = $player->getName();
					$playerData = Account::getInstance()->getData($user);
					if(isset($this->mute_personal[$user])){
						unset($players[$index]);
					}
					if($playerData->isMuteList($name)){
						unset($players[$index]);
					}
				}
			}
		}
		return array_values($players);
	}

	/**
	 * クリエイティブモードでの特定のアイテムを非表示に
	 */
	public function CreativeItemdelete(){
		Item::removeCreativeItem(Item::get(383, 15));
		Item::removeCreativeItem(Item::get(383, 17));
		Item::removeCreativeItem(Item::get(383, 32));
		Item::removeCreativeItem(Item::get(332));
	}
}

class TimeScheduler extends PluginTask{
	
	public function __construct(PluginBase $owner, $forceProceed = false){
		parent::__construct($owner);
		$this->forceProceed = $forceProceed;
	}

	public function onRun($tick){
		if($this->forceProceed){
			$this->getOwner()->gamestop = false;
		}
		$this->getOwner()->TimeTable();
	}
}

class Scanner extends PluginTask{

	public function onRun($tick){
		$this->getOwner()->broadcastScan();
	}
}

class AreaScanner extends PluginTask{

	public function __construct(PluginBase $owner, $sec = 5){
		parent::__construct($owner);
		$this->count = 0;;
	}

	public function onRun($tick){
		$this->count++;
		$this->getOwner()->AreabroadcastScan($this->count);
	}
}

class Count extends PluginTask{

	public function onRun($tick){
		$this->getOwner()->count_time++;
	}
}

class Inkcharge extends PluginTask{

	public function __construct(PluginBase $owner, $sec = 5){
		parent::__construct($owner);
		$this->count = 0;
		$this->plus = (100 / 20 / $sec);
	}

	public function onRun($tick){
		$this->count+=$this->plus;
		
		if($this->count >= 100){
			$this->count = 100;
			$this->getOwner()->InkChargeStop(false);
			$this->getOwner()->TimeTable();
		}
		$this->getOwner()->PlayersInkCharge($this->count);
	}
}

class Respawn extends PluginTask{

	public function __construct(PluginBase $owner, Player $player){
		parent::__construct($owner);
		$this->player = $player;
		$this->count = 0;
		$this->max_count = ($owner->area['mode'] == true) ? 90 : 120;
		$t = Gadget::getCorrection($player, Gadget::RESPAWN_TIME);
		$this->max_count *= $t;
	}

	public function onRun($tick){
		$this->count+= 1;
		if($this->count >= $this->max_count){
			$this->count = $this->max_count;
			if(isset($this->getOwner()->Task['Respawn'][$this->player->getName()])){
				Server::getInstance()->getScheduler()->cancelTask($this->getOwner()->Task['Respawn'][$this->player->getName()]->getTaskId());
				unset($this->getOwner()->Task['Respawn'][$this->player->getName()]);
			}
		}
		$this->getOwner()->PlayerInkCharge($this->player, $this->count, $this->max_count);
	}
}

class PositionCheck extends PluginTask{

	public function __construct(PluginBase $owner){
		parent::__construct($owner);
		$this->count = 0;
	}

	public function onRun($tick){
		$this->getOwner()->addTeamColorParticle($tick);
		if(($this->count % 5) === 0){
			$this->getOwner()->BattlePlayersPositionCheck();
		}
		if(($this->count % 10) === 0){
			if($this->getOwner()->area['mode']){
				$this->getOwner()->addAreaParticle($this->count);
			}
		}
		$this->count+=1;
	}
}

class Random extends PluginTask{
	public function __construct(PluginBase $owner){
		parent::__construct($owner);
		$this->start = false;
	}

	public function onRun($tick){
		if($this->start){
			if($this->getOwner()->Tips && count(Server::getInstance()->getOnlinePlayers())) $this->getOwner()->randomBroad();
		}else{
			$this->start = true;
		}
	}
}

class GameEnd extends PluginTask{
	public function onRun($tick){
		if( $this->getOwner()->count_time - time() > 0){
			$this->getOwner()->count_time -= mt_rand(3, 5);
			$this->getOwner()->broadcastScan();
		}else{
			Server::getInstance()->getScheduler()->cancelTask($this->getOwner()->Task['game']['end']->getTaskId());
			$this->getOwner()->GameEnd();
		}
	}
}

class TryPaintTask extends PluginTask{
	public function __construct(PluginBase $owner, Player $player){
		parent::__construct($owner);
		$this->player = $player;
		$this->count = 0;
	}

	public function onRun($tick){
		if(($this->count % 5) === 0){
			$this->getOwner()->TryPaint_TimeCheck($this->player);
		}
		$this->count+=1;
	}
}

class tprCheckTask extends PluginTask{
	public function onRun($tick){
		$this->getOwner()->tprCheck();
	}
}


class CountTimeData{

	public function __construct($count_time, $start_time){
		$this->count_time = $count_time;
		$this->start_time = $start_time;
		$this->time = time();
	}

	public function getMinValue(){
		return 0;
	}

	public function getMaxValue(){
		return $this->count_time - $this->start_time;
	}

	public function getValue(){
		return $this->count_time - $this->time;
	}

	public function getDefaultValue(){
		return 0;
	}
	
	public function getName(){
		return "minecraft:health";
	}

}


class InkTank{

	public function __construct($ink){
		$this->ink = $ink;
	}

	public function getMinValue(){
		return 0;
	}

	public function getMaxValue(){
		return 1;
	}

	public function getValue(){
		return $this->ink;
	}

	public function getDefaultValue(){
		return 0;
	}
	
	public function getName(){
		return "minecraft:player.experience";
	}

}

class HideEnemysNametag extends PluginTask{
	public function onRun($tick){
		$this->getOwner()->allHideEnemysNametag();
	}
}
