<?php
// Booking model
class Booking {
    public static function estimateFare($baseFare, $kmRate, $distanceKm) {
        return $baseFare + ($kmRate * $distanceKm);
    }
}
