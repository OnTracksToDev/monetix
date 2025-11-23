<template>
    <div class="container-custom py-4">
        <!-- Titre -->
        <div class="text-center mb-4">
            <h1 class="fw-bold text-primary display-5 mb-2">Nouvelle vente</h1>
        </div>

        <!-- CHOIX DU TYPE DE PAIEMENT -->
        <div class="row g-3 justify-content-center">
            <div class="col-12">
                <button
                    @click="typePaiement = 'immediat'"
                    class="btn btn-success btn-lg w-100 rounded-pill shadow-sm py-4 fs-4 fw-bold d-flex align-items-center justify-content-center"
                >
                    <i class="bi bi-cash-coin me-3"></i>
                    Paiement immédiat
                </button>
            </div>
            <div class="col-12">
                <button
                    @click="typePaiement = 'credit'"
                    class="btn btn-warning btn-lg w-100 rounded-pill shadow-sm py-4 fs-4 fw-bold d-flex align-items-center justify-content-center"
                >
                    <i class="bi bi-credit-card me-3"></i>
                    Gestion Crédit
                </button>
            </div>
        </div>

        <!-- SECTION PAIEMENT IMMÉDIAT -->
        <div v-if="typePaiement === 'immediat'" class="mt-4">
            <div class="mb-4">
                <label class="form-label fs-5 fw-semibold"
                    >Montant de la vente</label
                >
                <input
                    v-model="montantVente"
                    type="number"
                    step="0.01"
                    min="0"
                    class="form-control form-control-lg text-center py-3 rounded-pill"
                    placeholder="0.00"
                    :disabled="loading"
                />
            </div>

            <button
                @click="validerPaiementImmediat"
                class="btn btn-success btn-lg w-100 rounded-pill shadow-sm py-4 fs-4 fw-bold d-flex align-items-center justify-content-center"
                :disabled="!montantVente || montantVente <= 0 || loading"
            >
                <span
                    v-if="loading"
                    class="spinner-border spinner-border-sm me-2"
                ></span>
                Encaisser {{ montantVente }} €
            </button>
        </div>

        <!-- SECTION CRÉDIT AVEC DÉTECTION AUTO DES DOUBLONS -->
        <div v-if="typePaiement === 'credit'" class="mt-4">
            <!-- SAISIE DIRECTE DU NOM -->
            <div class="mb-4">
                <label class="form-label fs-5 fw-semibold">
                    Nom de l'adhérent
                    <small v-if="selectedAdherent" class="text-muted ms-2">
                        (cliquer pour changer)
                    </small>
                </label>
                <input
                    v-model="nomAdherent"
                    type="text"
                    class="form-control form-control-lg py-3 rounded-pill"
                    :placeholder="
                        selectedAdherent
                            ? 'Taper pour changer d\'adhérent...'
                            : 'Saisir le nom complet...'
                    "
                    @input="verifierDoublons"
                    @focus="onSearchFocus"
                    :disabled="loading"
                />
            </div>

            <!-- ADHÉRENT SÉLECTIONNÉ -->
            <div v-if="selectedAdherent" class="mb-4">
                <div class="card bg-light border-0 rounded-pill">
                    <div class="card-body py-3">
                        <div
                            class="d-flex justify-content-between align-items-center"
                        >
                            <div class="d-flex align-items-center">
                                <i
                                    class="bi bi-person-check me-3 fs-4 text-warning"
                                ></i>
                                <div>
                                    <div class="fw-bold fs-5">
                                        {{ selectedAdherent.nom }}
                                    </div>
                                    <small class="text-muted"
                                        >Adhérent sélectionné</small
                                    >
                                </div>
                            </div>
                            <span
                                class="badge fs-6 py-2 rounded-pill"
                                :class="
                                    selectedAdherent.credit_total > 0
                                        ? 'bg-warning text-dark'
                                        : selectedAdherent.credit_total < 0
                                        ? 'bg-success'
                                        : 'bg-secondary'
                                "
                            >
                                {{ selectedAdherent.credit_total }} €
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DOUBLONS DÉTECTÉS -->
            <div
                v-if="
                    doublonsDetectes.length > 0 &&
                    nomAdherent &&
                    !selectedAdherent
                "
                class="mb-4"
            >
                <h6 class="fw-semibold mb-3 text-muted">
                    <i class="bi bi-person-check me-2"></i>
                    Adhérents similaires trouvés :
                </h6>

                <div class="row g-3">
                    <div
                        v-for="doublon in doublonsDetectes"
                        :key="doublon.id"
                        class="col-12"
                    >
                        <button
                            @click="selectAdherent(doublon)"
                            class="btn btn-outline-primary btn-lg w-100 rounded-pill py-3 fs-5 fw-semibold d-flex align-items-center justify-content-between"
                        >
                            <div class="d-flex align-items-center">
                                <i class="bi bi-person me-3 fs-4"></i>
                                <div class="fw-bold">{{ doublon.nom }}</div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span
                                    class="badge fs-7 py-1 px-2 rounded-pill"
                                    :class="
                                        doublon.credit_total > 0
                                            ? 'bg-warning text-dark'
                                            : doublon.credit_total < 0
                                            ? 'bg-success'
                                            : 'bg-secondary'
                                    "
                                >
                                    {{ doublon.credit_total }} €
                                </span>
                                <i class="bi bi-plus-circle fs-5"></i>
                            </div>
                        </button>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <small class="text-muted">ou</small>
                </div>
            </div>

            <!-- BOUTON CRÉER NOUVEL ADHÉRENT -->
            <div v-if="nomAdherent && !selectedAdherent" class="mb-4">
                <button
                    @click="creerAdherentAvecNom(nomAdherent)"
                    class="btn btn-outline-primary btn-lg w-100 rounded-pill py-4 fs-5 fw-semibold d-flex align-items-center justify-content-center"
                    :disabled="loading"
                >
                    <i class="bi bi-person-add me-2"></i>
                    Créer "{{ nomAdherent }}"
                </button>
            </div>

            <!-- FORMULAIRE DE CONSOMMATION (APRÈS SÉLECTION) -->
            <div v-if="selectedAdherent" class="mt-4">
                <!-- NOUVELLE CONSOMMATION -->
                <div class="mb-4">
                    <label class="form-label fw-semibold fs-5"
                        >Nouvelle consommation</label
                    >
                    <input
                        v-model="nouvelleConsommation"
                        type="number"
                        step="0.01"
                        min="0"
                        class="form-control form-control-lg text-center py-3 rounded-pill"
                        placeholder="0.00"
                        :disabled="loading"
                    />
                </div>

                <!-- MONTANT REÇU -->
                <div class="mb-4">
                    <label class="form-label fw-semibold fs-5"
                        >Montant reçu en espèces</label
                    >
                    <input
                        v-model="montantRecu"
                        type="number"
                        step="0.01"
                        min="0"
                        class="form-control form-control-lg text-center py-3 rounded-pill"
                        placeholder="0.00"
                        :disabled="loading"
                        @input="calculerNouvelleDette"
                    />
                </div>

                <!-- RÉCAPITULATIF DÉTAILLÉ -->
                <div
                    v-if="nouvelleConsommation || montantRecu"
                    class="alert alert-warning rounded-3 mb-4"
                >
                    <div class="d-flex justify-content-between mb-2">
                        <span>Dette actuelle :</span>
                        <strong>{{ selectedAdherent.credit_total }} €</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Nouvelle consommation :</span>
                        <strong class="text-primary"
                            >{{ nouvelleConsommation || 0 }} €</strong
                        >
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Montant reçu :</span>
                        <strong class="text-success"
                            >{{ montantRecu || 0 }} €</strong
                        >
                    </div>
                    <hr />
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>Nouvelle dette :</span>
                        <strong>{{ calculerNouvelleDette() }} €</strong>
                    </div>

                    <!-- TYPE D'OPÉRATION DÉTECTÉ AUTOMATIQUEMENT -->
                    <div
                        v-if="nouvelleConsommation || montantRecu"
                        class="mt-2 text-center"
                    >
                        <span
                            class="badge fs-6 py-2 px-3"
                            :class="typeOperationClass"
                        >
                            <i :class="typeOperationIcon" class="me-1"></i>
                            {{ typeOperation }}
                        </span>
                    </div>
                </div>

                <!-- BOUTON VALIDATION -->
                <button
                    @click="validerOperationCredit"
                    class="btn btn-success btn-lg w-100 rounded-pill shadow-sm py-4 fs-5 fw-bold d-flex align-items-center justify-content-center"
                    :disabled="
                        loading || (!nouvelleConsommation && !montantRecu)
                    "
                >
                    <span
                        v-if="loading"
                        class="spinner-border spinner-border-sm me-2"
                    ></span>
                    {{ texteBoutonValidation }}
                </button>
            </div>
        </div>

        <!-- MESSAGE -->
        <div
            v-if="message"
            class="alert mt-3 text-center fs-5 rounded-pill border-0"
            :class="messageClass"
        >
            {{ message }}
        </div>

        <!-- BOUTON RETOUR -->
        <div class="text-center mt-4">
            <button
                @click="$router.push('/')"
                class="btn btn-outline-secondary btn-lg w-100 rounded-pill py-4 fs-5 fw-semibold d-flex align-items-center justify-content-center"
            >
                <i class="bi bi-arrow-left me-2"></i>
                Retour à l'accueil
            </button>
        </div>
    </div>
