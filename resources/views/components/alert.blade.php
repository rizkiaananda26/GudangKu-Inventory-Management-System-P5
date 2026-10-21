@props(['type' => 'info'])

<div class="flash flash-{{ $type }}">
    {{ $slot }}
</div>
