<?php

namespace App\Models;

use CodeIgniter\Model;

class LoginAttemptModel extends Model
{
    protected $table            = 'login_attempts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['email', 'ip_address'];

    protected $useTimestamps = true;
    protected $createdField  = 'attempted_at';
    protected $updatedField  = 'attempted_at';

    public function countRecent(string $email, string $ip, int $minutes)
    {
        return $this->where('email', $email)
            ->where('ip_address', $ip)
            ->where('attempted_at >', date('Y-m-d H:i:s', time() - $minutes * 60))
            ->countAllResults();
    }

    public function clearFor(string $email, string $ip)
    {
        return $this->where('email', $email)->where('ip_address', $ip)->delete();
    }
}
