<?php

namespace FlyCorp\SantanderBillet\providers;

use GuzzleHttp\Client;

class Santander
{
    protected $client, $options;

    public function __construct()
    {
        $certificatePath = storage_path(config('santander_billet.integrations.certificate_path'));

        $this->client = new Client([
            'base_uri' => config('santander_billet.integrations.host'),
            'cert' => [$certificatePath, config('santander_billet.integrations.certificate_auth')],
            'curl' => [CURLOPT_SSLCERTTYPE => 'P12'],
        ]);

        $this->options = [
            'form_params' => [
                'client_id' => config('santander_billet.integrations.client_id'),
                'client_secret' => config('santander_billet.integrations.client_secret'),
                'grant_type' => 'client_credentials'
            ]
        ];
    }

    private function retrieveToken()
    {
        $response  = $this->client->post("auth/oauth/v2/token", $this->options);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception("Request failed with status {$response->getStatusCode()}");
        }

        $body = $response->getBody();
        $data = json_decode($body, true);

        return $data;
    }

    private function token()
    {
        $cached = cache()->get('SANTANDER_BILLET_RESPONSE');

        return $cached
        ? $cached
        :cache()->remember('SANTANDER_BILLET_RESPONSE', now()->addSeconds(900), function(){
            return self::retrieveToken();
        });
    }

    private function authorizeHeaders()
    {
        $tokenData = self::token();

        return [
            "X-Application-Key" => config('santander_billet.integrations.client_id'),
            "Authorization" => "Bearer {$tokenData['access_token']}",
        ];
    }

    public function createWorkspace($body)
    {
        $options = [
            "headers" => self::authorizeHeaders(),
            "json" => $body
        ];

        $response  = $this->client->post("collection_bill_management/v2/workspaces", $options);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception("Request failed with status {$response->getStatusCode()}");
        }

        $body = $response->getBody();
        $data = json_decode($body, true);

        return $data;
    }

    public function searchWorkspace($workspaceId = null)
    {
        $options = [
            "headers" => self::authorizeHeaders()
        ];

        $response  = $this->client->get($workspaceId ? "collection_bill_management/v2/workspaces/{$workspaceId}" : "collection_bill_management/v2/workspaces", $options);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception("Request failed with status {$response->getStatusCode()}");
        }

        $body = $response->getBody();
        $data = json_decode($body, true);

        return $data;
    }

    public function deleteWorkspace($workspaceId)
    {
        $options = [
            "headers" => self::authorizeHeaders()
        ];

        $response  = $this->client->delete("collection_bill_management/v2/workspaces/{$workspaceId}", $options);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception("Request failed with status {$response->getStatusCode()}");
        }

        $body = $response->getBody();
        $data = json_decode($body, true);

        return $data;
    }

    public function updateWorkspace($workspaceId, $body = [])
    {
        $options = [
            "headers" => self::authorizeHeaders(),
            "json" => $body
        ];

        $response  = $this->client->patch("collection_bill_management/v2/workspaces/{$workspaceId}", $options);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception("Request failed with status {$response->getStatusCode()}");
        }

        $body = $response->getBody();
        $data = json_decode($body, true);

        return $data;
    }

    public function registerBill($workspaceId, array $body = [])
    {
        $options = [
            "headers" => self::authorizeHeaders(),
            "json" => $body
        ];

        $response  = $this->client->post("collection_bill_management/v2/workspaces/{$workspaceId}/bank_slips", $options);

        if(!in_array($response->getStatusCode(), [200, 201, 202, 203, 204, 205, 206])){
            throw new \Exception("Request failed with status {$response->getStatusCode()}");
        }

        $body = $response->getBody();
        $data = json_decode($body, true);

        return $data;
    }

    public function updateBillInstructions($workspaceId, array $body = [])
    {
        $options = [
            "headers" => self::authorizeHeaders(),
            "json" => $body
        ];

        $response  = $this->client->patch("collection_bill_management/v2/workspaces/{$workspaceId}/bank_slips", $options);

        if(!in_array($response->getStatusCode(), [200, 201, 202, 203, 204, 205, 206])){
            throw new \Exception("Request failed with status {$response->getStatusCode()}");
        }

        $body = $response->getBody();
        $data = json_decode($body, true);

        return $data;
    }

    public function getPdfBill($billId, $body)
    {
        #body["payerDocumentNumber"] => Documento do pagador (CPF/CNPJ)
        $options = [
            "headers" => self::authorizeHeaders(),
            "json" => $body
        ];

        $response  = $this->client->post("collection_bill_management/v2/bills/{$billId}/bank_slips", $options);

        if(!in_array($response->getStatusCode(), [200, 201, 202, 203, 204, 205, 206])){
            throw new \Exception("Request failed with status {$response->getStatusCode()}");
        }

        $body = $response->getBody();
        $data = json_decode($body, true);

        return $data;
    }

    public function simpleSearchBySonda($workspaceId, $bankSlip)
    {
        $options = [
            "headers" => self::authorizeHeaders()
        ];

        $response  = $this->client->get("collection_bill_management/v2/workspaces/{$workspaceId}/bank_slips/{$bankSlip}", $options);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception("Request failed with status {$response->getStatusCode()}");
        }

        $body = $response->getBody();
        $data = json_decode($body, true);

        return $data;
    }

    public function detailedSearchByNn($beneficiaryCode, $bankNumber)
    {
        $options = [
            "headers" => self::authorizeHeaders(),
            "query" => [
                "beneficiaryCode" => $beneficiaryCode,
                "bankNumber" => $bankNumber,
            ],
        ];

        $response  = $this->client->get("collection_bill_management/v2/bills", $options);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception("Request failed with status {$response->getStatusCode()}");
        }

        $body = $response->getBody();
        $data = json_decode($body, true);

        return $data;
    }

    public function detailedSearchBySn($beneficiaryCode, $bankNumber, $dueDate, $nominalValue)
    {
        $options = [
            "headers" => self::authorizeHeaders(),
            "query" => [
                "beneficiaryCode" => $beneficiaryCode,
                "bankNumber" => $bankNumber,
                "dueDate" => $dueDate,
                "nominalValue" => $nominalValue,
            ],
        ];

        $response  = $this->client->get("collection_bill_management/v2/bills", $options);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception("Request failed with status {$response->getStatusCode()}");
        }

        $body = $response->getBody();
        $data = json_decode($body, true);

        return $data;
    }

    public function detailedSearchBySearchType($billId, $searchType)
    {
        $options = [
            "headers" => self::authorizeHeaders(),
            "query" => [
                "tipoConsulta" => $searchType
            ],
        ];

        $response  = $this->client->get("collection_bill_management/v2/bills/{$billId}", $options);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception("Request failed with status {$response->getStatusCode()}");
        }

        $body = $response->getBody();
        $data = json_decode($body, true);

        return $data;
    }
}