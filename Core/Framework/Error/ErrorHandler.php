<?php
namespace Core\Framework\Error;

use Core\Framework\Core;
use Psr\Log\LoggerInterface;

/**
 * ErrorHandler.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class ErrorHandler
{

    /**
     *
     * @var Core
     */
    private $core;

    /**
     *
     * @var \Throwable
     */
    private $throwable;

    /**
     *
     * @var string
     */
    private $result = '';

    /**
     *
     * @var array
     */
    private $log_to = [];

    /**
     *
     * @var bool
     */
    private $ajax = false;

    /**
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     *
     * @var bool
     */
    private $public = false;

    /**
     *
     * @var int
     */
    private $http_response_code = 500;

    /**
     *
     * @var bool
     */
    private $fatal = false;

    /**
     *
     * @var int
     */
    private $level = 0;

    /**
     * Constructor
     *
     * @param Core $core
     */
    public function __construct(\Throwable $throwable)
    {
        $this->throwable = $throwable;
    }

    /**
     * Sets the error to handle
     *
     * @param \Throwable $throwable
     */
    public function setThrowable(\Throwable $throwable)
    {
        $this->throwable = $throwable;
    }

    /**
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     *
     * @param bool $ajax
     */
    public function setAjax(bool $ajax)
    {
        $this->ajax = $ajax;
    }

    /**
     *
     * @param bool $public
     */
    public function setPublic(bool $public)
    {
        $this->public = $public;
    }

    /**
     *
     * @param int $http_response_code
     */
    public function setHttpResponseCode(int $http_response_code)
    {
        $this->http_response_code = $http_response_code;
    }

    /**
     *
     * @param int $level
     */
    public function setLevel(int $level)
    {
        $this->level = $level;
    }

    /**
     *
     * @param bool $fatal
     */
    public function setFatal(bool $fatal)
    {
        $this->fatal = $fatal;
    }

    /**
     *
     * @param int $level
     */
    public function handle()
    {
        switch ($this->level) {

            case 1:
                $this->result = $this->high();

            default:
            case 0:
                $this->result = $this->low();
        }

        if (isset($this->logger)) {
            $this->logger->error($this->throwable->getMessage() . ' (File: ' . $this->throwable->getFile() . ':' . $this->throwable->getLine(). ')');
        }

        http_response_code($this->http_response_code);

        if ($this->fatal) {
            die($this->result);
        }
    }

    private function low()
    {
        if (!$this->ajax) {

            $html = '
            <html>
                <head>
                    <title>Error</title>
                    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
                    <style type="text/css">
                       * { margin: 0; padding: 0; }
                        body { background-color: #aaa; color: #eee; font-family: Sans-Serif; }
                        h1 { margin: 3px 0 7px; }
                        p, pre { margin-bottom: 7px; }
                        pre { padding: 5px; border: 1px solid #333; max-height: 400px; overflow-y: scroll; background-color: #fff; display: block; }
                    </style>
                </head>
                <body>' . $html . '</body>
            </html>';
        }

        return $html;
    }

    private function getHeadline(): string
    {
        return '<h1>Error occured (' . $this->throwable->getCode() . ')</h1>';
    }

    private function getMessage(): string
    {
        return '<p><strong>' . $this->throwable->getMessage() . '</strong></p>';
    }

    private function getFileinfo(): string
    {
        return '<p>in ' . $this->getFile() . ' (Line: ' . $this->throwable->getLine() . ')</p>';
    }

    private function getTrace(): string
    {
        return '<pre>' . $this->throwable->getTrace() . '</pre>';
    }

    public function high()
    {

        // The basic data of exception
        $message = $this->throwable->getMessage();
        $file = $this->throwable->getFile();
        $line = $this->throwable->getLine();
        $trace = $this->throwable->getTraceAsString();

        $fatal = true;
        $clean_buffer = true;
        $log_error = true;
        $send_mail = true;
        $to_db = true;
        $public = ini_get('display_errors') ? true : false;

        // Set flags according to exception type
        switch (true) {

            // Override db logging on PDO exceptions
            case ($this->t instanceof \PDOException):

                $error_log = true;
                $send_mail = true;

                // Prevent db logging on PDOException!!!
                $to_db = false;
                break;

            case ($this->t instanceof \Core\Mailer\MailerException):
                $send_mail = false;
                break;
        }

        // Log error
        if (!empty($this->config->Core['error.log.use'])) {

            // Write to error log?
            if (!empty($error_log) || !empty($this->config->Core['error.log.modes.php'])) {
                error_log($message . ' (' . $file . ':' . $line . ')');
            }

            // Write to db error log? Take care of avoid flag (-1) due to PDOExceptions
            if (!empty($to_db) || !empty($this->config->Core['error.log.modes.db'])) {

                try {

                    $this->db->qb([
                        'method' => 'INSERT',
                        'table' => 'core_error_logs',
                        'fields' => [
                            'stamp',
                            'msg',
                            'trace',
                            'file',
                            'line'
                        ],
                        'params' => [
                            ':stamp' => time(),
                            ':msg' => $message,
                            ':trace' => $trace,
                            ':file' => $file,
                            ':line' => $line
                        ]
                    ]);

                    $this->db->execute();
                }
                catch (\Exception $e) {
                    // Handle this exception without trying to save it to db
                    $this->handleException($e, false, false, true, true, false);
                }
            }
        }

        // Ajax output
        if ($this->core->router->isAjax()) {

            // Stop output buffering by removing content
            ob_end_clean();

            // Clean current command stack
            $this->ajax->cleanCommandStack();

            $this->ajax->addCommand(new AlertCommand('Test'));

            $cmd = new ErrorCommand($this->createErrorHtml(true), '#core-message');

            $this->ajax->addCommand($cmd);

            // We have to send a 200 response code or jQueries ajax handler
            // recognizes the error and cancels result processing
            http_response_code(200);
            header('Content-type: application/json; charset=utf-8');

            echo $this->ajax->process();

            exit();
        }

        // Clean output buffer?
        if ($clean_buffer == true) {
            ob_clean();
        }

        if ($fatal == true) {
            $this->fatal();
        }

        return $this->createErrorHtml(false);
    }

    /**
     * Creates html error message
     */
    private function createErrorHtml($dismissable = false)
    {
        $this->error_html = '
        <div class="alert alert-danger' . ($dismissable == true ? ' alert-dismissible' : '') . '" role="alert" id="' . $this->id . '">';

        if ($dismissable == true) {
            $this->error_html .= '<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>';
        }

        switch (true) {
            case method_exists($this->t, 'getPublic') && $this->throwable->getPublic():
            case (bool) $this->user->getAdmin():
            case !empty($this->config->Core['error.display.skip_security_check']):
                $this->error_html .= '
                <h3 class="no-v-margin">' . $this->throwable->getMessage() . '<br>
                <small><strong>File:</strong> ' . $this->throwable->getFile() . ' (Line: ' . $this->throwable->getLine() . ')</small></h3>
                <strong>Trace</strong>
                <pre>' . $this->throwable->getTraceAsString() . '</pre>
                <strong>Router</strong>
                <pre>' . print_r($this->router->getStatus(), true) . '</pre>';

                break;

            default:
                $this->error_html .= '
                <h3 class="no-top-margin">Error</h3>
                <p>Sorry for that! Webmaster has been informed. Please try again later.</p>';
        }

        $this->error_html .= '
        </div>';

        return $this->error_html;
    }

    /**
     * Fatal error!
     */
    private function fatal()
    {
        // Clean buffer with all output done so far
        ob_clean();

        // Send 500 http status
        http_response_code(500);

        return '
        <html>
            <head>
                <title>Error</title>
                <link href="' . BASEURL . '/Cache/combined.css" rel="stylesheet">
                <style type="text/css">
                    * { margin: 0; padding: 0; }
                    body { background-color: #aaa; color: #eee; font-family: Sans-Serif; }
                    h1 { margin: 3px 0 7px; }
                    p, pre { margin-bottom: 7px; }
                    pre { padding: 5px; border: 1px solid #333; max-height: 400px; overflow-y: scroll; background-color: #fff; display: block; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="row">
                        <div class="col-sm-12">' . $this->createErrorHtml(false) . '</div>
                    </div>
                </div>
            </body>
        </html>';
    }
}