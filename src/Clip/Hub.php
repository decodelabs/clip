<?php

/**
 * @package Clip
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Clip;

use DecodeLabs\Archetype;
use DecodeLabs\Atlas;
use DecodeLabs\Atlas\Dir;
use DecodeLabs\Atlas\File;
use DecodeLabs\Genesis;
use DecodeLabs\Genesis\AnalysisMode;
use DecodeLabs\Genesis\Build;
use DecodeLabs\Genesis\Build\Manifest as BuildManifest;
use DecodeLabs\Genesis\Environment\Config as EnvConfig;
use DecodeLabs\Genesis\Hub as HubInterface;
use DecodeLabs\Glitch;
use DecodeLabs\Kingdom;
use DecodeLabs\Kingdom\Runtime;
use DecodeLabs\Kingdom\Runtime\Clip as ClipRuntime;
use DecodeLabs\KingdomTrait;
use DecodeLabs\Monarch;
use DecodeLabs\Pandora\Container;
use DecodeLabs\Veneer;

class Hub implements HubInterface
{
    public ?BuildManifest $buildManifest {
        get => null;
    }

    protected Container $container;
    protected Archetype $archetype;

    public function __construct(
        protected Genesis $genesis,
        protected ?AnalysisMode $analysisMode = null
    ) {
        $workingDir = Atlas::getDir((string)getcwd());
        $composerFile = $this->findComposerJson($workingDir);
        $appDir = $composerFile->getParent() ?? $workingDir;

        $paths = Monarch::getPaths();
        $paths->root = $appDir->path;
        $paths->run = $appDir->path;
        $paths->working = $workingDir->path;
        $paths->localData = sys_get_temp_dir();
        $paths->sharedData = sys_get_temp_dir();

        $this->container = new Container();

        if (class_exists(Veneer::class)) {
            Veneer::setContainer($this->container);
        }

        $this->archetype = $this->container->getOrCreate(Archetype::class);
    }


    protected function findComposerJson(
        Dir $runDir
    ): File {
        $dir = $runDir;

        do {
            $file = $dir->getFile('composer.json');

            if ($file->exists()) {
                return $file;
            }

            $dir = $dir->getParent();
        } while ($dir !== null);

        return $runDir->getFile('composer.json');
    }

    public function loadBuild(): Build
    {
        return new Build(
            $this->genesis,
            Monarch::getPaths()->run
        );
    }

    public function initializeLoaders(): void
    {
    }

    public function loadEnvironmentConfig(): EnvConfig
    {
        return new EnvConfig\Development();
    }

    public function initializePlatform(): void
    {
        // Setup Glitch
        $glitch = $this->container->get(Glitch::class);
        $glitch->setStartTime(Monarch::getStartTime());
        $glitch->registerAsErrorHandler();
    }

    public function loadKingdom(): Kingdom
    {
        return new class($this->container) implements Kingdom {
            use KingdomTrait;

            public string $name { get => 'Clip application'; }

            public function initialize(): void
            {
                $this->container->setType(
                    Runtime::class,
                    ClipRuntime::class
                );
            }
        };
    }
}
