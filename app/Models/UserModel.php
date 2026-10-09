<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'role_id', 'name', 'email', 'password_hash', 'avatar', 'bio',
        'email_verified_at', 'verification_token', 'reset_token',
        'reset_token_expires', 'remember_token', 'is_banned',
        'last_login_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function findByEmail(string $email)
    {
        return $this->where('email', $email)->first();
    }

    public function findByVerificationToken(string $token)
    {
        return $this->where('verification_token', $token)->where('email_verified_at IS NULL', null, false)->first();
    }

    public function findByResetToken(string $token)
    {
        return $this->where('reset_token', $token)->where('reset_token_expires >', date('Y-m-d H:i:s'))->first();
    }

    public function findByRememberToken(string $hash)
    {
        return $this->where('remember_token', $hash)->where('is_banned', 0)->first();
    }

    public function withRole(int $id)
    {
        return $this->select('users.*, r.slug AS role_slug, r.name AS role_name')
            ->join('roles r', 'r.id = users.role_id')
            ->where('users.id', $id)
            ->where('users.is_banned', 0)
            ->first();
    }

    public function withRoleByEmail(string $email)
    {
        return $this->select('users.*, r.slug AS role_slug, r.name AS role_name')
            ->join('roles r', 'r.id = users.role_id')
            ->where('users.email', $email)
            ->first();
    }
}
