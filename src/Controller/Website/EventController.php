<?php

declare(strict_types=1);

namespace Pixel\EventBundle\Controller\Website;

use Pixel\EventBundle\Entity\Event;
use Sulu\Bundle\PreviewBundle\Preview\Preview;
use Sulu\Bundle\RouteBundle\Entity\RouteRepositoryInterface;
use Sulu\Bundle\WebsiteBundle\Resolver\TemplateAttributeResolverInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class EventController extends AbstractController
{
    private TemplateAttributeResolverInterface $templateAttributeResolver;

    private RouteRepositoryInterface $routeRepository;

    private WebspaceManagerInterface $webspaceManager;

    public function __construct(TemplateAttributeResolverInterface $templateAttributeResolver, RouteRepositoryInterface $routeRepository, WebspaceManagerInterface $webspaceManager)
    {
        $this->templateAttributeResolver = $templateAttributeResolver;
        $this->routeRepository = $routeRepository;
        $this->webspaceManager = $webspaceManager;
    }

    /**
     * @param array<mixed> $attributes
     * @throws \Exception
     */
    public function indexAction(Event $event, array $attributes = [], bool $preview = false, bool $partial = false): Response
    {
        if (! $event->getSeo() || (isset($event->getSeo()['title']) && ! $event->getSeo()['title'])) {
            $seo = [
                "title" => $event->getName(),
            ];

            $event->setSeo($seo);
        }

        $parameters = $this->templateAttributeResolver->resolve([
            'event' => $event,
            'localizations' => $this->getLocalizationsArrayForEntity($event),
        ]);

        if ($partial) {
            return $this->renderBlock(
                '@Event/event.html.twig',
                'content',
                $parameters
            );
        } elseif ($preview) {
            $content = $this->renderPreview(
                '@Event/event.html.twig',
                $parameters
            );
        } else {
            if (! $event->getEnabled()) {
                throw $this->createNotFoundException();
            }
            $content = $this->renderView(
                '@Event/event.html.twig',
                $parameters
            );
        }

        return new Response($content);
    }

    /**
     * @return array<string, array<string>>
     */
    protected function getLocalizationsArrayForEntity(Event $entity): array
    {
        $routes = $this->routeRepository->findAllByEntity(Event::class, (string) $entity->getId());

        $localizations = [];
        foreach ($routes as $route) {
            $url = $this->webspaceManager->findUrlByResourceLocator(
                $route->getPath(),
                null,
                $route->getLocale()
            );

            $localizations[$route->getLocale()] = [
                'locale' => $route->getLocale(),
                'url' => $url,
            ];
        }
        return $localizations;
    }

    /**
     * @param array<mixed> $parameters
     */
    protected function renderPreview(string $view, array $parameters = []): string
    {
        $parameters['previewParentTemplate'] = $view;
        $parameters['previewContentReplacer'] = Preview::CONTENT_REPLACER;

        return $this->renderView('@SuluWebsite/Preview/preview.html.twig', $parameters);
    }
}
