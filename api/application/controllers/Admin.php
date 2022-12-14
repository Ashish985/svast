<?php 
class Admin extends CI_Controller{

  public function __construct(){

    parent::__construct();
    Header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
    Header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
    Header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
    Header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
    //load database
    $this->load->database();
    $this->load->model(array("Admin_model"));
    $this->load->library(array("form_validation"));
    $this->load->helper("security");
  }

	public function ExlfileUpload()
	{
    $_FILES = json_decode(file_get_contents('php://input'), true);
    $user = $this->authUserToken([1]);
    if($user){
    
      $folderPath = "/assets";
      $img = $_FILES['exc_file'];
      $pms_id = $_FILES['pms_id'];
      $code = $_FILES['fileSource'];
      $image_parts1 = explode(";base64", $code);
      $image_base64 = base64_decode($image_parts1[1]);
      $file_name = explode('\\',$img);
      $file = $file_name[2];
    
      if(file_put_contents($file, $image_base64)){
        // echo 'Inside if block';
          $this->load->library('excel');
          $objReader= PHPExcel_IOFactory::createReader('Excel2007');
          $objReader->setReadDataOnly(true);     
          $objPHPExcel=$objReader->load($file);
          $totalrows=$objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
          $objWorksheet=$objPHPExcel->setActiveSheetIndex(0);
          $arr2 = array();

          for($i=2; $i<$totalrows; $i++)
          {   
            $uid            = $objWorksheet->getCellByColumnAndRow(0,$i)->getValue();
            $facility       = $this->Admin_model->get_clients_ByID('tbl_clients', 'name', $objWorksheet->getCellByColumnAndRow(1,$i)->getValue())['id'];
            $carrier_name   = $objWorksheet->getCellByColumnAndRow(2,$i)->getValue();
            $voucher_number = $objWorksheet->getCellByColumnAndRow(3,$i)->getValue();    
            $account_number = $objWorksheet->getCellByColumnAndRow(4,$i)->getValue();
            $patient_name   = $objWorksheet->getCellByColumnAndRow(5,$i)->getValue();
            $service_date   = date("Y-m-d", strtotime($objWorksheet->getCellByColumnAndRow(6,$i)->getValue()));   
            $billed_date   = date("Y-m-d", strtotime($objWorksheet->getCellByColumnAndRow(7,$i)->getValue()));   
            $fees           = $objWorksheet->getCellByColumnAndRow(8,$i)->getValue();   
            $balance        = $objWorksheet->getCellByColumnAndRow(9,$i)->getValue();   
            date_default_timezone_set("America/New_York");
            $created_date   = date('Y-m-d H:i:s', time());

            $subscribers_data = array(
              'UID'          =>$uid,
              'Facility'     =>$facility,
              'CarrierName'  => $carrier_name,
              'VoucherNumber'=>$voucher_number,
              'AccountNumber'=>$account_number,
              'PatientName'  => $patient_name,
              'ServiceDate'  =>$service_date,
              'BilledDate'  =>$billed_date,
              'Fees'         =>$fees,
              'Balance'      =>$balance,
              'inserted_date'=> $created_date,
              'ATBDate'=>$created_date,
              'AgingDays'=> $this->getDaysDiff($service_date),
              'AgingBucket'=> $this->getBucket($this->getDaysDiff($service_date)) ,
              'BilledDays' =>  $this->getDaysDiff($billed_date) ,
              'BilledBucket' => $this->getBucket($this->getDaysDiff($billed_date)) ,
              
              'pms_id'=> $pms_id,
              'Name'=>$user['id'],
              "assigned_to"=> NULL
            );
            $id = $this->Admin_model->insertFileData('tbl_output',$subscribers_data);
            // echo date($service_date);
            // echo date('Y-m-d H:i:s', $service_date);
            // echo date(time() + $service_date);
          }
        
      }

      $arr = array(
        'status' => 'success',
        'message' => 'OK',
      );
      echo json_encode($arr);
    }
    else{
      $arr = array(
        'status' => 'error',
        'message' => 'Modification not allowed',
      );
      echo json_encode($arr);
    }
  
  }



  private function getDaysDiff($date){
    $now = time(); // or your date as well
    $your_date = strtotime($date);
    $datediff = $now - $your_date;

    return round($datediff / (60 * 60 * 24));
  }

  private function getBucket($days){
    if($days >= 0 && $days <= 30){
      return '0-30';
    }else if($days >= 31 && $days <= 60){
      return '31-60';
    }else if($days >= 61 && $days <= 90){
      return '61-90';
    }else if($days >= 91 && $days <= 120){
      return '91-120';
    }else if($days >= 121 && $days <= 180){
      return '121-180';
    }else if($days >= 181 && $days <= 365){
      return '181-365';
    }else if($days >= 365){
      return '365+';
    }else{
      return NULL;
    }
  }
 
