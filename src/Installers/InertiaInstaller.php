<?php

declare(strict_types=1);

namespace Jengo\Inertia\Installers;

use CodeIgniter\CLI\CLI;
use Jengo\Base\Installers\Contracts\AbstractInstaller;
use Jengo\Base\Tooling\Modifier\ClassModifier;
use Jengo\Base\Traits\HasClientAssets;
use function Jengo\Base\Support\arr;
use function Jengo\Base\Support\str;

class InertiaInstaller extends AbstractInstaller
{
    use HasClientAssets;

    private string $framework;
    private string $clientDir;
    private string $stubsDir;

    public static function name(): string
    {
        return 'inertia';
    }

    public static function description(): string
    {
        return 'Install Inertia.js with Vue, React, or Svelte';
    }

    public static function reasonForSkipping(): string
    {
        return 'Inertia.js may already be installed. Please check your package.json and app/Views/app.php files.';
    }

    public static function dependencies(): array
    {
        return ['vite', 'typescript'];
    }

    public function shouldRun(): bool
    {
        return file_exists(ROOTPATH . 'package.json');
    }

    public function install(): void
    {
        $this->addRun();
        $this->stubsDir = __DIR__ . '/../Publisher/Stubs';

        if (!$this->shouldRun()) {
            CLI::error('package.json not found. Please run "php spark jengo:install vite" first.');
            return;
        }

        $this->framework = $this->whichFrameworkToUse();
        $this->resolveClientDirectory();

        $canInstallDependencies = $this->wantsToInstallDependencies();
        $pm = null;

        if ($canInstallDependencies) {
            $pm = $this->selectNodeManager();
            CLI::write("Using package manager: {$pm->getManager()}", 'cyan');
        }

        $canUpdateHomeController = $this->wantsToUpdateHomeController();

        // Publish View
        $sourceView = "{$this->stubsDir}/View/root.php";
        $destView = $this->root . 'app/Views/app.php';

        if (!copy($sourceView, $destView)) {
            CLI::error("Failed to copy view file.");
        }

        // Publish Client Stubs
        $hasAuth = $this->wantsAuth();
        $stubType = $hasAuth ? 'WithAuth' : 'Default';

        $sourceStubDir = match ($this->framework) {
            'vue' => "{$this->stubsDir}/Client/Vue/{$stubType}",
            'react' => "{$this->stubsDir}/Client/React/{$stubType}",
            'svelte' => "{$this->stubsDir}/Client/Svelte/{$stubType}",
        };

        CLI::write("Publishing client stubs ({$stubType}) to {$this->clientDir}", 'yellow');

        $this->publish($sourceStubDir, $this->clientDir);

        // Update Vite Config
        $this->updateViteConfig();

        // Update Home Controller
        if ($canUpdateHomeController) {
            $this->updateHomeController();
        }

        // Update Filters
        $this->publishFilters();
        $this->updateFiltersConfig();

        // Publish Exceptions Config
        $this->publishExceptionsConfig();

        // Install Dependencies
        if ($canInstallDependencies && $pm) {
            $dependencies = $this->getDependencies($this->framework);

            if (!empty($dependencies['prod'])) {
                $this->run($pm->getAddCommand($dependencies['prod']));
            }
            if (!empty($dependencies['dev'])) {
                $this->run($pm->getAddCommand($dependencies['dev'], true));
            }
        }

        CLI::write("Inertia ({$this->framework}) installed successfully.", 'green');
    }

    private function whichFrameworkToUse(): string
    {
        $framework = CLI::getOption('framework');
        if ($framework && in_array($framework, ['vue', 'react', 'svelte'])) {
            return $framework;
        }

        return CLI::prompt(
            'Which framework do you use?',
            ['vue', 'react', 'svelte'],
            'in_list[vue,react,svelte]'
        );
    }

    private function resolveClientDirectory(): void
    {
        $this->ensureClientDirectory();

        $this->clientDir = 'resources/js/inertia';
    }

    private function wantsToUpdateHomeController(): bool
    {
        if (CLI::getOption('yes')) {
            return true;
        }

        return CLI::prompt('Do you want to update the Home Controller?', ['y', 'n'], 'in_list[y,n]') === 'y';
    }

