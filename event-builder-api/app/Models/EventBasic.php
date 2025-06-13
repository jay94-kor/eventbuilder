<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventBasic extends Model
{
    protected $fillable = [
        'client_name',
        'event_title',
        'event_location',
        'venue_type',
        'zones',
        'total_budget',
        'is_total_budget_undecided',
        'event_start_date_range_min',
        'event_start_date_range_max',
        'event_end_date_range_min',
        'event_end_date_range_max',
        'event_duration_days',
        'setup_start_date',
        'teardown_end_date',
        'project_kickoff_date',
        'settlement_close_date',
        'contact_person_name',
        'contact_person_contact',
        'admin_person_name',
        'admin_person_contact',
    ];

    protected $casts = [
        'zones' => 'array',
        'total_budget' => 'decimal:2',
        'is_total_budget_undecided' => 'boolean',
        'event_start_date_range_min' => 'date',
        'event_start_date_range_max' => 'date',
        'event_end_date_range_min' => 'date',
        'event_end_date_range_max' => 'date',
        'setup_start_date' => 'date',
        'teardown_end_date' => 'date',
        'project_kickoff_date' => 'date',
        'settlement_close_date' => 'date',
    ];
}
