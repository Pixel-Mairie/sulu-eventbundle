<?php

declare(strict_types=1);

namespace Pixel\EventBundle\Domain\Event;

use Pixel\EventBundle\Entity\Event;
use Sulu\Bundle\ActivityBundle\Domain\Event\DomainEvent;

class EventCreatedEvent extends DomainEvent
{
    private Event $event;

    /**
     * @var mixed[]
     */
    private array $payload;

    /**
     * @param array<mixed> $payload
     */
    public function __construct(Event $event, array $payload)
    {
        parent::__construct();
        $this->event = $event;
        $this->payload = $payload;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getEventPayload(): ?array
    {
        return $this->payload;
    }

    public function getEventType(): string
    {
        return 'created';
    }

    public function getResourceKey(): string
    {
        return Event::RESOURCE_KEY;
    }

    public function getResourceId(): string
    {
        return (string) $this->event->getId();
    }

    public function getResourceTitle(): ?string
    {
        return $this->event->getName();
    }

    public function getResourceSecurityContext(): ?string
    {
        return Event::SECURITY_CONTEXT;
    }
}
