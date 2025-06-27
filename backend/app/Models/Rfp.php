<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids; // HasUuids 트레이트 추가

class Rfp extends Model
{
    use HasUuids; // HasUuids 트레이트 사용

    protected $fillable = [
        'project_id',
        'current_status',
        'created_by_user_id',
        'agency_id',
        'issue_type',
        'rfp_description',
        'closing_at',
        'published_at', // published_at도 fillable에 추가
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function elements()
    {
        return $this->hasMany(RfpElement::class);
    }
}
