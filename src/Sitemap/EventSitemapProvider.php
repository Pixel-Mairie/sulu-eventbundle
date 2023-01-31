<?php

declare(strict_types=1);

namespace Pixel\EventBundle\Sitemap;

use Pixel\EventBundle\Repository\EventRepository;
use Sulu\Bundle\WebsiteBundle\Sitemap\Sitemap;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapProviderInterface;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapUrl;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;

class EventSitemapProvider implements SitemapProviderInterface
{
    private EventRepository $eventRepository;

    private WebspaceManagerInterface $webspaceManager;

    /**
     * @var array<string>
     */
    private array $locales = [];

    public function __construct(EventRepository $eventRepository, WebspaceManagerInterface $webspaceManager)
    {
        $this->eventRepository = $eventRepository;
        $this->webspaceManager = $webspaceManager;
    }

    public function build($page, $scheme, $host): array
    {
        $locale = $this->getLocaleByHost($host);
        $result = [];
        foreach ($this->eventRepository->findAllForSitemap((int) $page, (int) self::PAGE_SIZE) as $event) {
            //$event->setLocale($locale);
            $result[] = new SitemapUrl(
                $scheme . '://' . $host . $event->getRoutePath(),
                $event->getLocale(),
                $event->getLocale(),
                new \DateTime()
            );
        }

        return $result;
    }

    private function getLocaleByHost(string $host): ?string
    {
        if (! \array_key_exists($host, $this->locales)) {
            $portalInformation = $this->webspaceManager->getPortalInformations();
            foreach ($portalInformation as $hostName => $portal) {
                if ($hostName === $host) {
                    $this->locales[$host] = $portal->getLocale();
                }
            }
        }
        if (isset($this->locales[$host])) {
            return $this->locales[$host];
        }
        return null;
    }

    public function createSitemap($scheme, $host): Sitemap
    {
        return new Sitemap($this->getAlias(), $this->getMaxPage($scheme, $host));
    }

    public function getAlias(): string
    {
        return 'event';
    }

    public function getMaxPage($scheme, $host): int
    {
        return (int) ceil($this->eventRepository->countForSitemap() / self::PAGE_SIZE);
    }
}
