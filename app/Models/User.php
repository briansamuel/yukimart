<?php

namespace App\Models;

use App\Helpers\Common;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\HasRolesAndPermissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class User extends Authenticatable
{
    use Notifiable, HasFactory, HasRolesAndPermissions;


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
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'birth_date' => 'date',
    ];

    /**
     * The storage format of the model's date columns.
     *
     * @var string
     */
    // protected $dateFormat = 'd/m/Y H:i:s';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['last_ago', 'first_letter_name', 'page_edit', 'page_delete', 'page_detail', 'background'];

    protected function firstLetterName(): Attribute
    {
        return new Attribute(
            get: fn ($value,  $attributes) => mb_substr($attributes['full_name'], 0, 1),
        );
    }

    protected function background(): Attribute
    {
        return new Attribute(
            get: fn($value,  $attributes) => Common::randomBackground(),
        );
    }

    /**
     * Get user setting value
     */
    public function getSetting($key, $default = null)
    {
        $setting = \App\Models\UserSetting::where('user_id', $this->id)
                                         ->where('key', $key)
                                         ->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Set user setting value
     */
    public function setSetting($key, $value)
    {
        return \App\Models\UserSetting::updateOrCreate(
            ['user_id' => $this->id, 'key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Relationship with branch shops through pivot table
     */
    public function branchShops()
    {
        return $this->belongsToMany(BranchShop::class, 'user_branch_shops')
                    ->withPivot([
                        'role_in_shop',
                        'start_date',
                        'end_date',
                        'is_active',
                        'is_primary',
                        'notes',
                        'assigned_by',
                        'assigned_at'
                    ])
                    ->withTimestamps();
    }

    /**
     * Get active branch shops for this user
     */
    public function activeBranchShops()
    {
        return $this->branchShops()->wherePivot('is_active', true);
    }

    /**
     * Get primary branch shop for this user
     */
    public function primaryBranchShop()
    {
        return $this->branchShops()->wherePivot('is_primary', true)->first();
    }

    /**
     * Get current branch shops (not ended)
     */
    public function currentBranchShops()
    {
        return $this->branchShops()
                    ->wherePivot('is_active', true)
                    ->where(function($query) {
                        $query->whereNull('user_branch_shops.end_date')
                              ->orWhere('user_branch_shops.end_date', '>=', now()->toDateString());
                    });
    }

    /**
     * Check if user works in specific branch shop
     */
    public function worksInBranchShop($branchShopId)
    {
        return $this->currentBranchShops()->where('branch_shops.id', $branchShopId)->exists();
    }

    /**
     * Check if user is manager of specific branch shop
     */
    public function isManagerOf($branchShopId)
    {
        return $this->currentBranchShops()
                    ->where('branch_shops.id', $branchShopId)
                    ->wherePivot('role_in_shop', 'manager')
                    ->exists();
    }

    /**
     * Get user's role in specific branch shop
     */
    public function getRoleInBranchShop($branchShopId)
    {
        $branchShop = $this->branchShops()
                          ->where('branch_shops.id', $branchShopId)
                          ->wherePivot('is_active', true)
                          ->first();

        return $branchShop ? $branchShop->pivot->role_in_shop : null;
    }

    /**
     * Get formatted birth date for forms
     */
    public function getFormattedBirthDateAttribute()
    {
        return $this->birth_date ? $this->birth_date->format('Y-m-d') : '';
    }

    /**
     * Get display birth date
     */
    public function getDisplayBirthDateAttribute()
    {
        return $this->birth_date ? $this->birth_date->format('d/m/Y') : '';
    }

    /**
     * Format pivot date safely
     */
    public static function formatPivotDate($date, $format = 'd/m/Y')
    {
        if (!$date) {
            return '-';
        }

        try {
            return \Carbon\Carbon::parse($date)->format($format);
        } catch (\Exception $e) {
            return '-';
        }
    }


    /**
     * Get the user's human created_at.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */

    protected function lastAgo(): Attribute
    {
        Carbon::setLocale('vi');
        return new Attribute(
            get: fn ($value, $attributes) => Carbon::parse($attributes['created_at'])->diffForHumans()
        );
    }

    /**
     * Get the user's edit page url.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */

    protected function pageEdit(): Attribute
    {
        return new Attribute(
            get: fn ($value,  $attributes) => route('admin.user.edit', ['user_id' => $attributes['id']]),
        );
    }

    /**
     * Get the user's delete page url.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */

    protected function pageDelete(): Attribute
    {
        return new Attribute(
            get: fn ($value,  $attributes) => route('admin.user.delete', ['user_id' => $attributes['id']]),
        );
    }

    /**
     * Get the user's detail page url.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */

    protected function pageDetail(): Attribute
    {
        return new Attribute(
            get: fn ($value,  $attributes) => route('admin.user.detail', ['user_id' => $attributes['id']]),
        );
    }
    /**
     * Get the user's created_at.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($value)->format('d/m/Y H:i:s')
        );
    }

    /**
     * Get the user's updated_at.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function updatedAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) =>  Carbon::parse($value)->format('d/m/Y H:i:s'),
        );
    }
}
