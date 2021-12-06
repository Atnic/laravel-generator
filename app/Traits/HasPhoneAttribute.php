<?php

namespace Atnic\LaravelGenerator\Traits;

use Illuminate\Support\Str;
use Propaganistas\LaravelPhone\PhoneNumber;

trait HasPhoneAttribute
{
    /**
     * @param PhoneNumber|string|null $value
     */
    public function setPhoneAttribute($value = null)
    {
        $this->attributes['phone_country'] = $this->phone_country;
        if (!$value) {
            $this->attributes['phone'] = null;
        }
        elseif ($value instanceof PhoneNumber) {
            $this->attributes['phone'] = $value->serialize();
            $this->attributes['phone_country'] = $value->getCountry();
        }
        elseif (is_string($value) && Str::contains($value, '+')) {
            $this->attributes['phone'] = phone($value)->serialize();
            $this->attributes['phone_country'] = phone($value)->getCountry();
        }
        else {
            $this->attributes['phone'] = phone($value, $this->phone_country)->serialize();
            $this->attributes['phone_country'] = phone($value, $this->phone_country)->getCountry();
        }
    }

    /**
     * @param $value
     * @return string
     */
    public function getPhoneCountryAttribute($value = null)
    {
        if ($value) return $value;
        if ($this->isDirty([ 'phone_country' ])) $sync = false;
        else $sync = true;
        $this->attributes['phone_country'] = $value ? : ($this->default_phone_country ?? 'US');
        if ($sync)
            $this->syncOriginalAttribute('phone_country');
        return $this->attributes['phone_country'];
    }

    /**
     * @param $value
     * @return string
     */
    public function getPhoneAttribute($value = null)
    {
        if ($value && Str::contains($value, '+')) return phone($value)->serialize();
        try {
            if ($this->isDirty([ 'phone' ])) $sync = false;
            else $sync = true;
            $this->attributes['phone'] = empty($value) ? null : phone($value, $this->phone_country)->serialize();
            if ($sync)
                $this->syncOriginalAttribute('phone');
            return $this->attributes['phone'];
        } catch (\Exception $exception) {
            return null;
        }
    }
}
