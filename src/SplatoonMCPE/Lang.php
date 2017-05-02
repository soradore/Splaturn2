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

use pocketmine\Server;
use pocketmine\lang\BaseLang;
use pocketmine\utils\MainLogger;

class Lang{

	const DEFAULT_LANG = "jpn";

	private $path;
	private $lang = [];
	private $defaultLang = [];

	function __construct($path, $lang){
		$this->path = $path;
		$this->loadLang(self::DEFAULT_LANG, $this->defaultLang);
		if($lang != null) $this->setLang($lang);
	}

	/**
	 * 言語ファイル読み込み
	 * @param  string  $lang
	 * @param  array   &$data
	 * @return bool
	 */
	public function LoadLang($lang, &$data){
		if(file_exists($this->path.$lang.".ini") and strlen($content = file_get_contents($this->path.$lang.".ini")) > 0){
			$data = [];
			foreach(explode("\n", $content) as $line){
				$line = trim($line);
				if($line === "" or $line{0} === "#"){
					continue;
				}
				$t = explode("=", $line, 2);
				if(count($t) < 2){
					continue;
				}
				$key = trim($t[0]);
				$value = str_replace('\n', "\n", trim($t[1]));
				if($value === ""){
					continue;
				}
				$data[$key] = $value;
			}
			return true;
		}else{
			if($this->getBeseText(($text = "language.fileError")) !== $text){
				MainLogger::getLogger()->error($this->translateString("language.fileError"));
			}else{
				MainLogger::getLogger()->error("Failed to read the language file...");
			}
			return false;
		}
	}

	/**
	 * 言語を設定
	 * @param  string $lang
	 * @return bool
	 */
	public function setLang($lang){
		if($this->LoadLang($lang, $this->lang)){
			//PMMP側の言語を変更 
			$serverInstance = Server::getInstance();
			$server = new \ReflectionClass($serverInstance);
			$property = $server->getProperty("baseLang");
			$property->setAccessible(true);
			$property->setValue($serverInstance, new BaseLang($lang));
			return true;
		}
		return false;
	}

	/**
	 * パラメーターを置き換えたテキストを取得
	 * @param  string   $str
	 * @param  string[] $params
	 * @return string
	 */
	public function translateString($str, array $params = []){
		$BaseText = $this->getBeseText($str);
		foreach($params as $i => $p){
			$BaseText = str_replace("{%$i}", $p, $BaseText);
		}
		return $BaseText;
	}

	/**
	 * パラメーターを置き換える前のテキストを取得
	 * @param  string   $str
	 * @return string
	 */
	public function getBeseText($str){
		if(isset($this->lang[$str])){
			return $this->lang[$str];
		}elseif(isset($this->defaultLang[$str])){
			return $this->defaultLang[$str];
		}
		MainLogger::getLogger()->debug("BeseText取得失敗(".$str.")");
		return $str;
	}
}