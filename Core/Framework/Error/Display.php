<?php
namespace Core\Framework\Error;

/**
 * Message.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Display
{

    private $headline = 'Error';

    private $file = '';

    private $line = 0;

    private $message = '';

    private $public = true;

    private $trace = '';

    public function setHeadline($headline)
    {
        $this->headline = $headline;
        return $this;
    }

    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    public function setLine($line)
    {
        $this->line = $line;

        return $this;
    }

    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    public function setPublic($public)
    {
        $this->public = (bool) $public;

        return $this;
    }

    public function setTrace($trace)
    {
        $this->trace = $trace;

        return $this;
    }

    public function highLevel()
    {}

    public function lowLevel($details = false)
    {
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
            <body>
                <div class="container">
                    <h1>' . $this->headline . '</h1>
                    <p><strong>' . $this->message . '</strong></p>
                    <p>in ' . $this->file . ' (Line: ' . $this->line . ')</p>';

        if (!empty($this->trace)) {
            $html .= '<pre>' . $this->trace . '</pre>';
        }

        $html .= '
                </div>
            </body>
        </html>';

        return $html;
    }
}

