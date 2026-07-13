<?php

declare(strict_types=1);

namespace Bhuba\AuthProfilePackage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PersonalAccessToken extends Model
{
    protected $table = 'auth_profile_tokens';

    public $timestamps = false;

    public const CREATED_AT = 'created_at';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tokenable_type',
        'tokenable_id',
        'token',
        'expires_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function tokenable(): MorphTo
    {
        return $this->morphTo();
    }
}
