<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    protected function casts(): array
    {
        return [
            'value' => 'string',
        ];
    }

    /**
     * A small map of the keys the application manages. Keys that point to
     * uploaded files are persisted as storage paths, everything else as text.
     */
    public static function fileKeys(): array
    {
        return [
            'letterhead_image',
            'letterhead_landscape',
            'watermark_image',
            'official_stamp',
            'director_signature',
        ];
    }

    public static function textKeys(): array
    {
        return [
            'director_name',
            'issue_date',
            'current_session',
            'result_year',
            'verification_enabled',
        ];
    }

    public static function allKeys(): array
    {
        return array_merge(self::fileKeys(), self::textKeys());
    }
}
