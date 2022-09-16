<?php
     
    $_FILES = json_decode(file_get_contents('php://input'), true);
    $this->load->library('excel');
    $objReader= PHPExcel_IOFactory::createReader('Excel2007');
    $objReader->setReadDataOnly(true);     
    $objPHPExcel=$objReader->load('testfie.xlsx');
    $totalrows=$objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
    $objWorksheet=$objPHPExcel->setActiveSheetIndex(0);
    $arr2 = array();

    for($i=2; $i<$totalrows; $i++)
    { 
        $uid           = $objWorksheet->getCellByColumnAndRow(0,$i)->getValue();
        $facility      = $objWorksheet->getCellByColumnAndRow(1,$i)->getValue();
        $carriername   = $objWorksheet->getCellByColumnAndRow(2,$i)->getValue();
        $vouchernumber = $objWorksheet->getCellByColumnAndRow(3,$i)->getValue();    
        $accountnumber = $objWorksheet->getCellByColumnAndRow(4,$i)->getValue();
        $patientname   = $objWorksheet->getCellByColumnAndRow(5,$i)->getValue();
        $servicedate   = $objWorksheet->getCellByColumnAndRow(6,$i)->getValue();
        $fees          = $objWorksheet->getCellByColumnAndRow(7,$i)->getValue();
        $balance       = $objWorksheet->getCellByColumnAndRow(8,$i)->getValue();   

        $output_data = array(
          'UID'           => $uid,
          'Facility'      => $facility,
          'CarrierName'   => $carriername,
          'VoucherNumber' => $vouchernumber,
          'AccountNumber' => $accountnumber,
          'PatientName'   => $patientname,
          'ServiceDate'   => $servicedate,
          'Fees'          => $fees,
          'Balance'       => $balance
        );
        $id = $this->Admin_model->insertFileData('tbl_output',$output_data);
        echo $id." ";
    }
    echo json_encode($output_data);
    exit();
       