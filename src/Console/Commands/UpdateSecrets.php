<?php

namespace Alavida\LaravelSecrets\Console\Commands;

use Aws\Laravel\AwsFacade;
use Aws\SecretsManager\SecretsManagerClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class UpdateSecrets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'secrets:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch secrets from AWS and save JSON of the secret list and values in the laravel cache directory';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // 1- list available secrets
        $client = AwsFacade::createClient('secretsManager');

        $secret_list = $client->listSecrets();

//        $secret_list = '
//            {
//              "SecretList":[
//                {
//                  "ARN":"arn:aws:secretsmanager:us-west-2:123456789012:secret:MyTestDatabaseSecret-a1b2c3",
//                  "Description":"My test database secret",
//                  "LastChangedDate":1.523477145729E9,
//                  "Name":"prod/MyTestDatabaseSecret",
//                  "SecretVersionsToStages":{
//                    "EXAMPLE2-90ab-cdef-fedc-ba987EXAMPLE":["AWSCURRENT"]
//                  }
//                },
//                {
//                  "ARN":"arn:aws:secretsmanager:us-west-2:123456789012:secret:AnotherDatabaseSecret-d4e5f6",
//                  "Description":"Another secret created for a different database",
//                  "LastChangedDate":1.523482025685E9,
//                  "Name":"dev/AnotherDatabaseSecret",
//                  "SecretVersionsToStages":{
//                    "EXAMPLE3-90ab-cdef-fedc-ba987EXAMPLE":["AWSCURRENT"]
//                  },
//                  "Tags": [
//                    {
//                       "Key": "country",
//                       "Value": "canada"
//                    }
//                  ]
//                }
//              ]
//            }
//        ';

        $json_secret_list = collect(json_decode($secret_list, true)['SecretList']);


        // 2- filter for secrets that match the filterPrefix (on name) or filterTag on tags
        $filtered_secrets = $this->filterSecrets($json_secret_list);


        // 3- grab the secret value
        $secrets = [];
        foreach ($filtered_secrets as $filtered_secret) {
            $secrets[] = $client->getSecretValue($filtered_secret['Name']);
        }


        // 4- save JSON of the secret list and values in the laravel cache directory
        $envVariables = '';
        foreach ($secrets as $secret) {
            $envVariables .= "{$secret['Name']}={$secret['SecretString']}\n";
        }
        Cache::forever('secrets', $envVariables);
    }

    /**
     * @param $json_secret_list
     * @return mixed
     */
    private function filterSecrets($json_secret_list)
    {
        $filterPrefix = config('laravelsecrets.filterPrefix');
        $filterTag = config('laravelsecrets.filterTag');

        $filtered_secrets = $json_secret_list->filter(function ($secret_list_item) use ($filterPrefix, $filterTag) {
            if (isset($secret_list_item['Tags'])) {
                $tags = $secret_list_item['Tags'];
                foreach ($tags as $tag) {
                    if ($tag['Value'] == $filterTag) {
                        return true;
                    }
                }
            }

            if (starts_with($secret_list_item['Name'], $filterPrefix)) {
                return true;
            }

            return false;
        });
        return $filtered_secrets;
    }
}
