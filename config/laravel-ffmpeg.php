<?php

return [
    'default_disk' => 'local',

    'ffmpeg' => [
        'binaries' => env('FFMPEG_BINARIES', '/usr/localbin/ffmpeg'),
        'threads' => 12,
    ],

    'ffprobe' => [
        'binaries' => env('FFPROBE_BINARIES', '/usr/localbin/ffprobe'),
    ],

    'timeout' => 3600,
];
