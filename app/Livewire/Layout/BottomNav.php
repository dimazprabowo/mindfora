<?php

namespace App\Livewire\Layout;

use App\Livewire\Traits\HasMenuItems;
use Livewire\Component;

/**
 * App-style bottom tab bar, rendered only inside the Android app (see is_native_app()).
 * Tabs = first top-level menu items that link directly to a route; everything else stays in the sidebar ("More").
 */
class BottomNav extends Component
{
    use HasMenuItems;

    private const MAX_TABS = 4;

    public function render()
    {
        $items = $this->getMenuItems();
        $tabs = array_slice(array_values(array_filter($items, fn (array $item) => isset($item['route']))), 0, self::MAX_TABS);

        return view('livewire.layout.bottom-nav', [
            'tabs' => $tabs,
            'moreActive' => in_array(true, array_column($items, 'active'), true)
                && ! in_array(true, array_column($tabs, 'active'), true),
        ]);
    }
}
