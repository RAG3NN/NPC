<?php

declare(strict_types=1);

namespace NPC;

use pocketmine\block\BlockFactory;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\Listener;
use pocketmine\item\Item;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

use NPC\entities\other\{
    NPCBoat, NPCFallingSand, NPCMinecart, NPCPrimedTNT
};
use NPC\entities\{
    NPCBat, NPCBlaze, NPCCaveSpider, NPCChicken,
    NPCCow, NPCCreeper, NPCDonkey, NPCElderGuardian,
    NPCEnderman, NPCEndermite, NPCEntity, NPCEvoker,
    NPCGhast, NPCGuardian, NPCHorse, NPCHuman,
    NPCHusk, NPCIronGolem, NPCLavaSlime, NPCLlama,
    NPCMule, NPCMushroomCow, NPCOcelot, NPCPig,
    NPCPigZombie, NPCPolarBear, NPCRabbit, NPCSheep,
    NPCShulker, NPCSilverfish, NPCSkeleton, NPCSkeletonHorse,
    NPCSlime, NPCSnowman, NPCSpider, NPCSquid,
    NPCStray, NPCVex, NPCVillager, NPCVindicator,
    NPCWitch, NPCWither, NPCWitherSkeleton, NPCWolf,
    NPCZombie, NPCZombieHorse, NPCZombieVillager
};

use NPC\events\npcCreationEvent;
use NPC\events\npcDeletionEvent;
use NPC\events\npcHitEvent;


class Main extends PluginBase implements Listener {

    const ENTITY_TYPES = [
        "Chicken", "Pig", "Sheep", "Cow",
        "MushroomCow", "Wolf", "Enderman", "Spider",
        "Skeleton", "PigZombie", "Creeper", "Slime",
        "Silverfish", "Villager", "Zombie", "Human",
        "Bat", "CaveSpider", "LavaSlime", "Ghast",
        "Ocelot", "Blaze", "ZombieVillager", "Snowman",
        "Minecart", "FallingSand", "Boat", "PrimedTNT",
        "Horse", "Donkey", "Mule", "SkeletonHorse",
        "ZombieHorse", "Witch", "Rabbit", "Stray",
        "Husk", "WitherSkeleton", "IronGolem", "Snowman",
        "LavaSlime", "Squid", "ElderGuardian", "Endermite",
        "Evoker", "Guardian", "PolarBear", "Shulker",
        "Vex", "Vindicator", "Wither", "Llama"
    ];

    const ENTITY_ALIASES = [
		"MagmaCube" => "LavaSlime",
        "ZombiePigman" => "PigZombie",
        "Mooshroom" => "MushroomCow",
        "Player" => "Human",
        "VillagerZombie" => "ZombieVillager",
        "SnowGolem" => "Snowman",
        "FallingBlock" => "FallingSand",
        "FakeBlock" => "FallingSand",
        "VillagerGolem" => "IronGolem",
        "EGuardian" => "ElderGuardian",
        "Emite" => "Endermite"
    ];

    /** @var array */
    public $hitSessions = [];
    /** @var array */
    public $idSessions = [];
    /** @var string */
    public $prefix = TextFormat::GREEN . "§f[ " . TextFormat::YELLOW . "§3Npc" . TextFormat::GREEN . "§f ]";
    /** @var string */
    public $noperm = TextFormat::GREEN . "§f[ " . TextFormat::YELLOW . "§3Npc" . TextFormat::GREEN . "§4 ] Vous n’avez pas la permission.";
    /** @var string */
    public $helpHeader =
        TextFormat::YELLOW . "----------------- " .
        TextFormat::GREEN . "§f[" . TextFormat::YELLOW . " §3Npc Help " . TextFormat::GREEN . "§f] " .
        TextFormat::YELLOW . "-----------------";