  public function otputData($page, $row_limit)
	{ 
    $first_page= false;
    $last_page = false;
    $total_records = $this->Admin_model->getCount('tbl_output');
    // $row_limit = 20;
    $total_pages = ceil($total_records/$row_limit);

    // handle errors
    if ($page > $total_pages || $page < 1){
      $arr = array(
        'status' => 'error',
        'message' => 'Invalid page number.',
      );
      echo json_encode($arr);
    }

    else{

      $skip = 0;
      if($page > 1){
        $first_page = false;
        $skip = $row_limit* ($page - 1);
      }
      else{
        $first_page = true;
      }
      if($total_pages == $page){
        $last_page = true;
      }
      $data_arr = $this->Admin_model->getOutputData($skip, $row_limit);

      $mDataArr = $this->Admin_model->mgetOutputData($data_arr);

      $arr = array(
        'status' => 'success',
        'first_page' => $first_page,
        'last_page' => $last_page,
        'total_pages' =>  $total_pages,
        'current_page' => $page,
        'total_records' => $total_records,
        'data'=> $mDataArr,
      );
      echo json_encode($arr);
    }

  }

  // get output file data by id
  public function getOutputFile($id)
	{ 
    $ress = $this->Admin_model->outputfileGet('tbl_output',$id);
    $res = $this->Admin_model->mgetOutputData2($ress);

    //print_r($query->result());    

    if(count($res) > 0){

      $arr = array(
        "status" =>"success",
        "message" => "data found",
        "data" => $res
      );
      echo json_encode($arr);
    }else{

      $arr = array(
        "status" => "error",
        "message" => "No data found",
        "data" => $res
      );
      echo json_encode($arr);
    }    
  }
  
  // update output file data by id
  public function outputfileUpdate($id)
	{ 
    
    $data = json_decode(file_get_contents("php://input"));
  
    if (isset($data->comments) && isset($data->statusName) && isset($data->subStatus) && isset($data->actionCode) 
       && isset($data->accountType) && isset($data->followUpDate) && isset($data->workedDate)) {
       
       $data = array(
        "Comments"           => $data->comments,
        "StatusName"      => $data->statusName,
        "SubStatus"   => $data->subStatus,
        "ActionCode" => $data->actionCode,
        "AccountType" => $data->accountType,
        "FollowUpDate"   => date("Y-m-d", strtotime($data->followUpDate)),
        "WorkedDate"   => date("Y-m-d", strtotime($data->workedDate)),
       );
        // echo json_encode($data);
        
       if ($this->Admin_model->outputfileEdit('tbl_output', $id, $data)) {
 
        $arr = array(
          'status' => "success",
          'message' => 'file data updated successfully',
        );
        echo json_encode($arr);
       } else {

         $arr = array(
          'status' => "error",
          'message' => 'Failed to update file data',
         );
        echo json_encode($arr);
       }
    } else {

        $arr = array(
         'status' => "error",
         'message' => 'All fields are needed',
        );
        echo json_encode($arr);
    }              
  }
  
  //delete output file data by id
  public function outputfileDelete()
  {  
    $data = json_decode(file_get_contents("php://input"));
    $id = $data->id;
    $is_del = $this->Admin_model->outputfileDeletedById('tbl_output', $id);

    echo json_encode($is_del);
  }
    

      // delete multiple data from excel file 
  public function outputFileDeleteMultiple(){
    $_POST = json_decode(file_get_contents('php://input'), true);
    $data = $this->input->post();
    $ids = $data['ids'];
    
    $is_del = $this->Admin_model->deletedByIdMul('tbl_output', $ids);

    echo json_encode($is_del);    

  }

 

  public function updateMultiple(){
    $_POST = json_decode(file_get_contents('php://input'), true);
    $data = $this->input->post();
    $is_data = $data['data'];
    // print_r($is_data);
    
    $res = $this->Admin_model->updateByIdMul($is_data);

    echo json_encode($res);  

  }

