<?php
namespace SplatoonMCPE;

use pocketmine\plugin\PluginBase;
use pocketmine\entity\Entity;
use pocketmine\entity\Item as ItemEntity;
use pocketmine\network\protocol\AddEntityPacket;

class FloatingText{

	public $owner;
	private $pos;
	private $title;
	private $text;
	private $show = false;
	private $entityId;

	public function __construct(PluginBase $owner, $pos, $text, $title, $show = false){
		$this->owner = $owner;
		$this->pos = $pos;
		$this->title = $title;
		$this->text = $text;
		$this->show = $show;
		$this->entityId = Entity::$entityCount++;
	}

	public function addText($players = null){
		if($players != null){
			foreach($players as $player){
				$this->spawnText($player);
			}
		}else{
			$this->spawnText();
		}
	}

	public function setTitle($title){
		$this->title = $title;
	}

	public function setText($text){
		$this->text = $text;
	}

	public function setInvisible($show = true){
		$this->show = $show;
	}

	private function spawnText($player = null){
		$pk = new AddEntityPacket();
		$pk->type = ItemEntity::NETWORK_ID;
		$pk->eid = $this->entityId;
		$pk->x = $this->pos->x;
		$pk->y = $this->pos->y;
		$pk->z = $this->pos->z;
		$pk->speedX = 0;
		$pk->speedY = 0;
		$pk->speedZ = 0;
		$pk->yaw = 0;
		$pk->pitch = 0;
		$pk->item = 0;
		$pk->meta = 0;
		$flags = 0;
		$flags |= 1 << Entity::DATA_FLAG_INVISIBLE;
		$flags |= 1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG;
		$flags |= 1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG;
		$flags |= 1 << Entity::DATA_FLAG_IMMOBILE;
		$pk->metadata = [
			Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
			Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $this->title . ($this->text !== "" ? "\n" . $this->text : "")],
	];

		if($this->show){
			return false;
		}
		if($player != null){
			$player->dataPacket($pk);
		}else{
			$this->owner->getServer()->broadcastPacket($this->owner->getServer()->getOnlinePlayers(), $pk);
		}
		return true;
	}

}