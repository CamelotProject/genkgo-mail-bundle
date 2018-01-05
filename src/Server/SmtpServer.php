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

use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * @internal
 * @codeCoverageIgnore
 */
final class SmtpServer implements SmtpServerInterface
{
    public function run(SmtpServerConfig $config, bool $disableOutput = true, ?callable $callback = null): void
    {
        if ($this->isRunning()) {
            throw new \RuntimeException(\sprintf('A process is already listening on %s.', $config->getAddress()));
        }

        $process = $this->createServerProcess($config);
        if ($disableOutput) {
            $process->disableOutput();
            $callback = null;
        } else {
            try {
                $process->setTty(true);
                $callback = null;
            } catch (RuntimeException $e) {
            }
        }

        $process->run($callback);

        if (!$process->isSuccessful()) {
            $error = 'Server terminated unexpectedly.';
            if ($process->isOutputDisabled()) {
                $error .= ' Run the command again with -v option for more details.';
            }

            throw new \RuntimeException($error);
        }
    }

    public function start(SmtpServerConfig $config, ?string $pidFile = null): int
    {
        $pidFile = $pidFile ?: $this->getDefaultPidFile();
        if ($this->isRunning($pidFile)) {
            throw new \RuntimeException(\sprintf('A process is already listening on %s.', $config->getAddress()));
        }

        $pid = \pcntl_fork();

        if ($pid < 0) {
            throw new \RuntimeException('Unable to start the server process.');
        }

        if ($pid > 0) {
            return self::STARTED;
        }

        if (\posix_setsid() < 0) {
            throw new \RuntimeException('Unable to set the child process as session leader.');
        }

        $process = $this->createServerProcess($config);
        $process->disableOutput();
        $process->start();

        if (!$process->isRunning()) {
            throw new \RuntimeException('Unable to start the server process.');
        }

        \file_put_contents($pidFile, $config->getAddress());

        // stop the SMTP server when the lock file is removed
        while ($process->isRunning()) {
            if (!\file_exists($pidFile)) {
                $process->stop();
            }

            \sleep(1);
        }

        return self::STOPPED;
    }

    public function stop(?string $pidFile = null): void
    {
        $pidFile = $pidFile ?: $this->getDefaultPidFile();
        if (!\file_exists($pidFile)) {
            throw new \RuntimeException('No SMTP server is listening.');
        }

        \unlink($pidFile);
    }

    public function getAddress(?string $pidFile = null): ?string
    {
        $pidFile = $pidFile ?: $this->getDefaultPidFile();
        if (!\file_exists($pidFile)) {
            return null;
        }

        return \file_get_contents($pidFile);
    }

    public function isRunning(?string $pidFile = null): bool
    {
        $pidFile = $pidFile ?: $this->getDefaultPidFile();
        if (!\file_exists($pidFile)) {
            return false;
        }

        $address = \file_get_contents($pidFile);
        $pos = \strrpos($address, ':');
        $hostname = \substr($address, 0, $pos);
        $port = (int) \substr($address, $pos + 1);
        if (false !== $fp = @\fsockopen($hostname, $port, $errNo, $errStr, 1)) {
            \fwrite($fp, 'QUIT');
            \fclose($fp);

            return true;
        }

        \unlink($pidFile);

        return false;
    }

    private function createServerProcess(SmtpServerConfig $config): Process
    {
        $finder = new PhpExecutableFinder();
        if (false === $binary = $finder->find(false)) {
            throw new \RuntimeException('Unable to find the PHP binary.');
        }

        $process = new Process(
            \array_merge(
                [$binary],
                $finder->findArguments(),
                [__DIR__ . '/../Resources/bin/server.php'],
                [$config->getAddress()]
            ));
        $process->setTimeout(null);

        $envVars = \getenv('SYMFONY_DOTENV_VARS');
        if ($envVars && \in_array('APP_ENV', \explode(',', $envVars))) {
            $process->setEnv(['APP_ENV' => false]);
            $process->inheritEnvironmentVariables();
        }

        return $process;
    }

    private function getDefaultPidFile(): string
    {
        return \getcwd() . '/.smtp-server-pid';
    }
}
