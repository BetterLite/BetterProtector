<?php

declare(strict_types=1);

namespace betterlite\betterprotector\session;

use pocketmine\player\Player;

/**
 * Manages player active sessions.
 * Author: BetterLite Team
 */
class SessionManager {

    /** @var array<string, SelectionSession> Keyed by player raw UUID */
    private array $sessions = [];

    public function getSession(Player $player): SelectionSession {
        $key = $player->getUniqueId()->getBytes();
        if (!isset($this->sessions[$key])) {
            $this->sessions[$key] = new SelectionSession();
        }

        return $this->sessions[$key];
    }

    public function removeSession(Player $player): void {
        $key = $player->getUniqueId()->getBytes();
        unset($this->sessions[$key]);
    }

    public function clear(): void {
        $this->sessions = [];
    }
}
