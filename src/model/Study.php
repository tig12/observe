<?php
/******************************************************************************

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-12 00:42:19+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\model;

use observe\app\ObserveException;
use observe\commands\observed;
use observe\commands\expected;
use observe\commands\stats;
use observe\commands\dim2;
use observe\commands\output;
use observe\commands\dev;
use tiglib\filesystem\mkdir;
use tigeph\model\IAA;

abstract class Study implements IStudy {
    
    /** Contents of the yaml command file located in config/  **/
    public readonly array $config;
    
    /** 
        @param  $studySlug Unique identifier of a study (entry "slug" in the yaml files in config/).
    **/
    public function __construct(string $studySlug){
        $this->config = Studies::getStudyConfig($studySlug);
        $this->checkStudyFile();
    }
    
    public function observed($params = []): string {
        return observed::execute($this, $params);
    }
    public function expected($params = []): string {
        return expected::execute($this, $params);
    }
    public function stats($params = []): string {
        return stats::execute($this, $params);
    }
    public function dim2($params = []): string {
        return dim2::execute($this, $params);
    }
    public function output($params = []): string {
        return output::execute($this, $params);
    }
    public function dev($params = []): string {
        return dev::execute($this, $params);
    }
    
    /**
        @return Error message if problem, empty message if ok.
    **/
    private function checkStudyFile(): void {
        $msg = '';
        if(!isset($this->config['slug'])){
            $msg .= "Missing entry \"slug\"\n";
        }
// TODO check $this->config['slug'] preg_match 'a-z0-9-'
        //
        if(!isset($this->config['working-dir'])){
            $msg .= "Missing entry \"working-dir\"\n";
        }
        else{
            if(!is_dir($this->config['working-dir'])){
                mkdir::execute($this->config['working-dir']);
            }
        }
        //
        if(!isset($this->config['out-dir'])){
            $msg .= "Missing entry \"out-dir\"\n";
        }
        else{
            if(!is_dir($this->config['out-dir'])){
                mkdir::execute($this->config['out-dir']);
            }
        }
        //
        if(!isset($this->config['planets'])){
            $msg .= "Missing entry \"planets\"\n";
        }
        //
// TODO check $this->config['dates'] preg_match 'a-z0-9-'
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
        Computes an associative array
            key = study slug
            value = study title
        with studies different from current instance.
        @param  $includeTestStudies If true, sudies with an entry "is-test-study: true" are also returned
    **/
    public function getOtherStudiesTitles(bool $includeTestStudies = false): array {
        $allSlugs = Studies::getAllStudySlugs();
        $res = [];
        foreach($allSlugs as $slug){
            if($slug == $this->config['slug']){
                continue;
            }
            $studyConfig = Studies::getStudyConfig($slug);
            if($includeTestStudies === false){
                $isTest = $studyConfig['is-test-study'] ?? false;
                if($isTest){
                    continue;
                }
            }
            $res[$slug] = $studyConfig['output']['title'];
        }
        return $res;
    }
    
    /**
        Returns the array passed to header.html to build the navigation menu in output pages.
        The format is an associative array: [
            'href' => 'label',
        ]
        If a key starts by "__SEP__", the template interprets it as a separator and does not echo a link,
        but just copies the value.
    **/
    public function getNavigationArray(): array {
        $res = [
            '../../index.html'  => 'Observe output home',
            '../../help.html'   => 'Help to read this page',
            '__SEP__1'          => '<center><hr width="80%"></center>',
            'index.html'        => $this->config['output']['title'],
            'gallery.html'      => 'Gallery',
        ];
        foreach($this->config['dates'] as $date){
            $res["$date.html"] = ucfirst($date);
        }
        for($i=0; $i < count($this->config['dates']); $i++){
            for($j=$i+1; $j < count($this->config['dates']); $j++){
                $code = $this->config['dates'][$i] . '-' . $this->config['dates'][$j];
                $label = ucfirst($this->config['dates'][$i]) . ' - ' . ucfirst($this->config['dates'][$j]);
                $res["$code.html"] =  $label;
            }
        }
        $res['__SEP__2'] = '<center><hr width="80%"></center>';
        foreach($this->getOtherStudiesTitles() as $slug => $title){
            $res["../$slug/index.html"] = $title;
        }
        return $res;
    }
    
    /**
        Returns the root directory where the output is generated by command output.
    **/
    public function getOutputDirectory(): string {
        return $this->config['out-dir'];
    }
    
    /**
        Returns the directory where the images are generated by command output.
    **/
    public function getOutputImgDirectory(): string {
        return $this->config['out-dir'] . DS . 'img';
    }
    
    /**
        Returns the directory where the downloadable compressed csv files are generated by command output.
    **/
    public function getOutputCsvDirectory(): string {
        return $this->config['out-dir'] . DS . 'csv';
    }
    
    /**
        Returns the path to data.csv.bz2.
    **/
    public function getDatafile(): string {
        return $this->config['working-dir'] . DS . 'data.csv.bz2';
    }
    
    /**
        Returns the working directory containing the observed distributions of a study.
    **/
    public function getWorkingDirectory(): string {
        return $this->config['working-dir'];
    }
    
    /**
        Returns the directory containing the observed distributions of a study.
    **/
    public function getObservedDirectory(): string {
        return $this->config['working-dir'] . DS . 'observed';
    }
    
    /**
        Returns the directory containing the expected distributions of a study.
    **/
    public function getExpectedDirectory(): string {
        return $this->config['working-dir'] . DS . 'expected';
    }
    
    /**
        Returns the directory containing all the controls of a study.
    **/
    public function getControlsDirectory(): string {
        return $this->config['working-dir'] . DS . 'controls';
    }
    
    /**
        Returns the list of subdirectories of controls/ = the list of directories containing individual controls.
    **/
    public function getControlSubdirectories(): array {
        return glob($this->getControlsDirectory() . DS . 'control-*');
    }
    
    //
    //
    // Experimental code used only by package commands\a002
    // (to see if it would be pertinent to replace data.csv.bz2 by data.sqlite3)
    //
    //
    
    /**
        Returns the path to data.sqlite3.
    **/
    public function getSqliteDataPath(): string {
        return $this->config['working-dir'] . DS . 'data.sqlite3';
    }
    
    /** 
        Initializes data.sqlite3
        One table "date", with one field per date listed in current study's configuration file.
    **/
    public function initalizeSqliteData(): \PDO {
        $sqlite_path = $this->getSqliteDataPath();
        if(is_file($sqlite_path)){
            unlink($sqlite_path);
        }
        $sqlite = new \PDO('sqlite:' . $sqlite_path);
        // ex: create table date(mother character(10),father character(10),child character(10),wedding character(10))
        $sql = 'create table date(' . implode(' character(10),', $this->config['dates']) . ' character(10))';
        $sqlite->exec($sql);
        echo "Initialized $sqlite_path\n";
        return $sqlite;
    }

    
} // end class
