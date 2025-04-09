<?php

/**
 * @package Clip
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Clip;

use DecodeLabs\Atlas;
use DecodeLabs\Atlas\Dir;
use DecodeLabs\Atlas\File;
use DecodeLabs\Clip;
use DecodeLabs\Exceptional;
use DecodeLabs\Fluidity\CastTrait;
use DecodeLabs\Genesis\Bootstrap;
use DecodeLabs\Genesis\Bootstrap\Bin as BinBootstrap;
use DecodeLabs\Genesis\Build;
use DecodeLabs\Genesis\Build\Manifest as BuildManifest;
use DecodeLabs\Genesis\Context;
use DecodeLabs\Genesis\Environment\Config as EnvConfig;
use DecodeLabs\Genesis\Hub as HubInterface;
use DecodeLabs\Genesis\Loader\Stack as StackLoader;
use DecodeLabs\Glitch;
use DecodeLabs\Monarch;
use DecodeLabs\Pandora\Container;
use DecodeLabs\Veneer;

class Hub implements HubInterface
{
    use CastTrait;

    public string $applicationName {
        get => 'Clip';
    }

    public string $applicationPath {
        get => Monarch::$paths->root;
    }

    public ?BuildManifest $buildManifest {
        get => null;
    }

    protected Context $context;

    public function __construct(
        Context $context,
        Bootstrap $bootstrap
    ) {
        if(!($bootstrap instanceof BinBootstrap)) {
            throw Exceptional::InvalidArgument(
                'Bootstrap must be a DecodeLabs\\Genesis\\Bootstrap\\Bin'
            );
        }

        $this->context = $context;
        $workingDir = Atlas::dir($bootstrap->rootPath);
        $composerFile = $this->findComposerJson($workingDir);
        $appDir = $composerFile->getParent() ?? $workingDir;

        Monarch::$paths->root = $appDir->getPath();
        Monarch::$paths->run = $appDir->getPath();
        Monarch::$paths->working = $workingDir->getPath();
        Monarch::$paths->localData = sys_get_temp_dir();
        Monarch::$paths->sharedData = sys_get_temp_dir();
    }


    /**
     * Find composer json
     */
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

    /**
     * Load build info
     */
    public function loadBuild(): Build
    {
        return new Build(
            $this->context,
            Monarch::$paths->run
        );
    }

    /**
     * Setup loaders
     */
    public function initializeLoaders(
        StackLoader $stack
    ): void {
    }

    /**
     * Load env config
     */
    public function loadEnvironmentConfig(): EnvConfig
    {
        return new EnvConfig\Development();
    }

    /**
     * Initialize platform
     */
    public function initializePlatform(): void
    {
        // Setup Glitch
        Glitch::setStartTime($this->context->getstartTime())
            ->setRunMode(Monarch::getEnvironmentMode()->value)
            ->registerPathAliases([
                'app' => Monarch::$paths->root,
                'vendor' => Monarch::$paths->root . '/vendor'
            ])
            ->registerAsErrorHandler();

        // Setup Archetype
        Normalizer::ensureRegistered();

        if(Monarch::$container instanceof Container) {
            Monarch::$container->bindShared(
                Controller::class
            );
        }
    }

    /**
     * Load kernel
     */
    public function loadKernel(): Kernel
    {
        return new Kernel($this->context);
    }
}
