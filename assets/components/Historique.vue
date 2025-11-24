<template>
    <div class="container-custom py-3">
        <!-- Titre -->
        <div class="text-center mb-4">
            <h1 class="fw-bold text-primary display-6 mb-2">Historique</h1>
            <p class="text-muted">Vue d'ensemble des ventes</p>
        </div>

        <!-- STATISTIQUES SIMPLES -->
        <div class="row g-2 mb-4">
            <div class="col-6">
                <div
                    class="card shadow-sm text-center border-0 bg-success bg-opacity-10"
                >
                    <div class="card-body py-3">
                        <div class="text-success fw-bold fs-4">
                            {{ totalEncaisse }} €
                        </div>
                        <small class="text-muted">Encaissé</small>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div
                    class="card shadow-sm text-center border-0 bg-warning bg-opacity-10"
                >
                    <div class="card-body py-3">
                        <div class="text-warning fw-bold fs-4">
                            {{ totalCredits }} €
                        </div>
                        <small class="text-muted">Crédits</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILTRE SIMPLIFIÉ -->
        <div class="mb-4">
            <div class="dropdown">
                <button
                    class="btn btn-outline-primary w-100 rounded-pill py-3 d-flex align-items-center justify-content-between"
                    type="button"
                    data-bs-toggle="dropdown"
                >
                    <span>{{ getPeriodLabel() }}</span>
                    <i class="bi bi-chevron-down ms-2"></i>
                </button>
                <ul class="dropdown-menu w-100">
                    <li>
                        <button
                            class="dropdown-item d-flex align-items-center"
                            :class="{ active: filters.period === '24h' }"
                            @click="filters.period = '24h'"
                        >
                            <i class="bi bi-clock me-2"></i>
                            Dernières 24h
                        </button>
                    </li>
                    <li>
                        <button
                            class="dropdown-item d-flex align-items-center"
                            :class="{ active: filters.period === 'week' }"
                            @click="filters.period = 'week'"
                        >
                            <i class="bi bi-calendar-week me-2"></i>
                            Cette semaine
                        </button>
                    </li>
                    <li>
                        <button
                            class="dropdown-item d-flex align-items-center"
                            :class="{ active: filters.period === 'month' }"
                            @click="filters.period = 'month'"
                        >
                            <i class="bi bi-calendar-month me-2"></i>
                            Ce mois
                        </button>
                    </li>
                    <li>
                        <button
                            class="dropdown-item d-flex align-items-center"
                            :class="{ active: filters.period === 'all' }"
                            @click="filters.period = 'all'"
                        >
                            <i class="bi bi-infinity me-2"></i>
                            Tout
                        </button>
                    </li>
                </ul>
            </div>
        </div>
        <!-- LISTE COMPACTE -->
        <div v-if="ventesFiltrees.length > 0" class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">
                    {{ ventesFiltrees.length }} opérations
                </h6>
                <small class="text-muted">{{ getPeriodLabel() }}</small>
            </div>

            <div class="list-group list-group-flush">
                <div
                    v-for="vente in ventesFiltrees"
                    :key="vente.id"
                    class="list-group-item p-4 border-bottom"
                >
                    <div
                        class="d-flex justify-content-between align-items-start"
                    >
                        <!-- INFOS PRINCIPALES -->
                        <div class="flex-grow-1">
                            <div
                                class="d-flex justify-content-between align-items-center mt-1"
                            >
                                <span class="fw-bold"
                                    >{{ vente.montant_total }} €</span
                                >
                                <span
                                    class="badge"
                                    :class="getBadgeClass(vente.mode_paiement)"
                                >
                                    {{ getPaiementLabel(vente.mode_paiement) }}
                                </span>
                            </div>

                            <div
                                class="d-flex justify-content-between align-items-center"
                            >
                                <small class="text-muted">
                                    {{
                                        vente.adherent
                                            ? vente.adherent.nom
                                            : "Client occasionnel"
                                    }}
                                </small>
                                <small class="text-muted">
                                    {{ formatDateSimple(vente.date) }}
                                </small>
                            </div>

                            <!-- DÉTAILS COMPACT -->
                            <div class="d-flex gap-3 mt-1">
                                <small class="text-success">
                                    <i class="bi bi-cash-coin me-1"></i>
                                    {{ vente.montant_paye }} € payés
                                </small>
                                <small
                                    v-if="vente.reste_a_payer > 0"
                                    class="text-warning"
                                >
                                    <i class="bi bi-credit-card me-1"></i>
                                    {{ vente.reste_a_payer }} € dû
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AUCUNE VENTE -->
        <div v-else-if="!loading" class="text-center py-4">
            <i class="bi bi-receipt fs-1 text-muted d-block mb-2"></i>
            <p class="text-muted mb-0">Aucune opération</p>
        </div>

        <!-- CHARGEMENT -->
        <div v-if="loading" class="text-center py-4">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2 text-muted">Chargement...</p>
        </div>

        <!-- BOUTON RETOUR -->
        <div class="text-center mt-4">
            <button
                @click="$router.push('/')"
                class="btn btn-outline-secondary btn-lg w-100 rounded-pill py-4 fs-5 fw-semibold d-flex align-items-center justify-content-center"
                :disabled="loading"
            >
                <i class="bi bi-arrow-left me-2"></i>
                Retour à l'accueil
            </button>
        </div>
    </div>
