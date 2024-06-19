<?php

declare(strict_types=1);

namespace Pixel\EventBundle\Preview;

use Pixel\EventBundle\Entity\Event;
use Pixel\EventBundle\Repository\EventRepository;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Sulu\Bundle\PreviewBundle\Preview\Object\PreviewObjectProviderInterface;

class EventObjectProvider implements PreviewObjectProviderInterface
{
    private EventRepository $eventRepository;

    private MediaManagerInterface $mediaManager;

    public function __construct(EventRepository $eventRepository, MediaManagerInterface $mediaManager)
    {
        $this->eventRepository = $eventRepository;
        $this->mediaManager = $mediaManager;
    }

    public function getObject($id, $locale): Event
    {
        return $this->eventRepository->find((int) $id);
    }

    /**
     * @param Event $object
     */
    public function getId($object): string
    {
        return (string) $object->getId();
    }

    /**
     * @param Event $object
     * @param array<mixed> $data
     */
    public function setValues($object, $locale, array $data): void
    {
        $imageId = $data['image']['id'] ?? null;
        $enabled = $data['enabled'] ?? null;
        $endDate = $data['endDate'] ?? null;
        $url = $data['url'] ?? null;
        $email = $data['email'] ?? null;
        $phoneNumber = $data['phoneNumber'] ?? null;
        $location = $data['location'] ?? null;
        $images = $data['images'] ?? null;

        $object->setName($data['name']);
        $object->setDescription($data['description']);
        $object->setImage($imageId ? $this->mediaManager->getEntityById($data['image']['id']) : null);
        $object->setEnabled($enabled);
        $object->setStartDate(new \DateTimeImmutable($data['startDate']));
        $object->setEndDate($endDate ? new \DateTimeImmutable($data['endDate']) : null);
        $object->setUrl($url);
        $object->setEmail($email);
        $object->setPhoneNumber($phoneNumber);
        $object->setLocation($location);
        $object->setImages($images);
    }

    /**
     * @param object $object
     * @param string $locale
     * @param array<mixed> $context
     * @return mixed
     */
    public function setContext($object, $locale, array $context)
    {
        if (\array_key_exists('template', $context)) {
            $object->setStructureType($context['template']);
        }

        return $object;
    }

    /**
     * @param Event $object
     * @return string
     */
    public function serialize($object)
    {
        if (! $object->getName()) {
            $object->setName("");
        }
        if (! $object->getDescription()) {
            $object->setDescription("");
        }

        return serialize($object);
    }

    /**
     * @return mixed
     */
    public function deserialize($serializedObject, $objectClass)
    {
        return unserialize($serializedObject);
    }

    public function getSecurityContext($id, $locale): ?string
    {
        return Event::SECURITY_CONTEXT;
    }
}
