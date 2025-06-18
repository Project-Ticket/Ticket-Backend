<?php

namespace App\Facades;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class MessageResponseJson extends JsonResponse
{
    /**
     * Default response structure
     */
    private const DEFAULT_STRUCTURE = [
        'success' => true,
        'code' => Response::HTTP_OK,
        'message' => '',
        'data' => null,
        'meta' => []
    ];

    /**
     * Render a standardized JSON response
     *
     * @param int $code HTTP status code
     * @param string $message Response message
     * @param mixed $data Response data
     * @param array $meta Additional metadata
     * @param bool $success Success status
     * @return JsonResponse
     */
    public static function render(
        int $code = Response::HTTP_OK,
        string $message = '',
        $data = null,
        array $meta = [],
        ?bool $success = null
    ): JsonResponse {
        $response = self::DEFAULT_STRUCTURE;

        $response['success'] = $success ?? ($code >= 200 && $code < 300);
        $response['code'] = $code;
        $response['message'] = $message;

        if ($data !== null || is_array($data)) {
            $response['data'] = $data;
        } else {
            unset($response['data']);
        }

        if (!empty($meta)) {
            $response['meta'] = $meta;
        } else {
            unset($response['meta']);
        }

        return response()->json($response, $code, [
            'Content-Type' => 'application/json;charset=UTF-8'
        ]);
    }

    /**
     * Success response (200)
     */
    public static function success(
        string $message = 'Request successful',
        $data = null,
        array $meta = []
    ): JsonResponse {
        return self::render(
            code: Response::HTTP_OK,
            message: $message,
            data: $data,
            meta: $meta
        );
    }

    /**
     * Created response (201)
     */
    public static function created(
        string $message = 'Resource created successfully',
        $data = null,
        array $meta = []
    ): JsonResponse {
        return self::render(
            code: Response::HTTP_CREATED,
            message: $message,
            data: $data,
            meta: $meta
        );
    }

    /**
     * Accepted response (202)
     */
    public static function accepted(
        string $message = 'Request accepted for processing',
        $data = null,
        array $meta = []
    ): JsonResponse {
        return self::render(
            code: Response::HTTP_ACCEPTED,
            message: $message,
            data: $data,
            meta: $meta
        );
    }

    /**
     * No Content response (204)
     */
    public static function noContent(
        string $message = 'Request successful, no content to return'
    ): JsonResponse {
        return self::render(
            code: Response::HTTP_NO_CONTENT,
            message: $message
        );
    }

    /**
     * Paginated response with enhanced pagination metadata
     */
    public static function paginated(
        string $message = 'Data retrieved successfully',
        LengthAwarePaginator $paginator,
        array $meta = []
    ): JsonResponse {
        $paginationMeta = [
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'count' => $paginator->count(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'has_more_pages' => $paginator->hasMorePages(),
                'path' => $paginator->path(),
                'links' => [
                    'first' => $paginator->url(1),
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                    'last' => $paginator->url($paginator->lastPage())
                ]
            ]
        ];

        return self::render(
            code: Response::HTTP_OK,
            message: $message,
            data: $paginator->items(),
            meta: array_merge($meta, $paginationMeta)
        );
    }

    /**
     * Collection response with count metadata
     */
    public static function collection(
        string $message = 'Data retrieved successfully',
        Collection $collection,
        array $meta = []
    ): JsonResponse {
        $collectionMeta = [
            'count' => $collection->count(),
            'is_empty' => $collection->isEmpty()
        ];

        return self::render(
            code: Response::HTTP_OK,
            message: $message,
            data: $collection->values(),
            meta: array_merge($meta, $collectionMeta)
        );
    }

    /**
     * Bad Request response (400)
     */
    public static function badRequest(
        string $message = 'Bad request',
        $data = null,
        array $meta = []
    ): JsonResponse {
        return self::render(
            code: Response::HTTP_BAD_REQUEST,
            message: $message,
            data: $data,
            meta: $meta,
            success: false
        );
    }

    /**
     * Unauthorized response (401)
     */
    public static function unauthorized(
        string $message = 'Authentication required',
        array $meta = []
    ): JsonResponse {
        return self::render(
            code: Response::HTTP_UNAUTHORIZED,
            message: $message,
            meta: $meta,
            success: false
        );
    }

    /**
     * Forbidden response (403)
     */
    public static function forbidden(
        string $message = 'Access forbidden',
        array $meta = []
    ): JsonResponse {
        return self::render(
            code: Response::HTTP_FORBIDDEN,
            message: $message,
            meta: $meta,
            success: false
        );
    }

