<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ElementDefinition extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'element_type',
        'display_name',
        'description',
        'input_schema',
        'default_details_template',
        'recommended_elements',
    ];

    protected $casts = [
        'input_schema' => 'array',
        'default_details_template' => 'array',
        'recommended_elements' => 'array',
    ];
}