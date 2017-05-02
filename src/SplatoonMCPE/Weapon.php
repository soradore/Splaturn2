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

use pocketmine\block\Block;

use pocketmine\entity\Attribute;
use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\entity\Egg;
use pocketmine\entity\Silverfish;
use pocketmine\entity\Projectile;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\entity\Creeper;

use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\level\Location;
use pocketmine\level\Explosion;
use pocketmine\level\MovingObjectPosition;
use pocketmine\level\format\FullChunk;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;

use pocketmine\level\particle\Particle;
use pocketmine\level\particle\BubbleParticle;
use pocketmine\level\particle\ItemBreakParticle;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\level\particle\InkParticle;
use pocketmine\level\particle\TerrainParticle;
use pocketmine\level\particle\DustParticle;
use pocketmine\level\particle\CriticalParticle;
use pocketmine\level\sound\LaunchSound;
use pocketmine\level\sound\ExplodeSound;
use pocketmine\level\sound\EndermanTeleportSound;
use pocketmine\level\sound\SplashSound;
use pocketmine\level\sound\AnvilFallSound;

use pocketmine\item\Item;

use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Math;
use pocketmine\math\Vector3;

use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\ExplodePacket;
use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\network\protocol\SetEntityMotionPacket;
use pocketmine\network\protocol\SetEntityDataPacket;
use pocketmine\network\protocol\LevelEventPacket;

use pocketmine\scheduler\Task;

use pocketmine\level\sound\GenericSound;

class Weapon{

	const TYPE_SHOOTER = 1;
	const TYPE_ROLLER = 2;
	const TYPE_CHARGER = 3;
	const TYPE_SLOSHER = 4;
	const TYPE_SPLATLING = 5;

	const PABLO = 1;
	const SPLAT_ROLLER = 2;
	const DYNAMO_ROLLER = 3;
	const SPLATTERSHOT = 4;
	const SPLATTERSHOT_JR = 5;
	const SPLAT_CHARGER = 6;
	const SPLOOSH_O_MATIC = 7;
	const SLOSHER = 8;
	const SPLATTERSHOT_PRO = 9;
	const GAL_96 = 10;
	const GAL_52 = 11;
	const DUAL_SQUELCHER = 12;
	const OCTOBRUSH = 13;
	const HEAVY_SPLATLING = 14;
	const MINI_SPLATLING = 15;
	const BRUSHWASHER = 16;
	const SPLASH_O_MATIC = 17;
	const SPLAT_LADLE = 18;
	const HYDRA_SPLATLING = 19;
	const LUNA_BLASTER = 20;
	const L3_NOZZLENOSE = 21;
	const T3_NOZZLENOSE = 22;
	const SPLAT_UMBRELLA = 23;
	const AEROSPRAY_PG = 24;
	const WILLEM = 25;
	const SPLAT_LADLE_CUSTOM = 26;
	const SPLAT_ROLLER_COLLABO = 27;
	const GOLD_DYNAMO_ROLLER = 28;
	const SPLATTERSHOT_COLLABO = 29;
	const SPLAT_CHARGER_WAKAME = 30;
	const SPLOOSH_O_MATIC_SEVEN = 31;
	const SLOSHER_DECO = 32;
	const SPLATTERSHOT_PRO_BERRY = 33;
	const GAL_96_DECO = 34;
	const DUAL_SQUELCHER_CUSTOM = 35;
	const OCTOBRUSH_HEW = 36;
	const HEAVY_SPLATLING_REMIX = 37;
	const MINI_SPLATLING_REPAIR = 38;
	const SPLASH_O_MATIC_NEO = 39;
	const HYDRA_SPLATLING_CUSTOM = 40;
	const DELTA_SQUELCHER_M = 41;
	const PABLO_HEW = 42;
	const AEROSPRAY_RG = 43;
	const DELTA_SQUELCHER_T = 44;
	const L3_NOZZLENOSE_D = 45;
	const T3_NOZZLENOSE_D = 46;
	const SPLATTERSHOT_JR_MOMIJI = 47;
	const WILLEM_HEW = 48;
	const PERMANENT_PABLO = 49;
	const SPLATTERSHOT_WASABI = 50;
	const SPLAT_ROLLER_COROCORO = 51;
	const SPLAT_CHARGER_BENTO = 52;
	const SLOSHER_SODA = 53;
	const MINI_SPLATLING_COLLABO = 54;
	const SPLAT_LADLE_NECRO = 55;
	const LONG_BLASTER = 56;
	const SPLOOSH_O_MATIC_NEO = 57;
	const DELTA_SQUELCHER_N = 58;
	const SPLATTERSHOT_PRO_COLLABO = 59;
	const SPLATTERSHOT_JR_SAKURA = 60;
	const T3_NOZZLENOSE_P = 61;
	const HEAVY_SPLATLING_DECO = 62;
	const WILLEM_DECAYED = 63;
	const HOT_BLASTER = 64;
	const RAPID_BLASTER = 65;
	const GAL_52_DECO = 66;
	const BRUSHWASHER_HEW = 67;
	const AEROSPRAY_MG = 68;
	const LUNA_BLASTER_NEO = 69;
	const LONG_BLASTER_CUSTOM = 70;
	const HOT_BLASTER_CUSTOM = 71;
	const RAPID_BLASTER_DECO = 72;
	const SPLASH_O_MATIC_TORA = 73;
	const OCTOBRUSH_COMET = 74;
	const BRUSHWASHER_METEOR = 75;
	const GAL_52_VEGA = 76;
	const GAL_96_SPICA = 77;
	const DUAL_SQUELCHER_GEMINI = 78;
	const L3_NOZZLENOSE_ALTAIR = 79;
	const RAPID_BLASTER_SIRIUS = 80;
	const LUNA_BLASTER_MERCURY = 81;
	const HOT_BLASTER_LIBRA = 82;
	const HYDRA_SPLATLING_REGULUS = 83;
	const LITRE3K = 84;
	const LITRE3K_CUSTOM = 85;
	const CLASSIC_SQUIFFER = 86;

	const SPLASH_BOMB = 1;
	const QUICK_BOMB = 2;
	const SUCKER_BOMB = 3;
	const KNOCK_BOMB = 4;
	const SPRINKLER = 5;
	const INK_BALL = 6;
	const ACID_BALL = 7;
	const TRAP = 8;
	const POINT_SENSOR = 9;
	const CHASE_BOMB = 10;

	const SHOOTER_SOUND_PITCH = 31.25;
	const SPLATLING_SOUND_PITCH = 25;

	const MOVEMENT_SPEED_DEFAULT_VALUE = 0.1;//AttributeのMOVEMENT_SPEEDのデフォルト値

	private $arrow = [];
	private $battleMember = [];
	private $field_data = [];
	public  $Task = [];
	private $tnt_data = [];
	private $weapondata = [];

	//MoveArrowのブロック判定時に使用、falseならCloseをしない
	private $blockData = [
		0 => false,
		8 => false,
		9 => false,
		10 => false,
		11 => false,
		30 => false,
		51 => false,
		106 => false,
	];

	function __construct($main){
		$this->main = $main;
		$this->resetWeaponsDataAll();
		$this->resetSubWeaponsDataAll();
		$this->startMoveTask();
		Entity::registerEntity(BombEntity::class);
		Entity::registerEntity(Nitron::class);
		Entity::registerEntity(Splatlon::class);
		Entity::registerEntity(Swind::class);
		Entity::registerEntity(Ambuffa::class);
		//Entity::registerEntity(ChaseBomb::class);
		Entity::registerEntity(Brupse::class, true);
		Entity::registerEntity(Charpse::class, true);
	}

	/**
	 * フィールドデータ取得
	 * @param  int $num 0 => 試合フィールド
	 * @return array
	 */
	public function getFieldData($num = 0){
		return (isset($this->field_data[$num])) ? $this->field_data[$num] : [];
	}

	public function setFieldData($data, $num = 0){
		$this->field_data[$num] = $data;
	}

	public function resetFieldData($num = 0){
		$this->field_data[$num] = [];
	}

	public function getWeaponData($weapon_num){
		return (!empty($this->weapondata[$weapon_num])) ? $this->weapondata[$weapon_num] : null;
	}

	public function getWeaponName($weapon_num){
		return (!empty($this->weapondata[$weapon_num][0])) ? $this->weapondata[$weapon_num][0] : "";
	}

	public function setWeaponName($weapon_num, $weapon_name){
		if(!empty($this->weapondata[$weapon_num])){
			$this->weapondata[$weapon_num][0] = $weapon_name;
			return true;
		}
		return false;
	}

	/**
	 * ブキ名を複数変更
	 * @param array $array [1 => パブロ]
	 */
	public function setWeaponsName(array $array){
		foreach($array as $weapon_num => $name){
			$this->weapondata[$weapon_num][0] = $name;
		}
		return true;
	}

	public function getSubWeaponDataFromWeapon($weapon_num){
		return $this->sub_weapondata[$this->weapondata[$weapon_num]['sub']];
	}

	public function getSubWeaponNumFromWeapon($weapon_num){
		return $this->weapondata[$weapon_num]['sub'];
	}

	public function getSubWeaponData($weapon_num){
		return !empty($this->sub_weapondata[$weapon_num]) ? $this->sub_weapondata[$weapon_num] : null;
	}

	public function getSubWeaponRate($weapon_num){
		return $this->sub_weapondata[$weapon_num][3];
	}

	public function getSubWeaponInkConsumption($player, $weapon_num){
		$cons = $this->sub_weapondata[$weapon_num][2];
		$cons *= Gadget::getCorrection($player, Gadget::RATE_SUB);
		return $cons;
	}

	public function getSubWeaponName($weapon_num){
		return $this->sub_weapondata[$weapon_num][0];
	}

	public function setSubWeaponName($weapon_num, $weapon_name){
		if(!empty($this->weapondata[$weapon_num])){
			$this->sub_weapondata[$weapon_num][0] = $weapon_name;
			return true;
		}
		return false;
	}

	public function setSubWeaponsName(array $array){
		foreach($array as $weapon_num => $name){
			$this->sub_weapondata[$weapon_num][0] = $name;
		}
	}

	public function getWeaponsDataAll(){
		return $this->weapondata;
	}

