<?php
// app/View/Components/Pelaporan/PelaporanCard.php

namespace App\View\Components\Pelaporan;

use Illuminate\View\Component;

class PelaporanCard extends Component
{
    public $assignment;
    public $type;
    public $canReport;
    public $isReported;
    public $reportedAt;

    public function __construct($assignment, $type, $canReport = false, $isReported = false, $reportedAt = null)
    {
        $this->assignment = $assignment;
        $this->type = $type;
        $this->canReport = $canReport;
        $this->isReported = $isReported;
        $this->reportedAt = $reportedAt;
    }

    public function render()
    {
        return view('components.pelaporan.pelaporan-card');
    }
}