    private function getDependencies(string $framework): array
    {
        return match ($framework) {
            'vue' => [
                'prod' => [
                    'vue',
                    '@inertiajs/vue3',
                ],
                'dev' => [
                    '@vitejs/plugin-vue',
                ],
            ],
            'react' => [
                'prod' => [
                    'react',
                    'react-dom',
                    '@inertiajs/react',
                ],
                'dev' => [
                    '@vitejs/plugin-react',
                    '@types/react-dom'
                ],
            ],
            'svelte' => [
                'prod' => [
                    'svelte',
                    '@inertiajs/svelte',
                ],
                'dev' => [
                    '@sveltejs/vite-plugin-svelte',
                ],
            ],
        };
    }

    private function updateViteConfig(): void
    {
        $configFile = ROOTPATH . 'vite.config.ts';
        if (!file_exists($configFile)) {
            $configFile = ROOTPATH . 'vite.config.js';
            if (!file_exists($configFile)) {
                CLI::error('vite.config.ts/js not found. Skipping config update.');
                return;
            }
        }

        $content = file_get_contents($configFile);

        $pluginImport = match ($this->framework) {
            'vue' => "import vue from '@vitejs/plugin-vue';",
            'react' => "import react from '@vitejs/plugin-react';",
            'svelte' => "import { svelte } from '@sveltejs/vite-plugin-svelte';",
        };

        $pluginUsage = match ($this->framework) {
            'vue' => "vue(),",
            'react' => "react(),",
            'svelte' => "svelte(),",
        };

        // Add Import
        if (!str_contains($content, $pluginImport)) {
            $content = preg_replace(
                "/(import .* from 'vite';)/",
                "$1\n$pluginImport",
                $content,
                1
            );
        }

        // Add Plugin
        if (!str_contains($content, trim($pluginUsage, ','))) {
            $content = preg_replace(
                "/(plugins:\s*\[)/",
                "$1\n        $pluginUsage",
                $content,
                1
            );
        }

        $this->writeFile($configFile, $content);
    }

    private function updateHomeController(): void
    {
        $this->publish("{$this->stubsDir}/Controllers", 'app/Controllers');
        CLI::write("Home Controller published.", 'green');
    }

    private function publishFilters(): void
    {
        $this->publish("{$this->stubsDir}/Filters", 'app/Filters');
        CLI::write("Inertia filter published.", 'green');
    }


    /**
     * Register the Inertia filter alias and add it to the global before/after filter lists
     * in app/Config/Filters.php using ClassModifier for AST-safe edits.
     */
    protected function updateFiltersConfig(): void
    {
        $path = APPPATH . 'Config/Filters.php';

        if (!file_exists($path)) {
            return;
        }

        try {
            $modifier = ClassModifier::fromFile($path);

            // 1. Add the filter alias: 'inertia' => \App\Filters\HandleInertiaRequests::class
            $modifier->mutateArrayProperty('aliases', function (array $aliases) {
                if (!isset($aliases['inertia'])) {
                    $aliases['inertia'] = \App\Filters\HandleInertiaRequests::class;
                }
                return $aliases;
            });

            // 2. Add 'inertia' to globals['before'] and globals['after']
            $modifier->mutateArrayProperty('globals', function (array $globals) {
                if (!in_array('inertia', $globals['before'] ?? [], true)) {
                    $globals['before'][] = 'inertia';
                }
                if (!in_array('inertia', $globals['after'] ?? [], true)) {
                    $globals['after'][] = 'inertia';
                }
                return $globals;
            });

            $modifier->saveTo($path);
        } catch (\Throwable $e) {
            CLI::write('[WARNING] Could not update Config/Filters.php automatically: ' . $e->getMessage());
            CLI::write('Please add the following to app/Config/Filters.php manually:');
            CLI::write("  \$aliases['inertia'] = \\App\\Filters\\HandleInertiaRequests::class;");
            CLI::write("  \$globals['before'][] = 'inertia';");
            CLI::write("  \$globals['after'][] = 'inertia';");
        }
    }

    private function wantsAuth(): bool
    {
        $auth = CLI::getOption('auth');
        if ($auth !== null) {
            return in_array($auth, ['y', 'yes', 'true', '1', true], true);
        }

        // If not specified via CLI, check if Shield is installed
        $shieldExists = class_exists('CodeIgniter\Shield\Auth') || file_exists(APPPATH . 'Config/Auth.php');

        // Use Shield existence as default choice
        $defaultAnswer = $shieldExists ? ['y', 'n'] : ['n', 'y'];

        return CLI::prompt('Do you want to include authentication scaffolding (Shield)?', $defaultAnswer, 'in_list[y,n]') === 'y';
    }

    public function publishExceptionsConfig(): void
    {
        $this->publish("{$this->stubsDir}/Config", 'app/Config');
        CLI::write('Exceptions config published.', 'green');
    }
}

