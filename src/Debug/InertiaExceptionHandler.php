<?php

declare(strict_types=1);

/**
 * This file is part of Inertia.js Codeigniter 4.
 *
 * (c) 2026 JengoPHP <hello@jengophp.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Jengo\Inertia\Debug;

use Closure;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Debug\BaseExceptionHandler;
use CodeIgniter\Debug\ExceptionHandlerInterface;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\View\View;
use Config\Exceptions as ExceptionsConfig;
use Config\Paths;
use Jengo\Inertia\Config\Inertia as InertiaConfig;
use Jengo\Inertia\Extras\Http;
use Jengo\Inertia\Inertia;
use Jengo\Inertia\Response;
use Throwable;
use function Jengo\Base\vite_version;

/**
 * Exception handler tailored for Jengo Inertia applications.
 *
 * Evaluates HTTP status codes and exception instances to render
 * matching Inertia page components with appropriate status headers
 * for both SPA visits (X-Inertia) and direct browser navigation.
 */
class InertiaExceptionHandler extends BaseExceptionHandler implements ExceptionHandlerInterface
{
    use ResponseTrait;

    private ?RequestInterface $request = null;
    private ?ResponseInterface $response = null;
    protected ?InertiaConfig $inertiaConfig = null;

    public function __construct(ExceptionsConfig $config, ?InertiaConfig $inertiaConfig = null)
    {
        parent::__construct($config);
        $this->inertiaConfig = $inertiaConfig;
    }

