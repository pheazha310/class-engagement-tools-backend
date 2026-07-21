/**
 * API service for making authenticated requests to the Laravel backend.
 * Uses the native Fetch API (no Axios dependency).
 *
 * On validation errors (422), returns the structured errors object.
 * On other errors, returns an error message string.
 */

export interface ApiValidationErrors {
    [field: string]: string[]
}

export type ApiError =
    | { type: 'validation'; errors: ApiValidationErrors; message: string }
    | { type: 'general'; message: string }

export interface ApiResponse<T = any> {
    data: T | null
    error?: ApiError
}

type HttpMethod = 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE'

async function getCsrfToken(): Promise<string> {
    // Try to read the XSRF-TOKEN cookie first
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]*)/)
    if (match) {
        return decodeURIComponent(match[1])
    }

    // Fallback: fetch a fresh token
    try {
        const response = await fetch('/sanctum/csrf-cookie', {
            method: 'GET',
            credentials: 'include',
        })
        if (response.ok) {
            const newMatch = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]*)/)
            if (newMatch) {
                return decodeURIComponent(newMatch[1])
            }
        }
    } catch {
        // Ignore errors, return empty string
    }

    return ''
}

function parseErrorResponse(status: number, body: any): ApiError {
    // Laravel validation errors (422) come with an `errors` object
    if (status === 422 && body?.errors) {
        return {
            type: 'validation',
            errors: body.errors as ApiValidationErrors,
            message: body.message || 'Validation failed.',
        }
    }

    // All other errors
    return {
        type: 'general',
        message: body?.message || `Request failed with status ${status}`,
    }
}

async function request<T = any>(
    method: HttpMethod,
    url: string,
    body?: Record<string, any> | null,
): Promise<ApiResponse<T>> {
    const headers: Record<string, string> = {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    }

    // Add XSRF token for stateful (session-based) requests
    if (method !== 'GET') {
        const token = await getCsrfToken()
        if (token) {
            headers['X-XSRF-TOKEN'] = token
        }
    }

    try {
        const response = await fetch(url, {
            method,
            headers,
            credentials: 'include',
            body: body ? JSON.stringify(body) : null,
        })

        const responseBody = await response.json().catch(() => null)

        if (!response.ok) {
            return {
                data: null,
                error: parseErrorResponse(response.status, responseBody),
            }
        }

        return { data: responseBody as T }
    } catch (error: any) {
        return {
            data: null,
            error: {
                type: 'general',
                message: error.message || 'Network request failed',
            },
        }
    }
}

/**
 * Perform a GET request.
 */
export function get<T = any>(url: string): Promise<ApiResponse<T>> {
    return request<T>('GET', url)
}

/**
 * Perform a POST request.
 */
export function post<T = any>(url: string, body?: Record<string, any>): Promise<ApiResponse<T>> {
    return request<T>('POST', url, body)
}

/**
 * Perform a PUT request.
 */
export function put<T = any>(url: string, body?: Record<string, any>): Promise<ApiResponse<T>> {
    return request<T>('PUT', url, body)
}

/**
 * Perform a DELETE request.
 */
export function del<T = any>(url: string): Promise<ApiResponse<T>> {
    return request<T>('DELETE', url)
}

export default { get, post, put, del }
