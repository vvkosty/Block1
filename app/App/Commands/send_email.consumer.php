<?php

declare(strict_types = 1);

namespace App\Commands;

use PhpAmqpLib\Connection\AMQPStreamConnection;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require dirname(__DIR__, 2) . '/config/bootstrap.php';

/** @var \App\Services\EmailService $emailService */

$connection = new AMQPStreamConnection(
    $_ENV['RABBITMQ_HOST'],
    $_ENV['RABBITMQ_PORT'],
    $_ENV['RABBITMQ_USER'],
    $_ENV['RABBITMQ_PASSWORD']
);
$channel = $connection->channel();

$channel->queue_declare('tag_updated', false, false, false, false);
$channel->queue_bind('tag_updated', 'email');

echo " [*] Waiting for messages. To exit press CTRL+C\n";

$callback = static function ($msg) use ($emailService) {
    $emailService->send((int)$msg->body);

    echo ' [x] Email sent to device ', $msg->body, "\n";
};

$channel->basic_consume('tag_updated', '', false, true, false, false, $callback);

while ($channel->is_open()) {
    $channel->wait();
}
