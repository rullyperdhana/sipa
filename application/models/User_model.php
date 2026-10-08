<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
    protected $table = 'users';

    public function getAll($filter = [])
    {
        $this->db->select('u.*, s.nama_skpd, s.kode_skpd');
        $this->db->from('users u');
        $this->db->join('skpd s', 's.id = u.skpd_id', 'left');

        if (!empty($filter['role']))    $this->db->where('u.role', $filter['role']);
        if (!empty($filter['skpd_id'])) $this->db->where('u.skpd_id', $filter['skpd_id']);
        if (!empty($filter['q'])) {
            $q = $filter['q'];
            $this->db->group_start()
                ->like('u.username', $q)
                ->or_like('u.nama_lengkap', $q)
                ->or_like('u.nip', $q)
                ->group_end();
        }

        return $this->db->order_by('u.created_at', 'DESC')->get()->result();
    }

    public function find($id)
    {
        $this->db->select('u.*, s.nama_skpd, s.kode_skpd, s.kepala_skpd, s.nip_kepala, s.jabatan_kepala, s.nama_pengurus, s.nip_pengurus');
        $this->db->from('users u');
        $this->db->join('skpd s', 's.id = u.skpd_id', 'left');
        $this->db->where('u.id', (int) $id);
        return $this->db->get()->row();
    }

    public function findByUsername($username)
    {
        return $this->db->get_where($this->table, ['username' => $username])->row();
    }

    public function create($data)
    {
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 10]);
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        return (int) $this->db->insert_id();
    }

    public function update($id, $data)
    {
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 10]);
        } else {
            unset($data['password']);
        }
        return $this->db->where('id', (int) $id)->update($this->table, $data);
    }

    public function delete($id)
    {
        return $this->db->where('id', (int) $id)->delete($this->table);
    }

    public function changePassword($userId, $newPassword)
    {
        return $this->db->where('id', (int) $userId)
            ->update($this->table, ['password' => password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 10])]);
    }

    public function verifyPassword($userId, $password)
    {
        $user = $this->find($userId);
        return $user && password_verify($password, $user->password);
    }
}
