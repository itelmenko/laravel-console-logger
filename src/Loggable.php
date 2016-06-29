<?php

namespace Illuminated\Console;

use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Illuminate\Support\Str;
use Illuminated\Console\Log\Formatter;
use Illuminated\Console\Log\HtmlFormatter;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait Loggable
{
    private $icLogger;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->initializeLogging();

        return parent::initialize($input, $output);
    }

    protected function initializeLogging()
    {
        $this->initializeErrorHandling();

        $class = get_class($this);
        $host = gethostname();
        $ip = gethostbyname($host);
        $this->logInfo("Command `{$class}` initialized.");
        $this->logInfo("Host: `{$host}` (`{$ip}`).");

        if (laravel_db_is_mysql()) {
            $dbIp = (string) laravel_db_mysql_variable('wsrep_node_address');
            $dbHost = (string) laravel_db_mysql_variable('hostname');
            $dbPort = (string) laravel_db_mysql_variable('port');
            $now = laravel_db_mysql_now();
            $this->logInfo("Database host: `{$dbHost}`, port: `{$dbPort}`, ip: `{$dbIp}`.");
            $this->logInfo("Database date: `{$now}`.");
        }
    }

    private function initializeErrorHandling()
    {
        app()->singleton('log.iclogger', function () {
            return new Logger('ICLogger', $this->getLogHandlers());
        });
        $this->icLogger = app('log.iclogger');

        app()->singleton(ExceptionHandlerContract::class, ExceptionHandler::class);
        app(ExceptionHandlerContract::class);
    }

    private function getLogHandlers()
    {
        $handlers = [];

        $rotatingFileHandler = new RotatingFileHandler($this->getLogPath(), 30);
        $rotatingFileHandler->setFilenameFormat('{date}', 'Y-m-d');
        $rotatingFileHandler->setFormatter(new Formatter());
        $handlers[] = $rotatingFileHandler;

        $mailerHandler = $this->getMailerHandler();
        if (!empty($mailerHandler)) {
            $handlers[] = $mailerHandler;
        }

        return $handlers;
    }

    protected function getMailerHandler()
    {
        $recipients = $this->getNotificationRecipients();
        if (empty($recipients)) {
            return false;
        }

        $subject = $this->getNotificationSubject();

        $mailerHandler = new NativeMailerHandler($recipients, $subject, 'no-reply-iclogger@example.com', Logger::NOTICE);
        $mailerHandler->setContentType('text/html');
        $mailerHandler->setFormatter(new HtmlFormatter());

        return $mailerHandler;
    }

    protected function logDebug($message, array $context = [])
    {
        return $this->icLogger->debug($message, $context);
    }

    protected function logInfo($message, array $context = [])
    {
        return $this->icLogger->info($message, $context);
    }

    protected function logNotice($message, array $context = [])
    {
        return $this->icLogger->notice($message, $context);
    }

    protected function logWarning($message, array $context = [])
    {
        return $this->icLogger->warning($message, $context);
    }

    protected function logError($message, array $context = [])
    {
        return $this->icLogger->error($message, $context);
    }

    protected function logCritical($message, array $context = [])
    {
        return $this->icLogger->critical($message, $context);
    }

    protected function logAlert($message, array $context = [])
    {
        return $this->icLogger->alert($message, $context);
    }

    protected function logEmergency($message, array $context = [])
    {
        return $this->icLogger->emergency($message, $context);
    }

    protected function getLogPath()
    {
        $name = Str::replaceFirst(':', '/', $this->getName());
        return storage_path("logs/{$name}/date.log");
    }

    protected function getNotificationRecipients()
    {
        return [
            // 'foo@example.com',
            // 'John Doe <john.doe@example.com>',
        ];
    }

    protected function getNotificationSubject()
    {
        return '[%level_name%] ICLogger notification';
    }

    protected function icLogger()
    {
        return $this->icLogger;
    }
}
