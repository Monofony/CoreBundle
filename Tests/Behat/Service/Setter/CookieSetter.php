<?php

/*
 * This file is part of the Monofony package.
 *
 * (c) Monofony
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Monofony\Bundle\CoreBundle\Tests\Behat\Service\Setter;

use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Session;
use FriendsOfBehat\SymfonyExtension\Driver\SymfonyDriver;
use Symfony\Component\BrowserKit\Cookie;

final class CookieSetter implements CookieSetterInterface
{
    /**
     * @var Session
     */
    private $minkSession;

    /**
     * @var array
     */
    private $minkParameters;

    /**
     */
    public function __construct(Session $minkSession, $minkParameters)
    {
        $this->minkSession = $minkSession;
        $this->minkParameters = $minkParameters;
    }

    /**
     * {@inheritdoc}
     */
    public function setCookie($name, $value)
    {
        $this->prepareMinkSessionIfNeeded($this->minkSession);

        $driver = $this->minkSession->getDriver();

        if ($driver instanceof SymfonyDriver) {
            $driver->getClient()->getCookieJar()->set(
                new Cookie($name, $value, null, null, parse_url($this->minkParameters['base_url'], PHP_URL_HOST))
            );

            return;
        }

        $this->minkSession->setCookie($name, $value);
    }

    private function prepareMinkSessionIfNeeded(Session $session): void
    {
        if ($this->shouldMinkSessionBePrepared($session)) {
            $session->visit(rtrim($this->minkParameters['base_url'], '/').'/');
        }
    }

    private function shouldMinkSessionBePrepared(Session $session): bool
    {
        $driver = $session->getDriver();

        if ($driver instanceof SymfonyDriver) {
            return false;
        }

        if ($driver instanceof Selenium2Driver && null === $driver->getWebDriverSession()) {
            return true;
        }

        if (false !== strpos($session->getCurrentUrl(), $this->minkParameters['base_url'])) {
            return false;
        }

        return true;
    }
}
