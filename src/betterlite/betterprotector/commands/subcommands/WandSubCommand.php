<?php

declare(strict_types=1);

namespace betterlite\betterprotector\commands\subcommands;

use betterlite\betterprotector\BetterProtector;
use betterlite\betterprotector\libs\commandox\BaseSubCommand;
use betterlite\betterprotector\libs\commandox\CommandContext;
use betterlite\betterprotector\listener\WandEventListener;
use betterlite\betterprotector\utils\Messages;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

/**
 * Wand subcommand: gives the selection wand item.
 * Author: BetterLite Team
 */
class WandSubCommand extends BaseSubCommand {

    public function __construct(Plugin $plugin) {
        parent::__construct(
            $plugin,
            "wand",
            "Get the area selection wand tool",
            ["tool"]
        );
    }

    protected function configure(): void {
        $this->setPermission("betterprotector.command.area.wand");
    }

    public function onRun(CommandContext $context): void {
        $sender = $context->getSender();
        if (!($sender instanceof Player)) {
            Messages::send($sender, "error-only-player");
            return;
        }

        $itemId = (string) BetterProtector::getInstance()->getConfig()->getNested("settings.wand-item", "wooden_axe");
        $item = StringToItemParser::getInstance()->parse($itemId) ?? VanillaItems::WOODEN_AXE();

        $item->setCustomName(Messages::get("wand-name", [], false));
        $item->setLore([
            Messages::get("wand-lore-1", [], false),
            Messages::get("wand-lore-2", [], false)
        ]);
        $item->getNamedTag()->setString(WandEventListener::WAND_TAG, "1");

        $sender->getInventory()->addItem($item);
        Messages::send($sender, "wand-given");
    }
}
