<?php
class Users extends CI_Controller
{

    public function __construct()
    {

        parent::__construct();
        Header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
        Header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
        Header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
        Header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
        //load database
        $this->load->database();
        $this->load->library('email');
        $this->load->model(array("Users_model"));
        $this->load->library(array("form_validation"));

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

    public function login()
    {
        $_POST = json_decode(file_get_contents('php://input'), true);
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        $result = $this->Users_model->CheckCredential($email);

        if ($result) {
            $detail = $result[0];

            if (password_verify($password, $detail->password)) {
                $role = $detail->role;
                $jwt = new JWT();
                $JwtSecretKey = "myloginSecret";
                date_default_timezone_set('Asia/Kolkata');
                $date = date('Y-m-d H:i:s', time());

                $result_t = array();
                $result_t['sub'] = $result[0]->email;
                $result_t['exp'] = time() + 172800; //172800;

                $token = $jwt->encode($result_t, $JwtSecretKey, 'HS256');

                if ($role == 1) {
                    $data = array(
                        'id' => $detail->id,
                        'username' => $detail->username,
                        'email' => $detail->email,
                        'appRoleId' => 1,
                    );
                    $res = array(
                        'status' => 'success',
                        'token' => $token,
                        'user' => $data,
                    );
                    echo json_encode($res);
                } elseif ($role == 2) {
                    $data = array(
                        'id' => $detail->id,
                        'username' => $detail->username,
                        'email' => $detail->email,
                        'appRoleId' => 2,
                    );
                    $res = array(
                        'status' => 'success',
                        'token' => $token,
                        'user' => $data,
                    );
                    echo json_encode($res);
                } elseif ($role == 3) {
                    $data = array(
                        'id' => $detail->id,
                        'username' => $detail->username,
                        'email' => $detail->email,
                        'appRoleId' => 3,
                    );
                    $res = array(
                        'status' => 'success',
                        'token' => $token,
                        'user' => $data,
                    );
                    echo json_encode($res);
                } elseif ($role == 4) {
                    $data = array(
                        'id' => $detail->id,
                        'username' => $detail->username,
                        'email' => $detail->email,
                        'appRoleId' => 4,
                    );
                    $res = array(
                        'status' => 'success',
                        'token' => $token,
                        'user' => $data,
                    );
                    echo json_encode($res);
                }

            } else {
                $res = array(
                    'status' => 'error',
                    'message' => 'invalid Credentials!',
                );
                echo json_encode($res);
            }
        } else {
            $res = array(
                'status' => 'error',
                'message' => 'invalid Credentials!',
            );
            echo json_encode($res);
        }

    }

    public function signup()
    {
        // insert data method
        $_POST = json_decode(file_get_contents('php://input'), true);
        //print_r($this->input->post());die;

        // collecting form data inputs
        $name = $this->security->xss_clean($this->input->post("name"));
        $email = $this->security->xss_clean($this->input->post("email"));
        $mobile = $this->security->xss_clean($this->input->post("mobile"));
        $username = $this->security->xss_clean($this->input->post("username"));
        $role = $this->security->xss_clean($this->input->post("role"));
        $status = $this->security->xss_clean($this->input->post("status"));
        $password = $this->security->xss_clean($this->input->post("password"));
        $cpassword = $this->security->xss_clean($this->input->post("cpassword"));

        // form validation for inputs
        $this->form_validation->set_rules("name", "Name", "required");
        $this->form_validation->set_rules("email", "Email", "required|valid_email");
        $this->form_validation->set_rules("mobile", "Mobile", "required");
        $this->form_validation->set_rules("username", "Username", "required");
        $this->form_validation->set_rules("role", "Role", "required");
        $this->form_validation->set_rules("status", "Status", "required");
        $this->form_validation->set_rules("password", "password", "required");
        $this->form_validation->set_rules("cpassword", "cpassword", "required|matches[cpassword]");

        // checking form submittion have any error or not
        if ($this->form_validation->run() === false) {

            // we have some errors
            $arr = array(
                'status' => 0,
                'message' => 'All fields are needed',
            );
            echo json_encode($arr);
        } else {

            if (!empty($name) && !empty($email) && !empty($mobile) && !empty($username) && !empty($role) && !empty($status) && !empty($password)) {
                // all values are available
                $password = password_hash($password, PASSWORD_BCRYPT);
                date_default_timezone_set('Asia/Kolkata');
                $created_date = date('Y-m-d H:i:s', time());
             
                $user = array(
                    "name" => $name,
                    "email" => $email,
                    "mobile" => $mobile,
                    "username" => $username,
                    "role" => $role,
                    "status" => $status,
                    "password" => $password,
                    "created_at" => $created_date,
                );

                // echo json_encode($user);

                if ($this->Users_model->insert('tbl_users', $user)) {

                    $arr = array(
                        'status' => "success",
                        'message' => 'User has been created',
                    );
                    echo json_encode($arr);
                } else {

                    $arr = array(
                        'status' => "error",
                        'message' => 'Failed to create User',
                    );
                    echo json_encode($arr);
                }
            } else {
                // we have some empty field
                $arr = array(
                    'status' => "error",
                    'message' => 'All fields are needed',
                );
                echo json_encode($arr);
            }
        }

    }

    // GET: All users from table
    
    public function getAllUsers($page,$row_limit)
	{ 
    $first_page= false;
    $last_page = false;
    $total_records = $this->Users_model->getCount('tbl_users');
    // $row_limit = 2;
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
      $data_arr = $this->Users_model->get_users('tbl_users', $skip, $row_limit);
       
    //   print_r($data_arr);
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

    public function getProfile()
    {

        $data = $this->authUserToken([1, 2, 3, 4]);
        if ($data) {
            // take role from comming data
            // perform operation
            unset($data["password"]);
            $res = array(
                'status' => 'success',
                'user' => $data,
            );
            echo json_encode($res);
        } else {
            // return status error and message invalid token
            $res = array(
                'status' => 'error',
                'message' => "Invalid Token",
            );
            echo json_encode($res);
        }

    }

    // GET: <project_url>/index.php/User
  public function getUserById($id){
    // list data method
    //echo "This is GET Method";
    // SELECT * from tbl_Users;
    $users = $this->Users_model->get_userById('tbl_users',$id);

    //print_r($query->result());    

    if(count($users) > 0){

      $arr = array(
        "status" =>"success",
        "message" => "Users found",
        "data" => $users
      );
      echo json_encode($arr);
    }else{

      $arr = array(
        "status" => "error",
        "message" => "No Users found",
        "data" => $users
      );
      echo json_encode($arr);
    }



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

    // PUT: <project_url>/index.php/student
    public function update($id)
    {
        // updating data method
        //echo "This is PUT Method";
        $data = json_decode(file_get_contents("php://input"));

        if (isset($id) && isset($data->name) && isset($data->email) && isset($data->mobile) && isset($data->username)) {
            date_default_timezone_set('Asia/Kolkata');
            $updated_date = date('Y-m-d H:i:s', time());

            $user_id = $id;
            $user_info = array(
                "name" => $data->name,
                "email" => $data->email,
                "mobile" => $data->mobile,
                "username" => $data->username,
                "updated_date" => $updated_date
            );
            // echo json_encode($user_info);
            if ($this->Users_model->update('tbl_users', $user_id, $user_info)) {

                $arr = array(
                    'status' => "success",
                    'message' => 'User data updated successfully',
                );
                echo json_encode($arr);
            } else {

                $arr = array(
                    'status' => "error",
                    'message' => 'Failed to update User data',
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

    public function delete()
    {
        $data = json_decode(file_get_contents("php://input"));
        $id = $data->id;
        
        $is_del = $this->Users_model->delete('tbl_users', $id);
    
        echo json_encode($is_del); 
    }
    
    public function changePassword()
    {
     
      $_POST = json_decode(file_get_contents('php://input'), true);
      // $token   = $_POST['token'];
      $oldpass = $_POST['oldpassword'];
      $newpass = $_POST['newpassword'];

      $data = $this->authUserToken([1, 2, 3, 4]);

      $password = password_hash($newpass, PASSWORD_BCRYPT);
      // print_r($data);
      if ($data) {
        // logic
        if(password_verify($oldpass, $data['password'])){
          // echo password_verify($oldpass, $data['password']);
          // update user password
          if ($this->Users_model->update('tbl_users',$data['id'], array('password' => $password))) 
             {
              
              $arr = array(
                  'status' => 'success',
                  'message' => 'password updated successfully',
              );
              echo json_encode($arr);
          } else {

              $arr = array(
                  'status' => 'error',
                  'message' => 'Failed to update password',
              );
              echo json_encode($arr);
          }

        }
        else{
          $res = array(
            'status' => 'error',
            'message' => "Old password is incorrect.",
          );
          echo json_encode($res);
        }


    } else {
        // return status error and message invalid token
        $res = array(
            'status' => 'error',
            'message' => "Invalid Token",
        );
        echo json_encode($res);
    }

   
  }
  

  function generateRandomString($length = 30) {
    $characters = '0123456789abcdefghijklmnopq@$rstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

  public function forgotPassword()
  {
    $_POST = json_decode(file_get_contents('php://input'), true);
    $data = $this->input->post();
    $email = $data['email'];
    $check_email = $this->Users_model->CheckCredential($email);
    if($check_email)
    { 
        $detail = $check_email[0];
        $id = $detail->id;
        $name = $detail->name;
        // $hash = $detail->hash_code; 
        $hash = $this->generateRandomString();
      // send Email.......................................
        $this->load->library('email');
        $this->email->from('info@mistpl.com',"Reset Your Password");
        $this->email->to($email);
        $this->email->subject('Reset Your Password');
        $this->email->set_header('Content-Type', 'text/html');
        $message = '
         <body style="background: #84dce01a">
         <div id="div">
         <b>Hi, '.$name.'</b>
         <p>Forgot your password? click on below link to reset your new password.
         <br><br>
         <a href="http://localhost:4200/auth/reset-password/'.$hash.'">Reset Password</a>
         <a href="https://valetpoint.co.in/svast/frontend/auth/reset-password/'.$hash.'">Reset Password</a>
         </p>
         </div>
         ';
        $this->email->message($message); 
        if($this->email->send()){
            date_default_timezone_set('Asia/Kolkata');
            $created_date = date('Y-m-d H:i:s', time());
            $user = array(
                "user_id" => $id,
                "token" => $hash,
                "is_reset"=>false,
                "date" => $created_date
            );

            // echo json_encode($user);
            $this->Users_model->insert('tbl_reset_password_token', $user);

          $result = array(
           'status' => 'success',
           'message' => 'Please check your email, we have sent a link at '.$email.' to reset your password.'
          );  
          echo json_encode($result);         
        }
        else
        {
          $result = array(
           'status' => 'error',
           'message' => 'Something went wrong!....'
          );  
          echo json_encode($result); 
        }
    }
    else
    {
      $result = array(
           'status' => "error",
           'message' => 'No such user exists with this email.'
          );  
      echo json_encode($result);       
    } 
  }

  public function resetPassword()
    {
        $_POST = json_decode(file_get_contents('php://input'), true);
        $data = $this->input->post();
        $new_password = $data['password'];
        $hash = $data['token'];
        $where = array('token'=>$hash);
        $res = $this->Users_model->getResetTokenData('tbl_reset_password_token', $where);
        $res_id =$res->id;
        if($res){
            if(!$res->is_reset){

                $user_id = $res->user_id;
                $token_date = $res->date;
                date_default_timezone_set('Asia/Kolkata');
                $current_date = date('Y-m-d h:i A', time());
        
                $seconds = strtotime($current_date) - strtotime($token_date);
                $hours = $seconds / 60 / 60;
            
                if ($hours<=24) {
                    $password = password_hash($new_password, PASSWORD_BCRYPT);  
                    $res=$this->Users_model->update('tbl_users',$user_id, array('password' => $password)); 
                    if($res)
                    {
                    $this->Users_model->update('tbl_reset_password_token',$res_id, array('is_reset' => true));
                    
                    $arr = array(
                        'status' => 'success',
                        'message' => 'Password updated successfully',
                    );
                    echo json_encode($arr);
                    } else {
        
                    $arr = array(
                        'status' => 'error',
                        'message' => 'Failed to reset password',
                    );
                    echo json_encode($arr);
                }
        
                }
                else{
                    $arr = array(
                        'status' => 'error',
                        'message' => 'Link exprire!',
                    );
                    echo json_encode($arr);
                }
            }
            else{
                $arr = array(
                    'status' => 'error',
                    'message' => 'Link exprire!',
                );
                echo json_encode($arr);
            }
        }
        else{
            $arr = array(
                'status' => 'error',
                'message' => 'Invalid Token!',
            );
            echo json_encode($arr);
        }
 
    }


    public function DashboardDataManager(){

        $data = array(
        );
    
        $arr = array(
          'status' => "success",
          'message' => 'OK',
          'data' => $data,
         );
         echo json_encode($arr);
      }
    
      public function DashboardDataAgent(){
    
        $data = array(
        );
    
        $arr = array(
          'status' => "success",
          'message' => 'OK',
          'data' => $data,
         );
         echo json_encode($arr);
      }
    
      public function DashboardDataClient(){
    
        $data = array(
        );
    
        $arr = array(
          'status' => "success",
          'message' => 'OK',
          'data' => $data,
         );
         echo json_encode($arr);
      }


}

