<?php

namespace App\Traits;

trait HasTranslatedResponses
{
    /**
     * Return a success response with translated message
     */
    protected function successResponse($data = null, string $messageKey = 'messages.success', int $status = 200)
    {
        $response = ['message' => __($messageKey)];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        return response()->json($response, $status);
    }
    
    /**
     * Return an error response with translated message
     */
    protected function errorResponse(string $messageKey, int $status = 422, $data = null)
    {
        $response = ['error' => __($messageKey)];
        
        if ($data !== null) {
            $response = array_merge($response, is_array($data) ? $data : ['data' => $data]);
        }
        
        return response()->json($response, $status);
    }
    
    /**
     * Return a validation error response with translated message
     */
    protected function validationErrorResponse(string $messageKey = 'messages.validation_failed', $errors = null)
    {
        $response = ['message' => __($messageKey)];
        
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        
        return response()->json($response, 422);
    }
    
    /**
     * Return a not found response with translated message
     */
    protected function notFoundResponse(string $messageKey = 'messages.not_found')
    {
        return response()->json(['error' => __($messageKey)], 404);
    }
    
    /**
     * Return an unauthorized response with translated message
     */
    protected function unauthorizedResponse(string $messageKey = 'messages.unauthorized')
    {
        return response()->json(['error' => __($messageKey)], 401);
    }
    
    /**
     * Return a forbidden response with translated message
     */
    protected function forbiddenResponse(string $messageKey = 'messages.forbidden')
    {
        return response()->json(['error' => __($messageKey)], 403);
    }
}