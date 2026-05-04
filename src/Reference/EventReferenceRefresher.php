<?php

declare(strict_types=1);

namespace Pixel\EventBundle\Reference;

use Pixel\EventBundle\Entity\Event;
use Pixel\EventBundle\Repository\EventRepository;
use Sulu\Bundle\ReferenceBundle\Application\Refresh\ReferenceRefresherInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;

class EventReferenceRefresher implements ReferenceRefresherInterface
{
    public function __construct(
        private EventReferenceProvider $eventReferenceProvider,
        private EventRepository $eventRepository,
        private WebspaceManagerInterface $webspaceManager,
        private string $suluContext,
    ) {
    }

    public static function getResourceKey(): string
    {
        return Event::RESOURCE_KEY;
    }

    public function refresh(): \Generator
    {
        $locales = $this->webspaceManager->getAllLocales();
        $events = $this->eventRepository->findAll();

        foreach ($events as $event) {
            foreach ($locales as $locale) {
                $event->setLocale($locale);
                $this->eventReferenceProvider->updateReferences($event, $locale, $this->suluContext);
            }

            yield $event;
        }
    }
}
