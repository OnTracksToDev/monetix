<template>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <!-- En-tête -->
                <div class="text-center mb-4">
                    <img src="/images/logo.png" alt="Logo" class="logo-img" />
                    <h1 class="h3 fw-bold mt-3">Nouveau mot de passe</h1>
                    <p class="text-muted">Créez votre nouveau mot de passe</p>
                </div>

                <!-- Formulaire -->
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <form @submit.prevent="handleReset">
                            <div class="mb-3">
                                <label class="form-label">Mot de passe</label>
                                <input
                                    v-model="password"
                                    type="password"
                                    class="form-control form-control-lg"
                                    placeholder="Saisissez votre mot de passe"
                                    required
                                    :disabled="loading"
                                />
                                <div class="form-text">
                                    Au moins 8 caractères
                                </div>
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
                                {{
                                    loading
                                        ? "Réinitialisation..."
                                        : "Réinitialiser"
                                }}
                            </button>
                        </form>

                        <!-- Message de statut -->
                        <div
                            v-if="message"
                            :class="[
                                'alert mt-3 mb-0',
                                success ? 'alert-success' : 'alert-danger',
                            ]"
                        >
                            <i
                                :class="[
                                    'me-2',
                                    success
                                        ? 'bi bi-check-circle'
                                        : 'bi bi-exclamation-triangle',
                                ]"
                            ></i>
                            {{ message }}
                        </div>

                        <!-- Lien de retour -->
                        <div class="text-center mt-3">
                            <router-link
                                to="/login"
                                class="text-decoration-none"
                            >
                                ← Retour à la connexion
                            </router-link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { API_URL } from "../services/config.js";

export default {
    name: "ResetPassword",
    data() {
        return {
            password: "",
            message: "",
            loading: false,
            success: false,
            token: null,
        };
    },
    beforeMount() {
        this.token = this.$route.params.token;
    },
    watch: {
        "$route.params.token"(newToken) {
            this.token = newToken;
        },
    },
    methods: {
        async handleReset() {
            if (this.password.length < 8) {
                this.message =
                    "Le mot de passe doit contenir au moins 8 caractères.";
                this.success = false;
                return;
            }

            this.loading = true;
            this.message = "";
            this.success = false;

            try {
                const response = await fetch(`${API_URL}/api/reset-password/reset`, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        token: this.token,
                        password: this.password,
                    }),
                });

                const data = await response.json();

                if (data.message) {
                    this.message = data.message;
                    this.success = true;

                    setTimeout(() => {
                        this.$router.push("/login");
                    }, 3000);
                } else {
                    this.message = data.error || "Une erreur est survenue.";
                    this.success = false;
                }
            } catch (error) {
                this.message = error.message || "Une erreur est survenue.";
                this.success = false;
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
