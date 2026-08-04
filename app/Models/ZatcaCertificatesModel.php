<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * ZATCA Certificates Model
 * 
 * Manages ZATCA certificates (CSR, private keys, CSIDs) per store/environment
 */
class ZatcaCertificatesModel extends Model
{
    protected $table = 'pos_zatca_certificates';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'store_id',
        'environment',
        'csr',
        'private_key',
        'compliance_request_id',
        'binary_security_token',
        'production_binary_security_token',
        'secret',
        'production_secret',
        'status',
        'created_at',
        'updated_at',
    ];

    // Dates
    protected $useTimestamps = false; // Manual timestamp management for better control
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'store_id' => 'required|integer',
        'environment' => 'required|in_list[sandbox,simulation,production]',
        'status' => 'required|in_list[draft,compliance,production]',
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Get active production certificate for store
     * 
     * @param int $storeId
     * @param string $environment
     * @return array|null
     */
    public function getActiveCertificate(int $storeId, string $environment = 'production')
    {
        return $this->where([
            'store_id' => $storeId,
            'environment' => $environment,
            'status' => 'production',
        ])->first();
    }

    /**
     * Get compliance certificate for store (for onboarding steps)
     * 
     * @param int $storeId
     * @param string $environment
     * @return array|null
     */
    public function getComplianceCertificate(int $storeId, string $environment)
    {
        return $this->where([
            'store_id' => $storeId,
            'environment' => $environment,
        ])->whereIn('status', ['compliance', 'production'])->first();
    }

    /**
     * Check if store has completed onboarding
     * 
     * @param int $storeId
     * @param string $environment
     * @return bool
     */
    public function isOnboardingComplete(int $storeId, string $environment): bool
    {
        $cert = $this->where([
            'store_id' => $storeId,
            'environment' => $environment,
            'status' => 'production',
        ])->first();

        return !empty($cert) && !empty($cert['production_binary_security_token']);
    }
}
