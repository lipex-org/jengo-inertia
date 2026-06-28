<?php

declare(strict_types=1);

namespace Jengo\Inertia\Installers;

use CodeIgniter\CLI\CLI;
use Jengo\Base\Installers\Contracts\AbstractInstaller;
use function Jengo\Base\Support\arr;
use function Jengo\Base\Support\str;

class InertiaInstaller extends AbstractInstaller
{
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
            $pm = $this->node(); // Use existing if detected, otherwise fallback in PackageManager
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

    private function whereToPlaceClientFiles(): string
    {
        $dir = CLI::getOption('client-dir');
        if ($dir) {
            return $dir;
        }

        return CLI::prompt('Where should we place the client files (relative to the ROOTPATH)? (e.g. app) ', 'resources/js', 'required');
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


    protected function updateFiltersConfig(): void
    {
        $path = APPPATH . 'Config/Filters.php';

        if (!file_exists($path)) {
            return;
        }

        $content = file_get_contents($path);

        // 1. Add the alias to the $aliases array if it doesn't exist
        if (!str_contains($content, "'inertia' => \\App\\Filters\\Inertia::class")) {
            // Find the public $aliases = [ line
            $aliasPattern = '/(public\s+array\s+\$aliases\s*=\s*\[)/';
            $aliasReplacement = "$1\n        'inertia' => \\App\\Filters\\Inertia::class,";
            $content = preg_replace($aliasPattern, $aliasReplacement, $content);
        }

        // 2. Add 'inertia' to the globals -> before array
        // Looks for 'before' => [ and ensures 'inertia' isn't already added
        if (preg_match('/\'before\'\s*=>\s*\[([^\]]*)/s', $content, $matches)) {
            if (!str_contains($matches[1], "'inertia'")) {
                $content = preg_replace(
                    '/(\'before\'\s*=>\s*\[)/',
                    "$1\n            'inertia',",
                    $content
                );
            }
        }

        // 3. Add 'inertia' to the globals -> after array
        // Looks for 'after' => [ and ensures 'inertia' isn't already added
        if (preg_match('/\'after\'\s*=>\s*\[([^\]]*)/s', $content, $matches)) {
            if (!str_contains($matches[1], "'inertia'")) {
                $content = preg_replace(
                    '/(\'after\'\s*=>\s*\[)/',
                    "$1\n            'inertia',",
                    $content
                );
            }
        }

        // Save the updated configuration back to the file
        file_put_contents($path, $content);
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
}

