// resources/ts/services/HttpService.ts

export class HttpService {
    static async request(url: string, method: string = 'GET', data: any = null): Promise<any> {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        const headers: HeadersInit = {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        };

        if (!(data instanceof FormData)) {
            headers['Content-Type'] = 'application/json';
            data = data ? JSON.stringify(data) : null;
        }

        const response = await fetch(url, {
            method,
            headers,
            body: data
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw { status: response.status, data: errorData };
        }

        return response.json();
    }

    static get(url: string) { return this.request(url, 'GET'); }
    static post(url: string, data: any) { return this.request(url, 'POST', data); }
    static put(url: string, data: any) { return this.request(url, 'PUT', data); }
    static delete(url: string) { return this.request(url, 'DELETE'); }
}
