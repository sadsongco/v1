<?php

include_once(__DIR__."/order_includes.php");
require_once(base_path("private/order_management/config.php"));

function createRMOrder($data) {
    $data['order_date'] = jsFormatDate($data['order_date']);
    $serviceCode = getServiceCode($data);
    $order_items = [];
    foreach($data['items'] as $item) {
        $order_items[] = createRMItem($item);
    }
    $rm_order = [
        "orderReference"=>$data['order_id'] . $data['sumup_id'],
        "isRecipientABusiness"=>false,
        "recipient"=>[
            "address"=>[
            "fullName"=>$data['name'],
            "companyName"=>"",
            "addressLine1"=>$data['address_1'],
            "addressLine2"=>$data['address_2'] ?? "",
            "addressLine3"=>"",
            "city"=>$data['city'],
            "county"=>"",
            "postcode"=>$data['postcode'],
            "countryCode"=>"GB"
            ],
            "phoneNumber"=>"",
            "emailAddress"=>$data['email']
        ],
        "sender"=>[
            "tradingName"=>"Unbelievable Truth",
            "phoneNumber"=>"07787 782550",
            "emailAddress"=>"info@unbelievabletruth.co.uk"
        ],
        "billing"=>[
            "address"=>[
                "fullName"=>"Nigel Powell",
                "companyName"=>"Unbelievable Truth",
                "addressLine1"=>"52 Claremont Road",
                "addressLine2"=>"",
                "addressLine3"=>"",
                "city"=>"Rugby",
                "county"=>"",
                "postcode"=>"CV21 3LX",
                "countryCode"=>"GB"
            ],
            "phoneNumber"=>"",
            "emailAddress"=>"info@unbelievabletruth.co.uk",
        ],
        "packages"=>[
            [
                "weightInGrams"=>$data['weight'],
                "packageFormatIdentifier"=>"small parcel",
                "customPackageFormatIdentifier"=>"",
                "contents"=>[
                    $order_items
                ]
            ],
        ],
        "orderDate"=>$data['order_date'],
        "subtotal"=>15,
        "shippingCostCharged"=>8,
        "otherCosts"=>0,
        "total"=>23,
        "currencyCode"=>"GBP",
        "postageDetails"=>[
            "sendNotificationsTo"=>"sender",
            "serviceCode"=>$serviceCode,
            "serviceRegisterCode"=>"",
            "receiveEmailNotification"=>true,
            "receiveSmsNotification"=>false,
            "guaranteedSaturdayDelivery"=>false,
            "requestSignatureUponDelivery"=>false,
            "isLocalCollect"=>false,
            "safePlace"=>"",
            "department"=>"",
            "AIRNumber"=>"",
            "IOSSNumber"=>"",
            "requiresExportLicense"=>false,
            "commercialInvoiceNumber"=>$data['order_id'] . $data['sumup_id'],
            "commercialInvoiceDate"=>$data['order_date']
        ],
        "tags"=>[
            [
              "key"=>"string",
              "value"=>"string"
            ]
        ],
        "label"=>[
            "includeLabelInResponse"=>true,
            "includeCN"=>true,
            "includeReturnsLabel"=>true
        ],
    ];
    return $rm_order;
}

function jsFormatDate($date) {
    $dateObj = new DateTime($date);
    return date_format($dateObj, 'Y-m-d\TH:i:s\Z');
}

function getServiceCode($data) {
    $method = $data['shipping_method'];
    $weight = $data['weight'];
    foreach (SHIPPING_METHODS_MAP as $key => $value) {
        if ($method == $value['postage_method'] && $weight >= $value['weight_min'] && $weight <= $value['weight_max'])
            return $value['rm_code'];
    }
    return false;
}

function createRMItem($item) {
    $rm_item = [
        "name"=>$item['name'],
        "quantity"=>$item['amount'],
        "unitValue"=>$item['price'],
        "unitWeightInGrams"=>$item['weight'] + $item['packaging_weight'],
        "customsDescription"=>"string",
        "extendedCustomsDescription"=>"string",
        "customsCode"=>"string",
        "originCountryCode"=>"GBR",
        "customsDeclarationCategory"=>"none",
        "requiresExportLicence"=>false,
        "stockLocation"=>"GB"
    ];
    return $rm_item;
}