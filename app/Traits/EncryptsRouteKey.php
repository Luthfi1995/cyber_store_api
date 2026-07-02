<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;

trait EncryptsRouteKey
{
    /**
     * Get the value of the model's route key.
     * Encrypts the key if the request is for the admin panel.
     *
     * @return mixed
     */
    public function getRouteKey()
    {
        if (request()->is('admin*')) {
            return Crypt::encryptString($this->getKey());
        }
        return $this->getKey();
    }

    /**
     * Retrieve the model for a bound value.
     * Decrypts the value if it's for the admin panel or looks like encrypted string.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if (request()->is('admin*') || (is_string($value) && !is_numeric($value))) {
            try {
                $decrypted = Crypt::decryptString($value);
                return $this->where($field ?? $this->getRouteKeyName(), $decrypted)->first();
            } catch (\Exception $e) {
                // Return null if decrypt fails for security
                return null;
            }
        }
        return parent::resolveRouteBinding($value, $field);
    }
}
