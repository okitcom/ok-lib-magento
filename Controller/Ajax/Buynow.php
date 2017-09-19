<?php
/**
 * Created by PhpStorm.
 * Date: 8/11/17
 */

namespace Okitcom\OkLibMagento\Controller\Ajax;

use Magento\Checkout\Model\Session;
use OK\Credentials\CashCredentials;
use OK\Credentials\Environment\DevelopmentEnvironment;
use OK\Model\Amount;
use OK\Model\Attribute;
use OK\Model\Attributes;
use OK\Model\Cash\LineItem;
use OK\Model\Cash\TransactionRequest;
use Okitcom\OkLibMagento\Controller\CheckoutAction;
use Okitcom\OkLibMagento\Helper\ConfigHelper;
use Okitcom\OkLibMagento\Setup\InstallData;

class Buynow extends CheckoutAction {

    public function execute() {
        if (ConfigHelper::TEST_MODE || $this->getRequest()->isAjax()) {

            $request = $this->getRequest();
            $product_id = $request->getParam("product_id");
            $qty = $request->getParam("qty");

            if ($product_id == null || $qty == null) {
                return $this->json([
                    "error" => "Invalid product."
                ]);
            }

            $quote = $this->quoteHelper->createQuote([[
                'product_id' => $product_id,
                'qty' => $qty
            ]]);

            $result = $this->requestCash($quote);
            if ($result != null) {
                return $this->json([
                    "guid" => $result["guid"],
                    "initiation" => $result["initiation"],
                    "culture" => $this->getLocale(),
                    "environment" => $this->getOkLibEnvironment()
                ]);
            }

            return $this->json([
                "error" => "Cart is empty"
            ], 400);
        }

        return $this->json([
            "error" => "Request not permitted"
        ], 400);
    }

}