	public function resetWeaponsDataAll(){
		$this->weapondata = [
			//パブロ
			self::PABLO				 => [
				"weaponName.1",
				[280, 0],
				4,
				450,
				[0, 0, true],
				0,
				4,
				0,
				1.45,
				9,//ここ変更しても意味ないよ
				35,
				'type' => self::TYPE_ROLLER,
				'pos' => ['x' => 528, 'y' => 10, 'z' => -148],
				'sub' => self::SPRINKLER,
				'tr' => true
			],
			//スプラローラー
			self::SPLAT_ROLLER		 => [
				"weaponName.2",
				[270, 0],
				30,
				450,
				[1000, 0, true],
				8,
				7,
				0,
				1,
				10.55,//ここ変更しても意味ないよ
				40,
				'type' => self::TYPE_ROLLER,
				'pos' => ['x' => 530, 'y' => 10, 'z' => -148],
				'sub' => self::SUCKER_BOMB,
				'tr' => true
			],
			//ダイナモローラー
			self::DYNAMO_ROLLER		 => [
				"weaponName.3",
				[274, 0],
				65,
				600,
				[1500, 0, true],
				22,
				10,
				0,
				0.75,
				10,//ここ変更しても意味ないよ
				50,
				'type' => self::TYPE_ROLLER,
				'pos' => ['x' => 532, 'y' => 10, 'z' => -148],
				'sub' => self::SPRINKLER,
				'tr' => true
			],
			//スプラシューター
			self::SPLATTERSHOT		 => [
				"weaponName.4",
				[291, 0],
				9,
				500,
				[800, 0, true],
				4,
				7,
				0,
				1.05,
				14,
				5,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 534, 'y' => 10, 'z' => -148],
				'sub' => self::QUICK_BOMB,
				'tr' => true
			],
			//わかばシューター
			self::SPLATTERSHOT_JR	 => [
				"weaponName.5",
				[290, 0],
				2,
				400,
				[0, 0, true],
				3,
				6,
				0,
				1.15,
				9,
				7,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 536, 'y' => 10, 'z' => -148],
				'sub' => self::SPLASH_BOMB,
				'tr' => true
			],
			//スプラチャージャー
			self::SPLAT_CHARGER		 => [
				"weaponName.6",
				[261, 0],
				50,
				550,
				[3000, 0, true],
				0,
				30,
				0,
				1,
				22,
				0,
				'type' => self::TYPE_CHARGER,
				'pos' => ['x' => 528, 'y' => 10, 'z' => -151],
				'sub' => self::SPLASH_BOMB,
				'tr' => true
			],
			//ボールドマーカー
			self::SPLOOSH_O_MATIC	 => [
				"weaponName.7",
				[269, 0],
				7,
				450,
				[2000, 0, true],
				3,
				8,
				0,
				1.125,
				6,
				19,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 530, 'y' => 10, 'z' => -151],
				'sub' => self::INK_BALL,
				'tr' => true
			],
			//バケットスロッシャー
			self::SLOSHER			 => [
				"weaponName.8",
				[325, 0],
				48,
				500,
				[0, 0, true],
				11,
				7,
				0,
				1,
				10,
				16,
				'type' => self::TYPE_SLOSHER,
				'pos' => ['x' => 532, 'y' => 10, 'z' => -151],
				'sub' => self::QUICK_BOMB,
				'tr' => true
			],
			//プライムシューター
			self::SPLATTERSHOT_PRO	 => [
				"weaponName.9",
				[294, 0],
				16,
				450,
				[1500, 0, true],
				8,
				8,
				0,
				1,
				17,
				2,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 534, 'y' => 10, 'z' => -151],
				'sub' => self::SPLASH_BOMB,
				'tr' => true
			],
			//.96ガロン
			self::GAL_96			 => [
				"weaponName.10",
				[284, 0],
				25,
				450,
				[2000, 0, true],
				12,
				12,
				0,
				1,
				17,
				4,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 536, 'y' => 10, 'z' => -151],
				'sub' => self::SPRINKLER,
				'tr' => true
			],
			//.52ガロン
			self::GAL_52			 => [
				"weaponName.11",
				[277, 0],
				7,
				450,
				[2000, 0, true],
				7,
				12,
				0,
				1,
				12,
				12,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 528, 'y' => 10, 'z' => -154],
				'sub' => self::KNOCK_BOMB,
				'tr' => true
			],
			//デュアルスイーパー
			self::DUAL_SQUELCHER	 => [
				"weaponName.12",
				[292, 0],
				10,
				450,
				[2500, 0, true],
				6,
				6,
				0,
				1,
				19,
				4,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 530, 'y' => 10, 'z' => -154],
				'sub' => self::SPLASH_BOMB,
				'tr' => true
			],
			//ホクサイ
			self::OCTOBRUSH			 => [
				"weaponName.13",
				[369, 0],
				15,
				450,
				[1500, 0, true],
				4,
				6,
				0,
				1.4,
				5,//ここ変更しても意味ないよ
				44,
				'type' => self::TYPE_ROLLER,
				'pos' => ['x' => 532, 'y' => 10, 'z' => -154],
				'sub' => self::INK_BALL,
				'tr' => true
			],
			//バレルスピナー
			self::HEAVY_SPLATLING	 => [
				"weaponName.14",
				[258, 0],
				8,
				600,
				[2500, 0, true],
				60,
				6,
				0,
				1,
				20,
				8,
				'charge' => 50,
				'shot-count' => 35,
				'type' => self::TYPE_SPLATLING,
				'pos' => ['x' => 534, 'y' => 10, 'z' => -154],
				'sub' => self::KNOCK_BOMB,
				'tr' => true
			],
			//スプラスピナー
			self::MINI_SPLATLING	 => [
				"weaponName.15",
				[275, 0],
				7,
				550,
				[2500,  0, true],
				32,
				6,
				0,
				1,
				15,
				8,
				'charge' => 15,
				'shot-count' => 15,
				'type' => self::TYPE_SPLATLING,
				'pos' => ['x' => 536, 'y' => 10, 'z' => -154],
				'sub' => self::SUCKER_BOMB,
				'tr' => true
			],
			//ヒッセン
			self::BRUSHWASHER		 => [
				"weaponName.16",
				[325, 1],
				20,
				550,
				[1000, 0, true],
				4,
				5,
				0,
				1,
				6,//ここ変更しても意味ないよ
				35,
				'type' => self::TYPE_SLOSHER,
				'pos' => ['x' => 528, 'y' => 10, 'z' => -157],
				'sub' => self::ACID_BALL,
				'tr' => true
			],
			//シャープマーカー
			self::SPLASH_O_MATIC	 => [
				"weaponName.17",
				[256, 0],
				8,
				600,
				[1500, 0, true],
				4,
				6,
				0,
				1.125,
				11,
				0,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 530, 'y' => 10, 'z' => -157],
				'sub' => self::SUCKER_BOMB,
				'tr' => true
			],
			//スプラドル
			self::SPLAT_LADLE		 => [
				"weaponName.18",
				[281, 0],
				20,
				500,
				[3000, 0, true],
				5,
				7,
				0,
				1,
				9.3,//ここ変更しても意味ないよ
				135,
				'type' => self::TYPE_SLOSHER,
				'pos' => ['x' => 532, 'y' => 10, 'z' => -157],
				'sub' => self::SUCKER_BOMB,
				'tr' => true
			],
			//ハイドラント
			self::HYDRA_SPLATLING	 => [
				"weaponName.19",
				[286, 0],
				5,
				450,
				[2000, 0, true],
				80,
				6,
				0,
				1,
				24,
				9,
				'charge' => 75,
				'shot-count' => 40,
				'type' => self::TYPE_SPLATLING,
				'pos' => ['x' => 534, 'y' => 10, 'z' => -157],
				'sub' => self::SPLASH_BOMB,
				'tr' => true
			],
			//ノヴァブラスター
			self::LUNA_BLASTER		 => [
				"weaponName.20",
				[359, 0],
				35,
				450,
				[7000, 0, true],
				20,
				30,
				0,
				1,
				7,
				0,
				'charge' => 6,
				'shot-count' => 1,
				'bomb_radius' => 2.7,
				'bomb_paint' => 5,
				'bomb_damageper' => 0.5,
				'speed' => 1,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 536, 'y' => 10, 'z' => -160],
				'sub' => self::TRAP,
				'tr' => true
			],
			//L3リールガン
			self::L3_NOZZLENOSE		 => [
				"weaponName.21",
				[273, 0],
				17,
				500,
				[2000, 0, true],
				20,
				8,
				0,
				1,
				16,
				2,
				'charge' => 6,
				'shot-count' => 3,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 536, 'y' => 10, 'z' => -157],
				'sub' => self::ACID_BALL,
				'tr' => true
			],
			//T3リールガン
			self::T3_NOZZLENOSE		 => [
				"weaponName.22",
				[271, 0],
				7,
				450,
				[2000, 0, true],
				12,
				7,
				0,
				1,
				11,
				2,
				'charge' => 3,
				'shot-count' => 3,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 528, 'y' => 10, 'z' => -160],
				'sub' => self::SUCKER_BOMB,
				'tr' => true
			],
			self::SPLAT_UMBRELLA	 => [
				//以下の数値は仮です！！
				"weaponName.23",
				[346, 0],
				70,
				500,
				[0,0,false],
				18,
				0,
				0,
				1,
				0,//ふめい
				0,//うえにおなじく
				'type' => self::TYPE_SLOSHER,
				'sub' => self::SPRINKLER,
				'tr' => true
			],
			//プロモデラーPG
			self::AEROSPRAY_PG	 => [
				"weaponName.24",
				[293, 0],
				5,
				600,
				[1000, 0, true],
				2,
				3,
				0,
				1.1,
				8,
				25,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 530, 'y' => 10, 'z' => -160],
				'sub' => self::QUICK_BOMB,
				'tr' => true
			],
			//ウィレム
			self::WILLEM		 => [
				"weaponName.25",
				[352, 0],
				55,
				650,
				[2500, 0, true],
				9,
				8,
				0,
				1.25,
				5,//ここ変更しても意味ないよ
				55,
				'type' => self::TYPE_ROLLER,
				'pos' => ['x' => 532, 'y' => 10, 'z' => -160],
				'sub' => self::SUCKER_BOMB,
				'tr' => true
			],
			//スプラドルカスタム
			self::SPLAT_LADLE_CUSTOM		 => [
				"weaponName.26",
				[281, 0],
				20,
				500,
				[9999, 0, true],
				5,
				7,
				0,
				1,
				9.3,//ここ変更しても意味ないよ
				135,
				'type' => self::TYPE_SLOSHER,
				'pos' => ['x' => 532, 'y' => 15, 'z' => -157],
				'sub' => self::SPRINKLER,
				'tr' => false
			],
			//スプラローラーコラボ
			self::SPLAT_ROLLER_COLLABO		 => [
				"weaponName.27",
				[270, 0],
				30,
				450,
				[3900, 0, true],
				8,
				7,
				0,
				1,
				10.55,//ここ変更しても意味ないよ
				40,
				'type' => self::TYPE_ROLLER,
				'pos' => ['x' => 530, 'y' => 15, 'z' => -148],
				'sub' => self::INK_BALL,
				'tr' => false
			],
			//ダイナモローラーテスラ
			self::GOLD_DYNAMO_ROLLER		 => [
				"weaponName.28",
				[274, 0],
				65,
				600,
				[3600, 0, true],
				22,
				10,
				0,
				0.75,
				10,//ここ変更しても意味ないよ
				50,
				'type' => self::TYPE_ROLLER,
				'pos' => ['x' => 532, 'y' => 15, 'z' => -148],
				'sub' => self::SPLASH_BOMB,
				'tr' => false
			],
			//スプラシューターコラボ
			self::SPLATTERSHOT_COLLABO		 => [
				"weaponName.29",
				[291, 0],
				9,
				500,
				[4200, 0, true],
				4,
				7,
				0,
				1.05,
				14,
				5,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 534, 'y' => 15, 'z' => -148],
				'sub' => self::SUCKER_BOMB,
				'tr' => false
			],
			//スプラチャージャーワカメ
			self::SPLAT_CHARGER_WAKAME		 => [
				"weaponName.30",
				[261, 0],
				50,
				550,
				[7500, 0, true],
				0,
				30,
				0,
				1,
				22,
				0,
				'type' => self::TYPE_CHARGER,
				'pos' => ['x' => 528, 'y' => 15, 'z' => -151],
				'sub' => self::SPRINKLER,
				'tr' => false
			],
			//ボールドマーカー7
			self::SPLOOSH_O_MATIC_SEVEN	 => [
				"weaponName.31",
				[269, 0],
				7,
				450,
				[7777, 0, true],
				3,
				8,
				0,
				1.125,
				6,
				19,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 530, 'y' => 15, 'z' => -151],
				'sub' => self::SPLASH_BOMB,
				'tr' => false
			],
			//バケットスロッシャーデコ
			self::SLOSHER_DECO			 => [
				"weaponName.32",
				[325, 0],
				48,
				500,
				[3800, 0, true],
				11,
				7,
				0,
				1,
				10,
				16,
				'type' => self::TYPE_SLOSHER,
				'pos' => ['x' => 532, 'y' => 15, 'z' => -151],
				'sub' => self::KNOCK_BOMB,
				'tr' => false
			],
			//プライムシューターベリー
			self::SPLATTERSHOT_PRO_BERRY	 => [
				"weaponName.33",
				[294, 0],
				16,
				450,
				[5400, 0, true],
				8,
				8,
				0,
				1,
				17,
				2,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 534, 'y' => 15, 'z' => -151],
				'sub' => self::SUCKER_BOMB,
				'tr' => false
			],
			//.96ガロンデコ
			self::GAL_96_DECO			 => [
				"weaponName.34",
				[284, 0],
				25,
				450,
				[4600, 0, true],
				12,
				12,
				0,
				1,
				17,
				4,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 536, 'y' => 15, 'z' => -151],
				'sub' => self::KNOCK_BOMB,
				'tr' => false
			],
			//デュアルスイーパーカスタム
			self::DUAL_SQUELCHER_CUSTOM	 => [
				"weaponName.35",
				[292, 0],
				10,
				450,
				[5020, 0, true],
				6,
				6,
				0,
				1,
				19,
				4,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 530, 'y' => 15, 'z' => -154],
				'sub' => self::INK_BALL,
				'tr' => false
			],
			//ホクサイ・ヒュー
			self::OCTOBRUSH_HEW			 => [
				"weaponName.36",
				[369, 0],
				15,
				450,
				[6900, 0, true],
				4,
				6,
				0,
				1.4,
				5,//ここ変更しても意味ないよ
				44,
				'type' => self::TYPE_ROLLER,
				'pos' => ['x' => 532, 'y' => 15, 'z' => -154],
				'sub' => self::SPLASH_BOMB,
				'tr' => false
			],
			//バレルスピナーリミックス
			self::HEAVY_SPLATLING_REMIX	 => [
				"weaponName.37",
				[258, 0],
				8,
				600,
				[5550, 0, true],
				60,
				6,
				0,
				1,
				20,
				8,
				'charge' => 50,
				'shot-count' => 35,
				'type' => self::TYPE_SPLATLING,
				'pos' => ['x' => 534, 'y' => 15, 'z' => -154],
				'sub' => self::SPRINKLER,
				'tr' => false
			],
			//スプラスピナーリペア
			self::MINI_SPLATLING_REPAIR	 => [
				"weaponName.38",
				[275, 0],
				7,
				550,
				[3500,  0, true],
				32,
				6,
				0,
				1,
				15,
				8,
				'charge' => 15,
				'shot-count' => 15,
				'type' => self::TYPE_SPLATLING,
				'pos' => ['x' => 536, 'y' => 15, 'z' => -154],
				'sub' => self::QUICK_BOMB,
				'tr' => false
			],
			//シャープマーカーネオ
			self::SPLASH_O_MATIC_NEO	 => [
				"weaponName.39",
				[256, 0],
				8,
				600,
				[4800, 0, true],
				4,
				6,
				0,
				1.125,
				11,
				0,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 530, 'y' => 15, 'z' => -157],
				'sub' => self::QUICK_BOMB,
				'tr' => false
			],
			//ハイドラントカスタム
			self::HYDRA_SPLATLING_CUSTOM	 => [
				"weaponName.40",
				[286, 0],
				5,
				450,
				[8800, 0, true],
				80,
				6,
				0,
				1,
				24,
				9,
				'charge' => 75,
				'shot-count' => 40,
				'type' => self::TYPE_SPLATLING,
				'pos' => ['x' => 534, 'y' => 15, 'z' => -157],
				'sub' => self::SPRINKLER,
				'tr' => false
			],
			//デルタスイーパーM
			self::DELTA_SQUELCHER_M		 => [
				"weaponName.41",
				[279, 0],
				14,
				500,
				[5000, 0, true],
				6,
				4,
				0,
				0.1,
				30,
				0,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 534, 'y' => 10, 'z' => -160],
				'sub' => self::KNOCK_BOMB,
				'tr' => true
			],
			//パブロ・ヒュー
			self::PABLO_HEW				 => [
				"weaponName.42",
				[280, 0],
				4,
				450,
				[4000, 0, true],
				0,
				4,
				0,
				1.45,
				9,//ここ変更しても意味ないよ
				35,
				'type' => self::TYPE_ROLLER,
				'pos' => ['x' => 528, 'y' => 15, 'z' => -148],
				'sub' => self::TRAP,
				'tr' => false
			],
			//プロモデラーRG
			self::AEROSPRAY_RG	 => [
				"weaponName.43",
				[293, 0],
				5,
				600,
				[4300, 0, true],
				2,
				3,
				0,
				1.1,
				8,
				25,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 530, 'y' => 15, 'z' => -160],
				'sub' => self::TRAP,
				'tr' => false
			],
			//デルタスイーパーT
			self::DELTA_SQUELCHER_T		 => [
				"weaponName.44",
				[279, 0],
				14,
				500,
				[10000, 0, true],
				6,
				4,
				0,
				0.1,
				30,
				0,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 534, 'y' => 15, 'z' => -160],
				'sub' => self::TRAP,
				'tr' => false
			],
			//L3リールガンD
			self::L3_NOZZLENOSE_D		 => [
				"weaponName.45",
				[273, 0],
				17,
				500,
				[3700, 0, true],
				20,
				8,
				0,
				1,
				16,
				2,
				'charge' => 6,
				'shot-count' => 3,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 536, 'y' => 15, 'z' => -157],
				'sub' => self::QUICK_BOMB,
				'tr' => false
			],
			//T3リールガンD
			self::T3_NOZZLENOSE_D		 => [
				"weaponName.46",
				[271, 0],
				7,
				450,
				[3700, 0, true],
				12,
				7,
				0,
				1,
				11,
				2,
				'charge' => 3,
				'shot-count' => 3,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 528, 'y' => 15, 'z' => -160],
				'sub' => self::KNOCK_BOMB,
				'tr' => false
			],
			//もみじシューター
			self::SPLATTERSHOT_JR_MOMIJI	 => [
				"weaponName.47",
				[290, 0],
				2,
				400,
				[2900, 0, true],
				3,
				6,
				0,
				1.15,
				9,
				7,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 536, 'y' => 15, 'z' => -148],
				'sub' => self::ACID_BALL,
				'tr' => false
			],
			//ウィレム・ヒュー
			self::WILLEM_HEW		 => [
				"weaponName.48",
				[352, 0],
				55,
				650,
				[9999, 0, true],
				9,
				8,
				0,
				1.25,
				5,//ここ変更しても意味ないよ
				55,
				'type' => self::TYPE_ROLLER,
				'pos' => ['x' => 532, 'y' => 15, 'z' => -160],
				'sub' => self::ACID_BALL,
				'tr' => false
			],
			//パーマネント・パブロ
			self::PERMANENT_PABLO				 => [
				"weaponName.49",
				[280, 0],
				4,
				450,
				[4000, 0, true],
				0,
				4,
				0,
				1.45,
				9,//ここ変更しても意味ないよ
				35,
				'type' => self::TYPE_ROLLER,
				'pos' => ['x' => 528, 'y' => 20, 'z' => -148],
				'sub' => self::SPLASH_BOMB,
				'tr' => false
			],
			//スプラシューターワサビ
			self::SPLATTERSHOT_WASABI		 => [
				"weaponName.50",
				[291, 0],
				9,
				500,
				[8300, 0, true],
				4,
				7,
				0,
				1.05,
				14,
				5,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 534, 'y' => 20, 'z' => -148],
				'sub' => self::SPLASH_BOMB,
				'tr' => false
			],
			//スプラローラーコロコロ
			self::SPLAT_ROLLER_COROCORO		 => [
				"weaponName.51",
				[270, 0],
				30,
				450,
				[3900, 0, true],
				8,
				7,
				0,
				1,
				10.55,//ここ変更しても意味ないよ
				40,
				'type' => self::TYPE_ROLLER,
				'pos' => ['x' => 530, 'y' => 20, 'z' => -148],
				'sub' => self::KNOCK_BOMB,
				'tr' => false
			],
			//スプラチャージャーベントー
			self::SPLAT_CHARGER_BENTO		 => [
				"weaponName.52",
				[261, 0],
				50,
				550,
				[8500, 0, true],
				0,
				30,
				0,
				1,
				22,
				0,
				'type' => self::TYPE_CHARGER,
				'pos' => ['x' => 528, 'y' => 20, 'z' => -151],
				'sub' => self::KNOCK_BOMB,
				'tr' => false
			],
			//バケットスロッシャーソーダ
			self::SLOSHER_SODA			 => [
				"weaponName.53",
				[325, 0],
				48,
				500,
				[6200, 0, true],
				11,
				7,
				0,
				1,
				10,
				16,
				'type' => self::TYPE_SLOSHER,
				'pos' => ['x' => 532, 'y' => 20, 'z' => -151],
				'sub' => self::SPLASH_BOMB,
				'tr' => false
			],
			//スプラスピナーコラボ
			self::MINI_SPLATLING_COLLABO	 => [
				"weaponName.54",
				[275, 0],
				7,
				550,
				[8800,  0, true],
				32,
				6,
				0,
				1,
				15,
				8,
				'charge' => 15,
				'shot-count' => 15,
				'type' => self::TYPE_SPLATLING,
				'pos' => ['x' => 536, 'y' => 20, 'z' => -154],
				'sub' => self::ACID_BALL
			],
			//スプラドルネクロ
			self::SPLAT_LADLE_NECRO		 => [
				"weaponName.55",
				[281, 0],
				20,
				500,
				[12345, 0, true],
				5,
				7,
				0,
				1,
				9.3,//ここ変更しても意味ないよ
				135,
				'type' => self::TYPE_SLOSHER,
				'pos' => ['x' => 532, 'y' => 20, 'z' => -157],
				'sub' => self::ACID_BALL,
				'tr' => false
			],
			//ロングブラスター
			self::LONG_BLASTER		 => [
				"weaponName.56",
				[267, 0],
				40,
				450,
				[8000, 0, true],
				30,
				30,
				0,
				1,
				14,
				2,
				'charge' => 9,
				'shot-count' => 1,
				'bomb_radius' => 2.5,
				'bomb_paint' => 5,
				'bomb_damageper' => 0.5,
				'speed' => 1,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 538, 'y' => 10, 'z' => -160],
				'sub' => self::KNOCK_BOMB,
				'tr' => true
			],
			//ボールドマーカーネオ
			self::SPLOOSH_O_MATIC_NEO	 => [
				"weaponName.57",
				[269, 0],
				7,
				450,
				[8000, 0, true],
				3,
				8,
				0,
				1.125,
				6,
				19,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 530, 'y' => 20, 'z' => -151],
				'sub' => self::POINT_SENSOR,
				'tr' => false
			],
			//デルタスイーパーN
			self::DELTA_SQUELCHER_N		 => [
				"weaponName.58",
				[279, 0],
				14,
				500,
				[10000, 0, true],
				6,
				4,
				0,
				0.1,
				30,
				0,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 534, 'y' => 20, 'z' => -160],
				'sub' => self::INK_BALL,
				'tr' => false
			],
			//プライムシューターコラボ
			self::SPLATTERSHOT_PRO_COLLABO	 => [
				"weaponName.59",
				[294, 0],
				16,
				450,
				[9900, 0, true],
				8,
				8,
				0,
				1,
				17,
				2,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 534, 'y' => 20, 'z' => -151],
				'sub' => self::POINT_SENSOR,
				'tr' => false
			],
			//さくらシューター
			self::SPLATTERSHOT_JR_SAKURA	 => [
				"weaponName.60",
				[290, 0],
				2,
				400,
				[3939, 0, true],
				3,
				6,
				0,
				1.15,
				9,
				7,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 536, 'y' =>20, 'z' => -148],
				'sub' => self::SPRINKLER,
				'tr' => false
			],
			//T3リールガンP
			self::T3_NOZZLENOSE_P		 => [
				"weaponName.61",
				[271, 0],
				7,
				450,
				[13333, 0, true],
				12,
				7,
				0,
				1,
				11,
				2,
				'charge' => 3,
				'shot-count' => 3,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 528, 'y' => 20, 'z' => -160],
				'sub' => self::POINT_SENSOR,
				'tr' => false
			],
			//バレルスピナーデコ
			self::HEAVY_SPLATLING_DECO	 => [
				"weaponName.62",
				[258, 0],
				8,
				600,
				[19800, 0, true],
				60,
				6,
				0,
				1,
				20,
				8,
				'charge' => 50,
				'shot-count' => 35,
				'type' => self::TYPE_SPLATLING,
				'pos' => ['x' => 534, 'y' => 20, 'z' => -154],
				'sub' => self::POINT_SENSOR,
				'tr' => false
			],
			//ウィレム・ディケイド
			self::WILLEM_DECAYED		 => [
				"weaponName.63",
				[352, 0],
				55,
				650,
				[12345, 0, true],
				9,
				8,
				0,
				1.25,
				5,//ここ変更しても意味ないよ
				55,
				'type' => self::TYPE_ROLLER,
				'pos' => ['x' => 532, 'y' => 20, 'z' => -160],
				'sub' => self::POINT_SENSOR,
				'tr' => false
			],
			//ホットブラスター
			self::HOT_BLASTER		 => [
				"weaponName.64",
				[272, 0],
				30,
				450,
				[8500, 0, true],
				25,
				30,
				0,
				1,
				11,
				2,
				'charge' => 6,
				'shot-count' => 1,
				'bomb_radius' => 2.5,
				'bomb_paint' => 5,
				'bomb_damageper' => 0.5,
				'speed' => 1,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 538, 'y' => 10, 'z' => -157],
				'sub' => self::ACID_BALL,
				'tr' => true
			],
			//ラピッドブラスター
			self::RAPID_BLASTER		 => [
				"weaponName.65",
				[268, 0],
				15,
				450,
				[9000, 0, true],
				16,
				18,
				0,
				1,
				18,
				2,
				'charge' => 3,
				'shot-count' => 1,
				'bomb_radius' => 2.25,
				'bomb_paint' => 4,
				'bomb_damageper' => 0.75,
				'speed' => 1.3,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 538, 'y' => 10, 'z' => -154],
				'sub' => self::TRAP,
				'tr' => true
			],
			//.52ガロンデコ
			self::GAL_52_DECO			 => [
				"weaponName.66",
				[277, 0],
				7,
				450,
				[8000, 0, true],
				7,
				12,
				0,
				1,
				12,
				12,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 528, 'y' => 15, 'z' => -154],
				'sub' => self::CHASE_BOMB,
				'tr' => false
			],
			//ヒッセン・ヒュー
			self::BRUSHWASHER_HEW		 => [
				"weaponName.67",
				[325, 1],
				20,
				550,
				[4500, 0, true],
				4,
				5,
				0,
				1,
				6,//ここ変更しても意味ないよ
				35,
				'type' => self::TYPE_SLOSHER,
				'pos' => ['x' => 528, 'y' => 15, 'z' => -157],
				'sub' => self::CHASE_BOMB,
				'tr' => false
			],
			//プロモデラーMG
			self::AEROSPRAY_MG	 => [
				"weaponName.68",
				[293, 0],
				5,
				600,
				[4300, 0, true],
				2,
				3,
				0,
				1.1,
				8,
				25,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 530, 'y' => 20, 'z' => -160],
				'sub' => self::CHASE_BOMB,
				'tr' => false
			],
			//ノヴァブラスターネオ
			self::LUNA_BLASTER_NEO		 => [
				"weaponName.69",
				[359, 0],
				35,
				450,
				[9999, 0, true],
				20,
				30,
				0,
				1,
				7,
				0,
				'charge' => 6,
				'shot-count' => 1,
				'bomb_radius' => 2.7,
				'bomb_paint' => 5,
				'bomb_damageper' => 0.5,
				'speed' => 1,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 536, 'y' => 15, 'z' => -160],
				'sub' => self::SPLASH_BOMB,
				'tr' => false
			],
			//ロングブラスターカスタム
			self::LONG_BLASTER_CUSTOM		 => [
				"weaponName.70",
				[267, 0],
				40,
				450,
				[14000, 0, true],
				30,
				30,
				0,
				1,
				14,
				2,
				'charge' => 9,
				'shot-count' => 1,
				'bomb_radius' => 2.5,
				'bomb_paint' => 5,
				'bomb_damageper' => 0.5,
				'speed' => 1,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 538, 'y' => 15, 'z' => -160],
				'sub' => self::SPLASH_BOMB,
				'tr' => false
			],
			//ホットブラスターカスタム
			self::HOT_BLASTER_CUSTOM		 => [
				"weaponName.71",
				[272, 0],
				30,
				450,
				[9500, 0, true],
				25,
				30,
				0,
				1,
				11,
				2,
				'charge' => 6,
				'shot-count' => 1,
				'bomb_radius' => 2.5,
				'bomb_paint' => 5,
				'bomb_damageper' => 0.5,
				'speed' => 1,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 538, 'y' => 15, 'z' => -157],
				'sub' => self::POINT_SENSOR,
				'tr' => false
			],
			//ラピッドブラスターデコ
			self::RAPID_BLASTER_DECO		 => [
				"weaponName.72",
				[268, 0],
				15,
				450,
				[9500, 0, true],
				16,
				18,
				0,
				1,
				18,
				2,
				'charge' => 3,
				'shot-count' => 1,
				'bomb_radius' => 2.25,
				'bomb_paint' => 4,
				'bomb_damageper' => 0.75,
				'speed' => 1.3,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 538, 'y' => 15, 'z' => -154],
				'sub' => self::SUCKER_BOMB,
				'tr' => false
			],
			//シャープマーカートラ
			self::SPLASH_O_MATIC_TORA	 => [
				"weaponName.73",
				[256, 0],
				8,
				600,
				[29800, 0, true],
				4,
				6,
				0,
				1.125,
				11,
				0,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 530, 'y' => 20, 'z' => -157],
				'sub' => self::CHASE_BOMB,
				'tr' => false
			],
			//ホクサイ・コメット
			self::OCTOBRUSH_COMET			 => [
				"weaponName.74",
				[369, 0],
				15,
				450,
				[18900, 0, true],
				4,
				6,
				0,
				1.4,
				5,//ここ変更しても意味ないよ
				44,
				'type' => self::TYPE_ROLLER,
				'pos' => ['x' => 532, 'y' => 20, 'z' => -154],
				'sub' => self::CHASE_BOMB,
				'tr' => false
			],
			//ヒッセン・ミーティア
			self::BRUSHWASHER_METEOR		 => [
				"weaponName.75",
				[325, 1],
				20,
				550,
				[12500, 0, true],
				4,
				5,
				0,
				1,
				6,//ここ変更しても意味ないよ
				35,
				'type' => self::TYPE_SLOSHER,
				'pos' => ['x' => 528, 'y' => 20, 'z' => -157],
				'sub' => self::INK_BALL,
				'tr' => false
			],
			//.52ガロンベガ
			self::GAL_52_VEGA			 => [
				"weaponName.76",
				[277, 0],
				7,
				450,
				[18000, 0, true],
				7,
				12,
				0,
				1,
				12,
				12,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 528, 'y' => 20, 'z' => -154],
				'sub' => self::TRAP,
				'tr' => false
			],
			//.96ガロンスピカ
			self::GAL_96_SPICA			 => [
				"weaponName.77",
				[284, 0],
				25,
				450,
				[14600, 0, true],
				12,
				12,
				0,
				1,
				17,
				4,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 536, 'y' => 20, 'z' => -151],
				'sub' => self::ACID_BALL,
				'tr' => false
			],
			//デュアルスイーパージェミニ
			self::DUAL_SQUELCHER_GEMINI	 => [
				"weaponName.78",
				[292, 0],
				10,
				450,
				[5020, 0, true],
				6,
				6,
				0,
				1,
				19,
				4,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 530, 'y' => 20, 'z' => -154],
				'sub' => self::SPRINKLER,
				'tr' => false
			],
			//L3リールガンアルタイル
			self::L3_NOZZLENOSE_ALTAIR		 => [
				"weaponName.79",
				[273, 0],
				17,
				500,
				[13700, 0, true],
				20,
				8,
				0,
				1,
				16,
				2,
				'charge' => 6,
				'shot-count' => 3,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 536, 'y' => 20, 'z' => -157],
				'sub' => self::SUCKER_BOMB,
				'tr' => false
			],
			//ラピッドブラスターシリウス
			self::RAPID_BLASTER_SIRIUS		 => [
				"weaponName.80",
				[268, 0],
				15,
				450,
				[12500, 0, true],
				16,
				18,
				0,
				1,
				18,
				2,
				'charge' => 3,
				'shot-count' => 1,
				'bomb_radius' => 2.25,
				'bomb_paint' => 4,
				'bomb_damageper' => 0.75,
				'speed' => 1.3,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 538, 'y' => 20, 'z' => -154],
				'sub' => self::SPRINKLER,
				'tr' => false
			],
			//ノヴァブラスターマーキュリー
			self::LUNA_BLASTER_MERCURY		 => [
				"weaponName.81",
				[359, 0],
				35,
				450,
				[15800, 0, true],
				20,
				30,
				0,
				1,
				7,
				0,
				'charge' => 6,
				'shot-count' => 1,
				'bomb_radius' => 2.7,
				'bomb_paint' => 5,
				'bomb_damageper' => 0.5,
				'speed' => 1,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 536, 'y' => 20, 'z' => -160],
				'sub' => self::CHASE_BOMB,
				'tr' => false
			],
			//ホットブラスターリブラ
			self::HOT_BLASTER_LIBRA		 => [
				"weaponName.82",
				[272, 0],
				30,
				450,
				[13500, 0, true],
				25,
				30,
				0,
				1,
				11,
				2,
				'charge' => 6,
				'shot-count' => 1,
				'bomb_radius' => 2.5,
				'bomb_paint' => 5,
				'bomb_damageper' => 0.5,
				'speed' => 1,
				'type' => self::TYPE_SHOOTER,
				'pos' => ['x' => 538, 'y' => 20, 'z' => -157],
				'sub' => self::SUCKER_BOMB,
				'tr' => false
			],
			//ハイドラントレグルス
			self::HYDRA_SPLATLING_REGULUS	 => [
				"weaponName.83",
				[286, 0],
				5,
				450,
				[14800, 0, true],
				80,
				6,
				0,
				1,
				24,
				9,
				'charge' => 75,
				'shot-count' => 40,
				'type' => self::TYPE_SPLATLING,
				'pos' => ['x' => 534, 'y' => 20, 'z' => -157],
				'sub' => self::QUICK_BOMB,
				'tr' => false
			],
			//リッター3K
			self::LITRE3K		 => [
				"weaponName.84",
				[261, 1],
				150,
				600,
				[13000, 0, true],
				0,
				35,
				0,
				0.85,
				30,
				0,
				'type' => self::TYPE_CHARGER,
				'pos' => ['x' => 538, 'y' => 10, 'z' => -163],
				'sub' => self::QUICK_BOMB,
				'tr' => true
			],
			//リッター3Kカスタム
			self::LITRE3K_CUSTOM		 => [
				"weaponName.85",
				[261, 1],
				150,
				600,
				[14000, 0, true],
				0,
				35,
				0,
				0.85,
				30,
				0,
				'type' => self::TYPE_CHARGER,
				'pos' => ['x' => 538, 'y' => 15, 'z' => -163],
				'sub' => self::INK_BALL,
				'tr' => false
			],
			//スクイックリンα
			self::CLASSIC_SQUIFFER		 => [
				"weaponName.86",
				[261, 2],
				30,
				500,
				[7500, 0, true],
				0,
				30,
				0,
				1.2,
				16,
				0,
				'type' => self::TYPE_CHARGER,
				'pos' => ['x' => 540, 'y' => 10, 'z' => -163],
				'sub' => self::POINT_SENSOR,
				'tr' => true
			],
			/*
			#Template
			self::WEAPON_NAME => [
				"weaponName.num",//Mainのほうで自動で名前が書き換えられる
				[ItemID, damage],
				1発の消費量(※必ずしもこの量を消費するわけではない),
				インクタンクの量,
				[値段, 購入時に確認されるあげないといけないブキレベルの数値, 購入できるかどうか(非売品か確認時に使用)]
				次にブキが塗れるようになるまでのtick(Rate),
				攻撃力(ハート半分を1、ハート1つで2とした数値),
				ノックバックの数値,
				移動速度(1.25で通常の速さ+25%になる),
				射程,
				拡散値(角度),
				'charge' => 50,//チャージにかかるtick,
				'type' => ブキのタイプ,
				'pos' => ['x' => 0, 'y' => 0, 'z' => 0]//ショップでブキが設置される座標、購入時のブキを判別する際にも使用される
				'sub' => サブウェポン
				'tr' => 試し塗りで配られるかどうか
			]
			*/
		];
		$this->weaponNumbyItemID = [];
		$this->shootersID = [];
		foreach($this->weapondata as $weapon_num => $data){
			$this->weaponNumbyItemID[$data[1][0]][$data[1][1]] = $weapon_num;
			if($data['type'] === self::TYPE_SHOOTER){
				$this->shootersID[$data[1][0]][$data[1][1]] = $weapon_num;
			}
		}
	}

	public function setWeaponsDataAllIntoDB(){
		return $this->main->s->setDataIntoDB("weaponsdata", $this->weapondata);
	}

	/**
	 * アイテムIDからブキIDを取得
	 * @param  int  $id
	 * @param  int $damage default = 0
	 * @return int | null
	 */
	public function getWeaponNumFromItemID($id, $damage = 0){
		return isset($this->weaponNumbyItemID[$id][$damage]) ? $this->weaponNumbyItemID[$id][$damage] : null;
	}

	public function getAttackDamage($weapon_num){
		return $this->weapondata[$weapon_num][6] ?? 0;
	}

	public function getKnockbackValue($weapon_num){
		return $this->weapondata[$weapon_num][7] ?? 0;
	}

	public function getMovementSpeedRate($player, $weapon_num){
		$speed = $this->weapondata[$weapon_num][8] ?? 1;
		$speed *= Gadget::getCorrection($player, Gadget::SPEED);
		return ($speed < 1.5)? $speed : 1.5;
	}

	/**
	 * 射程距離を取得
	 * @param  int $weapon_num
	 * @return int | float
	 */
	public function getFiringRange($weapon_num){
		$default = 50;
		return $this->weapondata[$weapon_num][9] ?? $default;
	}

	/**
	 * ブキのブレ(拡散値)を取得する
	 * @param  int         $weapon_num
	 * @param  bool        $random     default = true ランダムな値を取得するか
	 * @return int | float
	 */
	public function getDiffusionValue($weapon_num, $random = true){
		$angle = $this->weapondata[$weapon_num][10] ?? 0;
		if($random && $angle !== 0){
			$min = -$angle * 100;
			$max = $angle * 100;
			return mt_rand($min, $max) / 100;
		}else{
			return $angle;
		}
	}

	/**
	 * チャージにかかるtickを取得
	 * @param  int $weapon_num
	 * @return int | float
	 */
	public function getChargeTick($weapon_num){
		return $this->weapondata[$weapon_num]['charge'] ?? 0;
	}

	public function getShotCount($weapon_num){
		return $this->weapondata[$weapon_num]['shot-count'] ?? 0;
	}

	public function resetSubWeaponsDataAll(){
/*		$this->sub_weapondata = [
			//番号	 => [			名前, ItemID, 消費量]
			1		 => ["sub-weapon.1", 332, 0.3],
			2		 => ["sub-weapon.2",  46, 0.8],
		];*/
		$this->sub_weapondata = [
			self::SPLASH_BOMB => [
				"sub-weaponName.1",
				[264, 0], //diamond
				0.7,
				12,
			],
			self::QUICK_BOMB => [
				"sub-weaponName.2",
				[378, 0], //Magma cream
				0.4,
				4,
			],
			self::SUCKER_BOMB => [
				"sub-weaponName.3",
				[337, 0], //Clay
				0.7,
				12,
			],
			self::KNOCK_BOMB => [
				"sub-weaponName.4",
				[318, 0], //Flint
				0.5,
				12,
			],
			self::SPRINKLER => [
				"sub-weaponName.5",
				[341, 0], //Slime Ball
				0.8,
				12,
			],
			self::INK_BALL => [
				"sub-weaponName.6",
				[376, 0], //Fermented Spider Eye
				0.5,
				12,
			],
			self::ACID_BALL => [
				"sub-weaponName.7",
				[388, 0], //Emerald
				0.5,
				12,
			],
			self::TRAP => [
				//"sub-weaponName.8",
				"トラップ",//無理やり解決
				[289, 0], //Gunpowder
				0.5,
				12,
			],
			self::POINT_SENSOR => [
				"ポイントセンサー",
				[265, 0], //Fermented Spider Eye
				0.4,
				12,
			],
			self::CHASE_BOMB => [
				"チェイスボム",
				[407, 0], //Minecart with TNT
				0.8,
				12,
			],


			/*
			#Template
			self::WEAPON_NAME => [
				"sub-weaponName.num",//Mainのほうで自動で名前が書き換えられる
				[ItemID, damage],
				1発の消費量(※タンク量からの割合),
				次にブキが塗れるようになるまでのtick(Rate),
				'tap' => タップしたときに呼び出される関数(引数は $player,$X,$y,$z,$block),
				'throw' => 長押ししたときに呼び出される関数(引数は $player),
			]
			SPLASH_BOMB = 1;
			QUICK_BOMB = 2;
			SUCKER_BOMB = 3;
			KNOCK_BOMB = 4;
			SPRINKLER = 5;
			INK_BALL = 6;
			POISON_BALL = 7;
			*/
		];
	}

	public function canTryPaint($weapon_num){
		return $this->weapondata[$weapon_num]['tr'] ?? false;
	}

	public function weaponType($weapon_num){
		return $this->weapondata[$weapon_num]['type'] ?? false;
	}

	public function getWeaponItemId($weapon_num){
		return $this->weapondata[$weapon_num][1] ?? [0, 0];
	}

	public function getSubWeaponItemId($weapon_num){
		return $this->sub_weapondata[$weapon_num][1] ?? [0, 0];
	}

	/**
	 * ブキが非売品ではないかどうか
	 * @param  int $weapon_num
	 * @return bool
	 */
	public function canSellWeapons($weapon_num){
		return (!empty($this->weapondata[$weapon_num])) ? $this->weapondata[$weapon_num][4][2] : 0;
	}

	public function getWeaponAmount(){
		return count($this->weapondata);
	}

	public function getSubWeaponAmount(){
		return count($this->sub_weapondata);
	}

	public function getBattleTeamMember(){
		return $this->battleMember;
	}

	public function getAllBattleMembers(){
		$bm = $this->battleMember;
		$ar = [];
		foreach ($bm as $team_num => $members){
			$ar = array_merge($ar, $members);
		}
		return $ar;
	}

	/**
	 * 試合するメンバーをset
	 * @param string[] $array
	 */
	public function setBattleMember(array $array){
		$this->battleMember = $array;
	}

	/**
	 * 試合チームリセット
	 * @param int $numbers [description]
	 */
	public function resetBattleMember(...$numbers){
		if($numbers[0] === true){
			$this->battleMember = [];
		}else{
			foreach($numbers as $team_num){
				if(isset($this->battleMember[$team_num])) unset($this->battleMember[$team_num]);
			}
		}
	}

	public function removeBattleMember($user){
		$members_all = $this->battleMember;
		foreach($members_all as $team_num => $members){
			foreach($members as $member_num => $member){
				if($member === $user){
					unset($this->battleMember[$team_num][$member_num]);
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * プレイヤーにエフェクトを適用する
	 * @param  Player $player
	 * @param  int    $weapon_num                      削除の場合は0
	 * @param  bool   $removeAllEffect default = false エフェクトをすべて除去するかどうか(イカモード時はfalse推奨)
	 */
	public function applyEffect(Player $player, $weapon_num, $removeAllEffect = false){
		$user = $player->getName();
		$speedChange = true;//イカモード以外でも速度が変わる場合falseに変えてください
		$speedRate = $this->getMovementSpeedRate($player, $weapon_num);
		$moveSpeed = $player->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED);
		switch($weapon_num){
			case Weapon::PABLO:
			case Weapon::PABLO_HEW:
			case Weapon::PERMANENT_PABLO:
			case Weapon::OCTOBRUSH:
			case Weapon::OCTOBRUSH_HEW:
				if(!$player->hasEffect(Effect::SPEED))    $player->addEffect(Effect::getEffect(Effect::SPEED)->setDuration(150000)->setAmplifier(0)->setVisible(false));//移動速度上昇

				if($player->hasEffect(Effect::SLOWNESS))  $player->removeEffect(Effect::SLOWNESS);
				if($player->hasEffect(Effect::FATIGUE))   $player->removeEffect(Effect::FATIGUE);
				break;
/*			case Weapon::DYNAMO_ROLLER:
				if(!$player->hasEffect(Effect::SLOWNESS)) $player->addEffect(Effect::getEffect(Effect::SLOWNESS)->setDuration(150000)->setAmplifier(0)->setVisible(false));//移動速度低下
				if(!$player->hasEffect(Effect::FATIGUE))  $player->addEffect(Effect::getEffect(Effect::FATIGUE)->setDuration(150000)->setAmplifier(0)->setVisible(false));
				if($player->hasEffect(Effect::SPEED))     $player->removeEffect(Effect::SPEED);
				break;
			case Weapon::GAL_96:
				if(!$player->hasEffect(Effect::SLOWNESS)) $player->addEffect(Effect::getEffect(Effect::SLOWNESS)->setDuration(150000)->setAmplifier(0)->setVisible(false));//移動速度低下

				if($player->hasEffect(Effect::SPEED)) $player->removeEffect(Effect::SPEED);
				break;*/
			case Weapon::SPLOOSH_O_MATIC:
			case Weapon::SPLOOSH_O_MATIC_NEO:
			case Weapon::SPLOOSH_O_MATIC_SEVEN:
				if(!$player->hasEffect(Effect::SPEED))    $player->addEffect(Effect::getEffect(Effect::SPEED)->setDuration(150000)->setAmplifier(0)->setVisible(false));//移動速度上昇

				if($player->hasEffect(Effect::SLOWNESS))  $player->removeEffect(Effect::SLOWNESS);
				if($player->hasEffect(Effect::FATIGUE))   $player->removeEffect(Effect::FATIGUE);
				break;
			case Weapon::HEAVY_SPLATLING:
			case Weapon::HEAVY_SPLATLING_REMIX:
			case Weapon::HEAVY_SPLATLING_DECO:
			case Weapon::MINI_SPLATLING:
			case Weapon::MINI_SPLATLING_REPAIR:
			case Weapon::MINI_SPLATLING_COLLABO:
			case Weapon::HYDRA_SPLATLING:
			case Weapon::HYDRA_SPLATLING_CUSTOM:
			case Weapon::HYDRA_SPLATLING_REGULUS:
			case Weapon::T3_NOZZLENOSE:
			case Weapon::T3_NOZZLENOSE_D:
			case Weapon::T3_NOZZLENOSE_P:
			case Weapon::L3_NOZZLENOSE:
			case Weapon::L3_NOZZLENOSE_D:
			case Weapon::L3_NOZZLENOSE_ALTAIR:
				$speedChange = false;
				if(!$this->SplatlingChargeCheck($user)){
					$speedChange = true;
				}

			default:
				if($removeAllEffect){
					$player->removeAllEffects();
				}else{
					if($player->hasEffect(Effect::SPEED))     $player->removeEffect(Effect::SPEED);
					if($player->hasEffect(Effect::SLOWNESS))  $player->removeEffect(Effect::SLOWNESS);
					if($player->hasEffect(Effect::FATIGUE))   $player->removeEffect(Effect::FATIGUE);
				}
		}
		if($player->isSneaking()){
			$speedRate = 0.1;
		}
		if($speedChange){
			$moveSpeed->setDefaultValue(Weapon::MOVEMENT_SPEED_DEFAULT_VALUE * $speedRate);
			$moveSpeed->setValue($moveSpeed->getDefaultValue());
		}
	}

	 /*
	 * 遮蔽物があって攻撃が通らない場合にfalse、無い場合はtrueを返す
	 * @param Entity $ent1 攻撃者側
	 * @param Entity $ent2 被攻撃者側
	 */
	public function canAttack($ent1, $ent2){

		$level = Server::getInstance()->getDefaultLevel();
		$x1 = $ent1->x-0.5;
		$y1 = $ent1->y+1.5;
		$z1 = $ent1->z-0.5;

		$x2 = $ent2->x-0.5;
		$y2 = $ent2->y+1.5;
		$z2 = $ent2->z-0.5;

		$y = max($y1, $y2);
		$maxdist = max(abs($x2-$x1), abs($z2-$z1));

		if($maxdist == 0 || pow($x2-$x1, 2)+pow($z2-$z1, 2) <= 1){
			
			return true;
		}

		$xdist = ($x2-$x1)/$maxdist;
		$zdist = ($z2-$z1)/$maxdist;
		
		for($times = 0; $times <= $maxdist; $times++){

			$bid = $level->getBlockIdAt(floor($x1+$xdist*$times), $y, floor($z1+$zdist*$times));
			if(!$this->canThrough($bid)){
				
				return false;
				break;
			}
		}
		return true;
	}

	/**
	 * ブロックを攻撃が貫通できるかを返す
	 *
	 * @param int $blockId
	 */
	public function canThrough($blockId){
		switch($blockId){
			case 0:
			case 8:
			case 9:
			case 10:
			case 11:
			case 50:
			case 52:
			case 101:
			case 208:
				return true;
			break;
			default:
				return false;
		}
	}



	/*
	 * ボムなどの爆発
	 * @param Entity $entity
	 * @param Player $player
	 * @param double $x
	 * @param double $y
	 * @param double $z
	 * @param Block $block (wool)
	 * @param double $radius
	 * @param int $power
	 * @param array $array (攻撃対象となるプレイヤー名の配列)
	 *  @param int $paint (塗り範囲)
	 */
	public function bomb($entity, $player, $x, $y, $z, $block, $radius, $power, $array, $paint){

		$level = $player->getLevel();
		$radius_1 = $radius/2; //球の半径
		//$radius_2 = $radius*1.25; //球の半径
		$radius_3 = $radius; //球の半径
		$pos_ar = [];
		
		$F = function($array){

			$array[0]->addParticle($array[1]);
		};
		$x = round($x, 0, PHP_ROUND_HALF_DOWN);
		$y = round($y, 0, PHP_ROUND_HALF_DOWN);
		$z = round($z, 0, PHP_ROUND_HALF_DOWN);
		$p = new Vector3($x, $y, $z);
		$particle_1 = new DestroyBlockParticle($p, $block);
		$level->addParticle($particle_1);
		$level->addSound(new ExplodeSound($p));

		for($xxx = -floor($paint/2); $xxx < ceil($paint/2); $xxx++){

			for($yyy = -floor($paint/2); $yyy < ceil($paint/2); $yyy++){ 
			
				for($zzz = -floor($paint/2); $zzz < ceil($paint/2); $zzz++){ 
				
					//$pos = new Vector3(floor($xxx+$x), floor($yyy+$y), floor($zzz+$z));
					
					if($level->getBlockIdAt(round($xxx+$x, 0, PHP_ROUND_HALF_DOWN), round($yyy+$y, 0, PHP_ROUND_HALF_DOWN), round($zzz+$z, 0, PHP_ROUND_HALF_DOWN)) !== 0){
						
						//塗り
						//$level->setBlock($pos, $block);
						$pos_ar[] = [floor($xxx+$x), floor($yyy+$y), floor($zzz+$z)];
					}
					if(abs($yyy) <= round($paint/2, 0, PHP_ROUND_HALF_DOWN) && (abs($xxx) == round($paint/2, 0, PHP_ROUND_HALF_DOWN) || abs($zzz) == round($paint/2, 0, PHP_ROUND_HALF_DOWN)) && $level->getBlockIdAt(round($xxx*2+$x, 0, PHP_ROUND_HALF_DOWN), round($yyy+$y, 0, PHP_ROUND_HALF_DOWN), round($zzz*2+$z, 0, PHP_ROUND_HALF_DOWN)) !== 0){
						$pos_ar[] = [round($xxx*2+$x, 0, PHP_ROUND_HALF_DOWN), round($yyy+$y, 0, PHP_ROUND_HALF_DOWN), round($zzz*2+$z, 0, PHP_ROUND_HALF_DOWN)];
					}
				}
			}			
		}
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$color = $playerData->getColor();
		$result = $this->changeWoolsColor($level, $pos_ar, $color, $user);

		for($yaw = 0; $yaw < 360; $yaw += 360/(2*M_PI*$radius)){

			for($pitch = 0; $pitch <360; $pitch += 360/(2*M_PI*$radius)){

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

/*				$p->x = $x+$xx*$radius_2;
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
		if($this->main->dev == 2){
			$array = $level->getEntities();
			foreach($array as $en){
				if((!$player instanceof Player) or (!Enemy::isEnemy($en))){
					continue;
				}
				$distance = sqrt(pow($x - $en->x, 2) + pow($y - $en->y, 2) + pow($z - $en->z, 2));

				if($entity != $en && $player != $en && $distance <= $radius + $radius * 2 / 3){
					if($this->canAttack($entity, $en)){
						$dmg = floor($power - $power * 2 / 3 * ($distance / ($radius + $radius * 2/ 3)));
						$en->attack($dmg, new EntityDamageByEntityEvent($player, $en, EntityDamageEvent::CAUSE_ENTITY_EXPLOSION, $dmg, 0.2));
					}
				}
			}
		}else{
			foreach($array as $e){
				$en = Server::getInstance()->getPlayer($e);
				if((!$player instanceof Player) or (!$en instanceof Player)){
					continue;
				}
				if($this->main->canAttack($player->getName(), $en->getName())['result']){
					$distance = sqrt(pow($x - $en->x, 2) + pow($y - $en->y, 2) + pow($z - $en->z, 2));

					if($entity != $en && $player != $en && $distance <= $radius + $radius * 2 / 3){
						if($this->canAttack($entity, $en)){
							$dmg = floor($power - $power * 2 / 3 * ($distance / ($radius + $radius * 2/ 3)));
							$en->attack($dmg, new EntityDamageByEntityEvent($player, $en, EntityDamageEvent::CAUSE_ENTITY_EXPLOSION, $dmg, 0.2));
						}
					}
				}
			}
		}
		return $result;
	}

	/**
	 * パブロ(長押し)
	 * @param Player $player
	 * @param int    $x
	 * @param int    $y
	 * @param int    $z
	 */
	public function Pablo(Player $player, $x, $y, $z){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		if($playerData->getInk() < floor($playerData->getInkConsumption()/3)){
			return false;
		}
		$color = $playerData->getColor();
		$level = $player->getLevel();
		$syatei = 1;
		$yaw = $player->yaw;
		//yaw1の値を変えると左右のインクの広がり方が変わります
		$yaw1 = $yaw + 90;
		$pitch = 0;
		$pos_ar[] = [$x, $y, $z];
		$d = mt_rand(0, 1) ? $syatei : -$syatei;
		$xx1 = -$d * sin(deg2rad($yaw1)) * cos(deg2rad($pitch - 180));
		$zz1 =  $d * cos(deg2rad($yaw1)) * cos(deg2rad($pitch - 180));
		$pos_ar[] = [Math::floorFloat($x + $xx1), $y, Math::floorFloat($z + $zz1)];
		$this->changeWoolsColor($level, $pos_ar, $color, $user);
		return true;
	}

	/**
	 * パブロ(振り下ろし)
	 * @param Player $player
	 * @param int    $x
	 * @param int    $y
	 * @param int    $z
	 */
	public function Pablo_T(Player $player, $x, $y, $z){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		if($playerData->getInk() < $playerData->getInkConsumption()){
			return false;
		}
		$color = $playerData->getColor();
		$level = $player->getLevel();

		$yaw = $player->yaw;
		$yaw1 = $yaw + rand(0, 4) - 4;
		$yaw2 = $yaw + rand(0, 9) - 4;
		$yaw3 = $yaw + 8 + rand(0, 9) - 4;
		$yaw4 = $yaw - 8 + rand(0, 9) - 4;
		$yaw5 = $yaw + 25 + rand(0, 9) - 4;
		$yaw6 = $yaw - 30 + rand(0, 9) - 4;
		$yaw7 = $yaw + 30 + rand(0, 9) - 4;
		$pitch = 0;

		$xx1 = $x +      (3 + rand(0, 3) - 1) * sin(deg2rad($yaw1)) * cos(deg2rad($pitch - 180));
		$xx2 = $x +      (3 + rand(0, 3) - 1) * sin(deg2rad($yaw2)) * cos(deg2rad($pitch - 180));
		$xx3 = $x +      (3 + rand(0, 3) - 1) * sin(deg2rad($yaw3)) * cos(deg2rad($pitch - 180));
		$xx4 = $x +      (3 + rand(0, 3) - 1) * sin(deg2rad($yaw4)) * cos(deg2rad($pitch - 180));
		$xx5 = $x +      (4 + rand(0, 3) - 1) * sin(deg2rad($yaw5)) * cos(deg2rad($pitch - 180));
		$xx6 = $x +      (5 + rand(0, 5) - 1) * sin(deg2rad($yaw6)) * cos(deg2rad($pitch - 180));
		$xx7 = $x +      (5 + rand(0, 5) - 1) * sin(deg2rad($yaw7)) * cos(deg2rad($pitch - 180));
		$zz1 = $z + -1 * (3 + rand(0, 3) - 1) * cos(deg2rad($yaw1)) * cos(deg2rad($pitch - 180));
		$zz2 = $z + -1 * (3 + rand(0, 3) - 1) * cos(deg2rad($yaw2)) * cos(deg2rad($pitch - 180));
		$zz3 = $z + -1 * (3 + rand(0, 3) - 1) * cos(deg2rad($yaw3)) * cos(deg2rad($pitch - 180));
		$zz4 = $z + -1 * (3 + rand(0, 3) - 1) * cos(deg2rad($yaw4)) * cos(deg2rad($pitch - 180));
		$zz5 = $z + -1 * (4 + rand(0, 3) - 1) * cos(deg2rad($yaw5)) * cos(deg2rad($pitch - 180));
		$zz6 = $z + -1 * (5 + rand(0, 5) - 1) * cos(deg2rad($yaw6)) * cos(deg2rad($pitch - 180));
		$zz7 = $z + -1 * (5 + rand(0, 5) - 1) * cos(deg2rad($yaw7)) * cos(deg2rad($pitch - 180));

		$x_ar = [$x, $xx1, $xx2, $xx3, $xx4, $xx5, $xx6, $xx7];
		$z_ar = [$z, $zz1, $zz2, $zz3, $zz4, $zz5, $zz6, $zz7];

		$pos_ar = [
			[$x, $y, $z],
			[$xx1, $y, $zz1],
			[$xx2, $y, $zz2],
			[$xx3, $y, $zz3],
			[$xx4, $y, $zz4],
			[$xx5, $y, $zz5],
			[$xx6, $y, $zz6],
			[$xx7, $y, $zz7],
			[$x, $y+1, $z],
			[$xx1, $y+1, $zz1],
			[$xx2, $y+1, $zz2],
			[$xx3, $y+1, $zz3],
			[$xx4, $y+1, $zz4],
			[$xx5, $y+1, $zz5],
			[$xx6, $y+1, $zz6],
			[$xx7, $y+1, $zz7]
		];
		$ran2 = mt_rand(0, 3);
		$pos2 = $pos_ar[$ran2];
		$pos_ar[] = [$pos2[0]-1, $pos2[1], $pos2[2]];
		$pos_ar[] = [$pos2[0]+1, $pos2[1], $pos2[2]];
		$pos_ar[] = [$pos2[0], $pos2[1], $pos2[2]-1];
		$pos_ar[] = [$pos2[0], $pos2[1], $pos2[2]+1];
		$pos_ar[] = [$pos2[0]-1, $pos2[1]+1, $pos2[2]];
		$pos_ar[] = [$pos2[0]+1, $pos2[1]+1, $pos2[2]];
		$pos_ar[] = [$pos2[0], $pos2[1]+1, $pos2[2]-1];
		$pos_ar[] = [$pos2[0], $pos2[1]+1, $pos2[2]+1];

		$ran3 = mt_rand(4, 7);
		$pos3 = $pos_ar[$ran3];
		$pos_ar[] = [$pos3[0]-1, $pos3[1], $pos3[2]];
		$pos_ar[] = [$pos3[0]+1, $pos3[1], $pos3[2]];
		$pos_ar[] = [$pos3[0], $pos3[1], $pos3[2]-1];
		$pos_ar[] = [$pos3[0], $pos3[1], $pos3[2]+1];
		$pos_ar[] = [$pos3[0]-1, $pos3[1]+1, $pos3[2]];
		$pos_ar[] = [$pos3[0]+1, $pos3[1]+1, $pos3[2]];
		$pos_ar[] = [$pos3[0], $pos3[1]+1, $pos3[2]-1];
		$pos_ar[] = [$pos3[0], $pos3[1]+1, $pos3[2]+1];
		$this->changeWoolsColor($level, $pos_ar, $color, $user);
		$this->Attack_range(new AxisAlignedBB(min($x_ar) + 1, $y - 0.5, min($z_ar), max($x_ar) + 1,  $y + 3, max($z_ar) - 1), $this->getWeaponData(self::PABLO)[6], $level, $player);
		$playerData->setRate();
		return true;
	}
	/**
	 * ホクサイ
	 * @param Player $player
	 * @param int    $x
	 * @param int    $y
	 * @param int    $z
	 */
	public function Octobrush(Player $player, $x, $y, $z){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		if($playerData->getInk() < floor($playerData->getInkConsumption())){
			return false;
		}
		$color = $playerData->getColor();
		$level = $player->getLevel();
		$syatei = 0.75;
		$yaw = $player->yaw;
		//yaw1の値を変えると左右のインクの広がり方が変わります
		$yaw1 = $yaw + 90;
		$pitch = 0;
		$pos_ar = [
			[$x, $y, $z]
		];
		for($d = -$syatei; $d <= $syatei + 1; $d += 1){
			$xx1 =  $d * sin(deg2rad($yaw1)) * cos(deg2rad($pitch - 180));
			$zz1 = -$d * cos(deg2rad($yaw1)) * cos(deg2rad($pitch - 180));
			$pos_ar[] = [Math::floorFloat($x + $xx1), $y, Math::floorFloat($z + $zz1)];
		}
		$this->changeWoolsColor($level, $pos_ar, $color, $user);
		return true;
	}

	/**
	 * ホクサイ(振り下ろし)
	 * @param Player $player
	 * @param int    $x
	 * @param int    $y
	 * @param int    $z
	 */
	public function Octobrush_T(Player $player, $y){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		if($playerData->getInk() < $playerData->getInkConsumption()){
			return false;
		}
		$color = $playerData->getColor();
		$level = $player->getLevel();

		$yaw = $player->yaw;
		$x = floor($player->x-sin(deg2rad($yaw))*2);
		$z = floor($player->z+cos(deg2rad($yaw))*2);
		//yaw1とyaw2とyaw3の値を変えると左右のインクの広がり方が変わります
		//Before = $yaw + 35 + rand(0, 8) - 4;
		$yaw1 = $yaw + 40 + rand(0, 8) - 4;
		$yaw2 = $yaw - 40 + rand(0, 8) - 4;
		$yaw3 = $yaw + rand(0, 8) - 4;
		$yaw4 = $yaw - rand(0, 8) - 4;

		$yaw1_rad = deg2rad($yaw1);
		$yaw2_rad = deg2rad($yaw2);
		$yaw3_rad = deg2rad($yaw3);
		$yaw4_rad = deg2rad($yaw4);
		$pitch = 0;
		$pitch_rad = deg2rad($pitch - 180);
		$pitch_cos = cos($pitch_rad);

		$xx1 = $x + (     (4 + rand(0, 2) - 1) * sin($yaw1_rad) * $pitch_cos);
		$xx2 = $x + (     (4 + rand(0, 2) - 1) * sin($yaw2_rad) * $pitch_cos);
		$xx3 = $x + (     (4 + rand(0, 2) - 1) * sin($yaw3_rad) * $pitch_cos);
		$xx4 = $x + (     (4 + rand(0, 2) - 1) * sin($yaw4_rad) * $pitch_cos);

		$zz1 = $z + (-1 * (4 + rand(0, 2) - 1) * cos($yaw1_rad) * $pitch_cos);
		$zz2 = $z + (-1 * (4 + rand(0, 2) - 1) * cos($yaw2_rad) * $pitch_cos);
		$zz3 = $z + (-1 * (4 + rand(0, 2) - 1) * cos($yaw3_rad) * $pitch_cos);
		$zz4 = $z + (-1 * (4 + rand(0, 2) - 1) * cos($yaw4_rad) * $pitch_cos);

		$pos_ar = [
			[$x,     $y, $z],
			[$x + 1, $y, $z],
			[$x - 1, $y, $z],
			[$x,     $y, $z + 1],
			[$x,     $y, $z - 1],

			[$xx1,     $y, $zz1],
			[$xx1 + 1, $y, $zz1],
			[$xx1 - 1, $y, $zz1],
			[$xx1,     $y, $zz1 + 1],
			[$xx1,     $y, $zz1 - 1],

			[$xx2,     $y, $zz2],
			[$xx2 + 1, $y, $zz2],
			[$xx2 - 1, $y, $zz2],
			[$xx2,     $y, $zz2 + 1],
			[$xx2,     $y, $zz2 - 1],

			[$xx3,     $y, $zz3],
			[$xx3 + 1, $y, $zz3],
			[$xx3 - 1, $y, $zz3],
			[$xx3,     $y, $zz3 + 1],
			[$xx3,     $y, $zz3 - 1],

			[$x,     $y+1, $z],
			[$x + 1, $y+1, $z],
			[$x - 1, $y+1, $z],
			[$x,     $y+1, $z + 1],
			[$x,     $y+1, $z - 1],

			[$xx1,     $y+1, $zz1],
			[$xx1 + 1, $y+1, $zz1],
			[$xx1 - 1, $y+1, $zz1],
			[$xx1,     $y+1, $zz1 + 1],
			[$xx1,     $y+1, $zz1 - 1],

			[$xx2,     $y+1, $zz2],
			[$xx2 + 1, $y+1, $zz2],
			[$xx2 - 1, $y+1, $zz2],
			[$xx2,     $y+1, $zz2 + 1],
			[$xx2,     $y+1, $zz2 - 1],

			[$xx3,     $y+1, $zz3],
			[$xx3 + 1, $y+1, $zz3],
			[$xx3 - 1, $y+1, $zz3],
			[$xx3,     $y+1, $zz3 + 1],
			[$xx3,     $y+1, $zz3 - 1],

			[$xx4,     $y+1, $zz4],
			[$xx4 + 1, $y+1, $zz4],
			[$xx4 - 1, $y+1, $zz4],
			[$xx4,     $y+1, $zz4 + 1],
			[$xx4,     $y+1, $zz4 - 1],
		];
		$this->changeWoolsColor($level, $pos_ar, $color, $user);
		$x_ar = [$x, $x + 1, $x - 1, $xx1, $xx1 + 1, $xx1 - 1, $xx2, $xx2 + 1, $xx2 - 1, $xx3, $xx3 + 1, $xx3 - 1, $xx4, $xx4 + 1, $xx4 - 1];
		$z_ar = [$z, $z + 1, $z - 1, $zz1, $zz1 + 1, $zz1 - 1, $zz2, $zz2 + 1, $zz2 - 1, $zz3, $zz3 + 1, $zz3 - 1, $zz4, $zz4 + 1, $zz4 - 1];
		$this->Attack_range(new AxisAlignedBB(min($x_ar) - 0.5, $y - 0.5, min($z_ar) - 0.5, max($x_ar) + 0.5, $y + 3.3, max($z_ar) + 0.5), $this->getWeaponData(self::OCTOBRUSH)[6], $level, $player);
		$playerData->setRate();
		return true;
	}

	/**
	 * ウィレム
	 * @param Player $player
	 * @param int    $x
	 * @param int    $y
	 * @param int    $z
	 */
	public function Willem(Player $player, $x, $y, $z){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		if($playerData->getInk() < floor($playerData->getInkConsumption())){
			return false;
		}
		$color = $playerData->getColor();
		$level = $player->getLevel();
		$syatei = 1;
		$yaw = $player->yaw;
		//yaw1の値を変えると左右のインクの広がり方が変わります
		$yaw1 = $yaw + 90;
		$pitch = 0;
		$pos_ar = [
			[$x, $y, $z]
		];
		for($d = -$syatei; $d <= $syatei + 1; $d += 1){
			$xx1 =  $d * sin(deg2rad($yaw1)) * cos(deg2rad($pitch - 180));
			$zz1 = -$d * cos(deg2rad($yaw1)) * cos(deg2rad($pitch - 180));
			$pos_ar[] = [Math::floorFloat($x + $xx1), $y, Math::floorFloat($z + $zz1)];
		}
		$this->changeWoolsColor($level, $pos_ar, $color, $user);
		return true;
	}

	/**
	 * ウィレム(振り下ろし)
	 * @param Player $player
	 * @param int    $x
	 * @param int    $y
	 * @param int    $z
	 */
	public function Willem_T(Player $player, $y){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		if($playerData->getInk() < $playerData->getInkConsumption()){
			return false;
		}
		$color = $playerData->getColor();
		$level = $player->getLevel();
		$yaw = $player->yaw;
		$x = floor($player->x-sin(deg2rad($yaw)));
		$z = floor($player->z+cos(deg2rad($yaw)));
		//yaw1とyaw2とyaw3の値を変えると左右のインクの広がり方が変わります
		//Before = $yaw + 35 + rand(0, 8) - 4;
		$yaw1 = $yaw + 40 + rand(0, 8) - 4;
		$yaw2 = $yaw - 40 + rand(0, 8) - 4;
		$yaw3 = $yaw + rand(0, 8) - 4;
		$yaw4 = $yaw - rand(0, 12) - 6;
		$yaw1_rad = deg2rad($yaw1);
		$yaw2_rad = deg2rad($yaw2);
		$yaw3_rad = deg2rad($yaw3);
		$yaw4_rad = deg2rad($yaw4);

		$pitch = 0;
		$pitch_rad = deg2rad($pitch - 180);
		$pitch_cos = cos($pitch_rad);

		$xx1 = $x + (     (4 + rand(0, 4) - 1) * sin($yaw1_rad) * $pitch_cos);
		$xx2 = $x + (     (4 + rand(0, 4) - 1) * sin($yaw2_rad) * $pitch_cos);
		$xx3 = $x + (     (4 + rand(0, 4) - 1) * sin($yaw3_rad) * $pitch_cos);
		$xx4 = $x + (     (4 + rand(0, 4) - 1) * sin($yaw3_rad) * $pitch_cos);
		$xx5 = $x + (     (4 + rand(0, 4) - 1) * sin($yaw3_rad) * $pitch_cos);
		$xx6 = $x + (     (4 + rand(0, 4) - 1) * sin($yaw4_rad) * $pitch_cos);
		$zz1 = $z + (-1 * (4 + rand(0, 4) - 1) * cos($yaw1_rad) * $pitch_cos);
		$zz2 = $z + (-1 * (4 + rand(0, 4) - 1) * cos($yaw2_rad) * $pitch_cos);
		$zz3 = $z + (-1 * (4 + rand(0, 4) - 1) * cos($yaw3_rad) * $pitch_cos);
		$zz4 = $z + (-1 * (4 + rand(0, 4) - 1) * cos($yaw3_rad) * $pitch_cos);
		$zz5 = $z + (-1 * (4 + rand(0, 4) - 1) * cos($yaw3_rad) * $pitch_cos);
		$zz6 = $z + (-1 * (4 + rand(0, 4) - 1) * cos($yaw4_rad) * $pitch_cos);

		$pos_ar = [
			[$x,     $y, $z],
			[$x + 1, $y, $z],
			[$x - 1, $y, $z],
			[$x,     $y, $z + 1],
			[$x,     $y, $z - 1],

			[$xx1,     $y, $zz1],
			[$xx1 + 1, $y, $zz1],
			[$xx1 - 1, $y, $zz1],
			[$xx1,     $y, $zz1 + 1],
			[$xx1,     $y, $zz1 - 1],

			[$xx2,     $y, $zz2],
			[$xx2 + 1, $y, $zz2],
			[$xx2 - 1, $y, $zz2],
			[$xx2,     $y, $zz2 + 1],
			[$xx2,     $y, $zz2 - 1],

			[$xx3,     $y, $zz3],
			[$xx3 + 1, $y, $zz3],
			[$xx3 - 1, $y, $zz3],
			[$xx3,     $y, $zz3 + 1],
			[$xx3,     $y, $zz3 - 1],

			[$xx4,     $y, $zz4],
			[$xx4 + 1, $y, $zz4],
			[$xx4 - 1, $y, $zz4],
			[$xx4,     $y, $zz4 + 1],
			[$xx4,     $y, $zz4 - 1],

			[$xx5,     $y, $zz5],
			[$xx5 + 1, $y, $zz5],
			[$xx5 - 1, $y, $zz5],
			[$xx5,     $y, $zz5 + 1],
			[$xx5,     $y, $zz5 - 1],

			[$x,     $y+1, $z],
			[$x + 1, $y+1, $z],
			[$x - 1, $y+1, $z],
			[$x,     $y+1, $z + 1],
			[$x,     $y+1, $z - 1],

			[$xx1,     $y+1, $zz1],
			[$xx1 + 1, $y+1, $zz1],
			[$xx1 - 1, $y+1, $zz1],
			[$xx1,     $y+1, $zz1 + 1],
			[$xx1,     $y+1, $zz1 - 1],

			[$xx2,     $y+1, $zz2],
			[$xx2 + 1, $y+1, $zz2],
			[$xx2 - 1, $y+1, $zz2],
			[$xx2,     $y+1, $zz2 + 1],
			[$xx2,     $y+1, $zz2 - 1],

			[$xx3,     $y+1, $zz3],
			[$xx3 + 1, $y+1, $zz3],
			[$xx3 - 1, $y+1, $zz3],
			[$xx3,     $y+1, $zz3 + 1],
			[$xx3,     $y+1, $zz3 - 1],

			[$xx4,     $y+1, $zz4],
			[$xx4 + 1, $y+1, $zz4],
			[$xx4 - 1, $y+1, $zz4],
			[$xx4,     $y+1, $zz4 + 1],
			[$xx4,     $y+1, $zz4 - 1],

			[$xx5,     $y+1, $zz5],
			[$xx5 + 1, $y+1, $zz5],
			[$xx5 - 1, $y+1, $zz5],
			[$xx5,     $y+1, $zz5 + 1],
			[$xx5,     $y+1, $zz5 - 1],

			[$xx6,     $y+1, $zz6],
			[$xx6 + 1, $y+1, $zz6],
			[$xx6 - 1, $y+1, $zz6],
			[$xx6,     $y+1, $zz6 + 1],
			[$xx6,     $y+1, $zz6 - 1],
		];
		$this->changeWoolsColor($level, $pos_ar, $color, $user);
		$x_ar = [$x, $x + 1, $x - 1, $xx1, $xx1 + 1, $xx1 - 1, $xx2, $xx2 + 1, $xx2 - 1, $xx3, $xx3 + 1, $xx3 - 1, $xx4, $xx4 + 1, $xx4 - 1, $xx5, $xx5 + 1, $xx5 - 1, $xx6, $xx6 + 1, $xx6 - 1];
		$z_ar = [$z, $z + 1, $z - 1, $zz1, $zz1 + 1, $zz1 - 1, $zz2, $zz2 + 1, $zz2 - 1, $zz3, $zz3 + 1, $zz3 - 1, $zz4, $zz4 + 1, $zz4 - 1, $zz5, $zz5 + 1, $zz5 - 1, $zz6, $zz6 + 1, $zz6 - 1];
		$this->Attack_range(new AxisAlignedBB(min($x_ar) - 0.5, $y - 0.5, min($z_ar) - 0.5, max($x_ar) + 0.5, $y + 3.3, max($z_ar) + 0.5), $this->getWeaponData(self::WILLEM)[6], $level, $player);
		$playerData->setRate();
		return true;
	}


	/**
	 * スプラローラー(長押し)
	 * @param Player $player
	 * @param int    $x
	 * @param int    $y
	 * @param int    $z
	 */
	public function SplatRoller(Player $player, $x, $y, $z){
		$pos_ar = [];
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		if($playerData->getInk() < floor($playerData->getInkConsumption()/3)){
			return false;
		}
		$color = $playerData->getColor();
		$level = $player->getLevel();
		/*
		$pos_ar = [
			[$x + 1, $y, $z],
			[$x, $y, $z],
			[$x - 1, $y, $z],
			[$x + 1, $y, $z - 1],
			[$x, $y, $z - 1],
			[$x - 1, $y, $z - 1],
			[$x + 1, $y, $z + 1],
			[$x, $y, $z + 1],
			[$x - 1, $y, $z + 1],
			[$x + 1, $y + 1, $z],
			[$x, $y + 1, $z],
			[$x - 1, $y + 1, $z],
			[$x + 1, $y + 1, $z - 1],
			[$x, $y + 1, $z - 1],
			[$x - 1, $y + 1, $z - 1],
			[$x + 1, $y + 1, $z + 1],
			[$x, $y + 1, $z + 1],
			[$x - 1, $y + 1, $z + 1],
		];
		*/
		//11/16 もやさんのを参考に作ったコード(0929hitoshi)
		$syatei = 2;
		$yaw = $player->yaw;
		//yaw1の値を変えると左右のインクの広がり方が変わります
		$yaw1 = $yaw + 90;
		$yaw1_rad = deg2rad($yaw1);
		$pitch = 0;
		$pitch_rad = deg2rad($pitch - 180);
		for($d = -$syatei; $d <= $syatei + 1; $d+=1){
			//$xx1 = $d * sin(deg2rad($yaw1)) * cos(deg2rad($pitch - 180));
			//$zz1 = -$d * cos(deg2rad($yaw1)) * cos(deg2rad($pitch - 180));
			$xx1 = -$d * sin($yaw1_rad) * cos($pitch_rad);
			$zz1 =  $d * cos($yaw1_rad) * cos($pitch_rad);
			$pos_ar[] = [floor($x + $xx1), $y, floor($z + $zz1)];
		}
		$this->changeWoolsColor($level, $pos_ar, $color, $user);
		$members_all = $this->battleMember;
		foreach ($members_all as $team_num => $members){
			foreach($members as $member){
				$player_v = Server::getInstance()->getPlayer($member);
				if($player_v instanceof Player){
					$canAttack = $this->main->canAttack($member, $user)['result'];
					if($canAttack){
						$vx = $player_v->x;
						$vy = $player_v->y;
						$vz = $player_v->z;

						if($this->inOval($x, $z, $vx, $vz, 3.4, 1.9, $yaw) && abs($vy-1-$y) <= 1 && $player->isOnGround()){
						//ダイナモローラーの場合はinOvalの第5,6引数が4.5,2.2で、ダメージが7.2です
							$damage = $this->getAttackDamage(self::SPLAT_ROLLER);
							$knockback = $this->getKnockbackValue(self::SPLAT_ROLLER);
							$ev = new EntityDamageByEntityEvent($player, $player_v, EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK, $damage, $knockback);
							$player_v->attack($damage, $ev);
						}
					}
				}
			}
		}
		return true;
	}

	/**
	 * スプラローラー(振り下ろし)
	 * @param Player $player
	 * @param int    $x
	 * @param int    $y
	 * @param int    $z
	 */
	public function SplatRoller_T(Player $player, $y){
		$pos_ar = [];
		$yaw = $player->yaw;
		$x = floor($player->x-sin(deg2rad($yaw))*2);
		$z = floor($player->z+cos(deg2rad($yaw))*2);
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		if($playerData->getInk() < $playerData->getInkConsumption()){
			return false;
		}
		$color = $playerData->getColor();
		$level = $player->getLevel();
		$kakudo = 4;
		$yaw = $player->yaw;
		$pitch = 0;
		$rad_p = deg2rad($pitch - 180);
		$x_ar = [];
		$z_ar = [];
		for($d = -$kakudo; $d <= $kakudo; $d++){
			$rad_y = deg2rad($yaw + $d * 10);
			$sincos = sin($rad_y) * cos($rad_p);
			$coscos = cos($rad_y) * cos($rad_p);
			$x_1 = floor($x +  (5.55 + rand(0, 3)) * $sincos);
			$z_1 = floor($z + -(5.55 + rand(0, 3)) * $coscos);
			$x_2 = floor($x +  (5.55 + rand(0, 3)) * $sincos);
			$z_2 = floor($z + -(5.55 + rand(0, 3)) * $coscos);
			$x_3 = floor($x +  (5.55 + rand(0, 5)) * $sincos);
			$z_3 = floor($z + -(5.55 + rand(0, 5)) * $coscos);
			$x_4 = floor($x +  3.55 * $sincos);
			$z_4 = floor($z + -3.55 * $coscos);
			$x_5 = floor($x +  4.55 * $sincos);
			$z_5 = floor($z + -4.55 * $coscos);
			$pos_ar[] = [$x_1, $y, $z_1];
			$pos_ar[] = [$x_2, $y, $z_2];
			$pos_ar[] = [$x_3, $y, $z_3];
			$pos_ar[] = [$x_4, $y, $z_4];
			$pos_ar[] = [$x_5, $y, $z_5];
			$pos_ar[] = [$x_1, $y+1, $z_1];
			$pos_ar[] = [$x_2, $y+1, $z_2];
			$pos_ar[] = [$x_3, $y+1, $z_3];
			$pos_ar[] = [$x_4, $y+1, $z_4];
			$pos_ar[] = [$x_5, $y+1, $z_5];
			$x_ar[] = $x_1;
			$x_ar[] = $x_2;
			$x_ar[] = $x_3;
			$x_ar[] = $x_4;
			$x_ar[] = $x_5;
			$z_ar[] = $z_1;
			$z_ar[] = $z_2;
			$z_ar[] = $z_3;
			$z_ar[] = $z_4;
			$z_ar[] = $z_5;
		}
		$this->changeWoolsColor($level, $pos_ar, $color, $user);
		$this->Attack_range(new AxisAlignedBB(min($x_ar) - 1, $y - 0.75, min($z_ar) - 1, max($x_ar) + 1, $y + 3.3, max($z_ar) + 1), $this->getWeaponData(self::SPLAT_ROLLER)[6], $level, $player);
		$playerData->setRate();
		return true;
	}

	/**
	 * ダイナモローラー(長押し)
	 * @param Player $player
	 * @param int    $x
	 * @param int    $y
	 * @param int    $z
	 */
	public function DynamoRoller(Player $player, $x, $y, $z){
		$pos_ar = [];
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		if($playerData->getInk() < floor($playerData->getInkConsumption()/3)){
			return false;
		}
		$color = $playerData->getColor();
		$level = $player->getLevel();
		//11/16 もやさんのを参考に作ったコード(0929hitoshi)
		$syatei = 2.75;
		$yaw = $player->yaw;
		$yaw1 = ($yaw + 90) % 360;
		$pitch = 0;
		$rad_p = deg2rad($pitch - 180);
		$xx =      sin(deg2rad($yaw)) * cos($rad_p);
		$zz = -1 * cos(deg2rad($yaw)) * cos($rad_p);
		// $xx = -1 * sin(deg2rad($yaw)) * cos(deg2rad($pitch - 180));
		// $zz = cos(deg2rad($yaw)) * cos(deg2rad($pitch - 180));
		for($d = -$syatei; $d <= $syatei + 1; $d+=1){
			//$xx1 = $d * sin(deg2rad($yaw1)) * cos(deg2rad($pitch - 180));
			//$zz1 = -$d * cos(deg2rad($yaw1)) * cos(deg2rad($pitch - 180));
			$xx1 = -$d * sin(deg2rad($yaw1)) * cos($rad_p);
			$zz1 =  $d * cos(deg2rad($yaw1)) * cos($rad_p);
			$pos_ar[] = [floor($x + $xx1 + $xx), $y, floor($z + $zz1 + $zz)];
			$pos_ar[] = [floor($x + $xx1), $y, floor($z + $zz1)];
		}
		$this->changeWoolsColor($level, $pos_ar, $color, $user);
		$members_all = $this->battleMember;
		foreach ($members_all as $team_num => $members){
			foreach($members as $member){
				$player_v = Server::getInstance()->getPlayer($member);
				if($player_v instanceof Player){
					$canAttack = $this->main->canAttack($member, $user)['result'];
					if($canAttack){
						$vx = $player_v->x;
						$vy = $player_v->y;
						$vz = $player_v->z;
						if($this->inOval($x, $z, $vx, $vz, 4.5, 2.2, $yaw) && abs($vy-1-$y) <= 1 && $player->isOnGround()){
						//ダイナモローラーの場合はinOvalの第5,6引数が4.5,2.2で、ダメージが7.2です
							$damage = $this->getAttackDamage(self::DYNAMO_ROLLER);
							$knockback = $this->getKnockbackValue(self::DYNAMO_ROLLER);
							$ev = new EntityDamageByEntityEvent($player, $player_v, EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK, $damage, $knockback);
							$player_v->attack($damage, $ev);
						}
					}
				}
			}
		}
		return true;
	}

	/**
	 * ダイナモローラー(降り下ろし)
	 * @param Player $player
	 * @param int    $x
	 * @param int    $y
	 * @param int    $z
	 */
	public function DynamoRoller_T(Player $player, $x, $y, $z){
		$pos_ar = [];
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		if($playerData->getInk() < $playerData->getInkConsumption()){
			return false;
		}
		$color = $playerData->getColor();
		$level = $player->getLevel();
		$kakudo = 5;
		//$y_low = $y - 3.8;
		$y_low = $y - 10;
		$yaw = $player->yaw;
		$pitch = 0;
		$rad_p = deg2rad($pitch - 180);
		$x_ar = [];
		$y_ar = [];
		$z_ar = [];
		for($d = -$kakudo; $d <= $kakudo; $d++){
			$rad_y = deg2rad($yaw + $d * 10);
			$sincos = sin($rad_y) * cos($rad_p);
			$coscos = cos($rad_y) * cos($rad_p);
			for($n = 0; $n <= 6; $n++){
				$xx = floor($x +  (5 + mt_rand(0, 6)) * $sincos);
				$zz = floor($z + -(5 + mt_rand(0, 6)) * $coscos);
				$x_ar[] = $xx;
				$z_ar[] = $zz;
				for($yy = $y, $once = false; $y_low <= $yy; $yy--){
					//if($this->main->isWool($xx, $yy, $zz)){
					if($level->getBlockIdAt($xx, $yy, $zz) !== 0){
						$pos_ar[] = [$xx, $yy, $zz];
						$y_ar[] = $yy;
						$once = true;
						break;
					}else{
						if($once) break;
					}
				}
			}
			$x_4 = floor($x +  4 * $sincos);
			$z_4 = floor($z + -4 * $coscos);
			$x_5 = floor($x +  5 * $sincos);
			$z_5 = floor($z + -5 * $coscos);
			$x_ar[] = $x_4;
			$x_ar[] = $x_5;
			$z_ar[] = $z_4;
			$z_ar[] = $z_5;
			
			for($yy = $y, $once = false; $y_low <= $yy; $yy--){
				//if($this->main->isWool($x_4, $yy, $z_4)){
				if($level->getBlockIdAt($x_4, $yy, $z_4) !== 0){
					$pos_ar[] = [$x_4, $yy, $z + $z_4];
					$y_ar[] = $yy;
					$once = true;
					break;
				}else{
					if($once) break;
				}
			}
			for($yy = $y, $once = false; $y_low <= $yy; $yy--){
				//if($this->main->isWool($x_5, $yy, $z_5)){
				if($level->getBlockIdAt($x_5, $yy, $z_5) !== 0){
					$pos_ar[] = [$x_5, $yy, $z_5];
					$y_ar[] = $yy;
					$once = true;
					break;
				}else{
					if($once) break;
				}
			}
		}
		$this->changeWoolsColor($level, $pos_ar, $color, $user);
		//if($r) $this->Attack_range(new AxisAlignedBB(min($x_ar) - 1.25, min($y_ar) - 0.75, min($z_ar) - 1.25, max($x_ar) + 1.25, max(max($y_ar), $y + 1.3), max($z_ar) + 1.25), $this->getWeaponData(self::DYNAMO_ROLLER)[6], $player->getLevel(), $player);
		if(!empty($x_ar) && !empty($y_ar) && !empty($z_ar)) $this->Attack_range(new AxisAlignedBB(min($x_ar) - 1, min($y_ar), min($z_ar) - 1, max($x_ar) + 1, max(max($y_ar), $y + 1.8), max($z_ar) + 1), $this->getWeaponData(self::DYNAMO_ROLLER)[6], $level, $player);
		$playerData->setRate();

		$px = $player->x;
		$py = $player->y + $player->getEyeHeight() + 0.2;
		$pz = $player->z;
		if(true){
			$nx = (min($x_ar) + max($x_ar)) / 2;
			$ny = $y;
			$nz = (min($z_ar) + max($z_ar)) / 2;
			// $nx = $pos[0];
			// $ny = $pos[1];
			// $nz = $pos[2];
			$t  = -1 + sqrt(1 + 4 * (2 * ($py - $ny) / 9.8)) / 2 * 5;
			if($t != 0){
				$xe = ($nx - $px) / $t;
				$ze = ($nz - $pz) / $t;
				for($ts = 0; $ts <= $t; $ts += 0.1){//この0.1が小さいほど密度が高くなる
					$pk = new LevelEventPacket;
					// $pk->evid = LevelEventPacket::EVENT_PARTICLE_DESTROY;
					$pk->evid = LevelEventPacket::EVENT_ADD_PARTICLE_MASK | Particle::TYPE_TERRAIN & 0xFFF;
					$pk->x = $px +  $xe * $ts;
					$pk->y = $py + (4.9 * $ts - 4.9 * pow($ts, 2)) / 18;
					$pk->z = $pz +  $ze * $ts;
					// $pk->data = 35 + ($color << 12);
					$pk->data = (($color << 8) | 35);
					$level = $player->getLevel();
					$level->addChunkPacket($pk->x >> 4, $pk->z >> 4, $pk);
				}
			}
		}
		return true;
	}

	/**
	 * スプラアンブレラ
	 * @param Player  $player
	 * @param boolean $blockTap ブロックタップ時はtrue,竿を振った時はfalseを指定
	 */
	public function SplatUmbrella(Player $player, $blockTap = false, $x = null, $y = null, $z = null){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$color = $playerData->getColor();
		$amount = $playerData->getInkConsumption();
		if($playerData->canConsumeInk($amount)){
			$playerData->consumeInk($amount);
		}else{
			return false;
		}
		$pos_ar = [];
		$hankei = 3;//クルクルの最小半径
		$syatei = 7;//二倍になります
		$bureXZ = 30;//XZの最大角度(±15)
		$bureY = 30;//Yの最大角度(±15)
		$hassya = 30;//大きいほど綺麗に塗れる
		$level = $player->level;
		$user = $player->getName();
		if($blockTap){
			$x2 = $player->x;
			$y2 = $player->y;
			$z2 = $player->z;
			for($an = 0; $an <= 19; $an++){
				$rad_y = ($player->yaw + $an * 18) / 180 * M_PI;
				$xx = -sin($rad_y);
				$zz = cos($rad_y);
				for($d = 0; $d <= 3; $d++){
					$pk = new LevelEventPacket;
					list($pk->evid, $pk->x, $pk->y, $pk->z, $pk->data) = [
						LevelEventPacket::EVENT_ADD_PARTICLE_MASK | Particle::TYPE_TERRAIN & 0xFFF,
						$x2, $y2 - 0.5, $z2,
						(($color << 8) | 35)
					];
					Server::getInstance()->broadcastPacket($level->getChunkPlayers($pk->x >> 4, $pk->z >> 4), $pk);
				}
				$pos_ar[] = [$x + 0.5 + $xx * $hankei,       $y, $z + 0.5 + $zz * $hankei];
				$pos_ar[] = [$x + 0.5 + $xx * ($hankei + 2), $y, $z + 0.5 + $zz * ($hankei + 2)];
				$pos_ar[] = [$x + 0.5 + $xx * ($hankei + 4), $y, $z + 0.5 + $zz * ($hankei + 4)];
			}
			$damage = 3.5;
			$knockback = 0.8;
			foreach($level->getEntities() as $p){
				if(	$p instanceof Player &&
					!$this->inCircle($x, $z, $p->x, $p->z, 2.2) &&
					$this->inCircle($x, $z, $p->x, $p->z, 4.8) &&
					abs($p->y - 1 - $y) <= 2 &&
					$player != $p &&
					$this->main->canAttack($player->getName(), $p->getName())['result']
				){
					if($this->main->canAttack($player->getName(), $p->getName())['result']){
						$ev = new EntityDamageByEntityEvent($player, $p, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $damage, $knockback);
						$p->attack($ev->getFinalDamage(), $ev);
					}
				}
			}
		}else{
			//竿を振った時

			$damage = 5.5;
			$knockback = 0;
			
			$x = $player->x;
			$y = $player->y;
			$z = $player->z;
			$yaw = $player->yaw;
			$pitch = $player->pitch;
			for($ss = 0; $ss < $hassya; $ss++){
				$yaw2 = $yaw + floor(mt_rand(0, $bureXZ)) - $bureXZ / 2;
				$pitch2 = $pitch + floor(mt_rand(0, $bureY)) - $bureY / 2;
				$rad_y = $yaw2 / 180 * M_PI;
				$rad_p = ($pitch2 - 180) / 180 * M_PI;
				$xx = sin($rad_y) * cos($rad_p);
				$yy = sin($rad_p);
				$zz = -cos($rad_y) * cos($rad_p);
				$pk = new LevelEventPacket;
				list($pk->evid, $pk->x, $pk->y, $pk->z, $pk->data) = [
					LevelEventPacket::EVENT_ADD_PARTICLE_MASK | Particle::TYPE_TERRAIN & 0xFFF,
					$x, $y - 0.5, $z,
					(($color << 8) | 35)
				];
				Server::getInstance()->broadcastPacket($level->getChunkPlayers($pk->x >> 4, $pk->z >> 4), $pk);
				for($d = 1; $d <= $syatei + 1; $d++){
					$x2 = $x + $xx * $d * 2;
					$y2 = $y + $yy * $d * 2;
					$z2 = $z + $zz * $d * 2;
					if($level->getBlockIdAt($x2, $y2, $z2) === 0){
						$pk = new LevelEventPacket;
						list($pk->evid, $pk->x, $pk->y, $pk->z, $pk->data) = [
							LevelEventPacket::EVENT_ADD_PARTICLE_MASK | Particle::TYPE_TERRAIN & 0xFFF,
							$x2, $y2, $z2,
							(($color << 8) | 35)
						];
						Server::getInstance()->broadcastPacket($level->getChunkPlayers($pk->x >> 4, $pk->z >> 4), $pk);
					}else{
						$pos_ar[] = [$x2, $y2, $z2];
					}
				}
			}
			$rad = $yaw / 180 * M_PI;
			$xx = -1 * sin($rad);
			$zz = cos($rad);
			foreach($level->getEntities() as $p){
				if(	$p instanceof Player &&
					$this->inCircle($player->x, $player->z, $p->x, $p->z, 13) &&
					$this->inOval($player->x + $xx * 13, $player->z + $zz * 13, $p->x, $p->z, 4, 13, $yaw) &&
					abs($p->y - 1 - $player->y) <= $player->distance($p) * 2 / 3 + 2 &&
					$player != $p &&
					$this->main->canAttack($player->getName(), $p->getName())['result']
				){
					$ev = new EntityDamageByEntityEvent($player, $p, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $damage, $knockback);
					$p->attack($ev->getFinalDamage(), $ev);
				}
			}

			//後退
			$player->setMotion(new Vector3(sin($rad) / 2, 0.4, -cos($rad)));
		}
		$r = $this->changeWoolsColor($level, $pos_ar, $color, $user, false);
		$playerData->setRate();
		return $r;
	}

	private function inOval($ox, $oz, $x, $z, $xr, $zr, $yaw){
		$xx = $x - $ox;
		$zz = $z - $oz;
		$rad = $yaw / 180 * M_PI;
		return (pow($xx * cos($rad) + $zz * sin($rad), 2) / pow($xr, 2) + pow(-$xx * sin($rad) + $zz * cos($rad), 2) / pow($zr, 2) <= 1);
	}

	private function inCircle($ox, $oz, $x, $z, $r){
		return pow($x - $ox, 2) + pow($z - $oz, 2) <= pow($r, 2);
	}


	/**
	 * ボールドマーカー
	 * @param Player $player
	 */
	public function Splooshomatic(Player $player){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$amount = $playerData->getInkConsumption();
		$type = $playerData->getFieldNum();
		if($playerData->canConsumeInk($amount)){
			$playerData->consumeInk($amount);
			$this->main->sendInkAmount($player);
			$hand_id = 269;
			//return $this->MakeArrow($player, $hand_id, $type);
			return $this->addShooterBullet($player, $hand_id, $type);
		}
		return false;
	}

	/**
	 * スプラシューター
	 * @param Player $player
	 */
	public function Splattershot(Player $player){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$amount = $playerData->getInkConsumption();
		$type = $playerData->getFieldNum();
		if($playerData->canConsumeInk($amount)){
			$playerData->consumeInk($amount);
			$this->main->sendInkAmount($player);
			$hand_id = 291;
			//return $this->MakeArrow($player, $hand_id, $type);
			return $this->addShooterBullet($player, $hand_id, $type);

		}
		return false;
	}

	/**
	 * わかばシューター
	 * @param Player $player
	 */
	public function SplattershotJr(Player $player){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$amount = $playerData->getInkConsumption();
		$type = $playerData->getFieldNum();
		if($playerData->canConsumeInk($amount)){
			$playerData->consumeInk($amount);
			$this->main->sendInkAmount($player);
			$hand_id = 290;
			//return $this->MakeArrow($player, $hand_id, $type);
			return $this->addShooterBullet($player, $hand_id, $type);
		}
		return false;
	}


	/**
	 * プロモデラーPG
	 * @param Player $player
	 */
	public function AerosprayPG(Player $player){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$amount = $playerData->getInkConsumption();
		$type = $playerData->getFieldNum();
		if($playerData->canConsumeInk($amount)){
			$playerData->consumeInk($amount);
			$this->main->sendInkAmount($player);
			$hand_id = 293;
			//return $this->MakeArrow($player, $hand_id, $type);
			return $this->addShooterBullet($player, $hand_id, $type);
		}
		return false;
	}

	/**
	 * シャープマーカー
	 * @param Player $player
	 */
	public function SplashOMatic(Player $player){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$amount = $playerData->getInkConsumption();
		$type = $playerData->getFieldNum();
		if($playerData->canConsumeInk($amount)){
			$playerData->consumeInk($amount);
			$this->main->sendInkAmount($player);
			$hand_id = 256;
			//return $this->MakeArrow($player, $hand_id, $type);
			return $this->addShooterBullet($player, $hand_id, $type);
		}
		return false;
	}

	/**
	 * デルタスイーパーM
	 * @param Player $player
	 */
	public function DeltaSquelcherM(Player $player){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$amount = $playerData->getInkConsumption();
		$type = $playerData->getFieldNum();
		if($playerData->canConsumeInk($amount)){
			$playerData->consumeInk($amount);
			$this->main->sendInkAmount($player);
			$hand_id = 279;
			//return $this->MakeArrow($player, $hand_id, $type);
			return $this->addShooterBullet($player, $hand_id, $type);
		}
		return false;
	}

	/**
	 * プライムシューター
	 * @param Player $player
	 */
	public function SplatterShotPro(Player $player){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$amount = $playerData->getInkConsumption();
		$type = $playerData->getFieldNum();
		if($playerData->canConsumeInk($amount)){
			$playerData->consumeInk($amount);
			$this->main->sendInkAmount($player);
			$hand_id = 294;
			//return $this->MakeArrow($player, $hand_id, $type);
			return $this->addShooterBullet($player, $hand_id, $type);
		}
		return false;
	}

	/**
	 * .96ガロン
	 * @param Player $player
	 */
	public function Gal_96(Player $player){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$amount = $playerData->getInkConsumption();
		$type = $playerData->getFieldNum();
		if($playerData->canConsumeInk($amount)){
			$playerData->consumeInk($amount);
			$this->main->sendInkAmount($player);
			$hand_id = 284;
			//return $this->MakeArrow($player, $hand_id, $type);
			return $this->addShooterBullet($player, $hand_id, $type);
		}
		return false;
	}

	/**
	 * .52ガロン
	 * @param Player $player
	 */
	public function Gal_52(Player $player){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$amount = $playerData->getInkConsumption();
		$type = $playerData->getFieldNum();
		if($playerData->canConsumeInk($amount)){
			$playerData->consumeInk($amount);
			$this->main->sendInkAmount($player);
			$hand_id = 277;
			//return $this->MakeArrow($player, $hand_id, $type);
			return $this->addShooterBullet($player, $hand_id, $type);
		}
		return false;
	}

	/**
	 * デュアルスイーパー
	 * @param Player $player
	 */
	public function DualSquelcher(Player $player){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$amount = $playerData->getInkConsumption();
		$type = $playerData->getFieldNum();
		if($playerData->canConsumeInk($amount)){
			$playerData->consumeInk($amount);
			$this->main->sendInkAmount($player);
			$hand_id = 292;
			//return $this->MakeArrow($player, $hand_id, $type);
			return $this->addShooterBullet($player, $hand_id, $type);
		}
		return false;
	}

	/**
	 * ノヴァブラスター(チャージスタート)
	 * @param Player $player
	 */
	public function LunaBlaster_Charge(Player $player){
		$user = $player->getName();
		$hand_id = 359;
		$playerData = Account::getInstance()->getData($user);
		$amount = $playerData->getInkConsumption();
		if($playerData->canConsumeInk($amount)){
			if(!isset($this->Task["SplatlingCharge"][$player->getName()])){
				$charge_tick = $this->getChargeTick(Weapon::LUNA_BLASTER);
				$this->Task["SplatlingCharge"][$player->getName()] = Server::getInstance()->getScheduler()->scheduleRepeatingTask(new SplatlingCharge($this, $player, $hand_id, $charge_tick, false), 1);
			}

			return true;
		}

		return false;
	}

	/**
	 * ノヴァブラスター
	 * @param Player $player
	 */
	public function LunaBlaster(Player $player, $first = false){
		$user = $player->getName();
		$hand_id = 359;
		if((!$player->spawned || !$player->isAlive() || $player->getDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE) || 
			$player->getInventory()->getItemInHand()->getID() !== $hand_id) && isset($this->Task["SplatlingShot"][$user])){
			Server::getInstance()->getScheduler()->cancelTask($this->Task["SplatlingShot"][$user]->getTaskId());
			unset($this->Task["SplatlingShot"][$user]);
			return false;
		}
		$playerData = Account::getInstance()->getData($user);
		if(!isset($this->Task["SplatlingShot"][$player->getName()])){
			$shot_count = $this->getShotCount(Weapon::LUNA_BLASTER);
			$this->Task["SplatlingShot"][$player->getName()] = Server::getInstance()->getScheduler()->scheduleRepeatingTask(new SplatlingShot($this, $player, $hand_id, $shot_count), 3);
			$playerData->setRate();
		}
		$amount = $playerData->getInkConsumption();
		$type = $playerData->getFieldNum();
		if($playerData->canConsumeInk($amount)){
			$playerData->consumeInk($amount);
			$this->main->sendInkAmount($player);
			$players = $player->getViewers();
			$players[] = $player;
			$player->getLevel()->addSound(new LaunchSound($player, self::SHOOTER_SOUND_PITCH), $players);
			//return $this->MakeArrow($player, $hand_id, $type, $count);
			return ($first)? true : $this->addBlasterBullet($player, $hand_id, $type);
		}elseif(isset($this->Task["SplatlingShot"][$player->getName()])){
			Server::getInstance()->getScheduler()->cancelTask($this->Task["SplatlingShot"][$player->getName()]->getTaskId());
			unset($this->Task["SplatlingShot"][$player->getName()]);
			$this->main->Inkshortage($player);
		}
		return false;
	}

	/**
	 * ロングブラスター(チャージスタート)
	 * @param Player $player
	 */
	public function LongBlaster_Charge(Player $player){
		$user = $player->getName();
		$hand_id = 267;
		$playerData = Account::getInstance()->getData($user);
		$amount = $playerData->getInkConsumption();
		if($playerData->canConsumeInk($amount)){
			if(!isset($this->Task["SplatlingCharge"][$player->getName()])){
				$charge_tick = $this->getChargeTick(Weapon::LONG_BLASTER);
				$this->Task["SplatlingCharge"][$player->getName()] = Server::getInstance()->getScheduler()->scheduleRepeatingTask(new SplatlingCharge($this, $player, $hand_id, $charge_tick, false), 1);
			}

			return true;
		}

		return false;
	}

	/**
	 * ロングブラスター
	 * @param Player $player
	 */
	public function LongBlaster(Player $player, $first = false){
		$user = $player->getName();
		$hand_id = 267;
		if((!$player->spawned || !$player->isAlive() || $player->getDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE) || 
			$player->getInventory()->getItemInHand()->getID() !== $hand_id) && isset($this->Task["SplatlingShot"][$user])){
			Server::getInstance()->getScheduler()->cancelTask($this->Task["SplatlingShot"][$user]->getTaskId());
			unset($this->Task["SplatlingShot"][$user]);
			return false;
		}
		$playerData = Account::getInstance()->getData($user);
		if(!isset($this->Task["SplatlingShot"][$player->getName()])){
			$shot_count = $this->getShotCount(Weapon::LONG_BLASTER);
			$this->Task["SplatlingShot"][$player->getName()] = Server::getInstance()->getScheduler()->scheduleRepeatingTask(new SplatlingShot($this, $player, $hand_id, $shot_count), 3);
			$playerData->setRate();
		}
		$amount = $playerData->getInkConsumption();
		$type = $playerData->getFieldNum();
		if($playerData->canConsumeInk($amount)){
			$playerData->consumeInk($amount);
			$this->main->sendInkAmount($player);
			$players = $player->getViewers();
			$players[] = $player;
			$player->getLevel()->addSound(new LaunchSound($player, self::SHOOTER_SOUND_PITCH), $players);
			//return $this->MakeArrow($player, $hand_id, $type, $count);
			return ($first)? true : $this->addBlasterBullet($player, $hand_id, $type);
		}elseif(isset($this->Task["SplatlingShot"][$player->getName()])){
			Server::getInstance()->getScheduler()->cancelTask($this->Task["SplatlingShot"][$player->getName()]->getTaskId());
			unset($this->Task["SplatlingShot"][$player->getName()]);
			$this->main->Inkshortage($player);
		}
		return false;
	}


	/**
	 * ホットブラスター(チャージスタート)
	 * @param Player $player
	 */
	public function HotBlaster_Charge(Player $player){
		$user = $player->getName();
		$hand_id = 272;
		$playerData = Account::getInstance()->getData($user);
		$amount = $playerData->getInkConsumption();
		if($playerData->canConsumeInk($amount)){
			if(!isset($this->Task["SplatlingCharge"][$player->getName()])){
				$charge_tick = $this->getChargeTick(Weapon::HOT_BLASTER);
				$this->Task["SplatlingCharge"][$player->getName()] = Server::getInstance()->getScheduler()->scheduleRepeatingTask(new SplatlingCharge($this, $player, $hand_id, $charge_tick, false), 1);
			}

			return true;
		}

		return false;
	}

	/**
	 * ﾎｯﾄブラスター
	 * @param Player $player
	 */
	public function HotBlaster(Player $player, $first = false){
		$user = $player->getName();
		$hand_id = 272;
		if((!$player->spawned || !$player->isAlive() || $player->getDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE) || 
			$player->getInventory()->getItemInHand()->getID() !== $hand_id) && isset($this->Task["SplatlingShot"][$user])){
			Server::getInstance()->getScheduler()->cancelTask($this->Task["SplatlingShot"][$user]->getTaskId());
			unset($this->Task["SplatlingShot"][$user]);
			return false;
		}
		$playerData = Account::getInstance()->getData($user);
		if(!isset($this->Task["SplatlingShot"][$player->getName()])){
			$shot_count = $this->getShotCount(Weapon::HOT_BLASTER);
			$this->Task["SplatlingShot"][$player->getName()] = Server::getInstance()->getScheduler()->scheduleRepeatingTask(new SplatlingShot($this, $player, $hand_id, $shot_count), 3);
			$playerData->setRate();
		}
		$amount = $playerData->getInkConsumption();
		$type = $playerData->getFieldNum();
		if($playerData->canConsumeInk($amount)){
			$playerData->consumeInk($amount);
			$this->main->sendInkAmount($player);
			$players = $player->getViewers();
			$players[] = $player;
			$player->getLevel()->addSound(new LaunchSound($player, self::SHOOTER_SOUND_PITCH), $players);
			//return $this->MakeArrow($player, $hand_id, $type, $count);
			return ($first)? true : $this->addBlasterBullet($player, $hand_id, $type);
		}elseif(isset($this->Task["SplatlingShot"][$player->getName()])){
			Server::getInstance()->getScheduler()->cancelTask($this->Task["SplatlingShot"][$player->getName()]->getTaskId());
			unset($this->Task["SplatlingShot"][$player->getName()]);
			$this->main->Inkshortage($player);
		}
		return false;
	}

	/**
	 * ラピッドブラスター(チャージスタート)
	 * @param Player $player
	 */
	public function RapidBlaster_Charge(Player $player){
		$user = $player->getName();
		$hand_id = 268;
		$playerData = Account::getInstance()->getData($user);
		$amount = $playerData->getInkConsumption();
		if($playerData->canConsumeInk($amount)){
			if(!isset($this->Task["SplatlingCharge"][$player->getName()])){
				$charge_tick = $this->getChargeTick(Weapon::RAPID_BLASTER);
				$this->Task["SplatlingCharge"][$player->getName()] = Server::getInstance()->getScheduler()->scheduleRepeatingTask(new SplatlingCharge($this, $player, $hand_id, $charge_tick, false), 1);
			}

			return true;
		}

		return false;
	}

	/**
	 * ラピッドブラスター
	 * @param Player $player
	 */
	public function RapidBlaster(Player $player, $first = false){
		$user = $player->getName();
		$hand_id = 268;
		if((!$player->spawned || !$player->isAlive() || $player->getDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE) || 
			$player->getInventory()->getItemInHand()->getID() !== $hand_id) && isset($this->Task["SplatlingShot"][$user])){
			Server::getInstance()->getScheduler()->cancelTask($this->Task["SplatlingShot"][$user]->getTaskId());
			unset($this->Task["SplatlingShot"][$user]);
			return false;
		}
		$playerData = Account::getInstance()->getData($user);
		if(!isset($this->Task["SplatlingShot"][$player->getName()])){
			$shot_count = $this->getShotCount(Weapon::RAPID_BLASTER);
			$this->Task["SplatlingShot"][$player->getName()] = Server::getInstance()->getScheduler()->scheduleRepeatingTask(new SplatlingShot($this, $player, $hand_id, $shot_count), 3);
			$playerData->setRate();
		}
		$amount = $playerData->getInkConsumption();
		$type = $playerData->getFieldNum();
		if($playerData->canConsumeInk($amount)){
			$playerData->consumeInk($amount);
			$this->main->sendInkAmount($player);
			$players = $player->getViewers();
			$players[] = $player;
			$player->getLevel()->addSound(new LaunchSound($player, self::SHOOTER_SOUND_PITCH), $players);
			//return $this->MakeArrow($player, $hand_id, $type, $count);
			return ($first)? true : $this->addBlasterBullet($player, $hand_id, $type);
		}elseif(isset($this->Task["SplatlingShot"][$player->getName()])){
			Server::getInstance()->getScheduler()->cancelTask($this->Task["SplatlingShot"][$player->getName()]->getTaskId());
			unset($this->Task["SplatlingShot"][$player->getName()]);
			$this->main->Inkshortage($player);
		}
		return false;
	}

	/**
	 * スプラチャージャー
	 * @param Player $player
	 */
	public function SplatCharger(Player $player, $force){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$type = $playerData->getFieldNum();
		$hand_id = 261;
		$playerData->setRate();
		//return $this->MakeArrow($player, $hand_id, $type);
 		return $this->addChargerBullet($player, $hand_id, $force, 2);
	}

	/**
	 * リッター3K
	 * @param Player $player
	 */
	public function Litre3K(Player $player, $force){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$type = $playerData->getFieldNum();
		$hand_id = 261;
		$playerData->setRate();
		//return $this->MakeArrow($player, $hand_id, $type);
 		return $this->addChargerBullet($player, $hand_id, $force, 4);
	}

	/**
	 * スクイックリンα
	 * @param Player $player
	 */
	public function ClassicSquiffer(Player $player, $force){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$type = $playerData->getFieldNum();
		$hand_id = 261;
		$playerData->setRate();
		//return $this->MakeArrow($player, $hand_id, $type);
 		return $this->addChargerBullet($player, $hand_id, $force, 1.2);
	}

	public function ChargingCheck($player, $item){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$pa = (array) $player;
		$startAction = $pa["\0*\0startAction"];
		if($item !== 261 || $startAction == -1){
			return false;
		}
		$server = $pa["\0*\0server"];
		$diff = ($server->getTick() - $startAction);
		$p = $diff / 20;
		$f = (($p ** 2) + $p * 2) / 3 * 2;
		$nw = $playerData->getNowWeapon();
		switch($nw){
			case self::SPLAT_CHARGER:
			case self::SPLAT_CHARGER_WAKAME:
			case self::SPLAT_CHARGER_BENTO:
				$mf = 2;
				break;
			case self::LITRE3K:
			case self::LITRE3K_CUSTOM:
				$mf = 4;
				break;
			case self::CLASSIC_SQUIFFER:
				$mf = 1.2;
				break;
			default:
				$mf = 2;
				break;
		}
		if($f > $mf){
			$f = $mf;
		}
		$result = SplatlingCharge::chargePopup(floor($mf*100), floor($mf*100)-floor($f*100));
		if($result) $player->sendPopup($result);
	}

	/**
	 * バケットスロッシャー
	 * @param Player $player
	 * @param int    $x
	 * @param int    $y
	 * @param int    $z
	 */
	public function Slosher(Player $player, $x, $y, $z){
		$pos_ar = [];
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		if($playerData->getInk() < $playerData->getInkConsumption()){
			return false;
		}
		$color = $playerData->getColor();
		$level = $player->getLevel();
		$x_ar = [];
		$y_ar = [];
		$z_ar = [];
		$syatei = $this->getFiringRange(Weapon::SLOSHER);
		$yaw = $player->yaw;
		$pitch = 0;
		//yaw1とyaw2の値を変えると左右のインクの広がり方が変わります
		$yaw1 = ($yaw + 14) % 360;
		$yaw2 = ($yaw - 14) % 360;
		$yaw3 = ($yaw + 6) % 360;
		$yaw4 = ($yaw - 6) % 360;
		$pos_v = $player->getDirectionVector();
		$max_y = $pos_v->y + 2;
		$cos_deg2rad180 = cos(deg2rad($pitch - 180));
		for($d = 0; $d <= $syatei; $d++){
			$xx  = $x +  $d * sin(deg2rad($yaw))  * $cos_deg2rad180;
			$zz  = $z + -$d * cos(deg2rad($yaw))  * $cos_deg2rad180;
			$xx1 = $x +  $d * sin(deg2rad($yaw1)) * $cos_deg2rad180;
			$zz1 = $z + -$d * cos(deg2rad($yaw1)) * $cos_deg2rad180;
			$xx2 = $x +  $d * sin(deg2rad($yaw2)) * $cos_deg2rad180;
			$zz2 = $z + -$d * cos(deg2rad($yaw2)) * $cos_deg2rad180;
			$xx3 = $x +  $d * sin(deg2rad($yaw3)) * $cos_deg2rad180;
			$zz3 = $z + -$d * cos(deg2rad($yaw3)) * $cos_deg2rad180;
			$xx4 = $x +  $d * sin(deg2rad($yaw4)) * $cos_deg2rad180;
			$zz4 = $z + -$d * cos(deg2rad($yaw4)) * $cos_deg2rad180;
			$x_ar[] = $xx;
			$x_ar[] = $xx1;
			$x_ar[] = $xx2;
			$x_ar[] = $xx3;
			$x_ar[] = $xx4;
			$z_ar[] = $zz;
			$z_ar[] = $zz1;
			$z_ar[] = $zz2;
			$z_ar[] = $zz3;
			$z_ar[] = $zz4;
			//ここから塗りのコード
			$y_high = ceil(max($y, $y + $max_y) + 0.9 - (1 / $syatei * $d));
			$y_low = -3 + $y_high - mt_rand(30, 50) / 10;
			for($yy = $y_high, $once = false; $y_low <= $yy; $yy--){
				if($level->getBlockIdAt(floor($xx), floor($yy), floor($zz)) !== 0){
				// if($this->main->isWool($xx, $yy, $zz)){
					$pos_ar[] = [$xx, $yy, $zz];
					$y_ar[] = $yy;
					$once = true;
				}elseif($once){
					break;
				}
			}
			for($yy = $y_high, $once = false; $y_low <= $yy; $yy--){
				if($level->getBlockIdAt(floor($xx1), floor($yy), floor($zz1)) !== 0){
				// if($this->main->isWool($xx1, $yy, $zz1)){
					$pos_ar[] = [$xx1, $yy, $zz1];
					$y_ar[] = $yy;
					$once = true;
				}elseif($once){
					break;
				}
			}
			for($yy = $y_high, $once = false; $y_low <= $yy; $yy--){
				if($level->getBlockIdAt(floor($xx2), floor($yy), floor($zz2)) !== 0){
				// if($this->main->isWool($xx2, $yy, $zz2)){
					$pos_ar[] = [$xx2, $yy, $zz2];
					$y_ar[] = $yy;
					$once = true;
				}elseif($once){
					break;
				}
			}
			for($yy = $y_high, $once = false; $y_low <= $yy; $yy--){
				if($level->getBlockIdAt(floor($xx3), floor($yy), floor($zz3)) !== 0){
				// if($this->main->isWool($xx3, $yy, $zz3)){
					$pos_ar[] = [$xx3, $yy, $zz3];
					$y_ar[] = $yy;
					$once = true;
				}elseif($once){
					break;
				}
			}
			for($yy = $y_high, $once = false; $y_low <= $yy; $yy--){
				if($level->getBlockIdAt(floor($xx4), floor($yy), floor($zz4)) !== 0){
				// if($this->main->isWool($xx4, $yy, $zz4)){
					$pos_ar[] = [$xx4, $yy, $zz4];
					$y_ar[] = $yy;
					$once = true;
				}elseif($once){
					break;
				}
			}
		}
		$this->changeWoolsColor($level, $pos_ar, $color, $user);
		if(!empty($x_ar) && !empty($y_ar) && !empty($z_ar)) $this->Attack_range(new AxisAlignedBB(min($x_ar), min($y_ar), min($z_ar), max($x_ar), max(max($y_ar), $y + $max_y+1), max($z_ar)), $this->getWeaponData(self::SLOSHER)[6], $level, $player);
		$playerData->setRate();
		return true;
	}


	/**
	 * ヒッセン
	 * @param Player $player
	 * @param int    $x
	 * @param int    $y
	 * @param int    $z
	 */
	public function BrushWasher($player, $x, $y, $z){
		// 12/25 13:40 trasra
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		if($playerData->getInk() < $playerData->getInkConsumption()){
			return false;
		}
		$color = $playerData->getColor();
		$level = $player->getLevel();
		$pos_ar = [];
		$x_ar = [];
		$y_ar = [];
		$z_ar = [];
		$range = 4;//ここ
		$mange = 1;//ここ
		$yaw = $player->yaw;
		$pos_v = $player->getDirectionVector();
		$max_y = $pos_v->y + 3;
		$y_low = $y - 5;
		$pos_ar[] = [$x, $y, $z];
		$x_ar[] = $x;
		$y_ar[] = $y;
		$z_ar[] = $z;
		for ($angle = -3.5 ; $angle <= 3.5; $angle+=0.5){
			$rad_y = ($yaw + $angle * 10) / 180 * M_PI;
			$start=0;
			if(abs($angle) < 1.1) $start = 1;//ここ
			if(abs($angle) < 1){
				for($n = 0; $n <= $range + $start + 1; $n += 0.5){
					$xx = floor($x - ($n + mt_rand(0,10)/10) * sin($rad_y));
					$zz = floor($z + ($n + mt_rand(0,10)/10) * cos($rad_y));
					$x_ar[] = $xx;
					$z_ar[] = $zz;
					//ここから塗りのコード
					for($yy = max($y, $y + $max_y), $once = false; $y_low <= $yy; $yy--){
						if($level->getBlockIdAt($xx, $yy, $zz) !== 0){
						// if($this->main->isWool($xx, $yy, $zz)){
							$pos_ar[] = [$xx, $yy, $zz];
							$y_ar[] = $yy;
							$once = true;
						}elseif($once){
							break;
						}
					}
				}
			}
			$rad_y = ($yaw + $angle * 10) / 180 * M_PI;
			for($n = $start; $n <= $start + $mange; $n += 0.5){
				$xx = floor($x - ($range + $n + mt_rand(0, 10) / 10) * sin($rad_y));
				$zz = floor($z + ($range + $n + mt_rand(0, 10) / 10) * cos($rad_y));
				$x_ar[] = $xx;
				$z_ar[] = $zz;
				//ここから塗りのコード
				for($yy = max($y, $y + $max_y), $once = false; $y_low <= $yy; $yy--){
					if($level->getBlockIdAt($xx, $yy, $zz) !== 0){
					// if($this->main->isWool($xx, $yy, $zz)){
						$pos_ar[] = [$xx, $yy, $zz];
						$y_ar[] = $yy;
						$once = true;
					}elseif($once){
						break;
					}
				}
			}
		}
		$pos_ar = array_values(array_unique($pos_ar, SORT_REGULAR));
		$this->changeWoolsColor($level, $pos_ar, $color, $user);
		$this->Attack_range(new AxisAlignedBB(min($x_ar), min($y_ar) + 1.2, min($z_ar), max($x_ar), max(max($y_ar), $y + 1.5), max($z_ar)), $this->getWeaponData(self::BRUSHWASHER)[6], $level, $player);
		$playerData->setRate();
		return true;
	}

	/**
	 * スプラドル
	 * @param Player $player
	 * @param int    $x
	 * @param int    $y
	 * @param int    $z
	 */
	public function SplatLadle(Player $player, $x, $y, $z){
		$pos_ar = [];
		$x_ar = [];
		$x_ar = [];
		$z_ar = [];
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		if($playerData->getInk() < $playerData->getInkConsumption()){
			return false;
		}
		$color = $playerData->getColor();
		$level = $player->getLevel();
		$m = 13;//飛ばす密度
		$roc = 0.2;//広がり具合
		$yawr = rand(-5, 5)*5;
		switch(mt_rand(0, 1)){
			case 0:
				$yaw = $player->yaw - 135+$yawr;
				for($ia = 0; $ia <= 135; $ia += $m){
					$rad_y = deg2rad($yaw + $ia);
					$sin_y = sin($rad_y);
					$cos_y = cos($rad_y);
					$dc = $ia / $m * $roc;
					$xx1 = $x + (-2.5 * $dc * 1.5 * $sin_y);
					$zz1 = $z + ( 2.5 * $dc * 1.5 * $cos_y);
					$xx2 = $x + (-2   * $dc * 1.5 * $sin_y);
					$zz2 = $z + ( 2   * $dc * 1.5 * $cos_y);
					$xx3 = $x + (-3   * $dc * 1.5 * $sin_y);
					$zz3 = $z + ( 3   * $dc * 1.5 * $cos_y);
					$pos_ar[] = [$xx1, $y, $zz1];
					$pos_ar[] = [$xx2, $y, $zz2];
					$pos_ar[] = [$xx3, $y, $zz3];
					$pos_ar[] = [$xx1, $y-1, $zz1];
					$pos_ar[] = [$xx2, $y-1, $zz2];
					$pos_ar[] = [$xx3, $y-1, $zz3];
					$pos_ar[] = [$xx1, $y+1, $zz1];
					$pos_ar[] = [$xx2, $y+1, $zz2];
					$pos_ar[] = [$xx3, $y+1, $zz3];

					$x_ar[] = $xx1;
					$x_ar[] = $xx2;
					$x_ar[] = $xx3;
					$z_ar[] = $zz1;
					$z_ar[] = $zz2;
					$z_ar[] = $zz3;
				}
				break;
			case 1:
				$yaw = $player->yaw + 135+$yawr;
				for($ia=0; $ia <= 135; $ia += $m){
					$rad_y = deg2rad($yaw - $ia);
					$sin_y = sin($rad_y);
					$cos_y = cos($rad_y);
					$dc = $ia / $m * $roc;
					$xx1 = $x + (-2.5 * $dc * 1.5 * $sin_y);
					$zz1 = $z + ( 2.5 * $dc * 1.5 * $cos_y);
					$xx2 = $x + (-2   * $dc * 1.5 * $sin_y);
					$zz2 = $z + ( 2   * $dc * 1.5 * $cos_y);
					$xx3 = $x + (-3   * $dc * 1.5 * $sin_y);
					$zz3 = $z + ( 3   * $dc * 1.5 * $cos_y);
					$pos_ar[] = [$xx1, $y, $zz1];
					$pos_ar[] = [$xx2, $y, $zz2];
					$pos_ar[] = [$xx3, $y, $zz3];
					$pos_ar[] = [$xx1, $y-1, $zz1];
					$pos_ar[] = [$xx2, $y-1, $zz2];
					$pos_ar[] = [$xx3, $y-1, $zz3];
					$pos_ar[] = [$xx1, $y+1, $zz1];
					$pos_ar[] = [$xx2, $y+1, $zz2];
					$pos_ar[] = [$xx3, $y+1, $zz3];
					$x_ar[] = $xx1;
					$x_ar[] = $xx2;
					$x_ar[] = $xx3;
					$z_ar[] = $zz1;
					$z_ar[] = $zz2;
					$z_ar[] = $zz3;
				}
				break;
		}
		$this->changeWoolsColor($level, $pos_ar, $color, $user);
		$this->Attack_range(new AxisAlignedBB(min($x_ar), $y - 2, min($z_ar), max($x_ar), $y + 3.25, max($z_ar)), $this->getWeaponData(self::SPLAT_LADLE)[6], $level, $player);
		$playerData->setRate();
		return true;
	}

	/**
	 * バレルスピナー(発射)
	 * @param Player $player
	 * @param int    $count
	 */
	public function HeavySplatling(Player $player, $count, $first = false){
		$user = $player->getName();
		$hand_id = 258;
		if((!$player->spawned || !$player->isAlive() || $player->getDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE) || 
			$player->getInventory()->getItemInHand()->getID() !== $hand_id) && isset($this->Task["SplatlingShot"][$user])){
			Server::getInstance()->getScheduler()->cancelTask($this->Task["SplatlingShot"][$user]->getTaskId());
			unset($this->Task["SplatlingShot"][$user]);
			return false;
		}
		$playerData = Account::getInstance()->getData($user);
		if(!isset($this->Task["SplatlingShot"][$player->getName()])){
			$shot_count = $this->getShotCount(Weapon::HEAVY_SPLATLING);
			$this->Task["SplatlingShot"][$player->getName()] = Server::getInstance()->getScheduler()->scheduleRepeatingTask(new SplatlingShot($this, $player, $hand_id, $shot_count), 2);
			$playerData->setRate();
		}
		$amount = $playerData->getInkConsumption();
		$type = $playerData->getFieldNum();
		if($playerData->canConsumeInk($amount)){
			$playerData->consumeInk($amount);
			$this->main->sendInkAmount($player);
			$players = $player->getViewers();
			$players[] = $player;
			$player->getLevel()->addSound(new LaunchSound($player, self::SPLATLING_SOUND_PITCH), $players);
			//return $this->MakeArrow($player, $hand_id, $type, $count);
			return ($first)? true : $this->addShooterBullet($player, $hand_id, $type);
		}elseif(isset($this->Task["SplatlingShot"][$player->getName()])){
			Server::getInstance()->getScheduler()->cancelTask($this->Task["SplatlingShot"][$player->getName()]->getTaskId());
			unset($this->Task["SplatlingShot"][$player->getName()]);
			$this->main->Inkshortage($player);
		}
		return false;
	}

	/**
	 * バレルスピナー(チャージスタート)
	 * @param Player $player
	 */
	public function HeavySplatling_Charge(Player $player){
		$user = $player->getName();
		$hand_id = 258;
		$playerData = Account::getInstance()->getData($user);
		$amount = $playerData->getInkConsumption();
		if(!$playerData->canConsumeInk($amount)) return false;
		if(!isset($this->Task["SplatlingCharge"][$player->getName()])){
			$charge_tick = $this->getChargeTick(Weapon::HEAVY_SPLATLING);
			$this->Task["SplatlingCharge"][$player->getName()] = Server::getInstance()->getScheduler()->scheduleRepeatingTask(new SplatlingCharge($this, $player, $hand_id, $charge_tick), 1);
		}
	}

	/**
	 * スプラスピナー(発射)
	 * @param Player $player
	 * @param int    $count
	 */
	public function MiniSplatling(Player $player, $count, $first = false){
		$user = $player->getName();
		$hand_id = 275;
		if((!$player->spawned || !$player->isAlive() || $player->getDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE) || 
			$player->getInventory()->getItemInHand()->getID() !== $hand_id) && isset($this->Task["SplatlingShot"][$user])){
			Server::getInstance()->getScheduler()->cancelTask($this->Task["SplatlingShot"][$user]->getTaskId());
			unset($this->Task["SplatlingShot"][$user]);
			return false;
		}
		$playerData = Account::getInstance()->getData($user);
		if(!isset($this->Task["SplatlingShot"][$player->getName()])){
			$shot_count = $this->getShotCount(Weapon::MINI_SPLATLING);
			$this->Task["SplatlingShot"][$player->getName()] = Server::getInstance()->getScheduler()->scheduleRepeatingTask(new SplatlingShot($this, $player, $hand_id, $shot_count), 2);
			$playerData->setRate();
		}
		$amount = $playerData->getInkConsumption();
		$type = $playerData->getFieldNum();
		if($playerData->canConsumeInk($amount)){
			$playerData->consumeInk($amount);
			$this->main->sendInkAmount($player);
			$players = $player->getViewers();
			$players[] = $player;
			$player->getLevel()->addSound(new LaunchSound($player, self::SPLATLING_SOUND_PITCH), $players);
			//return $this->MakeArrow($player, $hand_id, $type, $count);
			return ($first)? true : $this->addShooterBullet($player, $hand_id, $type);
		}elseif(isset($this->Task["SplatlingShot"][$player->getName()])){
			Server::getInstance()->getScheduler()->cancelTask($this->Task["SplatlingShot"][$player->getName()]->getTaskId());
			unset($this->Task["SplatlingShot"][$player->getName()]);
			$this->main->Inkshortage($player);
		}
		return false;
	}

	/**
	 * スプラスピナー(チャージスタート)
	 * @param Player $player
	 */
	public function MiniSplatling_Charge(Player $player){
		$user = $player->getName();
		$hand_id = 275;
		$playerData = Account::getInstance()->getData($user);
		$amount = $playerData->getInkConsumption();
		if(!$playerData->canConsumeInk($amount)) return false;
		if(!isset($this->Task["SplatlingCharge"][$player->getName()])){
			$charge_tick = $charge_tick = $this->getChargeTick(Weapon::MINI_SPLATLING);
			$this->Task["SplatlingCharge"][$player->getName()] = Server::getInstance()->getScheduler()->scheduleRepeatingTask(new SplatlingCharge($this, $player, $hand_id, $charge_tick), 1);
		}
		return true;
	}

	/**
	 * ハイドラント(発射)
	 * @param Player $player
	 */
	public function HydraSplatling(Player $player, $count, $first = false){
		$user = $player->getName();
		$hand_id = 286;
		if((!$player->spawned || !$player->isAlive() || $player->getDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE)  || 
			$player->getInventory()->getItemInHand()->getID() !== $hand_id) && isset($this->Task["SplatlingShot"][$user])){
			Server::getInstance()->getScheduler()->cancelTask($this->Task["SplatlingShot"][$user]->getTaskId());
			unset($this->Task["SplatlingShot"][$user]);
			return false;
		}
		$playerData = Account::getInstance()->getData($user);
		if(!isset($this->Task["SplatlingShot"][$player->getName()])){
			$shot_count = $this->getShotCount(Weapon::HYDRA_SPLATLING);
			$this->Task["SplatlingShot"][$player->getName()] = Server::getInstance()->getScheduler()->scheduleRepeatingTask(new SplatlingShot($this, $player, $hand_id, $shot_count), 2);
			$playerData->setRate();
		}
		$amount = $playerData->getInkConsumption();
		$type = $playerData->getFieldNum();
		if($playerData->canConsumeInk($amount)){
			$playerData->consumeInk($amount);
			$this->main->sendInkAmount($player);
			$players = $player->getViewers();
			$players[] = $player;
			$player->getLevel()->addSound(new LaunchSound($player, self::SPLATLING_SOUND_PITCH), $players);
			//return $this->MakeArrow($player, $hand_id, $type, $count);
			return ($first)? true : $this->addShooterBullet($player, $hand_id, $type, $count);			
		}elseif(isset($this->Task["SplatlingShot"][$player->getName()])){
			Server::getInstance()->getScheduler()->cancelTask($this->Task["SplatlingShot"][$player->getName()]->getTaskId());
			unset($this->Task["SplatlingShot"][$player->getName()]);
			$this->main->Inkshortage($player);
		}
		return false;
	}

	/**
	 * ハイドラント(チャージスタート)
	 * @param Player $player
	 */
	public function HydraSplatling_Charge(Player $player){
		$user = $player->getName();
		$hand_id = 286;
		$playerData = Account::getInstance()->getData($user);
		$amount = $playerData->getInkConsumption();
		if(!$playerData->canConsumeInk($amount)) return false;
		if(!isset($this->Task["SplatlingCharge"][$player->getName()])){
			$charge_tick = $this->getChargeTick(Weapon::HYDRA_SPLATLING);
			$this->Task["SplatlingCharge"][$player->getName()] = Server::getInstance()->getScheduler()->scheduleRepeatingTask(new SplatlingCharge($this, $player, $hand_id, $charge_tick), 1);
		}
	}

	/**
	 * L3リールガン(発射)
	 * @param Player $player
	 * @param int    $count
	 */
	public function L3_Nozzlenose(Player $player, $count, $first = false){
		$user = $player->getName();
		$hand_id = 273;
		if((!$player->spawned || !$player->isAlive() || $player->getDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE) || 
			$player->getInventory()->getItemInHand()->getID() !== $hand_id) && isset($this->Task["SplatlingShot"][$user])){
			Server::getInstance()->getScheduler()->cancelTask($this->Task["SplatlingShot"][$user]->getTaskId());
			unset($this->Task["SplatlingShot"][$user]);
			return false;
		}
		$playerData = Account::getInstance()->getData($user);
		if(!isset($this->Task["SplatlingShot"][$player->getName()])){
			$shot_count = $this->getShotCount(Weapon::L3_NOZZLENOSE);
			$this->Task["SplatlingShot"][$player->getName()] = Server::getInstance()->getScheduler()->scheduleRepeatingTask(new SplatlingShot($this, $player, $hand_id, $shot_count), 3);
			$playerData->setRate();
		}
		$amount = $playerData->getInkConsumption();
		$type = $playerData->getFieldNum();
		if($playerData->canConsumeInk($amount)){
			$playerData->consumeInk($amount);
			$this->main->sendInkAmount($player);
			$players = $player->getViewers();
			$players[] = $player;
			$player->getLevel()->addSound(new LaunchSound($player, self::SPLATLING_SOUND_PITCH), $players);
			//return $this->MakeArrow($player, $hand_id, $type, $count);
			return ($first)? true : $this->addShooterBullet($player, $hand_id, $type);
		}elseif(isset($this->Task["SplatlingShot"][$player->getName()])){
			Server::getInstance()->getScheduler()->cancelTask($this->Task["SplatlingShot"][$player->getName()]->getTaskId());
			unset($this->Task["SplatlingShot"][$player->getName()]);
			$this->main->Inkshortage($player);
		}
		return false;
	}

	/**
	 * L3リールガン(チャージスタート)
	 * @param Player $player
	 */
	public function L3_Nozzlenose_Charge(Player $player){
		$user = $player->getName();
		$hand_id = 273;
		$playerData = Account::getInstance()->getData($user);
		$amount = $playerData->getInkConsumption();
		if($playerData->canConsumeInk($amount)){
			if(!isset($this->Task["SplatlingCharge"][$player->getName()])){
				$charge_tick = $this->getChargeTick(Weapon::L3_NOZZLENOSE);
				$this->Task["SplatlingCharge"][$player->getName()] = Server::getInstance()->getScheduler()->scheduleRepeatingTask(new SplatlingCharge($this, $player, $hand_id, $charge_tick, false), 1);
			}

			return true;
		}

		return false;
	}

	/**
	 * T3リールガン(発射)
	 * @param Player $player
	 * @param int    $count
	 */
	public function T3_Nozzlenose(Player $player, $count, $first = false){
		$user = $player->getName();
		$hand_id = 271;
		if((!$player->spawned || !$player->isAlive() || $player->getDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE) || 
			$player->getInventory()->getItemInHand()->getID() !== $hand_id) && isset($this->Task["SplatlingShot"][$user])){
			Server::getInstance()->getScheduler()->cancelTask($this->Task["SplatlingShot"][$user]->getTaskId());
			unset($this->Task["SplatlingShot"][$user]);
			return false;
		}
		$playerData = Account::getInstance()->getData($user);
		if(!isset($this->Task["SplatlingShot"][$player->getName()])){
			$shot_count = $this->getShotCount(Weapon::T3_NOZZLENOSE);
			$this->Task["SplatlingShot"][$player->getName()] = Server::getInstance()->getScheduler()->scheduleRepeatingTask(new SplatlingShot($this, $player, $hand_id, $shot_count), 3);
			$playerData->setRate();
		}
		$amount = $playerData->getInkConsumption();
		$type = $playerData->getFieldNum();
		if($playerData->canConsumeInk($amount)){
			$playerData->consumeInk($amount);
			$this->main->sendInkAmount($player);
			$players = $player->getViewers();
			$players[] = $player;
			$player->getLevel()->addSound(new LaunchSound($player, self::SPLATLING_SOUND_PITCH), $players);
			//return $this->MakeArrow($player, $hand_id, $type, $count);
			return ($first)? true : $this->addShooterBullet($player, $hand_id, $type);
		}elseif(isset($this->Task["SplatlingShot"][$player->getName()])){
			Server::getInstance()->getScheduler()->cancelTask($this->Task["SplatlingShot"][$player->getName()]->getTaskId());
			unset($this->Task["SplatlingShot"][$player->getName()]);
			$this->main->Inkshortage($player);
		}
		return false;
	}

	/**
	 * T3リールガン(チャージスタート)
	 * @param Player $player
	 */
	public function T3_Nozzlenose_Charge(Player $player){
		$user = $player->getName();
		$hand_id = 271;
		$playerData = $playerData = Account::getInstance()->getData($user);
		$amount = $playerData->getInkConsumption();
		if($playerData->canConsumeInk($amount)){
			if(!isset($this->Task["SplatlingCharge"][$player->getName()])){
				$charge_tick = $this->getChargeTick(Weapon::T3_NOZZLENOSE);
				$this->Task["SplatlingCharge"][$player->getName()] = Server::getInstance()->getScheduler()->scheduleRepeatingTask(new SplatlingCharge($this, $player, $hand_id, $charge_tick, false), 1);
			}
			return true;
		}
		return false;
	}

	public function SplatlingChargeCheck($user){
		return isset($this->Task["SplatlingCharge"][$user]);
	}

	public function Snow($player, $x, $y, $z, $level, $user, $color, $wall){
		$pos_ar = $wall ? [
			[$x, $y, $z],
			[$x, $y - 1, $z],
			[$x + 1, $y - 1, $z],
			[$x - 1, $y - 1, $z],
			[$x, $y - 1, $z + 1],
			[$x, $y - 1, $z - 1],
			[$x + 1, $y - 1, $z - 1],
			[$x + 1, $y - 1, $z + 1],
			[$x - 1, $y - 1, $z + 1],
			[$x - 1, $y - 1, $z - 1],
		] : [
			[$x, $y, $z],
			[$x, $y - 1, $z],
			[$x + 1, $y, $z],
			[$x + 1, $y, $z - 1],
			[$x + 1, $y, $z + 1],
			[$x - 1, $y, $z - 1],
			[$x - 1, $y , $z - 1],
			[$x, $y, $z + 1],
			[$x, $y, $z - 1],
			[$x, $y, $z],
			[$x + 1, $y - 1, $z],
			[$x + 1, $y - 1, $z - 1],
			[$x + 1, $y - 1, $z + 1],
			[$x - 1, $y - 1, $z - 1],
			[$x - 1, $y - 1, $z - 1],
			[$x, $y - 1, $z + 1],
			[$x, $y - 1, $z - 1],
			[$x, $y + 1, $z],
			[$x + 1, $y + 1, $z],
			[$x + 1, $y + 1, $z - 1],
			[$x + 1, $y + 1, $z + 1],
			[$x - 1, $y + 1, $z - 1],
			[$x - 1, $y + 1, $z - 1],
			[$x, $y + 1, $z + 1],
			[$x, $y + 1, $z - 1],
		];
		$this->changeWoolsColor($level, $pos_ar, $color, $user, false);
		$pos = new Position($x, $y, $z, $level);
		//$explosion = new Explosion($pos, 1);
		//$explosion->explodeB();
		$this->Explode($pos, 1, $player);
	}

	public function spawnEntity($meta, $level, $x, $y, $z, $custom_name = null){
		$nbt = new CompoundTag("", [
			"Pos" => new ListTag("Pos", [
				new DoubleTag("", $x + 0.5),
				new DoubleTag("", $y),
				new DoubleTag("", $z + 0.5)
			]),
			"Motion" => new ListTag("Motion", [
				new DoubleTag("", 0),
				new DoubleTag("", 0),
				new DoubleTag("", 0)
			]),
			"Rotation" => new ListTag("Rotation", [
				new FloatTag("", lcg_value() * 360),
				new FloatTag("", 0)
			]),
		]);

		if(!is_null($custom_name)){
			$nbt->CustomName = new StringTag("CustomName", $custom_name);
		}

		$entity = Entity::createEntity($meta, $level, $nbt);
		if($entity instanceof Entity){
			$entity->spawnToAll();
			return $entity;
		}
		echo "Not Entity";
		return false;
	}

	public function spawnBomb($player, $level, $x, $y, $z, $custom_name = null){
		$nbt = new CompoundTag("", [
			"Pos" => new ListTag("Pos", [
				new DoubleTag("", $x + 0.5),
				new DoubleTag("", $y),
				new DoubleTag("", $z + 0.5)
			]),
			"Motion" => new ListTag("Motion", [
				new DoubleTag("", 0),
				new DoubleTag("", 0),
				new DoubleTag("", 0)
			]),
			"Rotation" => new ListTag("Rotation", [
				new FloatTag("", lcg_value() * 360),
				new FloatTag("", 0)
			]),
		]);

		if(!is_null($custom_name)){
			$nbt->CustomName = new StringTag("CustomName", $custom_name);
		}

		//$entity = Entity::createEntity($meta, $level, $nbt);
		$entity = new BombEntity($level, $nbt, $player);
		$entity->main = $this->main;
		if($entity instanceof Entity){
			$entity->spawnToAll();
			return $entity;
		}
		echo "Not Entity";
		return false;
	}

	public function spawnChaseBomb($player, $level, $x, $y, $z, $target, $custom_name = null){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$tank = $playerData->getInkTank($user);
		$amount = $tank * $this->getSubWeaponInkConsumption($player, self::CHASE_BOMB);
		$type = $playerData->getFieldNum();
		
		if($playerData->canConsumeInk($amount)){
			$playerData->consumeInk($amount);
			$this->main->sendInkAmount($player);
			$nbt = new CompoundTag("", [
				"Pos" => new ListTag("Pos", [
					new DoubleTag("", $x),
					new DoubleTag("", $y),
					new DoubleTag("", $z)
				]),
				"Motion" => new ListTag("Motion", [
					new DoubleTag("", 0),
					new DoubleTag("", 0),
					new DoubleTag("", 0)
				]),
				"Rotation" => new ListTag("Rotation", [
					new FloatTag("", lcg_value() * 360),
					new FloatTag("", 0)
				]),
			]);

			if(!is_null($custom_name)){
				$nbt->CustomName = new StringTag("CustomName", $custom_name);
			}

			$entity = new ChaseBomb($level, $nbt, $player, $this->main, $target);
			if($entity instanceof Entity){
				$entity->spawnToAll();
				return $entity;
			}
			echo "Not Entity";
			return false;
		}
		return false;
	}

	public function setTrap($player, $x, $y, $z, $team){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$tank = $playerData->getInkTank();
		$color = $playerData->getColor();
		$block = Block::get(35, $color);
		$amount = $tank * $this->getSubWeaponInkConsumption($player, self::TRAP);
		if($playerData->canConsumeInk($amount)){
			$playerData->consumeInk($amount);
			$pos_ar = [[$x, $y, $z]];
			$this->changeWoolsColor($player->getLevel(), $pos_ar, $color, $user, false);
			$this->main->sendInkAmount($player);
			$trap = new trap($this, $player, $x, $y, $z, $block, $team);
			Server::getInstance()->getScheduler()->scheduleRepeatingTask($trap, 10);
			if(isset($this->Task["Trap"][$player->getName()])){

				$this->Task["Trap"][$player->getName()]->deleteTrap();
			}
			$this->Task["Trap"][$player->getName()] = $trap;
			$playerData->setRate($this->getSubWeaponRate(self::TRAP));
			return true;
		}
		return false;
	}

	public function setSensor($player, $target, $update = false){
		$user = $player->getName();
		$team_num = $this->main->team->getTeamOf($user);
		$members = $this->main->team->getTeamMember($team_num, true);
		$this->Task["sensor"][$target->getName()] = $player;
		$packet = new SetEntityDataPacket();
		foreach($members as $member){
			if(($p = Server::getInstance()->getPlayer($member)) instanceof Player){
				$pk = clone $packet;	
				$eid = $target->getId();
				$pk->eid = $eid;
				$pk->metadata = [Entity::DATA_LEAD_HOLDER_EID => [Entity::DATA_TYPE_LONG, $p->getId()]];
				$p->dataPacket($pk);
				$time = 200/Gadget::getCorrection($target, Gadget::BOMB_GUARD);
				if(!$update){
					$F = function ($array){
						$p = $array[0];
						$target = $array[1];
						$pk = new SetEntityDataPacket();
						$eid = $target->getId();
						$pk->eid = $eid;
						$pk->metadata = [Entity::DATA_LEAD_HOLDER_EID => [Entity::DATA_TYPE_LONG, -1]];
						$sen = $this->getSensor($target);
						$p->dataPacket($pk);
						if($sen === $this->Task["sensor"][$target->getName()]){
							unset($this->Task["sensor"][$target->getName()]);
						}
					};
					Server::getInstance()->getScheduler()->scheduleDelayedTask(new LateDo($this, $F, [$p, $target]), $time);
				}
			}
		}
	}

	/**
	 * センサー当てたやつ許さんぞ
	 */
	public function getSensor($player){
		if(isset($this->Task["sensor"][$player->getName()])){
			return $this->Task["sensor"][$player->getName()];
		}else{
			return false;
		}
	}

	public function SplashBomb(Player $player){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$tank = $playerData->getInkTank($user);
		$amount = $tank * $this->getSubWeaponInkConsumption($player, self::SPLASH_BOMB);
		$type = $playerData->getFieldNum();
		
		if($playerData->canConsumeInk($amount)){
			$playerData->consumeInk($amount);
			$this->main->sendInkAmount($player);
			$color = $playerData->getColor();
			$block = Block::get(35, $color);
			$enemys = $this->getAllBattleMembers();
			$yaw = $player->yaw;
			$pitch = $player->pitch;
			$rad_y = $yaw/180*M_PI;
			$rad_p = ($pitch-180)/180*M_PI;
			$ent = $this->spawnEntity("PrimedTNT", $player->getLevel(), $player->x+sin($rad_y)*cos($rad_p)-0.5, $player->y+1.5+sin($rad_p), $player->z-cos($rad_y)*cos($rad_p)-0.5);
			$speed = 0.9;
			$speed *= Gadget::getCorrection($player, Gadget::BOMB_THROW);
			$ent->setMotion(new Vector3(sin($rad_y)*cos($rad_p)*$speed, sin($rad_p)*$speed, -cos($rad_y)*cos($rad_p)*$speed));
			$F = function($array){
				$array[0]->bomb($array[6], $array[1], $array[6]->x, $array[6]->y, $array[6]->z, $array[2], $array[3], $array[4], $array[5], $array[7]);
				$array[6]->close();
			};

			Server::getInstance()->getScheduler()->scheduleDelayedTask(new LateDo($this, $F, [$this, $player, $block, 3.3, 40, $enemys, $ent, 5]), 40);

			$F_2 = function($array){
				$particle = new DestroyBlockParticle(new Vector3($array[1]->x, $array[1]->y+1, $array[1]->z), $array[2]);
				$array[0]->addParticle($particle);
			};
					
			Server::getInstance()->getScheduler()->scheduleDelayedTask(new LateDo($this, $F_2, [$player->getLevel(), $ent, $block]), 30);
			Server::getInstance()->getScheduler()->scheduleDelayedTask(new LateDo($this, $F_2, [$player->getLevel(), $ent, $block]), 20);
			$playerData->setRate($this->getSubWeaponRate(self::SPLASH_BOMB));
			return true;
		}
		return false;
	}

	public function QuickBomb(Player $player){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$tank = $playerData->getInkTank();
		$amount = $tank * $this->getSubWeaponInkConsumption($player, self::QUICK_BOMB);
		if($playerData->canConsumeInk($amount)){
			$playerData->consumeInk($amount);
			$this->main->sendInkAmount($player);

			$yaw = $player->yaw;
			$pitch = $player->pitch;
			$rad_y = $yaw/180*M_PI;
			$rad_p = ($pitch-180)/180*M_PI;
			$ent = $this->spawnBomb($player, $player->getLevel(), $player->x+sin($rad_y)*cos($rad_p)-0.5, $player->y+1.5+sin($rad_p), $player->z-cos($rad_y)*cos($rad_p)-0.5);
			$speed = 0.7;
			$speed *= Gadget::getCorrection($player, Gadget::BOMB_THROW);
			$ent->setMotion(new Vector3(sin($rad_y)*cos($rad_p)*$speed, sin($rad_p)*$speed, -cos($rad_y)*cos($rad_p)*$speed));
			$ent->bombType = 2;
			$ent->player = $player;
			$ent->block = Block::get(35, $playerData->getColor());
			$playerData->setRate($this->getSubWeaponRate(self::QUICK_BOMB));
			return true;
		}
		return false;
	}

	public function SuckerBomb(Player $player){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$tank = $playerData->getInkTank();
		$amount = $tank * $this->getSubWeaponInkConsumption($player, self::SUCKER_BOMB);
		if($playerData->canConsumeInk($amount)){
			$playerData->consumeInk($amount);
			$this->main->sendInkAmount($player);

			$yaw = $player->yaw;
			$pitch = $player->pitch;
			$rad_y = $yaw/180*M_PI;
			$rad_p = ($pitch-180)/180*M_PI;
			$ent = $this->spawnBomb($player, $player->getLevel(), $player->x+sin($rad_y)*cos($rad_p)-0.5, $player->y+1.5+sin($rad_p), $player->z-cos($rad_y)*cos($rad_p)*2-0.5);
			$speed = 0.7;
			$speed *= Gadget::getCorrection($player, Gadget::BOMB_THROW);
			$ent->setMotion(new Vector3(sin($rad_y)*cos($rad_p)*$speed, sin($rad_p)*$speed, -cos($rad_y)*cos($rad_p)*$speed));
			$ent->bombType = 1;
			$ent->player = $player;
			$ent->block = Block::get(35, $playerData->getColor());
			$playerData->setRate($this->getSubWeaponRate(self::SUCKER_BOMB));
			return true;
		}
		return false;
	}

	public function InkBall_shoot(Player $player){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$tank = $playerData->getInkTank();
		$amount = $tank * $this->getSubWeaponInkConsumption($player, self::INK_BALL);
		if($playerData->canConsumeInk($amount)){
			$playerData->consumeInk($amount);
			$this->main->sendInkAmount($player);

			$yaw = $player->yaw;
			$pitch = $player->pitch;
			$rad_y = $yaw/180*M_PI;
			$rad_p = ($pitch-180)/180*M_PI;
			$ent = $this->spawnBomb($player, $player->getLevel(), $player->x+sin($rad_y)*cos($rad_p)-0.5, $player->y+1.5+sin($rad_p), $player->z-cos($rad_y)*cos($rad_p)-0.5);
			$speed = 0.8;
			$speed *= Gadget::getCorrection($player, Gadget::BOMB_THROW);
			$ent->setMotion(new Vector3(sin($rad_y)*cos($rad_p)*$speed, sin($rad_p)*$speed, -cos($rad_y)*cos($rad_p)*$speed));
			$ent->bombType = 5;
			$ent->player = $player;
			$playerData->setRate($this->getSubWeaponRate(self::INK_BALL));
			return true;
		}
		return false;
	}


	public function InkBall($entity, $player, $x, $y, $z, $radius, $array){

		$level = $player->getLevel();
		$radius_1 = $radius/2; //球の半径
		$radius_2 = $radius/1.25; //球の半径
		$radius_3 = $radius; //球の半径

		$F = function($array){

			$array[0]->addParticle($array[1]);
		};

		$p = new Vector3($x, $y, $z);

		$level->addSound(new EndermanTeleportSound($p));
/*		$effect1 = Effect::getEffect(6);
	 	$effect1->setDuration(1);
		$effect1->setAmplifier(1);*/

		for($yaw = 0; $yaw < 360; $yaw += 360/(2*M_PI*$radius)){

			for($pitch = 0; $pitch <360; $pitch += 360/(M_PI*$radius)){

				$rad_y = $yaw/180*M_PI;
				$rad_p = ($pitch-180)/180*M_PI;
				$xx = sin($rad_y)*cos($rad_p);
				$yy = sin($rad_p);
				$zz = -cos($rad_y)*cos($rad_p);
/*				$p->x = $x+$xx*$radius_1;
				$p->y = $y+$yy*$radius_1;
				$p->z = $z+$zz*$radius_1;
				$particle_1 = new InkParticle($p);
				$level->addParticle($particle_1);

				$p->x = $x+$xx*$radius_2;
				$p->y = $y+$yy*$radius_2;
				$p->z = $z+$zz*$radius_2;
				$particle_2 = new InkParticle($p);
				Server::getInstance()->getScheduler()->scheduleDelayedTask(new lateDo($this, $F, [$level, $particle_2]),2);*/
					
				$p->x = $x+$xx*$radius_3;
				$p->y = $y+$yy*$radius_3;
				$p->z = $z+$zz*$radius_3;
				$level->addParticle(new InkParticle($p));
			}
		}

		foreach($array as $e){
			$en = Server::getInstance()->getPlayer($e);
			if((!$player instanceof Player) or (!$en instanceof Player)){
				continue;
			}
	
			if($this->main->canAttack($player->getName(), $en->getName())['result']){
				$distance = sqrt(pow($x-$en->x,2)+pow($y-$en->y,2)+pow($z-$en->z,2));

				if($entity != $en /*&& $player != $en*/ && $distance <= $radius+$radius*2/3){

					$playerData = Account::getInstance()->getData($e);
					$tank = $playerData->getInkTank();
					$power = Gadget::getCorrection($player, Gadget::POWER);
					$defence = Gadget::getCorrection($en, Gadget::DEFENCE);
					$par = $power/$defence;
					$par /= Gadget::getCorrection($en, Gadget::BOMB_GUARD);
					$amount = $tank*0.6*$par;
					$playerData->consumeInk($amount);
					$this->main->sendInkAmount($en);
					//$player->addEffect($effect1);
					$pd = Account::getInstance()->getData($player); 
					$pd->stockInk(floor($amount/2)); 
					$level->addSound(new SplashSound($player));
					$level->addParticle(new DestroyBlockParticle(35, 15));
				}
			}
		}
	}

	public function PointSensor_shoot(Player $player){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$tank = $playerData->getInkTank();
		$amount = $tank * $this->getSubWeaponInkConsumption($player, self::POINT_SENSOR);
		if($playerData->canConsumeInk($amount)){
			$playerData->consumeInk($amount);
			$this->main->sendInkAmount($player);

			$yaw = $player->yaw;
			$pitch = $player->pitch;
			$rad_y = $yaw/180*M_PI;
			$rad_p = ($pitch-180)/180*M_PI;
			$ent = $this->spawnBomb($player, $player->getLevel(), $player->x+sin($rad_y)*cos($rad_p)-0.5, $player->y+1.5+sin($rad_p), $player->z-cos($rad_y)*cos($rad_p)-0.5);
			$speed = 0.8;
			$speed *= Gadget::getCorrection($player, Gadget::BOMB_THROW);
			$ent->setMotion(new Vector3(sin($rad_y)*cos($rad_p)*$speed, sin($rad_p)*$speed, -cos($rad_y)*cos($rad_p)*$speed));
			$ent->bombType = 7;
			$ent->player = $player;
			$playerData->setRate($this->getSubWeaponRate(self::POINT_SENSOR));
			return true;
		}
		return false;
	}


	public function PointSensor($entity, $player, $x, $y, $z, $radius, $array){

		$level = $player->getLevel();
		$radius_1 = $radius/2; //球の半径
		$radius_2 = $radius/1.25; //球の半径
		$radius_3 = $radius; //球の半径

		$F = function($array){

			$array[0]->addParticle($array[1]);
		};

		$p = new Vector3($x, $y, $z);

		$level->addSound(new EndermanTeleportSound($p));
/*		$effect1 = Effect::getEffect(6);
	 	$effect1->setDuration(1);
		$effect1->setAmplifier(1);*/

		for($yaw = 0; $yaw < 360; $yaw += 360/(M_PI*$radius)){

			for($pitch = 0; $pitch <360; $pitch += 360/(M_PI*$radius)){

				$rad_y = $yaw/180*M_PI;
				$rad_p = ($pitch-180)/180*M_PI;
				$xx = sin($rad_y)*cos($rad_p);
				$yy = sin($rad_p);
				$zz = -cos($rad_y)*cos($rad_p);
/*				$p->x = $x+$xx*$radius_1;
				$p->y = $y+$yy*$radius_1;
				$p->z = $z+$zz*$radius_1;
				$particle_1 = new CriticalParticle($p);
				$level->addParticle($particle_1);

				$p->x = $x+$xx*$radius_2;
				$p->y = $y+$yy*$radius_2;
				$p->z = $z+$zz*$radius_2;
				$particle_2 = new InkParticle($p);
				Server::getInstance()->getScheduler()->scheduleDelayedTask(new lateDo($this, $F, [$level, $particle_2]),2);*/
					
				$p->x = $x+$xx*$radius_3;
				$p->y = $y+$yy*$radius_3;
				$p->z = $z+$zz*$radius_3;
				$particle_3 = new CriticalParticle($p);
				$level->addParticle($particle_3);
				//Server::getInstance()->getScheduler()->scheduleDelayedTask(new LateDo($this, $F, [$level, $particle_3]),3);
			}
		}

		foreach($array as $e){
			$en = Server::getInstance()->getPlayer($e);
			if((!$player instanceof Player) or (!$en instanceof Player)){
				continue;
			}
	
			if($this->main->canAttack($player->getName(), $en->getName())['result']){
				$distance = sqrt(pow($x-$en->x,2)+pow($y-$en->y,2)+pow($z-$en->z,2));

				if($entity != $en /*&& $player != $en*/ && $distance <= $radius+$radius*2/3){
					$this->setSensor($player, $en);
					$level->addSound(new SplashSound($p));
				}
			}
		}
	}

	public function acidBall_shoot(Player $player){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$tank = $playerData->getInkTank();
		$amount = $tank * $this->getSubWeaponInkConsumption($player, self::ACID_BALL);
		if($playerData->canConsumeInk($amount)){
			$playerData->consumeInk($amount);
			$this->main->sendInkAmount($player);

			$yaw = $player->yaw;
			$pitch = $player->pitch;
			$rad_y = $yaw/180*M_PI;
			$rad_p = ($pitch-180)/180*M_PI;
			$ent = $this->spawnBomb($player, $player->getLevel(), $player->x+sin($rad_y)*cos($rad_p)-0.5, $player->y+1.5+sin($rad_p), $player->z-cos($rad_y)*cos($rad_p)-0.5);
			$speed = 0.7;
			$speed *= Gadget::getCorrection($player, Gadget::BOMB_THROW);
			$ent->setMotion(new Vector3(sin($rad_y)*cos($rad_p)*$speed, sin($rad_p)*$speed, -cos($rad_y)*cos($rad_p)*$speed));
			$ent->bombType = 3;
			$ent->player = $player;
			$playerData->setRate($this->getSubWeaponRate(self::ACID_BALL));
			return true;
		}
		return false;
	}

	public function acidBall($entity, $player, $x, $y, $z, $radius, $array){

		$level = $player->getLevel();
		$radius_1 = $radius/2; //球の半径
		$radius_2 = $radius/1.25; //球の半径
		$radius_3 = $radius; //球の半径
		$user = $player->getName();
		$team_num = $this->main->team->getTeamOf($user);
		$rgb = $this->main->team->getTeamColorRGB($team_num);
		$F = function($array){

			$array[0]->addParticle($array[1]);
		};

		$p = new Vector3($x, $y, $z);
		$level->addSound(new SplashSound($p));

		for($yaw = 0; $yaw < 360; $yaw += 360/(2*M_PI*$radius)){

			for($pitch = 0; $pitch <360; $pitch += 360/(M_PI*$radius)){

				$rad_y = $yaw/180*M_PI;
				$rad_p = ($pitch-180)/180*M_PI;
				$xx = sin($rad_y)*cos($rad_p);
				$yy = sin($rad_p);
				$zz = -cos($rad_y)*cos($rad_p);
/*				$p->x = $x+$xx*$radius_1;
				$p->y = $y+$yy*$radius_1;
				$p->z = $z+$zz*$radius_1;
				$particle_1 = new DustParticle($p, $rgb[0], $rgb[1], $rgb[2]);
				$level->addParticle($particle_1);

				$p->x = $x+$xx*$radius_2;
				$p->y = $y+$yy*$radius_2;
				$p->z = $z+$zz*$radius_2;
				$particle_2 = new DustParticle($p, $rgb[0], $rgb[1], $rgb[2]);
				Server::getInstance()->getScheduler()->scheduleDelayedTask(new lateDo($this, $F, [$level, $particle_2]),2);*/
					
				$p->x = $x+$xx*$radius_3;
				$p->y = $y+$yy*$radius_3;
				$p->z = $z+$zz*$radius_3;
				$level->addParticle(new DustParticle($p, $rgb[0], $rgb[1], $rgb[2]));
				//Server::getInstance()->getScheduler()->scheduleDelayedTask(new LateDo($this, $F, [$level, $particle_3]),3);
			}
		}

		foreach($array as $e){
			$en = Server::getInstance()->getPlayer($e);
			if((!$player instanceof Player) or (!$en instanceof Player)){
				continue;
			}
	
			if($this->main->canAttack($player->getName(), $en->getName())['result']){
				$distance = sqrt(pow($x-$en->x,2)+pow($y-$en->y,2)+pow($z-$en->z,2));

				if($entity != $en /*&& $player != $en*/ && $distance <= $radius+$radius*2/3){

					$this->setAcid($player, $en, 160);
				}
			}
		}
	}

	public function KnockBomb_shoot(Player $player){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$tank = $playerData->getInkTank();
		$amount = $tank * $this->getSubWeaponInkConsumption($player, self::KNOCK_BOMB);
		if($playerData->canConsumeInk($amount)){
			$playerData->consumeInk($amount);
			$this->main->sendInkAmount($player);

			$yaw = $player->yaw;
			$pitch = $player->pitch;
			$rad_y = $yaw/180*M_PI;
			$rad_p = ($pitch-180)/180*M_PI;
			$ent = $this->spawnBomb($player, $player->getLevel(), $player->x+sin($rad_y)*cos($rad_p)-0.5, $player->y+1.5+sin($rad_p), $player->z-cos($rad_y)*cos($rad_p)-0.5);
			$speed = 0.7;
			$speed *= Gadget::getCorrection($player, Gadget::BOMB_THROW);
			$ent->setMotion(new Vector3(sin($rad_y)*cos($rad_p)*$speed, sin($rad_p)*$speed, -cos($rad_y)*cos($rad_p)*$speed));
			$ent->bombType = 4;
			$ent->player = $player;
			$ent->block = Block::get(35, $playerData->getColor());
			$playerData->setRate($this->getSubWeaponRate(self::KNOCK_BOMB));
			return true;
		}
		return false;
	}

	public function knockBomb($entity, $player, $x, $y, $z, $block, $radius, $power, $array, $paint){

		$level = $player->getLevel();
		$radius_1 = $radius/2; //球の半径
		//$radius_2 = $radius*1.25; //球の半径
		$radius_3 = $radius; //球の半径

		$F = function($array){

			$array[0]->addParticle($array[1]);
		};

		$p = new Vector3($x, $y, $z);
		$particle_1 = new DestroyBlockParticle($p, $block);
		$level->addParticle($particle_1);
		$level->addSound(new ExplodeSound($p));
		$pos_ar = [];

		for($xxx = -floor($paint/2); $xxx < ceil($paint/2); $xxx++){

			for($yyy = -floor($paint/2); $yyy < ceil($paint/2); $yyy++){ 
			
				for($zzz = -floor($paint/2); $zzz < ceil($paint/2); $zzz++){ 
				
					//$pos = new Vector3(floor($xxx+$x), floor($yyy+$y), floor($zzz+$z));
					
					if($level->getBlockIdAt(floor($xxx+$x), floor($yyy+$y), floor($zzz+$z)) !== 0){
						
						//塗り
						//$level->setBlock($pos, $block);
						$pos_ar[] = [floor($xxx + $x), floor($yyy + $y), floor($zzz + $z)];
					}
				}
			}			
		}
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$color = $playerData->getColor();
		$result = $this->changeWoolsColor($level, $pos_ar, $color, $user);
		for($yaw = 0; $yaw < 360; $yaw += 360/(2*M_PI*$radius)){

			for($pitch = 0; $pitch <360; $pitch += 360/(2*M_PI*$radius)){

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

/*				$p->x = $x+$xx*$radius_2;
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
		foreach($array as $e){
			$en = Server::getInstance()->getPlayer($e);
			if((!$player instanceof Player) or (!$en instanceof Player)){
				continue;
			}
	
			if($this->main->canAttack($player->getName(), $en->getName())['result']){
				$distance = sqrt(pow($x-$en->x,2)+pow($y-$en->y,2)+pow($z-$en->z,2));

				if($entity != $en && $player != $en && $distance <= $radius+$radius*2/3){

					if($this->canAttack($entity, $en)){
						$dmg = floor($power-$power*2/3*($distance/($radius+$radius*2/3)));
						$en->attack($dmg,new EntityDamageByEntityEvent($player,$en,EntityDamageEvent::CAUSE_ENTITY_EXPLOSION, $dmg,0));
						$nkr = 2/Gadget::getCorrection($en, Gadget::BOMB_GUARD);
						$v = (new Vector3($en->x-$x, 0, $en->z-$z))->normalize()->multiply($nkr*Gadget::getCorrection($player, Gadget::POWER));
						$v->y = 0.4+(0.25*Gadget::getCorrection($player, Gadget::POWER));
						$en->setMotion($v);
					}
				}
			}
		}
		return $result;
	}

	/**
	 * スプリンクラーを投げる
	 * @param Player $player
	 */
	public function sprinkler_shoot(Player $player){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$tank = $playerData->getInkTank();
		$amount = $tank * $this->getSubWeaponInkConsumption($player, self::SPRINKLER);
		if($playerData->canConsumeInk($amount)){
			$playerData->consumeInk($amount);
			$this->main->sendInkAmount($player);

			$yaw = $player->yaw;
			$pitch = $player->pitch;
			$rad_y = $yaw/180*M_PI;
			$rad_p = ($pitch-180)/180*M_PI;
			$ent = $this->spawnBomb($player, $player->getLevel(), $player->x+sin($rad_y)*cos($rad_p)-0.5, $player->y+1.5+sin($rad_p), $player->z-cos($rad_y)*cos($rad_p)-0.5);
			$speed = 0.7;
			$ent->setMotion(new Vector3(sin($rad_y)*cos($rad_p)*$speed, sin($rad_p)*$speed, -cos($rad_y)*cos($rad_p)*$speed));
			$ent->bombType = 6;
			$ent->player = $player;
			$ent->block = Block::get(35, $playerData->getColor());
			$playerData->setRate($this->getSubWeaponRate(self::SPRINKLER));
			return true;
		}
		return false;
	}


	/**
	 * 座標から無理やり面番号取得してやるぜって関数 スプリンクラーの接着判定に使用
	 * @param Level $level
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 */
	public function getFaceFromPos($level, $x, $y, $z){
		$x = floor($x);
		$y = floor($y);
		$z = floor($z);
		for($i = 0; $i <= 5; $i++){ 
			switch($i){
				case 0:
					if($level->getBlockIdAt($x, $y+1, $z) === 35){
						return 0;
					}
				break;
				case 1:
					if($level->getBlockIdAt($x, $y-1, $z) === 35){
						return 1;
					}
				break;
				case 2:
					if($level->getBlockIdAt($x, $y, $z+1) === 35){
						return 2;
					}
				break;
				case 3:
					if($level->getBlockIdAt($x, $y, $z-1) === 35){
						return 3;
					}
				break;
				case 4:
					if($level->getBlockIdAt($x+1, $y, $z) === 35){
						return 4;
					}
				break;
				case 5:
					if($level->getBlockIdAt($x-1, $y, $z) === 35){
						return 5;
					}
				break;

			}
		}
		return false;
	}

	public function getArkPlaceSprinkler($level, $x, $y, $z, $face = 1){
		if($level->getBlockIdAt($x, $y, $z) == 0){
			switch($face){
				case 0:
				if($level->getBlockIdAt($x, $y+1, $z) == 35){
					return [$x, $y+1, $z];
				}else{
					return false;
				}
					break;
				case 1:
				if($level->getBlockIdAt($x, $y-1, $z) == 35){
					return [$x, $y-1, $z];
				}else{
					return false;
				}
					break;
				case 2:
				if($level->getBlockIdAt($x, $y, $z+1) == 35){
					return [$x, $y, $z+1];
				}else{
					return false;
				}
					break;
				case 3:
				if($level->getBlockIdAt($x, $y, $z-1) == 35){
					return [$x, $y, $z-1];
				}else{
					return false;
				}
					break;
				case 4:
				if($level->getBlockIdAt($x+1, $y, $z) == 35){
					return [$x+1, $y, $z];
				}else{
					return false;
				}
					break;
				case 5:
				if($level->getBlockIdAt($x-1, $y, $z) == 35){
					return [$x-1, $y, $z];
				}else{
					return false;
				}
					break;
			}
		}else{
			return false;
		}
	}

	public function getPlaceSprinkler($level, $x, $y, $z, $face = 1){
		if($level->getBlockIdAt($x, $y, $z) == 35){
			switch($face){
				case 0:
				if($level->getBlockIdAt($x, $y-1, $z) == 0){
					return [$x, $y-1, $z];
				}else{
					return false;
				}
					break;
				case 1:
				if($level->getBlockIdAt($x, $y+1, $z) == 0){
					return [$x, $y+1, $z];
				}else{
					return false;
				}
					break;
				case 2:
				if($level->getBlockIdAt($x, $y, $z-1) == 0){
					return [$x, $y, $z-1];
				}else{
					return false;
				}
					break;
				case 3:
				if($level->getBlockIdAt($x, $y, $z+1) == 0){
					return [$x, $y, $z+1];
				}else{
					return false;
				}
					break;
				case 4:
				if($level->getBlockIdAt($x-1, $y, $z) == 0){
					return [$x-1, $y, $z];
				}else{
					return false;
				}
					break;
				case 5:
				if($level->getBlockIdAt($x+1, $y, $z) == 0){
					return [$x+1, $y, $z];
				}else{
					return false;
				}
					break;
			}
		}else{
			return false;
		}
	}

	public function spawnSprinkler($player, $x, $y, $z, $ent_pos, $face = 1, $consume = true){
		$ex = $ent_pos[0];
		$ey = $ent_pos[1];
		$ez = $ent_pos[2];
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$tank = $playerData->getInkTank();
		$color = $playerData->getColor();
		$amount = $tank * $this->getSubWeaponInkConsumption($player, self::SPRINKLER);
		if(!$consume || $playerData->canConsumeInk($amount)){
			if($consume)$playerData->consumeInk($amount);
			$pos_ar = [[$x, $y, $z]];
			$this->changeWoolsColor($player->getLevel(), $pos_ar, $color, $user, false);
			$this->main->sendInkAmount($player);
			if(isset($this->Task["Sprinkler"][$player->getName()])){
				$this->Task["Sprinkler"][$player->getName()]->deleteSprinkler();
			}
			$entity = $this->spawnEntity("Pig", $player->getLevel(), $ex, $ey, $ez);
			$entity->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_NO_AI, true);
			$sprinkler = new sprinkler($this, $player, $entity, $x, $y, $z, $face);
			Server::getInstance()->getScheduler()->scheduleRepeatingTask($sprinkler, 7);
			$this->Task["Sprinkler"][$player->getName()] = $sprinkler;
			return true;
		}else{
			return false;
		}
	}

	public function Explode(Position $pos, $size, $e = null){
		$explosionSize = $size * 2;
		$minX = Math::floorFloat($pos->x - $explosionSize - 1);
		$maxX = Math::ceilFloat($pos->x + $explosionSize + 1);
		$minY = Math::floorFloat($pos->y - $explosionSize - 1);
		$maxY = Math::ceilFloat($pos->y + $explosionSize + 1);
		$minZ = Math::floorFloat($pos->z - $explosionSize - 1);
		$maxZ = Math::ceilFloat($pos->z + $explosionSize + 1);

		$explosionBB = new AxisAlignedBB($minX, $minY, $minZ, $maxX, $maxY, $maxZ);

		$list = $pos->level->getNearbyEntities($explosionBB, $e instanceof Entity ? $e : null);
		foreach($list as $entity){
			$distance = $entity->distance($pos) / $explosionSize;

			if($distance <= 1 && $entity instanceof PLayer){
				$motion = $entity->subtract($pos)->normalize();

				$impact = (1 - $distance) * ($exposure = 1);

				$damage = (int) ((($impact * $impact + $impact) / 2) * 8 * $explosionSize + 1);

				if($e instanceof Entity){
					if($this->main->canAttack($e->getName(), $entity->getName())['result']){
						$ev = new EntityDamageByEntityEvent($e, $entity, EntityDamageEvent::CAUSE_ENTITY_EXPLOSION, $damage);
					}else{
						continue;
					}
				}elseif($e instanceof Block){
					$ev = new EntityDamageByBlockEvent($e, $entity, EntityDamageEvent::CAUSE_BLOCK_EXPLOSION, $damage);
				}else{
					$ev = new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_BLOCK_EXPLOSION, $damage);
				}

				$entity->attack($ev->getFinalDamage(), $ev);
				//$entity->setMotion($motion->multiply($impact));
			}
		}
		$pk = new ExplodePacket;
		$pk->x = $pos->x;
		$pk->y = $pos->y;
		$pk->z = $pos->z;
		$pk->radius = $size;
		$pk->records = [];
		$pos->level->addChunkPacket($pos->x >> 4, $pos->z >> 4, $pk);
		return true;
	}

	public function Attack_range(AxisAlignedBB $aabb, $damage, Level $level, Player $player = null){
	$list = $level->getNearbyEntities($aabb, $player instanceof Entity ? $player : null);
		if($this->main->dev == 2){
			foreach($list as $entity){
				if(Enemy::isEnemy($entity) && $player instanceof Player){
					$ev = new EntityDamageByEntityEvent($player, $entity, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $damage, 0);
					$entity->attack($ev->getFinalDamage(), $ev);
				}
			}
		}else{
			foreach($list as $entity){
				if($entity instanceof PLayer && $player instanceof Player && $this->main->canAttack($player->getName(), $entity->getName())['result']){
					$ev = new EntityDamageByEntityEvent($player, $entity, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $damage, 0);
					$entity->attack($ev->getFinalDamage(), $ev);
				}
			}
		}
		return true;
	}

	//TNT爆発
	public function TNTExplode($eid, $type){
		if(isset($this->tnt_data[$type][$eid]) && isset($this->arrow[$type][$eid])){
			$data = $this->tnt_data[$type][$eid];
			$player = $data['player'];
			//$pos = $data['pos'];
			$pos = $this->arrow[$type][$eid][3]; 
			$user = $data['name'];
			$color = $data['color'];
			$x = $pos->x;
			$y = $pos->y;
			$z = $pos->z;
			$pos_ar = [
				[$x, $y, $z],
				[$x + 1, $y, $z],
				[$x + 1, $y + 1, $z],
				[$x + 1, $y - 1, $z],
				[$x + 1, $y + 2, $z],
				[$x + 1, $y - 2, $z],
				[$x + 1, $y + 1, $z + 1],
				[$x + 1, $y - 1, $z + 1],
				[$x + 1, $y + 2, $z + 1],
				[$x + 1, $y - 2, $z + 1],
				[$x + 1, $y + 1, $z - 1],
				[$x + 1, $y - 1, $z - 1],
				[$x + 1, $y + 2, $z - 1],
				[$x + 1, $y - 2, $z - 1],
				[$x + 1, $y + 1, $z + 2],
				[$x + 1, $y - 1, $z + 2],
				[$x + 1, $y + 2, $z + 2],
				[$x + 1, $y - 2, $z + 2],
				[$x + 1, $y + 1, $z - 2],
				[$x + 1, $y - 1, $z - 2],
				[$x + 1, $y + 2, $z - 2],
				[$x + 1, $y - 2, $z - 2],
				[$x + 1, $y, $z + 1],
				[$x + 1, $y, $z - 1],
				[$x + 1, $y, $z + 2],
				[$x + 1, $y, $z - 2],
				[$x - 1, $y, $z],
				[$x - 1, $y + 1, $z],
				[$x - 1, $y - 1, $z],
				[$x - 1, $y + 2, $z],
				[$x - 1, $y - 2, $z],
				[$x - 1, $y + 1, $z + 1],
				[$x - 1, $y - 1, $z + 1],
				[$x - 1, $y + 2, $z + 1],
				[$x - 1, $y - 2, $z + 1],
				[$x - 1, $y + 1, $z - 1],
				[$x - 1, $y - 1, $z - 1],
				[$x - 1, $y + 2, $z - 1],
				[$x - 1, $y - 2, $z - 1],
				[$x - 1, $y + 1, $z + 2],
				[$x - 1, $y - 1, $z + 2],
				[$x - 1, $y + 2, $z + 2],
				[$x - 1, $y - 2, $z + 2],
				[$x - 1, $y + 1, $z - 2],
				[$x - 1, $y - 1, $z - 2],
				[$x - 1, $y + 2, $z - 2],
				[$x - 1, $y - 2, $z - 2],
				[$x - 1, $y, $z + 1],
				[$x - 1, $y, $z - 1],
				[$x - 1, $y, $z + 2],
				[$x - 1, $y, $z - 2],
				[$x + 2, $y, $z],
				[$x + 2, $y + 1, $z],
				[$x + 2, $y - 1, $z],
				[$x + 2, $y + 2, $z],
				[$x + 2, $y - 2, $z],
				[$x + 2, $y + 1, $z + 1],
				[$x + 2, $y - 1, $z + 1],
				[$x + 2, $y + 2, $z + 1],
				[$x + 2, $y - 2, $z + 1],
				[$x + 2, $y + 1, $z - 1],
				[$x + 2, $y - 1, $z - 1],
				[$x + 2, $y + 2, $z - 1],
				[$x + 2, $y - 2, $z - 1],
				[$x + 2, $y, $z + 1],
				[$x + 2, $y, $z - 1],
				[$x + 2, $y, $z + 2],
				[$x + 2, $y, $z - 2],
				[$x - 2, $y, $z],
				[$x - 2, $y + 1, $z],
				[$x - 2, $y - 1, $z],
				[$x - 2, $y + 2, $z],
				[$x - 2, $y - 2, $z],
				[$x - 2, $y + 1, $z + 1],
				[$x - 2, $y - 1, $z + 1],
				[$x - 2, $y + 2, $z + 1],
				[$x - 2, $y - 2, $z + 1],
				[$x - 2, $y + 1, $z - 1],
				[$x - 2, $y - 1, $z - 1],
				[$x - 2, $y + 2, $z - 1],
				[$x - 2, $y - 2, $z - 1],
				[$x - 2, $y, $z + 1],
				[$x - 2, $y, $z - 1],
				[$x, $y + 1, $z],
				[$x, $y + 1, $z + 1],
				[$x, $y + 1, $z - 1],
				[$x, $y + 1, $z + 2],
				[$x, $y + 1, $z - 2],
				[$x, $y - 1, $z],
				[$x, $y - 1, $z + 1],
				[$x, $y - 1, $z - 1],
				[$x, $y - 1, $z + 2],
				[$x, $y - 1, $z - 2],
				[$x, $y + 2, $z],
				[$x, $y + 2, $z + 1],
				[$x, $y + 2, $z - 1],
				[$x, $y - 2, $z],
				[$x, $y - 2, $z + 1],
				[$x, $y - 2, $z - 1],
				[$x, $y, $z + 1],
				[$x, $y, $z - 1],
				[$x, $y, $z + 2],
				[$x, $y, $z - 2],
			];
			$level = Server::getInstance()->getDefaultLevel();
			$position = new Position($x, $y, $z, $level);
			//$explosion = new Explosion($position, 3);
			//$explosion->explodeB();
			$this->Explode($position, 3, $player);
			$this->changeWoolsColor($level, $pos_ar, $color, $user, false);
			$pk = new RemoveEntityPacket;
			$pk->eid = $eid;
			Server::broadcastPacket(Server::getInstance()->getOnlinePlayers(), $pk);
			unset($this->tnt_data[$type][$eid]);
			unset($this->arrow[$type][$eid]);
		}
	}

	/**
	 * 羊毛の色をset
	 * @param  Level  $level
	 * @param  int    $x
	 * @param  int    $y
	 * @param  int    $z
	 * @param  int    $color_num ブロックのデータ値
	 * @param  string $user
	 * @return bool
	 */
	public function changeWoolColor(Level $level = null, $x, $y, $z, $color_num, $user){
		if(!$color_num) return false;
		if($level == null) $level = Server::getInstance()->getDefaultLevel();
		if($level->getBlockIdAt($x, $y, $z) === 35){
		//if($this->main->isWool($x, $y, $z)){
			$level->setBlockDataAt($x, $y, $z, $color_num);
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 羊毛の色をset
	 * @param  Level  $level
	 * @param  array  $pos_ar
	 * @param  int    $color_num
	 * @return int    ブロックの色を変えた数 | false
	 */
	public function changeWoolsColor(Level $level = null, $pos_ar, $color_num, $user = false, $ink_consume = false){
		if(!$color_num){
			return false;
		}

		if(!$level){
			$level = Server::getInstance()->getDefaultLevel();
		}
		$amount = count($pos_ar);
		$playerData = Account::getInstance()->getData($user);
		if($ink_consume){
			if(!$playerData->canConsumeInk($amount)){
				return false;
			}
		}
		$cnt = 0;
		$blocks = [];
		foreach($pos_ar as $pos){
			if($level->getBlockIdAt(floor($pos[0]), floor($pos[1]), floor($pos[2])) === 35){
			//if($this->main->isWool($pos[0], $pos[1], $pos[2])){
				$level->setBlockDataAt(floor($pos[0]), floor($pos[1]), floor($pos[2]), $color_num);
				$blocks[] = [$pos[0], $pos[1], $pos[2]];
				$cnt++;
			}else if($user !== false && $level->getBlockIdAt(floor($pos[0]), floor($pos[1]), floor($pos[2])) === 118){
				$this->createGeyser($level, $pos[0], $pos[1], $pos[2], $color_num, $user);
			}
		}
		$change_cnt = $this->main->changeWoolsindex($blocks, $user);
		//print_r(["count" => $amount, "change" => $cnt]);//debug!!
		$iuser = strtolower($user);
		if($user != false && $ink_consume){
			//$playerData->consumeInk($cnt);
		}
		if($user != false){
			//色を変えたブロックのみカウント
			$playerData->addPaintAmount($change_cnt);
		}
		return $cnt;
	}

	public function createGeyser($level, $x, $y, $z, $color, $user){
		$block = $level->getBlock(new Vector3(floor($x), floor($y), floor($z)));
		if(!isset($block->geyser)){
			$geyser = new Geyser($this, $level, $block, $color, Server::getInstance()->getPlayer($user));
			$block->geyser = $geyser;
			Server::getInstance()->getScheduler()->scheduleRepeatingTask($geyser, 4);
		}
	}

	public function canClose($id){
		return (isset($this->blockData[$id])) ? $this->blockData[$id] : true;
	}

	/**
	 * ブラスター弾発射
	 * @param Player  $player
	 * @param int     $itemid
	 * @param int     $player_type   試合中か試し塗りか
	 */
	public function addBlasterBullet($player, $itemid, $player_type = 0){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$weapon_num = $this->getWeaponNumFromItemID($itemid);
		$yaw = $player->yaw;
		$pitch = $player->pitch;
		$yaw_rand = $this->getDiffusionValue($weapon_num);
		$rad_y = ($yaw+$yaw_rand)/180*M_PI;
		$rad_p = ($pitch-180)/180*M_PI;
		$xx = sin($rad_y)*cos($rad_p);
		$yy = sin($rad_p);
		$zz = -cos($rad_y)*cos($rad_p);
		$blaster = new BlasterBullet($this, $player, $xx, $yy, $zz, $weapon_num);
		Server::getInstance()->getScheduler()->scheduleRepeatingTask($blaster, 1);
		return true;
	}

	/**
	 * チャージャー弾を発射
	 * @param Player  $player
	 * @param int     $itemid
	 * @param int     $player_type   試合中か試し塗りか
	 * @param int     $force
	 * @param int     $maxforce
	 */
	public function addChargerBullet($player, $itemid, $force, $maxforce){
		$user = $player->getName();
		$fp = $force/$maxforce;
		$playerData = Account::getInstance()->getData($user);
		$color = $playerData->getColor();
		$weapon_num = $playerData->getNowWeapon();
		$yaw = $player->yaw;
		$pitch = $player->pitch;
		$yaw_rand = $this->getDiffusionValue($weapon_num);
		$newpos = $player->getNextPosition();
		$x = $newpos->x;
		$y = $newpos->y+1.5;
		$z = $newpos->z;
		$rad_y = ($yaw+$yaw_rand)/180*M_PI;
		$rad_p = ($pitch-180)/180*M_PI;
		$xx = sin($rad_y)*cos($rad_p);
		$yy = sin($rad_p);
		$zz = -cos($rad_y)*cos($rad_p);
		$level = $player->getLevel();
		$range = $this->getFiringRange($weapon_num);
		if($force < $maxforce){
			$range *= (0.3+$fp)*3/4;
		}
		$wool = Block::get(35, $color);
		$no_break = true;
		$r = 0;
		for($p = 0; $p <= $range; $p++){
			$sx = $x+$xx*$p;
			$sy = $y+$yy*$p;
			$sz = $z+$zz*$p;
			$bid = $level->getBlockIdAt(floor($sx), floor($sy), floor($sz));
			if($this->canThrough($bid)){
				$r = $p;
				$level->addParticle(new TerrainParticle(new Vector3($sx, $sy, $sz), $wool));
				/*if($p%2 == 0){
					
					$particle = new TerrainParticle(new Vector3($sx, $sy, $sz), $wool);

					$F = function () use ($level, $particle){
						$level->addParticle($particle);
					};

					Server::getInstance()->getScheduler()->scheduleDelayedTask(new LateDo($this, $F, []), $p/2);
				}*/
			}else{
				$r = $p;
				$no_break = false;
				break;
			}
		}
		$this->orbitPaint(new Vector3(floor($x), floor($y), floor($z)), new Vector3(floor($x+$xx*$r), floor($y+$yy*$r), floor($z+$zz*$r)), $itemid, $level, $color, $user, $fp);
		$this->endBullet(floor($x+$xx*$r), floor($y+$yy*$r), floor($z+$zz*$r), $level, $color, $user);
		$members_all = $this->battleMember;
		$rr = 0.8;
		foreach ($members_all as $team_num => $members){
			foreach($members as $member){
				$player_v = Server::getInstance()->getPlayer($member);
				if($player_v instanceof Player){
					$canAttack = $this->main->canAttack($member, $user)['result'];
					if($canAttack){
						$vx = $player_v->x;
						$vy = $player_v->y+1.5;
						$vz = $player_v->z;
						$dis = sqrt(pow($x-$vx,2)+pow($y-$vy,2)+pow($z-$vz,2));
						if($dis <= $r){
							if(sqrt(pow($x+$xx*$dis-$vx,2)+pow($y+$yy*$dis-$vy,2)+pow($z+$zz*$dis-$vz,2)) <= $rr){
								$damage = $this->getAttackDamage($weapon_num);
								if($force < $maxforce){
									$damage *= ($fp+1)/3;
								}
								$knockback = $this->getKnockbackValue($weapon_num);
								$player_v->attack($damage, new EntityDamageByEntityEvent($player, $player_v, EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK, $damage, $knockback));
							}
						}
					}
				}
			}
		}
		return true;
	}
	/**
	 * 弾を発射開始
	 * @param Player  $player
	 * @param int     $itemid
	 * @param int     $player_type   試合中か試し塗りか
	 * @param int     $count         スピナーなどの回転に使用
	 */
	public function addShooterBullet($player, $itemid, $player_type = 0, $count = 0){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$color = $playerData->getColor();
		$weapon_num = $this->getWeaponNumFromItemID($itemid);
		$yaw = $player->yaw;
		$pitch = $player->pitch;
		$yaw_rand = $this->getDiffusionValue($weapon_num);
		$newpos = $player->getNextPosition();
		$x = $newpos->x;
		$y = $newpos->y+1.5;
		$z = $newpos->z;
		$rad_y = ($yaw+$yaw_rand)/180*M_PI;
		$rad_p = ($pitch-180)/180*M_PI;
		$xx = sin($rad_y)*cos($rad_p);
		$yy = sin($rad_p);
		$zz = -cos($rad_y)*cos($rad_p);
		$level = $player->getLevel();
		$range = $this->getFiringRange($weapon_num);
		$wool = Block::get(35, $color);
		$no_break = true;
		$r = 0;
		for($p = 0; $p <= $range; $p++){
			$sx = $x+$xx*$p;
			$sy = $y+$yy*$p;
			$sz = $z+$zz*$p;
			$bid = $level->getBlockIdAt(floor($sx), floor($sy), floor($sz));
			if($this->canThrough($bid)){
				$r = $p;
				if($p%2 == 0){
					/*
					$particle = new TerrainParticle(new Vector3($sx, $sy, $sz), $wool);

					$F = function () use ($level, $particle){
						$level->addParticle($particle);
					};

					Server::getInstance()->getScheduler()->scheduleDelayedTask(new LateDo($this, $F, []), $p/2);*/
				}
			}else{
				$r = $p;
				$no_break = false;
				break;
			}
		}
		$shooterParticle = new ShooterParticle($player, $xx, $yy, $zz, $r, $color);
		Server::getInstance()->getScheduler()->scheduleRepeatingTask($shooterParticle, 1);
		$this->orbitPaint(new Vector3(floor($x), floor($y), floor($z)), new Vector3(floor($x+$xx*$r), floor($y+$yy*$r), floor($z+$zz*$r)), $itemid, $level, $color, $user);
		$this->endBullet(floor($x+$xx*$r), floor($y+$yy*$r), floor($z+$zz*$r), $level, $color, $user);
		$rr = ($this->getWeaponData($weapon_num)['type'] == self::TYPE_SHOOTER) ? 1.35 : 1.15;
		if($this->main->dev == 2){
			$members_all = $level->getEntities();
			foreach($members_all as $player_v){
				if(Enemy::isEnemy($player_v)){
					$vx = $player_v->x;
					$vy = $player_v->y+1.5;
					$vz = $player_v->z;
					$dis = sqrt(pow($x-$vx,2)+pow($y-$vy,2)+pow($z-$vz,2));
					if($dis <= $r){
						if(sqrt(pow($x+$xx*$dis-$vx,2)+pow($y+$yy*$dis-$vy,2)+pow($z+$zz*$dis-$vz,2)) <= $rr){
							$damage = $this->getAttackDamage($weapon_num);
							$knockback = $this->getKnockbackValue($weapon_num);
							$player_v->attack($damage, new EntityDamageByEntityEvent($player, $player_v, EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK, $damage, $knockback));
						}
					}
				}
			}
		}else{
			$members_all = $this->battleMember;
			foreach ($members_all as $team_num => $members){
				foreach($members as $member){
					$player_v = Server::getInstance()->getPlayer($member);
					if($player_v instanceof Player){
						$canAttack = $this->main->canAttack($member, $user)['result'];
						if($canAttack){
							$vx = $player_v->x;
							$vy = $player_v->y+1.5;
							$vz = $player_v->z;
							$dis = sqrt(pow($x-$vx,2)+pow($y-$vy,2)+pow($z-$vz,2));
							if($dis <= $r){
								if(sqrt(pow($x+$xx*$dis-$vx,2)+pow($y+$yy*$dis-$vy,2)+pow($z+$zz*$dis-$vz,2)) <= $rr){
									$damage = $this->getAttackDamage($weapon_num);
									$knockback = $this->getKnockbackValue($weapon_num);
									$player_v->attack($damage, new EntityDamageByEntityEvent($player, $player_v, EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK, $damage, $knockback));
								}
							}
						}
					}
				}
			}
		}
		return true;
	}

	public function endBullet($x, $y, $z, $level, $color, $user){
		$pos_ar = [
			[$x, $y, $z],
			[$x + 1, $y, $z],
			[$x - 1, $y, $z],
			[$x, $y, $z - 1],
			[$x + 1, $y, $z - 1],
			[$x - 1, $y, $z - 1],
			[$x, $y, $z + 1],
			[$x + 1, $y, $z + 1],
			[$x - 1, $y, $z + 1],
			[$x, $y + 1, $z],
			[$x + 1, $y + 1, $z],
			[$x - 1, $y + 1, $z],
			[$x, $y + 1, $z - 1],
			[$x + 1, $y + 1, $z - 1],
			[$x - 1, $y + 1, $z - 1],
			[$x, $y + 1, $z + 1],
			[$x + 1, $y + 1, $z + 1],
			[$x - 1, $y + 1, $z + 1],
		];
		$rand = [4, 5, 7, 8, 13, 14, 16, 17];
		$key = $rand[array_rand($rand)];
		unset($pos_ar[$key]);
		$r = $this->changeWoolsColor($level, $pos_ar, $color, $user, false);
	}

	/**
	 * プレイヤーをアシッド状態にする
	 * @param Player $attacker 状態異常を付与したプレイヤー
	 * @param Player $victim 状態異常を付与されたプレイヤー
	 * @param int $time 効果時間(tick)
	 */
	public function setAcid($attacker, $victim, $time = 200){
		if(isset($this->Task["Acid"][$victim->getName()])){
			Server::getInstance()->getScheduler()->cancelTask($this->Task["Acid"][$victim->getName()]->getTaskId());
			unset($this->Task["Acid"][$victim->getName()]);
		}
		$time /= Gadget::getCorrection($victim, Gadget::BOMB_GUARD);
		$acid = new Acid($this, $attacker, $victim, $time/20);
		Server::getInstance()->getScheduler()->scheduleRepeatingTask($acid, 20);
		$this->Task["Acid"][$victim->getName()] = $acid;
	}

	/**
	 * 対象が目視可能かどうかを取得する
	 * @param Player $player
	 * return boolean
	 */
	public function canLook($player){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		if($this->main->spawnedSquid($player)){
			if($this->main->w->getSensor($player) || !$playerData->getRate()){
				return true;
			}
			return false;
		}
		return true;
	}
	/**
	 * 弾を発射開始
	 * @param Player  $player
	 * @param int     $itemid
	 * @param int     $player_type   試合中か試し塗りか
	 * @param int     $count         スピナーなどの回転に使用
	 */
	public function MakeArrow($player, $itemid, $player_type = 0, $count = 0){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$color = $playerData->getColor();
		$pk = new AddEntityPacket;
		$weapon_num = $this->getWeaponNumFromItemID($itemid);
		$yaw_rand = $this->getDiffusionValue($weapon_num);
		switch($itemid){
			case 46://TNT
				$type = 65;
				$MX = -sin($player->yaw / 180 * M_PI) * cos($player->pitch / 180 * M_PI) * 1.5;
				$MY = (-sin($player->pitch / 180 * M_PI) + 0.2) * 1.5;
				$MZ = cos($player->yaw / 180 * M_PI) * cos($player->pitch / 180 * M_PI) * 1.5;
				break;
			case 261://スプラチャージャー
				$type = 80;
				$MX = -sin(($player->yaw + $yaw_rand) / 180 * M_PI) * cos($player->pitch / 180 * M_PI) * 3;
				$MY = -sin($player->pitch / 180 * M_PI) * 3;
				$MZ = cos(($player->yaw + $yaw_rand) / 180 * M_PI) * cos($player->pitch / 180 * M_PI) * 3;
				break;
			case 290://わかばシューター
				$type = 81;
				$MX = -sin(($player->yaw + $yaw_rand) / 180 * M_PI) * cos($player->pitch / 180 * M_PI) * 2.5;
				$MY = -sin($player->pitch / 180 * M_PI) * 2.5 - 0.021;
				$MZ = cos(($player->yaw + $yaw_rand) / 180 * M_PI) * cos($player->pitch / 180 * M_PI) * 2.5;
				break;
			case 293://プロモデラーPG
			case 279://デルタスイーパーM
				$type = 81;
				$MX = -sin(($player->yaw + $yaw_rand) / 180 * M_PI) * cos($player->pitch / 180 * M_PI) * 2.5;
				$MY = -sin($player->pitch / 180 * M_PI) * 2.5 - 0.021;
				$MZ = cos(($player->yaw + $yaw_rand) / 180 * M_PI) * cos($player->pitch / 180 * M_PI) * 2.5;
				break;
			case 296://ボールドマーカー
				$type = 81;
				$MX = -sin(($player->yaw + $yaw_rand) / 180 * M_PI) * cos($player->pitch / 180 * M_PI) * 2;
				$MY = -sin($player->pitch / 180 * M_PI) * 2.5 - 0.021;
				$MZ = cos(($player->yaw + $yaw_rand) / 180 * M_PI) * cos($player->pitch / 180 * M_PI) * 2;
				break;
			case 286://ハイドラント
				$type = 81;
				$round = $count * (360 / 7);
				$sin   = -0.2 * sin(deg2rad($round));
				$cos   =  0.2 * cos(deg2rad($round));
				$pk->x = $player->x + $sin;
				$pk->y = $player->y + $player->getEyeHeight() + sin(deg2rad($round)) * cos(deg2rad($round));
				$pk->z = $player->z + $cos;

				$MX = -sin(($player->yaw + $yaw_rand) / 180 * M_PI) * cos($player->pitch / 180 * M_PI) * 2.5;
				$MY = -sin($player->pitch / 180 * M_PI) * 2.5 - 0.021;
				$MZ = cos(($player->yaw + $yaw_rand) / 180 * M_PI) * cos($player->pitch / 180 * M_PI) * 2.5;
				break;
			case 258://バレルスピナー
				$type = 81;
				$round = $count * (360 / 7);
				$sin   = -0.2 * sin(deg2rad($round));
				$cos   =  0.2 * cos(deg2rad($round));
				$pk->x = $player->x + $sin;
				$pk->y = $player->y + $player->getEyeHeight() + sin(deg2rad($round)) * cos(deg2rad($round));
				$pk->z = $player->z + $cos;

				$MX = -sin(($player->yaw + $yaw_rand) / 180 * M_PI) * cos($player->pitch / 180 * M_PI) * 2.5;
				$MY = -sin($player->pitch / 180 * M_PI) * 2.5 - 0.021;
				$MZ = cos(($player->yaw + $yaw_rand) / 180 * M_PI) * cos($player->pitch / 180 * M_PI) * 2.5;
				break;
			case 275://スプラスピナー
				$type = 81;
				$round = $count * (360 / 7);
				$sin   = -0.25 * sin(deg2rad($round));
				$cos   =  0.25 * cos(deg2rad($round));
				$pk->x = $player->x + $sin;
				$pk->y = $player->y + $player->getEyeHeight() + sin(deg2rad($round)) * cos(deg2rad($round));
				$pk->z = $player->z + $cos;

				$MX = -sin(($player->yaw + $yaw_rand) / 180 * M_PI) * cos($player->pitch / 180 * M_PI) * 2.5;
				$MY = -sin($player->pitch / 180 * M_PI) * 2.5 - 0.0315;
				$MZ = cos(($player->yaw + $yaw_rand) / 180 * M_PI) * cos($player->pitch / 180 * M_PI) * 2.5;
				break;
			case 273://L3リールガン
			case 271://T3リールガン
				$type = 81;
				$MX = -sin(($player->yaw + $yaw_rand) / 180 * M_PI) * cos($player->pitch / 180 * M_PI) * 2.5;
				$MY = -sin($player->pitch / 180 * M_PI) * 2.5 - 0.0315;
				$MZ = cos(($player->yaw + $yaw_rand) / 180 * M_PI) * cos($player->pitch / 180 * M_PI) * 2.5;
				break;
			default:
				$type = 81;
				$MX = -sin(($player->yaw + $yaw_rand) / 180 * M_PI) * cos($player->pitch / 180 * M_PI) * 2.5;
				$MY = -sin($player->pitch / 180 * M_PI) * 2.5;
				$MZ = cos(($player->yaw + $yaw_rand) / 180 * M_PI) * cos($player->pitch / 180 * M_PI) * 2.5;
		}
		$arrow = $pk->eid = Entity::$entityCount++;
		$pk->type = $type;
		if(!isset($pk->x)){
			$pk->x = $player->x;
			$pk->y = $player->y + $player->getEyeHeight();
			$pk->z = $player->z;
		}
		$pk->yaw = $player->yaw + $yaw_rand;
		$pk->pitch = $player->pitch;
		$fire_flag = ($player->getDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_ONFIRE)) ? 1 : 0;
		$pk->metadata = [Entity::DATA_FLAGS => [Entity::DATA_TYPE_BYTE, $fire_flag]];
		$player->level->addChunkPacket($pk->x >> 4, $pk->z >> 4, $pk);
		$this->arrow[$player_type][$arrow] = [
			0 => $player,
			1 => $itemid,
			2 => $color,
			3 => new Location($pk->x, $pk->y, $pk->z, $pk->yaw, $pk->pitch, $player->level),//pos
			4 => new Vector3($MX, $MY, $MZ),//motion
			5 => new Vector3($player->x, $player->y + $player->getEyeHeight(), $player->z),//startpos
			6 => $player->getDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_ONFIRE),//火がついているかどうか
		];
		$this->MoveArrow($arrow, $player_type);//0929hitoshi (11/03)
		return true;
	}

	public function MoveArrow($arrow, $player_type = 0){
		if(!isset($this->arrow[$player_type][$arrow])) return false;
		$data = $this->arrow[$player_type][$arrow];
		$player = $data[0];
		$user = $player->getName();
		$id = $data[1];
		$color = $data[2];
		$level = $data[0]->getLevel();
		$pos = $data[3];
		$motion = $data[4];
		switch($id){
			//case 46:
			case 279:
			case 290:
			case 293:
			case 258:
			case 286:
				$motion->y -= 0.065625;
				break;
			case 275:
				$motion->y -= 0.084;
				break;
			case 261:
				//$motion->y -= 0.021;
				$motion->y -= 0.075;
				break;
			case 46:
				$motion->y -= 0.05;
				break;
			default:
				$motion->y -= 0.042;
		}
		$this->arrow[$player_type][$arrow][4] = $motion;
		$x = $pos->x;
		$y = $pos->y;
		$z = $pos->z;
		//現在の位置にブロックがあるかどうか
		$blockId_dest = $level->getBlockIdAt(Math::floorFloat($x), Math::floorFloat($y), Math::floorFloat($z));
		if($this->canClose($blockId_dest)) return $this->CloseArrow($arrow, $player_type);

		#ここから移動処理
		$mx = $motion->x;
		$my = $motion->y;
		$mz = $motion->z;
		//$newpos = $this->arrow[$arrow][3] = new Vector3($x + $mx, $y + $my, $z + $mz);
		$newpos = $this->arrow[$player_type][$arrow][3]->setComponents($x + $mx, $y + $my, $z + $mz);
		//$newpos = $this->arrow[$player_type][$arrow][3];

		#移動先までの間にブロックがあるかどうか
		#射程外の距離かどうか
		$startpos = $data[5];
		$fielddata = $this->getFieldData($player_type);
		if(!isset($fielddata['scan'])) return false;
		$scan = $fielddata['scan'];
		$weapon_num = $this->getWeaponNumFromItemID($id);
		$max_x = $max_y = $max_z = $this->getFiringRange($weapon_num);
		$dist = max(abs($mx), abs($mz));

		for($i = 0; $i <= $dist; $i++){

			$d = $i / 10;
			$B_id_d = $level->getBlockIdAt(Math::floorFloat($x + ($mx * $d)), Math::floorFloat($y + ($my * $d)), Math::floorFloat($z + ($mz * $d)));

			if($this->canClose($B_id_d)){
				$this->arrow[$player_type][$arrow][3]->setComponents($x + ($mx * $d), $y + ($my * $d), $z + ($mz * $d));
				return $this->CloseArrow($arrow, $player_type);
			}elseif($id === 261){
				$pk = new LevelEventPacket;
				// $pk->evid = LevelEventPacket::EVENT_PARTICLE_DESTROY;
				$pk->evid = LevelEventPacket::EVENT_ADD_PARTICLE_MASK | Particle::TYPE_TERRAIN & 0xFFF;
				$pk->x = $pos->x;
				$pk->y = $pos->y;
				$pk->z = $pos->z;
				$pk->data = (($color << 8) | 35);
				$level->addChunkPacket($pk->x >> 4, $pk->z >> 4, $pk);
			}else{
				if($B_id_d === (8 || 9)){
					$particlePos = new Vector3($x + ($mx * $d), $y + ($my * $d), $z + ($mz*$d));
					$level->addParticle(new BubbleParticle($particlePos));
				}
				if($B_id_d === (10 || 11) && !$this->arrow[$player_type][$arrow][6]){
					$pk = new SetEntityDataPacket;
					$pk->eid = $arrow;
					$pk->metadata = [Entity::DATA_FLAGS => [Entity::DATA_TYPE_BYTE, 1]];
					$this->arrow[$player_type][$arrow][6] = true;
					$packetPos = new Vector3($x + ($mx * $d), $y + ($my * $d), $z + ($mz * $d));
					$level->addChunkPacket($packetPos->x >> 4, $packetPos->z >> 4, $pk);
				}
			}
			$pos_d = new Vector3($x + ($mx * $d), $y + ($my * $d), $z + ($mz * $d));
			if(
				(abs($pos_d->x - $startpos->x) > $max_x || abs($pos_d->y - $startpos->y) > $max_y || abs($pos_d->z - $startpos->z) > $max_z) or
				($pos_d->y < 0 || $pos_d->y > 128) or
				(min($scan[1][0], $scan[2][0]) - 10 > $pos_d->x || min($scan[1][2], $scan[2][2]) - 10 > $pos_d->z) or 
				(max($scan[1][0], $scan[2][0]) + 10 < $pos_d->x || max($scan[1][2], $scan[2][2]) + 10 < $pos_d->z)
			){
				$pk = new RemoveEntityPacket;
				$pk->eid = $arrow;
				$level->addChunkPacket($pos_d->x >> 4, $pos_d->z >> 4, $pk);
				if($id !== 46){
					$item = ($id === 261) ? Item::get(Item::ARROW) : Item::get(Item::SNOWBALL);
					$particle_B = new ItemBreakParticle($pos_d, $item);
					$level->addParticle($particle_B);
				}
				$this->orbitPaint($startpos, $pos_d, $id, $level, $color, $user);
				unset($this->arrow[$player_type][$arrow]);
				return true;
			}

			//あたり判定チェック (player)
			$members_all = $this->battleMember;
			foreach ($members_all as $team_num => $members){
				foreach($members as $member){
					$player = Server::getInstance()->getPlayer($member);
					if($player instanceof Player){
						$player_x = $player->x; $player_y = $player->y; $player_z = $player->z;
						$result = ($id === 261) ? 
							(abs($pos_d->x - $player_x) <= 1 && abs($pos_d->z - $player_z) <= 1 && abs($pos_d->y - $player_y) <= 1.6) :
							(abs($pos_d->x - $player_x) <= 1.2 && abs($pos_d->z - $player_z) <= 1.2 && abs($newpos->y - $player_y) <= 1.6);
						if($result){
							$bower = $data[0];//攻撃した相手
							$canAttack = $this->main->canAttack($bower->getName(), $player->getName())['result'];
							if(!isset($this->main->Task['Respawn'][$player->getName()]) && $canAttack && $bower->getId() != $player->getId() && $player->isAlive() && !$player->isSpectator()){
								$weapon_num = $this->getWeaponNumFromItemID($id);
								$damage = $this->getAttackDamage($weapon_num);
								$knockback = $this->getKnockbackValue($weapon_num);
								
								// $ev = new EntityDamageByEntityEvent($bower, $player, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $d, $knockback);
								$ev = new EntityDamageByEntityEvent($bower, $player, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $damage, 0);
								$deltaX = $player->x - $pos_d->x;
								$deltaZ = $player->z - $pos_d->z;
								$player->attack($d, $ev);
								if($knockback > 0){
									$player->knockBack($bower, $damage, $deltaX, $deltaZ, $knockback);
								}
								if($data[6] && $canAttack){
									$ticks = 5 * 20;
									if($ticks > $player->fireTicks){
										$player->fireTicks = $ticks;
									}
								}
								$this->CloseArrow($arrow, $player_type);

								return true;
							}
						}
					}
				}
			}
		}
		/*if($id == 261){
			$InkParticle = new DestroyBlockParticle($pos, new Block(35, $color));
			$level->addParticle($InkParticle);
		}*/
		/*$pk = new SetEntityMotionPacket;
		$pk->eid = $arrow;
		$pk->speedX = $mx;
		$pk->speedY = $my;
		$pk->speedZ = $mz;
		$level->addChunkPacket($newpos->x >> 4, $newpos->z >> 4, $pk);*/
		return true;
	}

	/**
	 * $startPosから$endPosまでの間を塗る
	 * @param  Vector3     $startPos
	 * @param  Vector3     $endPos
	 * @param  int         $itemId
	 * @param  Level       $level
	 * @param  int         $color
	 * @param  string      $user
	 * @return int | false
	 */
	public function orbitPaint($startPos, $endPos, $itemId, $level, $color, $user, $per = 1){
		$paintedRate = 1;
		switch($itemId){
			case 261:
			case 359:
			case 268:
			case 272:
			case 267:
				$paintedRate = 1;//Todo チャージ時間によって塗り具合を変える
				break;
			case 258:
				$shotCount = 40;
				$paintedRate = 0.3;
				//$paintedRate /= $shotCount;
				break;
			case 275:
				$shotCount = 27;
				$paintedRate = 0.4;
				//$paintedRate /= $shotCount;
				break;
			case 286:
				$shotCount = 55;
				$paintedRate = 0.15;
				//$paintedRate /= $shotCount;
				break;
			case 273:
			case 271:
				$paintedRate = 0.3;
				break;
			case 279:
				$paintedRate = 0.4;				
				break;
			case 256:
			case 269:
			case 277:
			case 284:
			case 290:
			case 293:
			case 291:
			case 292:
			case 294:
				$paintedRate = 0.7;
				break;
			case 290:
				$paintedRate = 0.95;
				break;
			default:
				return false;
		}
		$pos_ar = [];
		$distance = max(abs($endPos->x - $startPos->x), abs($endPos->z - $startPos->z));
		if($distance != 0){
			$x_dist = ($endPos->x - $startPos->x) / $distance;
			$z_dist = ($endPos->z - $startPos->z) / $distance;
			$y_high = max($endPos->y, $startPos->y - 2);
			$y_low  = min($endPos->y, $startPos->y - 2);
			/*
			for($c = 0; $c <= $distance; $c++){
				if($paintedRate < 1 && $paintedRate <= mt_rand(0, 1000) / 1000){
					continue;//ランダムで塗らない
				}
				$x = floor($startPos->x + $x_dist * $c);
				$z = floor($startPos->z + $z_dist * $c);
				for($height = floor($y_low - 7); $height <= $y_high + 1; $height++){
					if($level->getBlockIdAt($x, $height, $z) == 35){
						$pos_ar[] = [$x, $height, $z];
					}
				}
			}
			*/
			//確実に一定数ブロックを塗るように変更
			$dis_ar = [];
			for($c = 0; $c <= $distance; $c++){
				$x = floor($startPos->x + $x_dist * $c);
				$z = floor($startPos->z + $z_dist * $c);
				for($height = floor($y_high + 1); $height >= $y_low - 7; $height--){
					if($level->getBlockIdAt($x, $height, $z) !== 0){
						$dis_ar[] = [$x, $height, $z];
						if($itemId == 271 || $itemId == 273){
							$dis_ar[] = [$x-1, $height, $z];
							$dis_ar[] = [$x+1, $height, $z];
							$dis_ar[] = [$x, $height, $z-1];
							$dis_ar[] = [$x, $height, $z+1];
						}else if($itemId == 261){
							if(mt_rand(0, 99) < pow($per, 2)*100){
								if(mt_rand(1, 2) != 1) $dis_ar[] = [$x-1, $height, $z];
								if(mt_rand(1, 2) != 1) $dis_ar[] = [$x+1, $height, $z];
								if(mt_rand(1, 2) != 1) $dis_ar[] = [$x, $height, $z-1];
								if(mt_rand(1, 2) != 1) $dis_ar[] = [$x, $height, $z+1];
							}
						}
						if($itemId !== 261){
							break;
						}
					}
				}
			}
			$block_cnt = count($dis_ar);
			// shuffle($dis_ar);
			$this->mt_shuffle($dis_ar);
			$paintBlock_cnt = $block_cnt * $paintedRate;
			foreach($dis_ar as $pos){
				if($paintBlock_cnt-- > 0){
					$pos_ar[] = $pos;
				}
			}
			$r = $this->changeWoolsColor($level, $pos_ar, $color, $user, false);
			return $r;
		}
		return false;
	}

	function mt_shuffle(array &$array){
		$array = array_values($array);
		for($i = count($array) - 1; $i > 0; --$i){
			//$j = mt_rand(0, $i);
			$j = random_int(0, $i);
			$tmp = $array[$i];
			$array[$i] = $array[$j];
			$array[$j] = $tmp;
		}
	}

	public function MoveAllArrow(){
		foreach($this->arrow as $type => $ar){
			foreach($ar as $arrow => $h){
				$this->MoveArrow($arrow, $type);
			}
		}
	}

	/**
	 * シューターブキの自動発射
	 */
	public function AutoShot(){
		$members_all = $this->battleMember;
		foreach ($members_all as $team_num => $members){
			foreach($members as $member){
				$player = Server::getInstance()->getPlayer($member);
				if($player instanceof Player){
					$user = $player->getName();
					$player_data = Account::getInstance()->getData($user);

					if($player->spawned && $player->isAlive() && $player->getDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE) == false && $player_data->getRate()){
						$handItem = $player->getInventory()->getItemInHand();
						$handItemID = $handItem->getId();
						$handItemDamage = $handItem->getDamage();

						$weaponNum = $this->shootersID[$handItemID][$handItemDamage] ?? null;

						switch($weaponNum){
							case self::SPLATTERSHOT:
							case self::SPLATTERSHOT_COLLABO:
							case self::SPLATTERSHOT_WASABI:
								$result = $this->Splattershot($player);
								if($result){
									$this->main->sendInkAmount($player);
									$players = $player->getViewers();
									$players[] = $player;
									$player->getLevel()->addSound(new LaunchSound($player, self::SHOOTER_SOUND_PITCH), $players);
									$player_data->setRate();
								}else{
									$this->main->Inkshortage($player);
								}
								break;
							case self::SPLATTERSHOT_JR:
							case self::SPLATTERSHOT_JR_MOMIJI:
							case self::SPLATTERSHOT_JR_SAKURA:
								$result = $this->SplattershotJr($player);
								if($result){
									$this->main->sendInkAmount($player);
									$players = $player->getViewers();
									$players[] = $player;
									$player->getLevel()->addSound(new LaunchSound($player, self::SHOOTER_SOUND_PITCH), $players);
									$player_data->setRate();
								}else{
									$this->main->Inkshortage($player);
								}
								break;
							case self::SPLOOSH_O_MATIC:
							case self::SPLOOSH_O_MATIC_SEVEN:
							case self::SPLOOSH_O_MATIC_NEO:							
								$result = $this->Splooshomatic($player);
								if($result){
									$this->main->sendInkAmount($player);
									$players = $player->getViewers();
									$players[] = $player;
									$player->getLevel()->addSound(new LaunchSound($player, self::SHOOTER_SOUND_PITCH), $players);
									$player_data->setRate();
								}else{
									$this->main->Inkshortage($player);
								}
								break;
							case self::SPLATTERSHOT_PRO:
							case self::SPLATTERSHOT_PRO_BERRY:
							case self::SPLATTERSHOT_PRO_COLLABO:
								$result = $this->SplatterShotPro($player);
								if($result){
									$this->main->sendInkAmount($player);
									$players = $player->getViewers();
									$players[] = $player;
									$player->getLevel()->addSound(new LaunchSound($player, self::SHOOTER_SOUND_PITCH), $players);
									$player_data->setRate();
								}else{
									$this->main->Inkshortage($player);
								}
								break;
							case self::GAL_96:
							case self::GAL_96_DECO:
							case self::GAL_96_SPICA:
								$result = $this->Gal_96($player);
								if($result){
									$this->main->sendInkAmount($player);
									$players = $player->getViewers();
									$players[] = $player;
									$player->getLevel()->addSound(new LaunchSound($player, self::SHOOTER_SOUND_PITCH), $players);
									$player_data->setRate();
								}else{
									$this->main->Inkshortage($player);
								}
								break;
							case self::GAL_52:
							case self::GAL_52_DECO:
							case self::GAL_52_VEGA:
								$result = $this->Gal_52($player);
								if($result){
									$this->main->sendInkAmount($player);
									$players = $player->getViewers();
									$players[] = $player;
									$player->getLevel()->addSound(new LaunchSound($player, self::SHOOTER_SOUND_PITCH), $players);
									$player_data->setRate();
								}else{
									$this->main->Inkshortage($player);
								}
								break;
							case self::DUAL_SQUELCHER:
							case self::DUAL_SQUELCHER_CUSTOM:
							case self::DUAL_SQUELCHER_GEMINI:
								$result = $this->DualSquelcher($player);
								if($result){
									$this->main->sendInkAmount($player);
									$players = $player->getViewers();
									$players[] = $player;
									$player->getLevel()->addSound(new LaunchSound($player, self::SHOOTER_SOUND_PITCH), $players);
									$player_data->setRate();
								}else{
									$this->main->Inkshortage($player);
								}
								break;
							case self::SPLASH_O_MATIC:
							case self::SPLASH_O_MATIC_NEO:
							case self::SPLASH_O_MATIC_TORA:
								$result = $this->SplashOMatic($player);
								if($result){
									$this->main->sendInkAmount($player);
									$players = $player->getViewers();
									$players[] = $player;
									$player->getLevel()->addSound(new LaunchSound($player, self::SHOOTER_SOUND_PITCH), $players);
									$player_data->setRate();
								}else{
									$this->main->Inkshortage($player);
								}
								break;
							case self::AEROSPRAY_PG:
							case self::AEROSPRAY_RG:
							case self::AEROSPRAY_MG:
								$result = $this->AerosprayPG($player);
								if($result){
									$this->main->sendInkAmount($player);
									$players = $player->getViewers();
									$players[] = $player;
									$player->getLevel()->addSound(new LaunchSound($player, self::SHOOTER_SOUND_PITCH), $players);
									$player_data->setRate();
								}else{
									$this->main->Inkshortage($player);
								}
								break;
							case self::DELTA_SQUELCHER_M:
							case self::DELTA_SQUELCHER_T:
							case self::DELTA_SQUELCHER_N:
								$result = $this->DeltaSquelcherM($player);
								if($result){
									$this->main->sendInkAmount($player);
									$players = $player->getViewers();
									$players[] = $player;
									$player->getLevel()->addSound(new LaunchSound($player, self::SHOOTER_SOUND_PITCH), $players);
									$player_data->setRate();
								}else{
									$this->main->Inkshortage($player);
								}
								break;
							case self::LUNA_BLASTER:
							case self::LUNA_BLASTER_NEO:
							case self::LUNA_BLASTER_MERCURY:
								$result = $this->LunaBlaster_Charge($player);
								if(!$result){
									$this->main->Inkshortage($player);
								}
								break;
							case self::LONG_BLASTER:
							case self::LONG_BLASTER_CUSTOM:
								$result = $this->LongBlaster_Charge($player);
								if(!$result){
									$this->main->Inkshortage($player);
								}
								break;
							case self::HOT_BLASTER:
							case self::HOT_BLASTER_CUSTOM:
							case self::HOT_BLASTER_LIBRA:
								$result = $this->HotBlaster_Charge($player);
								if(!$result){
									$this->main->Inkshortage($player);
								}
								break;
							case self::RAPID_BLASTER:
							case self::RAPID_BLASTER_DECO:
							case self::RAPID_BLASTER_SIRIUS:
								$result = $this->RapidBlaster_Charge($player);
								if(!$result){
									$this->main->Inkshortage($player);
								}
								break;
							case self::L3_NOZZLENOSE:
							case self::L3_NOZZLENOSE_D:
							case self::L3_NOZZLENOSE_ALTAIR:
								$result = $this->L3_Nozzlenose_Charge($player, $player_data->getColor());
								if(!$result){
									$this->main->Inkshortage($player);
								}
								break;
							case self::T3_NOZZLENOSE:
							case self::T3_NOZZLENOSE_D:
							case self::T3_NOZZLENOSE_P:
								$result = $this->T3_Nozzlenose_Charge($player, $player_data->getColor());
								if(!$result){
									$this->main->Inkshortage($player);
								}
								break;
						}
					}
				}
			}
		}
	}

	public function CloseArrow($arrow, $player_type){
		if(!isset($this->arrow[$player_type][$arrow])) return false;
		$data = $this->arrow[$player_type][$arrow];
		$player = $data[0];
		$level = $player->getLevel();
		if($data[1] !== 46){
			$pk = new RemoveEntityPacket;
			$pk->eid = $arrow;
			//Server::broadcastPacket(Server::getInstance()->getOnlinePlayers(), $pk);
			$level->addChunkPacket($data[3]->x >> 4, $data[3]->z >> 4, $pk);
			if($data[1] !== 46){
				$item = ($data[1] === 261) ? Item::get(Item::ARROW) : Item::get(Item::SNOWBALL);
				$player = $data[0];
				$level = $player->getLevel();
				$particle_B = new ItemBreakParticle($data[3], $item);
				$level->addParticle($particle_B);
			}
		}
		$pos = $data[3]->floor();
		$startpos = $data[5]->floor();
		$x = $pos->x;
		$y = $pos->y;
		$z = $pos->z;
		$user = $player->getName();
		$color = $data[2];
		$id = $data[1];
		$this->orbitPaint($startpos, $pos, $id, $level, $color, $user);
		switch($id){
			case 46:
				if(!isset($this->tnt_data[$player_type][$arrow])){
					$this->tnt_data[$player_type][$arrow] = [
						'player' => $player,
						//'pos'	 => $pos,
						'name'	 => $user,
						'color'	 => $color
					];
					//unset($this->arrow[$arrow]);
					$data[4]->setComponents(0, $data[4]->y, 0);
					$pk = new SetEntityMotionPacket;
					$pk->eid = $arrow;
					$pk->speedX = 0;
					$pk->speedY = $data[4]->y;
					$pk->speedZ = 0;
					$level->addChunkPacket($pos->x >> 4, $pos->z >> 4, $pk);
					$level->addSound(new ExplosiveSound($pos));
					Server::getInstance()->getScheduler()->scheduleDelayedTask(new TNTExplode($this, $arrow, $player_type), 20*2);//2秒後に爆発処理を実行
				}
				return true;
				break;
			case 261:
				$pos_ar = [
					[$x, $y, $z],
					[$x + 1, $y, $z],
					[$x - 1, $y, $z],
					[$x, $y, $z - 1],
					[$x + 1, $y, $z - 1],
					[$x - 1, $y, $z - 1],
					[$x, $y, $z + 1],
					[$x + 1, $y, $z + 1],
					[$x - 1, $y, $z + 1],
					[$x, $y + 1, $z],
					[$x + 1, $y + 1, $z],
					[$x - 1, $y + 1, $z],
					[$x, $y + 1, $z - 1],
					[$x + 1, $y + 1, $z - 1],
					[$x - 1, $y + 1, $z - 1],
					[$x, $y + 1, $z + 1],
					[$x + 1, $y + 1, $z + 1],
					[$x - 1, $y + 1, $z + 1],
				];
				$rand = [4, 5, 7, 8, 13, 14, 16, 17];
				$key = $rand[array_rand($rand)];
				unset($pos_ar[$key]);
				break;
			case 269:
				$pos_ar = [
					[$x, $y, $z],
					[$x + 1, $y, $z],
					[$x - 1, $y, $z],
					[$x, $y + 1, $z],
					[$x, $y, $z - 1],
					[$x, $y, $z + 1],
				];
				break;
			case 291:
				$pos_ar = [
					[$x, $y, $z],
					[$x + 1, $y, $z],
					[$x - 1, $y, $z],
					[$x, $y, $z - 1],
					[$x + 1, $y, $z - 1],
					[$x - 1, $y, $z - 1],
					[$x, $y, $z + 1],
					[$x + 1, $y, $z + 1],
					[$x - 1, $y, $z + 1],
					[$x, $y + 1, $z],
					[$x + 1, $y + 1, $z],
					[$x - 1, $y + 1, $z],
					[$x, $y + 1, $z - 1],
					[$x + 1, $y + 1, $z - 1],
					[$x - 1, $y + 1, $z - 1],
					[$x, $y + 1, $z + 1],
					[$x + 1, $y + 1, $z + 1],
					[$x - 1, $y + 1, $z + 1],
				];
				$rand = [4, 5, 7, 8, 13, 14, 16, 17];
				$key = $rand[array_rand($rand)];
				unset($pos_ar[$key]);
				break;
			case 290:
				$pos_ar = [
					[$x, $y, $z],
					[$x + 1, $y, $z],
					[$x + 1, $y + 1, $z],
					[$x + 1, $y - 1, $z],
					[$x + 1, $y + 1, $z + 1],
					[$x + 1, $y - 1, $z + 1],//5
					[$x + 1, $y + 1, $z - 1],
					[$x + 1, $y - 1, $z - 1],
					[$x + 1, $y, $z + 1],
					[$x + 1, $y, $z - 1],
					[$x - 1, $y, $z],//10
					[$x - 1, $y + 1, $z],
					[$x - 1, $y - 1, $z],
					[$x - 1, $y + 1, $z + 1],
					[$x - 1, $y - 1, $z + 1],
					[$x - 1, $y + 1, $z - 1],//15
					[$x - 1, $y - 1, $z - 1],
					[$x - 1, $y, $z + 1],
					[$x - 1, $y, $z - 1],
					[$x, $y + 1, $z],
					[$x, $y + 1, $z + 1],//20
					[$x, $y + 1, $z - 1],
					[$x, $y - 1, $z],
					[$x, $y - 1, $z + 1],
					[$x, $y - 1, $z - 1],
					[$x, $y, $z + 1],
					[$x, $y, $z - 1],
				];
				$rand = [8, 9, 17, 18];
				$key = $rand[array_rand($rand)];
				switch(mt_rand(1, 8)){
					case 1: $key2 = 4; break;
					case 2: $key2 = 5; break;
					case 3: $key2 = 6; break;
					case 4: $key2 = 7; break;
					case 5: $key2 = 13; break;
					case 6: $key2 = 14; break;
					case 7: $key2 = 15; break;
					case 8: $key2 = 16; break;
				}
				unset($pos_ar[$key], $pos_ar[$key2]);
				break;
			case 293:
				$pos_ar = [
					[$x, $y, $z],
					[$x + 1, $y, $z],
					[$x + 1, $y + 1, $z],
					[$x + 1, $y - 1, $z],
					[$x + 1, $y + 1, $z + 1],
					[$x + 1, $y - 1, $z + 1],//5
					[$x + 1, $y + 1, $z - 1],
					[$x + 1, $y - 1, $z - 1],
					[$x + 1, $y, $z + 1],
					[$x + 1, $y, $z - 1],
					[$x - 1, $y, $z],//10
					[$x - 1, $y + 1, $z],
					[$x - 1, $y - 1, $z],
					[$x - 1, $y + 1, $z + 1],
					[$x - 1, $y - 1, $z + 1],
					[$x - 1, $y + 1, $z - 1],//15
					[$x - 1, $y - 1, $z - 1],
					[$x - 1, $y, $z + 1],
					[$x - 1, $y, $z - 1],
					[$x, $y + 1, $z],
					[$x, $y + 1, $z + 1],//20
					[$x, $y + 1, $z - 1],
					[$x, $y - 1, $z],
					[$x, $y - 1, $z + 1],
					[$x, $y - 1, $z - 1],
					[$x, $y, $z + 1],
					[$x, $y, $z - 1],
				];
				$rand = [8, 9, 17, 18];
				$key = $rand[array_rand($rand)];
				switch(mt_rand(1, 8)){
					case 1: $key2 = 4; break;
					case 2: $key2 = 5; break;
					case 3: $key2 = 6; break;
					case 4: $key2 = 7; break;
					case 5: $key2 = 13; break;
					case 6: $key2 = 14; break;
					case 7: $key2 = 15; break;
					case 8: $key2 = 16; break;
				}
				unset($pos_ar[$key], $pos_ar[$key2]);
				break;
			case 256:
				$pos_ar = [
					[$x, $y, $z],
					[$x + 1, $y, $z],
					[$x - 1, $y, $z],
					[$x, $y, $z - 1],
					[$x + 1, $y, $z - 1],
					[$x - 1, $y, $z - 1],
					[$x, $y, $z + 1],
					[$x + 1, $y, $z + 1],
					[$x - 1, $y, $z + 1],
					[$x, $y + 1, $z],
					[$x + 1, $y + 1, $z],
					[$x - 1, $y + 1, $z],
					[$x, $y + 1, $z - 1],
					[$x + 1, $y + 1, $z - 1],
					[$x - 1, $y + 1, $z - 1],
					[$x, $y + 1, $z + 1],
					[$x + 1, $y + 1, $z + 1],
					[$x - 1, $y + 1, $z + 1],
				];
				$rand = [4, 5, 7, 8, 13, 14, 16, 17];
				$key = $rand[array_rand($rand)];
				unset($pos_ar[$key]);
				break;
			case 279:
				$pos_ar = [
					[$x, $y, $z],
					[$x + 1, $y, $z],
					[$x - 1, $y, $z],
					[$x, $y, $z - 1],
					[$x + 1, $y, $z - 1],
					[$x - 1, $y, $z - 1],
					[$x, $y, $z + 1],
					[$x + 1, $y, $z + 1],
					[$x - 1, $y, $z + 1],
					[$x, $y + 1, $z],
					[$x + 1, $y + 1, $z],
					[$x - 1, $y + 1, $z],
					[$x, $y + 1, $z - 1],
					[$x + 1, $y + 1, $z - 1],
					[$x - 1, $y + 1, $z - 1],
					[$x, $y + 1, $z + 1],
					[$x + 1, $y + 1, $z + 1],
					[$x - 1, $y + 1, $z + 1],
				];
				$rand = [4, 5, 7, 8, 13, 14, 16, 17];
				$key = $rand[array_rand($rand)];
				unset($pos_ar[$key]);
				break;
			case 294:
				$pos_ar = [
					[$x, $y, $z],
					[$x + 1, $y, $z],
					[$x + 1, $y + 1, $z],
					[$x + 1, $y - 1, $z],
					[$x + 1, $y + 1, $z + 1],
					[$x + 1, $y - 1, $z + 1],//5
					[$x + 1, $y + 1, $z - 1],
					[$x + 1, $y - 1, $z - 1],
					[$x + 1, $y, $z + 1],
					[$x + 1, $y, $z - 1],
					[$x - 1, $y, $z],//10
					[$x - 1, $y + 1, $z],
					[$x - 1, $y - 1, $z],
					[$x - 1, $y + 1, $z + 1],
					[$x - 1, $y - 1, $z + 1],
					[$x - 1, $y + 1, $z - 1],//15
					[$x - 1, $y - 1, $z - 1],
					[$x - 1, $y, $z + 1],
					[$x - 1, $y, $z - 1],
					[$x, $y + 1, $z],
					[$x, $y + 1, $z + 1],//20
					[$x, $y + 1, $z - 1],
					[$x, $y - 1, $z],
					[$x, $y - 1, $z + 1],
					[$x, $y - 1, $z - 1],
					[$x, $y, $z + 1],
					[$x, $y, $z - 1],
				];
				$rand = [4, 6, 8, 9, 13, 14, 16, 17, 18];
				$key = $rand[array_rand($rand)];
				switch(mt_rand(1,3)){
					case 1: $key2 = 5; break;
					case 2: $key2 = 7; break;
					case 3: $key2 = 15; break;
				}
				unset($pos_ar[$key], $pos_ar[$key2]);
				break;
			case 284:
				$pos_ar = [
					[$x, $y, $z],
					[$x + 1, $y, $z],
					[$x + 1, $y + 1, $z],
					[$x + 1, $y - 1, $z],
					[$x + 1, $y + 1, $z + 1],
					[$x + 1, $y - 1, $z + 1],//5
					[$x + 1, $y + 1, $z - 1],
					[$x + 1, $y - 1, $z - 1],
					[$x + 1, $y, $z + 1],
					[$x + 1, $y, $z - 1],
					[$x - 1, $y, $z],//10
					[$x - 1, $y + 1, $z],
					[$x - 1, $y - 1, $z],
					[$x - 1, $y + 1, $z + 1],
					[$x - 1, $y - 1, $z + 1],
					[$x - 1, $y + 1, $z - 1],//15
					[$x - 1, $y - 1, $z - 1],
					[$x - 1, $y, $z + 1],
					[$x - 1, $y, $z - 1],
					[$x, $y + 1, $z],
					[$x, $y + 1, $z + 1],//20
					[$x, $y + 1, $z - 1],
					[$x, $y - 1, $z],
					[$x, $y - 1, $z + 1],
					[$x, $y - 1, $z - 1],
					[$x, $y, $z + 1],
					[$x, $y, $z - 1],
				];
				$rand = [4, 6, 8, 9, 13, 14, 16, 17, 18];
				shuffle($rand);
				$key = $rand[0];
				$key2 = $rand[1];
				unset($pos_ar[$key], $pos_ar[$key2]);
				break;
			case 277:
				$pos_ar = [
					[$x, $y, $z],
					[$x + 1, $y, $z],
					[$x + 1, $y + 1, $z],
					[$x + 1, $y - 1, $z],
					[$x + 1, $y + 1, $z + 1],
					[$x + 1, $y - 1, $z + 1],//5
					[$x + 1, $y + 1, $z - 1],
					[$x + 1, $y - 1, $z - 1],
					[$x + 1, $y, $z + 1],
					[$x + 1, $y, $z - 1],
					[$x - 1, $y, $z],//10
					[$x - 1, $y + 1, $z],
					[$x - 1, $y - 1, $z],
					[$x - 1, $y + 1, $z + 1],
					[$x - 1, $y - 1, $z + 1],
					[$x - 1, $y + 1, $z - 1],//15
					[$x - 1, $y - 1, $z - 1],
					[$x - 1, $y, $z + 1],
					[$x - 1, $y, $z - 1],
					[$x, $y + 1, $z],
					[$x, $y + 1, $z + 1],//20
					[$x, $y + 1, $z - 1],
					[$x, $y - 1, $z],
					[$x, $y - 1, $z + 1],
					[$x, $y - 1, $z - 1],
					[$x, $y, $z + 1],
					[$x, $y, $z - 1],
				];
				$rand = [4, 6, 8, 9, 13, 14, 16, 17, 18];
				shuffle($rand);
				$key = $rand[0];
				$key2 = $rand[1];
				unset($pos_ar[$key], $pos_ar[$key2]);
				break;
			case 292:
				$pos_ar = [
					[$x, $y, $z],
					[$x + 1, $y, $z],
					[$x + 1, $y + 1, $z],
					[$x + 1, $y - 1, $z],
					[$x + 1, $y + 1, $z + 1],
					[$x + 1, $y - 1, $z + 1],//5
					[$x + 1, $y + 1, $z - 1],
					[$x + 1, $y - 1, $z - 1],
					[$x + 1, $y, $z + 1],
					[$x + 1, $y, $z - 1],
					[$x - 1, $y, $z],//10
					[$x - 1, $y + 1, $z],
					[$x - 1, $y - 1, $z],
					[$x - 1, $y + 1, $z + 1],
					[$x - 1, $y - 1, $z + 1],
					[$x - 1, $y + 1, $z - 1],//15
					[$x - 1, $y - 1, $z - 1],
					[$x - 1, $y, $z + 1],
					[$x - 1, $y, $z - 1],
					[$x, $y + 1, $z],
					[$x, $y + 1, $z + 1],//20
					[$x, $y + 1, $z - 1],
					[$x, $y - 1, $z],
					[$x, $y - 1, $z + 1],
					[$x, $y - 1, $z - 1],
					[$x, $y, $z + 1],
					[$x, $y, $z - 1],
				];
				$rand = [4, 6, 8, 9, 13, 14, 16, 17, 18];
				shuffle($rand);
				$key = $rand[0];
				$key2 = $rand[1];
				$key3 = $rand[2];
				$key4 = $rand[3];
				$key5 = $rand[4];
				unset($pos_ar[$key], $pos_ar[$key2], $pos_ar[$key3], $pos_ar[$key4], $pos_ar[$key5]);
				break;
			case 258:
			case 275:
			case 273:
			case 271:
			case 286:
				$pos_ar = [
					[$x, $y, $z],
					[$x + 1, $y, $z],
					[$x - 1, $y, $z],
					[$x, $y, $z - 1],
					[$x, $y, $z + 1],
					[$x, $y + 1, $z],
					[$x + 1, $y + 1, $z],
					[$x - 1, $y + 1, $z],
					[$x, $y + 1, $z - 1],
					[$x, $y + 1, $z + 1],
				];
				break;
			case 263:
				$pos_ar = [
					[$x, $y, $z],
					[$x + 1, $y, $z],
					[$x + 1, $y + 1, $z],
					[$x + 1, $y - 1, $z],
					[$x + 1, $y + 2, $z],
					[$x + 1, $y - 2, $z],
					[$x + 1, $y + 1, $z + 1],
					[$x + 1, $y - 1, $z + 1],
					[$x + 1, $y + 1, $z - 1],
					[$x + 1, $y - 1, $z - 1],
					[$x + 1, $y + 1, $z + 2],
					[$x + 1, $y - 1, $z + 2],
					[$x + 1, $y + 1, $z - 2],
					[$x + 1, $y - 1, $z - 2],
					[$x + 1, $y, $z + 1],
					[$x + 1, $y, $z - 1],
					[$x + 1, $y, $z + 2],
					[$x + 1, $y, $z - 2],
					[$x - 1, $y, $z],
					[$x - 1, $y + 1, $z],
					[$x - 1, $y - 1, $z],
					[$x - 1, $y + 2, $z],
					[$x - 1, $y - 2, $z],
					[$x - 1, $y + 1, $z + 1],
					[$x - 1, $y - 1, $z + 1],
					[$x - 1, $y + 1, $z - 1],
					[$x - 1, $y - 1, $z - 1],
					[$x - 1, $y + 1, $z + 2],
					[$x - 1, $y - 1, $z + 2],
					[$x - 1, $y + 1, $z - 2],
					[$x - 1, $y - 1, $z - 2],
					[$x - 1, $y, $z + 1],
					[$x - 1, $y, $z - 1],
					[$x - 1, $y, $z + 2],
					[$x - 1, $y, $z - 2],
					[$x + 2, $y, $z],
					[$x + 2, $y + 1, $z],
					[$x + 2, $y - 1, $z],
					[$x + 2, $y + 2, $z],
					[$x + 2, $y - 2, $z],
					[$x + 2, $y + 1, $z + 1],
					[$x + 2, $y - 1, $z + 1],
					[$x + 2, $y + 1, $z - 1],
					[$x + 2, $y - 1, $z - 1],
					[$x + 2, $y, $z + 1],
					[$x + 2, $y, $z - 1],
					[$x + 2, $y, $z + 2],
					[$x + 2, $y, $z - 2],
					[$x - 2, $y, $z],
					[$x - 2, $y + 1, $z],
					[$x - 2, $y - 1, $z],
					[$x - 2, $y + 2, $z],
					[$x - 2, $y - 2, $z],
					[$x - 2, $y + 1, $z + 1],
					[$x - 2, $y - 1, $z + 1],
					[$x - 2, $y + 1, $z - 1],
					[$x - 2, $y - 1, $z - 1],
					[$x - 2, $y, $z + 1],
					[$x - 2, $y, $z - 1],
					[$x, $y + 1, $z],
					[$x, $y + 1, $z + 1],
					[$x, $y + 1, $z - 1],
					[$x, $y + 1, $z + 2],
					[$x, $y + 1, $z - 2],
					[$x, $y - 1, $z],
					[$x, $y - 1, $z + 1],
					[$x, $y - 1, $z - 1],
					[$x, $y - 1, $z + 2],
					[$x, $y - 1, $z - 2],
					[$x, $y + 2, $z],
					[$x, $y + 2, $z + 1],
					[$x, $y + 2, $z - 1],
					[$x, $y - 2, $z],
					[$x, $y - 2, $z + 1],
					[$x, $y - 2, $z - 1],
					[$x, $y, $z + 1],
					[$x, $y, $z - 1],
					[$x, $y, $z + 2],
					[$x, $y, $z - 2]
				];
				/*$amount = ($playerData = Account::getInstance()->getData($user))->getInkConsumption();
				$playerData->consumeInk($amount);
				$this->main->sendInkAmount($player);*/
				$this->Explode(new Position($x, $y, $z, $level), 2.25, $player);
				break;
		}
		//$r = $this->changeWoolsColor($level, $pos_ar, $color, $user, true);
		$r = $this->changeWoolsColor($level, $pos_ar, $color, $user, false);
		unset($this->arrow[$player_type][$arrow]);
		//$this->main->sendInkAmount($player);
		return true;
	}

	public function CloseAllArrow(...$closeType){
		foreach($closeType as $type){
			if(isset($this->tnt_data[$type])){
				foreach($this->tnt_data[$type] as $eid => $data){
					if(isset($this->arrow[$type][$eid])){
						$pos = $this->arrow[$type][$eid][3];
						$pk = new RemoveEntityPacket;
						$pk->eid = $eid;
						$level = $data[0]->getLevel();
						$level->addChunkPacket($pos->x >> 4, $pos->z >> 4, $pk);
					}
				}
				unset($this->tnt_data[$type]);
			}
			if(isset($this->arrow[$type])){
				foreach($this->arrow[$type] as $eid => $data){
					$pk = new RemoveEntityPacket;
					$pk->eid = $eid;
					$player = $data[0];
					$level = $player->getLevel();
					if($data[1] !== 46){
						$item = ($data[1] === 261) ? Item::get(Item::ARROW) : Item::get(Item::SNOWBALL);
						$player = $data[0];
						$level = $player->getLevel();
						$particle_B = new ItemBreakParticle($data[3], $item);
						$level->addParticle($particle_B);
					}
					$level->addChunkPacket($data[3]->x >> 4, $data[3]->z >> 4, $pk);
				}
				unset($this->arrow[$type]);
			}
		}
	}

	public function startMoveTask(){
		$this->Task["Move"] = Server::getInstance()->getScheduler()->scheduleRepeatingTask(new Move($this), 1);
		return true;
	}

	public function stopMoveTask(...$type){
		//if(isset($this->Task["Move"])){
			//Server::getInstance()->getScheduler()->cancelTask($this->Task["Move"]->getTaskId());
			$this->CloseAllArrow(...$type);
			//unset($this->Task["Move"]);
		//}
	}
}