   //create new clients
  public function createClient()
	{
	  
	  $name = $this->input->post('name');
	  $pmsId = $this->input->post('pms_id');
    $filename = NULL;
    $isUploadError = FALSE;

			// if ($_FILES && $_FILES['avatar']['name']) {
        if (true) {
        
				$config['upload_path']          = './assets/clientimage/';
	            $config['allowed_types']        = 'gif|jpg|png|jpeg';
	            $config['max_size']             = 50000;

	            $this->load->library('upload', $config);
	            if ( ! $this->upload->do_upload('avatar')) {

	            	$isUploadError = TRUE;

					$response = array(
						'status' => 'error',
						'message' => $this->upload->display_errors()
					);
	            }
	            else {
	            	$uploadData = $this->upload->data();
            		$filename = $uploadData['file_name'];
	            }
			}

			if( ! $isUploadError) {
	        	$blogData = array(
					 
					'image' => $filename,
          'name' => $name,
          'pms_id' => $pmsId,
          
			 
				);

				$id1 = $this->Admin_model->create('tbl_clients',$blogData);
				// $id2 = $this->Admin_model->create('pms_client_map',array(
					 
				// 	'pms_id' => $pmsId,
        //   'client_id' => $id1,
          
			 
				// ));
        

				$response = array(
					'status' => 'success',
          'data'=>$blogData
				);
			}

			$this->output
				->set_status_header(200)
				->set_content_type('application/json')
				->set_output(json_encode($response)); 
	}

  
  //check clients data
  public function client()
	{ 
    
    $_POST = json_decode(file_get_contents('php://input'), true);
    $data = $this->input->post();
    $name = $data['name'];
  
    $res=$this->Admin_model->check_client('tbl_clients', $name);
    $data_arr = $this->Admin_model->get_clients('tbl_clients');
  
    if ($res) {
      $arr = array(
        'status' => 'success',
        'message'=> 'client found',
        'data'=> $data_arr
      );
      echo json_encode($arr);
    }
    else{
      $arr = array(
        'status' => 'error',
        'message'=> 'not found',
        'data'=> $data_arr
      );
      echo json_encode($arr);
    }
      
  }
  
  //get all client
  public function clients($page, $row_limit)
	{ 
    $user = $this->authUserToken([1]);
    if($user){
      $data_arr = $this->Admin_model->get_clients_temp('tbl_clients');
  
      $first_page= false;
        $last_page = false;

        $total_records = count($data_arr);

      
        $total_pages = ceil($total_records/$row_limit);

        // // handle errors
        if ($page > $total_pages || $page < 1){
          $arr = array(
            'status' => 'error',
            'message' => 'Invalid page number.',
          );
          echo json_encode($arr);
        }

        else{

          $skip = 0;
          if($page > 1){
            $first_page = false;
            $skip = $row_limit* ($page - 1);
          }
          else{
            $first_page = true;
          }
          if($total_pages == $page){
            $last_page = true;
          }
          $data_arr = $this->Admin_model->get_clients_temp1('tbl_clients', $skip, $row_limit);
          $arr = array(
            'status' => 'success',
            'first_page' => $first_page,
            'last_page' => $last_page,
            'total_pages' =>  $total_pages,
            'current_page' => $page,
            'total_records' => $total_records,
            'data'=> $data_arr,
          );
          echo json_encode($arr);
        }
      
    }else{
      $arr = array(
        'status' => "error",
        'message' => 'Login failed'
       );
       echo json_encode($arr);
    }
      
  }

  //delete clients data by id
  public function deleteClient()
	{  
    $data = json_decode(file_get_contents("php://input"));
    $id = $data->id;
    $is_del = $this->Admin_model->outputfileDeletedById('tbl_clients', $id);

    echo json_encode($is_del);
  }
  
   // update clients data by id
  public function updateClient()
	{   
   
	  $name = $this->input->post('name');
	  $id = $this->input->post('id');
    $pmsId = $this->input->post('pms_id');

    $dbimg = $this->Admin_model->outputfileGet('tbl_clients',$id);
		$img=$dbimg[0];
    $img_file= $img->image;
    
    $isUploadError = FALSE;

			if ($_FILES && $_FILES['avatar']['name']) {
       
				      $config['upload_path']          = './assets/clientimage/';
	            $config['allowed_types']        = 'gif|jpg|png|jpeg';
	            $config['max_size']             = 50000;

	            $this->load->library('upload', $config);
	            if ( ! $this->upload->do_upload('avatar')) {

	            	$isUploadError = TRUE;

					$response = array(
						'status' => 'error',
						'message' => $this->upload->display_errors()
					);
	            }
	            else {
	   
					if($img_file && file_exists(FCPATH.'./assets/clientimage/'.$img_file))
					{
						unlink(FCPATH.'./assets/clientimage/'.$img_file);
					}

	            	$uploadData = $this->upload->data();
            		$filename = $uploadData['file_name'];
	            }
			}

			if( ! $isUploadError) {
	        	$data = array(
			 
					'name' => $name,
					'image' => $filename,
          'pms_id' => $pmsId
					 
				);

				if($this->Admin_model->outputfileEdit('tbl_clients',$id, $data)){
          $arr = array(
            'status' => "success",
            'message' => 'Record updated successfully',
          );
          echo json_encode($arr);
        }
        else{
          $arr = array(
            'status' => "error",
            'message' => 'Failed to update',
          );
          echo json_encode($arr);
        }
				 
      }

  }

