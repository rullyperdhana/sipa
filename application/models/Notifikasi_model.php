<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notifikasi_model extends CI_Model
{
    public function getForUser($userId, $unreadOnly = FALSE, $limit = 10)
    {
        $this->db->where('user_id', (int) $userId);
        if ($unreadOnly) $this->db->where('is_read', 0);
        return $this->db->order_by('created_at', 'DESC')->limit($limit)->get('notifikasi')->result();
    }

    public function countUnread($userId)
    {
        return (int) $this->db->where(['user_id' => (int) $userId, 'is_read' => 0])
            ->count_all_results('notifikasi');
    }

    public function markAsRead($id, $userId)
    {
        return $this->db->where(['id' => (int) $id, 'user_id' => (int) $userId])
            ->update('notifikasi', ['is_read' => 1]);
    }

    public function markAllAsRead($userId)
    {
        return $this->db->where('user_id', (int) $userId)
            ->update('notifikasi', ['is_read' => 1]);
    }
}
