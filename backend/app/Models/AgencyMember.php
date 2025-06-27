<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot; // <-- Model 대신 Pivot 상속

class AgencyMember extends Pivot // <-- Model 대신 Pivot 상속
{
    use HasFactory;

    // 테이블 이름 (모델 이름의 복수형과 다를 경우 명시)
    protected $table = 'agency_members';

    // 이 테이블은 자동 증가하는 'id' 컬럼을 사용하지 않음을 명시
    public $incrementing = false;

    // `firstOrCreate`가 기본적으로 'id' 컬럼을 반환하려고 시도하므로, 명시적으로 primary key를 null로 설정
    // protected $primaryKey = null; // Laravel 9+ with Pivot often doesn't strictly need this if incrementing is false and fillable includes all primary components

    protected $fillable = [
        'user_id',
        'agency_id',
        'position',
        'permissions',
        'joined_at',
    ];

    protected $casts = [
        'permissions' => 'array', // JSONB 컬럼을 배열로 캐스팅
        'joined_at' => 'datetime',
    ];

    // 관계 정의 (선택 사항: 필요에 따라 추가)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }
}
