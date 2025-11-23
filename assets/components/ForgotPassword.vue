<template>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <!-- En-tête -->
                <div class="text-center mb-4">
                    <img src="/images/logo.png" alt="Logo" class="logo-img" />
                    <h1 class="h3 fw-bold mt-3">Mot de passe oublié</h1>
                    <p class="text-muted">Réinitialisez votre mot de passe</p>
                </div>

                <!-- Formulaire -->
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <form @submit.prevent="handleForgot">
                            <div class="mb-4">
                                <label class="form-label">Email</label>
                                <input
                                    v-model="email"
                                    type="email"
                                    class="form-control form-control-lg"
                                    placeholder="votre@email.com"
                                    required
                                    :disabled="loading"
                                />
                                <div class="form-text">
                                    Un lien de réinitialisation vous sera envoyé
                                    par email.
                                </div>
                            </div>

                            <!-- INFORMATIONS IMPORTANTES -->
                            <div class="alert alert-info">
                                <small>
                                    <i class="bi bi-info-circle me-1"></i>
                                    <strong>Informations importantes :</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>
                                            Le lien de réinitialisation est
                                            valable <strong>1 heure</strong>
                                        </li>
                                        <li>
                                            Vous ne pouvez faire qu'une demande
                                            toutes les
                                            <strong>15 minutes</strong>
                                        </li>
                                    </ul>
                                </small>
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
                                {{ loading ? "Envoi..." : "Envoyer le lien" }}
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
import { API_URL } from '../services/config.js';

export default {
    name: "ForgotPassword",
    data() {
        return {
            email: "",
            message: "",
            loading: false,
            success: false,
        };
    },
    methods: {
        async handleForgot() {
            if (!this.email) {
                this.message = "L'adresse e-mail est requise.";
                this.success = false;
                return;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(this.email)) {
                this.message = "Format d'e-mail invalide.";
                this.success = false;
                return;
            }

            this.loading = true;
            this.message = "";
            this.success = false;

            try {
                const response = await fetch(`${API_URL}/api/reset-password/request`, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ email: this.email }),
                });

                const data = await response.json();

                this.message =
                    data.message ||
                    "Si l'adresse e-mail existe, un lien de réinitialisation a été envoyé.";
                this.success = true;
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
