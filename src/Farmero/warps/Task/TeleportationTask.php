<?php

declare(strict_types=1);

namespace Farmero\warps\Task;

use pocketmine\entity\effect\VanillaEffects;
use pocketmine\scheduler\Task;
use pocketmine\world\Position;
use pocketmine\player\Player;

use Farmero\warps\Warps;
use Farmero\warps\API\WarpAPI;

class TeleportationTask extends Task
{
    private $startPosition;
    private $player;
    private $warp;
    private $timer;

    public function __construct(Player $player, string $warp)
    {
        $this->warp = $warp;
        $this->player = $player;
        $this->startPosition = $player->getPosition();
        $this->timer = Warps::getConfigValue("delay");
        Warps::getInstance()->getScheduler()->scheduleDelayedRepeatingTask($this, 20, 20);
    }

    public function onRun(): void
    {
        $player = $this->player;
        if (!$player->isOnline()) {
            $this->getHandler()->cancel();
            return;
        }

        if ($player->getPosition()->getFloorX() === $this->startPosition->getFloorX() &&
            $player->getPosition()->getFloorY() === $this->startPosition->getFloorY() &&
            $player->getPosition()->getFloorZ() === $this->startPosition->getFloorZ()) {
            $player->sendTip(Warps::getConfigReplace("warp_tip_cooldown", ["{time}"], [$this->timer]));
            $player->sendTitle(Warps::getConfigReplace("warp_title_cooldown"));
            $this->timer--;
        } else {
            $player->sendMessage(Warps::getConfigReplace("warp_tip_cancel"));
            $player->sendMessage(Warps::getConfigReplace("warp_msg_cancel"));
            $player->sendTitle(Warps::getConfigReplace("warp_title_cancel"));
            $player->sendSubtitle(Warps::getConfigReplace("warp_subtitle_cancel"));
            $player->getEffects()->remove(VanillaEffects::BLINDNESS());
            $this->getHandler()->cancel();
            return;
        }

        if ($this->timer === 0) {
            $player->getEffects()->remove(VanillaEffects::BLINDNESS());
            $warpAPI = new WarpAPI();
            $player->teleport($warpAPI->getWarp($this->warp));
            $player->sendTip(Warps::getConfigReplace("warp_tip_teleport"));
            $player->sendMessage(Warps::getConfigReplace("warp_msg_teleport"));
            $player->sendTitle(Warps::getConfigReplace("warp_title_teleport"));
            $player->sendSubtitle(Warps::getConfigReplace("warp_subtitle_teleport"));
            $this->getHandler()->cancel();
            return;
        }
    }
}
