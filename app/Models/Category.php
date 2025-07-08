<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;
use App\Models\CategoryPost;
class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // protected $fillable = [
    //     'username', 'email', 'password',
    // ];

    protected $guarded = [];

      /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */

    protected $appends = ['last_ago', 'category_edit'];


    protected function categoryEdit(): Attribute
    {
        return new Attribute(
            get: fn($value,  $attributes) => route('category.edit', ['category_id' => $attributes['id']]),
        );
    }


    protected function lastAgo(): Attribute
    {
        Carbon::setLocale('vi');
        return new Attribute(
            get: fn ($value, $attributes) => Carbon::parse($attributes['created_at'])->diffForHumans()
        );
    }

    // Relation
    public function posts()
    {
        return $this->hasMany(CategoryPost::class, 'category_id');
    }
}
