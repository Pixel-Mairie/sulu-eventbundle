<?php

namespace Pixel\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sulu\Component\Persistence\Model\AuditableInterface;
use Sulu\Component\Persistence\Model\AuditableTrait;

/**
 * @ORM\Entity()
 * @ORM\Table(name="event_translation")
 * @ORM\Entity(repositoryClass="Pixel\EventBundle\Repository\EventRepository")
 * @Serializer\ExclusionPolicy("all")
 */
class EventTranslation implements AuditableInterface
{
    use AuditableTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @Serializer\Expose()
     */
    private ?int $id = null;

    /**
     * @var Event
     * @ORM\ManyToOne(targetEntity="Pixel\EventBundle\Entity\Event", inversedBy="translations")
     * @ORM\JoinColumn(nullable=true)
     */
    private $event;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private string $locale;

    /**
     * @ORM\Column(type="string")
     * @Serializer\Expose()
     */
    private string $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Serializer\Expose()
     */
    private ?string $description;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Expose()
     *
     * @Serializer\Expose()
     */
    private string $routePath;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @Serializer\Expose()
     * @var array<mixed>|null
     */
    private ?array $seo = null;

    public function __construct(Event $event, string $locale)
    {
        $this->event = $event;
        $this->locale = $locale;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = trim($name);
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getRoutePath(): string
    {
        return $this->routePath ?? '';
    }

    public function setRoutePath(string $routePath): void
    {
        $this->routePath = $routePath;
    }

    /**
     * @return array<mixed>|null
     */
    public function getSeo(): ?array
    {
        return $this->seo;
    }

    /**
     * @param array<mixed>|null $seo
     */
    public function setSeo(?array $seo): void
    {
        $this->seo = $seo;
    }
}
