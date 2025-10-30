<?php

namespace App\Models;

use CodeIgniter\Model;

class CartModel extends Model
{
    protected $table = 'carts';
    protected $primaryKey = 'id';
    protected $allowedFields = ['session_id', 'user_id', 'items', 'created_at', 'updated_at'];
    protected $useTimestamps = true;

    public function getCart($sessionId, $userId = null)
    {
        $builder = $this->where('session_id', $sessionId);

        if ($userId) {
            $builder->orWhere('user_id', $userId);
        }

        return $builder->orderBy('updated_at', 'DESC')->first();
    }

    public function saveCart($data)
    {
        $existing = $this->getCart($data['session_id'], $data['user_id'] ?? null);

        if ($existing) {
            return $this->update($existing['id'], $data);
        }

        return $this->insert($data);
    }
}
