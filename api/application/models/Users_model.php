<?php

class Users_model extends CI_Model
{
    public function __construct(){
        parent::__construct();
        $this->load->database();
        $this->load->helper('string');
    }
   
    public function CheckCredential($email) 
    {
        $this->db->select('*');
        $this->db->from('tbl_users');
        $this->db->where('email', $email);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            return $query->result();
        } else {
            return false;
        }
    }   

    public function getResetTokenData($table='',$where='')
    {
       if(!empty($where)){
        $this->db->where($where);
        $res = $this->db->get($table);  
        return ($res->num_rows())? $res->result()[0] : false;
       }else{
        return false;
       }
    }

    public function insert($table,$data){

        return $this->db->insert($table, $data);
    }
   
    function is_record_exists($table, $id)
   {
      $this->db->where('id',$id);
      $query = $this->db->get($table);
      if ($query->num_rows() > 0){
         return true;
      }
      else{ 
         return false;
      }
   }

    public function getCount($table){
        return $this->db->count_all_results($table);
       }
    
    public function get_users($table ,$skip, $limit) 
    { 
      $this->db->select("id,name,email,mobile,username,role,created_at");    
      $this->db->order_by("created_at", "desc");
      $query = $this->db->get($table, $limit, $skip);
    // print_r($query->result_array());
      return $query->result_array(); 
    }

    public function getUserProfile($table,$user_email)
    {
        $this->db->select('*');
        $this->db->from($table);
        $this->db->where('email', $user_email);
        $query = $this->db->get();

        return $query->result_array()[0];
    }

    public function delete_old($table,$id){
   
        $this->db->delete($table, ['id' => $id]);
        return $this->db->affected_rows();
    }

   

   //delete users data by id 
   public function delete($table,$id) 
   {
      
      if($this->is_record_exists($table, $id) == true){
         $this->db->where('id', $id);
         $this->db->delete($table);
         return array(
            'status' => 'success',
            'message' => 'record has been deleted',
        );
      }
      else{
         return array(
            'status' => 'error',
            'message' => 'id not exist!',
        );
      }
   }

    public function update($table,$user_id, $user_info){
     if($this->is_record_exists($table, $user_id,$user_info) == true){
       $this->db->where('id', $user_id);
       return $this->db->update($table, $user_info); 
      }
      else{
      return false;
      }
    } 

    public function get_userById($table,$id){
        
      $this->db->select("*");
      $this->db->from($table);
      $this->db->where("id", $id);
      $query = $this->db->get();
  
      return $query->result();
    }

  
    
}

?>