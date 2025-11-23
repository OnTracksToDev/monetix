<template>
    <div class="container-custom py-4">
        <!-- Spinner global de chargement -->
        <div v-if="loadingInitial" class="text-center py-5">
            <div
                class="spinner-border text-primary"
                style="width: 3rem; height: 3rem"
                role="status"
            >
                <span class="visually-hidden">Chargement de la tournée...</span>
            </div>
            <p class="text-muted mt-3">Chargement de la tournée...</p>
        </div>

        <!-- Contenu principal -->
        <div v-else>
            <!-- Bouton retour -->
            <div class="text-center mb-4">
                <button
                    @click="$router.push('/')"
                    class="btn btn-outline-secondary btn-lg w-100 rounded-pill py-4 fs-5 fw-semibold d-flex align-items-center justify-content-center"
                    :disabled="loading"
                >
                    <i class="bi bi-arrow-left me-2"></i>
                    Retour à l'accueil
                </button>
            </div>

            <!-- Titre et suppression -->
            <div class="mb-4">
                <div
                    class="d-flex justify-content-between align-items-center gap-2"
                >
                    <div class="d-flex align-items-center gap-3">
                        <h1 class="fw-bold text-primary mb-0">
                            <i class="bi bi-cup-straw me-2"></i>Tournée
                        </h1>
                        <p class="fw-bold text-dark fs-2 mb-0">
                            {{ tournee.adherent?.nom || "Adhérent non trouvé" }}
                        </p>
                    </div>

                    <button
                        @click="afficherModalSuppression"
                        class="btn btn-sm btn-outline-danger rounded-pill flex-shrink-0"
                        :disabled="loading"
                        title="Supprimer cette tournée"
                    >
                        <i class="bi bi-trash me-2"></i>
                        <span class="d-inline">Supp.</span>
                    </button>
                </div>
            </div>

            <!-- Carte résumé tournée -->
            <div class="card bg-light border-0 rounded-4 mb-4">
                <div class="card-body p-3">
                    <div class="row g-3 text-center">
                        <!-- Total € -->
                        <div class="col-4">
                            <div class="fs-3 fw-bold text-primary mb-1">
                                {{ totalVentes }} €
                            </div>
                            <small class="text-muted">Total</small>
                        </div>

                        <!-- Ventes avec objectif -->
                        <div class="col-4">
                            <div class="fs-3 fw-bold text-primary mb-1">
                                {{ ventes.length }}
                                <small
                                    v-if="tournee.estimation_clients"
                                    class="fs-7 text-muted"
                                >
                                    /{{ tournee.estimation_clients }}
                                </small>
                            </div>
                            <small class="text-muted d-block">Ventes</small>
                            <!-- Indicateur mini -->
                            <div v-if="tournee.estimation_clients" class="mt-1">
                                <small
                                    class="fw-semibold"
                                    :class="couleurProgression"
                                >
                                    {{ pourcentageProgression }}%
                                </small>
                                <span
                                    v-if="surplusVentes > 0"
                                    class="text-success ms-1"
                                >
                                    +{{ surplusVentes }}
                                </span>
                            </div>
                        </div>

                        <!-- Crédit adhérent -->
                        <div class="col-4">
                            <div
                                class="fs-3 fw-bold mb-1"
                                :class="couleurCredit"
                            >
                                {{ creditAdherent }} €
                            </div>
                            <small class="text-muted">Crédit</small>
                        </div>
                    </div>

                    <!-- Barre de progression -->
                    <div v-if="tournee.estimation_clients" class="mt-3">
                        <div
                            class="d-flex justify-content-between align-items-center mb-1"
                        >
                            <small class="text-muted">Progression</small>
                            <small
                                class="fw-semibold"
                                :class="couleurProgression"
                            >
                                {{ pourcentageProgression }}%
                            </small>
                        </div>
                        <div class="progress" style="height: 8px">
                            <div
                                class="progress-bar"
                                :class="
                                    pourcentageProgression >= 100
                                        ? 'bg-success'
                                        : 'bg-primary'
                                "
                                role="progressbar"
                                :style="{
                                    width:
                                        Math.min(pourcentageProgression, 100) +
                                        '%',
                                }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste de prix optimisée -->
            <div class="mb-4">
                <h5 class="fw-semibold mb-3 text-center">
                    <i class="bi bi-tags me-2"></i>Prix rapides
                </h5>
                <div class="row g-2">
                    <div
                        class="col-6 col-sm-4"
                        v-for="prix in listePrix"
                        :key="prix"
                    >
                        <button
                            @click="ajouterVente(prix)"
                            class="btn btn-outline-primary w-100 rounded-pill py-3"
                            :disabled="loading"
                        >
                            <span class="fs-5 fw-bold">{{ prix }} €</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Saisie manuelle -->
            <div class="card mb-4">
                <div class="card-body p-3">
                    <h6 class="fw-semibold mb-3">
                        <i class="bi bi-pencil me-2"></i>Montant personnalisé
                    </h6>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light border-0"
                            >€</span
                        >
                        <input
                            ref="montantInput"
                            v-model="montantPersonnalise"
                            type="number"
                            step="0.01"
                            min="0"
                            class="form-control border-0 text-center fs-5 py-3"
                            placeholder="0.00"
                            @keyup.enter="ajouterVentePersonnalisee"
                            :disabled="loading"
                        />
                        <button
                            @click="ajouterVentePersonnalisee"
                            class="btn btn-primary border-0 px-4"
                            :class="{
                                'opacity-50':
                                    !montantPersonnalise ||
                                    montantPersonnalise <= 0,
                            }"
                            :disabled="
                                !montantPersonnalise ||
                                montantPersonnalise <= 0 ||
                                loading
                            "
                        >
                            <i class="bi bi-plus-lg fs-5"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Historique des ventes -->
            <div class="card mb-4">
                <div class="card-header bg-light border-0 py-3">
                    <h6 class="fw-semibold mb-0">
                        <i class="bi bi-clock-history me-2"></i>
                        Historique ({{ ventes.length }})
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div
                        v-if="ventes.length === 0"
                        class="text-center text-muted py-5"
                    >
                        <i
                            class="bi bi-receipt fs-1 d-block mb-2 opacity-50"
                        ></i>
                        <p class="mb-0 fs-6">Aucune vente encore</p>
                    </div>
                    <div v-else class="list-group list-group-flush">
                        <div
                            v-for="(vente, index) in ventes.slice().reverse()"
                            :key="vente.id"
                            class="list-group-item d-flex justify-content-between align-items-center border-0 py-3 px-3"
                        >
                            <div class="d-flex align-items-center">
                                <span class="text-muted me-3 fs-7">
                                    #{{ ventes.length - index }}
                                </span>
                                <span class="fw-semibold fs-6">
                                    {{ vente.montant_total }} €
                                </span>
                            </div>
                            <button
                                @click="
                                    afficherModalSuppressionVente(
                                        ventes.length - 1 - index
                                    )
                                "
                                class="btn btn-outline-danger rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 40px; height: 40px"
                                title="Supprimer cette vente"
                            >
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="row g-2">
                <div class="col-6">
                    <button
                        @click="annulerDerniereVente"
                        class="btn btn-warning btn-lg w-100 rounded-pill py-3"
                        :disabled="ventes.length === 0 || loading"
                    >
                        <i class="bi bi-arrow-counterclockwise me-2"></i>
                        Annuler
                    </button>
                </div>
                <div class="col-6">
                    <button
                        @click="cloturerTournee"
                        class="btn btn-success btn-lg w-100 rounded-pill py-3 fw-bold"
                        :disabled="ventes.length === 0 || loading"
                    >
                        <i class="bi bi-check-circle me-2"></i>
                        Clôturer
                    </button>
                </div>
            </div>

            <!-- Modal clôture -->
            <div
                v-if="showCloture"
                class="modal fade show d-block"
                style="background: rgba(0, 0, 0, 0.5)"
            >
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content rounded-4 m-2">
                        <!-- En-tête normal -->
                        <div
                            class="modal-header border-0 pb-0"
                            v-if="!clotureReussie"
                        >
                            <h5 class="modal-title fw-bold fs-5">
                                <i class="bi bi-check-circle me-2"></i>Clôturer
                            </h5>
                        </div>

                        <!-- En-tête pour succès -->
                        <div class="modal-header border-0 pb-0" v-else>
                            <h5 class="modal-title fw-bold fs-5 text-success">
                                <i class="bi bi-check-circle me-2"></i>
                                Succès
                            </h5>
                        </div>

                        <div class="modal-body">
                            <!-- Contenu normal de clôture -->
                            <div v-if="!clotureReussie">
                                <div class="text-center mb-4">
                                    <div class="fs-2 fw-bold text-primary">
                                        {{ totalVentes }} €
                                    </div>
                                    <p class="text-muted">
                                        {{ ventes.length }} ventes •
                                        {{ tournee.adherent?.nom }}
                                    </p>
                                </div>

                                <!-- MONTANT REÇU -->
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">
                                        Montant reçu en espèces
                                        <small class="text-muted d-block">
                                            (0€ = tout au crédit)
                                        </small>
                                    </label>
                                    <input
                                        v-model="montantRecu"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="form-control form-control-lg text-center py-3 rounded-pill"
                                        placeholder="0.00"
                                        @input="calculerNouvelleDette"
                                    />
                                </div>

                                <!-- RÉCAPITULATIF -->
                                <div class="alert alert-warning rounded-3">
                                    <div class="row g-2 text-center">
                                        <div class="col-6">
                                            <small class="text-muted"
                                                >Crédit actuel</small
                                            >
                                            <div class="fw-bold">
                                                {{
                                                    (
                                                        adherentActuel?.credit_total ||
                                                        0
                                                    ).toFixed(2)
                                                }}
                                                €
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted"
                                                >Montant reçu</small
                                            >
                                            <div class="fw-bold text-success">
                                                {{
                                                    (montantRecu || 0).toFixed(
                                                        2
                                                    )
                                                }}
                                                €
                                            </div>
                                        </div>
                                    </div>
                                    <hr class="my-2" />
                                    <div class="text-center">
                                        <small class="text-muted"
                                            >Nouveau crédit</small
                                        >
                                        <div class="fw-bold fs-5">
                                            {{ calculerNouvelleDette() }} €
                                        </div>
                                    </div>

                                    <!-- TYPE D'OPÉRATION -->
                                    <div class="mt-2 text-center">
                                        <span
                                            class="badge py-2 px-3"
                                            :class="typeOperationClass"
                                        >
                                            <i
                                                :class="typeOperationIcon"
                                                class="me-1"
                                            ></i>
                                            {{ typeOperation }}
                                        </span>
                                    </div>
                                </div>

                                <!-- EXPLICATION -->
                                <div class="alert alert-info rounded-3">
                                    <small>
                                        <i class="bi bi-info-circle me-1"></i>
                                        <strong>{{ typeOperation }}</strong> -
                                        <span
                                            v-if="
                                                typeOperation ===
                                                'TOUT AU CRÉDIT'
                                            "
                                        >
                                            Tournée ajoutée au crédit
                                        </span>
                                        <span
                                            v-else-if="
                                                typeOperation ===
                                                'PAIEMENT COMPLET'
                                            "
                                        >
                                            Tournée réglée en espèces
                                        </span>
                                        <span
                                            v-else-if="
                                                typeOperation ===
                                                'PAIEMENT PARTIEL'
                                            "
                                        >
                                            {{ montantRecu }} € payés +
                                            {{
                                                (
                                                    parseFloat(totalVentes) -
                                                    parseFloat(montantRecu)
                                                ).toFixed(2)
                                            }}
                                            € crédit
                                        </span>
                                        <span
                                            v-else-if="
                                                typeOperation ===
                                                'REMBOURSEMENT'
                                            "
                                        >
                                            Remboursement de
                                            {{
                                                (
                                                    parseFloat(montantRecu) -
                                                    parseFloat(totalVentes)
                                                ).toFixed(2)
                                            }}
                                            €
                                        </span>
                                    </small>
                                </div>
                            </div>

                            <!-- Contenu succès clôture -->
                            <div v-else class="text-center py-4">
                                <i
                                    class="bi bi-check-circle text-success fs-1 d-block mb-3"
                                ></i>
                                <p class="fw-semibold fs-5 text-success mb-2">
                                    Tournée clôturée
                                </p>
                                <p class="text-muted mb-3">
                                    {{ messageCloture }}
                                </p>
                                <div
                                    class="spinner-border text-primary mt-2"
                                    role="status"
                                >
                                    <span class="visually-hidden"
                                        >Redirection...</span
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Pied de modal normal -->
                        <div
                            class="modal-footer border-0 pt-0"
                            v-if="!clotureReussie"
                        >
                            <button
                                @click="showCloture = false"
                                class="btn btn-outline-secondary rounded-pill px-4 py-2"
                            >
                                Annuler
                            </button>
                            <button
                                @click="finaliserTournee"
                                class="btn btn-success rounded-pill px-4 py-2 fw-semibold"
                                :disabled="loading"
                            >
                                <span
                                    v-if="loading"
                                    class="spinner-border spinner-border-sm me-2"
                                ></span>
                                <i class="bi bi-check-lg me-2"></i>
                                {{ texteBoutonValidation }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal suppression tournée -->
            <div
                v-if="showSuppression"
                class="modal fade show d-block"
                style="background: rgba(0, 0, 0, 0.5)"
            >
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content rounded-4 m-2">
                        <div
                            class="modal-header border-0 pb-0"
                            v-if="!suppressionReussie"
                        >
                            <h5 class="modal-title fw-bold fs-5 text-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Supprimer la tournée
                            </h5>
                        </div>

                        <!-- En-tête pour succès -->
                        <div class="modal-header border-0 pb-0" v-else>
                            <h5 class="modal-title fw-bold fs-5 text-success">
                                <i class="bi bi-check-circle me-2"></i>
                                Succès
                            </h5>
                        </div>

                        <div class="modal-body">
                            <!-- Contenu normal -->
                            <div v-if="!suppressionReussie">
                                <div class="text-center mb-4">
                                    <i
                                        class="bi bi-trash text-danger fs-1 d-block mb-3"
                                    ></i>
                                    <p class="fw-semibold fs-6 mb-2">
                                        Êtes-vous sûr de vouloir supprimer cette
                                        tournée ?
                                    </p>
                                    <p class="text-muted small">
                                        Cette action est irréversible. Toutes
                                        les ventes associées seront également
                                        supprimées.
                                    </p>
                                </div>

                                <!-- Détails -->
                                <div class="alert alert-warning rounded-3">
                                    <div class="row g-2 text-center">
                                        <div class="col-6">
                                            <small class="text-muted"
                                                >Adhérent</small
                                            >
                                            <div class="fw-bold">
                                                {{
                                                    tournee.adherent?.nom ||
                                                    "N/A"
                                                }}
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted"
                                                >Ventes</small
                                            >
                                            <div class="fw-bold">
                                                {{ ventes.length }}
                                            </div>
                                        </div>
                                    </div>
                                    <hr class="my-2" />
                                    <div class="text-center">
                                        <small class="text-muted"
                                            >Montant total</small
                                        >
                                        <div class="fw-bold fs-5">
                                            {{ totalVentes }} €
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contenu succès -->
                            <div v-else class="text-center py-4">
                                <i
                                    class="bi bi-check-circle text-success fs-1 d-block mb-3"
                                ></i>
                                <p class="fw-semibold fs-5 text-success mb-2">
                                    Tournée supprimée
                                </p>
                                <p class="text-muted">
                                    Redirection vers l'accueil...
                                </p>
                            </div>
                        </div>

                        <div
                            class="modal-footer border-0 pt-0"
                            v-if="!suppressionReussie"
                        >
                            <button
                                @click="showSuppression = false"
                                class="btn btn-outline-secondary w-100 rounded-pill px-4 py-2"
                                :disabled="loading"
                            >
                                Annuler
                            </button>
                            <button
                                @click="supprimerTournee"
                                class="btn btn-danger w-100 rounded-pill px-4 py-2 fw-semibold"
                                :disabled="loading"
                            >
                                <span
                                    v-if="loading"
                                    class="spinner-border spinner-border-sm me-2"
                                ></span>
                                <i class="bi bi-trash me-2"></i>
                                Supprimer définitivement
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal suppression vente -->
            <div
                v-if="showSuppressionVente"
                class="modal fade show d-block"
                style="background: rgba(0, 0, 0, 0.5)"
            >
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content rounded-4 m-2">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold fs-5 text-danger">
                                <i class="bi bi-trash me-2"></i>
                                Supprimer la vente
                            </h5>
                        </div>

                        <div class="modal-body">
                            <div class="text-center mb-4">
                                <i
                                    class="bi bi-exclamation-triangle text-warning fs-1 d-block mb-3"
                                ></i>
                                <p class="fw-semibold fs-6 mb-2">
                                    Supprimer cette vente ?
                                </p>
                                <p class="text-muted small">
                                    Cette action ne peut pas être annulée.
                                </p>
                            </div>

                            <!-- Détails de la vente -->
                            <div
                                class="alert alert-warning rounded-3 text-center"
                            >
                                <div class="fw-bold fs-4">
                                    {{ venteASupprimer?.montant_total }} €
                                </div>
                                <small class="text-muted">
                                    Vente #{{
                                        venteASupprimerIndex !== null
                                            ? ventes.length -
                                              venteASupprimerIndex
                                            : ""
                                    }}
                                </small>
                            </div>
                        </div>

                        <div class="modal-footer border-0 pt-0">
                            <button
                                @click="showSuppressionVente = false"
                                class="btn btn-outline-secondary w-100 rounded-pill px-4 py-2"
                                :disabled="loading"
                            >
                                Annuler
                            </button>
                            <button
                                @click="confirmerSuppressionVente"
                                class="btn btn-danger w-100 rounded-pill px-4 py-2 fw-semibold"
                                :disabled="loading"
                            >
                                <span
                                    v-if="loading"
                                    class="spinner-border spinner-border-sm me-2"
                                ></span>
                                <i class="bi bi-trash me-2"></i>
                                Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { authFetch } from "../services/http.js";
