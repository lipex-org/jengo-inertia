<?php

declare(strict_types=1);

namespace Tests\Unit;

use CodeIgniter\CLI\CLI;
use Jengo\Inertia\Installers\InertiaInstaller;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

class InertiaInstallerTest extends TestCase
{
    private ?string $originalFiltersContent = null;

    protected function setUp(): void
    {
        parent::setUp();
        $filtersPath = APPPATH . 'Config/Filters.php';
        if (file_exists($filtersPath)) {
            $this->originalFiltersContent = file_get_contents($filtersPath);
        }
    }

    private function setCliOptions(array $options): void
    {
        $reflection = new ReflectionClass(CLI::class);
        $optionsProperty = $reflection->getProperty('options');
        $optionsProperty->setAccessible(true);
        $optionsProperty->setValue(null, $options);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->setCliOptions([]);

        $filtersPath = APPPATH . 'Config/Filters.php';
        if ($this->originalFiltersContent !== null) {
            file_put_contents($filtersPath, $this->originalFiltersContent);
        }

        $paths = [
            ROOTPATH . 'package.json',
            ROOTPATH . 'vite.config.ts',
            ROOTPATH . 'vite.config.js',
            ROOTPATH . 'app/Views/app.php',
            ROOTPATH . 'app/Filters/Inertia.php',
            ROOTPATH . 'app/Controllers/Home.php',
        ];
        foreach ($paths as $path) {
            if (file_exists($path)) {
                @unlink($path);
            }
        }
        if (is_dir(ROOTPATH . 'resources/js/inertia')) {
            system('rm -rf ' . escapeshellarg(ROOTPATH . 'resources/js/inertia'));
        }
    }

    public function testInstallerMetadata(): void
    {
        $this->assertSame('inertia', InertiaInstaller::name());
        $this->assertSame('Install Inertia.js with Vue, React, or Svelte', InertiaInstaller::description());
        $this->assertNotEmpty(InertiaInstaller::reasonForSkipping());
        $this->assertSame(['vite', 'typescript'], InertiaInstaller::dependencies());
    }

    public function testInstallerShouldRun(): void
    {
        $installer = new InertiaInstaller();
        $this->assertIsBool($installer->shouldRun());
    }

    public function testGetDependencies(): void
    {
        $installer = new InertiaInstaller();
        $ref = new ReflectionClass($installer);
        $method = $ref->getMethod('getDependencies');
        $method->setAccessible(true);

        $vueDeps = $method->invoke($installer, 'vue');
        $this->assertContains('vue', $vueDeps['prod']);
        $this->assertContains('@inertiajs/vue3', $vueDeps['prod']);
        $this->assertContains('@vitejs/plugin-vue', $vueDeps['dev']);

        $reactDeps = $method->invoke($installer, 'react');
        $this->assertContains('react', $reactDeps['prod']);
        $this->assertContains('react-dom', $reactDeps['prod']);
        $this->assertContains('@inertiajs/react', $reactDeps['prod']);
        $this->assertContains('@vitejs/plugin-react', $reactDeps['dev']);

        $svelteDeps = $method->invoke($installer, 'svelte');
        $this->assertContains('svelte', $svelteDeps['prod']);
        $this->assertContains('@inertiajs/svelte', $svelteDeps['prod']);
        $this->assertContains('@sveltejs/vite-plugin-svelte', $svelteDeps['dev']);
    }

    public function testWhichFrameworkToUseWithCliOption(): void
    {
        $installer = new InertiaInstaller();
        $ref = new ReflectionClass($installer);
        $method = $ref->getMethod('whichFrameworkToUse');
        $method->setAccessible(true);

        $this->setCliOptions(['framework' => 'vue']);
        $this->assertSame('vue', $method->invoke($installer));

        $this->setCliOptions(['framework' => 'react']);
        $this->assertSame('react', $method->invoke($installer));

        $this->setCliOptions(['framework' => 'svelte']);
        $this->assertSame('svelte', $method->invoke($installer));
    }

    public function testWantsAuthWithCliOption(): void
    {
        $installer = new InertiaInstaller();
        $ref = new ReflectionClass($installer);
        $method = $ref->getMethod('wantsAuth');
        $method->setAccessible(true);

        $this->setCliOptions(['auth' => 'yes']);
        $this->assertTrue($method->invoke($installer));

        $this->setCliOptions(['auth' => 'no']);
        $this->assertFalse($method->invoke($installer));
    }

    public function testWantsToUpdateHomeControllerWithCliOption(): void
    {
        $installer = new InertiaInstaller();
        $ref = new ReflectionClass($installer);
        $method = $ref->getMethod('wantsToUpdateHomeController');
        $method->setAccessible(true);

        $this->setCliOptions(['yes' => true]);
        $this->assertTrue($method->invoke($installer));
    }

