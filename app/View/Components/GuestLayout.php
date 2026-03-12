<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
{
    public function __construct(
        public ?string $pageTitle = null,
        public ?string $eyebrow = null,
        public ?string $heading = null,
        public ?string $subheading = null,
        public ?string $asideEyebrow = null,
        public ?string $asideHeading = null,
        public ?string $asideText = null,
        public array $asidePoints = [],
        public bool $showBackHome = true,
        public ?string $backUrl = null,
        public ?string $backLabel = null,
    ) {
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.guest');
    }
}
