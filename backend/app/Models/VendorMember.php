<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class VendorMember extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'vendor_members';

    // protected $primaryKey = null; // 위와 동일

    protected $fillable = [
        'user_id',
        'vendor_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}