<a href="{{ $attributes['href'] }}"
   class="d-inline-block w-auto text-center rounded px-3 py-2 small fw-medium
   {{ $attributes['active'] ? 'bg-white text-dark shadow-sm' : 'text-secondary' }}">
    {{ $slot }}
</a>
