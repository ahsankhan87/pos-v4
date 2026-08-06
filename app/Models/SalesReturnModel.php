<?php

namespace App\Models;

use CodeIgniter\Model;

class SalesReturnModel extends Model
{
    protected $table = 'pos_sales_returns';
    protected $allowedFields = [
        'sale_id',
        'product_id',
        'quantity',
        'return_amount',
        'reason',
        'user_id',
        'created_at',
        'updated_at',
        'store_id',
        'zatca_credit_note_uuid',
        'zatca_credit_note_hash',
        'zatca_credit_note_xml_path',
        'zatca_credit_note_status',
        'zatca_credit_note_response',
        'zatca_credit_note_submitted_at',
    ];
}
