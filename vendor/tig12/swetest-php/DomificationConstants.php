<?php 
// Software released under the General Public License (version 2 or later), available at
// http://www.gnu.org/copyleft/gpl.html
/****************************************************************************************
        Computation of house cuspides
        
        @license    GPL
        @author     Thierry Graff
        @history 2002-11-10 : Creation
        @history 2002-11-25 : First attempt to write placidius()
        @history 2008-05-20 06:24 : Converted functions to a class
****************************************************************************************/
namespace swetest;

class DomificationConstants{
    
    /** 
        International Astrological Abbreviations for houses,
        as found in journal "Correlation" (vol 30.2 2016)
    **/
    const IAA = [
        DomificationConstants::ASC   => 'AS',
        DomificationConstants::DESC  => 'DS',
        DomificationConstants::MH    => 'MC',
        DomificationConstants::IC    => 'IC',
    ];
    
    //*********************************************************
    //                Domification systems
    //*********************************************************
    /** Constant designating the Placidus domification system. **/
    const PLACIDUS        = 'placidus';
    /** Constant designating the Koch domification system. **/
    const KOCH            = 'koch';
    /** Constant designating the Porphyrius domification system. **/
    const PORPHYRIUS      = 'porphyrius';
    /** Constant designating the Regiomontanus domification system. **/
    const REGIOMONTANUS   = 'regiomontanus';
    /** Constant designating the Regiomontanus domification system. **/
    const CAMPANUS        = 'campanus';
    /** Constant designating the Aequalis (equal houses) domification system. **/
    const AEQUELIS        = 'aequalis';
    /** Constant designating the "Whole sign" domification system. **/
    const WHOLE_SIGN      = 'whole-sign';
    
    
    //*********************************************************
    //                House cuspides
    //*********************************************************
    //
    // The values are used as 0-based array indexes
    //
    /** Constant designating cuspide of first house (synonym of {@link H1}). **/
    const ASC = '0';
    /** Constant designating cuspide of fourth house (Imum Coeli, synonym of {@link H4}). **/
    const IC = '3';
    /** Constant designating cuspide of seventh house (synonym of {@link H7}). **/
    const DESC = '6';
    /** Constant designating cuspide of tenth house (Mid Heaven, synonym of {@link H10}). **/
    const MH = '9';
    
    /** Constant designating cuspide of first house (synonym of {@link ASC}). **/
    const H1 = '0';
    /** Constant designating cuspide of second house. **/
    const H2 = '1';
    /** Constant designating cuspide of third house. **/
    const H3 = '2';
    /** Constant designating cuspide of fourth house (synonym of {@link IC}). **/
    const H4 = '3';
    /** Constant designating cuspide of fifth house. **/
    const H5 = '4';
    /** Constant designating cuspide of sixth house. **/
    const H6 = '5';
    /** Constant designating cuspide of seventh house (synonym of {@link DESC}). **/
    const H7 = '6';
    /** Constant designating cuspide of eighth house. **/
    const H8 = '7';
    /** Constant designating cuspide of ninth house. **/
    const H9 = '8';
    /** Constant designating cuspide of tenth house. **/
    const H10 = '9';
    /** Constant designating cuspide of eleventh house (synonym of {@link MC}). **/
    const H11 = '10';
    /** Constant designating cuspide of twelvth house. **/
    const H12 = '11';
    
    /** Array containing the codes of all houses **/
    public static $ALL_HOUSES = [
        DomificationConstants::H1,
        DomificationConstants::H2,
        DomificationConstants::H3,
        DomificationConstants::H4,
        DomificationConstants::H5,
        DomificationConstants::H6,
        DomificationConstants::H7,
        DomificationConstants::H8,
        DomificationConstants::H9,
        DomificationConstants::H10,
        DomificationConstants::H11,
        DomificationConstants::H12,
    ];
    
    /**
    Array containing the labels of houses
    @todo language dependant, move with i18n code
    **/
    public static $HOUSE_LABELS = [
        DomificationConstants::H1    => 'ASC',
        DomificationConstants::H2    => 'M2',
        DomificationConstants::H3    => 'M3',
        DomificationConstants::H4    => 'FC',
        DomificationConstants::H5    => 'M5',
        DomificationConstants::H6    => 'M6',
        DomificationConstants::H7    => 'DESC',
        DomificationConstants::H8    => 'M8',
        DomificationConstants::H9    => 'M9',
        DomificationConstants::H10   => 'MC',
        DomificationConstants::H11   => 'M11',
        DomificationConstants::H12   => 'M12',
    ];
    
    
}// end class