class SplatlingCharge extends Task{

	public function __construct($owner, Player $player, $hand_id, $tick, $sendPopup = true){
		$this->owner = $owner;
		$this->player = $player;
		$this->hand_id = $hand_id;
		$this->count = $tick;
		$this->maxcount = $tick;
		if($sendPopup && !$owner->main->spawnedSquid($player)){
			$owner->main->setSpeed($player, $owner->main->getSpeed($player, true) * 0.8);//移動速度を落とす(チャージ開始)
		}
		$this->sendPopup = $sendPopup;
	}

	public function getOwner(){
		return $this->owner;
	}

	public function onRun($tick){
		if($this->sendPopup && ($result = self::chargePopup($this->maxcount, $this->count))){
			$this->player->sendPopup($result);
		}
		if($this->count <= 0){
			//チャージ終了
			Server::getInstance()->getScheduler()->cancelTask($this->getOwner()->Task["SplatlingCharge"][$this->player->getName()]->getTaskId());
			unset($this->getOwner()->Task["SplatlingCharge"][$this->player->getName()]);
			$this->getOwner()->main->setSpeed($this->player, true);
			switch($this->hand_id){
				case 258:
					$this->getOwner()->HeavySplatling($this->player, $this->count, true);
					break;
				case 273:
					$this->getOwner()->L3_Nozzlenose($this->player, $this->count, true);
					break;
				case 271:
					$this->getOwner()->T3_Nozzlenose($this->player, $this->count, true);
					break;
				case 267:
					$this->getOwner()->LongBlaster($this->player, true);
					break;
				case 359:
					$this->getOwner()->LunaBlaster($this->player, true);
					break;
				case 272:
					$this->getOwner()->HotBlaster($this->player, true);
					break;
				case 268:
					$this->getOwner()->RapidBlaster($this->player, true);
					break;
				case 275:
					$this->getOwner()->MiniSplatling($this->player, $this->count, true);
					break;
				case 286:
					$this->getOwner()->HydraSplatling($this->player, $this->count, true);
					break;
			}
		}else{
			$isPlayer = $this->player instanceof Player;
			if(isset($this->getOwner()->Task["SplatlingCharge"][$this->player->getName()]) and ($this->player->getInventory() == null or $this->player->getInventory()->getItemInHand()->getID() !== $this->hand_id)){
				//アイテム持ち替えてたら
				Server::getInstance()->getScheduler()->cancelTask($this->getOwner()->Task["SplatlingCharge"][$this->player->getName()]->getTaskId());
				unset($this->getOwner()->Task["SplatlingCharge"][$this->player->getName()]);
				if($isPlayer && !$this->owner->main->spawnedSquid($this->player)){
					$this->getOwner()->main->setSpeed($this->player, true);
				}
				return false;
			}
			//if($this->count % 20 === 0 && $this->sendPopup) $this->player->sendPopup("§bwait for charging");
		}
		$this->count--;
	}

