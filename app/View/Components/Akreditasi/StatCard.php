<?php
// app/View/Components/StatCard.php

namespace App\View\Components\Akreditasi;

use Illuminate\View\Component;

class StatCard extends Component
{
    public $title;
    public $value;
    public $description;
    public $icon;
    public $iconBg;
    public $gradient;
    public $gradientMode;
    public $type;

    /**
     * Create a new component instance.
     *
     * @param string $title
     * @param int|string $value
     * @param string $description
     * @param string $icon Bootstrap icon name (tanpa 'bi-')
     * @param string $iconBg Background color untuk icon (default: 'light')
     */
    // Preset gradient colors
    public static $gradients = [
        'purple-pink' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
        'green-teal' => 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)',
        'blue-purple' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'yellow-green' => 'linear-gradient(135deg, #8ebb0aff 0%, #c0c30dff 100%)',
        'orange-red' => 'linear-gradient(135deg, #ff6a00 0%, #ee0979 100%)',
        'blue-cyan' => 'linear-gradient(135deg, #2193b0 0%, #6dd5ed 100%)',
    ];

    public function __construct(
        $title,
        $value,
        $description,
        $icon = 'graph-up',
        $iconBg = 'light',
        $gradient = null,
        $type = 'default',
        $gradientMode = 'accent',
        $gradientPreset = null, // New parameter
    ) {
        $this->title = $title;
        $this->value = $value;
        $this->description = $description;
        $this->icon = $icon;
        $this->iconBg = $iconBg;

        // Use preset if provided
        if ($gradientPreset && isset(self::$gradients[$gradientPreset])) {
            $this->gradient = self::$gradients[$gradientPreset];
        } else {
            $this->gradient = $gradient;
        }
        $this->type = $this->gradient ? 'gradient' : $type;
        $this->gradientMode = $gradientMode;
    }

    public function render()
    {
        return view('components.akreditasi.stat-card');
    }
}
