import { API_URL } from './config.js';
import authService from './auth.js';
import router from '../router/index.js';

export async function authFetch(url, options = {}) {
    const fullUrl = API_URL + url; 
    const authHeaders = authService.getAuthHeaders();
    
    const config = {
        ...options,
        headers: {
            'Content-Type': 'application/json',
            ...authHeaders,
            ...options.headers,
        }
    };

    try {
        const response = await fetch(fullUrl, config);
        
        if (response.status === 401) {
            authService.logout();
            router.push('/login');
            throw new Error('Session expirée - Veuillez vous reconnecter');
        }

        return response;
    } catch (error) {
        if (error.message.includes('Session expirée')) {
            throw error;
        }
        throw new Error('Erreur réseau: ' + error.message);
    }
}
