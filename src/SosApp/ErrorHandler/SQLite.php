<?php
/**
 * SOS PHP Framework
 *
 * @link      https://github.com/sos-solution/sos-http-application for the canonical source repository
 * @copyright Copyright (c) 2012-2018, SOS Solution Limited (Hong Kong). (https://www.sos-solution.com/)
 * @license   https://github.com/sos-solution/sos-http-application/blob/master/LICENSE.md New BSD License
 */

namespace SosApp\ErrorHandler;

/**
 * Class SQLite
 */
class SQLite extends Adapter {
    /**
     * File path
     * 
     * @var $path
    */    
    private $path = '';

    /**
     * Setup
     *
     * @param string $path
    */
    public function __construct($path) {
        $this->path = $path;
    }

    /**
     * Log function
     *
     * @param integer $errno
     * @param string $errstr
     * @param string $errfile
     * @param integer $errline
     * @param array $errtrace
     * @param string $errmsg
    */
    public function log($errno, $errstr, $errfile, $errline, $errtrace, $errmsg) {
        try { 
            // Create SQLite
            $filename = $this->path;
            $file_exists = file_exists($filename);
            $file_db = new \PDO('sqlite:' . $filename);
            $file_db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $file_db->exec("CREATE TABLE IF NOT EXISTS messages (id INTEGER PRIMARY KEY, datetime TEXT, type TEXT, msg TEXT, file TEXT, line INTEGER, trace TEXT, time INTEGER)");
            $file_db->exec("CREATE INDEX IF NOT EXISTS time ON messages(time)");

            // Prepare Variable
            $errtype = $this->errno_str($errno);
            $time = time();
            $datetime = date('Y-m-d H:i:s', $time);
            $trace = print_r($errtrace, TRUE);

            // Insert Query
            $stmt = $file_db->prepare('INSERT INTO messages (type, msg, file, line, trace, datetime, time) VALUES (:type, :msg, :file, :line, :trace, :datetime, :time)');
            $stmt->bindParam(':type', $errtype);
            $stmt->bindParam(':msg', $errstr);
            $stmt->bindParam(':file', $errfile);
            $stmt->bindParam(':line', $errline);
            $stmt->bindParam(':trace', $trace);
            $stmt->bindParam(':datetime', $datetime);
            $stmt->bindParam(':time', $time);

            $stmt->execute();

            $file_db = NULL;
            if ( $file_exists == FALSE ) {
                chmod($filename, 0666);
            }
        } catch (\Exception $e) {

            // If SQLite failure
            $errmsg = $this->logstring($errno, $errstr, $errfile, $errline);
            if ( error_log($errmsg, 3, COMPOSER_DIR . '/logs/error.log') == FALSE ) {
                // If save log failure 
            }
        }
    }
}