  //create new PMS 
  public function createPMS()
	{
	  
	  $name = $this->input->post('name');
    $filename = NULL;
    $isUploadError = FALSE;

			// if ($_FILES && $_FILES['avatar']['name']) {
        if (true) {
        
				$config['upload_path']          = './assets/clientimage/';
	            $config['allowed_types']        = 'gif|jpg|png|jpeg';
	            $config['max_size']             = 50000;

	            $this->load->library('upload', $config);
	            if ( ! $this->upload->do_upload('avatar')) {

	            	$isUploadError = TRUE;

					$response = array(
						'status' => 'error',
						'message' => $this->upload->display_errors()
					);
	            }
	            else {
	            	$uploadData = $this->upload->data();
            		$filename = $uploadData['file_name'];
	            }
			}

			if( ! $isUploadError) {
	        	$blogData = array(
					 
					'image' => $filename,
          'name' => $name
			 
				);

				$id = $this->Admin_model->create('tbl_pms',$blogData);

				$response = array(
					'status' => 'success',
          'data'=>$blogData
				);
			}

			$this->output
				->set_status_header(200)
				->set_content_type('application/json')
				->set_output(json_encode($response)); 
	}


  private function mappingStatus($id){
    $totalOutputCols = $this->Admin_model->getCount('output_file_cols');
    $map_count = $this->Admin_model->get_whereJ_SF('pms_system_cols', 'pms_id', $id, 'id, name as pms_col_name, map_col_id as output_col_id');
    // return $totalOutputCols;
    if(count($map_count) == 0){
      return 'not mapped';
    }else if(count($map_count) == $totalOutputCols){
      return 'mapped';
    }else{
      return 'partially mapped';
    }

  }

  //get all pms
  public function GetPMS($page,$row_limit)
  { 
    $user = $this->authUserToken([1,2]);
    if($user['role'] == 2){
      $SQL = "SELECT distinct pms.* FROM `manager_agent` m_a INNER JOIN tbl_clients as client ON client.id = m_a.client INNER JOIN tbl_pms as pms ON pms.id = client.pms_id where m_a.manager = ".$user['id'];
      $query = $this->db->query($SQL);
      $arr = array(
        'status' => 'success',
        'message' => "OK",
        'data'=> $query->result_array()
      );
      echo json_encode($arr);
    }
    else if($user['role'] == 1){
      $first_page= false;
      $last_page = false;
      $data_arr = $this->Admin_model->get_clients_pms_temp('tbl_pms');
      $total_records = count($data_arr);
      $total_pages = ceil($total_records/$row_limit);
      
      $data = array();
      if ($page > $total_pages || $page < 1){
        $arr = array(
          'status' => 'error',
          'message' => 'Invalid page number.',
        );
        echo json_encode($arr);
      }
      else{
        $skip = 0;
        if($page > 1){
          $first_page = false;
          $skip = $row_limit* ($page - 1);
        }
        else{
          $first_page = true;
        }
        if($total_pages == $page){
          $last_page = true;
        }
        $data_arr = $this->Admin_model->get_clients_pms_temp1('tbl_pms', $skip, $row_limit);
        foreach ($data_arr as $map) { 
          array_push($data,  array(      
            "id"=> $map->id,
            "image"=> $map->image,
            "name"=> $map->name,
            "created_date"=> $map->created_date,
            "clients"=> $this->Admin_model->get_where_temp('tbl_clients', 'pms_id', $map->id),
            "cols" => $this->Admin_model->get_where_SF('pms_system_cols', 'pms_id', $map->id, 'id, name as pms_col_name, map_col_id as output_col_id'),
            "mapping_status" => $this->mappingStatus($map->id)
          ));
        }
          // print_r($data_arr);
        if ($data_arr) {
          $arr = array(
            'status' => 'success',
            'first_page' => $first_page,
            'last_page' => $last_page,
            'total_pages' =>  $total_pages,
            'current_page' => $page,
            'total_records' => $total_records,
            'data'=> $data
          );
          echo json_encode($arr);
        }
        else{
          $arr = array(
            'status' => 'error',
            'message'=> 'error'
          );
          echo json_encode($arr);
        }
      }
      
    }
    else{
      $arr = array(
        'status' => 'error',
        'message'=> 'unauthenticated'
      );
      echo json_encode($arr);
    }
      
  }

  //delete PMS data by id
  public function deletePMS()
	{  
    $data = json_decode(file_get_contents("php://input"));
    $id = $data->id;
    $is_del = $this->Admin_model->outputfileDeletedById('tbl_pms', $id);

    echo json_encode($is_del);
  }
  
