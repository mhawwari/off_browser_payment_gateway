<?php

namespace Drupal\off_browser_payment_gateway\Controller;

use Drupal\commerce_checkout\Controller\CheckoutController;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\off_browser_payment_gateway\Plugin\Commerce\PaymentGateway\OffBrowserPaymentGateway;

/**
 * Provides the checkout return page access checks.
 */
class OffBrowserCheckoutController extends CheckoutController {

  /**
   * Checks access for the form page.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user account.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  public function checkAccess(RouteMatchInterface $route_match, AccountInterface $account) {
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $route_match->getParameter('commerce_order');
    $payment_reference = $route_match->getParameter('payment_reference');

    /** @var \Drupal\commerce_payment\Entity\PaymentGatewayInterface $payment_gateway */
    $payment_gateway = $order->get('payment_gateway')->entity;
    $gateway_plugin = $payment_gateway->getPlugin();
    assert($gateway_plugin instanceof OffBrowserPaymentGateway);

    if ($gateway_plugin->verifyReturnToken($order, $payment_reference)) {
      return AccessResult::allowed()->addCacheableDependency($order);
    }
    else {
      // Call default off-site access check if token is invalid.
      return parent::checkAccess($route_match, $account);
    }
  }

}
