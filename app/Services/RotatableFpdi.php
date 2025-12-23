<?php

namespace App\Services;

use setasign\Fpdi\Tcpdf\Fpdi;

class RotatableFpdi extends Fpdi
{
    protected $angle = 0;

    /**
     * Rotate the PDF output.
     * @param float $angle Angle in degrees
     * @param float $x Rotation center x (optional)
     * @param float $y Rotation center y (optional)
     */
    public function Rotate($angle, $x = -1, $y = -1)
    {
        if ($x == -1) $x = $this->x;
        if ($y == -1) $y = $this->y;

        if ($this->angle != 0) $this->_out('Q');
        $this->angle = $angle;

        if ($angle != 0) {
            $angle *= M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;

            // This is the PDF transformation matrix
            $this->_out(sprintf(
                'q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',
                $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy
            ));
        }
    }

    // Ensure we reset rotation at the end of the page
    public function _endpage()
    {
        if ($this->angle != 0) {
            $this->angle = 0;
            $this->_out('Q');
        }
        parent::_endpage();
    }
}