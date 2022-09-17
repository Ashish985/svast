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
       
  


      
      $save['email']  = 'ashish@mistpl.com';
      $save['name']   = 'Ashish';
      $save['verification_code'] = 'generateString(50)';

      if(1){
      $message = 'test message';
      $mail_config['smtp_host'] = 'smtp.gmail.com';
      $mail_config['smtp_port'] = '587';
      $mail_config['smtp_user'] = 'amangupta1542@gmail.com';
      $mail_config['_smtp_auth'] = TRUE;
      $mail_config['smtp_pass'] = 'ofeasqzmvrjcsxaf';
      $mail_config['smtp_crypto'] = 'tls';
      $mail_config['protocol'] = 'smtp';
      $mail_config['mailtype'] = 'html';
      $mail_config['send_multipart'] = FALSE;
      $mail_config['charset'] = 'utf-8';
      $mail_config['wordwrap'] = TRUE;
      $this->email->initialize($mail_config);

          $this->email->set_newline("\r\n");

          $this->load->library('email');
          $this->email->set_mailtype("html");
          $this->email->from('amangupta1542@gmail.com', 'Svast');
          $this->email->to($save['email']); 

          $this->email->subject('Comfirmation required Test mail');
          $this->email->message($message);  
          if($this->email->send())
          {
          //    $this->session->set_flashdata('success', 'Please verify your email. Info sent in email to you.');
          }
          else
          {
              // $this->session->set_flashdata('error', 'Something went wrong. Please share after some time.');
          }
      }
      else{
          // $this->session->set_flashdata('error', 'Something went wrong. Please share after some time.');
      }
