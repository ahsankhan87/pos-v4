<?php

if (!function_exists('format_quantity')) {
    /**
     * Format quantity to show cartons + pieces
     * 
     * @param float $pieces Total pieces
     * @param float|null $cartonSize Pieces per carton
     * @return string Formatted string like "9 ctns + 4 pcs" or "58 pcs"
     */
    function format_quantity($pieces, $cartonSize = null)
    {
        if (!$cartonSize || $cartonSize <= 1) {
            return number_format($pieces, 2) . ' pcs';
        }

        $cartons = floor($pieces / $cartonSize);
        $remaining = $pieces - ($cartons * $cartonSize);

        if ($remaining > 0) {
            return $cartons . ' ctns + ' . number_format($remaining, 2) . ' pcs';
        }

        return $cartons . ' ctns';
    }
}

if (!function_exists('pieces_to_cartons')) {
    /**
     * Convert pieces to cartons for display/input
     * 
     * @param float $pieces
     * @param float $cartonSize
     * @return float Number of cartons (decimal)
     */
    function pieces_to_cartons($pieces, $cartonSize)
    {
        if (!$cartonSize || $cartonSize <= 0) {
            return $pieces;
        }
        return $pieces / $cartonSize;
    }
}

if (!function_exists('cartons_to_pieces')) {
    /**
     * Convert cartons to pieces for storage
     * 
     * @param float $cartons
     * @param float $cartonSize
     * @return float Total pieces
     */
    function cartons_to_pieces($cartons, $cartonSize)
    {
        if (!$cartonSize || $cartonSize <= 0) {
            return $cartons;
        }
        return $cartons * $cartonSize;
    }
}
