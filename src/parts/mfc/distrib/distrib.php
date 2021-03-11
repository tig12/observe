<?php
/******************************************************************************
    Utilities shared by several distrib classes
    
    @license    GPL
    @history    2021-03-10 18:34:03+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\parts\mfc\distrib;

class distrib {
    
        public static function lineHasWedding(&$line, &$columns, &$skipW) {
            return $line[$columns['W']] != $skipW;
        }
    
} // end class
