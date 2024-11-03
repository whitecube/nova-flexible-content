<?php

declare(strict_types=1);

namespace Wmt\NovaFlexibleContent\FileAdder;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\MediaCollections\FileAdderFactory as OriginalFileAdderFactory;
use Wmt\NovaFlexibleContent\FileAdder\FileAdder as NewFileAdder;

class FileAdderFactory extends OriginalFileAdderFactory
{
    /**
     * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
     */
    public static function create(Model $subject, $file, ?string $suffix = null): \Spatie\MediaLibrary\MediaCollections\FileAdder
    {
        return app(NewFileAdder::class)
            ->setSubject($subject)
            ->setFile($file)
            ->setMediaCollectionSuffix($suffix)
        ;
    }
}
