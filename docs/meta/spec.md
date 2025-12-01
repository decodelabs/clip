# Clip — Package Specification

> **Cluster:** `cli`
> **Language:** `php`
> **Milestone:** `m2`
> **Repo:** `https://github.com/decodelabs/clip`
> **Role:** Cli Runtime

This document describes the purpose, contracts, and design of **Clip** within the Decode Labs ecosystem.

It is aimed at:

- Developers **using** Clip in their own applications or libraries.
- Contributors **maintaining or extending** Clip.
- Tools and AI assistants that need to reason about its behaviour.

---

## 1. Overview

### 1.1 Purpose

Clip provides a CLI kernel integration for DecodeLabs Genesis, enabling the creation of comprehensive CLI-based applications. It serves as middleware that provides an out-of-the-box setup for implementing a Genesis-based CLI task runner. Clip integrates Commandment for command dispatching, Terminus for I/O, and Genesis for application bootstrapping to create a complete CLI runtime environment.

### 1.2 Non-Goals

Clip does **not**:

- Provide command definitions or actions — these are defined by applications using Clip
- Handle command-line argument parsing directly — this is delegated to Commandment
- Provide web or HTTP interfaces — it is CLI-only
- Manage application state or persistence beyond the CLI session
- Provide built-in commands or utilities — it's a framework for building CLI applications

---

## 2. Role in the Ecosystem

### 2.1 Cluster & Positioning

- **Cluster:** `cli` (see Chorus taxonomy)
- Clip is a CLI runtime package that bridges Genesis (application framework) with Commandment (command dispatching) and Terminus (CLI I/O). It sits in the CLI cluster alongside Commandment and Terminus, providing the integration layer that makes Genesis applications runnable as CLI tools. It depends on many foundational packages (Archetype, Atlas, Coercion, Commandment, Exceptional, Genesis, Glitch, Hatch, Kingdom, Monarch, Pandora, Terminus) to provide a complete CLI application framework.

### 2.2 Typical Usage Contexts

Typical places Clip appears:

- CLI task runners and build tools
- Development tooling and automation scripts
- Application management commands (e.g., database migrations, cache clearing)
- Code generation tools
- Package management utilities
- CI/CD automation scripts

Clip is intended to be used whenever a Genesis-based application needs to expose CLI commands for tasks, utilities, or administrative operations.

---

## 3. Public Surface

> This section focuses on the conceptual API, not every symbol.

### 3.1 Key Types

The primary public types are:

- `DecodeLabs\Clip\Hub`
  Abstract Genesis Hub implementation that provides CLI-specific initialization. Handles path resolution, container setup, Glitch error handling registration, and Kingdom runtime configuration. Applications extend this class to create their own Hub.

- `DecodeLabs\Clip`
  Main CLI dispatcher service that extends Commandment's `Dispatcher`. Provides the `run()` method for executing actions and handles error reporting. Implements Kingdom's `Service` interface for dependency injection.

- `DecodeLabs\Kingdom\Runtime\Clip`
  Kingdom Runtime implementation for CLI mode. Handles signal processing, command-line argument parsing, and application lifecycle (initialize, run, shutdown). Manages exit codes based on command execution results.

- `DecodeLabs\Clip\Action\GenerateFileTrait`
  Trait for actions that generate files from templates. Provides common functionality for file generation with overwrite confirmation and check mode.

### 3.2 Main Entry Points

The main usage pattern is through extending `Hub`:

```php
use DecodeLabs\Clip\Hub as ClipHub;

class Hub extends ClipHub
{
    public function initializePlatform(): void
    {
        parent::initializePlatform();
        // Custom initialization
    }
}
```

The Hub is then bootstrapped via Genesis:

```php
use DecodeLabs\Genesis\Bootstrap\Bin as BinBootstrap;

new BinBootstrap(Hub::class)->run();
```

---

## 4. Dependencies

### 4.1 Decode Labs

- `decodelabs/archetype` (required)
  Used for class resolution and mapping action interfaces to implementations.

- `decodelabs/atlas` (required)
  Used for filesystem operations to locate composer.json files and resolve paths.

- `decodelabs/coercion` (required)
  Used for type coercion of command-line arguments.

- `decodelabs/commandment` (required)
  Used for command dispatching and action execution. Clip extends Commandment's `Dispatcher`.

- `decodelabs/exceptional` (required)
  Used for exception handling throughout the package.

- `decodelabs/genesis` (required)
  Used as the application framework. Clip provides a Hub implementation for Genesis.

- `decodelabs/glitch` (required)
  Used for error handling and exception logging. Registered as the error handler during platform initialization.

