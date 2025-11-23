<template>
    <div class="container-custom py-4">
        <!-- Titre -->
        <div class="text-center mb-4">
            <h1 class="fw-bold text-primary display-5 mb-2">Adhérents</h1>
            <p class="text-muted fs-5">
                Consultation des adhérents et de leurs crédits
            </p>
        </div>

        <!-- BARRE DE FILTRES ET TRI -->
        <div class="row g-3 mb-4">
            <!-- RECHERCHE -->
            <div class="col-12 col-md-6">
                <div class="position-relative">
                    <input
                        v-model="searchQuery"
                        type="text"
                        class="form-control form-control-lg py-3 rounded-pill"
                        placeholder="Rechercher un adhérent..."
                        @input="appliquerFiltres"
                        :disabled="chargementAdherents"
                    />
                    <div
                        class="position-absolute top-50 end-0 translate-middle-y me-3"
                    >
                        <i class="bi bi-search text-muted"></i>
                    </div>
                </div>
            </div>

            <!-- FILTRES ET TRI -->
            <div class="col-12 col-md-6">
                <div class="d-flex gap-2">
                    <!-- FILTRE CRÉDIT -->
                    <div class="dropdown flex-grow-1">
                        <button
                            class="btn btn-outline-primary w-100 rounded-pill py-3 d-flex align-items-center justify-content-between"
                            type="button"
                            data-bs-toggle="dropdown"
                            :disabled="chargementAdherents"
                        >
                            <span>{{ texteFiltreCredit }}</span>
                            <i class="bi bi-chevron-down ms-2"></i>
                        </button>
                        <ul class="dropdown-menu w-100">
                            <li>
                                <button
                                    class="dropdown-item d-flex align-items-center"
                                    :class="{ active: filtreCredit === 'tous' }"
                                    @click="changerFiltreCredit('tous')"
                                >
                                    <i class="bi bi-people me-2"></i>
                                    Tous les adhérents
                                    <span class="ms-2"
                                        >({{ adherents.length }})</span
                                    >
                                </button>
                            </li>
                            <li>
                                <button
                                    class="dropdown-item d-flex align-items-center"
                                    :class="{
                                        active: filtreCredit === 'avec-dette',
                                    }"
                                    @click="changerFiltreCredit('avec-dette')"
                                >
                                    <i
                                        class="bi bi-exclamation-triangle me-2"
                                    ></i>
                                    Avec dette
                                    <span class="ms-2"
                                        >({{ nombreAvecDette }})</span
                                    >
                                </button>
                            </li>
                            <li>
                                <button
                                    class="dropdown-item d-flex align-items-center"
                                    :class="{
                                        active: filtreCredit === 'sans-dette',
                                    }"
                                    @click="changerFiltreCredit('sans-dette')"
                                >
                                    <i class="bi bi-check-circle me-2"></i>
                                    Sans dette
                                    <span class="ms-2"
                                        >({{ nombreSansDette }})</span
                                    >
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- TRI -->
                    <div class="dropdown">
                        <button
                            class="btn btn-outline-secondary rounded-pill py-3 px-3 d-flex align-items-center"
                            type="button"
                            data-bs-toggle="dropdown"
                            :disabled="chargementAdherents"
                        >
                            <i class="bi bi-sort-down"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <button
                                    class="dropdown-item d-flex align-items-center"
                                    :class="{ active: tri === 'nom' }"
                                    @click="changerTri('nom')"
                                >
                                    <i class="bi bi-sort-alpha-down me-2"></i>
                                    Par nom (A-Z)
                                </button>
                            </li>
                            <li>
                                <button
                                    class="dropdown-item d-flex align-items-center"
                                    :class="{ active: tri === 'dette-desc' }"
                                    @click="changerTri('dette-desc')"
                                >
                                    <i class="bi bi-sort-numeric-down me-2"></i>
                                    Par dette (décroissant)
                                </button>
                            </li>
                            <li>
                                <button
                                    class="dropdown-item d-flex align-items-center"
                                    :class="{ active: tri === 'dette-asc' }"
                                    @click="changerTri('dette-asc')"
                                >
                                    <i class="bi bi-sort-numeric-up me-2"></i>
                                    Par dette (croissant)
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- EXPORT PDF -->
                    <button
                        @click="exporterPDF"
                        class="btn btn-primary rounded-pill py-3 px-3 d-flex align-items-center"
                        :disabled="
                            chargementAdherents ||
                            adherentsAvecDette.length === 0
                        "
                        :title="
                            adherentsAvecDette.length === 0
                                ? 'Aucun adhérent avec dette à exporter'
                                : 'Exporter la liste des adhérents avec dette en PDF'
                        "
                    >
                        <i class="bi bi-file-earmark-pdf"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- INDICATEUR DE CHARGEMENT -->
        <div v-if="chargementAdherents" class="text-center py-5">
            <div
                class="spinner-border text-primary"
                style="width: 3rem; height: 3rem"
            ></div>
            <p class="mt-3 fs-5 text-muted">Chargement des adhérents...</p>
        </div>

        <!-- LISTE DES ADHÉRENTS -->
        <div v-else class="row g-3 justify-content-center">
            <div
                v-for="adherent in adherentsFiltres"
                :key="adherent.id"
                class="col-12"
            >
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <div
                            class="d-flex justify-content-between align-items-center"
                        >
                            <!-- NOM ET DETTE -->
                            <div class="d-flex align-items-center">
                                <i
                                    class="bi bi-person me-3 fs-4 text-primary"
                                ></i>
                                <div>
                                    <h5
                                        class="fw-bold mb-1 text-truncate"
                                        style="max-width: 200px"
                                    >
                                        {{ adherent.nom }}
                                    </h5>
                                    <small class="text-muted">Adhérent</small>
                                </div>
                            </div>

                            <!-- BOUTONS ACTION ET DETTE -->
                            <div class="d-flex align-items-center gap-3">
                                <!-- DETTE -->
                                <span
                                    class="badge fs-6 py-2 px-3 rounded-pill"
                                    :class="
                                        adherent.credit_total > 0
                                            ? 'bg-warning text-dark'
                                            : 'bg-success text-white'
                                    "
                                >
                                    <i
                                        v-if="adherent.credit_total > 0"
                                        class="bi bi-credit-card me-1"
                                    ></i>
                                    {{ adherent.credit_total }} €
                                </span>

                                <!-- BOUTONS ACTION -->
                                <div class="dropdown">
                                    <button
                                        class="btn btn-outline-primary rounded-pill py-2 px-3 d-flex align-items-center"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        :disabled="loading"
                                        @click.stop
                                    >
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <button
                                                class="dropdown-item d-flex align-items-center"
                                                @click="voirDetails(adherent)"
                                            >
                                                <i class="bi bi-eye me-2"></i>
                                                Voir détails
                                            </button>
                                        </li>
                                        <li>
                                            <button
                                                class="dropdown-item d-flex align-items-center"
                                                @click="
                                                    modifierAdherent(adherent)
                                                "
                                            >
                                                <i
                                                    class="bi bi-pencil me-2"
                                                ></i>
                                                Modifier
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider" /></li>
                                        <li>
                                            <button
                                                class="dropdown-item d-flex align-items-center text-danger"
                                                @click="
                                                    afficherModalSuppression(
                                                        adherent
                                                    )
                                                "
                                            >
                                                <i class="bi bi-trash me-2"></i>
                                                Supprimer
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MESSAGES SI AUCUN ADHÉRENT -->
        <div
            v-if="
                !chargementAdherents &&
                adherentsFiltres.length === 0 &&
                adherents.length === 0
            "
            class="text-center mt-5"
        >
            <p class="fs-5 text-muted">Aucun adhérent enregistré</p>
            <p class="text-muted fs-5">
                Les adhérents sont créés automatiquement lors des premières
                ventes.
            </p>
        </div>

        <div
            v-if="
                !chargementAdherents &&
                adherentsFiltres.length === 0 &&
                adherents.length > 0
            "
            class="text-center mt-5"
        >
            <p class="fs-5 text-muted">
                Aucun adhérent trouvé pour "{{ searchQuery }}"
            </p>
            <button
                @click="reinitialiserFiltres"
                class="btn btn-outline-primary btn-lg rounded-pill py-3 px-4 fs-5 d-flex align-items-center justify-content-center mx-auto"
            >
                <i class="bi bi-arrow-clockwise me-2"></i>
                Afficher tous les adhérents
            </button>
        </div>

        <!-- Modal de suppression d'adhérent -->
        <div
            v-if="showModalSuppressionAdherent"
            class="modal fade show d-block"
            style="background: rgba(0, 0, 0, 0.5)"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 m-2">
                    <div
                        class="modal-header border-0 pb-0"
                        v-if="!suppressionAdherentReussie"
                    >
                        <h5 class="modal-title fw-bold fs-5 text-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Supprimer l'adhérent
                        </h5>
                    </div>

                    <div class="modal-header border-0 pb-0" v-else>
                        <h5 class="modal-title fw-bold fs-5 text-success">
                            <i class="bi bi-check-circle me-2"></i>
                            Succès
                        </h5>
                    </div>

                    <div class="modal-body">
                        <div v-if="!suppressionAdherentReussie">
                            <div class="text-center mb-4">
                                <i
                                    class="bi bi-person-x text-danger fs-1 d-block mb-3"
                                ></i>
                                <p class="fw-semibold fs-6 mb-2">
                                    Êtes-vous sûr de vouloir supprimer
                                    l'adhérent
                                    <strong
                                        >"{{ adherentASupprimer?.nom }}"</strong
                                    >
                                    ?
                                </p>

                                <!-- Vérifications -->
                                <div
                                    v-if="problemesSuppression.length > 0"
                                    class="alert alert-warning rounded-3 mt-3"
                                >
                                    <p class="fw-semibold mb-2">
                                        Impossible de supprimer cet adhérent :
                                    </p>
                                    <ul class="mb-0 ps-3">
                                        <li
                                            v-for="probleme in problemesSuppression"
                                            :key="probleme"
                                        >
                                            {{ probleme }}
                                        </li>
                                    </ul>
                                </div>
                                <div v-else>
                                    <p class="text-muted small">
                                        Cette action est irréversible.
                                    </p>
                                </div>
                            </div>

                            <!-- Détails de l'adhérent -->
                            <div class="alert alert-info rounded-3">
                                <div class="row g-2 text-center">
                                    <div class="col-6">
                                        <small class="text-muted"
                                            >Dette actuelle</small
                                        >
                                        <div class="fw-bold">
                                            {{
                                                adherentASupprimer?.credit_total ||
                                                0
                                            }}
                                            €
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Statut</small>
                                        <div
                                            class="fw-bold"
                                            :class="
                                                problemesSuppression.length > 0
                                                    ? 'text-danger'
                                                    : 'text-success'
                                            "
                                        >
                                            {{
                                                problemesSuppression.length > 0
                                                    ? "Non supprimable"
                                                    : "Supprimable"
                                            }}
                                        </div>
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
                                Adhérent supprimé avec succès
                            </p>
                            <p class="text-muted">Fermeture automatique...</p>
                        </div>
                    </div>

                    <div
                        class="modal-footer border-0 pt-0"
                        v-if="!suppressionAdherentReussie"
                    >
                        <button
                            @click="fermerModalSuppressionAdherent"
                            class="btn btn-outline-secondary rounded-pill px-4 py-2 w-100"
                            :disabled="loading"
                        >
                            Annuler
                        </button>
                        <button
                            @click="supprimerAdherent"
                            class="btn btn-danger rounded-pill px-4 py-2 fw-semibold w-100"
                            :disabled="
                                loading || problemesSuppression.length > 0
                            "
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

        <!-- BOUTON RETOUR -->
        <div class="text-center mt-4">
            <button
                @click="$router.push('/')"
                class="btn btn-outline-secondary btn-lg w-100 rounded-pill py-4 fs-5 fw-semibold d-flex align-items-center justify-content-center"
                :disabled="chargementAdherents"
            >
                <i class="bi bi-arrow-left me-2"></i>
                Retour à l'accueil
            </button>
        </div>
    </div>
