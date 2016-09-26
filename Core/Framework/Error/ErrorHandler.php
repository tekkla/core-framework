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
     * @return string
     */
    public function handle()
    {
        $html = $this->createErrorHtml(false);

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

        $raw = $this->throwable->getMessage() . ' (File: ' . $this->throwable->getFile() . ':' . $this->throwable->getLine() . ')';

        if (isset($this->logger)) {
            $this->logger->error($raw);
        }
        else {
            error_log($raw);
        }

        if ($this->fatal) {
            die($html);
        }

        return $html;
    }

    private function getHeadline(): string
    {
        $code = !empty($this->throwable->getCode()) ? ' (' . $this->throwable->getCode() . ')' : '';

        return '<h1 class="no-v-margin">Error occured' . $code . '</h1>';
    }

    private function getMessage(): string
    {
        return '<p>' . $this->throwable->getMessage() . '</p>';
    }

    private function getFileinfo(): string
    {
        return '<p>in ' . $this->throwable->getFile() . ' (Line: ' . $this->throwable->getLine() . ')</p>';
    }

    private function getTrace(): string
    {
        return '<pre>' . $this->throwable->getTraceAsString() . '</pre>';
    }

    /**
     * Creates html error message
     */
    private function createErrorHtml(bool $dismissable = false)
    {
        $html = '
        <div class="alert alert-danger' . ($dismissable == true ? ' alert-dismissible' : '') . '" role="alert" id="core-error-' . $this->throwable->getCode() . '">';

        if ($dismissable) {
            $html .= '<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>';
        }

        switch (true) {
            case $this->public:
                $html .= $this->getHeadline();
                $html .= $this->getMessage();
                $html .= $this->getFileinfo();
                $html .= $this->getTrace();

                break;

            default:
                $html .= '
                <h3 class="no-top-margin">Error</h3>
                <p>Sorry for that! Webmaster has been informed. Please try again later.</p>';
        }

        $html .= '
        </div>';

        return $html;
    }
}