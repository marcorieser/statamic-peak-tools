<?php

namespace Studio1902\PeakTools\Widgets;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Widgets\Widget;

class ImagesMissingAlt extends Widget
{
    /**
     * The HTML that should be shown in the widget.
     *
     * @return string|\Illuminate\View\View
     */
    public function html()
    {
        $expiration = Carbon::now()->addMinutes($this->config('expiry', 0));

        $assets = Cache::remember('widgets::ImagesMissingAlt', $expiration, function() {
            return Asset::query()
                ->where('container', $this->config('container', 'assets'))
                ->whereNull('alt')
                ->whereIn('extension', $this->config('filetypes', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'bmp', 'tiff', 'svg']))
                ->orderBy('last_modified', 'desc')
                ->limit(100)
                ->get()
                ->toAugmentedArray();
        });

        $assets = collect($assets);

        return view('statamic-peak-tools::widgets.images-missing-alt', [
            'assets' => $assets->slice(0, $this->config('limit', 5)),
            'amount' => count($assets),
            'container' => AssetContainer::findByHandle($this->config('container', 'assets'))->title(),
        ]);
    }
}
