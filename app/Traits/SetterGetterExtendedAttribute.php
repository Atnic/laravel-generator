<?php

namespace Atnic\LaravelGenerator\Traits;

use Carbon\Carbon;

trait SetterGetterExtendedAttribute
{
    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string $key
     * @return mixed
     */
    public function __get($key)
    {
        if (ends_with($key, '_number_formatted')) {
            $k = str_replace_last('_number_formatted', '', $key);
            $value = $this->__get($k);
            return empty($value) ? $value : number()->format($value);
        } elseif (ends_with($key, '_currency_formatted')) {
            $k = str_replace_last('_currency_formatted', '', $key);
            $value = $this->__get($k);
            return empty($value) ? $value : currency()->format($value, $this->currency_code ?? 'IDR');
        } elseif (ends_with($key, '_date')) {
            $k = str_replace_last('_date', '_at', $key);
            if (in_array($k, $this->getDates())) {
                /** @var \Carbon\Carbon $value */
                $value = $this->__get($k);
                return empty($value) ? $value : $value->toDateString();
            }
        } elseif (ends_with($key, '_time')) {
            $k = str_replace_last('_time', '_at', $key);
            if (in_array($k, $this->dates)) {
                /** @var \Carbon\Carbon $value */
                $value = $this->__get($k);
                return empty($value) ? $value : $value->toTimeString();
            }
        }
        return parent::__get($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set($key, $value)
    {
        if (ends_with($key, '_number_formatted')) {
            $k = str_replace_last('_number_formatted', '', $key);
            $this->offsetUnset($key);
            $this->__set($k, empty($value) ? $value : number()->parse($value));
            return;
        } elseif (ends_with($key, '_currency_formatted')) {
            $k = str_replace_last('_currency_formatted', '', $key);
            $this->offsetUnset($key);
            $this->__set($k, empty($value) ? $value : currency()->parse($value, $this->currency_code ?? 'IDR'));
            return;
        } elseif (ends_with($key, '_date')) {
            $k = str_replace_last('_date', '_at', $key);
            if (in_array($k, $this->getDates())) {
                $this->offsetUnset($key);
                $this->__set($k, empty($value) ? $value : ($this->{$k} ?? now())->setDateFrom(Carbon::parse($value))->toDateTimeString());
                return;
            }
        } elseif (ends_with($key, '_time')) {
            $k = str_replace_last('_time', '_at', $key);
            if (in_array($k, $this->dates)) {
                $this->offsetUnset($key);
                $this->__set($k, empty($value) ? $value : ($this->{$k} ?? now())->setTimeFrom(Carbon::parse($value))->toDateTimeString());
                return;
            }
        }
        parent::__set($key, $value);
    }
}
