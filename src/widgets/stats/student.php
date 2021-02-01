<?php
/******************************************************************************
    T-test
    Input, to compare two sets of data.
    question: are they significantly different?
    
    N1  and N2 the two sample sizes.
    M1 and M2 the two mean values
    S1 and S2  the two standard deviations
    
    Step 1    N1xS1xS1  = W      Step 2  N2xS2xS2  = X      Step 3    N1+N2-2 = Y
    Step 3   (X+Y) / Z     = Z
    Step 4   Sqrt {Z x (1/N1  + 1/N2)}    = t
    
    @license    GPL
    @history    2021-02-01 03:32:45+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\widgets\stats;

use observe\Observe;
use observe\patterns\Command;
use observe\ObserveException;

class student implements Command {
    public static function execute($params=[]){
    }
    
}// end class
