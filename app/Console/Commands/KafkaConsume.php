<?php

namespace App\Console\Commands;

use App\Events\OrderEvent;
use App\Models\Order;
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
        try {
            $config = new ConsumerConfig();
            $config->setBroker('warehouse.psivista.com:9092');
            $config->setBrokers(['warehouse.psivista.com:9092']);
            $config->setTopic('facilities.orders.updated');
            $config->setGroupId('facilities_group');
            $config->setClientId('facilities_client_' . Str::random(8));
            $config->setGroupInstanceId('facilities_instance_' . Str::random(8));
            $config->setAutoCommit(false);
            
            $consumer = new Consumer($config);
            Log::info('Kafka consumer started', [
                'topic' => 'facilities.orders.updated',
                'group' => 'facilities_group'
            ]);

            while (true) {
                try {
                    $message = $consumer->consume();
                    if ($message) {
                        $rawValue = $message->getValue();
                        $data = json_decode($rawValue, true);
                        event(new OrderEvent('Refreshed'));
                        
                        $consumer->ack($message);
                    }
                    usleep(100000); // 100ms sleep
                } catch (\Exception $e) {
                    sleep(1);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to start Kafka consumer: ' . $e->getMessage());
        }
    }
}
