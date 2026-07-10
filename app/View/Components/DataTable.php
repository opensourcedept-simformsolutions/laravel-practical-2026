<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DataTable extends Component
{
    
    
    public function __construct(public string $id,
        public bool $searching = true,
        public bool $paging = true,
        public bool $ordering = true,
        public int $pageLength = 5) {}

    
    public function render(): View|Closure|string
    {
        return view('components.data-table');
    }
}
