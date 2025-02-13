# Off Browser Payment Gateway Module Documentation

## Overview
The **off_browser_payment_gateway** module provides a way to process payments outside the standard browser workflow. It is designed to integrate with external payment processors such as mobile apps.

The module provides a new OffBrowserPaymentGateway extends OffsitePaymentGateway to provides a token that can be verified on return instead of relying on the cart session.

a new route off_browser_payment_gateway.checkout.return includes the payment_reference parameter that is used in the access check to verify the token.

## Use Case
1. Open a commerce site in a non-default browser on mobile.
2. Reach the payment step for an off-site payment gateway that redirects to a mobile app to complete the payment.
3. After completion the app redirects back to the site on the mobile's default browser. The site verifies the payment and redirects to the appropriate step accordingly.