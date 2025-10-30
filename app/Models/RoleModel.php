<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table = 'pos_roles';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'description'];
}
