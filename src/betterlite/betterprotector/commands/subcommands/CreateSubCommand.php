<?php

declare(strict_types=1);

namespace betterlite\betterprotector\commands\subcommands;

use betterlite\betterprotector\area\Area;
use betterlite\betterprotector\BetterProtector;
use betterlite\betterprotector\libs\commandox\argument\StringArgument;
use betterlite\betterprotector\libs\commandox\BaseSubCommand;
use betterlite\betterprotector\libs\commandox\CommandContext;
use betterlite\betterprotector\utils\Messages;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use function number_format;
use function preg_match;

/**
 * Create subcommand: creates a new protected area.
 * Author: BetterLite Team
 */
class CreateSubCommand extends BaseSubCommand {

    public function __construct(Plugin $plugin) {
        parent::__construct(
            $plugin,
            "create",
            "Create a new protected area from current selection",
            ["add", "new"]
        );
    }

    protected function configure(): void {
        $this->setPermission("betterprotector.command.area.create");
        $this->registerArgument(0, new StringArgument("name"));
    }

    public function onRun(CommandContext $context): void {
        $sender = $context->getSender();
        if (!($sender instanceof Player)) {
            Messages::send($sender, "error-only-player");
            return;
        }

        $name = (string) $context->getArg("name");
        if (!preg_match('/^[a-zA-Z0-9_\-]{2,32}$/', $name)) {
            Messages::send($sender, "area-create-invalid-name");
            return;
        }

        $plugin = BetterProtector::getInstance();
        $areaManager = $plugin->getAreaManager();
        if ($areaManager->hasArea($name)) {
            Messages::send($sender, "area-create-exists", ["name" => $name]);
            return;
        }

        $session = $plugin->getSessionManager()->getSession($sender);
        if (!$session->hasCompleteSelection()) {
            Messages::send($sender, "area-create-no-selection");
            return;
        }

        $pos1 = $session->getPos1();
        $pos2 = $session->getPos2();
        if ($pos1 === null || $pos2 === null || $pos1->getWorld()->getFolderName() !== $pos2->getWorld()->getFolderName()) {
            Messages::send($sender, "area-create-different-worlds");
            return;
        }

        $box = $session->getBoundingBox();
        if ($box === null) {
            Messages::send($sender, "area-create-no-selection");
            return;
        }

        $worldName = $pos1->getWorld()->getFolderName();
        $area = new Area(
            name: $name,
            worldName: $worldName,
            bounds: $box,
            whitelist: []
        );

        $areaManager->addArea($area);

        Messages::send($sender, "area-create-success", [
            "name" => $area->getName(),
            "volume" => number_format($box->getVolume()),
            "world" => $worldName
        ]);
    }
}
