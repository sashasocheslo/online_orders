<?php

namespace App\View\Components;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class TextInput extends Component
{
   
    public function __construct(
        public ?string $value = null,
        public ?string $name = null,
        public ?string $placeholder = null,
        public ?string $formId = null,
        public ?string $type = 'text',
        public bool $textarea = false
    )
    {

    }
    public function render(): View|Closure|string
    {
        return view('components.text-input');
    }
}
