<?php

namespace App\Console\Commands;

use App\Events\OrderEvent;
use Illuminate\Console\Command;
use longlang\phpkafka\Consumer\Consumer;
use longlang\phpkafka\Consumer\ConsumerConfig;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class KafkaConsume extends Command
{
    protected $signature = 'kafka:consume';
    protected $description = 'Start consuming Kafka messages';

    public function handle()
    {
        $this->info('Starting Kafka consumer...');

        try {
            $config = new ConsumerConfig();
            $config->setBroker('localhost:9092');
            $config->setBrokers(['localhost:9092']);
            $config->setTopic('facilities.orders.placed');
            $config->setGroupId('warehouse_group');
            $config->setClientId('warehouse_client_' . Str::random(8));
            $config->setGroupInstanceId('warehouse_instance_' . Str::random(8));
            $config->setAutoCommit(false);
            $config->setInterval(100);
            
            $consumer = new Consumer($config);
            $this->info('Consumer configured and ready');

            while (true) {
                try {
                    $message = $consumer->consume();
                    if ($message) {
                        $data = json_decode($message->getValue(), true);
                        $this->info('Message received: ' . json_encode($data));
                        
                        event(new OrderEvent('Refreshed'));
                        $consumer->ack($message);
                        
                        $this->info('Message processed successfully');
                    }
                    usleep(100000); // 100ms sleep instead of 1s
                } catch (\Exception $e) {
                    $this->error('Error consuming message: ' . $e->getMessage());
                    Log::error('Error consuming Kafka message: ' . $e->getMessage());
                    sleep(1);
                }
            }
        } catch (\Exception $e) {
            $this->error('Failed to start consumer: ' . $e->getMessage());
            Log::error('Failed to start Kafka consumer: ' . $e->getMessage());
        }
    }
}
