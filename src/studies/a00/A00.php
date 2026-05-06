<?php
/******************************************************************************
    Contains informations shared by several commands of this package.    

    @license    GPL
    @copyright  Thierry Graff
    
    @history    2026-05-05 22:38:56+02:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\studies\a00;

use observe\model\Study;
use observe\model\IStudy;
use observe\model\Studies;
use observe\app\ObserveException;

class A00 extends Study implements IStudy {
    
    //
    // Implementation of IStudy
    //
    
    public function __construct(string $studySlug) {
        
        parent::__construct($studySlug);
        
        if(!isset($this->config['raw-file-path'])){
            throw new ObserveException("Missing entry 'raw-file-path' in file {$this->config['__study-file__']}");
        }
        if(!is_file($this->config['raw-file-path'])){
            throw new ObserveException(
                "Unexisting file: {$this->config['raw-file-path']}\n"
                . "Check entry 'raw-file-path' in file {$this->config['__study-file__']}"
            );
        }
    }
    
    public function init($params = []): string {
        return ''; // nothing to do
    }
    public function import($params = []): string {
        return import::execute($this, $params);
    }
    public function control($params = []): string {
        return control::execute($this, $params);
    }
    
    //
    // Specific to a00
    //
    
} // end class
                                                                                                                               