  // update PMS  data by id
  public function updatePMS()
	{ 
  
	  $name = $this->input->post('name');
	  $id = $this->input->post('id');

    $dbimg = $this->Admin_model->outputfileGet('tbl_pms',$id);
		$img=$dbimg[0];
    $img_file= $img->image;
    
   
    $isUploadError = FALSE;

			if ($_FILES && $_FILES['avatar']['name']) {
       
				      $config['upload_path']          = './assets/clientimage/';
	            $config['allowed_types']        = 'gif|jpg|png|jpeg';
	            $config['max_size']             = 50000;

	            $this->load->library('upload', $config);
	            if ( ! $this->upload->do_upload('avatar')) {

	            	$isUploadError = TRUE;

					$response = array(
						'status' => 'error',
						'message' => $this->upload->display_errors()
					);
	            }
	            else {
	   
					if($img_file && file_exists(FCPATH.'./assets/clientimage/'.$img_file))
					{
						unlink(FCPATH.'./assets/clientimage/'.$img_file);
					}

	            	$uploadData = $this->upload->data();
            		$filename = $uploadData['file_name'];
	            }
			}

			if( ! $isUploadError) {
	        	$data = array(
			 
					'name' => $name,
					'image' => $filename
					 
				);

				if($this->Admin_model->outputfileEdit('tbl_pms',$id, $data)){
          $arr = array(
            'status' => "success",
            'message' => 'Record updated successfully',
          );
          echo json_encode($arr);
        }
        else{
          $arr = array(
            'status' => "error",
            'message' => 'Failed to update',
          );
          echo json_encode($arr);
        }
				 
      }      
  }


  public function GetManagerAgent(){

    $managers = $this->Admin_model->get_data('tbl_users', 2);
    $agents = $this->Admin_model->get_data('tbl_users', 3);
    // $manager_agents = array(
    //   'manager' => ,
    //   'agents' => $agents
    // );

    $data = array(
      'managers' => $managers,
      'agents' => $agents
      // 'manager_agents' => $manager_agents
    );

    $arr = array(
      'status' => "success",
      'message' => 'OK',
      'data' => $data,
     );
     echo json_encode($arr);
  }

  public function GetManagerAgent2(){
    $user = $this->authUserToken([2]);
    if($user){
      $agents = $this->Admin_model->get_data2('manager_agent', 'manager', $user['id']);
  
      $data = array(
        'agents' => $agents
      );
  
      $arr = array(
        'status' => "success",
        'message' => 'OK',
        'data' => $data,
       );
       echo json_encode($arr);
    }
    else{
      $arr = array(
        'status' => "error",
        'message' => 'Manager not found',
       );
       echo json_encode($arr);
    }
  }


  public function AssignManagerAgent(){
    $_POST = json_decode(file_get_contents('php://input'), true);
    $data = $this->input->post();
    $arrs = array();
    foreach ($data['agents'] as $agent) {
      array_push($arrs,array('manager' => $data['manager'], 'agent' => $agent, 'client' => null));
    }
    foreach ($data['clients'] as $client) {
      array_push($arrs,array('manager' => $data['manager'], 'client' => $client, 'agent' => null));
    }
    $this->Admin_model->insert_data('manager_agent',$arrs);

    $arr = array(
      'status' => "success",
      'message' => 'Assigned Successfully',
     );
     echo json_encode($arr);
  }

  public function AssignAgentClient(){
    $_POST = json_decode(file_get_contents('php://input'), true);
    $user = $this->authUserToken([2]);
    $data = $this->input->post();
    $arrs = array();
    foreach ($data['clients'] as $client) {
      array_push($arrs,array('manager_id'=> $user['id'], 'agent_id' => $data['agent'], 'client_id' => $client));
    }
    $this->Admin_model->insert_data('agent_client',$arrs);

    // assign in tbl_output need to comment 
    // commented code for 1 to many
    // $pms_ids = array();

    // foreach ($data['clients'] as $client) {
    //   array_push($pms_ids, $this->Admin_model->get_clients_ByID('tbl_clients', 'id', $client)['pms_id']);
    // }
    // $clients_arr = implode(', ', $data['clients']);
    // $pms_arr = implode(', ', $pms_ids);
    // // $where = $this->Admin_model->update_tbl_output('tbl_output', 'Facility', $data['clients'], $data['agent'], $pms_ids) ;
    // // $SQL = "UPDATE `tbl_output` SET `assigned_to` = ".$data['agent']." WHERE `Facility` IN (".implode(', ', $data['clients']).") AND `pms_id` IN (".implode(', ', $pms_ids).")";
    // $SQL = "UPDATE `tbl_output` SET `assigned_to` = '".$data['agent']."' WHERE `Facility` IN ('". $clients_arr ."') AND `pms_id` IN ('". $pms_arr ."')";
    // $query = $this->db->query($SQL);

    // $arr = array(
    //   'status' => "success",
    //   'message' => $SQL,
    //  );
    $arr = array(
      'status' => "success",
      'message' => "Assigned Successfully",
     );
     echo json_encode($arr);
  }

  public function AssignClaimsAgent(){
    $_POST = json_decode(file_get_contents('php://input'), true);
    $data = $this->input->post();
    $arrs = array();
    foreach ($data['claims'] as $claim) {
      array_push($arrs,array('assigned_to' => $data['agent'], 'id' => $claim));
    }

    foreach($arrs as $row){
      $this->Admin_model->update_table2('tbl_output',$row);
    }

    $arr = array(
      'status' => "success",
      'message' => 'Assigned Successfully',
     );
     echo json_encode($arr);
  }

