<?php

namespace Whitecube\NovaFlexibleContent\Concerns;

use Whitecube\NovaFlexibleContent\Flexible;
use Whitecube\NovaFlexibleContent\FileAdder\FileAdderFactory;
use Spatie\MediaLibrary\MediaRepository;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
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
        $model = Flexible::getOriginModel();

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
    public function addMedia($file)
    {
        return app(FileAdderFactory::class)
            ->create($this->getMediaModel(), $file, '_' . $this->inUseKey())
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
        return app(MediaRepository::class)->getCollection($this->getMediaModel(), $collectionName . '_' . $this->inUseKey(), $filters);
    }

}
