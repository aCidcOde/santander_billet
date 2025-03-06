<?php

namespace Acidcode\SantanderBillet;

use GuzzleHttp\Client;

class Santander
{
    protected $client, $options;

    public function __construct()
    {
        $certificatePath = storage_path(config('santander_billet.integrations.certificate_path'));

        $this->client = new Client([
            'base_uri' => config('santander_billet.integrations.host'),
            'cert' => storage_path() . config('santander_billet.integrations.certificate_path'),
            'ssl_key' =>  storage_path() . config('santander_billet.integrations.certificate_auth'),
            'curl' => [CURLOPT_SSLCERTTYPE => 'PEM'],
        ]);

        $this->options = [
            'form_params' => [
                'client_id' => config('santander_billet.integrations.client_id'),
                'client_secret' => config('santander_billet.integrations.client_secret'),
                'grant_type' => 'client_credentials'
            ]
        ];
    }

    private function handleRequest($method, $uri, $options = [])
    {
        try {
            $response = $this->client->request($method, $uri, $options);

            $body = $response->getBody();
            $data = json_decode($body, true);

            return [
                'success' => true,
                'code' => $response->getStatusCode(),
                'data' => $data
            ];

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $responseBody = $e->getResponse()->getBody()->getContents();
            $data = json_decode($responseBody, true);

            return [
                'success' => false,
                'code' => $e->getResponse()->getStatusCode(),
                'data' => $data
                ];
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    ];
                }
            }


    private function retrieveToken()
    {
        return $this->handleRequest('POST', 'auth/oauth/v2/token', $this->options);
    }

    private function token()
    {
        $cached = cache()->get('SANTANDER_BILLET_RESPONSE');

        return $cached
        ? $cached
        :cache()->remember('SANTANDER_BILLET_RESPONSE', now()->addSeconds(900), function(){

            $response = self::retrieveToken();

            if($response['success']){
                return $response['data'];
            }

            throw new \Exception($response['message'], $response['code']);
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

        return $this->handleRequest('POST', 'collection_bill_management/v2/workspaces', $options);
    }

    public function searchWorkspace($workspaceId = null)
    {
        $options = [
            "headers" => self::authorizeHeaders()
        ];

        return $this->handleRequest('GET', $workspaceId ? "collection_bill_management/v2/workspaces/{$workspaceId}" : "collection_bill_management/v2/workspaces", $options);
    }

    public function deleteWorkspace($workspaceId)
    {
        $options = [
            "headers" => self::authorizeHeaders()
        ];

        return $this->handleRequest('DELETE', "collection_bill_management/v2/workspaces/{$workspaceId}", $options);
    }

    public function updateWorkspace($workspaceId, $body = [])
    {
        $options = [
            "headers" => self::authorizeHeaders(),
            "json" => $body
        ];

        return $this->handleRequest('PATCH', "collection_bill_management/v2/workspaces/{$workspaceId}", $options);
    }

    public function registerBill($workspaceId, array $body = [])
    {
        $options = [
            "headers" => self::authorizeHeaders(),
            "json" => $body
        ];

        return $this->handleRequest('POST', "collection_bill_management/v2/workspaces/{$workspaceId}/bank_slips", $options);
    }

    public function updateBillInstructions($workspaceId, array $body = [])
    {
        $options = [
            "headers" => self::authorizeHeaders(),
            "json" => $body
        ];

        return $this->handleRequest('PATCH', "collection_bill_management/v2/workspaces/{$workspaceId}/bank_slips", $options);
    }

    public function getPdfBill($billId, $body)
    {
        #body["payerDocumentNumber"] => Documento do pagador (CPF/CNPJ)
        $options = [
            "headers" => self::authorizeHeaders(),
            "json" => $body
        ];

        return $this->handleRequest('POST', "collection_bill_management/v2/bills/{$billId}/bank_slips", $options);
    }

    public function simpleSearchBySonda($workspaceId, $bankSlip)
    {
        $options = [
            "headers" => self::authorizeHeaders()
        ];

        return $this->handleRequest('GET', "collection_bill_management/v2/workspaces/{$workspaceId}/bank_slips/{$bankSlip}", $options);
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

        return $this->handleRequest('GET', "collection_bill_management/v2/bills", $options);
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

        return $this->handleRequest('GET', "collection_bill_management/v2/bills", $options);
    }

    public function detailedSearchBySearchType($billId, $searchType)
    {
        $options = [
            "headers" => self::authorizeHeaders(),
            "query" => [
                "tipoConsulta" => $searchType
            ],
        ];

        return $this->handleRequest('GET', "collection_bill_management/v2/bills/{$billId}", $options);
    }
}
