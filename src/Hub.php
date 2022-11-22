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
use DecodeLabs\Exceptional;
use DecodeLabs\Genesis\Build;
use DecodeLabs\Genesis\Build\Manifest as BuildManifest;
use DecodeLabs\Genesis\Context;
use DecodeLabs\Genesis\Environment\Config as EnvConfig;
use DecodeLabs\Genesis\Hub as HubInterface;
use DecodeLabs\Genesis\Loader\Stack as StackLoader;
use DecodeLabs\Glitch;

class Hub implements HubInterface
{
    protected Dir $appDir;
    protected Context $context;

    public function __construct(
        Context $context,
        array $options
    ) {
        $this->context = $context;
        unset($options);

        if (false === ($dir = getcwd())) {
            throw Exceptional::Runtime('Unable to get current working directory');
        }

        $runDir = Atlas::dir($dir);
        $composerFile = $this->findComposerJson($runDir);
        $this->appDir = $composerFile->getParent() ?? $runDir;
    }


    /**
     * Find composer json
     */
    protected function findComposerJson(Dir $runDir): File
    {
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
     * Get application path
     */
    public function getApplicationPath(): string
    {
        return (string)getcwd();
    }

    /**
     * Get local data path
     */
    public function getLocalDataPath(): string
    {
        return sys_get_temp_dir();
    }

    /**
     * Get shared data path
     */
    public function getSharedDataPath(): string
    {
        return $this->getLocalDataPath();
    }


    /**
     * Get application name
     */
    public function getApplicationName(): string
    {
        return 'Clip';
    }

    /**
     * Load build info
     */
    public function loadBuild(): Build
    {
        return new Build(
            $this->context,
            $this->getApplicationPath()
        );
    }

    /**
     * Setup loaders
     */
    public function initializeLoaders(StackLoader $stack): void
    {
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
            ->setRunMode($this->context->environment->getMode())
            ->registerPathAliases([
                'app' => (string)$this->appDir,
                'vendor' => $this->appDir . '/vendor'
            ])
            ->registerAsErrorHandler();
    }

    /**
     * Load kernel
     */
    public function loadKernel(): Kernel
    {
        return new Kernel();
    }

    public function getBuildManifest(): ?BuildManifest
    {
        return null;
    }
}
