<template>
    <div class="container-custom py-5">
        <!-- Tournées en cours -->
        <div class="mb-4">
            <!-- Titre TOUJOURS visible -->
            <h5 class="fw-semibold mb-3">
                <i class="bi bi-play-circle me-2"></i>Tournées en cours
            </h5>

            <!-- Contenu dynamique -->
            <div class="min-height-150">
                <!-- État : Chargement -->
                <div v-if="chargementTournees" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden"
                            >Chargement des tournées...</span
                        >
                    </div>
                    <p class="text-muted mt-2">
                        Chargement des tournées en cours...
                    </p>
                </div>

                <!-- État : Aucune tournée -->
                <div
                    v-else-if="tourneesEnCours.length === 0"
                    class="text-center py-4 text-muted"
                >
                    <p class="mb-0">Aucune tournée en cours</p>
                    <small>Créez une nouvelle tournée pour commencer</small>
                </div>

                <!-- État : Liste des tournées -->
                <div v-else class="row g-3">
                    <div
                        class="col-12"
                        v-for="tournee in tourneesEnCours"
                        :key="tournee.id"
                    >
                        <button
                            @click="continuerTournee(tournee)"
                            class="btn btn-secondary btn-lg w-100 rounded-pill shadow-sm p-4 opacity-75"
                        >
                            <div
                                class="d-flex justify-content-between align-items-center w-100"
                            >
                                <!-- Partie gauche : Informations adhérent -->
                                <div
                                    class="d-flex align-items-center text-start flex-grow-1"
                                >
                                    <i
                                        class="bi bi-cup-straw fs-3 text-white me-3"
                                    ></i>
                                    <div>
                                        <div
                                            class="fs-5 fw-bold text-white mb-1"
                                        >
                                            {{ tournee.adherent.nom }}
                                        </div>
                                        <div
                                            class="d-flex gap-3 text-white opacity-75"
                                        >
                                            <small
                                                class="d-flex align-items-center"
                                            >
                                                <i class="bi bi-cart me-1"></i>
                                                <span
                                                    v-if="
                                                        tournee.estimation_clients
                                                    "
                                                    class="fw-semibold"
                                                >
                                                    {{
                                                        tournee.ventes
                                                            ?.length || 0
                                                    }}/{{
                                                        tournee.estimation_clients
                                                    }}
                                                </span>
                                                <span v-else>
                                                    {{
                                                        tournee.ventes
                                                            ?.length || 0
                                                    }}
                                                </span>
                                                ventes
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Partie droite : Montant et flèche -->
                                <div class="d-flex align-items-center gap-3">
                                    <span
                                        class="badge bg-light text-secondary fs-6 px-3 py-2"
                                    >
                                        {{ calculerTotalTournee(tournee) }} €
                                    </span>
                                    <i
                                        class="bi bi-chevron-right fs-4 text-white"
                                    ></i>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Boutons principaux -->
        <div class="row g-3 justify-content-center">
            <!-- Vente : bouton principal -->
            <div class="col-12">
                <router-link
                    to="/vente"
                    class="btn btn-primary btn-lg w-100 rounded-pill shadow-sm py-4 fs-4 fw-bold"
                >
                    <i class="bi bi-cart-plus me-2"></i>Nouvelle vente
                </router-link>
            </div>

            <!-- Tournée : ouvre la modal -->
            <div class="col-6">
                <button
                    @click="ouvrirModalTournee"
                    class="btn btn-outline-primary btn-lg w-100 rounded-pill shadow-sm py-4 fs-5 fw-semibold"
                >
                    <i class="bi bi-cup-straw me-2"></i>
                    {{ tourneesEnCours.length > 0 ? "Nouvelle" : "Tournée" }}
                </button>
            </div>

            <div class="col-6">
                <router-link
                    to="/historique"
                    class="btn btn-outline-primary btn-lg w-100 rounded-pill shadow-sm py-4 fs-5 fw-semibold"
                >
                    <i class="bi bi-clock-history me-2"></i>Historique
                </router-link>
            </div>

            <div class="col-6">
                <router-link
                    to="/adherents"
                    class="btn btn-outline-primary btn-lg w-100 rounded-pill shadow-sm py-4 fs-5 fw-semibold"
                >
                    <i class="bi bi-people me-2"></i>Adhérents
                </router-link>
            </div>

            <div class="col-6">
                <router-link
                    to="/caisse"
                    class="btn btn-outline-primary btn-lg w-100 rounded-pill shadow-sm py-4 fs-5 fw-semibold"
                >
                    <i class="bi bi-cash-coin me-2"></i>Caisse
                </router-link>
            </div>
        </div>

        <!-- Modal création tournée -->
        <div
            v-if="showModalTournee"
            class="modal fade show d-block"
            style="background: rgba(0, 0, 0, 0.5)"
            @click.self="fermerModal"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4">
                    <!-- En-tête normal -->
                    <div
                        class="modal-header border-0 pb-0"
                        v-if="!creationReussie"
                    >
                        <h5 class="modal-title fw-bold fs-4">
                            <i class="bi bi-cup-straw me-2"></i>Nouvelle tournée
                        </h5>
                        <button
                            type="button"
                            class="btn-close fs-5"
                            @click="fermerModal"
                        ></button>
                    </div>

                    <!-- En-tête succès -->
                    <div class="modal-header border-0 pb-0" v-else>
                        <h5 class="modal-title fw-bold fs-4 text-success">
                            <i class="bi bi-check-circle me-2"></i>Succès
                        </h5>
                    </div>

                    <div class="modal-body">
                        <!-- Contenu normal -->
                        <div v-if="!creationReussie">
                            <!-- Sélection adhérent -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold fs-6"
                                    >Adhérent responsable</label
                                >
                                <input
                                    v-model="nomAdherent"
                                    type="text"
                                    class="form-control form-control-lg rounded-pill"
                                    placeholder="Saisir le nom de l'adhérent..."
                                    @input="verifierDoublons"
                                    @keyup.enter="creerTournee"
                                />

                                <!-- Suggestions rapides -->
                                <div
                                    v-if="
                                        doublons.length > 0 &&
                                        nomAdherent &&
                                        !selectedAdherent
                                    "
                                    class="mt-3"
                                >
                                    <small class="text-muted d-block mb-2"
                                        >Adhérents existants :</small
                                    >
                                    <div class="d-grid gap-2">
                                        <button
                                            v-for="doublon in doublons"
                                            :key="doublon.id"
                                            @click="selectAdherent(doublon)"
                                            class="btn btn-outline-primary btn-lg rounded-pill py-2 d-flex justify-content-between align-items-center"
                                        >
                                            <span>{{ doublon.nom }}</span>
                                            <span
                                                class="badge"
                                                :class="
                                                    doublon.credit_total > 0
                                                        ? 'bg-warning text-dark'
                                                        : 'bg-success'
                                                "
                                            >
                                                {{ doublon.credit_total }} €
                                            </span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Adhérent sélectionné -->
                                <div
                                    v-if="selectedAdherent"
                                    class="alert alert-success mt-3 border-0"
                                >
                                    <div
                                        class="d-flex justify-content-between align-items-center"
                                    >
                                        <div class="d-flex align-items-center">
                                            <i
                                                class="bi bi-person-check me-2"
                                            ></i>
                                            <div>
                                                <strong class="d-block">{{
                                                    selectedAdherent.nom
                                                }}</strong>
                                                <small class="text-success"
                                                    >Adhérent sélectionné</small
                                                >
                                            </div>
                                        </div>
                                        <button
                                            @click="deselectAdherent"
                                            class="btn btn-sm btn-outline-success rounded-pill px-3"
                                        >
                                            Changer
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Estimation optionnelle -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold fs-6">
                                    Estimation clients
                                    <small class="text-muted"
                                        >(optionnel)</small
                                    >
                                </label>
                                <input
                                    v-model="estimationClients"
                                    type="number"
                                    min="0"
                                    class="form-control form-control-lg text-center rounded-pill"
                                    placeholder="0"
                                    @keyup.enter="creerTournee"
                                />
                                <small class="text-muted">
                                    Cette information vous aidera à suivre
                                    l'avancement des ventes
                                </small>
                            </div>
                        </div>

                        <!-- Contenu succès -->
                        <div v-else class="text-center py-4">
                            <i
                                class="bi bi-check-circle text-success fs-1 d-block mb-3"
                            ></i>
                            <p class="fw-semibold fs-5 text-success mb-2">
                                Tournée créée !
                            </p>
                            <p class="text-muted">
                                Redirection vers la tournée...
                            </p>
                        </div>
                    </div>

                    <div class="modal-footer border-0" v-if="!creationReussie">
                        <button
                            @click="fermerModal"
                            class="btn btn-outline-secondary rounded-pill px-4"
                        >
                            Annuler
                        </button>
                        <button
                            @click="creerTournee"
                            class="btn btn-primary rounded-pill px-4 fw-semibold"
                            :disabled="
                                (!selectedAdherent && !nomAdherent) || loading
                            "
                        >
                            <span
                                v-if="loading"
                                class="spinner-border spinner-border-sm me-2"
                            ></span>
                            <i class="bi bi-play-circle me-2"></i>
                            Démarrer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Message -->
        <div
            v-if="message"
            class="alert mt-3 text-center fs-5 rounded-pill border-0"
            :class="messageClass"
        >
            {{ message }}
        </div>
    </div>
