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
    /**
     * @return string[]
     */
    public static function getSubscribedServices(): array
    {
        $subscribedServices = parent::getSubscribedServices();

        $subscribedServices['sulu_core.webspace.webspace_manager'] = WebspaceManagerInterface::class;
        $subscribedServices['sulu.repository.route'] = RouteRepositoryInterface::class;
        $subscribedServices['sulu_website.resolver.template_attribute'] = TemplateAttributeResolverInterface::class;

        return $subscribedServices;
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

        $parameters = $this->get('sulu_website.resolver.template_attribute')->resolve([
            'event' => $event,
            'localizations' => $this->getLocalizationsArrayForEntity($event),
        ]);

        if ($partial) {
            $content = $this->renderBlock(
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
        $routes = $this->get('sulu.repository.route')->findAllByEntity(Event::class, (string) $entity->getId());

        $localizations = [];
        foreach ($routes as $route) {
            $url = $this->get('sulu_core.webspace.webspace_manager')->findUrlByResourceLocator(
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
     * Returns rendered part of template specified by block.
     *
     * @param mixed $template
     * @param mixed $block
     * @param mixed $attributes
     */
    protected function renderBlock($template, $block, $attributes = []): string
    {
        $twig = $this->get('twig');
        $attributes = $twig->mergeGlobals($attributes);

        $template = $twig->load($template);

        $level = ob_get_level();
        ob_start();

        try {
            $rendered = $template->renderBlock($block, $attributes);
            ob_end_clean();

            return $rendered;
        } catch (\Exception $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }

            throw $e;
        }
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