</template>

<script>
import { authFetch } from '../services/http.js';
export default {
    name: "Vente",
    data() {
        return {
            typePaiement: "",
            montantVente: "",
            nouvelleConsommation: "",
            montantRecu: "",
            nomAdherent: "",
            selectedAdherent: null,
            adherents: [],
            doublonsDetectes: [],
            message: "",
            messageClass: "alert-info",
            loading: false,
        };
    },
    computed: {
        // DÉTECTION AUTOMATIQUE DU TYPE D'OPÉRATION
        typeOperation() {
            const conso = parseFloat(this.nouvelleConsommation || 0);
            const recu = parseFloat(this.montantRecu || 0);
            const detteActuelle = parseFloat(
                this.selectedAdherent?.credit_total || 0
            );

            if (conso > 0 && recu === 0) return "TOUT AU CRÉDIT";
            if (conso === 0 && recu > 0) return "REMBOURSEMENT";
            if (conso > 0 && recu === conso) return "PAIEMENT COMPLET";
            if (conso > 0 && recu > 0 && recu < conso)
                return "PAIEMENT PARTIEL";
            if (conso > 0 && recu > conso) return "REMBOURSEMENT";
            return "OPÉRATION";
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
            const conso = parseFloat(this.nouvelleConsommation || 0);
            const recu = parseFloat(this.montantRecu || 0);

            if (this.typeOperation === "TOUT AU CRÉDIT")
                return `Ajouter ${conso} € au crédit`;
            if (this.typeOperation === "PAIEMENT COMPLET")
                return `Encaisser ${conso} €`;
            if (this.typeOperation === "REMBOURSEMENT") {
                if (conso === 0) return `Rembourser ${recu} €`;
                return `Rembourser ${(recu - conso).toFixed(
                    2
                )} € + payer consommation`;
            }
            if (this.typeOperation === "PAIEMENT PARTIEL") {
                return `Encaisser ${recu} € + ${(conso - recu).toFixed(
                    2
                )} € crédit`;
            }
            return "Valider l'opération";
        },
    },
    async mounted() {
        await this.chargerAdherents();
    },
    methods: {
        async chargerAdherents() {
            this.loading = true;
            try {
                const response = await authFetch("/api/adherents");
                if (!response.ok) throw new Error("Erreur chargement");
                this.adherents = await response.json();
            } catch (error) {
                this.showMessage("Erreur connexion", "danger");
            } finally {
                this.loading = false;
            }
        },

        onSearchFocus() {
            if (this.selectedAdherent) {
                const nomTape = this.nomAdherent;
                this.selectedAdherent = null;
                this.nouvelleConsommation = "";
                this.montantRecu = "";
                this.doublonsDetectes = [];
                this.nomAdherent = nomTape;
            }
        },

        verifierDoublons() {
            if (!this.nomAdherent || this.nomAdherent.length < 2) {
                this.doublonsDetectes = [];
                return;
            }

            const nomRecherche = this.nomAdherent.toLowerCase().trim();

            this.doublonsDetectes = this.adherents
                .filter((adherent) => {
                    const nomExistant = adherent.nom.toLowerCase();
                    if (nomExistant === nomRecherche) return true;
                    if (
                        nomExistant.includes(nomRecherche) ||
                        nomRecherche.includes(nomExistant)
                    )
                        return true;

                    const motsRecherche = nomRecherche.split(" ");
                    const motsExistant = nomExistant.split(" ");
                    const motsCommuns = motsRecherche.filter((mot) =>
                        motsExistant.some(
                            (motExist) =>
                                motExist.includes(mot) || mot.includes(motExist)
                        )
                    );
                    return (
                        motsCommuns.length >=
                        Math.min(motsRecherche.length, motsExistant.length) *
                            0.7
                    );
                })
                .slice(0, 3);
        },

        selectAdherent(adherent) {
            this.selectedAdherent = adherent;
            this.nomAdherent = adherent.nom;
            this.doublonsDetectes = [];
        },

        deselectAdherent() {
            this.selectedAdherent = null;
            this.nouvelleConsommation = "";
            this.montantRecu = "";
            this.nomAdherent = "";
            this.doublonsDetectes = [];
        },

        async creerAdherentAvecNom(nom) {
            if (!nom.trim()) return;

            this.loading = true;
            try {
                const response = await authFetch("/api/adherents", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        nom: nom.trim(),
                        credit_total: 0,
                    }),
                });

                if (!response.ok) throw new Error("Erreur création");

                const nouvelAdherent = await response.json();
                this.adherents.push(nouvelAdherent);
                this.selectedAdherent = nouvelAdherent;
                this.doublonsDetectes = [];

                this.showMessage(`✅ Adhérent "${nom}" créé`, "success");
            } catch (error) {
                this.showMessage("❌ Erreur création", "danger");
            } finally {
                this.loading = false;
            }
        },

        async validerPaiementImmediat() {
            this.loading = true;
            try {
                const response = await authFetch("/api/ventes", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        montant_total: parseFloat(this.montantVente),
                        montant_paye: parseFloat(this.montantVente),
                        reste_a_payer: 0,
                        mode_paiement: "paiement_complet",
                        adherent_id: null,
                        date: new Date().toISOString(),
                    }),
                });

                if (!response.ok) throw new Error("Erreur création vente");

                this.showMessage(
                    `✅ Vente de ${this.montantVente} € encaissée (PAIEMENT COMPLET)`,
                    "success"
                );
                this.resetForm();
            } catch (error) {
                this.showMessage("❌ Erreur enregistrement", "danger");
            } finally {
                this.loading = false;
            }
        },

        async validerOperationCredit() {
            this.loading = true;
            try {
                const nouvelleConso =
                    parseFloat(this.nouvelleConsommation) || 0;
                const montantRecu = parseFloat(this.montantRecu) || 0;
                const ancienneDette = parseFloat(
                    this.selectedAdherent.credit_total
                );
                const nouvelleDette =
                    ancienneDette + nouvelleConso - montantRecu;

                // DÉTERMINATION DU TYPE D'OPÉRATION POUR L'HISTORIQUE
                let typeOperation;
                if (nouvelleConso > 0 && montantRecu === 0) {
                    typeOperation = "tout_au_credit";
                } else if (nouvelleConso === 0 && montantRecu > 0) {
                    typeOperation = "remboursement";
                } else if (nouvelleConso > 0 && montantRecu === nouvelleConso) {
                    typeOperation = "paiement_complet";
                } else if (nouvelleConso > 0 && montantRecu > nouvelleConso) {
                    typeOperation = "remboursement";
                } else {
                    typeOperation = "paiement_partiel";
                }

                // MISE À JOUR DU CRÉDIT ADHÉRENT
                await authFetch(`/api/adherents/${this.selectedAdherent.id}`, {
                    method: "PUT",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ credit_total: nouvelleDette }),
                });

                // CRÉATION DE LA VENTE
                await authFetch("/api/ventes", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        montant_total: nouvelleConso,
                        montant_paye: montantRecu,
                        reste_a_payer: Math.max(0, nouvelleConso - montantRecu),
                        mode_paiement: typeOperation,
                        adherent_id: this.selectedAdherent.id,
                        date: new Date().toISOString(),
                    }),
                });

                // MISE À JOUR LOCALE
                this.selectedAdherent.credit_total = nouvelleDette;

                // MESSAGE DE SUCCÈS ADAPTÉ
                let message = "";
                switch (typeOperation) {
                    case "tout_au_credit":
                        message = `✅ ${nouvelleConso} € ajoutés au crédit → Dette: ${nouvelleDette.toFixed(
                            2
                        )} €`;
                        break;
                    case "paiement_complet":
                        message = `✅ ${nouvelleConso} € encaissés → Dette: ${nouvelleDette.toFixed(
                            2
                        )} €`;
                        break;
                    case "paiement_partiel":
                        message = `✅ ${montantRecu} € payés + ${(
                            nouvelleConso - montantRecu
                        ).toFixed(2)} € crédit → Dette: ${nouvelleDette.toFixed(
                            2
                        )} €`;
                        break;
                    case "remboursement":
                        message = `✅ Remboursement de ${montantRecu} € → Dette: ${nouvelleDette.toFixed(
                            2
                        )} €`;
                        break;
                }

                this.showMessage(message, "success");
                this.resetForm();
            } catch (error) {
                this.showMessage("❌ Erreur enregistrement", "danger");
            } finally {
                this.loading = false;
            }
        },

        calculerNouvelleDette() {
            if (!this.selectedAdherent) return "0.00";
            const ancienneDette = parseFloat(
                this.selectedAdherent.credit_total
            );
            const nouvelleConso = parseFloat(this.nouvelleConsommation) || 0;
            const montantRecu = parseFloat(this.montantRecu) || 0;
            return (ancienneDette + nouvelleConso - montantRecu).toFixed(2);
        },

        resetForm() {
            this.montantVente = "";
            this.nouvelleConsommation = "";
            this.montantRecu = "";
            this.selectedAdherent = null;
            this.nomAdherent = "";
            this.doublonsDetectes = [];
            this.typePaiement = "";
        },

        showMessage(text, type = "info") {
            this.message = text;
            this.messageClass = `alert-${type}`;
            setTimeout(() => {
                this.message = "";
            }, 5000);
        },
    },
};
</script>

<style scoped>

</style>
