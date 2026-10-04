@php
    $favicon = null;

    if (file_exists(public_path('images/favicon.svg'))) {
        $favicon = ['path' => 'images/favicon.svg', 'type' => 'image/svg+xml'];
    } elseif (file_exists(public_path('images/favicon.png'))) {
        $favicon = ['path' => 'images/favicon.png', 'type' => 'image/png'];
    }
@endphp

@if ($favicon)
    <link rel="icon" type="{{ $favicon['type'] }}" href="{{ asset($favicon['path']) }}">
@endif
