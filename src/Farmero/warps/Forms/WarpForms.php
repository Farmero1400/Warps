<?php

declare(strict_types=1);

namespace Farmero\warps\Forms;

use pocketmine\player\Player;
use pocketmine\Server;

use jojoe77777\FormAPI\SimpleForm;

use Farmero\warps\Warps;
use Farmero\warps\API\WarpAPI;

class WarpForms
{
    private $warpAPI;

    public function __construct(WarpAPI $warpAPI)
    {
        $this->warpAPI = $warpAPI;
    }

    public function warpForm(): SimpleForm
    {
        $form = new SimpleForm(function (Player $player, string $data = null) {
            if ($data === null) return;

            $name = explode(":", Warps::getConfigValue("warp_cmd"))[0];
            Server::getInstance()->getCommandMap()->dispatch($player, "$name $data");
        });
        $form->setTitle(Warps::getConfigValue("title"));
        $form->setContent(Warps::getConfigValue("content"));
        foreach ($this->warpAPI->getAllWarps() as $warp) {
            $form->addButton(Warps::getConfigReplace("button", "{warp}", $warp), -1, "", $warp);
        }
        return $form;
    }
}
