<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Log_model extends CI_Model
{
    public function getRecent($limit = 50, $userId = NULL)
    {
        $this->db->order_by('created_at', 'DESC')->limit($limit);
        if ($userId) $this->db->where('user_id', (int) $userId);
        return $this->db->get('log_aktivitas')->result();
    }
}