  public function GetAssignClaimsAgent($page, $row_limit){
    $user = $this->authUserToken([3]);
    if($user){
      $data_arr = $this->Admin_model->get_where_new('tbl_output','assigned_to', $user['id']);
  
      $first_page= false;
        $last_page = false;

        $total_records = count($data_arr);

      
        $total_pages = ceil($total_records/$row_limit);

        // // handle errors
        if ($page > $total_pages || $page < 1){
          $arr = array(
            'status' => 'error',
            'message' => 'Invalid page number.',
          );
          echo json_encode($arr);
        }

        else{

          $skip = 0;
          if($page > 1){
            $first_page = false;
            $skip = $row_limit* ($page - 1);
          }
          else{
            $first_page = true;
          }
          if($total_pages == $page){
            $last_page = true;
          }
          $data_arr = $this->Admin_model->getOutputDataWhere($skip, $row_limit, 'assigned_to', $user['id']);

          $mDataArr = $this->Admin_model->mgetOutputData($data_arr);

          $arr = array(
            'status' => 'success',
            'first_page' => $first_page,
            'last_page' => $last_page,
            'total_pages' =>  $total_pages,
            'current_page' => $page,
            'total_records' => $total_records,
            'data'=> $mDataArr,
          );
          echo json_encode($arr);
        }
      
    }else{
      $arr = array(
        'status' => "error",
        'message' => 'Login failed'
       );
       echo json_encode($arr);
    }
  }

  public function GetManagerAgentMapping(){
    $user = $this->authUserToken([2]);
    if($user){
      $SQL = "SELECT distinct pms.* FROM `manager_agent` m_a INNER JOIN tbl_clients as client ON client.id = m_a.client INNER JOIN tbl_pms as pms ON pms.id = client.pms_id where m_a.manager = ".$user['id'];
      $query = $this->db->query($SQL);
      // $query->result_array();

      $data = array(
        "agents" => $this->Admin_model->get_where2('manager_agent','manager', $user['id']),
        "clients" => $this->Admin_model->get_where3('manager_agent','manager', $user['id']),
        "pms" => $query->result_array()
      );
  
      $arr = array(
        'status' => "success",
        'message' => 'OK',
        'data' => $data
       );
       echo json_encode($arr);
    }else{
      $arr = array(
        'status' => "error",
        'message' => 'Login failed'
       );
       echo json_encode($arr);
    }
  }

  public function verifyAuthToken($token)
    {
        $jwt = new JWT();
        $jwtSecret = 'myloginSecret';
        $verification = $jwt->decode($token, $jwtSecret, 'HS256');
        return $verification;
        // $verification_json = $jwt->jsonEncode($verification);
        // return $verification_json;

  }

  public function authUserToken($roleArr)
    {
        $req = $this->input->request_headers();
        if (array_key_exists('Authorization', $req)) {
            $token = ltrim(substr($req['Authorization'], 6));
            
            $token_data = $this->verifyAuthToken($token);
            // print_r($token_data);
            date_default_timezone_set('Asia/Kolkata');
            $current_date = date('Y-m-d H:i:s', time());
            $token_date = date("Y-m-d H:i:s", $token_data->exp);

            // echo strtotime($current_date);
            // echo strtotime($token_date);
            // echo strtotime($current_date) - strtotime($token_date);

            if ((strtotime($current_date) - strtotime($token_date)) < 0) {
                // get role from email
                $user_email = $token_data->sub;

                // return data getting by email
                $res = $this->Users_model->getUserProfile('tbl_users', $user_email);
                // print_r($res);
                $role = $res['role'];
                // if role of user is exist in $role arrya ten return false else data
                if (in_array($role, $roleArr)) {
                    return $res;
                } else {
                    //role is not matched means not autheticated for this action
                    // echo "false";
                    return false;
                }
            } else {
                // if tooken invalid or expired then return
                return false;
            }
        } else {
            //if auth key not in header then return
            return false;
        }
  }  

  public function getMappedAgent(){
    $manager = $this->Admin_model->get_mngrId('manager_agent');
    $agent = $this->Admin_model->get_agentId('manager_agent');
  
    $agent_id=implode(', ', array_column($agent, 'agent'));
    
    $manager_id = "(" ."'" .implode("', '",array_column($manager, 'manager')) . "'". ")";
    // echo $comma_list;

    $this->db->select("name,email,manager,agent"); // Select field
    $this->db->from('tbl_users'); // from Table1
    $this->db->join('manager_agent','tbl_users.id = manager_agent.manager','INNER'); // Join table1 with table2 based on the foreign key
    $this->db->group_by('manager_agent.manager'); 
    // Set Filter
    // $this->db->where('tbl_users.id',8); // Set Filter
    // $this->db->where_in('tbl_users.id');
    $res = $this->db->get();
    echo json_encode($res->result());

  }


