<?php
namespace Foo\Bar;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use OCP\ILogger;

class Foo {
    public function __construct(private LoggerInterface $logger) {}

    public function logGenerically(): void {
        $this->logger->log(LogLevel::ALERT, 'alarm!');
        $this->logger->log(ILogger::ERROR, 'warn!');
        $this->logger->log(ILogger::DEBUG, 'debug!');
    }
}
?>
-----
<?php
namespace Foo\Bar;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use OCP\ILogger;

class Foo {
    public function __construct(private LoggerInterface $logger) {}

    public function logGenerically(): void {
        $this->logger->log(LogLevel::ALERT, 'alarm!');
        $this->logger->log(\Psr\Log\LogLevel::ERROR, 'warn!');
        $this->logger->log(\Psr\Log\LogLevel::DEBUG, 'debug!');
    }
}
?>
