<?php
/******************************************************************************
    International Astrological Abbreviations
    as found in journal "Correlation" (vol 30.2 2016)
    
    @license    GPL
    @history    2020-12-31 02:01:06+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\model\approach\astro;

class IAA {
    
    /**  **/
    const PLANETS = ['SO', 'MO', 'ME', 'VE', 'MA', 'JU', 'SA', 'UR', 'NE', 'PL', 'NN'];
    
    const PLANET_NAMES = [
        'SO' => 'Sun',
        'MO' => 'Moon',
        'ME' => 'Mercury',
        'VE' => 'Venus',
        'MA' => 'Mars',
        'JU' => 'Jupiter',
        'SA' => 'Saturn',
        'UR' => 'Uranus',
        'NE' => 'Neptune',
        'PL' => 'Pluto',
        'NN' => 'North node',
    ];
    
    
    /**  Match between constants used by tiglib/astro and IAA for planets **/
    const SWEPH_IAA = [
        SolarSystemConstants::SUN               => 'SO',
        SolarSystemConstants::MOON              => 'MO',
        SolarSystemConstants::MERCURY           => 'ME',
        SolarSystemConstants::VENUS             => 'VE',
        SolarSystemConstants::MARS              => 'MA',
        SolarSystemConstants::JUPITER           => 'JU',
        SolarSystemConstants::SATURN            => 'SA',
        SolarSystemConstants::URANUS            => 'UR',
        SolarSystemConstants::NEPTUNE           => 'NE',
        SolarSystemConstants::PLUTO             => 'PL',
        SolarSystemConstants::MEAN_LUNAR_NODE   => 'NN',
    ];

    const IAA_SWEPH = [
        'SO' => SolarSystemConstants::SUN,
        'MO' => SolarSystemConstants::MOON,
        'ME' => SolarSystemConstants::MERCURY,
        'VE' => SolarSystemConstants::VENUS,
        'MA' => SolarSystemConstants::MARS,
        'JU' => SolarSystemConstants::JUPITER,
        'SA' => SolarSystemConstants::SATURN,
        'UR' => SolarSystemConstants::URANUS,
        'NE' => SolarSystemConstants::NEPTUNE,
        'PL' => SolarSystemConstants::PLUTO, 
        'NN' => SolarSystemConstants::MEAN_LUNAR_NODE,
    ];

}// end class
