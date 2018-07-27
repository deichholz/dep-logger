<?php

namespace DepLogger\Singleton;

/**
 * Class Singleton
 * Manages a single instance of as many classes as extend this class.
 *
 * Shamefully pulled from http://php.net/language.oop5.late-static-bindings
 *
 */
abstract class AbstractSingleton
{

    private static $instances = array();

    /**
     * Validates singular status of new instance by checking the name of the currently requested class.
     *
     * SingletonAbstract constructor.
     */
    public function __construct()
    {
        $class = get_called_class();

        if ( array_key_exists( $class, self::$instances ) ) {
            trigger_error( "Tried to construct  a second instance of class \"$class\"", E_USER_WARNING );
        }

    }

    /**
     * Creates, stores and/or returns single existing instance of the requested class.
     *
     * @return mixed
     */
    public static function getInstance()
    {
        $class = get_called_class();

        if ( array_key_exists( $class, self::$instances ) === false ) {
            self::$instances[ $class ] = new $class();
        }

        return self::$instances[ $class ];
    }

}