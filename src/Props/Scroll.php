<?php

declare(strict_types=1);

/**
 * This file is part of Inertia.js Codeigniter 4.
 *
 * (c) 2023 Fab IT Hub <hello@fabithub.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Jengo\Inertia\Props;

use Inertia\Protocol\Props\Scroll as ProtocolScroll;

/**
 * @deprecated Use \Inertia\Protocol\Props\Scroll directly. Will be removed in the official v1.0.0 release.
 */
class Scroll extends ProtocolScroll
{
    public function __construct(
        mixed $data,
        string $pageName = 'page',
        ?int $previousPage = null,
        ?int $nextPage = null,
        int $currentPage = 1,
        bool $reset = false,
    ) {
        parent::__construct(
            data: $data,
            pageName: $pageName,
            previousPage: $previousPage,
            nextPage: $nextPage,
            currentPage: $currentPage,
            reset: $reset,
        );
    }
}
