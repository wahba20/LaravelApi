<?php

namespace App\Exceptions;

use App\Http\Middleware\VerifyCsrfToken;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param \Exception $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        //=======validation exception ==========//
        if ($exception instanceof ValidationException) {
            return $this->convertValidationExceptionToResponse($exception, $request);
        }
        //===========model not found exception===========//
        if ($exception instanceof ModelNotFoundException) {
            $modelName = strtolower(class_basename($exception->getModel()));
            return response()->json(['error', "{$modelName} model  not found"], 404);
        }
        //==================auth ==========//
        if ($exception instanceof AuthenticationException) {
            return $this->unauthenticated($request, $exception);
        }
        //============= authorization ================//
        if ($exception instanceof AuthorizationException) {
            return response()->json($exception->getMessage(), 403);
        }
        //==================== method not allowed ============//
        if ($exception instanceof MethodNotAllowedHttpException) {
            return response()->json('the specified method for request is invalid', 405);
        }
        //========== not found ======================//
        if ($exception instanceof NotFoundHttpException) {
            return response()->json('the specified URL not found', 404);
        }
        //========== general hhtp======================//
        if ($exception instanceof HttpException) {
            return response()->json($exception->getMessage(), $exception->getCode());
        }
        //============ when removing related resource =============//
        if ($exception instanceof QueryException) {
//            dd($exception);
            $errorCode = $exception->errorInfo[1];
            if ($errorCode == 19) {
                return response()->json('can not remove this resource it related to any other resource', 409);

            }
        }
        if ($exception instanceof TokenMismatchException) {
            return redirect()->back()->withInput($request->input());
        }

        if (config('app.debug')) {
            return parent::render($request, $exception);
        }
        return response()->json('un expected Exception , Try Later ', 500);


    }

    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        $errors = $e->validator->errors()->getMessages();

        return response()->json($errors, 422);
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json('unauthenticated', 422);

    }

    private function isFrontEnd($request)
    {
        return $request->acceptsHtml() && collect($request->route()->middleware())->contains('web');
    }

}