</template>

<script>
import { jsPDF } from 'jspdf';
import { authFetch } from '../services/http.js';

export default {
    name: "Adherents",
    data() {
        return {
            adherents: [],
            adherentsFiltres: [],
            searchQuery: "",
            chargementAdherents: true,
            loading: false,
            showModalSuppressionAdherent: false,
            suppressionAdherentReussie: false,
            adherentASupprimer: null,
            problemesSuppression: [],
            tourneesGlobales: [],
            ventesGlobales: [],

            // Nouveaux états pour filtres et tri
            filtreCredit: "tous", // 'tous', 'avec-dette', 'sans-dette'
            tri: "dette-desc", // 'nom', 'dette-desc', 'dette-asc'
        };
    },
    computed: {
        // Texte affiché pour le filtre crédit
        texteFiltreCredit() {
            switch (this.filtreCredit) {
                case "tous":
                    return `Tous (${this.adherents.length})`;
                case "avec-dette":
                    return `Avec dette (${this.nombreAvecDette})`;
                case "sans-dette":
                    return `Sans dette (${this.nombreSansDette})`;
                default:
                    return "Filtre";
            }
        },

        // Statistiques
        nombreAvecDette() {
            return this.adherents.filter((a) => a.credit_total > 0).length;
        },

        nombreSansDette() {
            return this.adherents.filter((a) => a.credit_total === 0).length;
        },

        // Adhérents avec dette pour l'export
        adherentsAvecDette() {
            return this.adherents.filter((a) => a.credit_total > 0);
        },
    },
    async mounted() {
        await this.chargerDonneesGlobales();
        await this.chargerAdherents();
    },
    methods: {
        // 🔹 CHARGER TOUTES LES DONNÉES GLOBALES
        async chargerDonneesGlobales() {
            try {
                const responseTournees = await authFetch("/api/tournees");
                if (responseTournees.ok) {
                    this.tourneesGlobales = await responseTournees.json();
                }

                const responseVentes = await authFetch("/api/ventes");
                if (responseVentes.ok) {
                    this.ventesGlobales = await responseVentes.json();
                }
            } catch (error) {
                console.error("Erreur chargement données globales:", error);
            }
        },

        // 🔹 CHARGEMENT DES ADHÉRENTS
        async chargerAdherents() {
            this.chargementAdherents = true;
            try {
                const response = await authFetch("/api/adherents");
                if (!response.ok) throw new Error("Erreur chargement");
                this.adherents = await response.json();
                this.appliquerFiltres(); // Applique les filtres par défaut
            } catch (error) {
                console.error("Erreur API:", error);
            } finally {
                this.chargementAdherents = false;
            }
        },

        // 🔹 APPLIQUER TOUS LES FILTRES ET TRI
        appliquerFiltres() {
            let resultats = [...this.adherents];

            // 1. Filtre par recherche texte
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                resultats = resultats.filter((adherent) =>
                    adherent.nom.toLowerCase().includes(query)
                );
            }

            // 2. Filtre par crédit
            switch (this.filtreCredit) {
                case "avec-dette":
                    resultats = resultats.filter((a) => a.credit_total > 0);
                    break;
                case "sans-dette":
                    resultats = resultats.filter((a) => a.credit_total === 0);
                    break;
                // 'tous' -> pas de filtre supplémentaire
            }

            // 3. Tri
            switch (this.tri) {
                case "nom":
                    resultats.sort((a, b) => a.nom.localeCompare(b.nom));
                    break;
                case "dette-desc":
                    resultats.sort((a, b) => b.credit_total - a.credit_total);
                    break;
                case "dette-asc":
                    resultats.sort((a, b) => a.credit_total - b.credit_total);
                    break;
            }

            this.adherentsFiltres = resultats;
        },

        // 🔹 CHANGER FILTRE CRÉDIT
        changerFiltreCredit(nouveauFiltre) {
            this.filtreCredit = nouveauFiltre;
            this.appliquerFiltres();
        },

        // 🔹 CHANGER TRI
        changerTri(nouveauTri) {
            this.tri = nouveauTri;
            this.appliquerFiltres();
        },

        // 🔹 RÉINITIALISER FILTRES
        reinitialiserFiltres() {
            this.searchQuery = "";
            this.filtreCredit = "tous";
            this.tri = "dette-desc";
            this.appliquerFiltres();
        },

        // 🔹 EXPORTER EN PDF
        exporterPDF() {
            const adherentsAExporter = this.adherentsAvecDette;

            if (adherentsAExporter.length === 0) {
                alert("Aucun adhérent avec dette à exporter");
                return;
            }

            try {
                const doc = new jsPDF();

                // Configuration
                const pageWidth = doc.internal.pageSize.getWidth();
                let yPosition = 20;
                const lineHeight = 8;
                const margin = 15;

                // Titre
                doc.setFontSize(16);
                doc.setFont(undefined, "bold");
                doc.text(
                    "LISTE DES ADHÉRENTS AVEC DETTE",
                    pageWidth / 2,
                    yPosition,
                    { align: "center" }
                );

                yPosition += lineHeight * 2;

                // Date
                doc.setFontSize(10);
                doc.setFont(undefined, "normal");
                const date = new Date().toLocaleDateString("fr-FR");
                doc.text(`Export du ${date}`, pageWidth / 2, yPosition, {
                    align: "center",
                });

                yPosition += lineHeight * 2;

                // En-tête du tableau
                doc.setFontSize(11);
                doc.setFont(undefined, "bold");
                doc.text("N°", margin, yPosition);
                doc.text("Nom de l'adhérent", margin + 25, yPosition);
                doc.text("Dette", pageWidth - margin - 15, yPosition, {
                    align: "right",
                });

                yPosition += lineHeight;

                // Ligne séparatrice
                doc.line(margin, yPosition, pageWidth - margin, yPosition);
                yPosition += lineHeight;

                // Liste des adhérents
                doc.setFontSize(10);
                doc.setFont(undefined, "normal");

                adherentsAExporter.forEach((adherent, index) => {
                    if (yPosition > doc.internal.pageSize.getHeight() - 20) {
                        doc.addPage();
                        yPosition = 20;
                    }

                    const numero = (index + 1).toString();
                    const nom =
                        adherent.nom.length > 25
                            ? adherent.nom.substring(0, 25) + "..."
                            : adherent.nom;

                    doc.text(numero, margin, yPosition);
                    doc.text(nom, margin + 12, yPosition);
                    doc.text(
                        `${adherent.credit_total.toFixed(2)} €`,
                        pageWidth - margin - 15,
                        yPosition,
                        { align: "right" }
                    );

                    yPosition += lineHeight;
                });

                // Total
                const totalDette = adherentsAExporter.reduce(
                    (total, adherent) =>
                        total + parseFloat(adherent.credit_total),
                    0
                );

                yPosition += lineHeight;
                doc.line(margin, yPosition, pageWidth - margin, yPosition);
                yPosition += lineHeight;

                doc.setFont(undefined, "bold");
                doc.text(
                    `TOTAL DETTE : ${totalDette.toFixed(2)} €`,
                    margin,
                    yPosition
                );
                yPosition += lineHeight;
                doc.text(
                    `Nombre d'adhérents : ${adherentsAExporter.length}`,
                    margin,
                    yPosition
                );

                // Sauvegarder le PDF
                const dateExport = new Date().toISOString().split("T")[0];
                doc.save(`adherents-avec-dette-${dateExport}.pdf`);

                console.log(
                    `✅ PDF exporté : ${adherentsAExporter.length} adhérents avec dette`
                );
            } catch (error) {
                console.error("Erreur génération PDF:", error);
                alert("Erreur lors de la génération du PDF");
            }
        },

        // 🔹 MODIFIER UN ADHÉRENT
        async modifierAdherent(adherent) {
            const nouveauNom = prompt("Nouveau nom :", adherent.nom);
            if (!nouveauNom || nouveauNom === adherent.nom) return;

            this.loading = true;
            try {
                const response = await authFetch(`/api/adherents/${adherent.id}`, {
                    method: "PUT",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        nom: nouveauNom,
                        credit_total: adherent.credit_total,
                    }),
                });

                if (!response.ok) throw new Error("Erreur modification");

                adherent.nom = nouveauNom;
                this.appliquerFiltres();

                console.log(`✅ Adhérent modifié : "${nouveauNom}"`);
            } catch (error) {
                console.error("Erreur modification:", error);
            } finally {
                this.loading = false;
            }
        },

        // 🔹 AFFICHER MODAL SUPPRESSION
        async afficherModalSuppression(adherent) {
            this.adherentASupprimer = adherent;
            this.problemesSuppression = [];
            this.suppressionAdherentReussie = false;

            this.verifierSuppressionPossible(adherent);
            this.showModalSuppressionAdherent = true;
        },

        // 🔹 VÉRIFIER SI LA SUPPRESSION EST POSSIBLE
        verifierSuppressionPossible(adherent) {
            this.problemesSuppression = [];

            if (adherent.credit_total > 0) {
                this.problemesSuppression.push(
                    `Dette de ${adherent.credit_total} € doit être réglée`
                );
            }

            const tourneesEnCoursAdherent = this.tourneesGlobales.filter(
                (t) => t.adherent?.id === adherent.id && t.statut === "en_cours"
            );
            if (tourneesEnCoursAdherent.length > 0) {
                this.problemesSuppression.push(
                    `${tourneesEnCoursAdherent.length} tournée(s) en cours`
                );
            }

            const ventesEnAttenteAdherent = this.ventesGlobales.filter(
                (v) =>
                    v.adherent?.id === adherent.id &&
                    v.mode_paiement === "en_attente"
            );
            if (ventesEnAttenteAdherent.length > 0) {
                this.problemesSuppression.push(
                    `${ventesEnAttenteAdherent.length} vente(s) en attente de paiement`
                );
            }
        },

        // 🔹 SUPPRIMER UN ADHÉRENT
        async supprimerAdherent() {
            if (this.problemesSuppression.length > 0) return;

            this.loading = true;
            try {
                const response = await authFetch(
                    `/api/adherents/${this.adherentASupprimer.id}`,
                    {
                        method: "DELETE",
                        headers: { "Content-Type": "application/json" },
                    }
                );

                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(
                        errorData.error ||
                            `Erreur suppression: ${response.status}`
                    );
                }

                this.loading = false;
                this.suppressionAdherentReussie = true;

                setTimeout(() => {
                    this.adherents = this.adherents.filter(
                        (a) => a.id !== this.adherentASupprimer.id
                    );
                    this.appliquerFiltres();
                    this.showModalSuppressionAdherent = false;
                }, 1500);
            } catch (error) {
                console.error("Erreur complète suppression:", error);
                this.loading = false;
                this.showModalSuppressionAdherent = false;
            }
        },

        // 🔹 FERMER MODAL SUPPRESSION
        fermerModalSuppressionAdherent() {
            this.showModalSuppressionAdherent = false;
            this.adherentASupprimer = null;
            this.problemesSuppression = [];
            this.suppressionAdherentReussie = false;
        },

        // 🔹 VOIR LES DÉTAILS
        voirDetails(adherent) {
            alert(
                `Détails de ${adherent.nom}\nDette : ${adherent.credit_total} €\n\n(Fonctionnalité à venir)`
            );
        },
    },
};
</script>

<style scoped>

.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
