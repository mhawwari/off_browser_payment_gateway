<?php

namespace Drupal\off_browser_payment_gateway\Controller;

use Drupal\commerce\Response\NeedsRedirectException;
use Drupal\commerce_cart\CartSession;
use Drupal\commerce_cart\CartSessionInterface;
use Drupal\commerce_checkout\CheckoutOrderManagerInterface;
use Drupal\commerce_payment\Controller\PaymentCheckoutController;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides checkout endpoints for off-site payments.
 */
class OffBrowserPaymentController extends PaymentCheckoutController {

  /**
   * The session.
   *
   * @var \Drupal\commerce_cart\CartSessionInterface
   */
  protected $cartSession;

  /**
   * Constructs a new PaymentCheckoutController object.
   *
   * @param \Drupal\commerce_checkout\CheckoutOrderManagerInterface $checkout_order_manager
   *   The checkout order manager.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Drupal\commerce_cart\CartSessionInterface $cart_session
   *   The cart session.
   */
  public function __construct(CheckoutOrderManagerInterface $checkout_order_manager, MessengerInterface $messenger, LoggerInterface $logger, EntityTypeManagerInterface $entity_type_manager, EventDispatcherInterface $event_dispatcher, CartSessionInterface $cart_session) {
    parent::__construct($checkout_order_manager, $messenger, $logger, $entity_type_manager, $event_dispatcher);
    $this->cartSession = $cart_session;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('commerce_checkout.checkout_order_manager'),
      $container->get('messenger'),
      $container->get('logger.channel.commerce_payment'),
      $container->get('entity_type.manager'),
      $container->get('event_dispatcher'),
      $container->get('commerce_cart.cart_session'),
    );
  }

  /**
   * Provides the "return" checkout payment page.
   *
   * Redirects to the next checkout page, completing checkout.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   */
  public function returnPage(Request $request, RouteMatchInterface $route_match) {
    try {
      parent::returnPage($request, $route_match);
    }
    catch (NeedsRedirectException $e) {
      /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
      $order = $route_match->getParameter('commerce_order');
      /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
      $order = $this->entityTypeManager->getStorage('commerce_order')->load($order->id());
      if (
        $order->get('checkout_step')->value == 'complete'
        && in_array($order->getState()->value, ['completed', 'authorization'])
        ) {
        // Finalize cart even if it was already completed by another request
        // or a server-side notification.
        $this->cartSession->addCartId($order->id(), CartSession::COMPLETED);
      }

      throw $e;
    }
  }

}