</template>

<script>
import { authFetch } from "../services/http.js";
export default {
    name: "Historique",
    data() {
        return {
            ventes: [],
            adherents: [],
            filters: {
                period: "24h",
            },
            loading: false,
        };
    },
    computed: {
        ventesFiltrees() {
            return this.ventes
                .filter((vente) => this.dansPeriode(vente.date))
                .sort((a, b) => new Date(b.date) - new Date(a.date));
        },

        totalEncaisse() {
            return this.ventesFiltrees
                .reduce((sum, vente) => sum + parseFloat(vente.montant_paye), 0)
                .toFixed(2);
        },

        totalCredits() {
            return this.adherents
                .reduce(
                    (sum, adherent) =>
                        sum + parseFloat(adherent.credit_total || 0),
                    0
                )
                .toFixed(2);
        },
    },

    async mounted() {
        await this.chargerVentes();
        await this.chargerAdherents();
    },

    methods: {
        async chargerVentes() {
            this.loading = true;
            try {
                const response = await authFetch("/api/ventes");
                if (!response.ok) throw new Error("Erreur chargement");
                this.ventes = await response.json();
            } catch (error) {
                console.error("Erreur:", error);
            } finally {
                this.loading = false;
            }
        },

        async chargerAdherents() {
            try {
                const response = await authFetch("/api/adherents");
                if (!response.ok)
                    throw new Error("Erreur chargement adhérents");
                this.adherents = await response.json();
            } catch (error) {
                console.error("Erreur chargement adhérents:", error);
            }
        },

        dansPeriode(dateString) {
            const dateVente = new Date(dateString);
            const maintenant = new Date();

            switch (this.filters.period) {
                case "24h":
                    const ilYa24h = new Date(
                        maintenant.getTime() - 24 * 60 * 60 * 1000
                    );
                    return dateVente >= ilYa24h;
                case "week":
                    const debutSemaine = this.getDebutSemaine(maintenant);
                    const finSemaine = this.getFinSemaine(maintenant);
                    return dateVente >= debutSemaine && dateVente <= finSemaine;
                case "month":
                    const debutMois = this.getDebutMois(maintenant);
                    const finMois = this.getFinMois(maintenant);
                    return dateVente >= debutMois && dateVente <= finMois;
                case "all":
                    return true;
                default:
                    return true;
            }
        },

        getDebutSemaine(date) {
            const jour =
                date.getDate() - date.getDay() + (date.getDay() === 0 ? -6 : 1);
            const debut = new Date(date);
            debut.setDate(jour);
            debut.setHours(0, 0, 0, 0);
            return debut;
        },

        getFinSemaine(date) {
            const debutSemaine = this.getDebutSemaine(date);
            const fin = new Date(debutSemaine);
            fin.setDate(debutSemaine.getDate() + 6);
            fin.setHours(23, 59, 59, 999);
            return fin;
        },

        getDebutMois(date) {
            const debut = new Date(date.getFullYear(), date.getMonth(), 1);
            debut.setHours(0, 0, 0, 0);
            return debut;
        },

        getFinMois(date) {
            const fin = new Date(date.getFullYear(), date.getMonth() + 1, 0);
            fin.setHours(23, 59, 59, 999);
            return fin;
        },

        getPeriodLabel() {
            const labels = {
                "24h": "24h",
                week: "Semaine",
                month: "Mois",
                all: "Tout",
            };
            return labels[this.filters.period];
        },

        formatDateSimple(dateString) {
            const date = new Date(dateString);
            const aujourdhui = new Date();
            const hier = new Date(aujourdhui);
            hier.setDate(aujourdhui.getDate() - 1);

            if (date.toDateString() === aujourdhui.toDateString()) {
                return date.toLocaleTimeString("fr-FR", {
                    hour: "2-digit",
                    minute: "2-digit",
                });
            } else if (date.toDateString() === hier.toDateString()) {
                return (
                    "Hier " +
                    date.toLocaleTimeString("fr-FR", {
                        hour: "2-digit",
                        minute: "2-digit",
                    })
                );
            } else {
                return date.toLocaleDateString("fr-FR", {
                    day: "2-digit",
                    month: "2-digit",
                });
            }
        },

        getBadgeClass(modePaiement) {
            const classes = {
                paiement_complet: "bg-success",
                paiement_partiel: "bg-primary",
                tout_au_credit: "bg-warning text-dark",
                remboursement: "bg-info text-dark",
            };
            return classes[modePaiement] || "bg-secondary";
        },

        getPaiementLabel(modePaiement) {
            const labels = {
                paiement_complet: "PAIEMENT COMPLET",
                paiement_partiel: "PAIEMENT PARTIEL",
                tout_au_credit: "TOUT AU CRÉDIT",
                remboursement: "REMBOURSEMENT",
            };
            return labels[modePaiement] || modePaiement;
        },
    },
};
</script>

<style scoped></style>
