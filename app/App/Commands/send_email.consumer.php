<?php

declare(strict_types=1);

namespace App\Commands;

use App\Core\App;
use Error;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;
use Monolog\Logger;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require dirname(__DIR__, 2) . '/config/bootstrap.php';

/** @var App $app */

$connection = new AMQPStreamConnection(
    $_ENV['RABBITMQ_HOST'],
    $_ENV['RABBITMQ_PORT'],
    $_ENV['RABBITMQ_USER'],
    $_ENV['RABBITMQ_PASSWORD']
);
$channel = $connection->channel();

$channel->exchange_declare('email', 'direct');
$channel->queue_declare('tag_updated', false, false, false, false);
$channel->queue_bind('tag_updated', 'email');

echo " [*] Waiting for messages. To exit press CTRL+C\n";

$log = new Logger('app');
$log->pushHandler(new RotatingFileHandler('logs/app.log', 2, Level::Debug));

$callback = static function (AMQPMessage $msg) use ($app, $log) {
    try {
        $app->getEmailService()->send((int)$msg->body);
    } catch (Error $e) {
        $log->error($e->getMessage());
        echo ' [x] Email was not send to device ', $msg->body, "\n";
        $msg->nack(true);

        return;
    }

    echo ' [x] Email sent to device ', $msg->body, "\n";
    $msg->ack();
};

$channel->basic_consume('tag_updated', '', false, false, false, false, $callback);

while ($channel->is_open()) {
    $channel->wait();
}

$channel->close();
$connection->close();
