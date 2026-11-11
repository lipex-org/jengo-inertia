<?php

/**
 * This file is part of Inertia.js Codeigniter 4.
 *
 * (c) 2023 Fab IT Hub <hello@fabithub.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Jengo\Inertia\Ssr;

use CodeIgniter\HTTP\CURLRequest;
use Exception;
use Inertia\Protocol\Ssr\HttpSsrGateway as ProtocolHttpSsrGateway;
use Jengo\Inertia\Config\Inertia;
use Jengo\Inertia\Extras\Gateway;

/**
 * @deprecated Use \Inertia\Protocol\Ssr\HttpSsrGateway directly. Will be removed in the official v1.0.0 release.
 */
class HttpGateway extends ProtocolHttpSsrGateway implements Gateway
{
    public function __construct(
        string $url = 'http://127.0.0.1:13714',
        float $timeout = 1.0,
        bool $throwOnError = false,
    ) {
        /** @var Inertia|null $config */
        $config = config('Inertia');
        $ssrUrl = $config?->ssrUrl ?? $url;

        parent::__construct(
            url: $ssrUrl,
            timeout: $timeout,
            throwOnError: $throwOnError,
        );
    }

    public function dispatch(array $page): ?Response
    {
        /** @var Inertia|null $config */
        $config = config('Inertia');

        if (!$config?->isSsrEnabled) {
            return null;
        }

        $url = str_replace('/render', '', $config->ssrUrl) . '/render';

        try {
            /** @var CURLRequest $client */
            $client = service('curlRequest');
            $apiCall = $client->setJSON($page)->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ],
            ]);
            $response = json_decode($apiCall->getBody(), true);
        } catch (Exception) {
            return null;
        }

        if (null === $response || !is_array($response) || !isset($response['body'])) {
            return null;
        }

        $head = isset($response['head']) && is_array($response['head'])
            ? implode("\n", $response['head'])
            : ($response['head'] ?? '');

        return new Response(
            $head,
            (string) $response['body'],
        );
    }
}
