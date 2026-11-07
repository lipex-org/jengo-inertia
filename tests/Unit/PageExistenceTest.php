<?php

declare(strict_types=1);

namespace Tests\Unit;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\UserAgent;
use Config\App;
use Config\Services;
use Jengo\Inertia\Config\Inertia as ConfigInertia;
use Jengo\Inertia\Inertia;
use Jengo\Inertia\ResponseFactory;
use Jengo\Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\AssertionFailedError;
use Tests\TestCase;

class PageExistenceTest extends TestCase
{
    private string $tempPagesDir;

    protected function setUp(): void
    {
        parent::setUp();
        Inertia::flushShared();

        // Create a temporary pages directory for testing
        $this->tempPagesDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'jengo_test_pages_' . uniqid();
        mkdir($this->tempPagesDir, 0777, true);

        $this->createInertiaRequest();
    }

    protected function tearDown(): void
    {
        // Cleanup temp directory
        $this->removeDirectory($this->tempPagesDir);
        Services::resetSingle('inertia');
        Services::resetSingle('request');
        parent::tearDown();
    }

    private function createInertiaRequest(): IncomingRequest
    {
        $config = new App();
        $config->baseURL = 'http://example.com/';
        $config->indexPage = '';
        $uri = new \CodeIgniter\HTTP\SiteURI($config, 'test');
        $userAgent = new UserAgent();
        $request = new IncomingRequest($config, $uri, 'php://input', $userAgent);
        $request->setHeader('X-Inertia', 'true');
        Services::injectMock('request', $request);

        return $request;
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir) ?: [];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

    public function testConfigDefaults(): void
    {
        $config = new ConfigInertia();

        $this->assertFalse($config->pages['ensure_page_exists']);
        $this->assertFalse($config->testing['ensure_page_exists']);
        $this->assertContains('vue', $config->pages['extensions']);
        $this->assertContains('tsx', $config->pages['extensions']);

        // Explicitly populated supported page paths
        $this->assertNotEmpty($config->pages['pages']);
        $this->assertIsArray($config->pages['pages']);

        // Check snake_case toggling
        $config->pages['ensure_page_exists'] = true;
        $this->assertTrue($config->pages['ensure_page_exists']);

        $config->testing['ensure_page_exists'] = true;
        $this->assertTrue($config->testing['ensure_page_exists']);
    }

    public function testPageExistsResolvesDirectAndExtensionMatches(): void
    {
        // Create sample page files
        file_put_contents($this->tempPagesDir . '/Welcome.vue', '<template>Welcome</template>');
        file_put_contents($this->tempPagesDir . '/Dashboard.tsx', 'export default function() {}');

        mkdir($this->tempPagesDir . '/Auth', 0777, true);
        file_put_contents($this->tempPagesDir . '/Auth/Login.svelte', '<script></script>');

        mkdir($this->tempPagesDir . '/Users', 0777, true);
        file_put_contents($this->tempPagesDir . '/Users/Index.vue', '<template>Users</template>');

        $config = new ConfigInertia();
        $config->pages['pages'] = [$this->tempPagesDir];

        $factory = (new ResponseFactory())->setConfig($config);

        // Exact match
        $this->assertTrue($factory->pageExists('Welcome'));
        $this->assertTrue($factory->pageExists('Dashboard'));

        // Case insensitivity / lowercase resolution
        $this->assertTrue($factory->pageExists('welcome'));
        $this->assertTrue($factory->pageExists('dashboard'));

        // Nested paths
        $this->assertTrue($factory->pageExists('Auth/Login'));
        $this->assertTrue($factory->pageExists('auth/login'));

        // Folder index resolution
        $this->assertTrue($factory->pageExists('Users'));
        $this->assertTrue($factory->pageExists('users'));

        // Non-existent component
        $this->assertFalse($factory->pageExists('NonExistent'));
        $this->assertFalse($factory->pageExists('Auth/Register'));
    }

    public function testRenderThrowsPageNotFoundExceptionWhenEnsurePageExistsIsEnabled(): void
    {
        $config = new ConfigInertia();
        $config->pages['ensure_page_exists'] = true;
        $config->pages['pages'] = [$this->tempPagesDir];

        $factory = (new ResponseFactory())->setConfig($config);

        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage('Inertia page component [MissingComponent] not found on disk.');

        $factory->render('MissingComponent');
    }

    public function testRenderDoesNotThrowWhenEnsurePageExistsIsDisabled(): void
    {
        $config = new ConfigInertia();
        $config->pages['ensure_page_exists'] = false;
        $config->pages['pages'] = [$this->tempPagesDir];

        $factory = (new ResponseFactory())->setConfig($config);

        $response = $factory->render('MissingComponent');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testRenderSucceedsWhenPageExistsAndEnsurePageExistsIsEnabled(): void
    {
        file_put_contents($this->tempPagesDir . '/ExistingPage.vue', '<template>Hi</template>');

        $config = new ConfigInertia();
        $config->pages['ensure_page_exists'] = true;
        $config->pages['pages'] = [$this->tempPagesDir];

        $factory = (new ResponseFactory())->setConfig($config);

        $response = $factory->render('ExistingPage');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testAssertableInertiaComponentExistenceAssertion(): void
    {
        file_put_contents($this->tempPagesDir . '/ActivePage.vue', '<template>Active</template>');

        $config = new ConfigInertia();
        $config->testing['ensure_page_exists'] = true;
        $config->testing['page_paths'] = [$this->tempPagesDir];

        $factory = (new ResponseFactory())->setConfig($config);
        Services::injectMock('inertia', $factory);

        // When component exists, assertion passes
        $assertableExisting = new AssertableInertia(
            component: 'ActivePage',
            props: ['user' => 'John'],
            url: '/active'
        );
        $assertableExisting->component('ActivePage');
        $this->assertTrue(true);

        // When component does not exist, assertion fails
        $assertableMissing = new AssertableInertia(
            component: 'NonExistentPage',
            props: [],
            url: '/missing'
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia page component file [NonExistentPage] does not exist on disk.');

        $assertableMissing->component('NonExistentPage');
    }

    public function testAssertableInertiaSkipsCheckWhenTestingEnsurePageExistsIsDisabled(): void
    {
        $config = new ConfigInertia();
        $config->testing['ensure_page_exists'] = false;
        $config->testing['page_paths'] = [$this->tempPagesDir];

        $factory = (new ResponseFactory())->setConfig($config);
        Services::injectMock('inertia', $factory);

        $assertableMissing = new AssertableInertia(
            component: 'MissingPageWithoutError',
            props: [],
            url: '/test'
        );

        // Does not throw despite component file not existing
        $assertableMissing->component('MissingPageWithoutError');
        $this->assertTrue(true);
    }
}
