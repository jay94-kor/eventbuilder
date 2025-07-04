<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contract extends Model
{
    use HasUuids, HasFactory;

    protected $table = 'contracts';

    protected $fillable = [
        'announcement_id',
        'proposal_id',
        'vendor_id',
        'final_price',
        'contract_file_path',
        'contract_signed_at',
        'prepayment_amount',
        'prepayment_paid_at',
        'balance_amount',
        'balance_paid_at',
        'payment_status',
    ];

    protected $casts = [
        'final_price' => 'decimal:2',
        'prepayment_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'contract_signed_at' => 'datetime',
        'prepayment_paid_at' => 'datetime',
        'balance_paid_at' => 'datetime',
    ];

    /**
     * 공고와의 관계 (다대일)
     */
    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }

    /**
     * 제안서와의 관계 (일대일)
     */
    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    /**
     * 용역사와의 관계 (다대일)
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