    /**
     * Determines the correct way to display the error.
     *
     * @param CLIRequest|IncomingRequest $request
     */
    public function handle(
        Throwable $exception,
        RequestInterface $request,
        ResponseInterface $response,
        int $statusCode,
        int $exitCode,
    ): void {
        $this->request  = $request;
        $this->response = $response;

        if ($request instanceof IncomingRequest) {
            try {
                $response->setStatusCode($statusCode);
            } catch (HTTPException) {
                $statusCode = 500;
                $response->setStatusCode($statusCode);
            }

            if (! headers_sent()) {
                header(
                    sprintf(
                        'HTTP/%s %s %s',
                        $request->getProtocolVersion(),
                        $response->getStatusCode(),
                        $response->getReasonPhrase(),
                    ),
                    true,
                    $statusCode,
                );
            }

            // 1. Non-HTML and non-Inertia requests (standard API endpoints)
            if (! str_contains($request->getHeaderLine('accept'), 'text/html') && ! Http::isInertiaRequest($request)) {
                $data = $this->isDisplayErrorsEnabled()
                    ? $this->collectVars($exception, $statusCode)
                    : '';

                if ($data !== '') {
                    $data = $this->sanitizeData($data);
                }

                $this->respond($data, $statusCode)->send();

                // @codeCoverageIgnoreStart
                if (ENVIRONMENT !== 'testing') {
                    exit($exitCode);
                }
                // @codeCoverageIgnoreEnd

                return;
            }

            // 2. Resolve configured Inertia error page
            /** @var InertiaConfig $inertiaConfig */
            $inertiaConfig = $this->inertiaConfig ?? config('Inertia') ?? new InertiaConfig();
            $component = $inertiaConfig->resolveErrorPage($statusCode);

            $shouldShowDebug = $statusCode >= 500
                && $this->isDisplayErrorsEnabled()
                && $inertiaConfig->showDebugInDevelopment;

            if ($component !== null && ! $shouldShowDebug) {
                $this->renderInertia($component, $exception, $request, $statusCode, $inertiaConfig);

                // @codeCoverageIgnoreStart
                if (ENVIRONMENT !== 'testing') {
                    exit($exitCode);
                }
                // @codeCoverageIgnoreEnd

                return;
            }
        }

        // 3. Fallback: standard CodeIgniter error views
        $addPath = ($request instanceof IncomingRequest ? 'html' : 'cli') . DIRECTORY_SEPARATOR;
        $path    = $this->viewPath . $addPath;
        $altPath = rtrim((new Paths())->viewDirectory, '\\/ ')
            . DIRECTORY_SEPARATOR . 'errors' . DIRECTORY_SEPARATOR . $addPath;

        $view    = $this->determineView($exception, $path, $statusCode);
        $altView = $this->determineView($exception, $altPath, $statusCode);

        $viewFile = null;
        if (is_file($path . $view)) {
            $viewFile = $path . $view;
        } elseif (is_file($altPath . $altView)) {
            $viewFile = $altPath . $altView;
        }

        $this->render($exception, $statusCode, $viewFile);

        // @codeCoverageIgnoreStart
        if (ENVIRONMENT !== 'testing') {
            exit($exitCode);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Renders an Inertia error component response.
     */
    protected function renderInertia(
        string $component,
        Throwable $exception,
        RequestInterface $request,
        int $statusCode,
        InertiaConfig $inertiaConfig,
    ): void {
        $props = [
            'status'  => $statusCode,
            'message' => $exception->getMessage() ?: (ResponseInterface::HTTP_STATUS_CODES[$statusCode] ?? 'An error occurred'),
        ];

        if ($this->isDisplayErrorsEnabled()) {
            $props['exception'] = $this->collectVars($exception, $statusCode);
        }

        $version = $inertiaConfig->version ?? (string) vite_version();
        $sharedProps = Inertia::getShared(null) ?: [];

        $responseObj = new Response($component, array_merge($sharedProps, $props), $version);
        $result = $responseObj->toResponse($request);

        if ($result instanceof View) {
            try {
                $body = view($inertiaConfig->rootView, $result->getData());
                $this->response = service('response')
                    ->setStatusCode($statusCode)
                    ->setHeader('Content-Type', 'text/html')
                    ->setBody($body);
            } catch (Throwable) {
                $pageJson = json_encode($result->getData()['page'] ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
                $body = '<!DOCTYPE html><html><head><meta charset="utf-8" /></head><body><div id="app" data-page="' . htmlspecialchars((string) $pageJson, ENT_QUOTES, 'UTF-8') . '"></div></body></html>';
                $this->response = service('response')
                    ->setStatusCode($statusCode)
                    ->setHeader('Content-Type', 'text/html')
                    ->setBody($body);
            }
        } else {
            $this->response = $result->setStatusCode($statusCode);
        }

        $this->response->send();
    }

    /**
     * Access the response built during error handling.
     */
    public function getHandledResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    /**
     * Determines the view to display based on the exception thrown, HTTP status
     * code, whether an HTTP or CLI request, etc.
     */
    protected function determineView(
        Throwable $exception,
        string $templatePath,
        int $statusCode = 500,
    ): string {
        $view = 'production.php';

        if ($this->isDisplayErrorsEnabled()) {
            $view = 'error_exception.php';
        }

        if ($exception instanceof PageNotFoundException) {
            return 'error_404.php';
        }

        $templatePath = rtrim($templatePath, '\\/ ') . DIRECTORY_SEPARATOR;

        if (is_file($templatePath . 'error_' . $statusCode . '.php')) {
            return 'error_' . $statusCode . '.php';
        }

        return $view;
    }

    protected function isDisplayErrorsEnabled(): bool
    {
        return in_array(
            strtolower((string) ini_get('display_errors')),
            ['1', 'true', 'on', 'yes'],
            true,
        );
    }

    /**
     * Sanitizes data to remove non-JSON-serializable values like resources and closures.
     *
     * @param array<int, bool> $seen Used internally to prevent infinite recursion
     */
    protected function sanitizeData(mixed $data, array &$seen = []): mixed
    {
        $type = gettype($data);

        switch ($type) {
            case 'resource':
            case 'resource (closed)':
                return '[Resource #' . (int) $data . ']';

            case 'array':
                $result = [];

                foreach ($data as $key => $value) {
                    $result[$key] = $this->sanitizeData($value, $seen);
                }

                return $result;

            case 'object':
                $oid = spl_object_id($data);
                if (isset($seen[$oid])) {
                    return '[' . $data::class . ' Object *RECURSION*]';
                }
                $seen[$oid] = true;

                if ($data instanceof Closure) {
                    return '[Closure]';
                }

                $result = [];

                foreach ((array) $data as $key => $value) {
                    $cleanKey          = preg_replace('/^\x00.*\x00/', '', (string) $key);
                    $result[$cleanKey] = $this->sanitizeData($value, $seen);
                }

                return $result;

            default:
                return $data;
        }
    }
}
