<?php
// News model
class News {
    public static function getLatest() {
        return [
            [
                'title' => 'SmartMove Partners with CAASL & PUCSL to Test eVTOL Flying Taxis & EV Transport in Colombo',
                'date' => 'August 14, 2026',
                'image' => '../img/news_ev.jpg',
                'excerpt' => 'Initiating Sri Lanka\'s first regulatory sandbox pilot for electric Vertical Take-Off & Landing passenger drones and commercial EV fleets.',
                'link' => '#'
            ],
            [
                'title' => 'SmartMove E-Mobility Initiative: Converting Over 5,000 Three-Wheelers to Zero-Emission Power',
                'date' => 'May 27, 2026',
                'image' => '../img/threeweel/threeweel1.jpg',
                'excerpt' => 'Government and UNDP pilot program transitions urban 3-wheelers to high-efficiency electric motors, slashing driver fuel costs.',
                'link' => '#'
            ],
            [
                'title' => 'SmartMove Launches Elexi Vehicle Ownership Program for Top-Rated Driver Partners',
                'date' => 'March 28, 2026',
                'image' => '../img/driver_partner.jpg',
                'excerpt' => 'Providing flexible monthly financing for electric passenger vehicles and tuk-tuks to gain full vehicle ownership.',
                'link' => '#'
            ]
        ];
    }
}
