<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityLogModel extends Model
{
    protected $table            = 'activity_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'action', 'entity_type', 'entity_id', 'ip_address', 'user_agent'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'created_at';

    public function last7DaysChart()
    {
        return $this->select("DATE(created_at) AS day, COUNT(*) AS count")
            ->where('created_at >=', date('Y-m-d', strtotime('-7 days')))
            ->groupBy('DATE(created_at)')
            ->orderBy('day', 'ASC')
            ->findAll();
    }
}
