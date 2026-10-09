<?php

namespace App\Models;

use CodeIgniter\Model;

class NewsletterSubscriberModel extends Model
{
    protected $table            = 'newsletter_subscribers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['email'];

    protected $useTimestamps = true;
    protected $createdField  = 'subscribed_at';
    protected $updatedField  = 'subscribed_at';

    public function subscribe(string $email): bool
    {
        return $this->ignore(true)->insert(['email' => $email]) !== false;
    }
}
