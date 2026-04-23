<?php
/******************************************************************************
    
    Contains static code related to studies.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    
    @history    2026-03-11 14:59:09+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\model;

use tiglib\filesystem\globRecursive;

class Studies {
    
    /** 
        Associative array
            Keys: slugs of the studies
            Values: Contents of the corresponding yaml files located in config/
    **/
    private static array $studyConfigs = [];
    
    /**
        Returns the slugs of all available studies.
    **/
    public static function getAllStudySlugs(): array {
        self::computeStudyConfigs();
        return array_keys(self::$studyConfigs);
    }
    
    /**
        Returns the contents of a yaml study file.
    **/
    public static function getStudyConfig(string $studySlug): array {
        self::computeStudyConfigs();
        return self::$studyConfigs[$studySlug];
    }

    /**
        Fills self::$studyConfigs with all available study configs (here, "study config" = content of a yaml study file).
        Completes each element of self::$studyConfigs with a key "__study-file__".
        The slugs come from yaml files stored in study file, in config/
    **/
    private static function computeStudyConfigs(): void {
        if(count(self::$studyConfigs) != 0){
            return; // already computed
        }
        $files = globRecursive::compute('config/*.yml');
        foreach($files as $file){
            $studyConfig = yaml_parse_file($file);
            // At this step, doesn't check if the yaml file is valid
            if(isset($studyConfig['slug'])){
                $slug = $studyConfig['slug'];
                //
                // HERE store the contents of the yaml in self::$studyConfigs
                // (with a supplementary entry: '__study-file__')
                //
                self::$studyConfigs[$slug] = [...$studyConfig, ...['__study-file__' => $file]];
            }
        }
    }
    
    /**
        Returns the fqcn (fully qualified class name) of a class implementing IStudy.
        
        BASED ON A CONVENTION: the namespace relative to a study and the name of the class implementing IStudy is built from the class slug.
        For example, for study slug death-fr:
        - the namespace is observe\studies\death_fr
        - the class implementing Istudy is observe\studies\death_fr\Death_fr
    **/
    public static function getStudyClasspath(string $studySlug): string {
        $namespace = self::getStudyNamespace($studySlug);
        $classname = ucfirst($namespace);               // ex: Death_fr
        return 'observe\\studies\\' . $namespace . '\\' . $classname; // ex: observe\studies\death_fr\Death_fr
    }

    /**
        Returns the namespace containing a class. Not fully qualified.
    **/
    public static function getStudyNamespace(string $studySlug): string {
        // WARNING: CONVENTION THAT NEEDS TO BE RESPECTED WHEN CREATING A NEW STUDY
        return str_replace('-', '_', $studySlug); // ex: death_fr
    }
    
    /**
        Returns the directory containing the observed distributions of a subgroup of a given split of a study.
    **/
    public static function getObservedDirectory(array &$studyConfig, string $split, string $subgroup): string {
        return $studyConfig['working-dir'] . DS . 'split-' . $split . DS . $subgroup . DS . 'observed';
    }
    
    /**
        Returns the directory containing the expected distributions of a subgroup of a given split of a study.
    **/
    public static function getExpectedDirectory(array &$studyConfig, string $split, string $subgroup): string {
        return $studyConfig['working-dir'] . DS . 'split-' . $split . DS . $subgroup . DS . 'expected';
    }
    
    /**
        Returns the directory containing all the controls of a study.
    **/
    public static function getControlsDirectory(array &$studyConfig): string {
        return $studyConfig['working-dir'] . DS . 'controls';
    }
    
    /**
        Returns the directory containing the intermediate files of a given split of a study.
    **/
    public static function getSplitDirectory(array &$studyConfig, string $split): string {
        return $studyConfig['working-dir'] . DS . 'split-' . $split;
    }
    
    /**
        Returns the names of the subgroups of a split = the names of the directories of a split
        ex:
            var/studies/death-fr/split-age/
                ├── 01--0-2days
                ├── 02--2days-2months
                ├── 03--2months-6months
                ├── 04--6months-2years
                ├── 05--2years-5years
                ├── 06--5years-20years
                ├── 07--20years-50years
                ├── 08--50years-90years
                └── 09--90years-200years
    **/
    public static function getSplitSubgroups(array &$studyConfig, string $split): array {
        return self::getStudyClasspath($studyConfig['slug'])::getSplitSubgroups($split);
    }
    
    /**
        Returns the directory containing the intermediate files of a given split of a study.
    **/
    public static function getSubgroupDirectory(array &$studyConfig, string $split, string $subgroup): string {
        return self::getSplitDirectory($studyConfig, $split) . DS . $subgroup;
    }
    
    /**
        Returns an array containing the dates of a study, different from $dateName.
        @param      $dateName String like 'birth', 'death', 'mother' etc.
    **/
    ///////////// NOT USED YET /////////////////
    public static function otherDates1(array &$studyConfig, string $dateName): array {
        $res = [];
        foreach($studyConfig['dates'] as $date){
            if($date != $dateName){
                $res[] = $date;
            }
        }
        return $res;
    }
    
    /**
        Returns an array containing the possible combinations of 2 dates of a study, different from $dateName.
        @param  $dateName String like 'mother-father'
        @return     Ex: ['child-father', 'child-mother', 'child-wedding', 'mother-wedding', 'father-wedding']
    **/
    ///////////// NOT USED YET /////////////////
    public static function otherDates2(array &$studyConfig, string $dateName) {
        // TODO Implement
        return [];
    }
    
} // end class
