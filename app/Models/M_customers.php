<?php

namespace App\Models;

use CodeIgniter\Model;

class M_customers extends Model
{
    protected $table = 'pos_customers';
    protected $allowedFields = [
        'name',
        'email',
        'phone',
        'vat_number', // ZATCA B2B customer VAT registration number
        'address',
        'zatca_street_name',
        'zatca_building_number',
        'zatca_city_subdivision_name',
        'zatca_city_name',
        'zatca_postal_code',
        'zatca_country_code',
        'zatca_registration_name',
        'zatca_cr_number',
        'created_at',
        'store_id',
        'updated_at',
        'points',
        'area',
        'opening_balance',
        'credit_limit',
    ]; // adjust fields as per your table

    public function forStore($storeId = null)
    {
        if ($storeId === null) {
            $storeId = session('store_id');
        }
        $this->where('store_id', $storeId);
        return $this;
    }
}
