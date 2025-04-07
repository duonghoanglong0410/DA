<?php

namespace App\Models;

use App\Models\BaseModel;

class RoleModel extends BaseModel
{
    protected $table = 'roles';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'description', 'created_at', 'updated_at'];
}
