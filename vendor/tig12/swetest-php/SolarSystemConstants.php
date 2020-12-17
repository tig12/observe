<?php
// Software released under the General Public License (version 2 or later), available at
// http://www.gnu.org/copyleft/gpl.html
/********************************************************************************
    Class containing solar system constants.
    
    @license  GPL
    @author   Thierry Graff
    @history 2007.02.26 : PHP5 port from java, from jephem.astro.solarsystem.SolarSystemC.java
    @history 2000.12.16 : creation from SolarSystem.java
*********************************************************************************/
namespace swetest;

class SolarSystemConstants{

  //********* Static initializer *********
  /** Static initializer, to be called before using the class */
  public static function init(){
      SolarSystemConstants::$ALL_BODIES = array_merge(
      SolarSystemConstants::$MAIN_PLANETS,
      SolarSystemConstants::$MAIN_ASTEROIDS,
      SolarSystemConstants::$MAIN_CENTAUR_ASTEROIDS
    );
  }
  
  //********* Heavenly bodies -- called "planets" *********
  //
  /** Constant designating the Sun. */                  const SUN       = 'sun';
  /** Constant designating Mercury. */                  const MERCURY   = 'mercury';
  /** Constant designating Venus. */                    const VENUS     = 'venus';
  /** Constant designating the Earth. */                const EARTH     = 'earth';
  /** Constant designating the Moon. */                 const MOON      = 'moon';
  /** Constant designating Earth-Moon barycenter. */    const EMB       = 'emb';
  /** Constant designating Mars. */                     const MARS      = 'mars';
  /** Constant designating Jupiter. */                  const JUPITER   = 'jupiter';
  /** Constant designating Saturn. */                   const SATURN    = 'saturn';
  /** Constant designating Uranus. */                   const URANUS    = 'uranus';
  /** Constant designating Neptune. */                  const NEPTUNE   = 'neptune';
  /** Constant designating Pluto. */                    const PLUTO     = 'pluto';
  //
  /** Constant designating asteroid Ceres. */           const CERES     = 'ceres';
  /** Constant designating asteroid  Pallas. */         const PALLAS    = 'pallas';
  /** Constant designating asteroid  Vesta. */          const VESTA     = 'vesta';
  /** Constant designating asteroid  Juno. */           const JUNO      = 'juno';
  //
  /** Constant designating asteroid  Pholus. */         const PHOLUS    = 'pholus';
  /** Constant designating asteroid  Chiron. */         const CHIRON    = 'chiron';
  /** Constant designating asteroid  Kronos. */         const KRONOS    = 'kronos';
  //
  /** Constant designating asteroid  Cupido. */         const CUPIDO    = 'cupido';
  /** Constant designating asteroid  Hades. */          const HADES     = 'hades';
  /** Constant designating asteroid  Zeus. */           const ZEUS      = 'zeus';
  
  /** Array containing the codes of all the planets */
  public static $MAIN_PLANETS = [
    SolarSystemConstants::SUN,
    SolarSystemConstants::MOON,
    SolarSystemConstants::MERCURY,
    SolarSystemConstants::VENUS,
    SolarSystemConstants::EARTH,
    SolarSystemConstants::MARS,
    SolarSystemConstants::JUPITER,
    SolarSystemConstants::SATURN,
    SolarSystemConstants::URANUS,
    SolarSystemConstants::NEPTUNE,
    SolarSystemConstants::PLUTO
  ];
  
  /** Array containing the codes of gazeous planets */
  public static $GAZEOUS_PLANETS = [
    SolarSystemConstants::JUPITER,
    SolarSystemConstants::SATURN,
    SolarSystemConstants::URANUS,
    SolarSystemConstants::NEPTUNE
  ];
  
  /** Array containing the codes of the 4 main asteroids */
  public static $MAIN_ASTEROIDS = [
    SolarSystemConstants::CERES,
    SolarSystemConstants::PALLAS,
    SolarSystemConstants::VESTA,
    SolarSystemConstants::JUNO,
  ];
  
  /** Array containing the codes of the main "centaure" asteroids */
  public static $MAIN_CENTAUR_ASTEROIDS = [
    SolarSystemConstants::CHIRON,
    SolarSystemConstants::PHOLUS,
  ];
  
  /** Array containing the codes of all bodies known by the program */
  public static $ALL_BODIES;
  
  
  //********* General parameters *********
  //
  /** Nutation. */
  const NUTATION = 'nutation';
  /** Obliquity of the ecliptic. */
  const OBLIQUITY = 'obliquity';
  /** Mean obliquity for t = 1900.0 (23.4522944) */
  const E0_1900 = 23.4522944;
  /**  Mean obliquity for  t = 1950.0 (23.4457889) */
  const E0_1950 = 23.4457889;
  /** Mean obliquity for  t = 2000.0 (23.439292). */
  const E0_2000 = 23.439292;

  //********* Orbital parameters of particular bodies *********
  /** Constant designating the moon's mean node (north) */
  const MEAN_LUNAR_NODE = 'mean-lunar-node';
  /** Constant designating the moon's true node (north) */
  const TRUE_LUNAR_NODE = 'true-lunar-node';
  /** Constant designating the moon's mean apogee */
  const MEAN_LUNAR_APOGEE = 'mean-lunar-apogee'; // black moon
  /** Constant designating the moon's oscultating apogee */
  const OSCULTATING_LUNAR_APOGEE = 'oscultating-lunar-apogee'; // true black moon ?

  //********* phases of the moon *********
  /** Constant designating the new moon */
  const NEW_MOON = 'new-moon';
  /** Constant designating the full moon */
  const FULL_MOON = 'full-moon';
  /** Constant designating the moon's first quarter */
  const FIRST_MOON_QUARTER = 'first-moon-quarter';
  /** Constant designating the moon's last quarter */
  const LAST_MOON_QUARTER = 'last-moon-quarter';

}//end class
