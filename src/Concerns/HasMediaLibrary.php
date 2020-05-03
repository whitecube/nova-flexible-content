<?php

namespace Whitecube\NovaFlexibleContent\Concerns;

use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Spatie\MediaLibrary\MediaCollections\FileAdderFactory;
use Spatie\MediaLibrary\MediaCollections\MediaRepository;
use Whitecube\NovaFlexibleContent\Flexible;
use Spatie\MediaLibrary\HasMedia;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Illuminate\Support\Collection;
use Whitecube\NovaFlexibleContent\Http\ScopedRequest;

trait HasMediaLibrary {

    use InteractsWithMedia;

    /**
     * Return the underlying model implementing the HasMedia interface
     *
     * @return \Spatie\MediaLibrary\HasMedia
     */
    protected function getMediaModel() : HasMedia
    {
        $model = Flexible::getOriginModel() ?? $this->model;

        if(is_null($model) || !($model instanceof HasMedia)) {
            throw new \Exception('Origin HasMedia model not found.');
        }

        return $model;
    }

    /**
     * Add a file to the medialibrary.
     *
     * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
     * @return \Spatie\MediaLibrary\FileAdder\FileAdder
     */
    public function addMedia($file) : FileAdder
    {
        return app(FileAdderFactory::class)
            ->create($this->getMediaModel(), $file)
            ->preservingOriginal();
    }

    /**
     * Get media collection by its collectionName.
     *
     * @param string $collectionName
     * @param array|callable $filters
     *
     * @return \Illuminate\Support\Collection
     */
    public function getMedia(string $collectionName = 'default', $filters = []): Collection
    {
        return app(MediaRepository::class)
            ->getCollection($this->getMediaModel(), $collectionName, $filters);
    }

}