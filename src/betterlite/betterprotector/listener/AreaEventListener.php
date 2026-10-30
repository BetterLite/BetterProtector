<?php

declare(strict_types=1);

namespace betterlite\betterprotector\listener;

use betterlite\betterprotector\BetterProtector;
use betterlite\betterprotector\utils\Messages;
use pocketmine\block\Anvil;
use pocketmine\block\Barrel;
use pocketmine\block\Bed;
use pocketmine\block\Bell;
use pocketmine\block\BrewingStand;
use pocketmine\block\Button;
use pocketmine\block\Cauldron;
use pocketmine\block\Chest;
use pocketmine\block\CraftingTable;
use pocketmine\block\DaylightSensor;
use pocketmine\block\Door;
use pocketmine\block\EnchantingTable;
use pocketmine\block\EnderChest;
use pocketmine\block\FenceGate;
use pocketmine\block\Furnace;
use pocketmine\block\Hopper;
use pocketmine\block\Jukebox;
use pocketmine\block\Lectern;
use pocketmine\block\Lever;
use pocketmine\block\Note;
use pocketmine\block\RedstoneComparator;
use pocketmine\block\RedstoneRepeater;
use pocketmine\block\ShulkerBox;
use pocketmine\block\Trapdoor;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockBurnEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\LeavesDecayEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerBucketEmptyEvent;
use pocketmine\event\player\PlayerBucketFillEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use pocketmine\world\Position;

/**
 * Handles core area protection and restriction events.
 * Author: BetterLite Team
 */
class AreaEventListener implements Listener {

    public function __construct(
        private BetterProtector $plugin
    ) {}

    private function notifyBlocked(Player $player, string $messageKey): void {
        $config = $this->plugin->getConfig();
        if (!$config->getNested("settings.notify-action-blocked", true)) {
            return;
        }

        $type = (string) $config->getNested("settings.action-blocked-notification", "popup");
        match ($type) {
            "actionbar" => Messages::sendActionBar($player, $messageKey),
            "chat" => Messages::send($player, $messageKey),
            default => Messages::sendPopup($player, $messageKey),
        };
    }

    private function sendBoundaryNotification(Player $player, string $messageKey, string $areaName): void {
        $type = (string) $this->plugin->getConfig()->getNested("settings.notification-type", "actionbar");
        if ($type === "none") {
            return;
        }

        $params = ["name" => $areaName];
        match ($type) {
            "title" => Messages::sendTitle($player, $messageKey, "", $params),
            "popup" => Messages::sendPopup($player, $messageKey, $params),
            "chat" => Messages::send($player, $messageKey, $params),
            default => Messages::sendActionBar($player, $messageKey, $params),
        };
    }

    public function onBlockBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();
        if (WandEventListener::isWandItem($event->getItem())) {
            return;
        }