    /**
     * Not Found response (404)
     */
    public static function notFound(
        string $message = 'Resource not found',
        array $meta = []
    ): JsonResponse {
        return self::render(
            code: Response::HTTP_NOT_FOUND,
            message: $message,
            meta: $meta,
            success: false
        );
    }

    /**
     * Method Not Allowed response (405)
     */
    public static function methodNotAllowed(
        string $message = 'Method not allowed',
        array $allowedMethods = []
    ): JsonResponse {
        $meta = !empty($allowedMethods) ? ['allowed_methods' => $allowedMethods] : [];

        return self::render(
            code: Response::HTTP_METHOD_NOT_ALLOWED,
            message: $message,
            meta: $meta,
            success: false
        );
    }

    /**
     * Conflict response (409)
     */
    public static function conflict(
        string $message = 'Resource conflict',
        $data = null,
        array $meta = []
    ): JsonResponse {
        return self::render(
            code: Response::HTTP_CONFLICT,
            message: $message,
            data: $data,
            meta: $meta,
            success: false
        );
    }

    /**
     * Validation Error response (422)
     */
    public static function validationError(
        string $message = 'Validation failed',
        array $errors = [],
        array $meta = []
    ): JsonResponse {
        $errorMeta = [
            'validation' => [
                'error_count' => count($errors),
                'failed_fields' => array_keys($errors)
            ]
        ];

        return self::render(
            code: Response::HTTP_UNPROCESSABLE_ENTITY,
            message: $message,
            data: ['errors' => $errors],
            meta: array_merge($meta, $errorMeta),
            success: false
        );
    }

    /**
     * Too Many Requests response (429)
     */
    public static function tooManyRequests(
        string $message = 'Too many requests',
        array $rateLimitInfo = []
    ): JsonResponse {
        $meta = !empty($rateLimitInfo) ? ['rate_limit' => $rateLimitInfo] : [];

        return self::render(
            code: Response::HTTP_TOO_MANY_REQUESTS,
            message: $message,
            meta: $meta,
            success: false
        );
    }

    /**
     * Internal Server Error response (500)
     */
    public static function serverError(
        string $message = 'Internal server error',
        array $meta = []
    ): JsonResponse {
        return self::render(
            code: Response::HTTP_INTERNAL_SERVER_ERROR,
            message: $message,
            meta: $meta,
            success: false
        );
    }

    /**
     * Service Unavailable response (503)
     */
    public static function serviceUnavailable(
        string $message = 'Service temporarily unavailable',
        array $meta = []
    ): JsonResponse {
        return self::render(
            code: Response::HTTP_SERVICE_UNAVAILABLE,
            message: $message,
            meta: $meta,
            success: false
        );
    }

    /**
     * Custom response with custom status code
     */
    public static function custom(
        int $code,
        string $message,
        $data = null,
        array $meta = [],
        ?bool $success = null
    ): JsonResponse {
        return self::render(
            code: $code,
            message: $message,
            data: $data,
            meta: $meta,
            success: $success
        );
    }

    /**
     * Deprecated methods for backward compatibility
     */

    /**
     * @deprecated Use created() instead
     */
    public static function create(
        string $message = "Data has been created!",
        $data = null
    ): JsonResponse {
        return self::created($message, $data);
    }

    /**
     * @deprecated Use unauthorized() instead
     */
    public static function unauhtorize(
        string $message = "Unauthorized!"
    ): JsonResponse {
        return self::unauthorized($message);
    }

    /**
     * @deprecated Use badRequest() instead
     */
    public static function warning(
        string $message = ""
    ): JsonResponse {
        return self::badRequest($message);
    }

    /**
     * @deprecated Use validationError() instead
     */
    public static function validator(
        string $message = "Fill data correctly!",
        array $data = [],
        bool $isList = false
    ): JsonResponse {
        if ($isList && count($data) > 1) {
            $message .= ' and ' . (count($data) - 1) . ' other errors.';
        }

        return self::validationError($message, $data);
    }

    /**
     * @deprecated Use serverError() instead
     */
    public static function error(
        string $message = "Something went Wrong!"
    ): JsonResponse {
        return self::serverError($message);
    }

    /**
     * @deprecated Use paginated() instead
     */
    public static function paginate(
        string $message = "",
        LengthAwarePaginator $data
    ): JsonResponse {
        return self::paginated($message, $data);
    }
}
