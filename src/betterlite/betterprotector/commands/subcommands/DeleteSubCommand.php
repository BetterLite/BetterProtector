<?php

declare(strict_types=1);

namespace betterlite\betterprotector\commands\subcommands;

use betterlite\betterprotector\BetterProtector;
use betterlite\betterprotector\libs\commandox\argument\StringArgument;
use betterlite\betterprotector\libs\commandox\BaseSubCommand;
use betterlite\betterprotector\libs\commandox\CommandContext;
use betterlite\betterprotector\utils\Messages;
use pocketmine\plugin\Plugin;
use function strtolower;

/**
 * Delete subcommand: deletes an existing protected area.
 * Author: BetterLite Team
 */
class DeleteSubCommand extends BaseSubCommand {

    public function __construct(Plugin $plugin) {
        parent::__construct(
            $plugin,
            "delete",
            "Delete an existing protected area",
            ["remove", "del"]
        );
    }

    protected function configure(): void {
        $this->setPermission("betterprotector.command.area.delete");
        $this->registerArgument(0, new StringArgument("name"));
    }

    public function onRun(CommandContext $context): void {
        $sender = $context->getSender();
        $name = strtolower((string) $context->getArg("name"));
        $areaManager = BetterProtector::getInstance()->getAreaManager();

        if (!$areaManager->hasArea($name)) {
            Messages::send($sender, "area-delete-not-found", ["name" => $name]);
            return;
        }

        $areaManager->removeArea($name);
        Messages::send($sender, "area-delete-success", ["name" => $name]);
    }
}
