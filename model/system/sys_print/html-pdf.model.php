<?php

class htmltopdf {
    function generate($html) {
        $mpdf = new \Mpdf\Mpdf();
        // $mpdf->SetTitle($name);
        $mpdf->SetCompression(true);
        $mpdf->simpleTables = false;
        $mpdf->packTableData = true;
        $mpdf->WriteHTML($html);
        $mpdf->WriteHTML(ob_get_clean());
        $mpdf->Output('document.pdf', 'I');
    }
}