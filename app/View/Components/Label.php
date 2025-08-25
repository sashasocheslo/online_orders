<?php

namespace App\View\Components;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class Label extends Component
{
    public function __construct(
        public ?string $for = '',
        public ?bool $required = false
    )
    {

    }

    public function render(): View|Closure|string
    {
        return view('components.label');
    }
}