</template>

<script>
import { authFetch } from "../services/http.js";
export default {
    name: "Accueil",
    data() {
        return {
            tourneesEnCours: [],
            showModalTournee: false,
            nomAdherent: "",
            selectedAdherent: null,
            adherents: [],
            doublons: [],
            estimationClients: 0,
            loading: false,
            chargementTournees: true,
            creationReussie: false,
            tourneeCreee: null,
            message: "",
            messageClass: "alert-info",
        };
    },
    async mounted() {
        await this.chargerTourneesEnCours();
        await this.chargerAdherents();
    },
    methods: {
        async chargerTourneesEnCours() {
            this.chargementTournees = true;
            try {
                const response = await authFetch("/api/tournees");
                if (response.ok) {
                    const tournees = await response.json();
                    this.tourneesEnCours = tournees.filter(
                        (t) => t.statut === "en_cours"
                    );
                }
            } catch (error) {
                console.error("Erreur chargement tournées", error);
            } finally {
                this.chargementTournees = false;
            }
        },

        async chargerAdherents() {
            try {
                const response = await authFetch("/api/adherents");
                if (response.ok) {
                    this.adherents = await response.json();
                    this.adherents.sort((a, b) => a.nom.localeCompare(b.nom));
                }
            } catch (error) {
                console.error("Erreur chargement adhérents", error);
            }
        },

        calculerTotalTournee(tournee) {
            if (tournee.ventes && tournee.ventes.length > 0) {
                const total = tournee.ventes.reduce((sum, vente) => {
                    return sum + parseFloat(vente.montant_total || 0);
                }, 0);
                return total.toFixed(2);
            }
            return "0.00";
        },

        ouvrirModalTournee() {
            this.showModalTournee = true;
            this.nomAdherent = "";
            this.selectedAdherent = null;
            this.estimationClients = 0;
            this.doublons = [];
            this.creationReussie = false;
            this.tourneeCreee = null;
            this.message = "";
        },

        fermerModal() {
            this.showModalTournee = false;
            this.nomAdherent = "";
            this.selectedAdherent = null;
            this.doublons = [];
            this.creationReussie = false;
            this.tourneeCreee = null;
        },

        verifierDoublons() {
            if (!this.nomAdherent || this.nomAdherent.length < 2) {
                this.doublons = [];
                return;
            }

            const nomRecherche = this.nomAdherent.toLowerCase().trim();
            this.doublons = this.adherents
                .filter(
                    (adherent) =>
                        adherent.nom.toLowerCase().includes(nomRecherche) ||
                        nomRecherche.includes(adherent.nom.toLowerCase())
                )
                .slice(0, 3);
        },

        selectAdherent(adherent) {
            this.selectedAdherent = adherent;
            this.nomAdherent = adherent.nom;
            this.doublons = [];
        },

        deselectAdherent() {
            this.selectedAdherent = null;
            this.nomAdherent = "";
            this.doublons = [];
        },

        async creerTournee() {
            // Validation
            if (!this.selectedAdherent && !this.nomAdherent?.trim()) {
                this.showMessage(
                    "Veuillez saisir un nom d'adhérent",
                    "warning"
                );
                return;
            }

            this.loading = true;
            let adherentId = this.selectedAdherent?.id;

            try {
                // Créer nouvel adhérent si besoin
                if (!adherentId && this.nomAdherent) {
                    const response = await authFetch("/api/adherents", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({
                            nom: this.nomAdherent.trim(),
                            credit_total: 0,
                        }),
                    });

                    if (response.ok) {
                        const nouvelAdherent = await response.json();
                        adherentId = nouvelAdherent.id;
                        this.adherents.push(nouvelAdherent);
                    } else {
                        throw new Error("Erreur création adhérent");
                    }
                }

                if (!adherentId) {
                    throw new Error("Aucun ID adhérent valide");
                }

                // Créer la tournée
                const response = await authFetch("/api/tournees", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        adherent_id: adherentId,
                        montant_total: 0,
                        montant_paye: 0,
                        reste_a_payer: 0,
                        estimation_clients: this.estimationClients || null,
                        date_debut: new Date().toISOString(),
                        statut: "en_cours",
                    }),
                });

                if (response.ok) {
                    const tournee = await response.json();

                    // ✅ SUCCÈS - Affichage dans la modal
                    this.loading = false;
                    this.creationReussie = true;
                    this.tourneeCreee = tournee;

                    // Redirection après 1.5 secondes
                    setTimeout(() => {
                        this.showModalTournee = false;
                        this.$router.push(`/tournee/${tournee.id}`);
                    }, 1500);
                } else {
                    throw new Error("Erreur création tournée");
                }
            } catch (error) {
                console.error("❌ Erreur:", error);
                this.loading = false;
                this.showMessage(
                    "Erreur lors de la création de la tournée",
                    "danger"
                );
            }
        },

        continuerTournee(tournee) {
            this.$router.push(`/tournee/${tournee.id}`);
        },

        formatHeure(dateString) {
            return new Date(dateString).toLocaleTimeString("fr-FR", {
                hour: "2-digit",
                minute: "2-digit",
            });
        },

        showMessage(text, type = "info") {
            this.message = text;
            this.messageClass = `alert-${type}`;
            setTimeout(() => {
                this.message = "";
            }, 4000);
        },
    },
};
</script>

<style scoped></style>
