<?php

use Gear;

trait GearManager{

	private $gearData,$equipment,$list;

	public function setGearData($gearData, $equipment){
		$this->gearData = $gearData;
		$this->equipment = $equipment;
		$this->calculate();
	}

	public function setGearHead($gearNum){
		$this->equipment[0] = $gearNum;
	}

	public function setGearClothes($gearNum){
		$this->equipment[1] = $gearNum;
	}

	public function setGearBoots($gearNum){
		$this->equipment[2] = $gearNum;
	}
/*
	$gearData = [
		1:[
			1 => 10,
			2 => 3,
			4 => 6
		],
		3:[
			2 => 10,
			4 => 9,
		],
		ギア番号:[
			効果番号 => 最大を10としたときの数値(効果倍数)
			効果番号 => 最大を10としたときの数値(効果倍数)
		]
		//各ギアで、効果倍数の合計は19まで
	];
	$equipment =　[1,5,6];
	//各ギア番号、合計3まで装備可能
*/

	public function calculate(){
		$list = [];
		foreach($this->equipment as $key => $gearNum){
			if(isset($this->gearData[$gearNum])){
				foreach($this->gearData[$gearNum] as $effNo => $num){
					$list[$effNo] = isset($list[$effno]) ? 
						($list[$effNo] + $num) * 0.85 :
						$num;
				}
			}
		}
		$this->list = $list;
	}

/*
	$playerData側での計算式
	向こう側では掛け算するだけ。

	1,
	$atk = もともとの攻撃値;
	$atk = $atk * $playerData->getAttackUp();
	//効果倍数が攻撃に19ふってある場合の最大は19*0.1で威力1.19倍

	$inkAmount = もともとの消費インク
	$inkAmount = $inkAmount * $playerData->getInkEffectionMain();
	}
*/

	public function getAttackUp(){
		return isset($this->list[Gear::ATTACK_UP]) ? 1 + $this->list[Gear::ATTACK_UP] * 0.01 : 1;
	}

	public function getDefenceUp(){
		return isset($this->list[Gear::DEFENCE_UP]) ? 1 + $this->list[Gear::DEFENCE_UP] * 0.01 : 1;
	}

	public function getInkEffectionMain(){
		return isset($this->list[Gear::INK_EFF_MAIN]) ? 1 - $this->list[Gear::INK_EFF_MAIN] * 0.01 : 1;
	}

	public function getInkEffectionSub(){
		return isset($this->list[Gear::INK_EFF_SUB]) ? 1 - $this->list[Gear::INK_EFF_SUB] * 0.01 : 1;
	}

	public function getHealUp(){
		return isset($this->list[Gear::INK_HEALUP]) ? 1 + $this->list[Gear::INK_HEALUP] * 0.01 : 1;
	}

	public function getMoveUpSteve(){
		return isset($this->list[Gear::MOVE_UP_STEVE]) ? 1 + $this->list[Gear::MOVE_UP_STEVE] * 0.01 : 1;
	}

	public function getMoveUpSquid(){
		return isset($this->list[Gear::MOVE_UP_SQUID]) ? 1 + $this->list[Gear::MOVE_UP_SQUID] * 0.01 : 1;
	}

	//残りがだるかった
}

class Gear{

	const ATTACK_UP　= 1;
	const DEFENCE_UP = 2;
	const INK_EFF_MAIN = 3;
	const INK_EFF_SUB = 4;//未
	const INK_HEALUP = 5;
	const MOVE_UP_STEVE = 6;
	const MOVE_UP_SQUID　= 7;
	const SPECIAL_UP = 8;//未
	const SPECIAL_DOWN = 9;//未
	const SPECIAL_DURATION = 10;//未
	const RESPAWN_SHORTEN = 11;
	const SUPERJUMP_SHORTEN = 12;
	const LEAPBETTER = 13;
	const ADVERSITY = 14;
	const COMEBACK = 15;

	//http://wikiwiki.jp/splatoon2ch/?%A5%AE%A5%A2%A5%D1%A5%EF%A1%BC%CA%CC%A5%EA%A5%B9%A5%C8
	private static $nameList = [
		//メイン攻撃
		1 => "レモンスカッシュバンド",
		2 => "サブマリンゴーグル",
		//メイン守備
		3 => "タコメッシュ",
		4 => "スイミングゴーグル",
		5 => "チャキリング帽",
		//インク効率メイン
		6 => "ショートビーニー",
		7 => "スゲースゲ",
		8 => "スタジオヘッドホン",
		9 => "サファリハット",
		//インク効率アップサブ
		//なし
		//インク回復アップ
		10 => "スズベルハット",
		11 => "ウメスカッシュバンド",
		11 => "トレジャーメット",
		12 => "サイクルメット",
		//移動速度ヒト
		13 => "サンサンサンバイザー",
		14 => "ラグビーメット",
		//移動速度イカ
		15 => "キャンプキャップ",
		16 => "カモメッシュ",
		17 => "ナイトビジョン",
		//スペシャルアップ(打った時にたまるスペシャルゲージが増える)
		//なし
		//スペシャルダウン(死んでもスペシャルゲージが減りにくく)
		//なし
		//スペシャル延長
		//なし
		//復活短縮(復活するまでの時間が短く)
		18 => "クロブチレトロ",
		19 => "バックワードキャップ",
		//スパジャン短縮(羽を使ってリス地に戻るまでの時間が短く)

		//ボム飛距離アップ

		//逆境強化(味方の人数<敵の人数 の時、攻撃力すごく上がる。メインにしかつかない)

		//カムバック(死んでリスポーン後、20秒間は攻撃、移動速度、スペシャルゲージたまるスピードが上がる)

	];

	public static function getGearName($gearNum){
		return self::$nameList[$gearNum];
	}
}