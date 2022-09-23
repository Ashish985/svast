<?php

class Admin_model extends CI_Model{


  public function insertFileData($table='',$data='')
   {
		if(!empty($data) && !empty($table)){
          $this->db->insert($table,$data);
          return $this->db->insert_id();
		}else{
			return false;
		}
   }
   
   public function get_mngrId($table) 
   { 
     $this->db->select("*");    
     $this->db->group_by('manager');
     $query = $this->db->get($table);
     return $query->result();
   }

   public function get_agentId($table) 
   { 
     $this->db->select("*");    
     $this->db->group_by('agent'); 
     $query = $this->db->get($table);
     return $query->result();
   }

   public function get_clients($table) 
    { 
      $this->db->select("id,image,name,created_date");    
      // $this->db->order_by("created_date", "desc");
      $query = $this->db->get($table);
      // print_r($query->result());
      // die();
      return $query->result();
    } 

    public function get_clients_temp($table) 
    { 
      $this->db->select("tbl_clients.id,tbl_clients.image,tbl_clients.name,tbl_clients.created_date, tbl_pms.name as pms_name");    
      // $this->db->order_by("created_date", "desc");
      $this->db->from('tbl_clients');
      $this->db->join('tbl_pms', 'tbl_pms.id = tbl_clients.pms_id');
      $query = $this->db->get();
      // print_r($query->result());
      // die();
      return $query->result();
    }

    public function get_clients_pms_temp($table) 
    { 
      $this->db->select("*");    
      // $this->db->order_by("created_date", "desc");
      $query = $this->db->get($table);
      // print_r($query->result());
      // die();
      return $query->result();
    }

   public function getCount($table){
	return $this->db->count_all_results('tbl_output');
   }

   public function getCountPms($table,$id){
      $where = "pms_id='Joe' AND status='boss' OR status='active'";
      $this->db->where('pms_id',$id);
      $query = $this->db->get($table);
      return $query->num_rows();
   }

   public function check_client($table,$name){
     
     $this->db->select('*');
     $this->db->from($table);
     $this->db->where('name', $name);
     $query = $this->db->get();
     if ($query->num_rows() > 0) {
         return $query->result();
     } else {
         return false;
     }
      
   }

   public function create($table,$data){

      $this->db->insert($table, $data);
      return $this->db->insert_id();
   }

   public function getOutputData($skip, $limit) 
   {
      $this->db->order_by("inserted_date", "desc");
      $query = $this->db->get('tbl_output', $limit, $skip);
	// print_r($query->result_array());
	return $query->result_array();
   }
  
   //exel outputfile data get by id 
   public function outputfileGet($table,$id) 
   {
      $this->db->select("*");
      $this->db->from($table);
      $this->db->where("id", $id); 
      $query = $this->db->get();
  
      return $query->result();
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


   //delete exel outputfile data by id 
   public function outputfileDeletedById($table,$id) 
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


   public function deletedByIdMul($table,$ids) 
   {
      for($i=0; $i<count($ids); $i++){
         if($this->is_record_exists($table, $ids[$i]) == true){
            $this->db->where('id', $ids[$i]);
            $this->db->delete($table);
         }
      }
      return array(
         'status' => 'success',
         'message' => 'record has been deleted',
     );
   }

   public function updateByIdMul($is_data) 
   {
          // Check so incoming data is actually an array and not empty
    if (is_array($is_data) && ! empty($is_data))
    {
      $this->db->update_batch('tbl_output', $is_data, 'id');
      return array(
         'status' => 'success',
         'message' => 'record has been updated',
      );
    }
    return false;
   }


   //edit exel outputfile data by id 
   public function outputfileEdit($table,$id,$data) 
   {
   //   print_r($data);
     if($this->is_record_exists($table, $id,$data) == true){
      $this->db->where('id', $id);
      return $this->db->update($table, $data); 
     }
     else{
      return false;
     }
      
   }
 
   public function get_data($table, $role) 
   { 
     $this->db->select("id,name,email,mobile,username,role,created_at");
     $this->db->from($table);
     $this->db->where('role', $role);
     $query = $this->db->get();
     return $query->result();
   }

   public function insert_data($table, $data){
     // echo json_encode($data);
     for ($i=0; $i < count($data); $i++) { 
        $this->db->insert($table,$data[$i]);
     }
        // $this->db->insert_batch($table,$data);
         return true;
   }


   public function get_count($table){
      $this->db->select('COUNT(*) as count');
      $this->db->from($table);
      $query = $this->db->get();
      return $query->result();
   }

   public function get_where($table, $field, $data){
      $this->db->select('*');
      $this->db->from('agent_claim');
      // $this->db->from($table);
      $this->db->join('tbl_output', 'tbl_output.id = agent_claim.claim_id');
      $this->db->where($field, $data);
      $query = $this->db->get();
      return $query->result();
    }

    public function get_where2($table, $field, $data){
      $this->db->select('*');
      $this->db->from('manager_agent');
      // $this->db->from($table);
      $this->db->join('tbl_users', 'tbl_users.id = manager_agent.agent');
      $this->db->where($field, $data);
      $query = $this->db->get();
      return $query->result_array();
    }

    public function get_where_temp($table, $field, $data){
      $this->db->select('*');
      $this->db->from($table);
      $this->db->where($field, $data);
      $query = $this->db->get();
      return $query->result();
    }
    public function get_where_temp_agent($table, $field, $data){

      $this->db->select('*');
      $this->db->from($table);
      $this->db->join('tbl_users', 'tbl_users.id = manager_agent.agent');
      $this->db->where($field, $data->id);
      $query = $this->db->get();
      return $query->result();
    }
    public function get_where_temp_client($table, $field, $data){
      $this->db->select('*');
      $this->db->from($table);
      // if($data->client)
      $this->db->join('tbl_clients', 'tbl_clients.id = manager_agent.client');
      $this->db->where($field, $data->id);
      $query = $this->db->get();
      return $query->result();
    }
 

   public function get_all_data($table) 
   { 
     $this->db->select("*");
     $this->db->from($table);
     $query = $this->db->get();
     return $query->result();
   }

   public function delete_where($table,$field, $data) 
   {
      
       
     $this->db->where('manager', $data['manager_id']);
     $this->db->where($field, $data[$field]);
     $this->db->delete($table);
     
     return array(
        'status' => 'success',
        'message' => 'record has been deleted',
     );
     
   }


   public function delete_where_id($table,$field, $id) 
   {
      
         $this->db->where($field, $id);
         $this->db->delete($table);
   }
   
   public function get_whereJ_SF($table, $field, $data, $fields){
      $where = "pms_id='$data' AND map_col_id is NOT NULL";
      $this->db->select($fields);
      $this->db->from($table);
      $this->db->where($where);
      $query = $this->db->get();
      return $query->result();
    }

    public function get_where_SF($table, $field, $data, $fields){
      $this->db->select($fields);
      $this->db->from($table);
      $this->db->where($field, $data);
      $query = $this->db->get();
      return $query->result();
    }

   


}

 ?>