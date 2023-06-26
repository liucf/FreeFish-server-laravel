<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'price',
        'originalPrice',
        'rootcategory_id',
        'subcategory_id',
        'category_id',
        'status',
        'description',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'price' => 'float',
        'originalPrice' => 'float',
        'rootcategory_id' => 'integer',
        'subcategory_id' => 'integer',
        'category_id' => 'integer',
    ];

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function thumbs(): BelongsToMany
    {
        return $this->belongsToMany(Thumb::class);
    }

    public function video(): HasOne
    {
        return $this->hasOne(Video::class);
    }

    public function rootcategory(): BelongsTo
    {
        return $this->belongsTo(Rootcategory::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
