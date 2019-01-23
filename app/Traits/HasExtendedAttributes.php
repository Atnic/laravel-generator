<?php

namespace Atnic\LaravelGenerator\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

trait HasExtendedAttributes
{
    protected function castAttribute($key, $value)
    {
        if (is_null($value)) {
            return $value;
        }

        switch ($this->getCastType($key)) {
            case 'html':
                return $this->asHtml($value);
            case 'storage_url':
                return $this->asStorageUrl($value);
            case 'storage_public_url':
                return $this->asStoragePublicUrl($value);
            case 'storage_cloud_url':
                return $this->asStorageCloudUrl($value);
            case 'storage_path':
                return $this->asStoragePath($value);
            default:
                return parent::castAttribute($key, $value);
        }
    }

    /**
     * @param $value
     * @return HtmlString
     */
    protected function asHtml($value)
    {
        return new HtmlString($value);
    }

    /**
     * @param $value
     * @return string
     */
    protected function asStorageUrl($value)
    {
        return Storage::url($value);
    }

    /**
     * @param $value
     * @return string
     */
    protected function asStoragePublicUrl($value)
    {
        return Storage::disk('public')->url($value);
    }

    /**
     * @param $value
     * @return string
     */
    protected function asStorageCloudUrl($value)
    {
        if (Storage::cloud()->exists($value))
            return Storage::cloud()->url($value);
        elseif (Storage::exists($value))
            return $this->asStorageUrl($value);
        else
            return $this->asStoragePublicUrl($value);
    }

    /**
     * @param $value
     * @return string
     */
    protected function asStoragePath($value)
    {
        return Storage::path($value);
    }

    /**
     * Add the casted attributes to the attributes array.
     *
     * @param  array  $attributes
     * @param  array  $mutatedAttributes
     * @return array
     */
    protected function addCastAttributesToArray(array $attributes, array $mutatedAttributes)
    {
        $attributes = parent::addCastAttributesToArray($attributes, $mutatedAttributes);

        foreach ($this->getCasts() as $key => $value) {
            if (! array_key_exists($key, $attributes) || in_array($key, $mutatedAttributes)) {
                continue;
            }

            if ($attributes[$key] && $value === 'html') {
                $attributes[$key] = (string) $attributes[$key];
            }
        }

        return $attributes;
    }
}
