<?php

declare(strict_types=1);

namespace betterlite\betterprotector;

use betterlite\betterprotector\area\AreaManager;
use betterlite\betterprotector\area\AreaRepository;
use betterlite\betterprotector\commands\AreaCommand;
use betterlite\betterprotector\libs\commandox\network\PacketHooker;
use betterlite\betterprotector\listener\AreaEventListener;
use betterlite\betterprotector\listener\WandEventListener;
use betterlite\betterprotector\session\SessionManager;
use betterlite\betterprotector\utils\Messages;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\Config;
use function file_exists;
use function mkdir;

/**
 * BetterProtector: Advanced Area Protection Plugin for PocketMine-MP 5
 * Author: BetterLite Team
 */
class BetterProtector extends PluginBase {

    private static ?self $instance = null;

    private AreaRepository $repository;
    private AreaManager $areaManager;
    private SessionManager $sessionManager;
    private AreaCommand $areaCommand;

    public static function getInstance(): self {
        if (self::$instance === null) {
            throw new \RuntimeException("BetterProtector is not enabled yet.");
        }
        return self::$instance;
    }

    protected function onLoad(): void {
        self::$instance = $this;
    }

    protected function onEnable(): void {
        if (!file_exists($this->getDataFolder())) {
            @mkdir($this->getDataFolder(), 0777, true);
        }

        $this->saveDefaultConfig();
        $this->saveResource("messages.yml");

        // Initialize messages
        $messagesConfig = new Config($this->getDataFolder() . "messages.yml", Config::YAML);
        Messages::init($messagesConfig);

        // Initialize core systems
        $this->repository = new AreaRepository($this->getDataFolder() . "areas.json", $this->getLogger());
        $this->areaManager = new AreaManager($this, $this->repository);
        $this->sessionManager = new SessionManager();

        // Register event listeners
        $pluginManager = $this->getServer()->getPluginManager();
        $pluginManager->registerEvents(new AreaEventListener($this), $this);
        $pluginManager->registerEvents(new WandEventListener($this), $this);

        // Register CommandoX Bedrock tab-completion packet hooker
        PacketHooker::register($this);

        // Register commands
        $this->areaCommand = new AreaCommand($this);
        $commandMap = $this->getServer()->getCommandMap();
        $fallback = $commandMap->getCommand("area");
        if ($fallback !== null) {
            $commandMap->unregister($fallback);
        }
        $commandMap->register("betterprotector", $this->areaCommand);

        // Auto-save task
        $interval = (int) $this->getConfig()->getNested("settings.auto-save-interval", 6000);
        if ($interval > 0) {
            $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function(): void {
                $this->areaManager->save();
            }), $interval);
        }

        $this->getLogger()->info("§aBetterProtector v" . $this->getDescription()->getVersion() . " has been successfully enabled! (By BetterLite Team)");
    }

    protected function onDisable(): void {
        if (isset($this->areaManager)) {
            $this->areaManager->save();
        }
        if (isset($this->sessionManager)) {
            $this->sessionManager->clear();
        }

        // Reset the PacketHooker static registration flag so a subsequent
        // enable (e.g. /reload) re-registers the listener.
        PacketHooker::unregister();

        // Clear singleton reference to avoid stale instance after disable.
        if (self::$instance === $this) {
            self::$instance = null;
        }
    }

    public function reload(): void {
        $this->reloadConfig();
        $messagesConfig = new Config($this->getDataFolder() . "messages.yml", Config::YAML);
        Messages::init($messagesConfig);
        $this->areaManager->load();
    }

    public function getAreaManager(): AreaManager {
        return $this->areaManager;
    }

    public function getSessionManager(): SessionManager {
        return $this->sessionManager;
    }

    public function getAreaCommand(): AreaCommand {
        return $this->areaCommand;
    }
}
