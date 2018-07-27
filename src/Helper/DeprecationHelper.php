<?php

namespace DepLogger\Helper;

use DepLogger\Singleton\AbstractSingleton;
use Monolog\Logger;


/**
 * Class DeprecationHelper
 * Logs instances where deprecated function or method has been referenced.
 *
 * Note: only tested in PHP 5.6. Found warnings about behavior of debug_backtrace() in different versions of PHP.
 */
class DeprecationHelper extends AbstractSingleton
{

    /**
     * @var Logger $logger
     */
    private $logger;

    /**
     * @var String[] $loggedErrors
     */
    private $loggedErrors;

    /**
     * Gets singleton instance.
     * This is a convenience method to make it easy to find this class.
     *
     * @return DeprecationHelper
     */
    public static function getInstance()
    {
        return parent::getInstance();
    }

    /**
     * Logs an instance of a call to a deprecated function or method using information gleaned
     *
     * @param null $extraMessage
     * @return $this
     */
    public function logIt( $extraMessage = null )
    {
        $logger = $this->getLogger();
        if ( isset( $logger ) ) {
            $backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 3 );
            $stackData = $this->parseStack( $backtrace );

            $message = sprintf(
                'Deprecated call to %s:%d -> %s from %s:%d' . $extraMessage ? ' (%s)' : '',
                $stackData[ 'calledFile' ],
                $stackData[ 'calledLine' ],
                $stackData[ 'calledFunc' ],
                $stackData[ 'callerFile' ],
                $stackData[ 'callerLine' ],
                $extraMessage
            );

            if ( $this->isMessageUnique( $message ) ) {
                $logger->info( $message );
            }
        }

        return $this;
    }

    /**
     * @return Logger
     */
    private function getLogger()
    {
        return $this->logger;
    }

    /**
     * Take results of php's get_call_stack() function and identify caller and called line info.
     *
     * @param $stack
     * @return array
     */
    private function parseStack( $stack )
    {

        $returnFields = ['calledFile', 'calledLine', 'calledFunc', 'callerFile', 'callerLine', 'callerFunc'];
        $returnData = array_fill_keys( $returnFields, 'UNDEFINED' );

        // map data from stack to caller / called set
        if ( isset( $stack[ 0 ] ) ) {
            $returnData[ 'calledFile' ] = isset( $stack[ 0 ][ 'file' ] ) ? $stack[ 0 ][ 'file' ] : '';
            $returnData[ 'calledLine' ] = isset( $stack[ 0 ][ 'line' ] ) ? $stack[ 0 ][ 'line' ] : 0;
            $returnData[ 'calledFunc' ] = isset( $stack[ 0 ][ 'function' ] ) ? $stack[ 0 ][ 'function' ] : '';
        }
        if ( isset( $stack[ 1 ] ) ) {
            $returnData[ 'callerFile' ] = isset( $stack[ 1 ][ 'file' ] ) ? $stack[ 1 ][ 'file' ] : '';
            $returnData[ 'callerLine' ] = isset( $stack[ 1 ][ 'line' ] ) ? $stack[ 1 ][ 'line' ] : 0;
            $returnData[ 'callerFunc' ] = isset( $stack[ 1 ][ 'function' ] ) ? $stack[ 1 ][ 'function' ] : '';
        }

        // compensate for funky mix up in data that happens a lot. ??
        if ( isset( $stack[ 1 ][ 'function' ] ) && $stack[ 0 ][ 'function' ] === 'logIt' ) {
            $returnData[ 'calledFunc' ] = isset( $stack[ 1 ][ 'function' ] ) ? $stack[ 1 ][ 'function' ] : '';
            $returnData[ 'callerFunc' ] = '';
        }

        return $returnData;
    }

    /**
     * Verifies that message has not already been logged
     *
     * @param $message
     * @return bool
     */
    private function isMessageUnique( $message )
    {
        $unique = in_array( $message, $this->loggedErrors );
        if ( $unique ) {
            $this->loggedErrors[] = $message;
        }

        return $unique;
    }

    /**
     * @param Logger $logger
     * @return $this
     */
    public function setLogger( Logger $logger )
    {
        $this->logger = $logger;

        return $this;
    }

}