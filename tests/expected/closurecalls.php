<?php

return [
    'existing' => env('EXISTING', 'default-value'), 'new2' => function (
        $content
    ) {
        return mb_strtolower($content);
    }, 'new3' => fn(
        $content
    ) => mb_strtolower($content),
];
