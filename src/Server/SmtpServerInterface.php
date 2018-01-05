<?php

declare(strict_types=1);

/*
 * This file is part of the Camelot Genkgo Mail bundle.
 *
 * (c) Gawain Lynch <gawain.lynch@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Camelot\GenkgoMailBundle\Server;

/**
 * @internal
 */
interface SmtpServerInterface
{
    const STARTED = 0;
    const STOPPED = 1;

    public function run(SmtpServerConfig $config, bool $disableOutput = true, ?callable $callback = null): void;

    public function start(SmtpServerConfig $config, ?string $pidFile = null): int;

    public function stop(?string $pidFile = null): void;

    public function getAddress(?string $pidFile = null): ?string;

    public function isRunning(?string $pidFile = null): bool;
}
