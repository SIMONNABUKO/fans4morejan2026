<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ListType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'is_default'];

    protected $casts = [
        'is_default' => 'boolean',
    ];


    public function users(): BelongsToMany
    {

        return $this->belongsToMany(User::class, 'user_lists');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'list_members', 'list_type_id', 'member_id')
            ->withTimestamps();
    }
}