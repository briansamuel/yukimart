<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Models\User;

class PlatformUserProvider extends EloquentUserProvider
{
    /**
     * Retrieve a user by their unique identifier.
     */
    public function retrieveById($identifier)
    {
        $model = $this->createModel();

        return $this->newModelQuery($model)
                    ->withoutGlobalScopes() // Bypass tenant scope
                    ->where($model->getAuthIdentifierName(), $identifier)
                    ->whereNull('tenant_id') // Platform users have null tenant_id
                    ->first();
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     */
    public function retrieveByToken($identifier, $token)
    {
        $model = $this->createModel();

        $retrievedModel = $this->newModelQuery($model)
                                ->withoutGlobalScopes() // Bypass tenant scope
                                ->where($model->getAuthIdentifierName(), $identifier)
                                ->whereNull('tenant_id') // Platform users have null tenant_id
                                ->first();

        if (!$retrievedModel) {
            return null;
        }

        $rememberToken = $retrievedModel->getRememberToken();

        return $rememberToken && hash_equals($rememberToken, $token)
                        ? $retrievedModel : null;
    }

    /**
     * Retrieve a user by the given credentials.
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials) ||
           (count($credentials) === 1 &&
            str_contains($this->firstCredentialKey($credentials), 'password'))) {
            return null;
        }

        // Build the query without tenant scope (for platform users)
        $query = $this->newModelQuery()->withoutGlobalScopes();

        foreach ($credentials as $key => $value) {
            if (str_contains($key, 'password')) {
                continue;
            }

            if (is_array($value) || $value instanceof \Arrayable) {
                $query->whereIn($key, $value);
            } else {
                $query->where($key, $value);
            }
        }

        // Only platform users (tenant_id is null)
        $query->whereNull('tenant_id');

        return $query->first();
    }

    /**
     * Validate a user against the given credentials.
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        // Ensure this is a platform user
        if ($user->tenant_id !== null) {
            return false;
        }

        return parent::validateCredentials($user, $credentials);
    }

    /**
     * Get the first key from the credential array.
     */
    protected function firstCredentialKey(array $credentials)
    {
        foreach ($credentials as $key => $value) {
            return $key;
        }

        return null;
    }
}