	public static function chargePopup($max, $now){
		if($max == $now) return "§8".str_pad("", 10 * 2, "-");
		$t = $max - $now;
		$d = $max;
		if(floor($t / $d * 100) % 10 === 0){
			$cnt = floor($t / $d * 100) / 10;
			if($cnt != 10){
				switch($cnt){
					case 0: case 1: $color = "§1"; break;
					case 2: case 3: $color = "§b"; break;
					case 4: case 5: $color = "§a"; break;
					case 6: case 7: $color = "§e"; break;
					case 8: case 9: $color = "§6"; break;
					default: $color = ""; break;
				}
				$txt = $color . str_pad("", $cnt * 2, "-")."§8".str_pad("", (10 - $cnt) * 2, "-");
			}else{
				$txt = "§c-------- §fMAX§c --------";
			}
			return $txt;
		}else{
			return false;
		}
	}
}

class SplatlingShot extends Task{

	public function __construct($owner, Player $player, $hand_id, $tick){
		$this->owner = $owner;
		$this->player = $player;
		$this->hand_id = $hand_id;
		$this->count = $tick;
	}

	public function getOwner(){
		return $this->owner;
	}

	public function onRun($tick){
		if($this->count === 0){
			Server::getInstance()->getScheduler()->cancelTask($this->getOwner()->Task["SplatlingShot"][$this->player->getName()]->getTaskId());
			unset($this->getOwner()->Task["SplatlingShot"][$this->player->getName()]);
		}else{
			switch($this->hand_id){
				case 258:
					$this->getOwner()->HeavySplatling($this->player, $this->count);
					break;
				case 273:
					$this->getOwner()->L3_Nozzlenose($this->player, $this->count);
					break;
				case 271:
					$this->getOwner()->T3_Nozzlenose($this->player, $this->count);
					break;
				case 267:
					$this->getOwner()->LongBlaster($this->player);
					break;
				case 359:
					$this->getOwner()->LunaBlaster($this->player);
					break;
				case 272:
					$this->getOwner()->HotBlaster($this->player);
					break;
				case 268:
					$this->getOwner()->RapidBlaster($this->player);
					break;
				case 275:
					$this->getOwner()->MiniSplatling($this->player, $this->count);
					break;
				case 286:
					$this->getOwner()->HydraSplatling($this->player, $this->count);
					break;
			}
		}
		$this->count--;
	}
}

