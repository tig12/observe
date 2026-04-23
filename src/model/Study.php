<?php
/******************************************************************************

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-12 00:42:19+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\model;

use observe\app\ObserveException;
use tiglib\filesystem\mkdir;
use tigeph\model\IAA;

class Study implements IStudy {
    
    /** Contents of the yaml command file located in config/  **/
    public readonly array $config;
    
    /** 
        @param  $studySlug Unique identifier of a study (entry "slug" in the yaml files in config/).
    **/
    public function __construct(string $studySlug){
        $this->config = Studies::getStudyConfig($studySlug);
        $this->checkStudyFile();
    }
    
    /**
        @return Error message if problem, empty message if ok.
    **/
    private function checkStudyFile(): void {
        $msg = '';
        if(!isset($this->config['slug'])){
            $msg .= "Missing entry \"slug\"\n";
        }
        //
        if(!isset($this->config['working-dir'])){
            $msg .= "Missing entry \"working-dir\"\n";
        }
        if(!is_dir($this->config['working-dir'])){
            mkdir::execute($this->config['working-dir']);
        }
        //
        if(!isset($this->config['out-dir'])){
            $msg .= "Missing entry \"out-dir\"\n";
        }
        if(!is_dir($this->config['out-dir'])){
            mkdir::execute($this->config['out-dir']);
        }
        //
        if(!isset($this->config['planets'])){
            $msg .= "Missing entry \"planets\"\n";
        }
        //
        if(!isset($this->config['dates'])){
            $msg .= "Missing entry \"dates\"\n";
        }
        if(($msg1 = IAA::checkCodes($this->config['planets'])) != ''){
            $msg .= $msg1;
        }
        //
        if($msg != ''){
            $msg = "INVALID STUDY FILE {$this->config['__study-file__']}\n" . trim($msg);
            throw new ObserveException($msg);
        }
    }
    
    /**
        Returns the array passed to header.html to build the navigation menu in output pages.
        The format is an associative array: [
            'href' => 'label',
        ]
    **/
    public function getNavigationArray(): array {
        $res = [
            '../../index.html'  => 'Observe home',
            'index.html'        => $this->config['output']['title'],
        ];
        foreach($this->config['dates'] as $date){
            $res["$date.html"] = ucfirst($date);
        }
        for($i=0; $i < count($this->config['dates']); $i++){
            for($j=$i+1; $j < count($this->config['dates']); $j++){
                $code = $this->config['dates'][$i] . '-' . $this->config['dates'][$j];
                $label = ucfirst($this->config['dates'][$i]) . ' - ' . ucfirst($this->config['dates'][$j]);
                $res ["$code.html"] =  $label;
            }
        }
        return $res;
    }
    
} // end class