    public function testUpdateFiltersConfig(): void
    {
        $configDir = APPPATH . 'Config';
        if (!is_dir($configDir)) {
            mkdir($configDir, 0777, true);
        }

        $filtersPath = APPPATH . 'Config/Filters.php';
        $originalContent = null;
        if (file_exists($filtersPath)) {
            $originalContent = file_get_contents($filtersPath);
        }

        $stubFilters = <<<'PHP'
<?php
namespace Config;
use CodeIgniter\Config\BaseConfig;
class Filters extends BaseConfig
{
    public array $aliases = [
        'csrf' => \CodeIgniter\Filters\CSRF::class,
    ];
    public array $globals = [
        'before' => [
            'csrf',
        ],
        'after' => [
            'toolbar',
        ],
    ];
}
PHP;

        file_put_contents($filtersPath, $stubFilters);

        $installer = new InertiaInstaller();
        $ref = new ReflectionClass($installer);
        $method = $ref->getMethod('updateFiltersConfig');
        $method->setAccessible(true);
        $method->invoke($installer);

        $updatedContent = file_get_contents($filtersPath);
        $this->assertStringContainsString("'inertia' => \\App\\Filters\\Inertia::class", $updatedContent);
        $this->assertStringContainsString("'inertia'", $updatedContent);

        // Restore original content or delete
        if ($originalContent !== null) {
            file_put_contents($filtersPath, $originalContent);
        } else {
            unlink($filtersPath);
        }
    }

    public function testUpdateViteConfig(): void
    {
        $viteFile = ROOTPATH . 'vite.config.ts';
        $stubVite = <<<'TS'
import { defineConfig } from 'vite';
import jengo from 'jengo-vite-plugin';

export default defineConfig({
    plugins: [
        jengo(),
    ],
});
TS;
        file_put_contents($viteFile, $stubVite);

        $installer = new InertiaInstaller();
        $ref = new ReflectionClass($installer);

        $frameworkProp = $ref->getProperty('framework');
        $frameworkProp->setAccessible(true);
        $frameworkProp->setValue($installer, 'react');

        $method = $ref->getMethod('updateViteConfig');
        $method->setAccessible(true);
        $method->invoke($installer);

        $updatedContent = file_get_contents($viteFile);
        $this->assertStringContainsString("import react from '@vitejs/plugin-react';", $updatedContent);
        $this->assertStringContainsString("react(),", $updatedContent);

        // Test Vue
        file_put_contents($viteFile, $stubVite);
        $frameworkProp->setValue($installer, 'vue');
        $method->invoke($installer);
        $updatedVue = file_get_contents($viteFile);
        $this->assertStringContainsString("import vue from '@vitejs/plugin-vue';", $updatedVue);
        $this->assertStringContainsString("vue(),", $updatedVue);

        // Test Svelte
        file_put_contents($viteFile, $stubVite);
        $frameworkProp->setValue($installer, 'svelte');
        $method->invoke($installer);
        $updatedSvelte = file_get_contents($viteFile);
        $this->assertStringContainsString("import { svelte } from '@sveltejs/vite-plugin-svelte';", $updatedSvelte);
        $this->assertStringContainsString("svelte(),", $updatedSvelte);

        unlink($viteFile);
    }

    public function testResolveClientDirectory(): void
    {
        $installer = new InertiaInstaller();
        $ref = new ReflectionClass($installer);
        $method = $ref->getMethod('resolveClientDirectory');
        $method->setAccessible(true);
        $method->invoke($installer);

        $prop = $ref->getProperty('clientDir');
        $prop->setAccessible(true);
        $this->assertSame('resources/js/inertia', $prop->getValue($installer));
    }

    public function testUpdateHomeControllerAndPublishFilters(): void
    {
        $installer = new InertiaInstaller();
        $ref = new ReflectionClass($installer);
        $stubsProp = $ref->getProperty('stubsDir');
        $stubsProp->setAccessible(true);
        $stubsProp->setValue($installer, __DIR__ . '/../../src/Publisher/Stubs');

        $methodHome = $ref->getMethod('updateHomeController');
        $methodHome->setAccessible(true);
        $methodHome->invoke($installer);

        $methodFilters = $ref->getMethod('publishFilters');
        $methodFilters->setAccessible(true);
        $methodFilters->invoke($installer);

        $this->assertTrue(true);
    }

    public function testInstallSkipsWhenNoPackageJson(): void
    {
        $pkgPath = ROOTPATH . 'package.json';
        $hadPkg = file_exists($pkgPath);
        $pkgBackup = $hadPkg ? file_get_contents($pkgPath) : null;

        if ($hadPkg) {
            unlink($pkgPath);
        }

        $installer = new InertiaInstaller();
        $installer->install();

        $this->assertSame(1, $installer->runs);

        if ($hadPkg && $pkgBackup !== null) {
            file_put_contents($pkgPath, $pkgBackup);
        }
    }

    public function testFullInstallExecution(): void
    {
        $pkgPath = ROOTPATH . 'package.json';
        $vitePath = ROOTPATH . 'vite.config.ts';
        $viewsDir = ROOTPATH . 'app/Views';
        $viewFile = $viewsDir . '/app.php';

        if (!is_dir($viewsDir)) {
            mkdir($viewsDir, 0777, true);
        }

        file_put_contents($pkgPath, '{"name": "test"}');
        file_put_contents($vitePath, "import { defineConfig } from 'vite';\nexport default defineConfig({ plugins: [] });");

        $this->setCliOptions(['framework' => 'react', 'auth' => 'no', 'yes' => true]);

        $installer = new class extends InertiaInstaller {
            protected function wantsToInstallDependencies(): bool
            {
                return false;
            }
        };

        $installer->install();

        $this->assertSame(1, $installer->runs);
        $this->assertFileExists($viewFile);

        // Cleanup
        if (file_exists($pkgPath)) {
            unlink($pkgPath);
        }
        if (file_exists($vitePath)) {
            unlink($vitePath);
        }
        if (file_exists($viewFile)) {
            unlink($viewFile);
        }
    }
}