class TNTExplode extends Task{

	public function __construct($owner, $eid, $type){
		$this->owner = $owner;
		$this->eid = $eid;
		$this->type = $type;
	}

	public function getOwner(){
		return $this->owner;
	}

	public function onRun($tick){
		$this->getOwner()->TNTExplode($this->eid, $this->type);
	}
}

class Move extends Task{

	public function __construct($owner){
		$this->owner = $owner;
	}

	public function getOwner(){
		return $this->owner;
	}

	public function onRun($tick){
		$this->getOwner()->AutoShot();
		$this->getOwner()->MoveAllArrow();
	}
}

/**
 * 爆発直前のサウンド
 */
class ExplosiveSound extends GenericSound{
	public function __construct(Vector3 $pos, $pitch = 0){
		parent::__construct($pos, 1005, $pitch);
	}
}

/**
 * 関数を一定時間後に実行するクラス
 * @param $owner 親クラス
 * @param $f 実行する関数
 * @param Array $array 渡す引数を配列にしたもの
 */
class lateDo extends Task{
	public function __construct($owner, $f, $array){
		$this->f = $f;
		$this->array = $array;
	}

	public function onRun($tick){
		$func = $this->f;
		$func($this->array);
	}
}


class Geyser extends Task{
	
	public function __construct($owner, $level, $block, $color, $player){
		$this->count = 0;
		$this->owner = $owner;
		$this->level = $level;
		$this->block = $block;
		$this->color = $color;
		$this->player = $player;
		$this->wool = Block::get(35, $color);
		$x = $block->x;
		$y = $block->y;
		$z = $block->z;
		$this->particle1 = new TerrainParticle(new Vector3($x+0.5, $y+1, $z+0.5), $this->wool);
		$this->particle2 = new TerrainParticle(new Vector3($x+0.5, $y+2, $z+0.5), $this->wool);
		$this->particle3 = new TerrainParticle(new Vector3($x+0.5, $y+3, $z+0.5), $this->wool);
		$this->particle4 = new TerrainParticle(new Vector3($x+0.5, $y+4, $z+0.5), $this->wool);
		$this->particle5 = new TerrainParticle(new Vector3($x+0.5, $y+5, $z+0.5), $this->wool);
		$this->pos_ar = [
		[$x-2, $y-1, $z-2],
		[$x-2, $y-1, $z-1],
		[$x-2, $y-1, $z],
		[$x-2, $y-1, $z+1],
		[$x-2, $y-1, $z+2],
		[$x-1, $y-1, $z-2],
		[$x-1, $y-1, $z-1],
		[$x-1, $y-1, $z],
		[$x-1, $y-1, $z+1],
		[$x-1, $y-1, $z+2],
		[$x, $y-1, $z-2],
		[$x, $y-1, $z-1],
		[$x, $y-1, $z],
		[$x, $y-1, $z+1],
		[$x, $y-1, $z+2],
		[$x+2, $y-1, $z-2],
		[$x+2, $y-1, $z-1],
		[$x+2, $y-1, $z],
		[$x+2, $y-1, $z+1],
		[$x+2, $y-1, $z+2],
		[$x+1, $y-1, $z-2],
		[$x+1, $y-1, $z-1],
		[$x+1, $y-1, $z],
		[$x+1, $y-1, $z+1],
		[$x+1, $y-1, $z+2],
		[$x-2, $y, $z-2],
		[$x-2, $y, $z-1],
		[$x-2, $y, $z],
		[$x-2, $y, $z+1],
		[$x-2, $y, $z+2],
		[$x-1, $y, $z-2],
		[$x-1, $y, $z-1],
		[$x-1, $y, $z],
		[$x-1, $y, $z+1],
		[$x-1, $y, $z+2],
		[$x, $y, $z-2],
		[$x, $y, $z-1],
		[$x, $y, $z],
		[$x, $y, $z+1],
		[$x, $y, $z+2],
		[$x+2, $y, $z-2],
		[$x+2, $y, $z-1],
		[$x+2, $y, $z],
		[$x+2, $y, $z+1],
		[$x+2, $y, $z+2],
		[$x+1, $y, $z-2],
		[$x+1, $y, $z-1],
		[$x+1, $y, $z],
		[$x+1, $y, $z+1],
		[$x+1, $y, $z+2]
		];
	}

