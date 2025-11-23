<template>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <!-- En-tête -->
                <div class="text-center mb-4">
                    <img src="/images/logo.png" alt="Logo" class="logo-img" />
                    <h1 class="h3 fw-bold mt-3">Buvette App</h1>
                    <p class="text-muted">Connectez-vous à votre compte</p>
                </div>

                <!-- Formulaire -->
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <form @submit.prevent="handleLogin">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input
                                    v-model="email"
                                    type="email"
                                    class="form-control form-control-lg"
                                    placeholder="votre@email.com"
                                    required
                                    :disabled="loading"
                                />
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Mot de passe</label>
                                <input
                                    v-model="password"
                                    type="password"
                                    class="form-control form-control-lg"
                                    placeholder="Votre mot de passe"
                                    required
                                    :disabled="loading"
                                />
                            </div>

                            <button
                                type="submit"
                                class="btn btn-primary btn-lg w-100 py-3 fw-bold"
                                :disabled="loading"
                            >
                                <span
                                    v-if="loading"
                                    class="spinner-border spinner-border-sm me-2"
                                ></span>
                                {{ loading ? "Connexion..." : "Se connecter" }}
                            </button>
                        </form>

                        <div class="text-center mt-3">
                            <router-link
                                to="/forgot-password"
                                class="text-decoration-none"
                            >
                                Mot de passe oublié ?
                            </router-link>
                        </div>

                        <!-- Message d'erreur -->
                        <div v-if="error" class="alert alert-danger mt-3 mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            {{ error }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import authService from "../services/auth.js";

export default {
    name: "Login",
    data() {
        return {
            email: "", 
            password: "", 
            loading: false,
            error: "",
        };
    },
    mounted() {
        const publicPaths = ["/forgot-password", "/reset-password"];
        const currentPath = this.$route.path;

        // Rediriger seulement si on est sur /login ET pas sur une route publique
        if (
            authService.isAuthenticated() &&
            !publicPaths.some((p) => currentPath.startsWith(p))
        ) {
            this.$router.push("/");
        }
    },
    methods: {
        async handleLogin() {
            this.loading = true;
            this.error = "";

            const result = await authService.login(this.email, this.password);

            if (result.success) {
                // ✅ Connexion réussie - redirection vers l'accueil
                this.$router.push("/");
            } else {
                // ❌ Erreur de connexion
                this.error = result.error;
            }

            this.loading = false;
        },
    },
};
</script>

<style scoped>
.logo-img {
    height: 72px;
    width: auto;
}
</style>
