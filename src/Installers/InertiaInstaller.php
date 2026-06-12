<?php

declare(strict_types=1);

namespace Jengo\Inertia\Installers;

use CodeIgniter\CLI\CLI;
use Jengo\Base\Installers\Contracts\AbstractInstaller;

class InertiaInstaller extends AbstractInstaller
{
    private string $framework;
    private string $clientDir;

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
        return ['vite'];
    }

    public function shouldRun(): bool
    {
        return file_exists(ROOTPATH . 'package.json');
    }

    public function install(): void
    {
        $this->addRun();

        if (!$this->shouldRun()) {
            CLI::error('package.json not found. Please run "php spark jengo:install vite" first.');
            return;
        }

        $this->framework = $this->whichFrameworkToUse();
        // resolve . and / as the root directory
        $this->resolveClientDirectory();

        $pm = $this->detectPackageManager();
        $dependencies = $this->getDependencies($this->framework);
        $canInstallDependencies = $this->wantsToInstallDependencies();
        $canUpdateHomeController = $this->wantsToUpdateHomeController();
        $stubsDir = __DIR__ . '/../Publisher/Stubs';

        if ($canInstallDependencies) {
            CLI::write("Using package manager: {$pm}", 'cyan');
        }

        // Publish View
        $sourceView = "{$stubsDir}/View/root.php";
        $destView = $this->root . 'app/Views/app.php';

        if (!copy($sourceView, $destView)) {
            CLI::error("Failed to copy view file.");
        }

        // Publish Client Stubs
        $sourceStubDir = match ($this->framework) {
            'vue' => "{$stubsDir}/Client/Vue",
            'react' => "{$stubsDir}/Client/React",
            'svelte' => "{$stubsDir}/Client/Svelte",
        };

        CLI::write("Publishing client stubs to {$this->clientDir}", 'yellow');

        $this->publish($sourceStubDir, $this->clientDir);

        // Update Vite Config
        $this->updateViteConfig();

        // Update Home Controller
        if ($canUpdateHomeController) {
            $this->updateHomeController();
        }

        // Install Dependencies
        if ($canInstallDependencies) {
            $this->run(
                $this->buildPackageManagerInstallCommand($pm, $dependencies)
            );
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

    private function wantsToInstallDependencies(): bool
    {
        if (CLI::getOption('yes')) {
            return true;
        }

        return CLI::prompt('Should we install the dependencies?', ['y', 'n'], 'in_list[y,n]') === 'y';
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
        $common = [];

        $specific = match ($framework) {
            'vue' => [
                'vue' => 'vue',
                'inertia-vue' => '@inertiajs/vue3',
                'vite-plugin-vue' => '@vitejs/plugin-vue',
            ],
            'react' => [
                'react' => 'react',
                'react-dom' => 'react-dom',
                'inertia-react' => '@inertiajs/react',
                'vite-plugin-react' => '@vitejs/plugin-react',
            ],
            'svelte' => [
                'svelte' => 'svelte',
                'inertia-svelte' => '@inertiajs/svelte',
                'vite-plugin-svelte' => '@sveltejs/vite-plugin-svelte',
            ],
        };

        return array_merge($common, $specific);
    }

    private function devDependencies(): array
    {
        return [
            'vite-plugin-vue',
            'vite-plugin-react',
            'vite-plugin-svelte',
        ];
    }

    private function buildPackageManagerInstallCommand(string $pm, array $dependencies): string
    {
        $deps = [];
        $devDeps = $this->devDependencies();

        foreach ($dependencies as $key => $name) {
            if (in_array($key, $devDeps)) {
                $deps[] = "-D {$name}";
            } else {
                $deps[] = $name;
            }
        }

        $deps = implode(' ', $deps);
        $baseCommand = $this->packageMangerInstallCommand($pm);

        return "{$baseCommand} {$deps}";
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
        $file = APPPATH . 'Controllers/Home.php';

        $content = <<<'PHP'
<?php

namespace App\Controllers;

use Jengo\Inertia\Inertia;

class Home extends BaseController
{
    public function index()
    {
        return Inertia::render('Welcome');
    }
}
PHP;

        $this->writeFile($file, $content);
        CLI::write("Home Controller updated.", 'green');
    }

    private function resolveClientDirectory(): void
    {
        $userInputDir = trim($this->whereToPlaceClientFiles(), '/');

        $dir = trim(
            $userInputDir === '.' ? 'Client' : "{$userInputDir}/Client",
            '/'
        );

        $this->clientDir = $dir === 'Client' ? strtolower($dir) : $dir;
    }
}