export default {
    name: "TourneeDetail",
    data() {
        return {
            tournee: { adherent: { nom: "", credit_total: 0 } },
            ventes: [],
            montantPersonnalise: "",
            showCloture: false,
            showSuppression: false,
            showSuppressionVente: false,
            suppressionReussie: false,
            clotureReussie: false,
            montantRecu: 0,
            loading: false,
            loadingInitial: true,
            messageCloture: "",
            listePrix: [1.5, 2.0, 3.0, 3.5, 10.0],
            adherentActuel: null,
            venteASupprimerIndex: null,
        };
    },
    computed: {
        totalVentes() {
            return this.ventes
                .reduce(
                    (total, vente) => total + parseFloat(vente.montant_total),
                    0
                )
                .toFixed(2);
        },

        creditAdherent() {
            return this.adherentActuel?.credit_total || 0;
        },

        couleurCredit() {
            return this.creditAdherent > 0 ? "text-warning" : "text-success";
        },

        pourcentageProgression() {
            if (
                !this.tournee.estimation_clients ||
                this.tournee.estimation_clients === 0
            ) {
                return 0;
            }
            const progression =
                (this.ventes.length / this.tournee.estimation_clients) * 100;
            return Math.round(progression);
        },

        surplusVentes() {
            if (!this.tournee.estimation_clients) return 0;
            return Math.max(
                0,
                this.ventes.length - this.tournee.estimation_clients
            );
        },

        couleurProgression() {
            if (this.pourcentageProgression >= 100) return "text-success";
            return "text-muted";
        },

        typeOperation() {
            const total = parseFloat(this.totalVentes);
            const recu = parseFloat(this.montantRecu || 0);

            if (recu === 0) return "TOUT AU CRÉDIT";
            if (recu === total) return "PAIEMENT COMPLET";
            if (recu > total) return "REMBOURSEMENT";
            return "PAIEMENT PARTIEL";
        },

        typeOperationClass() {
            switch (this.typeOperation) {
                case "TOUT AU CRÉDIT":
                    return "bg-warning text-dark";
                case "PAIEMENT COMPLET":
                    return "bg-success text-white";
                case "PAIEMENT PARTIEL":
                    return "bg-primary text-white";
                case "REMBOURSEMENT":
                    return "bg-info text-dark";
                default:
                    return "bg-secondary text-white";
            }
        },

        typeOperationIcon() {
            switch (this.typeOperation) {
                case "TOUT AU CRÉDIT":
                    return "bi bi-credit-card";
                case "PAIEMENT COMPLET":
                    return "bi bi-cash-coin";
                case "PAIEMENT PARTIEL":
                    return "bi bi-cash-stack";
                case "REMBOURSEMENT":
                    return "bi bi-arrow-left-circle";
                default:
                    return "bi bi-question-circle";
            }
        },

        texteBoutonValidation() {
            const total = parseFloat(this.totalVentes);
            const recu = parseFloat(this.montantRecu || 0);

            if (this.typeOperation === "TOUT AU CRÉDIT")
                return `Ajouter ${total} € crédit`;
            if (this.typeOperation === "PAIEMENT COMPLET")
                return `Encaisser ${total} €`;
            if (this.typeOperation === "REMBOURSEMENT") {
                const rembourse = recu - total;
                return `Rembourser ${rembourse.toFixed(2)} €`;
            }
            return `Encaisser ${recu.toFixed(2)} €`;
        },

        venteASupprimer() {
            if (this.venteASupprimerIndex === null) return null;
            return this.ventes[this.venteASupprimerIndex];
        },
    },
    async mounted() {
        await this.chargerTournee();
    },
    methods: {
        async chargerTournee() {
            const tourneeId = this.$route.params.id;
            try {
                const response = await authFetch(`/api/tournees/${tourneeId}`);
                if (response.ok) {
                    this.tournee = await response.json();
                    await this.chargerVentesTournee(tourneeId);
                    await this.chargerAdherentActuel();
                } else {
                    throw new Error("Tournée non trouvée");
                }
            } catch (error) {
                console.error("Erreur chargement tournée", error);
            } finally {
                this.loadingInitial = false;
            }
        },

        async chargerVentesTournee(tourneeId) {
            try {
                const response = await authFetch(
                    `/api/tournees/${tourneeId}/ventes`
                );
                if (response.ok) {
                    const ventes = await response.json();
                    this.ventes = ventes.filter(
                        (vente) => vente.mode_paiement === "en_attente"
                    );
                } else {
                    await this.chargerVentesAlternative(tourneeId);
                }
            } catch (error) {
                console.error("Erreur chargement ventes", error);
                await this.chargerVentesAlternative(tourneeId);
            }
        },

        async chargerVentesAlternative(tourneeId) {
            try {
                const response = await authFetch("/api/ventes");
                if (response.ok) {
                    const toutesVentes = await response.json();
                    this.ventes = toutesVentes.filter(
                        (vente) =>
                            vente.tournee &&
                            vente.tournee.id == tourneeId &&
                            vente.mode_paiement === "en_attente"
                    );
                }
            } catch (error) {
                console.error("Erreur chargement ventes alternative", error);
            }
        },

        async ajouterVente(montant) {
            this.loading = true;
            try {
                const venteData = {
                    montant_total: parseFloat(montant),
                    montant_paye: 0,
                    reste_a_payer: parseFloat(montant),
                    mode_paiement: "en_attente",
                    adherent_id: this.tournee.adherent.id,
                    tournee_id: this.tournee.id,
                    date: new Date().toISOString(),
                };

                const response = await authFetch("/api/ventes", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(venteData),
                });

                if (response.ok) {
                    const venteCreee = await response.json();
                    this.ventes.push(venteCreee);
                    this.montantPersonnalise = "";
                } else {
                    throw new Error("Erreur création vente");
                }
            } catch (error) {
                console.error("Erreur ajout vente", error);
            } finally {
                this.loading = false;
            }
        },

        ajouterVentePersonnalisee() {
            if (!this.montantPersonnalise || this.montantPersonnalise <= 0)
                return;
            this.ajouterVente(this.montantPersonnalise);
        },

        async annulerDerniereVente() {
            if (this.ventes.length > 0) {
                const derniereVente = this.ventes[this.ventes.length - 1];
                await this.supprimerVenteAPI(derniereVente.id);
                this.ventes.pop();
                // Pas de message de validation
            }
        },

        afficherModalSuppressionVente(index) {
            this.venteASupprimerIndex = index;
            this.showSuppressionVente = true;
        },

        async confirmerSuppressionVente() {
            this.loading = true;
            try {
                const venteASupprimer = this.ventes[this.venteASupprimerIndex];
                await this.supprimerVenteAPI(venteASupprimer.id);
                this.ventes.splice(this.venteASupprimerIndex, 1);
                this.showSuppressionVente = false;
                this.venteASupprimerIndex = null;
                // Pas de message de validation
            } catch (error) {
                console.error("Erreur suppression vente", error);
            } finally {
                this.loading = false;
            }
        },

        async supprimerVenteAPI(venteId) {
            const response = await authFetch(`/api/ventes/${venteId}`, {
                method: "DELETE",
            });
            if (!response.ok) throw new Error("Erreur suppression vente");
        },

        async cloturerTournee() {
            this.loading = true;
            try {
                await this.chargerAdherentActuel();
                this.showCloture = true;
                this.clotureReussie = false;
                this.montantRecu = 0;
            } catch (error) {
                console.error("Erreur chargement adhérent", error);
            } finally {
                this.loading = false;
            }
        },

        async chargerAdherentActuel() {
            if (!this.tournee.adherent?.id) return;
            const response = await authFetch(
                `/api/adherents/${this.tournee.adherent.id}`
            );
            if (response.ok) {
                this.adherentActuel = await response.json();
            } else {
                this.adherentActuel = { ...this.tournee.adherent };
            }
        },

        calculerNouvelleDette() {
            if (!this.adherentActuel) return "0.00";
            const ancienneDette = parseFloat(
                this.adherentActuel.credit_total || 0
            );
            const totalTournee = parseFloat(this.totalVentes);
            const montantRecu = parseFloat(this.montantRecu || 0);
            return (ancienneDette + totalTournee - montantRecu).toFixed(2);
        },

        async finaliserTournee() {
            this.loading = true;
            try {
                const totalTournee = parseFloat(this.totalVentes);
                const montantRecu = parseFloat(this.montantRecu || 0);
                const ancienneDette = parseFloat(
                    this.adherentActuel?.credit_total || 0
                );
                const nouvelleDette = parseFloat(this.calculerNouvelleDette());

                let typeOperationFinal;
                if (montantRecu === 0) typeOperationFinal = "tout_au_credit";
                else if (montantRecu === totalTournee)
                    typeOperationFinal = "paiement_complet";
                else if (montantRecu > totalTournee)
                    typeOperationFinal = "remboursement";
                else typeOperationFinal = "paiement_partiel";

                // Mise à jour du crédit de l'adhérent
                const responseAdherent = await authFetch(
                    `/api/adherents/${this.tournee.adherent.id}`,
                    {
                        method: "PUT",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({
                            credit_total: nouvelleDette,
                            nom: this.tournee.adherent.nom,
                        }),
                    }
                );

                if (!responseAdherent.ok)
                    throw new Error("Erreur mise à jour crédit");

                this.tournee.adherent.credit_total = nouvelleDette;
                this.adherentActuel.credit_total = nouvelleDette;

                // Mise à jour des ventes
                let montantRestantAPayer = montantRecu;
                const ventesTriees = [...this.ventes].sort(
                    (a, b) => new Date(a.date) - new Date(b.date)
                );

                for (const vente of ventesTriees) {
                    const montantVente = parseFloat(vente.montant_total);
                    let montantPayeVente, resteAPayerVente, modePaiementVente;

                    if (typeOperationFinal === "paiement_complet") {
                        montantPayeVente = montantVente;
                        resteAPayerVente = 0;
                        modePaiementVente = "paiement_complet";
                    } else if (typeOperationFinal === "tout_au_credit") {
                        montantPayeVente = 0;
                        resteAPayerVente = montantVente;
                        modePaiementVente = "tout_au_credit";
                    } else if (typeOperationFinal === "remboursement") {
                        montantPayeVente = montantVente;
                        resteAPayerVente = 0;
                        modePaiementVente = "paiement_complet";
                    } else {
                        if (montantRestantAPayer >= montantVente) {
                            montantPayeVente = montantVente;
                            resteAPayerVente = 0;
                            modePaiementVente = "paiement_complet";
                            montantRestantAPayer -= montantVente;
                        } else if (montantRestantAPayer > 0) {
                            montantPayeVente = montantRestantAPayer;
                            resteAPayerVente =
                                montantVente - montantRestantAPayer;
                            modePaiementVente = "paiement_partiel";
                            montantRestantAPayer = 0;
                        } else {
                            montantPayeVente = 0;
                            resteAPayerVente = montantVente;
                            modePaiementVente = "tout_au_credit";
                        }
                    }

                    await authFetch(`/api/ventes/${vente.id}`, {
                        method: "PUT",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({
                            mode_paiement: modePaiementVente,
                            montant_paye: montantPayeVente,
                            reste_a_payer: resteAPayerVente,
                        }),
                    });
                }

                // Mise à jour de la tournée
                const responseTournee = await authFetch(
                    `/api/tournees/${this.tournee.id}`,
                    {
                        method: "PUT",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({
                            montant_total: totalTournee,
                            montant_paye: montantRecu,
                            reste_a_payer: Math.max(
                                0,
                                totalTournee - montantRecu
                            ),
                            date_fin: new Date().toISOString(),
                            statut: "cloturee",
                        }),
                    }
                );

                if (!responseTournee.ok)
                    throw new Error("Erreur mise à jour tournée");

                // ✅ SUCCÈS - Affichage du message de validation dans la modal
                this.clotureReussie = true;

                // Génération du message de confirmation
                switch (typeOperationFinal) {
                    case "tout_au_credit":
                        this.messageCloture = `Tournée ajoutée au crédit : ${totalTournee} € → Dette: ${nouvelleDette.toFixed(
                            2
                        )} €`;
                        break;
                    case "paiement_complet":
                        this.messageCloture = `Paiement complet : ${totalTournee} € encaissés → Dette: ${nouvelleDette.toFixed(
                            2
                        )} €`;
                        break;
                    case "paiement_partiel":
                        this.messageCloture = `Paiement partiel : ${montantRecu} € payés + ${(
                            totalTournee - montantRecu
                        ).toFixed(2)} € crédit → Dette: ${nouvelleDette.toFixed(
                            2
                        )} €`;
                        break;
                    case "remboursement":
                        const rembourse = montantRecu - totalTournee;
                        this.messageCloture = `Remboursement : ${rembourse.toFixed(
                            2
                        )} € remboursés → Dette: ${nouvelleDette.toFixed(2)} €`;
                        break;
                }

                // Redirection vers l'accueil après 2 secondes
                setTimeout(() => {
                    this.showCloture = false;
                    this.$router.push("/");
                }, 2000);
            } catch (error) {
                console.error("Erreur finalisation", error);
                this.showCloture = false;
            } finally {
                this.loading = false;
            }
        },

        afficherModalSuppression() {
            this.showSuppression = true;
            this.suppressionReussie = false;
        },

        async supprimerTournee() {
            this.loading = true;
            try {
                // Supprimer d'abord toutes les ventes associées
                for (const vente of this.ventes) {
                    await this.supprimerVenteAPI(vente.id);
                }

                // Puis supprimer la tournée elle-même
                const response = await authFetch(
                    `/api/tournees/${this.tournee.id}`,
                    {
                        method: "DELETE",
                    }
                );

                if (response.ok) {
                    // ✅ SUCCÈS
                    this.loading = false;
                    this.suppressionReussie = true;

                    // Fermeture progressive après 1.5 secondes
                    setTimeout(() => {
                        this.showSuppression = false;
                        this.$router.push("/");
                    }, 1500);
                } else {
                    throw new Error(
                        "Erreur lors de la suppression de la tournée"
                    );
                }
            } catch (error) {
                console.error("Erreur suppression tournée", error);
                this.loading = false;
                this.showSuppression = false;
            }
        },
    },
};
</script>

<style scoped></style>
