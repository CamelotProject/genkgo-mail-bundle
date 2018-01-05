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

use InvalidArgumentException;
use RuntimeException;

/**
 * @internal
 */
final class SmtpServerConfig
{
    private $hostname;
    private $port;

    /**
     * @param string|int|null $address
     */
    public function __construct($address = null)
    {
        $address = (string) $address;
        if ($address === '') {
            $this->hostname = '127.0.0.1';
            $this->port = $this->findBestPort();
        } elseif (false !== $pos = \strrpos($address, ':')) {
            $this->hostname = \substr($address, 0, $pos);
            if ($this->hostname === '*') {
                $this->hostname = '0.0.0.0';
            }
            $this->port = (int) \substr($address, $pos + 1);
        } elseif (\ctype_digit($address)) {
            $this->hostname = '127.0.0.1';
            $this->port = (int) $address;
        } else {
            $this->hostname = $address;
            $this->port = $this->findBestPort();
        }

        if (!\is_numeric($this->port)) {
            throw new InvalidArgumentException(\sprintf('Port "%s" is not valid.', $this->port));
        }
    }

    public function getHostname(): string
    {
        return $this->hostname;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getAddress(): string
    {
        return $this->hostname . ':' . $this->port;
    }

    /**
     * @throws RuntimeException
     */
    private function findBestPort(): int
    {
        $port = 2525;
        while (false !== $fp = @\fsockopen($this->hostname, $port, $errNo, $errStr, 1)) {
            \fclose($fp);
            if ($port++ >= 2625) {
                throw new RuntimeException('Unable to find a port available to run the SMTP server.');
            }
        }

        return $port;
    }
}
