<?php

namespace App\Models;

use CodeIgniter\Model;

class ContactMessageModel extends Model
{
    protected $table            = 'contact_messages';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id', 'subject', 'category', 'message', 'status', 'admin_reply',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function withUser()
    {
        return $this->select('m.*, u.name AS user_name, u.email AS user_email')
            ->from('contact_messages m')
            ->join('users u', 'u.id = m.user_id')
            ->orderBy('m.created_at', 'DESC')
            ->findAll();
    }
}
