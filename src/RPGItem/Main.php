<?php
namespace RPGItem;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\command\defaults\GiveCommand;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\entity\Item;
use pocketmine\nbt\NBT;
use RPGItem\EventListener;
use pocketmine\entity\Lightning;
use pocketmine\scheduler\PluginTask;
use pocketmine\item\enchantment\Enchantment;
class Main extends PluginBase implements Listener {
	static function rc($g) {
		if ((int)$g >= 100) {
			$g = 100;
		}
		if (mt_rand(1,100) <= $g) {
			return true;
		} else {
			return false;
		}
		
	}
	public function onEnable() {
		if(!file_exists($this->getDataFolder())){
			mkdir($this->getDataFolder());
		}
		if(!file_exists($this->getDataFolder() . 'items')){
			mkdir($this->getDataFolder() . 'items');
		}
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new class($this) extends PluginTask {
			public function onRun($currentTick) {
				$this->owner->getServer()->getLogger()->info('rpgitem구매 문의 카카오그룹 누젤라서버 그룹장 왕고슴도치');
			}
		}, 20 * 20);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
	}
	public function onCommand(CommandSender $sender,Command $command, $label,array $args) {
		$args[0] = urlencode($args[0]);
		if ($command->getName() == 'rpgitem') {
			if (!isset($args[0])) {
				$sender->sendMessage(TextFormat::WHITE . 'rpgitem <사용할 이름>');
				return true;
			}
			if (!isset($args[1])) {
				$message = <<<END
일부 미구현이 있을수 있습니다
/rpgitem <자기가하고싶은이름> create
/rpgitem <아까자기가적은이름> give
/rpgitem <자기가적은이름> display (그아이템의정하고싶은이름)
/rpgitem <자기가적은이름> item (아이템코드)
/rpgitem <자기가적은이름> damage (숫자)
/rpgitem <자기가적은이름> power lightning (확률)
/rpgitem <자기가적은이름> power fireball (쿨타임)
/rpgitem <자기가적은이름> power ice (쿨타임)
/rpgitem <자기가적은이름> power rainbow (쿨타임) (양털의갯수)
/rpgitem <자기가적은이름> power arrow (쿨타임)
/rpgitem <자기가적은이름> power tntcannon (쿨타임)
/rpgitem <자기가적은이름> power knockup (확률) (올라가는높이)
/rpgitem <자기가적은이름> power teleport (쿨타임) (이동블럭)
/rpgitem <자기가적은이름> power rumble (쿨타임) (띄우는높이) {마법의능력이갈수있는거리}
/rpgitem <자기가적은이름> power potiontick (포션레벨) (포션효과)
/rpgitem <자기가적은이름> power potionself (쿨타임) (몇초지속) (포션레벨) (포션효과)
/rpgitem <자기가적은이름> power potion (확률) (지속시간) (포션레벨) (포션효과)
/rpgitem <자기가적은이름> durability (횟수)
/rpgitem <자기가적은이름> armour Armour (Integer (0-100))
/rpgitem <자기가적은이름> removepower (능력)
/rpgitem <자기가적은이름> remove
END;
				$sender->sendMessage(TextFormat::WHITE . $message);
				return true;
			}
			//create
			if ($args[1] == 'create') {
				if (is_file($this->getDataFolder() . 'items' . DIRECTORY_SEPARATOR . $args[0] . '.json')) {
					$sender->sendMessage(TextFormat::WHITE . '이미 있는 아이템입니다');
					return true;
				}
				file_put_contents($this->getDataFolder() . 'items' . DIRECTORY_SEPARATOR . $args[0] . '.json', json_encode(array( 'display' => 'WOODEN_SWORD', 'itemcode' => array(268, 0))));
				$sender->sendMessage(TextFormat::WHITE . urldecode($args[0]) . '을 생성했습니다');
				return true;
			}
			//display
			if ($args[1] == 'display') {
				if (!is_file($this->getDataFolder() . 'items' . DIRECTORY_SEPARATOR . $args[0] . '.json')) {
					$sender->sendMessage(TextFormat::WHITE . '없는 아이템 이름입니다');
				}
				if (!isset($args[2])) {
					$sender->sendMessage(TextFormat::WHITE . '/rpgitem <자기가적은이름> display (그아이템의정하고싶은이름)');
					return true;
				}
				$item = json_decode(file_get_contents($this->getDataFolder() . 'items' . DIRECTORY_SEPARATOR . $args[0] . '.json'), true);
				$item['display'] = urlencode($args[2]);
				file_put_contents($this->getDataFolder() . 'items' . DIRECTORY_SEPARATOR . $args[0] . '.json', json_encode($item));
				return true;
			}
			//remove
			if ($args[1] == 'remove') {
				if (!is_file($this->getDataFolder() . 'items' . DIRECTORY_SEPARATOR . $args[0] . '.json')) {
					$sender->sendMessage(TextFormat::WHITE . '없는 아이템 이름입니다');
					return true;
				} else {
					unlink($this->getDataFolder() . 'items' . DIRECTORY_SEPARATOR . $args[0] . '.json');
					$sender->sendMessage(TextFormat::WHITE . '삭제하였습니다');
					return true;
				}
			}
			//removepower
			if ($args[1] == 'removepower') {
				if (!is_file($this->getDataFolder() . 'items' . DIRECTORY_SEPARATOR . $args[0] . '.json')) {
					$sender->sendMessage(TextFormat::WHITE . '없는 아이템 이름입니다');
				}
				if (!isset($args[2])) {
					$sender->sendMessage(TextFormat::white . '/rpgitem <자기가적은이름> removepower (지울 능력)');
					return true;
				}
				$item = json_decode(file_get_contents($this->getDataFolder() . 'items' . DIRECTORY_SEPARATOR . $args[0] . '.json'), true);
				if (!isset($item[$args[2]])) {
					$sender->sendMessage(TextFormat::WHITE . '적용되어있지 않은 능력입니다');
					return true;
				}
				unset($item[$args[2]]);
				file_put_contents($this->getDataFolder() . 'items' . DIRECTORY_SEPARATOR . $args[0] . '.json', json_encode($item));
				return true;
			}
			//item
			if ($args[1] == 'item') {
				if (!isset($args[2])) {
					$sender->sendMessage(TextFormat::WHITE . '아이템코드를 입력하여 주십시오');
					return true;
				}
				if (!is_file($this->getDataFolder() . 'items' . DIRECTORY_SEPARATOR . $args[0] . '.json')) {
					$sender->sendMessage(TextFormat::WHITE . '없는 아이템 이름입니다');
					return true;
				} else {
					$item = json_decode(file_get_contents($this->getDataFolder() . 'items' . DIRECTORY_SEPARATOR . $args[0] . '.json'), true);
					$item['itemcode'] = explode(':', $args[2]);
					if(!isset($item['itemcode'][1])) $item['itemcode'][1] = 0;
					file_put_contents($this->getDataFolder() . 'items' . DIRECTORY_SEPARATOR . $args[0] . '.json', json_encode($item));
					$sender->sendMessage(TextFormat::WHITE . '아이템코드를' . $args[2] . '로 설정하였습니다');
					return true;
				}
			}
			//durability
			if ($args[1] == 'durability') {
				if (!is_file($this->getDataFolder() . 'items' . DIRECTORY_SEPARATOR . $args[0] . '.json')) {
					$sender->sendMessage(TextFormat::WHITE . '없는 아이템 이름입니다');
					return true;
				}
				$item = json_decode(file_get_contents($this->getDataFolder() . 'items' . DIRECTORY_SEPARATOR . $args[0] . '.json'), true);
				if(!isset($args[2])) {
					$sender->sendMessage('/rpgitem <자기가적은이름> durability (횟수)');
					return true;
				}
				if(!is_numeric($args[2])) {
					$sender->sendMessage('횟수는 숫자여야 합니다');
					return true;
				}
				$item['dura'] = (int)$args[2];
				if($args[2] == '0') {
					$sender->sendMessage($args[0] . '의 내구도를 무제한으로 설정하였습니다');
					unset($item['dura']);
				} else {
					$sender->sendMessage($args[0] . '의 내구도를' . $args[2] . '회로 제한하였습니다');
				}
				file_put_contents($this->getDataFolder() . 'items' . DIRECTORY_SEPARATOR . $args[0] . '.json', json_encode($item));
				return true;
			}
			//damage
			if ($args[1] == 'damage') {
				if (!is_file($this->getDataFolder() . 'items' . DIRECTORY_SEPARATOR . $args[0] . '.json')) {
					$sender->sendMessage(TextFormat::WHITE . '없는 아이템 이름입니다');
					return true;
				}
				if (!isset($args[2])) {
					$sender->sendMessage(TextFormat::WHITE . '설정할 데미지를 입력해 주세요');
					return true;
				}
				if (!is_numeric($args[2])) {
					$sender->sendMessage(TextFormat::WHITE . '설정할 데미지는 숫자로 입력해 주세요');
					return true;
				}
				$item = json_decode(file_get_contents($this->getDataFolder() . 'items' . DIRECTORY_SEPARATOR . $args[0] . '.json'), true);
				$item['damage'] = (int)$args[2];
				file_put_contents($this->getDataFolder() . 'items' . DIRECTORY_SEPARATOR . $args[0] . '.json', json_encode($item));
				return true;
			}
			//power
			if ($args[1] == 'power') {
				if (!is_file($this->getDataFolder() . 'items' . DIRECTORY_SEPARATOR . $args[0] . '.json')) {
					$sender->sendMessage(TextFormat::WHITE . '없는 아이템 이름입니다');
					return true;
				}
				if (!isset($args[2])) {
					$sender->sendMessage(TextFormat::WHITE . '적용할 능력을 입력해주세요');
					return true;
				}
				$item = json_decode(file_get_contents($this->getDataFolder() . 'items' . DIRECTORY_SEPARATOR . $args[0] . '.json'), true);
				switch ($args[2]) {
					case 'lightning':
						if (!isset($args[3])) {
							$sender->sendMessage(TextFormat::WHITE . '확률을 입력해주세요');
							return true;
						}
						$item[$args[2]] = array();
						$item[$args[2]]['rd'] = (int)$args[3];
						break;
				}
				file_put_contents($this->getDataFolder() . 'items' . DIRECTORY_SEPARATOR . $args[0] . '.json', json_encode($item));
				return true;
			}
			//give
			if ($args[1] == 'give') {
				if (!is_file($this->getDataFolder() . 'items' . DIRECTORY_SEPARATOR . $args[0] . '.json')) {
					$sender->sendMessage(TextFormat::WHITE . '없는 아이템 이름입니다');
					return true;
				}
				if (isset($args[2])) {
					if (!$this->getServer()->getPlayer($args[2]) instanceof Player) {
						$sender->sendMessage(TextFormat::WHITE . '없는 플레이어입니다');
						return true;
					}
				}				
				$item = json_decode(file_get_contents($this->getDataFolder() . 'items' . DIRECTORY_SEPARATOR . $args[0] . '.json'), true);
				//아이템 이름 적용
				$nbttag = array();
				if(isset($item['dura'])) {
					$nbttag['rpg_dura'] = $item['dura'];
					$rpg_dura = ' ' . $item['dura'] . '/' . $item['dura'];
				} else {
					$rpg_dura = NULL;
				}
				$nbttag['rpgitem'] = $args[0];
				$nbttag['CanPlaceOn'] = array("air");
				if (isset($item['display'])) {
					$nbttag['display'] = array("Name" => (urldecode($item['display']) . $rpg_dura));
					$sender->sendMessage($nbttag['display']);
				}
				//아이템 코드
				if (!isset($item['itemcode'])) {
					$item['itemcode'][0] = 268;
					$item['itemcode'][1] = 0;
				}
				if (!isset($args[2])) {
					$args[2] = $sender->getName();
				}
				$gitem = \pocketmine\item\Item::get((int)$item['itemcode'][0], (int)$item['itemcode'][1], 1);
				$gitem->setNamedTag(NBT::parseJSON(json_encode($nbttag)));
				$this->getServer()->getPlayer($args[2])->getInventory()->addItem($gitem);
				$sender->sendMessage($args[0] . '을 획득하였다');
			}
		}
	}
}