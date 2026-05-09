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
        self::computeAllStudyConfigs();
        return array_keys(self::$studyConfigs);
    }
    
    /**
        Returns the contents of a yaml study file.
    **/
    public static function getStudyConfig(string $studySlug): array {
        self::computeAllStudyConfigs();
        return self::$studyConfigs[$studySlug];
    }

    /**
        Fills self::$studyConfigs with all available study configs (here, "study config" = content of a yaml study file).
        Completes each element of self::$studyConfigs with a key "__study-file__".
        The slugs come from yaml files stored in study file, in config/
    **/
    private static function computeAllStudyConfigs(): void {
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
    
} // end class
