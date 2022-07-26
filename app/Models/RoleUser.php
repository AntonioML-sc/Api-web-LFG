<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\Pivot;

use App\Traits\Uuids;

class RoleUser extends Pivot
{
    use Uuids;
    use HasFactory;
    
    protected $primaryKey = 'id';
}
