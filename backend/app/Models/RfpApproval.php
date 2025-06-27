<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids; // HasUuids 트레이트 추가

class RfpApproval extends Model
{
    use HasUuids; // HasUuids 트레이트 사용

    protected $fillable = [
        'rfp_id',
        'approver_user_id',
        'status',
        'comment',
        'approved_at',
    ];

    public function rfp()
    {
        return $this->belongsTo(Rfp::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }
}