	public function onRun($tick){
		$x = floor($this->block->x);
		$y = floor($this->block->y);
		$z = floor($this->block->z);
		$player = $this->player;
		for($i=0; $i < 3; $i++){ 
			$this->level->addParticle($this->particle1);
			$this->level->addParticle($this->particle2);
			$this->level->addParticle($this->particle3);
			$this->level->addParticle($this->particle4);
			$this->level->addParticle($this->particle5);
		}
		$array = $this->owner->getAllBattleMembers();
		foreach($array as $e){
			$en = Server::getInstance()->getPlayer($e);
			if((!$player instanceof Player) or (!$en instanceof Player)){
				continue;
			}
			$distance = sqrt(pow($x - $en->x, 2) + pow($z - $en->z, 2));
			if($distance <= 1.5){
				if($this->owner->main->canAttack($player->getName(), $en->getName())['result']){
					$dmg = 100;
					$en->attack($dmg, new EntityDamageByEntityEvent($player, $en, EntityDamageEvent::CAUSE_SUFFOCATION, $dmg, 0));
				}else{
					$my = 0.25*(5-abs($y-$player->y));
					if($my > 0){
						$en->setMotion(new Vector3(0, $my, 0));
						$user = $en->getName();
						$playerData = Account::getInstance()->getData($en);
						$playerData->fillInk();
					}
				}
			}
		}
		if($this->count%5 === 0 && ($player instanceof Player)){
			$this->owner->changeWoolsColor($this->level, $this->pos_ar, $this->color, $this->player->getName(), false);
		}
		if($this->count >= 34 || !($player instanceof Player)){
			$this->delete();
		}
		$this->count++;
	}

