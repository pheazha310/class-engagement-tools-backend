/**
 * API service for making authenticated requests to the Laravel backend.
 * Uses the native Fetch API (no Axios dependency).
 */

interface ApiResponse<T = any> {
    data: T
    error?: string
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

        if (!response.ok) {
            const errorData = await response.json().catch(() => null)
            const errorMessage = errorData?.message || `Request failed with status ${response.status}`
            return { data: null as unknown as T, error: errorMessage }
        }

        const data = await response.json()
        return { data: data as T }
    } catch (error: any) {
        return { data: null as unknown as T, error: error.message || 'Network request failed' }
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
