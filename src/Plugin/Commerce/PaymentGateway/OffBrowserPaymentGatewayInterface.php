<?php

namespace Drupal\off_browser_payment_gateway\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayInterface;

/**
 * Defines the base interface for off-browser payment gateways.
 *
 * Off-browser payment flow:
 * 1) Customer hits the "payment" checkout step.
 * 2) The PaymentProcess checkout pane shows the gateway plugin form.
 * 3) The plugin form performs a redirect to an external app.
 * 4) The customer provides their payment details to the payment provider.
 * 5) The external app redirects the customer back to the browser.
 * 6) A payment is created in either onReturn() or onNotify().
 */
interface OffBrowserPaymentGatewayInterface extends OffsitePaymentGatewayInterface {

  /**
   * Returns the return URL with verification token.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order.
   *
   * @return string
   *   The tokenized return URL.
   */
  public function getReturnUrl(OrderInterface $order): string;

  /**
   * Verify the return token for security purposes.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order entity.
   * @param string $token
   *   The token to verify.
   *
   * @return bool
   *   TRUE if the token is valid, FALSE otherwise.
   */
  public function verifyReturnToken(OrderInterface $order, $token): bool;

}
