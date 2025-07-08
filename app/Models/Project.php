<?php

namespace App\Models;

use App\Helpers\Common;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;
class Project extends Model
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
    protected $appends = ['badge_status', 'first_letter_name', 'background'];

    protected function firstLetterName(): Attribute
    {
        return new Attribute(
            get: fn ($value,  $attributes) => mb_substr($attributes['project_name'], 0, 1),
        );
    }

    /**
     * Get the user's project_status.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function projectStatus(): Attribute
    {
        return Attribute::make(
            get: function($value,  $attributes) {
                $dueDate = Carbon::parse($attributes['project_due_date']);
                $nowDate = Carbon::now();
                $result = $nowDate->gt($dueDate);
                return $result && $value == 'in_progress' ? 'over_due' : $value;

            }
        );
    }

    protected function badgeStatus(): Attribute
    {
        return Attribute::make(
            get: function($value,  $attributes) {
                $dueDate = Carbon::parse($attributes['project_due_date']);
                $nowDate = Carbon::now();
                $result = $nowDate->gt($dueDate);
                $status = $result && $attributes['project_status'] == 'in_progress' ? 'over_due' : $attributes['project_status'];
                return Common::statusBadge($status);

            }
        );

    }

    protected function background(): Attribute
    {
        return new Attribute(
            get: fn($value,  $attributes) => Common::randomBackground(),
        );
    }



    /**
     * Get the user's created_at.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function projectDueDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($value)->format('d/m/Y')
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
     * Get the user's created_at_default.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function createdAtDefault(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($value)->format('Y-m-d H:i:s')
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

    // Relation
    public function project_users()
    {
        return $this->hasMany(ProjectUser::class, 'project_id');
    }

    // Relation Task
    public function tasks()
    {
        return $this->hasMany(Task::class, 'project_id', 'id');
    }


    public function users()
    {
        return $this->hasMany(User::class, 'id',  'user_id');
    }
}