  public function DashboardDataAdmin(){

    $managers = $this->Admin_model->get_data('tbl_users', 2);
    $agents = $this->Admin_model->get_data('tbl_users', 3);
    $users = $this->Admin_model->get_count('tbl_users');

    $data = array(
      'managers' => $managers,
      'agents' => $agents,
      'users' => $users
    );

    $arr = array(
      'status' => "success",
      'message' => 'OK',
      'data' => $data,
     );
     echo json_encode($arr);
  }

  public function MATempMap(){
    $managers = $this->Admin_model->get_data('tbl_users', 2);
    // $agents = $this->Admin_model->get_data('tbl_users', 3);
    // $clients = $this->Admin_model->get_all_data('tbl_clients');
    $agents = $this->Admin_model->get_all_unassign_data('tbl_users', 'agent');
    $clients = $this->Admin_model->get_all_unassign_data('tbl_clients', 'client');
    $data_arr =  $this->Admin_model->get_where_temp('tbl_users', 'role', '2');
 
    // 
    // $data_arr = $this->Admin_model->get_clients_pms_temp('tbl_pms');
      $data = array();

      foreach ($data_arr as $map) { 
        array_push($data,  array(
          "id"=> $map->id,
          "name"=> $map->name,
          "agent"=> $this->Admin_model->get_where_temp_agent('manager_agent', 'manager', $map),
          "client"=> $this->Admin_model->get_where_temp_client('manager_agent', 'manager', $map)
        ));
      }
    // 
    
    $data = array(
      'managers' => $managers,
      'agents' => $agents,
      'clients' => $clients,
      'mapping' => $data,
    );
    $arr = array(
      'status' => "success",
      'message' => 'Assigned Successfully',
      'data' => $data
     );
     echo json_encode($arr);
  }

  public function DelMATempMap(){
    $_POST = json_decode(file_get_contents('php://input'), true);
    $data = $this->input->post();

    $manager_id = $data['manager_id'];
    $client_id = $data['client_id'];
    $agent_id = $data['agent_id'];

    

    if($agent_id != "null"){
      $data = array(
        "agent" => $agent_id,
        "manager_id" =>$manager_id
      );
      $result =  $this->Admin_model->delete_where('manager_agent', 'agent', $data);
    }
    if($client_id != "null"){
      $data = array(
        "client" => $client_id,
        "manager_id" =>$manager_id
      );
      $result =  $this->Admin_model->delete_where('manager_agent', 'client', $data);
      
    }

     echo json_encode($result);
  }


  public function PostOutputColumns(){
    $_POST = json_decode(file_get_contents('php://input'), true);
    $data = $this->input->post();
    $arrs = array();

    $id=$data['pms_id'];
    
    $this->Admin_model->delete_where_id('pms_system_cols', 'pms_id', $id);


    foreach ($data['mapValues'] as $val) {
      
      array_push($arrs,array('name'=>$val['pms_col_name'],'map_col_id' => $val['output_col_id'] == "0" ? NULL : $val['output_col_id'], 'pms_id' => $id));

    }
    $this->Admin_model->insert_data('pms_system_cols',$arrs);

    $arr = array(
      'status' => "success",
      'message' => 'Assigned Successfully',
     );
     echo json_encode($arr);
  }
  
  public function GetOutputColumns(){
    $data = $this->Admin_model->get_all_data('output_file_cols');
    echo json_encode(array(
      "status" => 'success',
      "message" => 'OK',
      'data' => $data
    ));
  }

  public function MatchPMS($id){
    $data = $this->Admin_model->get_whereJ_SF('pms_system_cols', 'pms_id', $id, "*");
    echo json_encode(array(
      "status" => 'success',
      "message" => 'OK',
      'data' => $data,
      'total_records' => count($data)
    ));
  }

  public function unAssignAgent(){
    $_POST = json_decode(file_get_contents('php://input'), true);
    $data = $this->input->post();
    // $id = $data['id'];
    // $assign_id = $data['assign_id'];

    $data = $this->Admin_model->update_table('tbl_output', 'assigned_to', $data);
    echo json_encode(array(
      "status" => 'success',
      "message" => $data
    ));
  }

  
  public function DelAgentClient(){
    $user = $this->authUserToken([2]);
    $_POST = json_decode(file_get_contents('php://input'), true);
    $data = $this->input->post();
    
    $manager_id = $user['id'];
    $client_id = $data['client_id'];
    $agent_id = $data['agent_id'];
    
    $data = array(
      "agent_id" => $agent_id,
      "client_id" => $client_id,
      "manager_id" =>$manager_id
    );
    $result =  $this->Admin_model->delete_where2('agent_client', 'agent_id', $data);

    $client_pms = $this->Admin_model->get_clients_ByID('tbl_clients', 'id', $client_id)['pms_id'];
    // need to removed
    $this->Admin_model->del_tbl_output('tbl_output', 'Facility', $client_id, $agent_id, $client_pms);
    
    
    echo json_encode($result);
  }

