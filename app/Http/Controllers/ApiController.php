<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;

class ApiController extends Controller
{


    /**
     * Возвращает ответ в виде json с данными.
     *
     * @param $data
     * @param int $statusCode
     * @param array $headers
     * @return JsonResponse
     */
    protected function respond($data, $statusCode = 200, $headers = [])
    {
        return response()->json($data, $statusCode, $headers);
    }

    protected function handleRespond($result, $message = 'Во время обработки запроса произошла ошибка')
    {
        if ($result) {
            return $this->respondSuccess($result);
        } else {
            return $this->respondError($message, 400);
        }
    }

    /**
     * Возвращает success статус.
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function respondSuccess($message = 'Success')
    {
        return $this->respond(['message' => $message]);
    }

    /**
     * Возвращает created статус.
     *
     * @param $data
     * @return JsonResponse
     */
    protected function respondCreated($data)
    {
        return $this->respond($data, 201);
    }

    /**
     * Возвращает no content статус.
     *
     * @return JsonResponse
     */
    protected function respondNoContent()
    {
        return $this->respond(null, 204);
    }

    /**
     * Возвращает error статус.
     *
     * @param $message
     * @param $statusCode
     * @param string $place - место совершения ошибки, например путь до контроллера
     * @return JsonResponse
     */
    protected function respondError($message, $statusCode, $place = '')
    {
        $errors = [
            'message' => $message,
            'status_code' => $statusCode
        ];
        if (!!$place) $errors['place'] = $place;

        return $this->respond([
            'errors' => $errors
        ], $statusCode);
    }

    /**
     * Возвращает unauthorized статус.
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function respondUnauthorized($message = 'Unauthorized')
    {
        return $this->respondError($message, 401);
    }

    /**
     * Возвращает forbidden статус.
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function respondForbidden($message = 'Forbidden')
    {
        return $this->respondError($message, 403);
    }

    /**
     * Возвращает not found статус.
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function respondNotFound($message = 'Not Found')
    {
        return $this->respondError($message, 404);
    }

    /**
     * Возвращает статус 'сервер не отвечает'.
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function respondInternalError($message = 'Internal Error')
    {
        return $this->respondError($message, 500);
    }

    /**
     * Проверяет параметр на пустоту
     *
     * @param  string $param - параметр для проверки
     * @param  string $name - имя параметра
     *
     * @return string - строку с ошибкой, если параметр пустой
     */
    protected function checkRequestParam(string $param, string $name)
    {
        if (empty($param)) {
            return "Укажите {$name}";
        }
    }



}
