<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebHooksController extends Controller
{
    public function fedapay()
    {
        // You can find your endpoint's secret key in your webhook settings
        $endpoint_secret = env('FEDAPAY_WEBHOOK_KEY');

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_X_FEDAPAY_SIGNATURE'];
        $event = null;


        try {
            $event = \FedaPay\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );

            $data = $this->getData($event);
        } catch (\UnexpectedValueException $e) {
            // Invalid payload

            http_response_code(400);
            exit;
        } catch (\FedaPay\Error\SignatureVerification $e) {

            http_response_code(400);
            exit;
        }

        // Handle the event
        switch ($event->name) {
            case 'transaction.approved':
                $this->transactionApproved($data);
                break;
            case 'transaction.canceled':
                $this->transactionCanceled($data);
                break;
            default:
                http_response_code(400);
                exit;
        }


        http_response_code(200);
        exit;
    }

    private function transactionApproved(array $data)
    {
        file_put_contents(base_path('web.json'), json_encode($data));
    }


    private function transactionCanceled(array $data) {}

    private function getData($event)
    {
        $entity = data_get($event, 'entity');
        return [
            'amount' => data_get($entity, 'amount'),
            'transactionId' => data_get($entity, 'id'),
            'status' => data_get($entity, 'status'),
            'approved_at' => data_get($event, 'approved_at'),
            'created_at' => data_get($event, 'created_at'),
            'canceled_at' => data_get($event, 'canceled_at'),
            'receipt_url' => data_get($event, 'receipt_url'),
            'reference' => data_get($event, 'reference'),
        ];
    }
}
