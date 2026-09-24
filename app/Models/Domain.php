<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\School;

class Domain extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain',
        'tenant_id',
    ];

    public function tenant()
    {
        return $this->belongsTo(School::class, 'tenant_id', 'tenant_id');
    }

    // ADD AN ACCESSOR FOR THE 'DOMAIN' ATTRIBUTE
    public function getDomainAttribute($value)
    {
        // MANIPULATE THE DOMAIN VALUE AS NEEDED
        return str_replace(config("tenancy.sub_domain"), "", $value);

    }
}
