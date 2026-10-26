<?php

declare(strict_types=1);

namespace betterlite\betterprotector\commands\subcommands;

use betterlite\betterprotector\BetterProtector;
use betterlite\betterprotector\libs\commandox\BaseSubCommand;
use betterlite\betterprotector\libs\commandox\CommandContext;
use betterlite\betterprotector\utils\Messages;
use pocketmine\plugin\Plugin;

/**
 * Reload subcommand: reloads configuration, messages, and areas.
 * Author: BetterLite Team
 */
class ReloadSubCommand extends BaseSubCommand {

    public function __construct(Plugin $plugin) {
        parent::__construct(
            $plugin,
            "reload",
            "Reload BetterProtector configuration and areas"
        );
    }

    protected function configure(): void {
        $this->setPermission("betterprotector.command.area.reload");
    }

    public function onRun(CommandContext $context): void {
        BetterProtector::getInstance()->reload();
        Messages::send($context->getSender(), "area-reload-success");
    }
}