	public function delete(){
		Server::getInstance()->getScheduler()->cancelTask($this->getTaskId());
		unset($this->block->geyser);
	}
}

class sprinkler extends Task{

	public function __construct($owner, $player, $entity, $x, $y, $z, $face = 1){
		$this->owner = $owner;
		$this->player = $player;
		$this->entity = $entity;
		$this->face = $face;
		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$color = $playerData->getColor();
		$this->color = $color;
		$this->block = Block::get(35, $color);
		//$this->particle = new DestroyBlockParticle(new Vector3($this->entity->x, $this->entity->y+0.5, $this->entity->z), $this->block);
		$this->particle = new TerrainParticle(new Vector3($this->entity->x, $this->entity->y+0.5, $this->entity->z), $this->block);
		$this->sound = new LaunchSound(new Vector3($x, $y, $z), Weapon::SHOOTER_SOUND_PITCH);
		$this->pos = new Vector3($entity->x, $entity->y, $entity->z);
		$this->life = 3+(3*Gadget::getGadgetsCount($player, Gadget::BOMB_GUARD)); //耐久力
	}

	public function onRun($tick){
		$level = $this->player->getLevel();
		$x = $this->x;
		$y = $this->y;
		$z = $this->z;
		if($level->getBlockDataAt($x, $y, $z) !== $this->color){
			$this->owner->changeWoolsColor($level, [[$x, $y, $z]], $this->color, $this->player->getName(), false);
			$this->life--;
		}
		if($this->owner->main->canPaint($this->player) && $this->life > 0){
			switch($this->face){
				case 0://下向き
					$yaw = $this->entity->yaw;
					$this->entity->setRotation($yaw+197, 0);
					$pitch = -mt_rand(30, 60);
					$vel = mt_rand(25, 50)/100;
					$rad_y = $yaw/180*M_PI;
					$rad_p = $pitch/180*M_PI;
					$xx = sin($rad_y)*cos($rad_p);
					$yy = sin($rad_p);
					$zz = -cos($rad_y)*cos($rad_p);
					$ent = $this->owner->spawnEntity("Snowball", $level, $this->pos->x+$xx*1.5-0.5, $this->pos->y+$yy*1.5+0.5, $this->pos->z+$zz*1.5-0.5);
					$vec = new Vector3($xx*$vel, $yy*$vel, $zz*$vel);
					$ent->setMotion($vec);
				break;
				case 2:
					//-Z方向,yawは180
					$yaw = 90+180;
					$pitch = $this->entity->pitch;
					$this->entity->setRotation(180, $pitch+197);
					$vel = mt_rand(25, 50)/100;
					$rad_y = $yaw/180*M_PI;
					$rad_p = $pitch/180*M_PI;
					$xx = sin($rad_y)*cos($rad_p);
					$yy = sin($rad_p);
					$zz = -cos($rad_y)*cos($rad_p);
					$frad_y = 180/180*M_PI;
					$fx = -sin($frad_y);
					$fy = 0;
					$fz = cos($frad_y);
					$vecx = ($xx+$fx)*2/3;
					$vecy = ($yy+$fy)*2/3;
					$vecz = ($zz+$fz)*2/3;
					$ent = $this->owner->spawnEntity("Snowball", $level, $this->pos->x+$vecx*1.5-0.5, $this->pos->y+$vecy*1.5, $this->pos->z+$vecz*1.5-0.5);
					$vec = new Vector3($vecx*$vel, $vecy*$vel, $vecz*$vel);
					$ent->setMotion($vec);
				break;
				case 3:
					//-Z方向,yawは180
					$yaw = 90;
					$pitch = $this->entity->pitch;
					$this->entity->setRotation(0, $pitch+197);
					$vel = mt_rand(25, 50)/100;
					$rad_y = $yaw/180*M_PI;
					$rad_p = $pitch/180*M_PI;
					$xx = sin($rad_y)*cos($rad_p);
					$yy = sin($rad_p);
					$zz = -cos($rad_y)*cos($rad_p);
					$frad_y = 0/180*M_PI;
					$fx = -sin($frad_y);
					$fy = 0;
					$fz = cos($frad_y);
					$vecx = ($xx+$fx)*2/3;
					$vecy = ($yy+$fy)*2/3;
					$vecz = ($zz+$fz)*2/3;
					$ent = $this->owner->spawnEntity("Snowball", $level, $this->pos->x+$vecx*1.5-0.5, $this->pos->y+$vecy*1.5, $this->pos->z+$vecz*1.5-0.5);
					$vec = new Vector3($vecx*$vel, $vecy*$vel, $vecz*$vel);
					$ent->setMotion($vec);
				break;
				case 4:
					//-Z方向,yawは180
					$yaw = 90+90;
					$pitch = $this->entity->pitch;
					$this->entity->setRotation(90, $pitch+197);
					$vel = mt_rand(25, 50)/100;
					$rad_y = $yaw/180*M_PI;
					$rad_p = $pitch/180*M_PI;
					$xx = sin($rad_y)*cos($rad_p);
					$yy = sin($rad_p);
					$zz = -cos($rad_y)*cos($rad_p);
					$frad_y = 90/180*M_PI;
					$fx = -sin($frad_y);
					$fy = 0;
					$fz = cos($frad_y);
					$vecx = ($xx+$fx)*2/3;
					$vecy = ($yy+$fy)*2/3;
					$vecz = ($zz+$fz)*2/3;
					$ent = $this->owner->spawnEntity("Snowball", $level, $this->pos->x+$vecx*1.5-0.5, $this->pos->y+$vecy*1.5, $this->pos->z+$vecz*1.5-0.5);
					$vec = new Vector3($vecx*$vel, $vecy*$vel, $vecz*$vel);
					$ent->setMotion($vec);
				break;
				case 5:
					//-Z方向,yawは180
					$yaw = 270+90;
					$pitch = $this->entity->pitch;
					$this->entity->setRotation(270, $pitch+197);
					$vel = mt_rand(25, 50)/100;
					$rad_y = $yaw/180*M_PI;
					$rad_p = $pitch/180*M_PI;
					$xx = sin($rad_y)*cos($rad_p);
					$yy = sin($rad_p);
					$zz = -cos($rad_y)*cos($rad_p);
					$frad_y = 270/180*M_PI;
					$fx = -sin($frad_y);
					$fy = 0;
					$fz = cos($frad_y);
					$vecx = ($xx+$fx)*2/3;
					$vecy = ($yy+$fy)*2/3;
					$vecz = ($zz+$fz)*2/3;
					$ent = $this->owner->spawnEntity("Snowball", $level, $this->pos->x+$vecx*1.5-0.5, $this->pos->y+$vecy*1.5, $this->pos->z+$vecz*1.5-0.5);
					$vec = new Vector3($vecx*$vel, $vecy*$vel, $vecz*$vel);
					$ent->setMotion($vec);
				break;

				default ://1も兼ねる　上向き
					$yaw = $this->entity->yaw;
					$this->entity->setRotation($yaw+197, 0);
					$pitch = mt_rand(30, 60);
					$vel = mt_rand(25, 50)/100;
					$rad_y = $yaw/180*M_PI;
					$rad_p = $pitch/180*M_PI;
					$xx = sin($rad_y)*cos($rad_p);
					$yy = sin($rad_p);
					$zz = -cos($rad_y)*cos($rad_p);
					$ent = $this->owner->spawnEntity("Snowball", $level, $this->pos->x+$xx*1.5-0.5, $this->pos->y+$yy*1.5, $this->pos->z+$zz*1.5-0.5);
					$vec = new Vector3($xx*$vel, $yy*$vel, $zz*$vel);
					$ent->setMotion($vec);
				break;
			}
			$ent->ink = $this->block;
			$ent->level = $level;
			$ent->player = $this->player;
			for($i = 0; $i < 4; $i++){
				$level->addParticle($this->particle);
			}
			$level->addSound($this->sound);
			$this->entity->teleport($this->pos);
		}else{
			$this->deleteSprinkler();
		}
	}

