<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Project extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'project_name',
        'start_datetime',
        'end_datetime',
        'preparation_start_datetime',
        '철수_end_datetime',
        'client_name',
        'client_contact_person',
        'client_contact_number',
        'main_agency_contact_user_id',
        'sub_agency_contact_user_id',
        'agency_id',
        'is_indoor',
        'location',
        'budget_including_vat',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'preparation_start_datetime' => 'datetime',
        '철수_end_datetime' => 'datetime',
        'budget_including_vat' => 'decimal:2',
        'is_indoor' => 'boolean',
    ];

    public function mainAgencyContactUser()
    {
        return $this->belongsTo(User::class, 'main_agency_contact_user_id');
    }

    public function subAgencyContactUser()
    {
        return $this->belongsTo(User::class, 'sub_agency_contact_user_id');
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * 이 프로젝트에 연결된 RFP들.
     */
    public function rfps()
    {
        return $this->hasMany(Rfp::class);
    }

    /**
     * 이 프로젝트에 연결된 스케줄들 (다형적 관계).
     */
    public function schedules()
    {
        return $this->morphMany(Schedule::class, 'schedulable');
    }
}
