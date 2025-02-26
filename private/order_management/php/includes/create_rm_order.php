<?php

function createRMOrder($data) {
    $data['order_date'] = jsFormatDate($data['order_date']);
    $rm_order = [
        "orderReference"=>$data['order_id'] . $data['sumup_id'],
        "isRecipientABusiness"=>false,
        "recipient"=>[
            "address"=>[
            "fullName"=>$data['name'],
            "companyName"=>"",
            "addressLine1"=>$data['address_1'],
            "addressLine2"=>$data['address_2'],
            "addressLine3"=>"",
            "city"=>$data['city'],
            "county"=>"",
            "postcode"=>$data['postcode'],
            "countryCode"=>"GB"
            ],
            "phoneNumber"=>"",
            "emailAddress"=>$data['email'],
            "addressBookReference"=>""
        ],
        "sender"=>[
            "tradingName"=>"Unbelievable Truth",
            "phoneNumber"=>"07787 782550",
            "emailAddress"=>"info@unbelievabletruth.co.uk"
        ],
        "billing"=>[
            "address"=>[
                "fullName"=>$data['name'],
                "companyName"=>"",
                "addressLine1"=>$data['address_1'],
                "addressLine2"=>$data['address_2'],
                "addressLine3"=>"",
                "city"=>$data['city'],
                "county"=>"",
                "postcode"=>$data['postcode'],
                "countryCode"=>"GB"
            ],
            "phoneNumber"=>"",
            "emailAddress"=>$data['email'],
        ],
        "packages"=>[
            [
                "weightInGrams"=>1,
                "packageFormatIdentifier"=>"box",
                "customPackageFormatIdentifier"=>"",
                "dimensions"=>[
                "heightInMms"=>450,
                "widthInMms"=>240,
                "depthInMms"=>35
                ],
                "contents"=>[
                    [
                        "name"=>"string",
                        "SKU"=>"string",
                        "quantity"=>1,
                        "unitValue"=>15,
                        "unitWeightInGrams"=>400,
                        "customsDescription"=>"string",
                        "extendedCustomsDescription"=>"string",
                        "customsCode"=>"string",
                        "originCountryCode"=>"str",
                        "customsDeclarationCategory"=>"none",
                        "requiresExportLicence"=>false,
                        "stockLocation"=>"GB"
                    ]
                ]
            ],
        ],
        "orderDate"=>$data['order_date'],
        "plannedDespatchDate"=>"2019-08-24T14:15:22Z",
        "specialInstructions"=>"",
        "subtotal"=>15,
        "shippingCostCharged"=>8,
        "otherCosts"=>0,
        "customsDutyCosts"=>0,
        "total"=>23,
        "currencyCode"=>"GBP",
        "postageDetails"=>[
            "sendNotificationsTo"=>"sender",
            "serviceCode"=>"CRL2",
            "serviceRegisterCode"=>"st",
            "consequentialLoss"=>0,
            "receiveEmailNotification"=>true,
            "receiveSmsNotification"=>false,
            "guaranteedSaturdayDelivery"=>false,
            "requestSignatureUponDelivery"=>true,
            "isLocalCollect"=>true,
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
              "key"=>"",
              "value"=>""
            ]
        ],
        "label"=>[
            "includeLabelInResponse"=>true,
            "includeCN"=>true,
            "includeReturnsLabel"=>true
        ],
        "orderTax"=>0,
        "containsDangerousGoods"=>false,
        "dangerousGoodsUnCode"=>"",
        "dangerousGoodsDescription"=>0,
        "dangerousGoodsQuantity"=>0
    ];
    return $rm_order;
}

function jsFormatDate($date) {
    $dateObj = new DateTime($date);
    return date_format($dateObj, 'Y-m-d\TH:i:s\Z');
}