<?php

namespace Drupal\off_browser_payment_gateway\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayBase;
use Drupal\Core\Url;

/**
 * Provides the Off-Browser Payment Gateway.
 */
abstract class OffBrowserPaymentGateway extends OffsitePaymentGatewayBase implements OffBrowserPaymentGatewayInterface {

  /**
   * Generates a return URL token for the specified order.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order for which the token is generated.
   *
   * @return string
   *   A unique token to be included in the return URL.
   */
  protected function getReturnUrlToken(OrderInterface $order) {
    // Generate a unique token based on order ID and timestamp.
    $token_data = $order->id() . microtime();
    $token = hash('sha256', $token_data);

    $order->setData('off_browser_payment_reference', $token);
    return $token;
  }

  /**
   * {@inheritdoc}
   */
  public function getReturnUrl(OrderInterface $order): string {
    $token = $this->getReturnUrlToken($order);
    $return_url = Url::fromRoute('off_browser_payment_gateway.checkout.return', [
      'commerce_order' => $order->id(),
      'step' => 'payment',
      'payment_reference' => $token,
    ], ['absolute' => TRUE])->toString();

    return $return_url;
  }

  /**
   * {@inheritdoc}
   */
  public function verifyReturnToken(OrderInterface $order, $token): bool {
    $stored_token = $order->getData('off_browser_payment_reference', FALSE);
    return $stored_token && hash_equals($stored_token, $token);
  }

}