- `decodelabs/hatch` (required)
  Used for file template generation in the `GenerateFileTrait`.

- `decodelabs/kingdom` (required)
  Used for service container and application structure. Clip provides a Kingdom implementation and Runtime.

- `decodelabs/monarch` (required)
  Used for path management and service location.

- `decodelabs/pandora` (required)
  Used as the PSR-11 dependency injection container.

- `decodelabs/terminus` (required)
  Used for CLI I/O operations (reading input, writing output, error reporting).

### 4.2 External

- `composer-runtime-api` (required)
  Used for accessing Composer's runtime API (e.g., `$_composer_autoload_path`).

### 4.3 Optional Integrations

- `decodelabs/veneer` (optional)
  Detected at runtime if installed, used for static facade support. Clip sets the Veneer container if available.

---

## 5. Behaviour & Contracts

### 5.1 Invariants

- The Hub always resolves paths relative to composer.json files
- Glitch is always registered as the error handler during platform initialization
- The Kingdom Runtime is always set to `ClipRuntime` for CLI mode
- Command execution results map to exit codes: `true` → 0, `false` → 1, `null` → 1
- Signal handlers (SIGTERM, SIGINT, SIGQUIT) are registered if `pcntl_signal` is available
- The CLI runtime always sets `set_time_limit(0)` to allow long-running commands

### 5.2 Input & Output Contracts

**Input:**
- `Hub::__construct(Genesis $genesis, ?AnalysisMode $analysisMode)`
  - Accepts Genesis instance and optional analysis mode
  - Automatically resolves paths from Monarch and composer.json locations
  - Creates Pandora container and Archetype instance

- `Clip::run(string $action, string ...$args): bool`
  - Accepts action name and variable arguments
  - Creates a Commandment Request and dispatches it
  - Returns boolean indicating success/failure

- `Runtime\Clip::run(): void`
  - Reads command-line arguments from `$_SERVER['argv']`
  - Executes the Clip service with parsed arguments
  - Handles exceptions and sets exit codes

**Output:**
- All I/O operations go through Terminus `Session`
- Error messages are written to error stream
- Exit codes follow standard Unix conventions (0 = success, non-zero = failure)
- Verbose mode can be enabled with `-v` or `--verbose` flag

### 5.3 Path Resolution

Clip resolves several path types:
- **Root**: Project root directory (found via composer.json traversal)
- **Run**: Same as root for CLI applications
- **Subject Root**: Application directory (where the executing composer.json is located)
- **Working**: Current working directory
- **Local Data**: System temp directory
- **Shared Data**: System temp directory

### 5.4 Error Handling

- Command not found errors are caught and displayed with error message
- Command execution exceptions are logged via Monarch
- Verbose mode (`-v`) causes exceptions to be re-thrown for debugging
- Non-verbose mode displays error message and file:line information
- Exit codes are set based on execution result

---

## 6. Error Handling

- `Clip::runAction()` catches `CommandNotFoundException` and displays user-friendly error message
- `Clip::runAction()` catches `CommandmentException` and either re-throws (verbose) or displays error message
- `Runtime\Clip::run()` catches `Throwable` and handles with error reporting
- Exceptions are logged via `Monarch::logException()` before display
- Signal handlers set exit codes (128 + signal number) and trigger shutdown
- Missing action arguments result in error message and exit code 1

---

## 7. Configuration & Extensibility

### 7.1 Hub Extension

Applications extend `Hub` to customize initialization:

```php
class Hub extends ClipHub
{
    public function initializePlatform(): void
    {
        parent::initializePlatform();
        // Custom platform initialization
    }
}
```

### 7.2 Action Mapping

Actions are mapped using Archetype:

```php
$archetype->map(ActionInterface::class, Action::class);
```

### 7.3 Genesis Configuration

The Hub is registered in `composer.json`:

```json
{
    "extra": {
        "genesis": {
            "hub": "MyApp\\Hub"
        }
    }
}
```

### 7.4 Runtime Customization

The Kingdom Runtime can be customized by overriding `loadKingdom()` in the Hub, though the default `ClipRuntime` is typically sufficient.

---

## 8. Interactions with Other Packages

### 8.1 Genesis

Clip provides a Hub implementation for Genesis. The Hub handles Genesis lifecycle methods including path resolution, container setup, and platform initialization.

### 8.2 Commandment

Clip extends Commandment's `Dispatcher` to provide command execution. Actions are defined using Commandment's `Action` interface and executed through Commandment's request/response system.

### 8.3 Terminus

Clip uses Terminus `Session` for all CLI I/O operations. The Session is injected into the Clip service and used for reading input, writing output, and error reporting.

### 8.4 Glitch

