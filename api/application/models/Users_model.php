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

    public function delete($table,$id){

        // delete method
        $this->db->delete($table, ['id' => $id]);
        return $this->db->affected_rows();
    }

    public function update($table,$user_id, $user_info){

        $this->db->where("id", $user_id);
        return $this->db->update($table, $user_info);
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