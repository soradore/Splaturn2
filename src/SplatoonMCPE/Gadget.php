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

class Gadget{

	/* memo
	 * ガジェット関連の変更部に　#gadget　と記入
	 *
	 * 変更したファイル
	 * Event
	 * Weapon
	 * PlayerData
	 * Main
	 */
	const TYPES = 10;//ガジェットの種類数(通常のみ)
	const ALL_TYPES = 13;//ガジェットの種類数(ガチャに使用)

	const RATE_MAIN = 1;//メイン効率強化
	const RATE_SUB = 2;//サブ効率強化
	const INK_HEAL = 3;//インク回復量アップ
	const POWER = 4;//攻撃力強化
	const DEFENCE = 5;//防御力強化
	const BOMB_GUARD = 6;//ボムガード
	const BOMB_THROW = 7;//ボム飛距離アップ
	const SPEED = 8;//ヒト移動速度アップ
	const RESPAWN_TIME = 9;//復活時間短縮
	const IKA_SPEED = 10;//イカ移動速度アップ

	const NECRO_PAINT = 11;//ネクロペイント
	const REGRET = 12;//うらみ
	const SAFE_SHOES = 13;//安全シューズ

	const INK_TRICK = 14;//インクトリック
	const HOTARU = 15;//無双のホタルイカ
	const IDATEN = 16;//韋駄天

	const BOMB_MASTER = 17;//ボムマスター
	const AORI = 18;//無双のアオリイカ
	const SPIKE = 19;//スパイクシューズ

	const CHANGE_GADGET_VALUE_1 = 11;
	const CHANGE_GADGET_VALUE_2 = 12;
	const CHANGE_GADGET_VALUE_3 = 13;

	/**
	 * 現在取得しているガジェットデータの取得
	 * @param Player $player
	*/
	public static function getGadgetsData(Player $player){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		return $playerData->getNowGadgets();
	}

	/**
	 * 指定した種類のガジェットの数を取得
	 * @param Player $player
	 * @param Int $type
	*/
	public static function getGadgetsCount(Player $player, $type){
		$data = self::getGadgetsData($player);
		$count = 0;
		foreach ($data as $slot){
			if($slot === $type) $count++;
		}
		return $count;
	}

	/**
	 * 指定した種類のガジェットの補正値を取得
	 * @param Player $player
	 * @param Int $type
	*/
	public static function getCorrection(Player $player, $type){
		$count = self::getGadgetsCount($player, $type);
		switch($type){
			case self::RATE_MAIN:
				$correction = 1-$count*0.1;
				break;
			case self::RATE_SUB:
				$count += self::getGadgetsCount($player, self::BOMB_MASTER);
				$correction = 1-$count*0.1;
				break;
			case self::INK_HEAL:
				$count += self::getGadgetsCount($player, self::BOMB_MASTER);
				$correction = 1+$count*0.2;
				$correction *= (1-self::getGadgetsCount($player, self::INK_TRICK)*0.5);
				break;
			case self::POWER:
				$count += self::getGadgetsCount($player, self::INK_TRICK);
				$count += self::getGadgetsCount($player, self::SPIKE);
				$correction = 1+$count*0.1;
				break;
			case self::DEFENCE:
				$count += self::getGadgetsCount($player, self::INK_TRICK);
				$correction = 1+$count*0.1;
				break;
			case self::BOMB_GUARD:
				$count += self::getGadgetsCount($player, self::INK_TRICK);
				$correction = 1+$count*0.25;
				break;
			case self::BOMB_THROW:
				$count += self::getGadgetsCount($player, self::BOMB_MASTER);
				$correction = 1+$count*0.1;
				break;
			case self::SPEED:
				$count += self::getGadgetsCount($player, self::IDATEN)*2;
				$count += self::getGadgetsCount($player, self::SPIKE);
				$correction = 1+$count*0.1;
				break;
			case self::RESPAWN_TIME:
				$correction = 1-$count*0.2;
				$correction *= (1+self::getGadgetsCount($player, self::AORI)*0.5);
				break;
			case self::IKA_SPEED:
				$count += self::getGadgetsCount($player, self::IDATEN)*2;
				$count += self::getGadgetsCount($player, self::SPIKE);
				$correction = 1+$count*0.1;
				break;
			case self::NECRO_PAINT:
				$correction = $count;
				break;
			case self::REGRET:
				$correction = $count;
				break;
			case self::SAFE_SHOES:
				$correction = 1+$count*0.7;
				break;
			case self::INK_TRICK:
				$correction = $count;
				break;
			case self::HOTARU:
				$correction = $count*2;
				break;
			case self::AORI:
				$correction = $count*0.5;
				break;
			case self::IDATEN:
				$correction = $count;
				break;
			case self::SPIKE:
				$correction = $count;
				break;
			case self::BOMB_MASTER:
				$correction = $count;
				break;
			default:
				$correction = 1;
				break;
		}
		return $correction;
	}

