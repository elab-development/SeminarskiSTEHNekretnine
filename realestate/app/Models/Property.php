<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'price',
        'address',
        'city',
        'status',
        'property_type_id',
        'listed_by'
    ];

    public function propertyType()
    {
        return $this->belongsTo(PropertyType::class);
    }

    public function listedBy()
    {
        return $this->belongsTo(User::class, 'listed_by');
    }

    public function inquiries()
    {
        return $this->hasMany(Inquiry::class);
    }
}
