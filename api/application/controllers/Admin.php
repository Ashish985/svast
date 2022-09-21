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
    
		$folderPath = "/assets";
		$img = $_FILES['exc_file'];
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
          $facility       = $objWorksheet->getCellByColumnAndRow(1,$i)->getValue();
          $carrier_name   = $objWorksheet->getCellByColumnAndRow(2,$i)->getValue();
          $voucher_number = $objWorksheet->getCellByColumnAndRow(3,$i)->getValue();    
          $account_number = $objWorksheet->getCellByColumnAndRow(4,$i)->getValue();
          $patient_name   = $objWorksheet->getCellByColumnAndRow(5,$i)->getValue();
          $service_date   = $objWorksheet->getCellByColumnAndRow(6,$i)->getValue();   
          $fees           = $objWorksheet->getCellByColumnAndRow(7,$i)->getValue();   
          $balance        = $objWorksheet->getCellByColumnAndRow(8,$i)->getValue();   
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
            'Fees'         =>$fees,
            'Balance'      =>$balance,
            'inserted_date'=> $created_date
          );
          $id = $this->Admin_model->insertFileData('tbl_output',$subscribers_data);
          echo $id." ";
        }
      
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

  }

  // get output file data by id
  public function getOutputFile($id)
	{ 
    $res = $this->Admin_model->outputfileGet('tbl_output',$id);

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
  
    if (isset($data->uid) && isset($data->facility) && isset($data->carrierName) && isset($data->voucherNumber) 
       && isset($data->accountNumber) && isset($data->patientName)) {
       
       $data = array(
        "UID"           => $data->uid,
        "Facility"      => $data->facility,
        "CarrierName"   => $data->carrierName,
        "VoucherNumber" => $data->voucherNumber,
        "AccountNumber" => $data->accountNumber,
        "PatientName"   => $data->patientName
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

  public function createClient()
	{
	  $is_featured = $this->input->post('avatar');
	  $name = $this->input->post('name');
    $filename = NULL;
    $isUploadError = FALSE;

			// if ($_FILES && $_FILES['avatar']['name']) {
        if (true) {
        
				$config['upload_path']          = './assets/clientimage/';
	            $config['allowed_types']        = 'gif|jpg|png|jpeg';
	            $config['max_size']             = 500;

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

				$id = $this->Admin_model->create('tbl_clients',$blogData);

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
	

  public function createPMS()
	{
	  $is_featured = $this->input->post('avatar');
	  $name = $this->input->post('name');
    $filename = NULL;
    $isUploadError = FALSE;

			// if ($_FILES && $_FILES['avatar']['name']) {
        if (true) {
        
				$config['upload_path']          = './assets/clientimage/';
	            $config['allowed_types']        = 'gif|jpg|png|jpeg';
	            $config['max_size']             = 500;

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
	

  
  //create new clients
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
  public function clients()
	{ 
    $data_arr = $this->Admin_model->get_clients('tbl_clients');
      // print_r($data_arr);
    if ($data_arr) {
      $arr = array(
        'status' => 'success',
        'data'=> $data_arr
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

    //get all pms
    public function GetPMS()
    { 
      $data_arr = $this->Admin_model->get_clients('tbl_pms');
        // print_r($data_arr);
      if ($data_arr) {
        $arr = array(
          'status' => 'success',
          'data'=> $data_arr
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

  //delete client data by id
  public function deletePMS()
	{  
    $data = json_decode(file_get_contents("php://input"));
    $id = $data->id;
    $is_del = $this->Admin_model->outputfileDeletedById('tbl_pms', $id);

    echo json_encode($is_del);
  }
  
   // update output file data by id
  public function updatePMS($id)
	{ 
    
    $data = json_decode(file_get_contents("php://input"));
  
    if (isset($data->name)) {
       
       $data = array(
        "name" => $data->name
        
       );
       $res=$this->Admin_model->outputfileEdit('tbl_pms', $id, $data);  
        
       if ($res) {
 
        $arr = array(
          'status' => "success",
          'message' => 'Record updated successfully',
        );
        echo json_encode($arr);
       } else {

         $arr = array(
          'status' => "error",
          'message' => 'Failed to update ',
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


  public function AssignManagerAgent(){
    $_POST = json_decode(file_get_contents('php://input'), true);
    $data = $this->input->post();
    $arrs = array();
    foreach ($data['agents'] as $agent) {
      array_push($arrs,array('manager' => $data['manager'], 'agent' => $agent));
    }
    $this->Admin_model->insert_data('manager_agent',$arrs);

    $arr = array(
      'status' => "success",
      'message' => 'Assigned Successfully',
     );
     echo json_encode($arr);
  }

  public function AssignClaimsAgent(){
    $_POST = json_decode(file_get_contents('php://input'), true);
    $data = $this->input->post();
    $arrs = array();
    foreach ($data['claims'] as $claim) {
      array_push($arrs,array('agent' => $data['agent'], 'claim_id' => $claim));
    }
    $this->Admin_model->insert_data('agent_claim',$arrs);

    $arr = array(
      'status' => "success",
      'message' => 'Assigned Successfully',
     );
     echo json_encode($arr);
  }

  public function GetAssignClaimsAgent($page, $row_limit){
    $user = $this->authUserToken([3]);
    if($user){
      $data = $this->Admin_model->get_where('agent_claim','agent', $user['id']);
  
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

  public function GetManagerAgentMapping(){
    $user = $this->authUserToken([2]);
    if($user){
      $data = $this->Admin_model->get_where2('manager_agent','manager', $user['id']);
  
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
    $this->db->group_by('manager_agent.manager'); // Set Filter
    // $this->db->where('tbl_users.id',8); // Set Filter
    // $this->db->where_in('tbl_users.id');
    $res = $this->db->get();
    print_r($res->result());

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


 
 

}


?>