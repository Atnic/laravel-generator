<?php

namespace Atnic\LaravelGenerator\Traits;

use Carbon\Carbon;
use Illuminate\Support\Str;

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
        if (Str::endsWith($key, '_number_formatted')) {
            $k = Str::replaceLast('_number_formatted', '', $key);
            $value = $this->__get($k);
            return empty($value) ? $value : number()->format($value);
        } elseif (Str::endsWith($key, '_currency_formatted')) {
            $k = Str::replaceLast('_currency_formatted', '', $key);
            $value = $this->__get($k);
            return empty($value) ? $value : currency()->format($value, $this->currency_code ?? 'IDR');
        } elseif (Str::endsWith($key, '_date')) {
            $k = Str::replaceLast('_date', '_at', $key);
            if (in_array($k, $this->getDates())) {
                /** @var \Carbon\Carbon $value */
                $value = $this->__get($k);
                return empty($value) ? $value : $value->toDateString();
            }
        } elseif (Str::endsWith($key, '_time')) {
            $k = Str::replaceLast('_time', '_at', $key);
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
        if (Str::endsWith($key, '_number_formatted')) {
            $k = Str::replaceLast('_number_formatted', '', $key);
            $this->offsetUnset($key);
            $this->__set($k, empty($value) ? $value : number()->parse($value));
            return;
        } elseif (Str::endsWith($key, '_currency_formatted')) {
            $k = Str::replaceLast('_currency_formatted', '', $key);
            $this->offsetUnset($key);
            $this->__set($k, empty($value) ? $value : currency()->parse($value, $this->currency_code ?? 'IDR'));
            return;
        } elseif (Str::endsWith($key, '_date')) {
            $k = Str::replaceLast('_date', '_at', $key);
            if (in_array($k, $this->getDates())) {
                $this->offsetUnset($key);
                $this->__set($k, empty($value) ? $value : ($this->{$k} ?? now())->setDateFrom(Carbon::parse($value))->toDateTimeString());
                return;
            }
        } elseif (Str::endsWith($key, '_time')) {
            $k = Str::replaceLast('_time', '_at', $key);
            if (in_array($k, $this->dates)) {
                $this->offsetUnset($key);
                $this->__set($k, empty($value) ? $value : ($this->{$k} ?? now())->setTimeFrom(Carbon::parse($value))->toDateTimeString());
                return;
            }
        }
        parent::__set($key, $value);
    }
}
