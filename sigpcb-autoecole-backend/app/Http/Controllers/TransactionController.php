<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Services\PaymentService;
use App\Http\Resources\TransactionResource;

class TransactionController extends ApiController
{
    /**
     * Creates an agreement transaction.
     *
     * @param int $id The ID of the agreement request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($service, $id)
    {
        $transaction = Transaction::findJustOne([
            "service" => $service,
            "service_id" => $id,
            "status" => Transaction::APPROVED,
        ]);

        if ($transaction) {
            return $this->errorResponse("Vous ne pouvez pas créer de transaction pour cette demande");
        }
        # Charger dynamiquement un service (demande d'agrément, )
        $infos = app(PaymentService::class)->loadService($service, $id);

        $payerNpi = $infos['payer']['npi'];
        $uuid = Str::uuid();

        $this->fedapayEnv();
        $montant = $infos['amount'];

        # Créer une transaction depuis Fedapay
        $fedPayTransaction = \FedaPay\Transaction::create([
            "description" => "Paiement à " . env('APP_NAME'),
            "amount" => $montant,
            "currency" => ["iso" => "XOF"],
            "callback_url" => $infos["callback_url"],
        ]);



        # Créer une transaction dans notre base de données
        $transaction = Transaction::updateOrCreate([
            'service' => $service,
            'service_id' => $id,
        ], [
            'service' => $service,
            'service_id' => $id,
            'amount' => $montant,
            'npi' => $payerNpi,
            'note' => 'Paiement à ' . env('APP_NAME') . " pour $service",
            'transaction_id' => $fedPayTransaction->id,
            "uuid" => $uuid
        ]);

        return $this->successResponse([
            "transaction_id" => $transaction->transaction_id,
            "uuid" => $transaction->uuid,

        ], "Paiement créé avec succès");
    }

    /**
     * Verify a transaction.
     *
     * This method checks the status of a transaction against the FedaPay API and updates the transaction status accordingly.
     *
     * @param Transaction $transaction The transaction to verify.
     *
     * @return mixed Returns a success response if the transaction is approved, otherwise an error response.
     */
    public function proceed(Transaction $transaction)
    {
        // If the transaction is already approved, return a success response.
        if ($transaction->status == "approved") {
            return $this->successResponse(TransactionResource::make($transaction));
        }

        $this->fedapayEnv();

        # Vérification de la transaction depuis FEDPAY
        $fedapyTransaction = \FedaPay\Transaction::retrieve($transaction->transaction_id);

        if ($fedapyTransaction->status == 'pending') {
            return $this->successResponse(TransactionResource::make($transaction));
        }
        $status = $fedapyTransaction->status == "approved" ? "approved" : "failed";

        if ($fedapyTransaction->amount != $transaction->amount) {
            return $this->errorResponse("Le montant payé est différent de celui demandé");
        }

        // Update the transaction status to approved and set the perform time.
        $transaction->update([
            "status" => $status,
            "perform_time" => now()
        ]);

        return $this->successResponse(TransactionResource::make($transaction));
    }

    /**
     * Verifie si le payment a été effectué, si la transaction n'est pas payé le code essaie de vérifier 5 fois
     * @param mixed $transaction_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkPayment(Transaction $transaction)
    {
        if ($transaction->status === 'approved') {
            return $this->successResponse($transaction);
        }
        $this->fedapayEnv();

        try {
            /**
             * Retry the FedaPay transaction retrieval operation up to 10 times.
             *
             * This function attempts to retrieve a FedaPay transaction using the provided transaction ID.
             * If the transaction status is not 'approved', it throws an exception.
             * The function retries the operation up to 5 times with a delay of 5 milliseconds between each attempt.
             */
            $transaction = retry(
                5,
                function () use ($transaction) {
                    /**
                     * Retrieve the FedaPay transaction using the provided transaction ID.
                     */
                    $result = \FedaPay\Transaction::retrieve($transaction->transaction_id);
                    /**
                     * Check if the transaction status is 'approved'.
                     */
                    if ($result->status === 'approved') {
                        /**
                         * Return the retrieved transaction object if the status is 'approved'.
                         */
                        return $this->proceed($transaction);
                    }
                    # Important pour relancer, la vérifier
                    throw new \Exception('Paiement non effectué');
                },
                sleepMilliseconds: 10000 //10 seconds avant de relancer
            );
        } catch (\Throwable $th) {
            logger()->error($th);
            // En cas d'erreur, retourner un message d'erreur avec le message de l'exception.
            // Cela peut être utile pour suivre les erreurs lors de la vérification du paiement.
            return $this->successResponse(TransactionResource::make($transaction));
        }

        return $this->successResponse(TransactionResource::make($transaction));
    }
    private function fedapayEnv()
    {
        $fedaPayEnv = env('FEDAPAY_ENV', 'sandbox');
        if ($fedaPayEnv === 'live') {
            \FedaPay\FedaPay::setEnvironment('live');
            \FedaPay\FedaPay::setApiKey(env('FEDAPAY_LIVE_PRIVATE_KEY'));
        } else {
            \FedaPay\FedaPay::setEnvironment('sandbox');
            \FedaPay\FedaPay::setApiKey(env('FEDAPAY_SANDBOX_PRIVATE_KEY'));
        }
    }
}
