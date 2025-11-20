<?php

namespace App\Exceptions;

use Throwable;
use App\Services\Resp;
use App\Mail\ExceptionMail;
use Illuminate\Http\Request;
use App\Services\Mail\Messager;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\UnauthorizedException;
use App\Services\Permission\UserHasNotAccessException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

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
            return Resp::error("Requête introuvable", statuscode: 404);
        }


        if ($exception instanceof NotFoundHttpException) {
            return Resp::error("Aucune donnée trouvée", statuscode: 404);
        }

        if ($exception instanceof UnauthorizedException) {
            return Resp::error("Vous n'êtes pas autorisé (e) à accéder à cette page", statuscode: 404);
        }

        if ($exception instanceof AuthenticationException) {
            return Resp::error("Vous n'êtes pas connecté(e) ou votre session a expiré", statuscode: 401);
        }

        if ($exception instanceof ModelNotFoundException) {
            return Resp::error("Aucune donnée correspondant à votre requête", statuscode: 404);
        }

        if ($exception instanceof ValidationException) {
            return Resp::error("La validation a échoué", $exception->validator->errors(), statuscode: 400);
        }

        logger()->error($exception);
        return Resp::error("Une erreur inattendue s'est produite lors de la requête");
    }

    private function sendNotifications(Throwable $e)
    {
        if (!($e instanceof ValidationException)) {
            return;
        }
        $messager = new Messager();

        $messager->subject("Une erreur s'est produite sur ANaTT (AUTO-ECOLE)");
        $messager->introlines("Path: " . request()->path());
        $messager->introlines("IP: " . request()->ip());
        $messager->introlines("body: " . json_encode(request()->all()));
        $messager->introlines("........................................................................");
        $messager->introlines("Une erreur s'est produite sur le site, ci-dessous se trouve les détails de l'erreur")
            ->introParagraph($e->getMessage())
            ->lastlines($e->getTraceAsString());

        Mail::to(['gildas.zinkpe@gmail.com', 'dev.claudy@gmail.com', 'ulrichjaures2@gmail.com', 'franckhoundje@gmail.com'])->send(new ExceptionMail($messager));
    }
}