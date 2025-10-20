<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SavedConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'configuration_number', 'machine_id', 'selected_components', 'total_price',
        'customer_name', 'customer_email', 'customer_company', 'customer_phone',
        'notes', 'status'
    ];

    protected $casts = [
        'selected_components' => 'array',
        'total_price' => 'decimal:2'
    ];

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function getComponentsAttribute()
    {
        return Component::whereIn('id', $this->selected_components)->get();
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($configuration) {
            if (empty($configuration->configuration_number)) {
                $count = static::count() + 1;
                $configuration->configuration_number = 'CFG' . date('Ymd') . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}