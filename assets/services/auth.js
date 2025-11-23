import { API_URL } from './config.js';

class AuthService {

    // Connexion
    async login(email, password) {
        try {
            const response = await fetch(`${API_URL}/api/login`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || 'Email ou mot de passe incorrect');
            }

            const data = await response.json();
            localStorage.setItem('jwt_token', data.token);
            localStorage.setItem('user', JSON.stringify(data.user));

            return { success: true, user: data.user };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }

    // Déconnexion
    logout() {
        localStorage.removeItem('jwt_token');
        localStorage.removeItem('user');
    }

    // TOUJOURS relire localStorage
    isAuthenticated() {
        return !!localStorage.getItem('jwt_token');
    }

    getAuthHeaders() {
        const token = localStorage.getItem('jwt_token');
        return token ? { 'Authorization': `Bearer ${token}` } : {};
    }

    getCurrentUser() {
        return JSON.parse(localStorage.getItem('user'));
    }
}

export default new AuthService();
