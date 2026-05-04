<?php

declare(strict_types=1);

namespace Pixel\EventBundle\Reference;

use Pixel\EventBundle\Entity\Event;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\ReferenceBundle\Application\Collector\ReferenceCollector;
use Sulu\Bundle\ReferenceBundle\Domain\Repository\ReferenceRepositoryInterface;

class EventReferenceProvider
{
    public function __construct(
        private ReferenceRepositoryInterface $referenceRepository,
    ) {
    }

    public function updateReferences(Event $event, string $locale, string $context): void
    {
        $referenceCollector = new ReferenceCollector(
            $this->referenceRepository,
            Event::RESOURCE_KEY,
            (string) $event->getId(),
            $locale,
            $event->getName() ?? '',
            $context,
            ['id' => $event->getId(), 'locale' => $locale],
        );

        if ($image = $event->getImage()) {
            $referenceCollector->addReference(
                MediaInterface::RESOURCE_KEY,
                (string) $image->getId(),
                'image',
            );
        }

        if ($pdf = $event->getPdf()) {
            $referenceCollector->addReference(
                MediaInterface::RESOURCE_KEY,
                (string) $pdf->getId(),
                'pdf',
            );
        }

        foreach ($event->getImages()['ids'] ?? [] as $id) {
            $referenceCollector->addReference(
                MediaInterface::RESOURCE_KEY,
                (string) $id,
                'images',
            );
        }

        $referenceCollector->persistReferences();
        $this->referenceRepository->flush();
    }
}
