<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplianceRoleRule extends Model
{
    protected $fillable = [
        'role',
        'compliance_area',
    ];
}
