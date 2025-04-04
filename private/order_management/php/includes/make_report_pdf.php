<?php

require("../lib/fpdf.php");
define('GBP',chr(163));

class ORDER_PDF extends FPDF {
    const LILAC = [100, 100, 100];
    const GREY = [220, 220, 220];
    const BLACK = [0, 0, 0];
    const ITEM_GREY = [80, 80, 80];
    const HEADER_POS = [10, 10];
    const DATE_POS = [120, 28];
    const ADDRESS_POS = [120, 37];
    const ORDER_NO_POS = [25, 77];
    const ITEM_POS = [25, 83, 145];
    const PRICE_X = -30;
    private $pw;

    function Init ($report) {
        $this->SetTitle("Unbelievable Truth sales report " . $report["start_date"] . " - " . $report["end_date"]);
        $this->SetSubject("Unbelievable Truth sales report");
        $this->SetAuthor("Nigel Powell");
        $this->AddFont('opensansbold','', 'OpenSans-Bold.php');
        $this->AddFont('opensansregular', '', 'OpenSans-Regular.php');
        $this->pw = $this->GetPageWidth();
    }

    function Header () {
        $logo_url = __DIR__ . "/../../assets/images/ut-logo-black.png";
        $w = 100;
        $this->SetX(($this->pw-$w)/2);
        $this->Image($logo_url, null, null, $w, 0, 'PNG', 'https://unbelievabletruth.co.uk');
    }

    function Footer () {
        $this->SetFont('opensansregular', '', 9);
        $h = 15;
        $this->SetY(-$h);
        $this->setFillColor(...self::GREY);
        $address = "Unbelievable Truth, 52 Claremont Road, Rugby, CV21 3LX, UK";
        $email = "info@unbelievabletruth.co.uk";
        $this->SetTextColor(...self::BLACK);
        $this->Cell(0, $h, $address." :: ".$email, 'T', 0, 'C', true, 'mailto:info@unbelievabletruth.co.uk');
    }

    function DateCell($report) {
        $this->SetFont('opensansbold', '', 12);
        $this->SetTextColor(...self::BLACK);
        $this->SetDrawColor(...self::LILAC);
        $this->SetXY(...self::DATE_POS);
        $this->Cell(0, 8, $report["date"], 'B', 1);
    }


    function ReportNoCell($report) {
        $this->SetFont('opensansbold', '', 12);
        $this->SetTextColor(...self::BLACK);
        $this->SetXY(...self::ORDER_NO_POS);
        $this->Cell(0, 8, "Sales report  " . $report["start_date"] . " - " . $report["end_date"], 0, 1);
    }
    
    function SubTotalCell($subtotal) {
        $this->setFont('opensansbold', '', 12);
        $this->SetX(self::ITEM_POS[0]);
        $this->Cell(0, 0, "Subtotal", 0, 0, 'L');
        $this->setFont('opensansregular', '', 11);
        $this->SetX(self::PRICE_X);
        $money_format = new NumberFormatter("en_GB", NumberFormatter::DECIMAL);
        $money_format->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, 2);
        $this->Cell(0, 0, GBP.$money_format->format($subtotal), 0, 1, 'R');
    }

    function ShippingCell($shipping_cost) {
        $this->SetX(self::ITEM_POS[0]);
        $this->Cell(0, 0, "Shipping", 0, 0, 'L');
        $this->SetX(self::PRICE_X);
        $money_format = new NumberFormatter("en_GB", NumberFormatter::DECIMAL);
        $money_format->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, 2);
        $this->Cell(0, 0, GBP.$money_format->format($shipping_cost), 0, 1, 'R');
    }

    function VatCell($vat) {
        $this->SetX(self::ITEM_POS[0]);
        $this->Cell(0, 0, "VAT", 0, 0, 'L');
        $this->SetX(self::PRICE_X);
        $money_format = new NumberFormatter("en_GB", NumberFormatter::DECIMAL);
        $money_format->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, 2);
        $this->Cell(0, 0, GBP.$money_format->format($vat), 0, 1, 'R');
    }

    function TotalCell($total) {
        $this->SetX(self::ORDER_NO_POS[0]);
        $this->setFont('opensansbold', '', 16);
        $this->setTextColor(...self::BLACK);
        $this->SetDrawColor(...self::LILAC);
        $this->Cell(0, 8, "TOTAL", 'T', 0, 'L');
        $this->SetX(self::PRICE_X);
        $money_format = new NumberFormatter("en_GB", NumberFormatter::DECIMAL);
        $money_format->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, 2);
        $this->Cell(0, 10, GBP.$money_format->format($total), 'T', 1, 'R');

    }

    function Spacer($height = 10) {
     $this->Cell(0, $height, '', 0, 1);   
    }

    function ReportDetailsCell ($report) {
        $this->DateCell($report);
        // $this->AddressCell($report);
        $this->ReportNoCell($report);

        $this->Spacer();
        $this->SubTotalCell($report['totals']['subtotal']);
        $this->Spacer();
        $this->ShippingCell($report['totals']['shipping']);
        $this->Spacer();
        $this->VatCell($report['totals']['vat']);
        $this->Spacer();
        $this->TotalCell($report['totals']['total']);
        $this->Spacer(20);
    }
}

function makeReportPDF($report) {
    $pdf = new ORDER_PDF();
    $pdf->Init($report);
    $pdf->AddPage();
    $pdf->ReportDetailsCell($report);
    $pdf->Output('D', "Unbelievable Truth Sales Report year ending " . $report["year_ending"] . ".pdf");
}