Clip registers Glitch as the error handler during platform initialization. This ensures all errors and exceptions are handled consistently with proper logging and display.

### 8.5 Kingdom

Clip provides a Kingdom implementation that sets the Runtime to `ClipRuntime` for CLI mode. This integrates Clip with Kingdom's service container and application structure.

### 8.6 Monarch

Clip uses Monarch for path management and service location. Paths are resolved from Monarch's path registry.

### 8.7 Pandora

Clip uses Pandora as the PSR-11 dependency injection container. The container is created in the Hub and used throughout the application.

---

## 9. Usage Examples

### 9.1 Basic Hub Setup

```php
namespace MyApp;

use DecodeLabs\Clip\Hub as ClipHub;
use DecodeLabs\Archetype;
use DecodeLabs\Commandment\Action as ActionInterface;
use MyApp\Action;

class Hub extends ClipHub
{
    public function initializePlatform(): void
    {
        parent::initializePlatform();

        $archetype = $this->container->get(Archetype::class);
        $archetype->map(ActionInterface::class, Action::class);
    }
}
```

### 9.2 Bootstrap Script

```php
namespace MyApp;

use DecodeLabs\Genesis\Bootstrap\Bin as BinBootstrap;
use MyApp\Hub;

require_once $_composer_autoload_path ?? __DIR__ . '/../vendor/autoload.php';
new BinBootstrap(Hub::class)->run();
```

### 9.3 Action Definition

```php
namespace MyApp\Action;

use DecodeLabs\Commandment\Action;
use DecodeLabs\Commandment\Request;
use DecodeLabs\Terminus\Session;

class MyAction implements Action
{
    public function __construct(
        private Session $io
    ) {
    }

    public function execute(
        Request $request
    ): bool {
        $this->io->writeLine('Hello world');
        return true;
    }
}
```

### 9.4 File Generation Action

```php
namespace MyApp\Action;

use DecodeLabs\Clip\Action\GenerateFileTrait;
use DecodeLabs\Atlas\File;
use DecodeLabs\Hatch\FileTemplate;
use DecodeLabs\Commandment\Request;
use DecodeLabs\Terminus\Session;

class GenerateConfig implements Action
{
    use GenerateFileTrait;

    protected function getTargetFile(): File
    {
        return Atlas::getFile('config.php');
    }

    protected function getTemplate(): FileTemplate
    {
        return new FileTemplate('config.template.php');
    }
}
```

---

## 10. Implementation Notes (for Contributors)

### 10.1 Path Resolution

The Hub resolves paths by:
1. Finding composer.json files by traversing up the directory tree
2. Using Monarch's path registry as the base
3. Setting root to the project root (where composer.json is found)
4. Setting subject root to the application directory

### 10.2 Container Setup

The Hub creates a Pandora container and:
- Registers Archetype for class resolution
- Sets Veneer container if available
- Provides container to Kingdom for service resolution

### 10.3 Signal Handling

The Runtime registers signal handlers for:
- SIGTERM (termination request)
- SIGINT (interrupt, typically Ctrl+C)
- SIGQUIT (quit signal)

Handlers set exit codes (128 + signal number) and trigger graceful shutdown.

### 10.4 Error Reporting

Error reporting follows this flow:
1. Exceptions are caught in `Clip::runAction()` or `Runtime\Clip::run()`
2. Exceptions are logged via `Monarch::logException()`
3. If verbose mode is enabled, exception is re-thrown
4. Otherwise, error message and file:line are displayed
5. Exit code is set based on result

### 10.5 Exit Code Mapping

Exit codes are mapped as:
- `true` → 0 (success)
- `false` → 1 (failure)
- `null` → 1 (failure)
- Integer → used directly (e.g., 128 + signal number)

---

## 11. Testing & Quality

- **Code Quality Score:** 4/5
- **README Quality Score:** 3/5
- **Documentation Score:** 0/5 (this spec)
- **Test Coverage Score:** 0/5

See `composer.json` for supported PHP versions.

---

## 12. Roadmap & Future Ideas

- Enhanced path resolution options
- Support for interactive command prompts
- Built-in command discovery and listing
- Support for command aliases
- Enhanced error reporting and debugging tools
- Support for command groups and namespaces
- Add test coverage

---

## 13. References

- [Genesis Package](https://github.com/decodelabs/genesis) — Application framework
- [Commandment Package](https://github.com/decodelabs/commandment) — Command dispatching
- [Terminus Package](https://github.com/decodelabs/terminus) — CLI I/O
- [Kingdom Package](https://github.com/decodelabs/kingdom) — Service container
- [Chorus Package Index](../../../chorus/config/packages.json) — Ecosystem metadata

