<?php

namespace Whitecube\NovaFlexibleContent\Concerns;

use Spatie\MediaLibrary\MediaRepository;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\FileAdder\FileAdderFactory;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Illuminate\Support\Collection;

trait HasMediaLibrary {

    use HasMediaTrait;

    /**
     * Return the underlying model implementing the HasMedia interface
     *
     * @return \Spatie\MediaLibrary\HasMedia\HasMedia
     */
    protected function getMediaModel() : HasMedia
    {
        return resolve(NovaRequest::class)->findModelOrFail();
    }

    /**
     * Add a file to the medialibrary.
     *
     * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
     * @return \Spatie\MediaLibrary\FileAdder\FileAdder
     */
    public function addMedia($file)
    {
        return app(FileAdderFactory::class)->create($this->getMediaModel(), $file);
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
        return app(MediaRepository::class)->getCollection($this->getMediaModel(), $collectionName, $filters);
    }

}