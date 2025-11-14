<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table = 'payments';
    protected $primaryKey = 'id';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'subscription_id',
        'amount',
        'currency',
        'provider',
        'provider_payment_id',
        'status',
        'meta',
        'paid_at'
    ];
}