        $pos = $event->getBlock()->getPosition();
        if (!$this->plugin->getAreaManager()->canEdit($pos, $player)) {
            $event->cancel();
            $this->notifyBlocked($player, "action-blocked-edit");
        }
    }

    public function onBlockPlace(BlockPlaceEvent $event): void {
        $player = $event->getPlayer();
        $world = $player->getWorld();
        $areaManager = $this->plugin->getAreaManager();

        foreach ($event->getTransaction()->getBlocks() as [$x, $y, $z, $block]) {
            $pos = new Position($x, $y, $z, $world);
            if (!$areaManager->canEdit($pos, $player)) {
                $event->cancel();
                $this->notifyBlocked($player, "action-blocked-edit");
                return;
            }
        }
    }

    public function onBucketEmpty(PlayerBucketEmptyEvent $event): void {
        $player = $event->getPlayer();
        $blockClicked = $event->getBlockClicked();
        $blockReplaced = $blockClicked->getSide($event->getBlockFace());
        $pos = $blockReplaced->getPosition();
        if (!$this->plugin->getAreaManager()->canEdit($pos, $player)) {
            $event->cancel();
            $this->notifyBlocked($player, "action-blocked-edit");
        }
    }

    public function onBucketFill(PlayerBucketFillEvent $event): void {
        $player = $event->getPlayer();
        $pos = $event->getBlockClicked()->getPosition();
        if (!$this->plugin->getAreaManager()->canEdit($pos, $player)) {
            $event->cancel();
            $this->notifyBlocked($player, "action-blocked-edit");
        }
    }

    public function onInteract(PlayerInteractEvent $event): void {
        if ($event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
            return;
        }

        if (WandEventListener::isWandItem($event->getItem())) {
            return;
        }

        $block = $event->getBlock();
        // Check if the block is an interactive container / mechanism
        $isInteractive = $block instanceof Chest
            || $block instanceof EnderChest
            || $block instanceof Door
            || $block instanceof Trapdoor
            || $block instanceof FenceGate
            || $block instanceof Button
            || $block instanceof Lever
            || $block instanceof Furnace
            || $block instanceof Barrel
            || $block instanceof CraftingTable
            || $block instanceof Anvil
            || $block instanceof Bed
            || $block instanceof Hopper
            || $block instanceof BrewingStand
            || $block instanceof EnchantingTable
            || $block instanceof Lectern
            || $block instanceof Bell
            || $block instanceof DaylightSensor
            || $block instanceof RedstoneRepeater
            || $block instanceof RedstoneComparator
            || $block instanceof Jukebox
            || $block instanceof Note
            || $block instanceof Cauldron
            || $block instanceof ShulkerBox;

        if (!$isInteractive) {
            return;
        }

        $player = $event->getPlayer();
        $pos = $block->getPosition();

        if (!$this->plugin->getAreaManager()->canTouch($pos, $player)) {
            $event->cancel();
            $this->notifyBlocked($player, "action-blocked-touch");
        }
    }

    public function onEntityDamage(EntityDamageEvent $event): void {
        $victim = $event->getEntity();

        // God mode protection for players
        if ($victim instanceof Player) {
            if ($this->plugin->getAreaManager()->isGod($victim->getPosition())) {
                if ($event->getCause() !== EntityDamageEvent::CAUSE_SUICIDE) {
                    $event->cancel();
                    return;
                }
            }
        }

        // PvP combat protection
        if ($event instanceof EntityDamageByEntityEvent) {
            $damager = $event->getDamager();
            if ($victim instanceof Player && $damager instanceof Player) {
                $areaManager = $this->plugin->getAreaManager();
                $victimPos = $victim->getPosition();
                $damagerPos = $damager->getPosition();

                if (!$areaManager->canPvp($victimPos, $damager) || !$areaManager->canPvp($damagerPos, $damager)) {
                    $event->cancel();
                    $this->notifyBlocked($damager, "action-blocked-pvp");
                }
            }
        }
    }

    public function onMove(PlayerMoveEvent $event): void {
        $from = $event->getFrom();
        $to = $event->getTo();

        // Ignore look rotations without block movement
        if ($from->getFloorX() === $to->getFloorX()
            && $from->getFloorY() === $to->getFloorY()
            && $from->getFloorZ() === $to->getFloorZ()
        ) {
            return;
        }

        $player = $event->getPlayer();
        $areaManager = $this->plugin->getAreaManager();

        // Entry restriction check — only enforce when actually ENTERING an area,
        // not for movement within the same area (otherwise players already inside
        // a no-enter area would be frozen).
        $fromArea = $areaManager->getAreaAt($from);
        $toArea   = $areaManager->getAreaAt($to);

        if ($fromArea !== $toArea && !$areaManager->canEnter($to, $player)) {
            $event->setTo($from);
            $this->notifyBlocked($player, "action-blocked-entry");
            return;
        }

        // Area border transition notification
        $session = $this->plugin->getSessionManager()->getSession($player);
        $toAreaName = $toArea?->getName();

        if ($session->getLastAreaName() !== $toAreaName) {
            $lastAreaName = $session->getLastAreaName();
            if ($lastAreaName !== null) {
                $lastArea = $areaManager->getArea($lastAreaName);
                if ($lastArea !== null) {
                    $leaveMsg = $lastArea->getLeaveMessage();
                    if ($leaveMsg !== null && $leaveMsg !== "") {
                        $player->sendMessage(Messages::format($leaveMsg));
                    } else {
                        $this->sendBoundaryNotification($player, "area-leave", $lastArea->getName());
                    }
                }
            }

            if ($toArea !== null) {
                $enterMsg = $toArea->getEnterMessage();
                if ($enterMsg !== null && $enterMsg !== "") {
                    $player->sendMessage(Messages::format($enterMsg));
                } else {
                    $this->sendBoundaryNotification($player, "area-enter", $toArea->getName());
                }
            }

            $session->setLastAreaName($toAreaName);
        }
    }

    public function onExplode(EntityExplodeEvent $event): void {
        $areaManager = $this->plugin->getAreaManager();
        $blocks = $event->getBlockList();
        $safeBlocks = [];

        foreach ($blocks as $block) {
            if ($areaManager->canExplode($block->getPosition())) {
                $safeBlocks[] = $block;
            }
        }

        $event->setBlockList($safeBlocks);
    }

    public function onBurn(BlockBurnEvent $event): void {
        $pos = $event->getBlock()->getPosition();
        if (!$this->plugin->getAreaManager()->canEdit($pos)) {
            $event->cancel();
        }
    }

    public function onLeavesDecay(LeavesDecayEvent $event): void {
        $pos = $event->getBlock()->getPosition();
        if (!$this->plugin->getAreaManager()->canDecay($pos)) {
            $event->cancel();
        }
    }

    public function onDropItem(PlayerDropItemEvent $event): void {
        $player = $event->getPlayer();
        if (!$this->plugin->getAreaManager()->canDropItem($player->getPosition(), $player)) {
            $event->cancel();
            $this->notifyBlocked($player, "action-blocked-item-drop");
        }
    }

    public function onPickupItem(EntityItemPickupEvent $event): void {
        $entity = $event->getEntity();
        if ($entity instanceof Player) {
            if (!$this->plugin->getAreaManager()->canPickupItem($entity->getPosition(), $entity)) {
                $event->cancel();
                $this->notifyBlocked($entity, "action-blocked-item-pickup");
            }
        }
    }

    public function onQuit(PlayerQuitEvent $event): void {
        $this->plugin->getSessionManager()->removeSession($event->getPlayer());
    }
}
