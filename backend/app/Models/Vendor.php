<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // HasUuids 트레이트 추가

class Vendor extends Model
{
    use HasFactory, HasUuids; // HasUuids 트레이트 사용

    protected $fillable = [
        'name',
        'business_registration_number',
        'address',
        'description',
        'specialties',
        'master_user_id',
        'status',
        'ban_reason',
        'banned_at',
    ];

    protected $casts = [
        'specialties' => 'array', // JSONB 필드는 'array' 또는 'asArray'로 캐스팅
        'banned_at' => 'datetime',
    ];

    /**
     * 이 용역사가 마스터 사용자로 연결된 사용자.
     */
    public function masterUser()
    {
        return $this->belongsTo(User::class, 'master_user_id');
    }

    /**
     * 이 용역사에 소속된 멤버들.
     */
    public function members()
    {
        return $this->hasMany(VendorMember::class);
    }

    /**
     * 이 용역사를 승인한 대행사들 (다대다 관계).
     */
    public function approvedAgencies()
    {
        return $this->belongsToMany(Agency::class, 'agency_approved_vendors', 'vendor_id', 'agency_id')
                    ->withTimestamps(); // approved_at은 pivot 테이블에 있지만, belongsToMany의 withTimestamps는 created_at, updated_at을 사용함
    }
}