	public function deleteSprinkler(){
		Server::getInstance()->getScheduler()->cancelTask($this->owner->Task["Sprinkler"][$this->player->getName()]->getTaskId());
		$this->owner->Task["Sprinkler"][$this->player->getName()]->entity->close();
		unset($this->owner->Task["Sprinkler"][$this->player->getName()]);
	}
}

class trap extends Task{

	public function __construct($owner, $player, $x, $y, $z, $block, $team){
		$this->owner = $owner;
		$this->player = $player;
		$this->block = $block;
		$this->team = $team;
		$this->pos = new Vector3($x, $y, $z);
		$this->particle = new DestroyBlockParticle(new Vector3($x, $y+1, $z), $block);
		$this->level = $player->getLevel();
		$this->count = -10*Gadget::getGadgetsCount($player, Gadget::BOMB_GUARD);
	}

	public function onRun($tick){
		$this->level->addParticle($this->particle, $this->team);
		$this->count += 1;
		if($this->level->getBlock($this->pos)->getDamage() != $this->block->getDamage() || $this->count == 60){
			
			$F = function($array){

			$array[0]->addParticle($array[1]);
			};

			$F_2 = function($array){

				$array[0]->bomb($array[1], $array[2], $array[3], $array[4], $array[5], $array[6], $array[7], $array[8], $array[9], $array[10]);
			};
			Server::getInstance()->getScheduler()->scheduleDelayedTask(new lateDo($this->owner, $F, [$this->level, $this->particle]),5);
			Server::getInstance()->getScheduler()->scheduleDelayedTask(new lateDo($this->owner, $F, [$this->level, $this->particle]),10);
			Server::getInstance()->getScheduler()->scheduleDelayedTask(new lateDo($this->owner, $F, [$this->level, $this->particle]),15);
			Server::getInstance()->getScheduler()->scheduleDelayedTask(new lateDo($this->owner, $F_2, [$this->owner, $this->pos, $this->player, $this->pos->x, $this->pos->y, $this->pos->z, $this->block, 3.5, 45, $this->owner->getAllBattleMembers(), 7]),20);
			Server::getInstance()->getScheduler()->cancelTask($this->owner->Task["Trap"][$this->player->getName()]->getTaskId());
		}
	}

	public function deleteTrap(){
		Server::getInstance()->getScheduler()->cancelTask($this->owner->Task["Trap"][$this->player->getName()]->getTaskId());
		unset($this->owner->Task["Trap"][$this->player->getName()]);
	}
}

class Acid extends Task{

	public function __construct($owner, $attacker, $player, $count = 20){
		$this->owner = $owner;
		$this->player = $player;
		$this->attacker = $attacker;
		$this->count = $count;
		$this->level = $player->getLevel();
	}

	public function onRun($tick){
		if($this->owner->main->canPaint($this->player) && $this->count-- >= 0){
			$x = floor($this->player->x);
			$y = floor($this->player->y);
			$z = floor($this->player->z);
			$user = $this->attacker->getName();
			$playerData = Account::getInstance()->getData($user);
			$color = $playerData->getColor();
			$this->level->addParticle(new DestroyBlockParticle($this->player, Block::get(35, $color)));
/*			$pos_ar = [
				[$x-1, $y-2, $z-1],
				[$x-1, $y-2, $z],
				[$x-1, $y-1, $z-1],
				[$x-1, $y-1, $z],
				[$x-1, $y-1, $z+1],
				[$x-1, $y, $z-1],
				[$x-1, $y, $z],
				[$x-1, $y, $z+1],
				[$x, $y-2, $z-1],
				[$x, $y-2, $z],
				[$x, $y-2, $z+1],
				[$x, $y-1, $z-1],
				[$x, $y-1, $z],
				[$x, $y-1, $z+1],
				[$x, $y, $z-1],
				[$x, $y, $z],
				[$x, $y, $z+1],
				[$x+1, $y-2, $z-1],
				[$x+1, $y-2, $z],
				[$x+1, $y-2, $z+1],
				[$x+1, $y-1, $z-1],
				[$x+1, $y-1, $z],
				[$x+1, $y-1, $z+1],
				[$x+1, $y, $z-1],
				[$x+1, $y, $z],
				[$x+1, $y, $z+1],
			];*/
			$pos_ar = [
				[$x-1, $y-2, $z],
				[$x-1, $y-1, $z],
				[$x-1, $y, $z],
				[$x, $y-2, $z-1],
				[$x, $y-2, $z],
				[$x, $y-2, $z+1],
				[$x, $y-1, $z-1],
				[$x, $y-1, $z],
				[$x, $y-1, $z+1],
				[$x, $y, $z-1],
				[$x, $y, $z],
				[$x, $y, $z+1],
				[$x+1, $y-2, $z],
				[$x+1, $y-1, $z],
				[$x+1, $y, $z],
			];
			$this->owner->changeWoolsColor($this->level, $pos_ar, $color, $user, false);
		}else{
			if(isset($this->owner->Task["Acid"][$this->player->getName()])){
				Server::getInstance()->getScheduler()->cancelTask($this->owner->Task["Acid"][$this->player->getName()]->getTaskId());
				unset($this->owner->Task["Acid"][$this->player->getName()]);
			}
		}
	}
}

/**
* 
*/
class BlasterBullet extends Task{
	
	public function __construct($owner, $player, $vx, $vy, $vz, $weapon){
		$this->w = $owner;
		$this->x = $player->x;
		$this->y = $player->y+1.5;
		$this->z = $player->z;
		$this->vx = $vx;
		$this->vy = $vy;
		$this->vz = $vz;
		$this->player = $player;
		$this->range = $owner->getFiringRange($weapon);
		$this->power = $owner->getAttackDamage($weapon);
		$this->item = $owner->getWeaponItemId($weapon)[0];
		$weapondata = $owner->getWeaponData($weapon);
		$this->radius = $weapondata['bomb_radius'];
		$this->paint = $weapondata['bomb_paint'];
		$this->percent = $weapondata['bomb_damageper'];
		$this->speed = $weapondata['speed'];
		$this->count = 0;
	}

	public function onRun($tick){
		$this->count += $this->speed;
		$player = $this->player;
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$color = $playerData->getColor();
		$block = Block::get(35, $color);
		$level = $player->getLevel();
		$p = $this->getPos();
		$x = $p[0];
		$y = $p[1];
		$z = $p[2];
		$vec = new Vector3($p[0], $p[1], $p[2]);
		$level->addParticle(new TerrainParticle($vec, $block));
		$level->addParticle(new CriticalParticle($vec, 1));
		$hit = false;
		$array = $this->w->getAllBattleMembers();
		foreach($array as $e){
			$en = Server::getInstance()->getPlayer($e);
			if((!$player instanceof Player) or (!$en instanceof Player)){
				continue;
			}
			if($this->w->main->canAttack($player->getName(), $en->getName())['result']){
				$distance = sqrt(pow($x - $en->x, 2) + pow($y - $en->y-1.5, 2) + pow($z - $en->z, 2));

				if($distance <= 1.3){
					$hit = $en;
					break;
				}
			}
		}

		if($hit === false){
			$np = $this->getPos();
			if($this->count >= $this->range){
				$this->end(2);
			}else if(!$this->w->canThrough( $level->getBlockIdAt(floor($np[0]), floor($np[1]), floor($np[2])) )){
				$this->end(1);
			}
		}else{
			$dmg = $this->power;
			$en->attack($dmg, new EntityDamageByEntityEvent($player, $hit, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $dmg, 0));
			$as = new AnvilFallSound($player);
			$level->addSound($as, [$player]);
			$this->end(0);
		}
	}

	public function getPos(){
		return [$this->x+$this->vx*$this->count, $this->y+$this->vy*$this->count, $this->z+$this->vz*$this->count];
	}

	public function getNextPos(){
		return [$this->x+$this->vx*($this->count+1), $this->y+$this->vy*($this->count+1), $this->z+$this->vz*($this->count+1)];
	}

	public function end($type){
		$player = $this->player;
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$color = $playerData->getColor();
		$level = $player->getLevel();
		switch($type){
			//hit => 0
			case 1:
				//break
				$this->explode(0.5);
				break;
			case 2:
				//range
				$this->explode(1);
			break;
		}
		$p = $this->getPos();
		$this->w->orbitPaint($player, new Vector3($p[0], $p[1], $p[2]), $this->item, $level, $color, $user);
		Server::getInstance()->getScheduler()->cancelTask($this->getTaskId());	
	}

	public function explode($percent = 1){
		$p = $this->getPos();
		$x = $p[0];
		$y = $p[1];
		$z = $p[2];
		$player = $this->player;
		$array = $this->w->getAllBattleMembers();
		$paint = $this->paint*$percent;
		$radius = $this->radius*$percent;
		$power = $this->power*$percent*$this->percent;
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$color = $playerData->getColor();
		$block = Block::get(35, $color);
		$level = $player->getLevel();
		$radius_1 = $radius/2; //球の半径
		$radius_3 = $radius; //球の半径
		$pos_ar = [];
		$entity = new Vector3($x, $y, $z);
		
		$F = function($array){

			$array[0]->addParticle($array[1]);
		};
		
		$p = new Vector3($x, $y, $z);
		$particle_1 = new DestroyBlockParticle($p, $block);
		$level->addParticle($particle_1);

		for($xxx = -floor($paint/2); $xxx < ceil($paint/2); $xxx++){

			for($yyy = -floor($paint); $yyy < ceil($paint); $yyy++){ 
			
				for($zzz = -floor($paint/2); $zzz < ceil($paint/2); $zzz++){ 
				
					//$pos = new Vector3(floor($xxx+$x), floor($yyy+$y), floor($zzz+$z));
					
					if($level->getBlockIdAt(floor($xxx+$x), floor($yyy+$y), floor($zzz+$z)) !== 0){
						
						//塗り
						//$level->setBlock($pos, $block);
						$pos_ar[] = [floor($xxx+$x), floor($yyy+$y), floor($zzz+$z)];
					}
				}
			}			
		}
		$result = $this->w->changeWoolsColor($level, $pos_ar, $color, $user);

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
			}
		}

		for($yaw = 0; $yaw < 360; $yaw += 360/(M_PI*$radius_3)){

			for($pitch = 0; $pitch <360; $pitch += 360/(M_PI*$radius_3)){

				$rad_y = $yaw/180*M_PI;
				$rad_p = ($pitch-180)/180*M_PI;
				$xx = sin($rad_y)*cos($rad_p);
				$yy = sin($rad_p);
				$zz = -cos($rad_y)*cos($rad_p);
				$p->x = $x+$xx*$radius_3;
				$p->y = $y+$yy*$radius_3;
				$p->z = $z+$zz*$radius_3;
				$particle_3 = new TerrainParticle($p, $block);
				Server::getInstance()->getScheduler()->scheduleDelayedTask(new LateDo($this->w, $F, [$level, $particle_3]),3);
			}
		}
		foreach($array as $e){
			$en = Server::getInstance()->getPlayer($e);
			if((!$player instanceof Player) or (!$en instanceof Player)){
				continue;
			}
			if($this->w->main->canAttack($player->getName(), $en->getName())['result']){
				$distance = pow($x - $en->x, 2) + pow($y - $en->y-1.5, 2) + pow($z - $en->z, 2);

				if($distance <= pow($radius, 2)){
					if($this->w->canAttack($entity, $en)){
						$dmg = floor($power);
						$en->attack($dmg, new EntityDamageByEntityEvent($player, $en, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $dmg, 0));
					}
				}
			}
		}
		return $result;
	}
}

/**
* 
*/
class ShooterParticle extends Task{
	
	public function __construct($player, $vx, $vy, $vz, $range, $color){
		$newpos = $player->getNextPosition();
		$this->x = $newpos->x;
		$this->y = $newpos->y+1.5;
		$this->z = $newpos->z;
		$this->level = $player->getLevel();
		$this->vx = $vx;
		$this->vy = $vy;
		$this->vz = $vz;
		$this->block = Block::get(35, $color);
		$this->range = $range;
		$this->count = 0;
	}

	public function onRun($tick){
		if($this->count < $this->range){
			$this->count += 2;
			$pos = new Vector3($this->x+$this->vx*$this->count, $this->y+$this->vy*$this->count, $this->z+$this->vz*$this->count);
			$particle1 = new TerrainParticle($pos, $this->block);
			$particle2 = new CriticalParticle($pos, 1);
			$this->level->addParticle($particle1);
			$this->level->addParticle($particle2);
		}else{
			Server::getInstance()->getScheduler()->cancelTask($this->getTaskId());
		}
	}
}

class ChaseBomb extends Silverfish{

	public function __construct($level, $nbt, $player, $main, $target = false){
		parent::__construct($level, $nbt);
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$this->main = $main;
		$this->player = $player;
		$this->color = $playerData->getColor();
		$this->speed = 0.8;
		$this->chase = 4+Gadget::getGadgetsCount($player, Gadget::BOMB_THROW);
		$this->yaw = $player->yaw;
		$this->count = 0;

		$this->enemys = $main->w->getAllBattleMembers();

		if($target){
			$this->target = $this->searchTarget();
		}else{
			$this->target = false;
		}
		if($this->target === false) $this->speed *= 1.1;
		return $this;
	}

	/**
	 * ロックオンする対象を探す
	 * 返り値 Player or bool
	 */
	public function searchTarget(){
		$player = $this->player;
		$x = $player->x;
		$y = $player->y;
		$z = $player->z;
		$target = false;
		$disq = 1000;
		foreach($this->enemys as $e){
			$en = Server::getInstance()->getPlayer($e);
			if((!$player instanceof Player) or (!$en instanceof Player)){
				continue;
			}
			$distance_sq = pow($x - $en->x, 2) + pow($z - $en->z, 2);

			if($this->main->canAttack($player->getName(), $e)['result'] && $this->main->w->canLook($en) && abs(self::getYawDifference($this, $en)) && $distance_sq <= $disq){
				$target = $en;
				$disq = $distance_sq;
			}
		}
		return $target;
	}

	public static function getYawDifference($entity, $target){
/*		$Ax = $entity->x;
		$Az = $entity->z;
		$Bx = $entity->x-sin(deg2rad($entity->yaw)*100);
		$Bz = $entity->z+cos(deg2rad($entity->yaw)*100);
		$Cx = $target->x;
		$Cz = $target->z;
		$p = ($Bx-$Ax)*($Cz-$Az)-($Bz-$Az)*($Cx-$Ax);
		return $p;
*/
		$x1 = $entity->x;
		$z1 = $entity->z;
		$x2 = $target->x;
		$z2 = $target->z;
		$yaw = atan(($x2-$x1)/(-$z2+$z1))*180/M_PI;

		if((-$z2+$z1)/abs(-$z2+$z1) == 1){

			$yaw = $yaw+180;
		}
		return (($yaw%180)-($entity->yaw%180));
	}

	/**
	 * 曲がる方向を取得
	 *　負の方向なら-1,正の方向なら+1,真っ直ぐなら0を返す
	 */
	public static function getCurve($entity, $target){
		$x1 = $entity->x;
		$z1 = $entity->z;
		$x2 = $target->x;
		$z2 = $target->z;
		$rad_p = deg2rad($entity->yaw+6);
		$rad_m = deg2rad($entity->yaw-6);
		$xx1p = $x1-sin($rad_p);
		$zz1p = $z1+cos($rad_p);
		$xx1m = $x1-sin($rad_m);
		$zz1m = $z1+cos($rad_m);
		$disq_p = pow($xx1p-$x2, 2)+pow($zz1p-$z2, 2);
		$disq_m = pow($xx1m-$x2, 2)+pow($zz1m-$z2, 2);
		if($disq_p < $disq_m){
			return 1;
		}elseif ($disq_m < $disq_p) {
			return -1;
		}
		return 0;
	}

	public function isExplode(){
		if($this->count === 140){
			return true;
		}
		$player = $this->player;
		$x = floor($this->x) + 0.5;
		$y = round($this->y);
		$z = floor($this->z) + 0.5;
		$dir = [0 => 270, 1 => 360, 2 => 90, 3 => 180];
		$yaw = $dir[$this->getDirection()];
		$Yaw_rad = deg2rad($yaw);
		$velX = -1 * sin($Yaw_rad);
		$velZ = cos($Yaw_rad);
		$x = floor($x + $velX);
		$z = floor($z + $velZ);
		if($player->getLevel()->getBlockIdAt($x, $y, $z) !== 0){
			return true;
		}
		foreach($this->enemys as $e){
			$en = Server::getInstance()->getPlayer($e);
			if((!$player instanceof Player) or (!$en instanceof Player)){
				continue;
			}
			$distance_sq = pow($x - $en->x, 2) + pow($z - $en->z, 2);

			if($this->main->canAttack($player->getName(), $e)['result'] && $distance_sq <= 1){
				return true;
			}
		}
		return false;
	}

	public function onUpdate($tick){
		$x = $this->x;
		$y = $this->y;
		$z = $this->z;
		if($this->isExplode()){
			$this->main->w->bomb($this, $this->player, $x, $y, $z, Block::get(35, $this->color), 2.5, 40, $this->enemys, 5);
			$this->close();
			return true;
		}
		$this->count++;
		$speed = $this->speed;
		if($this->onGround){
			$pos_ar = [[floor($x), floor($y-1), floor($z)]];
			$this->main->w->changeWoolsColor($this->player->getLevel(), $pos_ar, $this->color, $this->player->getName(), false);
			if($this->target !== false){
				$yawdif = self::getCurve($this, $this->target);
				if($yawdif < 0){
					$this->yaw -= $this->chase;
				}else if($yawdif > 0){
					$this->yaw += $this->chase;
				}
			}else{
				$speed *= 1.1;
			}
		}
		$rad = deg2rad($this->yaw);
		$vx = -sin($rad)*$speed;
		$vz = cos($rad)*$speed;
		$this->motionX = $vx;
		$this->motionZ = $vz;
		$this->player->getLevel()->addParticle(new TerrainParticle($this, Block::get(35, $this->color)));
		parent::onUpdate($tick);
	}
}

class BombEntity extends Egg{

	public function onUpdate($currentTick){
		if($this->closed){
			return false;
		}


		$tickDiff = $currentTick - $this->lastUpdate;
		if($tickDiff <= 0 and !$this->justCreated){
			return true;
		}
		$this->lastUpdate = $currentTick;

		$hasUpdate = $this->entityBaseTick($tickDiff);

		if($this->isAlive()){

			$movingObjectPosition = null;

			if(!$this->isCollided){
				$this->motionY -= $this->gravity;
			}

			$moveVector = new Vector3($this->x + $this->motionX, $this->y + $this->motionY, $this->z + $this->motionZ);

			$list = $this->getLevel()->getCollidingEntities($this->boundingBox->addCoord($this->motionX, $this->motionY, $this->motionZ)->expand(1, 1, 1), $this);

			$nearDistance = PHP_INT_MAX;
			$nearEntity = null;

			foreach($list as $entity){
				if(/*!$entity->canCollideWith($this) or */
				($entity === $this->shootingEntity and $this->ticksLived < 5)
				){
					continue;
				}

				$axisalignedbb = $entity->boundingBox->grow(0.3, 0.3, 0.3);
				$ob = $axisalignedbb->calculateIntercept($this, $moveVector);

				if($ob === null){
					continue;
				}

				$distance = $this->distanceSquared($ob->hitVector);

				if($distance < $nearDistance){
					$nearDistance = $distance;
					$nearEntity = $entity;
				}
			}

			if($nearEntity !== null){
				$movingObjectPosition = MovingObjectPosition::fromEntity($nearEntity);
			}

			if($movingObjectPosition !== null){
				if($movingObjectPosition->entityHit !== null && $this->shootingEntity->getName() !== $movingObjectPosition->entityHit->getName() && ($this->main->canAttack($this->shootingEntity->getName(), $movingObjectPosition->entityHit->getName())['result'] || Enemy::isEnemy($movingObjectPosition->entityHit))){

					$this->server->getPluginManager()->callEvent(new ProjectileHitEvent($this));

					$motion = sqrt($this->motionX ** 2 + $this->motionY ** 2 + $this->motionZ ** 2);
					$damage = ceil($motion * $this->damage);

					if($this instanceof Arrow and $this->isCritical()){
						$damage += mt_rand(0, (int) ($damage / 2) + 1);
					}

/*					if($this->shootingEntity === null){
						$ev = new EntityDamageByEntityEvent($this, $movingObjectPosition->entityHit, EntityDamageEvent::CAUSE_PROJECTILE, $damage);
					}else{
						$ev = new EntityDamageByChildEntityEvent($this->shootingEntity, $this, $movingObjectPosition->entityHit, EntityDamageEvent::CAUSE_PROJECTILE, $damage);
					}

					if($movingObjectPosition->entityHit->attack($ev->getFinalDamage(), $ev) === true){
						if($this instanceof Arrow and $this->getPotionId() != 0){
							foreach(Potion::getEffectsById($this->getPotionId() - 1) as $effect){
								$movingObjectPosition->entityHit->addEffect($effect->setDuration($effect->getDuration() / 8));
							}
						}
						$ev->useArmors();
					}*/

					$this->hadCollision = true;

					if($this->fireTicks > 0){
						$ev = new EntityCombustByEntityEvent($this, $movingObjectPosition->entityHit, 5);
						$this->server->getPluginManager()->callEvent($ev);
						if(!$ev->isCancelled()){
							$movingObjectPosition->entityHit->setOnFire($ev->getDuration());
						}
					}

					$this->kill();
					return true;
				}
			}

			$this->move($this->motionX, $this->motionY, $this->motionZ);

			if($this->isCollided and !$this->hadCollision){
				$this->hadCollision = true;

				$this->motionX = 0;
				$this->motionY = 0;
				$this->motionZ = 0;

				$this->server->getPluginManager()->callEvent(new ProjectileHitEvent($this));
			}elseif(!$this->isCollided and $this->hadCollision){
				$this->hadCollision = false;
			}

			if(!$this->onGround or abs($this->motionX) > 0.00001 or abs($this->motionY) > 0.00001 or abs($this->motionZ) > 0.00001){
				$f = sqrt(($this->motionX ** 2) + ($this->motionZ ** 2));
				$this->yaw = (atan2($this->motionX, $this->motionZ) * 180 / M_PI);
				$this->pitch = (atan2($this->motionY, $f) * 180 / M_PI);
				$hasUpdate = true;
			}

			$this->updateMovement();

		}

		return $hasUpdate;
	}
}