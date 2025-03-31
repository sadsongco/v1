<?php

require_once(base_path("private/order_management/config.php"));

function createRMOrder($data, $db) {
    $data['order_date'] = jsFormatDate($data['order_date']);
    [$serviceCode, $serviceName] = getServiceCode($data);
    if (!$serviceCode) return false;
    updateShippingMethod($data['order_id'], $serviceCode, $serviceName, $db);
    $order_items = [];
    foreach($data['items'] as $item) {
        $order_items[] = createRMItem($item);
    }
    foreach (PACKAGE_FORMATS as $package_format) {
        if ($data['weight'] > $package_format['weight_min'] && $data['weight'] <= $package_format['weight_max']) {
            $data['package_format'] = $package_format['name'];
            break;
        }
    }
    $rm_order = [
        "orderReference"=>$data['order_id'],
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
            "countryCode"=>$data['country_code']
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
        "packages"=>[
            [
                "weightInGrams"=>(string)$data['weight'],
                "packageFormatIdentifier"=>$data['package_format'],
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
            "sendNotificationsTo"=>"recipient",
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
    $method = trim($data['shipping_method']);
    $weight = $data['weight'];
    foreach (SHIPPING_METHODS_MAP as $value) {
        if (strpos($value['postage_method'], $method) !== false && $weight >= $value['weight_min'] && $weight <= $value['weight_max'])
            return [$value['rm_code'], $value['rm_name']];
        if (preg_match('/' . $value['postage_method'] .'/', $method) == 1 && $weight >= $value['weight_min'] && $weight <= $value['weight_max'])
            return [$value['rm_code'], $value['rm_name']];
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
        "customsDeclarationCategory"=>"SaleOfGoods",
        "requiresExportLicence"=>false,
        "stockLocation"=>"GB"
    ];
    return $rm_item;
}

function updateShippingMethod($order_id, $serviceCode, $serviceName, $db) {
    $query = "UPDATE Orders
    SET rm_service_code = ?,
    rm_service_name = ?
    WHERE order_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$serviceCode, $serviceName, $order_id]);
}