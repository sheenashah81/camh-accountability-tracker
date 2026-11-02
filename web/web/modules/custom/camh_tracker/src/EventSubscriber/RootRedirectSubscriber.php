<?php

namespace Drupal\camh_tracker\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Sends visitors who open the bare site address to /en or /es.
 *
 * The language comes from the browser's preference (Spanish if it is listed
 * before English), otherwise English. Every page then carries its language in
 * the address, so the language switch and shared links always make sense.
 */
class RootRedirectSubscriber implements EventSubscriberInterface {

  public static function getSubscribedEvents(): array {
    return [KernelEvents::REQUEST => ['onRequest', 100]];
  }

  public function onRequest(RequestEvent $event): void {
    $request = $event->getRequest();
    if (!$event->isMainRequest() || $request->getPathInfo() !== '/' || !$request->isMethod('GET')) {
      return;
    }
    $prefix = 'en';
    foreach (explode(',', (string) $request->headers->get('Accept-Language', '')) as $part) {
      $lang = strtolower(substr(trim(explode(';', $part)[0]), 0, 2));
      if ($lang === 'es' || $lang === 'en') {
        $prefix = $lang;
        break;
      }
    }
    // The answer depends on the browser, so it must never be stored by the page cache.
    \Drupal::service('page_cache_kill_switch')->trigger();
    $query = $request->getQueryString();
    $event->setResponse(new RedirectResponse($request->getBasePath() . '/' . $prefix . ($query ? '?' . $query : ''), 302));
  }

}