    /** @var string[] */
    public $mainArgs = [
        "§6help: /npc help",
        "§6spawn: /npc spawn <type> [name]",
        "§6edit: /npc edit [id] [args...]",
        "§6id: /npc id",
        "§6remove: /npc remove [id]",
        "§6version: /npc version",
        "§6cancel: /npc cancel",
    ];
    /** @var string[] */
    public $editArgs = [
        "helmet: /npc edit <eid> helmet <id>",
        "chestplate: /npc edit <eid> chestplate <id>",
        "leggings: /npc edit <eid> leggings <id>",
        "boots: /npc edit <eid> boots <id>",
        "skin: /npc edit <eid> skin",
        "name: /npc edit <eid> name <name>",
        "addcommand: /npc edit <eid> addcommand <command>",
        "delcommand: /npc edit <eid> delcommand <command>",
        "listcommands: /npc edit <eid> listcommands",
        "blockid: /npc edit <eid> block <id[:meta]>",
        "scale: /npc edit <eid> scale <size>",
        "tphere: /npc edit <eid> tphere",
        "tpto: /npc edit <eid> tpto",
        "menuname: /npc edit <eid> menuname <name/remove>"
    ];

    /**
     * @return void
     */
    public function onEnable(): void {
        foreach ([
                    npcCreeper::class, npcBat::class, npcSheep::class,
                    npcPigZombie::class, npcGhast::class, npcBlaze::class,
                    npcIronGolem::class, npcSnowman::class, npcOcelot::class,
                    npcZombieVillager::class, npcHuman::class, npcCow::class,
                    npcZombie::class, npcSquid::class, npcVillager::class,
                    npcSpider::class, npcPig::class, npcMushroomCow::class,
                    npcWolf::class, npcLavaSlime::class, npcSilverfish::class,
                    npcSkeleton::class, npcSlime::class, npcChicken::class,
                    npcEnderman::class, npcCaveSpider::class, npcBoat::class,
                    npcMinecart::class, npcMule::class, npcWitch::class,
                    npcPrimedTNT::class, npcHorse::class, npcDonkey::class,
                    npcSkeletonHorse::class, npcZombieHorse::class, npcRabbit::class,
                    npcStray::class, npcHusk::class, npcWitherSkeleton::class,
                    npcFallingSand::class, npcElderGuardian::class, npcEndermite::class,
                    npcEvoker::class, npcGuardian::class, npcLlama::class,
                    npcPolarBear::class, npcShulker::class, npcVex::class,
                    npcVindicator::class, npcWither::class
                 ] as $className) {
            Entity::registerEntity($className, true);
        }
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    /**
     * @param CommandSender $sender
     * @param Command       $command
     * @param string        $label
     * @param string[]      $args
     *
     * @return bool
     */
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        switch (strtolower($command->getName())) {
            case "nothing":
                return true;
            case "rca":
                if (count($args) < 2) {
                    $sender->sendMessage($this->prefix . "S’il vous plaît entrer un joueur et une commande.");
                    return true;
                }
                $player = $this->getServer()->getPlayer(array_shift($args));
                if ($player instanceof Player) {
                    $this->getServer()->dispatchCommand($player, trim(implode(" ", $args)));
                    return true;
                } else {
                    $sender->sendMessage($this->prefix . "Joueur non trouvé.");
                    return true;
                }
            case "npc":
                if ($sender instanceof Player) {
                    if (!isset($args[0])) {
                        if (!$sender->hasPermission("npc.command")) {
                            $sender->sendMessage($this->noperm);
                            return true;
                        } else {
                            $sender->sendMessage($this->prefix . "[ §3/npc help §f]'.");
                            return true;
                        }
                    }
                    $arg = array_shift($args);
                    switch ($arg) {
                        case "id":
                            if (!$sender->hasPermission("npc.id")) {
                                $sender->sendMessage($this->noperm);
                                return true;
                            }
                            $this->idSessions[$sender->getName()] = true;
                            $sender->sendMessage($this->prefix . "Frappez une entité pour obtenir son ID!");
                            return true;
                        case "version":
                            if (!$sender->hasPermission("npc.version")) {
                                $sender->sendMessage($this->noperm);
                                return true;
                            }
                            $desc = $this->getDescription();
                            $sender->sendMessage($this->prefix . TextFormat::BLUE . $desc->getName() . " " . $desc->getVersion() . " " . TextFormat::GREEN . "by " . TextFormat::GOLD . "jojoe77777");
                            return true;
                        case "cancel":
                        case "stopremove":
                        case "stopid":
                            unset($this->hitSessions[$sender->getName()]);
                            unset($this->idSessions[$sender->getName()]);
                            $sender->sendMessage($this->prefix . "Cancelled.");
                            return true;
                        case "remove":
                            if (!$sender->hasPermission("npc.remove")) {
                                $sender->sendMessage($this->noperm);
                                return true;
                            }
                            if (!isset($args[0])) {
                                $this->hitSessions[$sender->getName()] = true;
                                $sender->sendMessage($this->prefix . "Frappez une entité pour l’enlever.");
                                return true;
                            }
                            $entity = $sender->getLevel()->getEntity((int) $args[0]);
                            if ($entity !== null) {
                                if ($entity instanceof npcEntity || $entity instanceof npcHuman) {
                                    $this->getServer()->getPluginManager()->callEvent(new npcDeletionEvent($entity));
                                    $entity->close();
                                    $sender->sendMessage($this->prefix . "Entité supprimée.");
                                } else {
                                    $sender->sendMessage($this->prefix . "Cette entité n’est pas gérée par npc.");
                                }
                            } else {
                                $sender->sendMessage($this->prefix . "L’entité n’existe pas.");
                            }
                            return true;
                        case "edit":
                            if (!$sender->hasPermission("npc.edit")) {
                                $sender->sendMessage($this->noperm);
                                return true;
                            }
                            if (isset($args[0])) {
                                $level = $sender->getLevel();
                                $entity = $level->getEntity((int) $args[0]);
                                if ($entity !== null) {
                                    if ($entity instanceof npcEntity || $entity instanceof npcHuman) {
                                        if (isset($args[1])) {
                                            switch ($args[1]) {
                                                case "helm":
                                                case "helmet":
                                                case "head":
                                                case "hat":
                                                case "cap":
                                                    if ($entity instanceof npcHuman) {
                                                        if (isset($args[2])) {
                                                            $entity->getArmorInventory()->setHelmet(Item::fromString($args[2]));
                                                            $sender->sendMessage($this->prefix . "Casque mis à jour.");
                                                        } else {
                                                            $sender->sendMessage($this->prefix . "Veuillez saisir l'identifiant de l'item.");
                                                        }
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "Cette entité ne peut pas porter d’armure.");
                                                    }
                                                    return true;
                                                case "chest":
                                                case "shirt":
                                                case "chestplate":
                                                    if ($entity instanceof npcHuman) {
                                                        if (isset($args[2])) {
                                                            $entity->getArmorInventory()->setChestplate(Item::fromString($args[2]));
                                                            $sender->sendMessage($this->prefix . "Chestplate mis à jour.");
                                                        } else {
                                                            $sender->sendMessage($this->prefix . "Veuillez saisir l'identifiant de l'item.");
                                                        }
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "Cette entité ne peut pas porter d’armure.");
                                                    }
                                                    return true;
                                                case "pants":
                                                case "legs":
                                                case "leggings":
                                                    if ($entity instanceof npcHuman) {
                                                        if (isset($args[2])) {
                                                            $entity->getArmorInventory()->setLeggings(Item::fromString($args[2]));
                                                            $sender->sendMessage($this->prefix . "Leggings mis à jour.");
                                                        } else {
                                                            $sender->sendMessage($this->prefix . "Veuillez saisir l'identifiant de l'item.");
                                                        }
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "Cette entité ne peut pas porter d’armure.");
                                                    }
                                                    return true;
                                                case "feet":
                                                case "boots":
                                                case "shoes":
                                                    if ($entity instanceof npcHuman) {
                                                        if (isset($args[2])) {
                                                            $entity->getArmorInventory()->setBoots(Item::fromString($args[2]));
                                                            $sender->sendMessage($this->prefix . "Bottes mises à jour.");
                                                        } else {
                                                            $sender->sendMessage($this->prefix . "Veuillez saisir l'identifiant de l'item.");
                                                        }
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "Cette entité ne peut pas porter d’armure.");
                                                    }
                                                    return true;
                                                case "hand":
                                                case "item":
                                                case "holding":
                                                case "arm":
                                                case "held":
                                                    if ($entity instanceof npcHuman) {
                                                        if (isset($args[2])) {
                                                            $entity->getInventory()->setItemInHand(Item::fromString($args[2]));
                                                            $entity->getInventory()->sendHeldItem($entity->getViewers());
                                                            $sender->sendMessage($this->prefix . "Article mis à jour.");
                                                        } else {
                                                            $sender->sendMessage($this->prefix . "Veuillez saisir l'identifiant de l'item.");
                                                        }
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "Cette entité ne peut pas porter d’armure.");
                                                    }
                                                    return true;
                                                case "setskin":
                                                case "changeskin":
                                                case "editskin";
                                                case "skin":
                                                    if ($entity instanceof npcHuman) {
                                                        $entity->setSkin($sender->getSkin());
                                                        $entity->sendData($entity->getViewers());
                                                        $sender->sendMessage($this->prefix . "Skin mise à jour.");
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "Cette entité ne peut pas avoir de skin.");
                                                    }
                                                    return true;
                                                case "name":
                                                case "customname":
                                                    if (isset($args[2])) {
                                                        array_shift($args);
                                                        array_shift($args);
                                                        $entity->setNameTag(str_replace(["{color}", "{line}"], ["§", "\n"], trim(implode(" ", $args))));
                                                        $entity->sendData($entity->getViewers());
                                                        $sender->sendMessage($this->prefix . "Nom mis à jour.");
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "S’il vous plaît entrer un nom.");
                                                    }
                                                    return true;
                                                case "listname":
                                                case "nameonlist":
                                                case "menuname":
                                                    if ($entity instanceof npcHuman) {
                                                        if (isset($args[2])) {
                                                            $type = 0;
                                                            array_shift($args);
                                                            array_shift($args);
                                                            $input = trim(implode(" ", $args));
                                                            switch (strtolower($input)) {
                                                                case "remove":
                                                                case "":
                                                                case "disable":
                                                                case "off":
                                                                case "hide":
                                                                    $type = 1;
                                                            }
                                                            if ($type === 0) {
                                                                $entity->namedtag->setString("MenuName", $input);
                                                            } else {
                                                                $entity->namedtag->setString("MenuName", "");
                                                            }
                                                            $entity->respawnToAll();
                                                            $sender->sendMessage($this->prefix . "Nom du menu mis à jour.");
                                                        } else {
                                                            $sender->sendMessage($this->prefix . "S’il vous plaît entrer un nom de menu.");
                                                            return true;
                                                        }
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "Cette entité ne peut pas avoir un nom de menu.");
                                                    }
                                                    return true;
                                                case "addc":
                                                case "addcmd":
                                                case "addcommand":
                                                    if (isset($args[2])) {
                                                        array_shift($args);
                                                        array_shift($args);
                                                        $input = trim(implode(" ", $args));

                                                        $commands = $entity->namedtag->getCompoundTag("Commands") ?? new CompoundTag("Commands");

                                                        if ($commands->hasTag($input)) {
                                                            $sender->sendMessage($this->prefix . "Cette commande a déjà été ajoutée.");
                                                            return true;
                                                        }
                                                        $commands->setString($input, $input);
                                                        $entity->namedtag->setTag($commands); //in case a new CompoundTag was created
                                                        $sender->sendMessage($this->prefix . "Commande ajoutée.");
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "S’il vous plaît entrer une commande.");
                                                    }
                                                    return true;
                                                case "delc":
                                                case "delcmd":
                                                case "delcommand":
                                                case "removecommand":
                                                    if (isset($args[2])) {
                                                        array_shift($args);
                                                        array_shift($args);
                                                        $input = trim(implode(" ", $args));

                                                        $commands = $entity->namedtag->getCompoundTag("Commands") ?? new CompoundTag("Commands");

                                                        $commands->removeTag($input);
                                                        $entity->namedtag->setTag($commands); //in case a new CompoundTag was created
                                                        $sender->sendMessage($this->prefix . "Command removed.");
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "Please enter a command.");
                                                    }
                                                    return true;
                                                case "listcommands":
                                                case "listcmds":
                                                case "listcs":
                                                    $commands = $entity->namedtag->getCompoundTag("Commands");
                                                    if ($commands !== null and $commands->getCount() > 0) {
                                                        $id = 0;

                                                        /** @var StringTag $stringTag */
                                                        foreach ($commands as $stringTag) {
                                                            $id++;
                                                            $sender->sendMessage(TextFormat::GREEN . "[" . TextFormat::YELLOW . "S" . TextFormat::GREEN . "] " . TextFormat::YELLOW . $id . ". " . TextFormat::GREEN . $stringTag->getValue() . "\n");
                                                        }
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "That entity does not have any commands.");
                                                    }
                                                    return true;
                                                case "block":
                                                case "tile":
                                                case "blockid":
                                                case "tileid":
                                                    if (isset($args[2])) {
                                                        if ($entity instanceof npcFallingSand) {
                                                            $data = explode(":", $args[2]);
                                                            //haxx: we shouldn't use toStaticRuntimeId() because it's internal, but there isn't really any better option at the moment
                                                            $entity->getDataPropertyManager()->setInt(Entity::DATA_VARIANT, BlockFactory::toStaticRuntimeId((int) ($data[0] ?? 1), (int) ($data[1] ?? 0)));
                                                            $entity->sendData($entity->getViewers());
                                                            $sender->sendMessage($this->prefix . "Block updated.");
                                                        } else {
                                                            $sender->sendMessage($this->prefix . "That entity is not a block.");
                                                        }
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "Please enter a value.");
                                                    }
                                                    return true;
                                                case "teleporthere":
                                                case "tphere":
                                                case "movehere":
                                                case "bringhere":
                                                    $entity->teleport($sender);
                                                    $sender->sendMessage($this->prefix . "Entité téléportée à vous.");
                                                    $entity->respawnToAll();
                                                    return true;
                                                case "teleportto":
                                                case "tpto":
                                                case "goto":
                                                case "teleport":
                                                case "tp":
                                                    $sender->teleport($entity);
                                                    $sender->sendMessage($this->prefix . "Vous avez téléporté à l’entité.");
                                                    return true;
                                                case "scale":
                                                case "size":
                                                    if (isset($args[2])) {
                                                        $scale = (float) $args[2];
                                                        $entity->getDataPropertyManager()->setFloat(Entity::DATA_SCALE, $scale);
                                                        $entity->sendData($entity->getViewers());
                                                        $sender->sendMessage($this->prefix . "Échelle mise à jour.");
                                                    } else {
                                                        $sender->sendMessage($this->prefix . "S’il vous plaît entrer une valeur.");
                                                    }
                                                    return true;
                                                default:
                                                    $sender->sendMessage($this->prefix . "Commande inconnue.");
                                                    return true;
                                            }
                                        } else {
                                            $sender->sendMessage($this->helpHeader);
                                            foreach ($this->editArgs as $msgArg) {
                                                $sender->sendMessage(str_replace("<eid>", $args[0], TextFormat::GREEN . " - " . $msgArg . "\n"));
                                            }
                                            return true;
                                        }
                                    } else {
                                        $sender->sendMessage($this->prefix . "Cette entité n’est pas gérée par npc.");
                                    }
                                } else {
                                    $sender->sendMessage($this->prefix . "L’entité n’existe pas.");
                                }
                                return true;
                            } else {
                                $sender->sendMessage($this->helpHeader);
                                foreach ($this->editArgs as $msgArg) {
                                    $sender->sendMessage(TextFormat::GREEN . " - " . $msgArg . "\n");
                                }
                                return true;
                            }
                        case "help":
                        case "?":
                            $sender->sendMessage($this->helpHeader);
                            foreach ($this->mainArgs as $msgArg) {
                                $sender->sendMessage(TextFormat::GREEN . " - " . $msgArg . "\n");
                            }
                            return true;
                        case "add":
                        case "make":
                        case "create":
                        case "spawn":
                        case "apawn":
                        case "spanw":
                            if (!$sender->hasPermission("npc.create")) {
                                $sender->sendMessage($this->noperm);
                                return true;
                            }
                            $type = array_shift($args);
                            $name = str_replace(["{color}", "{line}"], ["§", "\n"], trim(implode(" ", $args)));
                            if ($type === null || empty(trim($type))) {
                                $sender->sendMessage($this->prefix . "S’il vous plaît entrer un type d’entité.");
                                return true;
                            }
                            if (empty($name)) {
                                $name = $sender->getDisplayName();
                            }
                            $types = self::ENTITY_TYPES;
                            $aliases = self::ENTITY_ALIASES;
                            $chosenType = null;
                            foreach ($types as $t) {
                                if (strtolower($type) === strtolower($t)) {
                                    $chosenType = $t;
                                }
                            }
                            if ($chosenType === null) {
                                foreach ($aliases as $alias => $t) {
                                    if (strtolower($type) === strtolower($alias)) {
                                        $chosenType = $t;
                                    }
                                }
                            }
                            if ($chosenType === null) {
                                $sender->sendMessage($this->prefix . "Type d’entité invalide.");
                                return true;
                            }
                            $nbt = $this->makeNBT($chosenType, $sender, $name);
                            /** @var npcEntity $entity */
                            $entity = Entity::createEntity("NPC" . $chosenType, $sender->getLevel(), $nbt);
                            $this->getServer()->getPluginManager()->callEvent(new npcCreationEvent($entity, "npc" . $chosenType, $sender, npcCreationEvent::CAUSE_COMMAND));
                            $entity->spawnToAll();
                            $sender->sendMessage($this->prefix . $chosenType . " entité engendrée avec le nom " . TextFormat::WHITE . "\"" . TextFormat::BLUE . $name . TextFormat::WHITE . "\"" . TextFormat::GREEN . " and entity ID " . TextFormat::BLUE . $entity->getId());
                            return true;
                        default:
                            $sender->sendMessage($this->prefix . "[ §3/npc help §f]");
                            return true;
                    }
                } else {
                    $sender->sendMessage($this->prefix . "This command only works in game.");
                    return true;
                }
        }
        return true;
    }

    /**
     * @param string $type
     * @param Player $player
     * @param string $name
     *
     * @return CompoundTag
     */
    private function makeNBT($type, Player $player, string $name): CompoundTag {
        $nbt = Entity::createBaseNBT($player, null, $player->getYaw(), $player->getPitch());
        $nbt->setShort("Health", 1);
        $nbt->setTag(new CompoundTag("Commands", []));
        $nbt->setString("MenuName", "");
        $nbt->setString("CustomName", $name);
        $nbt->setString("npcVersion", $this->getDescription()->getVersion());
        if ($type === "Human") {
            $player->saveNBT();

            $inventoryTag = $player->namedtag->getListTag("Inventory");
            assert($inventoryTag !== null);
            $nbt->setTag(clone $inventoryTag);

            $skinTag = $player->namedtag->getCompoundTag("Skin");
            assert($skinTag !== null);
            $nbt->setTag(clone $skinTag);
        }
        return $nbt;
    }

    /**
     * @param EntityDamageEvent $event
     *
     * @ignoreCancelled true
     *
     * @return void
     */
    public function onEntityDamage(EntityDamageEvent $event): void {
        $entity = $event->getEntity();
        if ($entity instanceof npcEntity || $entity instanceof npcHuman) {
            $event->setCancelled(true);
            if (!$event instanceof EntityDamageByEntityEvent) {
                return;
            }
            $damager = $event->getDamager();
            if (!$damager instanceof Player) {
                return;
            }
            $this->getServer()->getPluginManager()->callEvent($event = new npcHitEvent($entity, $damager));
            if ($event->isCancelled()) {
                return;
            }
            $damagerName = $damager->getName();
            if (isset($this->hitSessions[$damagerName])) {
                if ($entity instanceof npcHuman) {
                    $entity->getInventory()->clearAll();
                }
                $entity->close();
                unset($this->hitSessions[$damagerName]);
                $damager->sendMessage($this->prefix . "Entity removed.");
                return;
            }
            if (isset($this->idSessions[$damagerName])) {
                $damager->sendMessage($this->prefix . "Entity ID: " . $entity->getId());
                unset($this->idSessions[$damagerName]);
                return;
            }

            if (($commands = $entity->namedtag->getCompoundTag("Commands")) !== null) {
                $server = $this->getServer();
                /** @var StringTag $stringTag */
                foreach ($commands as $stringTag) {
                    $server->dispatchCommand(new ConsoleCommandSender(), str_replace("{player}", '"' . $damagerName . '"', $stringTag->getValue()));
                }
            }
        }
    }

    /**
     * @param EntitySpawnEvent $ev
     *
     * @return void
     */
    public function onEntitySpawn(EntitySpawnEvent $ev): void {
        $entity = $ev->getEntity();
        if ($entity instanceof npcEntity || $entity instanceof npcHuman) {
            $clearLagg = $this->getServer()->getPluginManager()->getPlugin("ClearLagg");
            if ($clearLagg !== null) {
                $clearLagg->exemptEntity($entity);
            }
        }
    }

    /**
     * @param EntityMotionEvent $event
     *
     * @return void
     */
    public function onEntityMotion(EntityMotionEvent $event): void {
        $entity = $event->getEntity();
        if ($entity instanceof npcEntity || $entity instanceof npcHuman) {
            $event->setCancelled(true);
        }
    }
}
