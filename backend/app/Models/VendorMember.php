<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot; // <-- Model 대신 Pivot 상속

class VendorMember extends Pivot // <-- Model 대신 Pivot 상속
{
    use HasFactory;

    protected $table = 'vendor_members';

    public $incrementing = false; // 이 테이블은 자동 증가하는 'id' 컬럼을 사용하지 않음

    // protected $primaryKey = null; // 위와 동일

    protected $fillable = [
        'user_id',
        'vendor_id',
        'position',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
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