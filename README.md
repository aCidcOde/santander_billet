# Criar boleto pix Santander

Pacote  responsável por gerar boletos com opção PIX via API do Santander

## 🚀 Começando

Essas instruções permitirão que você obtenha uma cópia do projeto em operação na sua máquina local para fins de desenvolvimento e teste.


### 📋 Pré-requisitos

- PHP ˆ7.2
- Laravel ˆ6.0
- Composer

### 🔧 Instalação

Use o gerenciador de pacotes COMPOSER para incluir as dependéncias  ao seu projeto.

Comando a execultar:

```
composer require flycorp/santander-billet
```
Ou, em seu 'composer.json' adicione:

```json
{
    "require": {
        "flycorp/santander-billet": "dev-main"
    }
}
```
Em seguida, execute o comando de instalação do gerenciador de pacotes:

    composer install

Em seguida, publique o arquivo de configuração do pacote em config\santander_billet.php:

    php artisan vendor:publish --provider="FlyCorp\SantanderBillet\providers\SantanderBilletServiceProvider" --tag=config

Lembre-se de realizar a importação da classe Santander quando for utilizar o pacote:

    use FlyCorp\SantanderBillet\Santander;

## Variáveis de ambiente

Adicione ao seu arquivo '.env' as variáveis de ambiente que possibilitarão a comunicação com o respectivo endpoint de API:

    SANTANDER_BILLET_ENVIRONMENT="sandbox"
    SANTANDER_BILLET_CLIENT_ID=
    SANTANDER_BILLET_CLIENT_SECRET=
    SANTANDER_BILLET_CERTIFICATE_AUTH=
    SANTANDER_BILLET_CERTIFICATE_PATH=

Descrição das variáveis:

    * SANTANDER_BILLET_ENVIRONMENT: Ambiente da API(sandbox ou production)
    * SANTANDER_BILLET_CLIENT_ID: Valor do client_id fornecido pelo Santader
    * SANTANDER_BILLET_CLIENT_SECRET: Valor do client_secret fornecido pelo Santander
    * SANTANDER_BILLET_CERTIFICATE_AUTH: Senha do certificado
    * SANTANDER_BILLET_CERTIFICATE_PATH: Caminho do certificado em formato pfx dentro da pasta Storage

## Endpoints de API

### Workspace:

1. [POST] Criar workspace */collection_bill_management/v2/workspaces*:

   ```php
   $santanderIntegration = new Santander;
   
   $workspace = [
        "type" => "BILLING",
        "covenants" => [
            [
                "code" => 0000001
            ]
        ],
        "description" => "Testando",
        "bankSlipBillingWebhookActive" => true,
        "pixBillingWebhookActive" => true,
        "webhookURL" => "https://teste"
    ];

   $data = $santanderIntegration->createWorkspace($workspace);
   ```

2. [GET] Consultar workspace */collection_bill_management/v2/workspaces*:

   ```php
   $santanderIntegration = new Santander;

   $data = $santanderIntegration->searchWorkspace();#INFORME O ID DO WORKSPACE PARA PESQUISA ESPECÍFICA
   ```

3. [DELETE] Deletar workspace */collection_bill_management/v2/workspaces/{workspace_id}*:

   ```php
   $santanderIntegration = new Santander;

   $data = $santanderIntegration->deleteWorkspace("5ca21612-ee50-4626-b2da-e66ca35c605f");
   ```
3. [PATCH] Alterar workspace */collection_bill_management/v2/workspaces/{workspace_id}*:

   ```php
   $santanderIntegration = new Santander;

   $body = [
        "covenants" => [
            [
                "code" => 0000001
            ]
        ],
        "description" => "Testando"
    ];
   
   #Forneça o workspace_id e body da requisição
   $data = $santanderIntegration->updateWorkspace("5ca21612-ee50-4626-b2da-e66ca35c605f", $body);
   ```

### Boletos:

