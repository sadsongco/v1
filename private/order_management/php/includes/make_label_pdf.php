<?php

require("../lib/fpdf.php");

class LABEL_PDF extends FPDF {
    const COLS = 2;
    const ROWS = 4;
    const NO_LABELS = self::COLS * self::ROWS;
    const LABEL_W = 105;
    const LABEL_H = 74;
    const ADD_MARGIN = 10;
    const RET_MARGIN_X = 38;
    const RET_MARGIN_Y = 50;
    const ORD_MARGIN_X = 90;
    const ORD_MARGIN_Y = 4;
    const SHIPPING_MARGIN_X = -50;

    function AddressCell ($row, $col, $order) {
        $this->SetFont('times', '', 15);
        $address = $order["address_1"];
        if ($order["address_2"] != "") $address.="\n".$order["address_2"];
        $this->SetXY((self::LABEL_W * $col) + self::ADD_MARGIN, (self::LABEL_H * $row) + self::ADD_MARGIN);
        $this->MultiCell(
            self::LABEL_W - (2 * self::ADD_MARGIN),
            5,
            iconv('UTF-8', "CP1250//TRANSLIT", $order["name"])."\n".iconv('UTF-8', "CP1250//TRANSLIT", $address)."\n".iconv('UTF-8', "CP1250//TRANSLIT", $order["city"])."\n".iconv('UTF-8', "CP1250//TRANSLIT", $order["postcode"])."\n".iconv('UTF-8', "CP1250//TRANSLIT", $order["country"]),
            0,
            'L',
            false
        );
    }

    function OrderNoCell ($row, $col, $order) {
        $this->SetFont('courier', '', 6);
        $h = 4;
        $this->SetXY((self::LABEL_W * $col) + self::ORD_MARGIN_X + self::SHIPPING_MARGIN_X, (self::LABEL_H * $row) + self::ORD_MARGIN_Y);
        $this->MultiCell(0, $h, $order['sumup_id'] . " // " . $order['shipping_method'] . " // " . $order['shipping'], 0, 'L', false);        
    }

    function ReturnCell ($row, $col) {
        $this->SetFont('courier', '', 8);
        $text = "if undelivered please return to:\nUnbelievable Truth\n52 Claremont Rd, Rugby CV21 3LX, UK";
        $w = $this->GetStringWidth($text);
        $h = 4;
        $this->SetXY((self::LABEL_W * $col) + self::RET_MARGIN_X, (self::LABEL_H * $row) + self::RET_MARGIN_Y);
        $this->MultiCell($w, $h, $text, 0, 'L', false);
    }
    
    function CreateLabels ($orders, $start_label = 1) {
        $this->AddPage();
        $start_label--;
        $start_col = $start_label%self::COLS;
        $start_row = floor(($start_label/self::COLS))%self::ROWS;
        $this->SetAutoPageBreak(false);
        for ($row = $start_row; $row < self::ROWS; $row ++) {
            for ($col = $start_col; $col < self::COLS; $col++) {
                $start_col = 0;
                if (sizeof($orders)==0) return;
                $order = array_shift($orders);
                $this->SetXY((self::LABEL_W * $col), (self::LABEL_H * $row));
                $this->AddressCell($row, $col, $order);
                $this->ReturnCell($row, $col);
                $this->OrderNoCell($row, $col, $order);
            }
        }
        if (sizeof($orders) > 0) $this->CreateLabels($orders);
    }


}

function MakeLabelPDF ($orders, $start_label = 1) {
    $pdf = new LABEL_PDF();
    $pdf->CreateLabels($orders, $start_label);
    $pdf->Output('D', "ut_labels.pdf");
}

?>