<template>
    <div class="container-custom py-4">
        <!-- Titre -->
        <div class="text-center mb-4">
            <h1 class="fw-bold text-primary display-5 mb-2">Caisse</h1>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center py-5">
            <div
                class="spinner-border text-primary"
                style="width: 3rem; height: 3rem"
                role="status"
            ></div>
            <p class="mt-3 fs-5 text-muted">Chargement...</p>
        </div>

        <!-- Contenu principal -->
        <div v-else class="pb-4">
            <!-- ARGENT EN CAISSE -->
            <div class="card mb-4">
                <div class="card-body p-4 text-center">
                    <h4 class="fw-bold mb-3">
                        <i class="bi bi-calculator me-2"></i>Comptage caisse
                    </h4>

                    <!-- Indication de l'argent théorique -->
                    <div class="mb-2 text-muted fs-6">
                        Argent théorique : {{ argentTheoriqueCaisse }} €
                    </div>

                    <input
                        v-model="caisseReelle"
                        type="number"
                        step="0.01"
                        class="form-control form-control-lg text-center fw-bold border-0 bg-light py-3"
                        placeholder="0.00"
                        @input="sauvegarderCaisseReelle"
                    />
                    <div
                        v-if="caisseReelle"
                        class="mt-3"
                        :class="ecart >= 0 ? 'text-success' : 'text-danger'"
                    >
                        <span class="fw-bold fs-5">
                            {{ ecart > 0 ? "+" : "" }}{{ ecart }} €
                            {{ ecart > 0 ? "en trop" : "manquant" }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- EXPLICATION DU CALCUL -->
            <div class="card mb-4">
                <div class="card-body p-2 text-center">
                    <small class="text-muted">
                        <strong>Argent théorique :</strong><br />
                        Encaissements ({{ totalEncaissements }} €) - Dépenses
                        ({{ totalDepenses }} €) = {{ argentTheoriqueCaisse }} €
                    </small>
                </div>
            </div>
            <!-- SITUATION FINANCIÈRE -->
            <div class="card mb-4">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4">
                        <i class="bi bi-calculator me-2"></i>Situation
                        financière
                    </h4>

                    <!-- CHIFFRE D'AFFAIRES -->
                    <div
                        class="d-flex justify-content-between align-items-center mb-3 p-3 bg-primary bg-opacity-10 rounded"
                    >
                        <div class="d-flex align-items-center">
                            <i
                                class="bi bi-graph-up-arrow text-primary fs-4 me-3"
                            ></i>
                            <div>
                                <div class="fs-5 fw-bold">
                                    Chiffre d'affaires
                                </div>
                                <small class="text-muted"
                                    >Total des ventes (tournée(s) en cours
                                    comprises)
                                </small>
                            </div>
                        </div>
                        <strong class="text-primary fs-4"
                            >{{ totalVentes }} €</strong
                        >
                    </div>

                    <!-- DÉCOMPOSITION DU CHIFFRE D'AFFAIRES -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div
                                class="bg-success bg-opacity-10 rounded p-2 text-center"
                            >
                                <small class="text-muted">Total Encaissé</small>
                                <div class="fw-bold text-success">
                                    {{ totalEncaissements }} €
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div
                                class="bg-warning bg-opacity-10 rounded p-2 text-center"
                            >
                                <small class="text-muted"
                                    >Total Crédits adhérents</small
                                >
                                <div class="fw-bold text-warning">
                                    {{ totalCredits }} €
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DÉPENSES RÉELLES -->
                    <div
                        class="d-flex justify-content-between align-items-center mb-3 p-3 bg-danger bg-opacity-10 rounded"
                    >
                        <div class="d-flex align-items-center">
                            <i
                                class="bi bi-arrow-up-right text-danger fs-4 me-3"
                            ></i>
                            <div>
                                <div class="fs-5 fw-bold">Dépenses réelles</div>
                                <small class="text-muted"
                                    >Dépenses déjà effectuées</small
                                >
                            </div>
                        </div>
                        <strong class="text-danger fs-4"
                            >- {{ totalDepenses }} €</strong
                        >
                    </div>

                    <!-- DETTES GESTIONNAIRES (OBLIGATIONS FUTURES) -->
                    <div
                        class="d-flex justify-content-between align-items-center mb-3 p-3 bg-warning bg-opacity-10 rounded"
                    >
                        <div class="d-flex align-items-center">
                            <i
                                class="bi bi-cash-coin text-warning fs-4 me-3"
                            ></i>
                            <div>
                                <div class="fs-5 fw-bold">
                                    Dettes gestionnaires
                                </div>
                                <small class="text-muted"
                                    >Montant à rembourser</small
                                >
                            </div>
                        </div>
                        <strong class="text-warning fs-4"
                            >- {{ totalDettesGestionnaires }} €</strong
                        >
                    </div>

                    <!-- BÉNÉFICE NET-->
                    <div
                        class="d-flex justify-content-between align-items-center p-3 rounded fw-bold fs-5"
                        :class="
                            beneficeNet >= 0
                                ? 'bg-success text-white'
                                : 'bg-danger text-white'
                        "
                    >
                        <div class="d-flex align-items-center">
                            <i
                                :class="
                                    beneficeNet >= 0
                                        ? 'bi bi-currency-euro'
                                        : 'bi bi-exclamation-triangle'
                                "
                                class="me-3"
                            ></i>
                            <span>Bénéfice net</span>
                        </div>
                        <strong>{{ beneficeNet }} €</strong>
                    </div>

                    <!-- LÉGENDE -->
                    <div class="mt-3 text-center">
                        <small class="text-muted">
                            Bénéfice = Chiffre d'affaires - (Dépenses + Dettes
                            gestionnaires)
                        </small>
                    </div>
                </div>
            </div>

            <!-- ACTIONS -->
            <div class="card mb-4">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4">
                        <i class="bi bi-lightning me-2"></i>Actions
                    </h4>
                    <div class="row g-3">
                        <!-- Dépense buvette (principale) -->
                        <div class="col-12">
                            <button
                                @click="ouvrirModalDepense"
                                class="btn btn-primary btn-lg w-100 rounded-pill shadow-sm py-4 fs-4 fw-bold d-flex align-items-center justify-content-center"
                            >
                                <i class="bi bi-currency-euro me-3"></i>
                                Dépense buvette
                            </button>
                        </div>
                        <!-- Avance gestionnaire (secondaire) -->
                        <div class="col-12">
                            <button
                                @click="ouvrirModalAvance"
                                class="btn btn-warning btn-lg w-100 rounded-pill shadow-sm py-4 fs-4 fw-bold d-flex align-items-center justify-content-center"
                            >
                                <i class="bi bi-cash me-2"></i>
                                Avance gestionnaire
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DETTES SIMPLES -->
            <div v-if="gestionnairesAvecDettes.length > 0" class="card mb-4">
                <div class="card-body p-4">
                    <div
                        class="d-flex justify-content-between align-items-center mb-4"
                    >
                        <h4 class="fw-bold mb-0">
                            <i class="bi bi-receipt me-2"></i>Dettes à
                            rembourser
                        </h4>
                        <span
                            class="badge bg-warning fs-5 py-2 d-inline-flex align-items-center"
                        >
                            <i class="bi bi-credit-card me-1"></i>
                            {{ totalDettesGestionnaires }} €
                        </span>
                    </div>

                    <div
                        v-for="gestionnaire in gestionnairesAvecDettes"
                        :key="gestionnaire.nom"
                        class="mb-3"
                    >
                        <div
                            class="d-flex justify-content-between align-items-center p-3 bg-light rounded"
                        >
                            <div>
                                <strong class="fs-5">{{
                                    gestionnaire.nom
                                }}</strong>
                                <div class="text-warning fw-bold fs-5">
                                    {{ gestionnaire.montant }} €
                                </div>
                            </div>
                            <button
                                @click="ouvrirRemboursement(gestionnaire)"
                                class="btn btn-secondary btn-lg rounded-pill py-2 px-4 d-flex align-items-center"
                            >
                                <i class="bi bi-credit-card me-2"></i>
                                Rembourser
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DERNIÈRES OPÉRATIONS -->
            <div class="card mb-4">
                <div class="card-body p-4">
                    <!-- En-tête avec boutons groupés -->
                    <div
                        class="d-flex justify-content-between align-items-center mb-3"
                    >
                        <h4 class="fw-bold mb-0">
                            <i class="bi bi-list-check me-2"></i>Dernières
                            opérations de gestion
                        </h4>
                        <div class="d-flex gap-2">
                            <button
                                @click="rafraichirMouvements"
                                :disabled="refreshing"
                                class="btn btn-outline-secondary btn-lg rounded-pill py-2 px-3 d-flex align-items-center"
                                title="Rafraîchir"
                            >
                                <span
                                    v-if="refreshing"
                                    class="spinner-border spinner-border-sm me-1"
                                ></span>
                                <i v-else class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                    </div>

                    <div v-if="mouvementsRecents.length > 0">
                        <div
                            v-for="mouvement in mouvementsRecents"
                            :key="mouvement.id"
                            class="d-flex justify-content-between align-items-center py-3 border-bottom"
                        >
                            <div class="d-flex align-items-center">
                                <i
                                    :class="getMouvementIcon(mouvement.type)"
                                    class="me-3 fs-4"
                                ></i>
                                <div>
                                    <div class="fw-bold fs-5">
                                        {{ mouvement.montant }} €
                                    </div>
                                    <small class="text-muted fs-6">{{
                                        mouvement.gestionnaire
                                    }}</small>
                                    <small
                                        v-if="mouvement.description"
                                        class="d-block text-muted fs-6"
                                    >
                                        {{ mouvement.description }}
                                    </small>
                                    <!-- ÉTAT DU REMBOURSEMENT POUR LES AVANCES -->
                                    <div
                                        v-if="mouvement.type === 'avance'"
                                        class="mt-2"
                                    >
                                        <span
                                            v-if="mouvement.rembourse"
                                            class="badge bg-success fs-6 d-inline-flex align-items-center"
                                        >
                                            <i
                                                class="bi bi-check-circle me-1"
                                            ></i>
                                            Remboursé
                                        </span>
                                        <span
                                            v-else-if="
                                                mouvement.montantRemboursePartiel >
                                                0
                                            "
                                            class="badge bg-info fs-6 d-inline-flex align-items-center"
                                        >
                                            <i
                                                class="bi bi-arrow-repeat me-1"
                                            ></i>
                                            {{
                                                getResteARembourser(mouvement)
                                            }}€ restant
                                        </span>
                                        <span
                                            v-else
                                            class="badge bg-warning fs-6 d-inline-flex align-items-center"
                                        >
                                            <i class="bi bi-clock me-1"></i>
                                            À rembourser
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block fs-6">
                                    {{ formatDateMobile(mouvement.date) }}
                                </small>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-4 text-muted fs-5">
                        Aucun mouvement
                    </div>
                </div>
            </div>

            <!-- Bouton historique complet au-dessus du retour -->
            <div class="text-center mb-3">
                <button
                    @click="voirHistoriqueComplet"
                    class="btn btn-outline-secondary btn-sm w-100 rounded-pill py-3 fs-6 fw-semibold d-flex align-items-center justify-content-center"
                >
                    <i class="bi bi-graph-up-arrow me-2"></i>
                    Voir l'historique complet
                </button>
            </div>

            <!-- BOUTON RETOUR -->
            <div class="text-center">
                <button
                    @click="$router.push('/')"
                    class="btn btn-outline-secondary btn-lg w-100 rounded-pill py-4 fs-5 fw-semibold d-flex align-items-center justify-content-center"
                >
                    <i class="bi bi-arrow-left me-2"></i>
                    Retour à l'accueil
                </button>
            </div>
        </div>

        <!-- MODAL REMBOURSEMENT -->
        <div
            v-if="showModalRemboursement"
            class="modal fade show d-block"
            style="background: rgba(0, 0, 0, 0.5)"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title fw-bold">
                            <i class="bi bi-credit-card me-2"></i>
                            Rembourser {{ gestionnaireSelectionne.nom }}
                        </h4>
                        <button
                            type="button"
                            class="btn-close"
                            @click="fermerModalRemboursement"
                        ></button>
                    </div>
                    <div class="modal-body text-center">
                        <div
                            class="h2 text-warning mb-4 d-flex align-items-center justify-content-center"
                        >
                            <i class="bi bi-credit-card me-3"></i>
                            {{ gestionnaireSelectionne.montant }} € à rembourser
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold fs-5"
                                >Montant :</label
                            >
                            <input
                                v-model="montantRemboursement"
                                type="number"
                                step="0.01"
                                class="form-control form-control-lg text-center fw-bold py-3"
                                placeholder="0.00"
                            />
                        </div>

                        <div class="form-check mb-4 text-start">
                            <input
                                v-model="remboursementTotal"
                                class="form-check-input"
                                type="checkbox"
                            />
                            <label class="form-check-label fw-bold fs-5">
                                Tout rembourser ({{
                                    gestionnaireSelectionne.montant
                                }}
                                €)
                            </label>
                        </div>

                        <div
                            class="alert alert-light border fs-5"
                            v-if="montantRemboursementValide"
                        >
                            <div class="fw-bold">
                                <span v-if="!remboursementTotal">
                                    Vous donnez :
                                    <span class="text-success"
                                        >{{ montantRemboursement }} €</span
                                    ><br />
                                    Reste dû :
                                    <span class="text-warning"
                                        >{{
                                            (
                                                gestionnaireSelectionne.montant -
                                                parseFloat(montantRemboursement)
                                            ).toFixed(2)
                                        }}
                                        €</span
                                    >
                                </span>
                                <span
                                    v-else
                                    class="text-success d-flex align-items-center justify-content-center"
                                >
                                    <i class="bi bi-check-circle me-2"></i>
                                    Dette soldée
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="row g-2 w-100">
                            <div class="col-6">
                                <button
                                    type="button"
                                    class="btn btn-secondary btn-lg w-100 rounded-pill py-3 d-flex align-items-center justify-content-center"
                                    @click="fermerModalRemboursement"
                                >
                                    <i class="bi bi-x-circle me-2"></i>
                                    Annuler
                                </button>
                            </div>
                            <div class="col-6">
                                <button
                                    type="button"
                                    class="btn btn-success btn-lg w-100 rounded-pill py-3 fw-bold d-flex align-items-center justify-content-center"
                                    @click="confirmerRemboursement"
                                    :disabled="
                                        !montantRemboursementValide ||
                                        submitting
                                    "
                                >
                                    <span
                                        v-if="submitting"
                                        class="spinner-border spinner-border-sm me-2"
                                    ></span>
                                    <i
                                        v-else
                                        class="bi bi-check-circle me-2"
                                    ></i>
                                    {{
                                        submitting ? "Traitement..." : "Valider"
                                    }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL DÉPENSE -->
        <div
            v-if="showModalDepense"
            class="modal fade show d-block"
            style="background: rgba(0, 0, 0, 0.5)"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title fw-bold">
                            <i class="bi bi-currency-euro me-2"></i>
                            Nouvelle dépense buvette
                        </h4>
                        <button
                            type="button"
                            class="btn-close"
                            @click="fermerModalDepense"
                        ></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-4">
                            <label class="form-label fw-bold fs-5"
                                >Montant :</label
                            >
                            <input
                                v-model="nouvelleDepense.montant"
                                type="number"
                                step="0.01"
                                class="form-control form-control-lg text-center fw-bold py-3"
                                placeholder="0.00"
                            />
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold fs-5"
                                >Gestionnaire :</label
                            >
                            <input
                                v-model="nouvelleDepense.gestionnaire"
                                type="text"
                                class="form-control form-control-lg text-center py-3"
                                placeholder="Nom du gestionnaire"
                            />
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold fs-5"
                                >Description :</label
                            >
                            <input
                                v-model="nouvelleDepense.description"
                                type="text"
                                class="form-control form-control-lg text-center py-3"
                                placeholder="Description (optionnel)"
                            />
                        </div>

                        <div
                            v-if="nouvelleDepense.montant"
                            class="alert alert-info border fs-5 text-center"
                        >
                            <div class="fw-bold">
                                Montant :
                                <span class="text-primary"
                                    >{{ nouvelleDepense.montant }} €</span
                                >
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="row g-2 w-100">
                            <div class="col-6">
                                <button
                                    type="button"
                                    class="btn btn-secondary btn-lg w-100 rounded-pill py-3 d-flex align-items-center justify-content-center"
                                    @click="fermerModalDepense"
                                >
                                    <i class="bi bi-x-circle me-2"></i>
                                    Annuler
                                </button>
                            </div>
                            <div class="col-6">
                                <button
                                    type="button"
                                    class="btn btn-primary btn-lg w-100 rounded-pill py-3 fw-bold d-flex align-items-center justify-content-center"
                                    @click="confirmerDepense"
                                    :disabled="!depenseValide || submitting"
                                >
                                    <span
                                        v-if="submitting"
                                        class="spinner-border spinner-border-sm me-2"
                                    ></span>
                                    <i
                                        v-else
                                        class="bi bi-check-circle me-2"
                                    ></i>
                                    {{
                                        submitting
                                            ? "Enregistrement..."
                                            : "Valider"
                                    }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL AVANCE -->
        <div
            v-if="showModalAvance"
            class="modal fade show d-block"
            style="background: rgba(0, 0, 0, 0.5)"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title fw-bold">
                            <i class="bi bi-cash me-2"></i>
                            Nouvelle avance gestionnaire
                        </h4>
                        <button
                            type="button"
                            class="btn-close"
                            @click="fermerModalAvance"
                        ></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-4">
                            <label class="form-label fw-bold fs-5"
                                >Montant :</label
                            >
                            <input
                                v-model="nouvelleAvance.montant"
                                type="number"
                                step="0.01"
                                class="form-control form-control-lg text-center fw-bold py-3"
                                placeholder="0.00"
                            />
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold fs-5"
                                >Gestionnaire :</label
                            >
                            <input
                                v-model="nouvelleAvance.gestionnaire"
                                type="text"
                                class="form-control form-control-lg text-center py-3"
                                placeholder="Nom du gestionnaire"
                            />
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold fs-5"
                                >Description :</label
                            >
                            <input
                                v-model="nouvelleAvance.description"
                                type="text"
                                class="form-control form-control-lg text-center py-3"
                                placeholder="Description (optionnel)"
                            />
                        </div>

                        <div
                            v-if="nouvelleAvance.montant"
                            class="alert alert-warning border fs-5 text-center"
                        >
                            <div class="fw-bold">
                                Montant :
                                <span class="text-warning"
                                    >{{ nouvelleAvance.montant }} €</span
                                >
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="row g-2 w-100">
                            <div class="col-6">
                                <button
                                    type="button"
                                    class="btn btn-secondary btn-lg w-100 rounded-pill py-3 d-flex align-items-center justify-content-center"
                                    @click="fermerModalAvance"
                                >
                                    <i class="bi bi-x-circle me-2"></i>
                                    Annuler
                                </button>
                            </div>
                            <div class="col-6">
                                <button
                                    type="button"
                                    class="btn btn-warning btn-lg w-100 rounded-pill py-3 fw-bold d-flex align-items-center justify-content-center"
                                    @click="confirmerAvance"
                                    :disabled="!avanceValide || submitting"
                                >
                                    <span
                                        v-if="submitting"
                                        class="spinner-border spinner-border-sm me-2"
                                    ></span>
                                    <i
                                        v-else
                                        class="bi bi-check-circle me-2"
                                    ></i>
                                    {{
                                        submitting
                                            ? "Enregistrement..."
                                            : "Valider"
                                    }}
                                </button>
                            </div>
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
    name: "Caisse",
    data() {
        return {
            ventes: [],
            mouvements: [],
            adherents: [],
            caisseReelle: "",
            loading: true,
            refreshing: false,
            submitting: false,

            // Modales
            showModalRemboursement: false,
            showModalDepense: false,
            showModalAvance: false,

            // Données pour les modales
            gestionnaireSelectionne: null,
            montantRemboursement: "",
            remboursementTotal: false,

            nouvelleDepense: {
                montant: "",
                gestionnaire: "",
                description: "Dépense buvette",
            },

            nouvelleAvance: {
                montant: "",
                gestionnaire: "",
                description: "Avance gestionnaire",
            },
        };
    },
    computed: {
        totalVentes() {
            return this.ventes
                .reduce(
                    (sum, vente) => sum + parseFloat(vente.montant_total || 0),
                    0
                )
                .toFixed(2);
        },
        totalEncaissements() {
            return this.ventes
                .reduce(
                    (sum, vente) => sum + parseFloat(vente.montant_paye || 0),
                    0
                )
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

        totalDepenses() {
            const depenses = this.mouvements
                .filter(
                    (m) => m.type === "depense" || m.type === "remboursement"
                )
                .reduce((sum, m) => sum + parseFloat(m.montant || 0), 0);
            return depenses.toFixed(2);
        },

        argentTheoriqueCaisse() {
            const encaissements = parseFloat(this.totalEncaissements);
            const depensesEtRemboursements = parseFloat(this.totalDepenses);

            return (encaissements - depensesEtRemboursements).toFixed(2);
        },

        ecart() {
            if (!this.caisseReelle) return 0;
            return (
                parseFloat(this.caisseReelle) -
                parseFloat(this.argentTheoriqueCaisse)
            ).toFixed(2);
        },

        beneficeNet() {
            const benefice =
                parseFloat(this.totalVentes) -
                (parseFloat(this.totalDepenses) +
                    parseFloat(this.totalDettesGestionnaires));
            return benefice.toFixed(2);
        },

        totalDettesGestionnaires() {
            const avances = this.mouvements.filter(
                (m) => m.type === "avance" && !m.rembourse
            );
            const total = avances.reduce(
                (sum, m) =>
                    sum +
                    (parseFloat(m.montant) -
                        (parseFloat(m.montantRemboursePartiel) || 0)),
                0
            );
            return total.toFixed(2);
        },
        gestionnairesAvecDettes() {
            const gestionnaires = {};
            this.mouvements
                .filter((m) => m.type === "avance" && !m.rembourse)
                .forEach((m) => {
                    if (!gestionnaires[m.gestionnaire]) {
                        gestionnaires[m.gestionnaire] = {
                            nom: m.gestionnaire,
                            montant: 0,
                        };
                    }
                    const montantRestant =
                        parseFloat(m.montant) -
                        (parseFloat(m.montantRemboursePartiel) || 0);
                    gestionnaires[m.gestionnaire].montant += montantRestant;
                });
            return Object.values(gestionnaires);
        },
        mouvementsRecents() {
            return this.mouvements
                .sort((a, b) => new Date(b.date) - new Date(a.date))
                .slice(0, 8);
        },
        montantRemboursementValide() {
            if (this.remboursementTotal) return true;
            const montant = parseFloat(this.montantRemboursement);
            return (
                montant > 0 &&
                montant <=
                    parseFloat(this.gestionnaireSelectionne?.montant || 0)
            );
        },
        depenseValide() {
            const montant = parseFloat(this.nouvelleDepense.montant);
            return (
                montant > 0 && this.nouvelleDepense.gestionnaire.trim() !== ""
            );
        },
        avanceValide() {
            const montant = parseFloat(this.nouvelleAvance.montant);
            return (
                montant > 0 && this.nouvelleAvance.gestionnaire.trim() !== ""
            );
        },
    },

    watch: {
        remboursementTotal(newVal) {
            if (newVal && this.gestionnaireSelectionne) {
                this.montantRemboursement =
                    this.gestionnaireSelectionne.montant;
            } else {
                this.montantRemboursement = "";
            }
        },
    },

    async mounted() {
        await this.chargerDonnees();
        this.chargerCaisseReelle();
    },

    methods: {
        // MÉTHODE SIMPLE POUR LE RESTE À REMBOURSER
        getResteARembourser(avance) {
            const montantTotal = parseFloat(avance.montant);
            const dejaRembourse =
                parseFloat(avance.montantRemboursePartiel) || 0;
            return (montantTotal - dejaRembourse).toFixed(2);
        },

        // Méthode pour voir l'historique complet
        voirHistoriqueComplet() {
            this.$router.push("/caisse-historique-complet");
        },

        async chargerDonnees() {
            this.loading = true;
            try {
                await this.chargerVentes();
                await this.chargerMouvements();
                await this.chargerAdherents();
            } catch (error) {
                console.error("Erreur chargement:", error);
                this.$toast.error("Erreur chargement");
            } finally {
                this.loading = false;
            }
        },

        async chargerAdherents() {
            try {
                const response = await authFetch("/api/adherents");
                if (response.ok) {
                    this.adherents = await response.json();
                } else {
                    throw new Error("Erreur API adhérents");
                }
            } catch (error) {
                console.error("Erreur chargement adhérents:", error);
                throw error;
            }
        },

        async chargerVentes() {
            try {
                const response = await authFetch("/api/ventes");
                if (response.ok) {
                    this.ventes = await response.json();
                } else {
                    throw new Error("Erreur API");
                }
            } catch (error) {
                console.error("Erreur chargement ventes:", error);
                throw error;
            }
        },

        async chargerMouvements() {
            try {
                const response = await authFetch("/api/mouvements-caisse");
                if (response.ok) {
                    this.mouvements = await response.json();
                } else {
                    throw new Error("Erreur API");
                }
            } catch (error) {
                console.error("Erreur chargement mouvements:", error);
                throw error;
            }
        },

        // Rafraîchir avec état de chargement
        async rafraichirMouvements() {
            this.refreshing = true;
            try {
                await this.chargerMouvements();
                this.$toast.success("Rafraîchi");
            } catch (error) {
                this.$toast.error("Erreur rafraîchissement");
            } finally {
                this.refreshing = false;
            }
        },

        chargerCaisseReelle() {
            const savedCaisse = localStorage.getItem("caisseReelle");
            if (savedCaisse) {
                this.caisseReelle = savedCaisse;
            }
        },

        sauvegarderCaisseReelle() {
            localStorage.setItem("caisseReelle", this.caisseReelle);
        },

        // MODALES POUR AVANCE - VERSION CORRECTE
        ouvrirModalAvance() {
            this.nouvelleAvance = {
                montant: "",
                gestionnaire: "",
                description: "Avance gestionnaire",
            };
            this.showModalAvance = true;
        },

        fermerModalAvance() {
            this.showModalAvance = false;
            this.nouvelleAvance = {
                montant: "",
                gestionnaire: "",
                description: "Avance gestionnaire",
            };
        },

        async confirmerAvance() {
            if (!this.avanceValide || this.submitting) return;

            this.submitting = true;

            try {
                const response = await authFetch(
                    "/api/mouvements-caisse/create",
                    {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({
                            type: "avance",
                            montant: parseFloat(this.nouvelleAvance.montant),
                            gestionnaire:
                                this.nouvelleAvance.gestionnaire.trim(),
                            description:
                                this.nouvelleAvance.description || "Avance",
                        }),
                    }
                );

                // ✅ VÉRIFICATION SIMPLIFIÉE
                if (response.ok) {
                    const result = await response.json();
                    this.mouvements.unshift(result);
                    this.fermerModalAvance();
                }
            } catch (error) {
                console.error("Erreur:", error);
            } finally {
                this.submitting = false;
            }
        },
        // MODALES POUR DÉPENSE
        ouvrirModalDepense() {
            this.nouvelleDepense = {
                montant: "",
                gestionnaire: "",
                description: "Dépense buvette",
            };
            this.showModalDepense = true;
        },

        fermerModalDepense() {
            this.showModalDepense = false;
            this.nouvelleDepense = {
                montant: "",
                gestionnaire: "",
                description: "Dépense buvette",
            };
        },

        async confirmerDepense() {
            if (!this.depenseValide || this.submitting) return;

            this.submitting = true;

            try {
                const response = await authFetch(
                    "/api/mouvements-caisse/create",
                    {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({
                            type: "depense",
                            montant: parseFloat(this.nouvelleDepense.montant),
                            gestionnaire:
                                this.nouvelleDepense.gestionnaire.trim(),
                            description:
                                this.nouvelleDepense.description || "Dépense",
                        }),
                    }
                );

                if (response.ok) {
                    const result = await response.json();
                    this.mouvements.unshift(result);
                    this.fermerModalDepense(); // ✅ C'EST LA SEULE LIGNE IMPORTANTE
                }
            } catch (error) {
                console.error("Erreur:", error);
            } finally {
                this.submitting = false;
            }
        },

        // MODALE REMBOURSEMENT - VERSION CORRECTE
        ouvrirRemboursement(gestionnaire) {
            this.gestionnaireSelectionne = gestionnaire;
            this.montantRemboursement = "";
            this.remboursementTotal = false;
            this.showModalRemboursement = true;
        },

        fermerModalRemboursement() {
            this.showModalRemboursement = false;
            this.gestionnaireSelectionne = null;
            this.montantRemboursement = "";
            this.remboursementTotal = false;
        },

        async confirmerRemboursement() {
            if (!this.montantRemboursementValide || this.submitting) return;

            const montant = this.remboursementTotal
                ? parseFloat(this.gestionnaireSelectionne.montant)
                : parseFloat(this.montantRemboursement);

            this.submitting = true;

            try {
                const responseRemboursement = await authFetch(
                    "/api/mouvements-caisse/create",
                    {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({
                            type: "remboursement",
                            montant: montant,
                            gestionnaire: this.gestionnaireSelectionne.nom,
                            description: this.remboursementTotal
                                ? "Remboursement total"
                                : `Remboursement partiel`,
                        }),
                    }
                );

                // ✅ VÉRIFICATION SIMPLIFIÉE
                if (responseRemboursement.ok) {
                    const result = await responseRemboursement.json();
                    this.mouvements.unshift(result);

                    if (this.remboursementTotal) {
                        await this.marquerAvancesRembourseesAPI(
                            this.gestionnaireSelectionne.nom
                        );
                    } else {
                        await this.gestionRemboursementPartielAPI(
                            this.gestionnaireSelectionne.nom,
                            montant
                        );
                    }

                    await this.chargerMouvements();
                    this.fermerModalRemboursement();
                }
            } catch (error) {
                console.error("Erreur remboursement:", error);
            } finally {
                this.submitting = false;
            }
        },
        async gestionRemboursementPartielAPI(
            nomGestionnaire,
            montantRemboursement
        ) {
            try {
                const avancesNonRemboursees = this.mouvements
                    .filter(
                        (m) =>
                            m.type === "avance" &&
                            m.gestionnaire === nomGestionnaire &&
                            !m.rembourse
                    )
                    .sort((a, b) => new Date(a.date) - new Date(b.date));

                let montantRestant = parseFloat(montantRemboursement);

                for (let avance of avancesNonRemboursees) {
                    if (montantRestant <= 0) break;

                    const montantAvance = parseFloat(avance.montant);
                    const dejaRembourse =
                        parseFloat(avance.montantRemboursePartiel) || 0;
                    const resteAvance = montantAvance - dejaRembourse;

                    if (montantRestant >= resteAvance) {
                        await this.marquerUneAvanceRemboursee(avance.id);
                        montantRestant -= resteAvance;
                    } else {
                        const nouveauMontant = dejaRembourse + montantRestant;
                        await this.marquerAvancePartiellementRemboursee(
                            avance.id,
                            nouveauMontant
                        );
                        montantRestant = 0;
                    }
                }
            } catch (error) {
                console.error("Erreur remboursement partiel:", error);
                throw error;
            }
        },

        async marquerUneAvanceRemboursee(avanceId) {
            const response = await authFetch(
                `/api/mouvements-caisse/${avanceId}/rembourser`,
                {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                }
            );
            if (!response.ok) throw new Error(`Erreur remboursement`);
            return await response.json();
        },

        async marquerAvancePartiellementRemboursee(avanceId, nouveauMontant) {
            const response = await authFetch(
                `/api/mouvements-caisse/${avanceId}/rembourser-partiel`,
                {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        montantRemboursePartiel: nouveauMontant,
                    }),
                }
            );
            if (!response.ok) throw new Error(`Erreur remboursement partiel`);
            return await response.json();
        },

        async marquerAvancesRembourseesAPI(nomGestionnaire) {
            try {
                const avancesNonRemboursees = this.mouvements.filter(
                    (m) =>
                        m.type === "avance" &&
                        m.gestionnaire === nomGestionnaire &&
                        !m.rembourse
                );
                for (let avance of avancesNonRemboursees) {
                    await this.marquerUneAvanceRemboursee(avance.id);
                }
            } catch (error) {
                console.error("Erreur marquage remboursement:", error);
                throw error;
            }
        },

        getMouvementIcon(type) {
            const icons = {
                avance: "bi bi-cash text-warning",
                depense: "bi bi-currency-euro text-primary",
                remboursement: "bi bi-cash-coin text-primary",
            };
            return icons[type] || "bi bi-file-text text-muted";
        },

        formatDateMobile(dateString) {
            const date = new Date(dateString);
            return (
                date.toLocaleDateString("fr-FR", {
                    day: "2-digit",
                    month: "2-digit",
                }) +
                " " +
                date.toLocaleTimeString("fr-FR", {
                    hour: "2-digit",
                    minute: "2-digit",
                })
            );
        },
    },
};
</script>

<style scoped></style>