1. [POST] Registrar boleto */collection_bill_management/v2/workspaces/{workspace_id}/bank_slips*:

   ```php
   $billet = new Billet();
   $santanderIntegration = new Santander;

   $billet->setEnvironment("sandbox")# Valores aceitos(sandbox, PRODUCAO)
        ->setNsuCode(1014)
        ->setNsuDate("2023-05-09")
        ->setCovenantCode("0000001")#INFORME O SEU
        ->setBankNumber("1014")#INFORME O SEU
        ->setClientNumber("123")
        ->setDueDate("2023-05-09")
        ->setIssueDate("2023-05-09")
        ->setParticipantCode("teste liq abat")
        ->setNominalValue(1.00)
        ->payer()
            ->setName("Fulano")
            ->setDocumentType("CPF")
            ->setDocumentNumber("94620639079")
            ->setAddress("rua nove de janeiro")
            ->setNeighborhood("bela vista")
            ->setCity("sao paulo")
            ->setState("SP")
            ->setZipCode("05134-897")
        ->beneficiary()
            ->setName("Ciclano")
            ->setDocumentType("CNPJ")
            ->setDocumentNumber("20201210000155")
        ->setDocumentKind("DUPLICATA_MERCANTIL")
        ->setDeductionValue(0.10)
        ->setPaymentType("REGISTRO")
        ->key()#ESTA CHAVE É NECESSÁRIA PARA GERAR O QR-CODE
            ->setType("CNPJ")#TIPOS ACEITOS (CPF, CNPJ, CELULAR, EMAIL, EVP)
            ->setDictKey("00000000000000")#CNPJ SEM MÁSCARA
        ->setWriteOffQuantityDays("30")
        ->setMessages(["mensagem um", "mensagem dois"]);

    #Forneça o workspace_id e body da requisição
    $data = $santanderIntegration->registerBill('5ca21612-ee50-4626-b2da-e66ca35c605f', $billet->toArray());
   ```
2. [PATCH] Atualizar instruções */collection_bill_management/v2/workspaces/{workspace_id}/bank_slips*:

   ```php
   $santanderIntegration = new Santander;

   $body = [
        "covenantCode" => "0000001",
        "bankNumber" => "123",
        "operation" => "BAIXAR"
    ];

   #Forneça o workspace_id e body da requisição
   $data = $santanderIntegration->updateBillInstructions("5ca21612-ee50-4626-b2da-e66ca35c605f", $body);
   ```

3. [POST] Obter dados para acessar boleto(PDF/QR-code) */collection_bill_management/v2/bills/{$billId}/bank_slips*:

   ```php
   $santanderIntegration = new Santander;

   $billId = "0000001.1005";

   $body = [
        "payerDocumentNumber" => "94620639079",
    ];    

   #Forneça o bill_id e body da requisição
   $data = $santanderIntegration->getPdfBill($billId, $body);
   ```

### Consulta simples:

1. [GET] Consulta sonda */collection_bill_management/v2/workspaces/{workspace_id}/bank_slips/{bank_slips}*:

   ```php
   $santanderIntegration = new Santander;

   $data = $santanderIntegration->simpleSearchBySonda();
   ```

2. [GET] Consulta nn */collection_bill_management/v2/bills*:

   ```php
   $santanderIntegration = new Santander;

   $beneficiaryCode = "356720"; #CÓDIGO DO BENEFICIÁRIO
   $bankNumber = "10054325"; #CÓDIGO DO BANCO

   $data = $santanderIntegration->detailedSearchByNn($beneficiaryCode, $bankNumber);
   ```

3. [GET] Consulta sn */collection_bill_management/v2/bills*:

   ```php
   $santanderIntegration = new Santander;

   $beneficiaryCode = "356720"; #CÓDIGO DO BENEFICIÁRIO
   $bankNumber = "10054325"; #CÓDIGO DO BANCO
   $dueDate = "2023-04-25";
   $nominalValue = "100.00";

   $data = $santanderIntegration->detailedSearchBySn($beneficiaryCode, $bankNumber, $dueDate, $nominalValue);
   ```

4. [GET] Consulta sn */collection_bill_management/v2/bills*:

   ```php
   $santanderIntegration = new Santander;

   $beneficiaryCode = "356720"; #CÓDIGO DO BENEFICIÁRIO
   $bankNumber = "10054325"; #CÓDIGO DO BANCO
   $dueDate = "2023-04-25"; #DATA DE VENCIMENTO
   $nominalValue = "100.00";#VALOR NOMINAL

   $data = $santanderIntegration->detailedSearchBySn($beneficiaryCode, $bankNumber, $dueDate, $nominalValue);
   ```

5. [GET] Pesquisa por tipo */collection_bill_management/v2/bills*:

   ```php
   $santanderIntegration = new Santander;

   $billId = "0000001.1005";

   #Valores para type (bankslips, default, duplicate, registry, settlement)
   #A. default: Pesquisa padrão, trazendo somente dados básicos do boleto
   #B. duplicate: Pesquisa de dados para emissão de segunda via de boleto
   #C. bankslip: Pesquisa para dados completos do boleto
   #D. setlement: Pesquisa para informações de baixas/liquidações do boleto
   #E. registry: Pesquisa de informações de cartório no boleto
   $data = $santanderIntegration->detailedSearchBySearchType($billId, $type);
   ```