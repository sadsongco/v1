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
            "emailAddress"=>"info@unbelievabletruth.co.uk",
            "addressBookReference"=>"001"
        ],
        "billing"=>[
            "address"=>[
                "fullName"=>"Nigel Powell",
                "companyName"=>"Unbelievable Truth",
                "addressLine1"=>"52 Claremont Road",
                "addressLine2"=>"",
                "addressLine3"=>"",
                "city"=>"Rugby",
                "county"=>"Warwickshire",
                "postcode"=>"CV21 3LX",
                "countryCode"=>"GB"
            ],
            "phoneNumber"=>"07787 782550",
            "emailAddress"=>"info@unbelievabletruth.co.uk",
        ],
        "packages"=>[
            [
                "weightInGrams"=>(string)$data['weight'],
                "packageFormatIdentifier"=>"small parcel",
                "customPackageFormatIdentifier"=>"1",
                "dimensions"=>[
                    "heightInMms"=>330,
                    "widthInMms"=>330,
                    "depthInMms"=>25
                ],
                "contents"=>$order_items
            ],
        ],
        "orderDate"=>$data['order_date'],
        "plannedDespatchDate"=>"",
        "subtotal"=>(float)$data['subtotal'],
        "shippingCostCharged"=>(float)$data['shipping'],
        "otherCosts"=>"0",
        "total"=>(float)$data['total'],
        "currencyCode"=>"GBP",
        "postageDetails"=>[
            "sendNotificationsTo"=>"sender",
            "serviceCode"=>$serviceCode,
            "serviceRegisterCode"=>"",
            "receiveEmailNotification"=>false,
            "receiveSmsNotification"=>false,
            "guaranteedSaturdayDelivery"=>false,
            "requestSignatureUponDelivery"=>false,
            "isLocalCollect"=>false
        ],
        "tags"=>[
            [
              "key"=>"string",
              "value"=>"string"
            ]
        ],
        "label"=>[
            "includeLabelInResponse"=>false,
            "includeCN"=>false,
            "includeReturnsLabel"=>false
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
        "unitWeightInGrams"=>$item['weight'],
        "customsDescription"=>$item['customs_description'],
        "extendedCustomsDescription"=>$item['name'],
        "customsCode"=>$item['customs_code'],
        "originCountryCode"=>"GBR",
        "customsDeclarationCategory"=>"none",
        "requiresExportLicence"=>false,
        "stockLocation"=>"GB"
    ];
    return $rm_item;
}