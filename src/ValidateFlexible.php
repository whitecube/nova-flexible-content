<?php

namespace Whitecube\NovaFlexibleContent;

use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Collection;
use Laravel\Nova\Http\Requests\NovaRequest;

trait ValidateFlexible
{
    /**
     * Get all flexible field of resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Illuminate\Support\Collection
     */
    protected static function getFlexibleFields(NovaRequest $request): Collection
    {
        return (new static(new static::$model))->availableFields($request)
            ->filter(function ($field) {
                return $field instanceof Flexible;
            });
    }

    /**
     * Validate flexible fields.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     */
    protected static function validateFlexibleFields(NovaRequest $request, ValidatorContract $validator): void
    {
        static::getFlexibleFields($request)->each(function (Flexible $field) use ($validator, $request) {
            $field->validate($request, $validator);
        });
    }

    /**
     * Validate flexible fields for creation.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     */
    protected static function validateFlexibleFieldsForCreation(NovaRequest $request, ValidatorContract $validator): void
    {
        static::getFlexibleFields($request)->each(function (Flexible $field) use ($validator, $request) {
                $field->validateForCreation($request, $validator);
            });
    }

    /**
     * Validate flexible fields for update.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     */
    protected static function validateFlexibleFieldsForUpdate(NovaRequest $request, ValidatorContract $validator): void
    {
        static::getFlexibleFields($request)->each(function (Flexible $field) use ($validator, $request) {
            $field->validateForUpdate($request, $validator);
        });
    }
}