  public function GetAgentClient(){
    $user = $this->authUserToken([2]);
    $agents = $this->Admin_model->get_where_not_null('manager_agent', 'manager', $user['id'], 'agent');
    $clients = $this->Admin_model->get_where_not_null('manager_agent', 'manager', $user['id'], 'client');
   
    //    uncomment for 1 to many 
    // $SQL1 = "SELECT * FROM tbl_clients WHERE id NOT IN ( SELECT client.client_id FROM `manager_agent` m_a INNER JOIN agent_client as client ON client.manager_id = m_a.manager WHERE m_a.manager = 15 AND m_a.client IS NOT NULL)";
    // $SQL1 ="SELECT * FROM tbl_clients WHERE id IN ( SELECT m_a.client FROM `manager_agent` m_a WHERE m_a.manager = 15 AND m_a.client IS NOT NULL) AND id NOT IN ( SELECT client.client_id FROM `manager_agent` m_a INNER JOIN agent_client as client ON client.manager_id = m_a.manager WHERE m_a.manager = 15 AND m_a.client IS NOT NULL)";
    // $query1 = $this->db->query($SQL1);
    // $clients = $query1->result_array();

    $data_arr = array();

    foreach ($agents as $map) { 
      array_push($data_arr,  array(
        "id"=> $map->id,
        "agent_name"=> $map->agent_name,
        "agent_id" => $map->agent,
        "clients"=> $this->Admin_model->get_where_temp_client2('agent_client', 'manager_id', $map)
      ));
    }
    
    $data = array(
      'agents' => $agents,
      'clients' => $clients,
      'mapping' => $data_arr
    );
    $arr = array(
      'status' => "success",
      'message' => 'OK',
      'data' => $data
     );
     echo json_encode($arr);
  }

  // public function GetAgentClient(){
  //   $user = $this->authUserToken([2]);
  //   $agents = $this->Admin_model->get_where_not_null('manager_agent', 'manager', $user['id'], 'agent');
  //   $clients = $this->Admin_model->get_where_not_null('manager_agent', 'manager', $user['id'], 'client');
  //   $data_arr =  $this->Admin_model->get_where_temp('tbl_users', 'role', '2');
 
  //   // 
  //   // $data_arr = $this->Admin_model->get_clients_pms_temp('tbl_pms');
  //     $data = array();

  //     foreach ($data_arr as $map) { 
  //       array_push($data,  array(
  //         "id"=> $map->id,
  //         "name"=> $map->name,
  //         "agent"=> $this->Admin_model->get_where_temp_agent('manager_agent', 'manager', $map),
  //         "client"=> $this->Admin_model->get_where_temp_client('manager_agent', 'manager', $map)
  //       ));
  //     }
  //   // 
    
  //   $data = array(
  //     'agents' => $agents,
  //     'clients' => $clients,
  //     'mapping' => $data,
  //   );
  //   $arr = array(
  //     'status' => "success",
  //     'message' => 'OK',
  //     'data' => $data
  //    );
  //    echo json_encode($arr);
  // }




  public function outputDataManager($page, $row_limit)
    { 
      $user = $this->authUserToken([2]);
      if($user['role'] == 2){
        $SQL = "SELECT distinct pms.* FROM `manager_agent` m_a INNER JOIN tbl_clients as client ON client.id = m_a.client INNER JOIN tbl_pms as pms ON pms.id = client.pms_id where m_a.manager = ".$user['id'];
        $query = $this->db->query($SQL);
        $pms_ids = [];

        foreach ( $query->result_array() as $pms) {
          array_push($pms_ids, $pms['id']);
        }

        $first_page= false;
        $last_page = false;

        $total_records = count($this->Admin_model->getCountWhereIn('tbl_output', 'pms_id', $pms_ids));

      
        $total_pages = ceil($total_records/$row_limit);

        // // handle errors
        if ($page > $total_pages || $page < 1){
          $arr = array(
            'status' => 'error',
            'message' => 'Invalid page number.',
          );
          echo json_encode($arr);
        }

        else{

          $skip = 0;
          if($page > 1){
            $first_page = false;
            $skip = $row_limit* ($page - 1);
          }
          else{
            $first_page = true;
          }
          if($total_pages == $page){
            $last_page = true;
          }
          $data_arr = $this->Admin_model->getOutputDataWhere($skip, $row_limit, 'pms_id', $pms_ids);

          $mDataArr = $this->Admin_model->mgetOutputData($data_arr);

          $arr = array(
            'status' => 'success',
            'first_page' => $first_page,
            'last_page' => $last_page,
            'total_pages' =>  $total_pages,
            'current_page' => $page,
            'total_records' => $total_records,
            'data'=> $mDataArr,
          );
          echo json_encode($arr);
        }
      }
      else{
        $arr = array(
          'status' => 'error',
          'message' => 'Un authenticated',
        );
        echo json_encode($arr);
      }
    }

  }

?>


