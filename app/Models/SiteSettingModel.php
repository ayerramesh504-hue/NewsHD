<?php

namespace App\Models;

use CodeIgniter\Model;

class SiteSettingModel extends Model
{
    protected $table            = 'site_settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['setting_key', 'setting_value'];

    protected $useTimestamps = false;

    protected $all = null;

    public function allAsMap(): array
    {
        if ($this->all === null) {
            $this->all = [];
            foreach ($this->findAll() as $row) {
                $this->all[$row['setting_key']] = $row['setting_value'];
            }
        }
        return $this->all;
    }

    public function get(string $key, ?string $default = null): ?string
    {
        return $this->allAsMap()[$key] ?? $default;
    }
}
