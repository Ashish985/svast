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

   public function get_clients($table) 
    { 
      $this->db->select("name");    
      // $this->db->order_by("created_date", "desc");
      $query = $this->db->get($table);
      // print_r($query->result());
      // die();
      return $query->result();
    }

   public function getCount($table){
	return $this->db->count_all_results('tbl_output');
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

   //delete exel outputfile data by id 
   public function outputfileDelete($table,$id) 
   {
      $this->db->delete($table, ['id' => $id]);
      return $this->db->affected_rows();
   }

   //edit exel outputfile data by id 
   public function outputfileEdit($table,$id,$data) 
   {
   //   print_r($data);
     $this->db->where("id", $id);
     return $this->db->update($table, $data); 
   }


}

 ?>