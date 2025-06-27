<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // HasUuids 트레이트 추가

class Agency extends Model
{
    use HasFactory, HasUuids; // HasUuids 트레이트 사용

    protected $fillable = [
        'name',
        'business_registration_number',
        'address',
        'master_user_id',
        'subscription_status',
        'subscription_end_date',
    ];

    protected $casts = [
        'subscription_end_date' => 'datetime',
    ];

    /**
     * 이 대행사의 마스터 사용자.
     */
    public function masterUser()
    {
        return $this->belongsTo(User::class, 'master_user_id');
    }

    /**
     * 이 대행사에 소속된 멤버들.
     */
    public function members()
    {
        return $this->hasMany(AgencyMember::class);
    }

    /**
     * 이 대행사가 승인한 용역사들 (다대다 관계).
     */
    public function approvedVendors()
    {
        return $this->belongsToMany(Vendor::class, 'agency_approved_vendors', 'agency_id', 'vendor_id')
                    ->withTimestamps();
    }
}
