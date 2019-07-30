<?php

namespace Atnic\LaravelGenerator\Traits;

use Propaganistas\LaravelPhone\PhoneNumber;

trait HasPhoneAttribute
{
    /**
     * @param PhoneNumber|string|null $value
     */
    public function setPhoneAttribute($value = null)
    {
        $this->attributes['phone_country'] = $this->phone_country;
        if (is_null($value))
            $this->attributes['phone'] = $value;
        elseif ($value instanceof PhoneNumber)
            $this->attributes['phone'] = $value->serialize();
        elseif (is_string($value) && str_contains($value, '+'))
            $this->attributes['phone'] = phone($value)->serialize();
        else {
            $this->attributes['phone'] = phone($value, $this->phone_country)->serialize();
        }
    }

    /**
     * @param $value
     * @return string
     */
    public function getPhoneCountryAttribute($value = null)
    {
        if ($value) return $value;
        $this->attributes['phone_country'] = $value ? : 'ID';
        $this->syncOriginalAttribute('phone_country');
        return $this->attributes['phone_country'];
    }

    /**
     * @param $value
     * @return string
     */
    public function getPhoneAttribute($value = null)
    {
        if ($value && str_contains($value, '+')) return phone($value)->serialize();
        try {
            $this->attributes['phone'] = empty($value) ? null : phone($value, $this->phone_country)->serialize();
            $this->syncOriginalAttribute('phone');
            return $this->attributes['phone'];
        } catch (\Exception $exception) {
            return null;
        }
    }
}
