<?php

namespace App\Models;

// 다음 use 문을 추가합니다.
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number', // 추가했던 phone_number도 fillable에 추가합니다.
        'user_type',    // 추가했던 user_type도 fillable에 추가합니다.
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the agency members associated with the user.
     */
    public function agency_members()
    {
        return $this->hasMany(AgencyMember::class);
    }

    /**
     * Get the vendor members associated with the user.
     */
    public function vendor_members()
    {
        return $this->hasMany(VendorMember::class);
    }

    /**
     * 이 사용자가 심사위원으로 부여한 평가들.
     */
    public function evaluations()
    {
        return $this->hasMany(Evaluation::class, 'evaluator_user_id');
    }

    /**
     * 이 사용자가 심사위원으로 배정된 공고들.
     */
    public function announcement_evaluations()
    {
        return $this->hasMany(AnnouncementEvaluator::class);
    }

    /**
     * 이 사용자의 심사위원 이력들.
     */
    public function evaluator_histories()
    {
        return $this->hasMany(EvaluatorHistory::class, 'evaluator_user_id');
    }

    /**
     * 특정 요소에 대한 이 사용자의 평가 이력.
     */
    public function getExpertiseInElement($elementType)
    {
        return $this->evaluator_histories()
            ->where('element_type', $elementType)
            ->where('evaluation_completed', true)
            ->count();
    }

    /**
     * 이 사용자가 가장 많이 평가한 요소 타입.
     */
    public function getTopExpertiseElement()
    {
        return $this->evaluator_histories()
            ->where('evaluation_completed', true)
            ->select('element_type')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('element_type')
            ->orderByDesc('count')
            ->first();
    }
}
