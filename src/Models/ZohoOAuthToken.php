<?php

namespace Asciisd\Zoho\Models;

use Illuminate\Database\Eloquent\Model;

class ZohoOAuthToken extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'zoho_oauth_tokens';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_identifier',
        'access_token',
        'refresh_token',
        'expires_at',
        'token_type',
        'grant_token',
        'data_center',
        'environment',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Check if the access token is expired.
     */
    public function isExpired(): bool
    {
        if (! $this->expires_at) {
            return true;
        }

        return $this->expires_at->isPast();
    }

    /**
     * Check if the access token is valid.
     */
    public function isValid(): bool
    {
        return ! $this->isExpired() && ! empty($this->access_token);
    }
}
