/**
 * API Client for Laravel Backend
 * Handles all HTTP requests to the backend API
 */

const API = {
    baseURL: "/api/v1", // Same domain - no CORS issues!

    /**
     * Get CSRF token from meta tag
     */
    getCsrfToken() {
        return document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");
    },

    /**
     * Initialize CSRF cookie from backend
     * This must be called before making any API requests
     */
    async initCsrf() {
        if (this._csrfInitialized) return;

        try {
            await fetch(
                `${this.baseURL.replace("/api/v1", "")}/sanctum/csrf-cookie`,
                {
                    method: "GET",
                    credentials: "include",
                },
            );
            this._csrfInitialized = true;
        } catch (error) {
            console.error("CSRF initialization error:", error);
        }
    },

    /**
     * Handle API response
     */
    async handleResponse(response) {
        const data = await response.json();

        if (!response.ok) {
            throw {
                status: response.status,
                message: data.message || "An error occurred",
                errors: data.errors || {},
            };
        }

        return data;
    },

    /**
     * GET request
     */
    async get(endpoint, params = {}) {
        await this.initCsrf(); // Ensure CSRF cookie is set

        const url = new URL(`${this.baseURL}${endpoint}`);
        Object.keys(params).forEach((key) => {
            if (params[key] !== null && params[key] !== undefined) {
                url.searchParams.append(key, params[key]);
            }
        });

        try {
            const response = await fetch(url, {
                method: "GET",
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                },
                credentials: "include", // Include cookies for Sanctum
            });

            return await this.handleResponse(response);
        } catch (error) {
            console.error("API GET Error:", error);
            throw error;
        }
    },

    /**
     * POST request
     */
    async post(endpoint, data = {}) {
        try {
            const response = await fetch(`${this.baseURL}${endpoint}`, {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": this.getCsrfToken(),
                },
                credentials: "include",
                body: JSON.stringify(data),
            });

            return await this.handleResponse(response);
        } catch (error) {
            console.error("API POST Error:", error);
            throw error;
        }
    },

    /**
     * PUT request
     */
    async put(endpoint, data = {}) {
        try {
            const response = await fetch(`${this.baseURL}${endpoint}`, {
                method: "PUT",
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": this.getCsrfToken(),
                },
                credentials: "include",
                body: JSON.stringify(data),
            });

            return await this.handleResponse(response);
        } catch (error) {
            console.error("API PUT Error:", error);
            throw error;
        }
    },

    /**
     * DELETE request
     */
    async delete(endpoint) {
        try {
            const response = await fetch(`${this.baseURL}${endpoint}`, {
                method: "DELETE",
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": this.getCsrfToken(),
                },
                credentials: "include",
            });

            return await this.handleResponse(response);
        } catch (error) {
            console.error("API DELETE Error:", error);
            throw error;
        }
    },

    /**
     * Download file (for exports)
     */
    async download(endpoint, params = {}, filename = "download") {
        const url = new URL(`${this.baseURL}${endpoint}`);
        Object.keys(params).forEach((key) => {
            if (params[key] !== null && params[key] !== undefined) {
                url.searchParams.append(key, params[key]);
            }
        });

        try {
            const response = await fetch(url, {
                method: "GET",
                headers: {
                    Accept: "application/json",
                },
                credentials: "include",
            });

            if (!response.ok) {
                throw new Error("Download failed");
            }

            const blob = await response.blob();
            const downloadUrl = window.URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.href = downloadUrl;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(downloadUrl);
            document.body.removeChild(a);
        } catch (error) {
            console.error("API Download Error:", error);
            throw error;
        }
    },
};

/**
 * Helper function to display error messages
 */
function showError(message, errors = {}) {
    // Create alert element
    const alert = document.createElement("div");
    alert.className =
        "fixed top-4 right-4 z-50 max-w-md p-4 bg-red-50 border border-red-200 rounded-lg shadow-lg dark:bg-red-900/20 dark:border-red-800";

    let errorHtml = `
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">${message}</h3>
    `;

    if (Object.keys(errors).length > 0) {
        errorHtml +=
            '<ul class="mt-2 text-sm text-red-700 dark:text-red-300 list-disc list-inside">';
        Object.values(errors).forEach((errorArray) => {
            errorArray.forEach((error) => {
                errorHtml += `<li>${error}</li>`;
            });
        });
        errorHtml += "</ul>";
    }

    errorHtml += `
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-3 flex-shrink-0 text-red-500 hover:text-red-700">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
    `;

    alert.innerHTML = errorHtml;
    document.body.appendChild(alert);

    // Auto remove after 5 seconds
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

/**
 * Helper function to display success messages
 */
function showSuccess(message) {
    const alert = document.createElement("div");
    alert.className =
        "fixed top-4 right-4 z-50 max-w-md p-4 bg-green-50 border border-green-200 rounded-lg shadow-lg dark:bg-green-900/20 dark:border-green-800";

    alert.innerHTML = `
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <p class="text-sm font-medium text-green-800 dark:text-green-200">${message}</p>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-3 flex-shrink-0 text-green-500 hover:text-green-700">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
    `;

    document.body.appendChild(alert);

    // Auto remove after 3 seconds
    setTimeout(() => {
        alert.remove();
    }, 3000);
}

/**
 * Loading overlay helper
 */
const Loading = {
    show() {
        const overlay = document.createElement("div");
        overlay.id = "loading-overlay";
        overlay.className =
            "fixed inset-0 z-999999 flex items-center justify-center bg-white/80 dark:bg-black/80";
        overlay.innerHTML = `
            <div class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent"></div>
        `;
        document.body.appendChild(overlay);
    },

    hide() {
        const overlay = document.getElementById("loading-overlay");
        if (overlay) {
            overlay.remove();
        }
    },
};
