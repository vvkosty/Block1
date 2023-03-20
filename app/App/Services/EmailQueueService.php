<?php

declare(strict_types=1);

namespace App\Services;

use PhpAmqpLib\Channel\AbstractChannel;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class EmailQueueService
{

    private AMQPStreamConnection $connection;
    private AbstractChannel|AMQPChannel $channel;

    public function __construct()
    {
        $this->connection = new AMQPStreamConnection(
            $_ENV['RABBITMQ_HOST'],
            $_ENV['RABBITMQ_PORT'],
            $_ENV['RABBITMQ_USER'],
            $_ENV['RABBITMQ_PASSWORD']
        );
        $this->channel = $this->connection->channel();

        $this->channel->exchange_declare('email', 'direct');
        $this->channel->queue_declare('tag_updated', false, false, false, false);
        $this->channel->queue_bind('tag_updated', 'email');
    }

    public function sendTagUpdatedEvent(int $deviceId): void
    {
        $msg = new AMQPMessage($deviceId);
        $this->channel->basic_publish($msg, 'email');
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }
}