	/**
	 * 装備してるブキのガジェットを全部ランダムなものにする。つまりガチャ
	 * @param Player $player
	 * 返り値 変更後のガジェット
	 */
	public static function resetAllGadgetSpecial(Player $player){
		$slot1 = self::getRandomGadget(1);
		$slot2 = self::getRandomGadget(2);
		$slot3 = self::getRandomGadget(3);
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$weap = $playerData->getNowWeapon();
		$playerData->setGadgets($weap, [$slot1, $slot2, $slot3]);
		return [$slot1, $slot2, $slot3];
	}

	/**
	 * 装備してるブキのガジェットを全部ランダムなものにする。つまりガチャ
	 * @param Player $player
	 * 返り値 変更後のガジェット
	 */
	public static function resetAllGadget(Player $player){
		$slot1 = mt_rand(1, self::TYPES); 
		$slot2 = mt_rand(1, self::TYPES); 
		$slot3 = mt_rand(1, self::TYPES); 
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$weap = $playerData->getNowWeapon();
		$playerData->setGadgets($weap, [$slot1, $slot2, $slot3]);
		return [$slot1, $slot2, $slot3];
	}

	/**
	 * 装備しているブキの指定したスロットのガジェットをランダムなものにする
	 * @param Player $player
	 * @param int $slot
	 * 返り値 変更後のガジェット番号
	 */
	public static function setRandomGadget(Player $player, $slot){
		$result = self::getRandomGadget($slot);
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$weap = $playerData->getNowWeapon();
		$playerData->setGadgets($weap, $slot, $result);
		return $result;
	}

	public static function getRandomGadget($slot = 0){
		$result = mt_rand(1, self::ALL_TYPES);
		if($result === self::CHANGE_GADGET_VALUE_1){
			switch($slot){
				case 1:
					$result = self::NECRO_PAINT;
				break;
				case 2:
					$result = self::REGRET;
				break;
				case 3:
					$result = self::SAFE_SHOES;
				break;
			}
		}else if($result === self::CHANGE_GADGET_VALUE_2){
			switch($slot){
				case 1:
					$result = self::INK_TRICK;
				break;
				case 2:
					$result = self::HOTARU;
				break;
				case 3:
					$result = self::IDATEN;
				break;
			}
		}else if($result === self::CHANGE_GADGET_VALUE_3){
			switch($slot){
				case 1:
					$result = self::BOMB_MASTER;
				break;
				case 2:
					$result = self::AORI;
				break;
				case 3:
					$result = self::SPIKE;
				break;
			}
		}		return $result;
	}

	/**
	 * 装備してるブキのガジェットを任意のものにする(チート)
	 * @param Player $player
	 * @param Array $gadget
	 */
	public static function setAllGadget(PLayer $player, Array $gadget){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$weap = $playerData->getNowWeapon();
		$playerData->setGadgets($weap, $gadget);
	}

	public static function changeSaveGadget(Player $player){
		$user = $player->getName();
		$playerData = Account::getInstance()->getData($user);
		$gadgetData = self::getGadgetsData($player);
		$save = $playerData->getSaveGadget();
		self::setAllGadget($player, $save);
		$playerData->setSaveGadget($gadgetData);
		return $save;
	}

	/**
	 * ガジェットのネームを取得(lang変換前)
	 * @param int $type
	*/
	public static function getGadgetName($type){
		return "gadgetName.".$type;
	}
}