<?php

namespace Whitecube\NovaFlexibleContent\FileAdder;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\FileAdder\FileAdderFactory as OriginalFileAdderFactory;
use Whitecube\NovaFlexibleContent\FileAdder\FileAdder as NewFileAdder;

class FileAdderFactory extends OriginalFileAdderFactory
{
    /**
     * @param \Illuminate\Database\Eloquent\Model $subject
     * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
     * @return \Spatie\MediaLibrary\FileAdder\FileAdder
     */
    public static function create(Model $subject, $file, string $suffix = null)
    {
        return app(NewFileAdder::class)
            ->setSubject($subject)
            ->setFile($file)
            ->setMediaCollectionSuffix($suffix);
    }
}
