<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'user_id', 'title', 'description',
        'start', 'end', 'all_day', 'category', 'url',
    ];

    protected $casts = [
        'start'   => 'datetime',
        'end'     => 'datetime',
        'all_day' => 'boolean',
    ];

    public const CATEGORIES = ['personal', 'business', 'family', 'holiday', 'etc'];

    public const COLORS = [
        'personal' => '#FF3E1D',
        'business' => '#03C3EC',
        'family'   => '#FFAB00',
        'holiday'  => '#71DD37',
        'etc'      => '#696CFF',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getColorAttribute(): string
    {
        return self::COLORS[$this->category] ?? self::COLORS['etc'];
    }

    public function toFullCalendar(): array
    {
        return [
            'id'              => $this->id,
            'title'           => $this->title,
            'start'           => $this->start->toIso8601String(),
            'end'             => optional($this->end)->toIso8601String(),
            'allDay'          => (bool) $this->all_day,
            'url'             => $this->url,
            'backgroundColor' => $this->color,
            'borderColor'     => $this->color,
            'extendedProps'   => [
                'description' => $this->description,
                'category'    => $this->category,
            ],
        ];
    }
}
