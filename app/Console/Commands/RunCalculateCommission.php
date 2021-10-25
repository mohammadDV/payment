<?php

namespace App\Console\Commands;

use App\Services\CommissionService;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use League\Flysystem\Config;

class RunCalculateCommission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:service';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Running calculate commission service';

    private $service;
    private $currencies = [];
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CommissionService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->get_currencies();
        $this->service->set_currencies($this->currencies);
        $result = $this->service->process();
        foreach ($result as $item){
            echo round_cm($item) . PHP_EOL;
        }
    }
    /**
     * Get rate of currencies from Api.
     *
     * @return fill currencies property
     */
    private function get_currencies(){
        $client     = new Client();
        $request    = new \GuzzleHttp\Psr7\Request('GET', "http://api.exchangeratesapi.io/v1/latest?access_key=" . Config("payment.access_key"));
        $promise    = $client->sendAsync($request)->then(function ($response) {
            $result = json_decode($response->getBody(),true);
            if ($result["success"] === true){
                $this->currencies = !empty($result["rates"]) ? $result["rates"] : [];
            }
        });
        $promise->wait();
    }
}
