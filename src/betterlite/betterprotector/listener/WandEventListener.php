<?php

declare(strict_types=1);

namespace betterlite\betterprotector\listener;

use betterlite\betterprotector\BetterProtector;
use betterlite\betterprotector\utils\Messages;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use function number_format;

/**
 * Handles wand selection tool events.
 * Author: BetterLite Team
 */
class WandEventListener implements Listener {

    public const WAND_TAG = "BetterProtectorWand";

    public function __construct(
        private BetterProtector $plugin
    ) {}

    public static function isWandItem(Item $item): bool {
        return $item->getNamedTag()->getTag(self::WAND_TAG) !== null;
    }

    public function onBlockBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();
        $item = $event->getItem();

        if (!self::isWandItem($item)) {
            return;
        }

        if (!$player->hasPermission("betterprotector.command.area.pos1") && !$player->hasPermission("betterprotector.admin")) {
            return;
        }

        $event->cancel();

        $block = $event->getBlock();
        $pos = $block->getPosition();

        $session = $this->plugin->getSessionManager()->getSession($player);
        $session->setPos1($pos);

        Messages::send($player, "pos1-set", [
            "x" => $pos->getFloorX(),
            "y" => $pos->getFloorY(),
            "z" => $pos->getFloorZ(),
            "world" => $pos->getWorld()->getFolderName()
        ]);

        if ($session->hasCompleteSelection()) {
            $box = $session->getBoundingBox();
            if ($box !== null) {
                Messages::send($player, "pos-volume", [
                    "width" => $box->getWidth(),
                    "height" => $box->getHeight(),
                    "length" => $box->getLength(),
                    "volume" => number_format($box->getVolume())
                ]);
            }
        }
    }

    public function onInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        $item = $event->getItem();

        if (!self::isWandItem($item)) {
            return;
        }

        $block = $event->getBlock();
        $pos = $block->getPosition();
        $session = $this->plugin->getSessionManager()->getSession($player);

        if ($event->getAction() === PlayerInteractEvent::LEFT_CLICK_BLOCK) {
            if (!$player->hasPermission("betterprotector.command.area.pos1") && !$player->hasPermission("betterprotector.admin")) {
                return;
            }

            $event->cancel();
            $session->setPos1($pos);

            Messages::send($player, "pos1-set", [
                "x" => $pos->getFloorX(),
                "y" => $pos->getFloorY(),
                "z" => $pos->getFloorZ(),
                "world" => $pos->getWorld()->getFolderName()
            ]);

            if ($session->hasCompleteSelection()) {
                $box = $session->getBoundingBox();
                if ($box !== null) {
                    Messages::send($player, "pos-volume", [
                        "width" => $box->getWidth(),
                        "height" => $box->getHeight(),
                        "length" => $box->getLength(),
                        "volume" => number_format($box->getVolume())
                    ]);
                }
            }
        } elseif ($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
            if (!$player->hasPermission("betterprotector.command.area.pos2") && !$player->hasPermission("betterprotector.admin")) {
                return;
            }

            $event->cancel();
            $session->setPos2($pos);

            Messages::send($player, "pos2-set", [
                "x" => $pos->getFloorX(),
                "y" => $pos->getFloorY(),
                "z" => $pos->getFloorZ(),
                "world" => $pos->getWorld()->getFolderName()
            ]);

            if ($session->hasCompleteSelection()) {
                $box = $session->getBoundingBox();
                if ($box !== null) {
                    Messages::send($player, "pos-volume", [
                        "width" => $box->getWidth(),
                        "height" => $box->getHeight(),
                        "length" => $box->getLength(),
                        "volume" => number_format($box->getVolume())
                    ]);
                }
            }
        }
    }
}
