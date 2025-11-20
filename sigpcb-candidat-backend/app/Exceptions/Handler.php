<?php

namespace App\Exceptions;

use Throwable;
use App\Services\Resp;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            $this->sendNotifications($e);
        });
    }

    public function render($request, Throwable $e)
    {
        return $this->handleException($request, $e);
    }

    public function handleException(Request $request, Throwable $exception)
    {
        if (!$request->is('api/*')) {
            return parent::render($request, $exception);
        }

        if ($exception instanceof RouteNotFoundException) {
            if (str($exception->getMessage())->contains('login')) {
                return Resp::error("Vous n'êtes pas connecté(e) ou votre session a expiré", statuscode: 401);
            }
            return Resp::error("Requête introuvable", statuscode: 404);
        }

        if ($exception instanceof NotFoundHttpException) {
            return Resp::error("Aucune donnée trouvée", statuscode: 404);
        }

        if ($exception instanceof UnauthorizedException) {
            return Resp::error("Vous n'êtes pas autorisé(e) à accéder à cette page", statuscode: 403); // Correction du code d'erreur à 403 (Forbidden)
        }

        if ($exception instanceof AuthenticationException) {
            return Resp::error("Vous n'êtes pas connecté(e) ou votre session a expiré", statuscode: 401);
        }

        if ($exception instanceof ModelNotFoundException) {
            return Resp::error("Aucune donnée ne correspond à votre requête", statuscode: 404); // Correction du message
        }

        if ($exception instanceof ValidationException) {
            return Resp::error("La validation a échoué", $exception->validator->errors(), statuscode: 400);
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return Resp::error("La méthode demandée n'est pas autorisée", statuscode: 405); // Correction du message
        }

        $this->sendNotifications($exception);
        return Resp::error("Une erreur inattendue s'est produite lors de la requête"); // Correction du message
    }

    private function sendNotifications(Throwable $e)
    {
        // $isLocal = App::isLocal();
        // if (!$isLocal) {
        //     $messager = new Messager();

        //     $messager->subject("Une erreur s'est produite sur ANaTT (BASE)");
        //     $messager->introlines("Path: " . request()->path());
        //     $messager->introlines("IP: " . request()->ip());
        //     $messager->introlines("BODY: " . json_encode(request()->all()));
        //     $messager->introlines("........................................................................");
        //     $messager->introlines($e->getMessage());

        //     Mail::to(
        //         ["'ulrichjaures2@gmail.com'", 'gildas.zinkpe@gmail.com', 'dev.claudy@gmail.com', 'franckhoundje@gmail.com']
        //     )->send(new ExceptionMail($messager));

        //     // abonnementbj@gmail.com
        // }
    }
}
