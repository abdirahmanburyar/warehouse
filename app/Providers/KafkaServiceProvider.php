<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use longlang\phpkafka\Producer\Producer;
use longlang\phpkafka\Producer\ProducerConfig;

class KafkaServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register producer service
        $this->app->singleton('kafka.producer', function ($app) {
            $config = new ProducerConfig();
            $config->setBrokers(['localhost:9092']);
            $config->setUpdateBrokers(false);
            $config->setAcks(-1);
            $config->setConnectTimeout(3);
            
            return new Producer($config);
        });

        // Register Kafka service facade
        $this->app->singleton('kafka', function ($app) {
            return new class($app) {
                protected $app;
                protected $producer;

                public function __construct($app)
                {
                    $this->app = $app;
                    try {
                        $this->producer = $app->make('kafka.producer');
                    } catch (\Exception $e) {
                        \Log::error('Kafka Producer Error: ' . $e->getMessage());
                    }
                }

                public function publishOrderPlaced($data)
                {
                    try {
                        if (!$this->producer) {
                            \Log::error('Kafka producer not initialized');
                            return;
                        }

                        $message = is_array($data) ? $data : ['message' => $data];
                        
                        \Log::info('Publishing to Kafka', [
                            'topic' => 'facilities.orders.placed',
                            'data' => $message
                        ]);
                        
                        $this->producer->send('facilities.orders.placed', json_encode($message));
                        
                        \Log::info('Successfully published to Kafka');
                    } catch (\Exception $e) {
                        \Log::error('Kafka Send Error', [
                            'error' => $e->getMessage(),
                            'data' => $data
                        ]);
                    }
                }
            };
        });
    }

    public function boot()
    {
        //
    }
}
