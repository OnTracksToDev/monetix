<template>
    <div class="container-custom py-4">
        <!-- En-tête -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bold text-primary display-6 mb-2">
                    <i class="bi bi-graph-up me-2"></i>Historique complet
                </h1>
                <p class="text-muted mb-0">Toutes les opérations de caisse</p>
            </div>
            <button
                @click="exporterPDF"
                class="btn btn-primary rounded-pill py-3 px-3 d-flex align-items-center"
                :disabled="mouvementsFiltres.length === 0"
                :title="
                    mouvementsFiltres.length === 0
                        ? 'Aucune donnée à exporter'
                        : 'Exporter en PDF'
                "
            >
                <i class="bi bi-file-earmark-pdf fs-5"></i>
            </button>
        </div>

        <!-- Spinner de chargement -->
        <div v-if="chargementEnCours" class="text-center py-5">
            <div
                class="spinner-border text-primary"
                style="width: 3rem; height: 3rem"
                role="status"
            >
                <span class="visually-hidden">Chargement...</span>
            </div>
            <p class="mt-3 text-muted">Chargement de l'historique...</p>
        </div>

        <!-- Contenu principal -->
        <div v-else>
            <!-- BARRE DE FILTRES -->
            <div class="row g-3 mb-4">
                <!-- FILTRE TYPE -->
                <div class="col-12 col-md-6">
                    <div class="dropdown">
                        <button
                            class="btn btn-outline-primary w-100 rounded-pill py-3 d-flex align-items-center justify-content-between"
                            type="button"
                            data-bs-toggle="dropdown"
                        >
                            <span>{{ texteFiltreType }}</span>
                            <i class="bi bi-chevron-down ms-2"></i>
                        </button>
                        <ul class="dropdown-menu w-100">
                            <li>
                                <button
                                    class="dropdown-item d-flex align-items-center"
                                    :class="{ active: filtreType === 'all' }"
                                    @click="changerFiltreType('all')"
                                >
                                    <i class="bi bi-funnel me-2"></i>
                                    Tous les types
                                    <span class="ms-2"
                                        >({{ tousLesMouvements.length }})</span
                                    >
                                </button>
                            </li>
                            <li>
                                <button
                                    class="dropdown-item d-flex align-items-center"
                                    :class="{
                                        active: filtreType === 'depense',
                                    }"
                                    @click="changerFiltreType('depense')"
                                >
                                    <i
                                        class="bi bi-arrow-up-circle me-2 text-danger"
                                    ></i>
                                    Dépenses
                                    <span class="ms-2"
                                        >({{ nombreDepenses }})</span
                                    >
                                </button>
                            </li>
                            <li>
                                <button
                                    class="dropdown-item d-flex align-items-center"
                                    :class="{ active: filtreType === 'avance' }"
                                    @click="changerFiltreType('avance')"
                                >
                                    <i
                                        class="bi bi-cash-coin me-2 text-warning"
                                    ></i>
                                    Avances
                                    <span class="ms-2"
                                        >({{ nombreAvances }})</span
                                    >
                                </button>
                            </li>
                            <li>
                                <button
                                    class="dropdown-item d-flex align-items-center"
                                    :class="{
                                        active: filtreType === 'remboursement',
                                    }"
                                    @click="changerFiltreType('remboursement')"
                                >
                                    <i
                                        class="bi bi-arrow-down-circle me-2 text-success"
                                    ></i>
                                    Remboursements
                                    <span class="ms-2"
                                        >({{ nombreRemboursements }})</span
                                    >
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- FILTRE GESTIONNAIRE -->
                <div class="col-12 col-md-6">
                    <div class="dropdown">
                        <button
                            class="btn btn-outline-secondary w-100 rounded-pill py-3 d-flex align-items-center justify-content-between"
                            type="button"
                            data-bs-toggle="dropdown"
                        >
                            <span>{{ texteFiltreGestionnaire }}</span>
                            <i class="bi bi-chevron-down ms-2"></i>
                        </button>
                        <ul class="dropdown-menu w-100">
                            <li>
                                <button
                                    class="dropdown-item d-flex align-items-center"
                                    :class="{
                                        active: filtreGestionnaire === 'all',
                                    }"
                                    @click="changerFiltreGestionnaire('all')"
                                >
                                    <i class="bi bi-people me-2"></i>
                                    Tous les gestionnaires
                                </button>
                            </li>
                            <li
                                v-for="gestionnaire in gestionnaires"
                                :key="gestionnaire"
                            >
                                <button
                                    class="dropdown-item d-flex align-items-center"
                                    :class="{
                                        active:
                                            filtreGestionnaire === gestionnaire,
                                    }"
                                    @click="
                                        changerFiltreGestionnaire(gestionnaire)
                                    "
                                >
                                    <i class="bi bi-person me-2"></i>
                                    {{ gestionnaire }}
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Statistiques adaptatives -->
            <div class="row mb-4">
                <template
                    v-for="(stat, index) in statistiquesAdaptatives"
                    :key="index"
                >
                    <div v-if="stat.valeur !== ''" class="col-6 col-md-3 mb-3">
                        <div
                            class="card text-white text-center border-0 rounded-4"
                            :class="stat.couleur"
                        >
                            <div class="card-body py-3">
                                <div class="fs-4 fw-bold">
                                    {{ stat.valeur }}
                                </div>
                                <small class="opacity-90">{{
                                    stat.titre
                                }}</small>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Liste complète -->
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Date</th>
                                    <th>Type</th>
                                    <th>Gestionnaire</th>
                                    <th>Description</th>
                                    <th class="text-end pe-4">Montant</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="mouvement in mouvementsFiltres"
                                    :key="mouvement.id"
                                    class="border-bottom"
                                >
                                    <td class="fw-bold ps-4">
                                        {{ formatDateComplete(mouvement.date) }}
                                    </td>
                                    <td>
                                        <span
                                            class="badge rounded-pill"
                                            :class="
                                                getBadgeClass(mouvement.type)
                                            "
                                        >
                                            <i
                                                :class="
                                                    getTypeIcon(mouvement.type)
                                                "
                                                class="me-1"
                                            ></i>
                                            {{ getTypeLabel(mouvement.type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i
                                                class="bi bi-person me-2 text-muted"
                                            ></i>
                                            {{ mouvement.gestionnaire }}
                                        </div>
                                    </td>
                                    <td>{{ mouvement.description }}</td>
                                    <td
                                        class="text-end fw-bold pe-4"
                                        :class="getMontantClass(mouvement.type)"
                                    >
                                        {{ mouvement.montant }} €
                                    </td>
                                    <td>
                                        <span
                                            v-if="mouvement.type === 'avance'"
                                            class="badge rounded-pill"
                                            :class="getStatutBadge(mouvement)"
                                        >
                                            <i
                                                :class="
                                                    getStatutIcon(mouvement)
                                                "
                                                class="me-1"
                                            ></i>
                                            {{ getStatutText(mouvement) }}
                                        </span>
                                        <span v-else class="text-muted">—</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div
                        v-if="mouvementsFiltres.length === 0"
                        class="text-center py-5 text-muted"
                    >
                        <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                        <p class="fs-5 mt-3 mb-2">Aucune opération trouvée</p>
                        <p class="text-muted">
                            Essayez de modifier vos filtres
                        </p>
                        <button
                            @click="reinitialiserFiltres"
                            class="btn btn-outline-primary rounded-pill mt-2"
                        >
                            <i class="bi bi-arrow-clockwise me-2"></i>
                            Réinitialiser les filtres
                        </button>
                    </div>
                </div>
            </div>

            <!-- BOUTON RETOUR -->
            <div class="text-center mt-4">
                <button
                    @click="$router.push('/caisse')"
                    class="btn btn-outline-secondary btn-sm w-100 rounded-pill py-3 fs-6 fw-semibold d-flex align-items-center justify-content-center"
                >
                    <i class="bi bi-arrow-left me-2"></i>
                    Retour Caisse
                </button>
            </div>

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
    </div>
</template>

<script>
import { jsPDF } from "jspdf";
import { authFetch } from '../services/http.js';

export default {
    name: "CaisseHistoriqueComplet",
    data() {
        return {
            tousLesMouvements: [],
            filtreType: "all",
            filtreGestionnaire: "all",
            gestionnaires: [],
            chargementEnCours: true,
        };
    },
    computed: {
        mouvementsFiltres() {
            let filtered = [...this.tousLesMouvements];

            if (this.filtreType !== "all") {
                filtered = filtered.filter(
                    (mouvement) => mouvement.type === this.filtreType
                );
            }

            if (this.filtreGestionnaire !== "all") {
                filtered = filtered.filter(
                    (mouvement) =>
                        mouvement.gestionnaire === this.filtreGestionnaire
                );
            }

            return filtered.sort((a, b) => new Date(b.date) - new Date(a.date));
        },

        // Textes des filtres
        texteFiltreType() {
            switch (this.filtreType) {
                case "all":
                    return `Types (${this.tousLesMouvements.length})`;
                case "depense":
                    return `Dépenses (${this.nombreDepenses})`;
                case "avance":
                    return `Avances (${this.nombreAvances})`;
                case "remboursement":
                    return `Remboursements (${this.nombreRemboursements})`;
                default:
                    return "Type";
            }
        },

        texteFiltreGestionnaire() {
            return this.filtreGestionnaire === "all"
                ? "Gestionnaires"
                : this.filtreGestionnaire;
        },

        // Statistiques de comptage
        nombreDepenses() {
            return this.tousLesMouvements.filter((m) => m.type === "depense")
                .length;
        },

        nombreAvances() {
            return this.tousLesMouvements.filter((m) => m.type === "avance")
                .length;
        },

        nombreRemboursements() {
            return this.tousLesMouvements.filter(
                (m) => m.type === "remboursement"
            ).length;
        },

        // Calculs de base réutilisables
        totalOperations() {
            return this.mouvementsFiltres.length;
        },

        totalMontant() {
            return this.mouvementsFiltres
                .reduce((sum, m) => sum + parseFloat(m.montant), 0)
                .toFixed(2);
        },

        montantMoyen() {
            return this.totalOperations > 0
                ? (
                      parseFloat(this.totalMontant) / this.totalOperations
                  ).toFixed(2)
                : "0.00";
        },

        plusGrosMontant() {
            return this.totalOperations > 0
                ? Math.max(
                      ...this.mouvementsFiltres.map((m) =>
                          parseFloat(m.montant)
                      )
                  ).toFixed(2)
                : "0.00";
        },

        // Statistiques par type
        totalDepenses() {
            return this.mouvementsFiltres
                .filter((m) => m.type === "depense")
                .reduce((sum, m) => sum + parseFloat(m.montant), 0)
                .toFixed(2);
        },

        totalAvances() {
            return this.mouvementsFiltres
                .filter((m) => m.type === "avance")
                .reduce((sum, m) => sum + parseFloat(m.montant), 0)
                .toFixed(2);
        },

        totalRemboursements() {
            return this.mouvementsFiltres
                .filter((m) => m.type === "remboursement")
                .reduce((sum, m) => sum + parseFloat(m.montant), 0)
                .toFixed(2);
        },

        // Pour les avances : calculs de remboursement
        totalRembourseAvances() {
            const avances = this.mouvementsFiltres.filter(
                (m) => m.type === "avance"
            );
            return avances
                .reduce(
                    (sum, m) =>
                        sum + (parseFloat(m.montantRemboursePartiel) || 0),
                    0
                )
                .toFixed(2);
        },

        resteAPayerAvances() {
            const totalAvances = parseFloat(this.totalAvances);
            const totalRembourse = parseFloat(this.totalRembourseAvances);
            return (totalAvances - totalRembourse).toFixed(2);
        },

        tauxRemboursement() {
            const totalAvances = parseFloat(this.totalAvances);
            if (totalAvances === 0) return "0";
            const totalRembourse = parseFloat(this.totalRembourseAvances);
            return ((totalRembourse / totalAvances) * 100).toFixed(0);
        },

        // Pour les gestionnaires : argent reçu vs rendu
        totalArgentRecuGestionnaire() {
            return this.mouvementsFiltres
                .filter((m) => m.type === "depense" || m.type === "avance")
                .reduce((sum, m) => sum + parseFloat(m.montant), 0)
                .toFixed(2);
        },

        totalArgentRenduGestionnaire() {
            return this.mouvementsFiltres
                .filter((m) => m.type === "remboursement")
                .reduce((sum, m) => sum + parseFloat(m.montant), 0)
                .toFixed(2);
        },

        resteDuGestionnaire() {
            const recu = parseFloat(this.totalArgentRecuGestionnaire);
            const rendu = parseFloat(this.totalArgentRenduGestionnaire);
            return (recu - rendu).toFixed(2);
        },

        // Statistiques adaptatives principales
        statistiquesAdaptatives() {
            // Cas 1: Filtre par gestionnaire + type spécifique
            if (
                this.filtreGestionnaire !== "all" &&
                this.filtreType !== "all"
            ) {
                return this.getStatsGestionnaireType();
            }

            // Cas 2: Filtre par gestionnaire seulement
            if (this.filtreGestionnaire !== "all") {
                return this.getStatsGestionnaire();
            }

            // Cas 3: Filtre par type seulement
            if (this.filtreType !== "all") {
                return this.getStatsType();
            }

            // Cas 4: Vue globale (pas de filtre)
            return this.getStatsGlobales();
        },
    },

    async mounted() {
        await this.chargerHistoriqueComplet();
        this.extraireGestionnaires();
        this.chargementEnCours = false;
    },

    methods: {
        // 🔹 CHANGER FILTRES
        changerFiltreType(type) {
            this.filtreType = type;
        },

        changerFiltreGestionnaire(gestionnaire) {
            this.filtreGestionnaire = gestionnaire;
        },

        reinitialiserFiltres() {
            this.filtreType = "all";
            this.filtreGestionnaire = "all";
        },

        // 🔹 EXPORT PDF AVEC FILTRES
        exporterPDF() {
            if (this.mouvementsFiltres.length === 0) {
                alert("Aucune donnée à exporter avec les filtres actuels");
                return;
            }

            try {
                const doc = new jsPDF();

                // Configuration
                const pageWidth = doc.internal.pageSize.getWidth();
                let yPosition = 20;
                const lineHeight = 8;
                const margin = 15;

                // Titre avec informations des filtres
                doc.setFontSize(16);
                doc.setFont(undefined, "bold");

                let titre = "HISTORIQUE COMPLET DES OPÉRATIONS";
                if (
                    this.filtreType !== "all" ||
                    this.filtreGestionnaire !== "all"
                ) {
                    titre = "RAPPORT FILTRÉ DES OPÉRATIONS";
                }

                doc.text(titre, pageWidth / 2, yPosition, { align: "center" });
                yPosition += lineHeight * 2;

                // Informations des filtres
                doc.setFontSize(10);
                doc.setFont(undefined, "normal");

                const date = new Date().toLocaleDateString("fr-FR");
                let infosFiltres = `Export du ${date}`;

                if (this.filtreType !== "all") {
                    infosFiltres += ` • Type: ${this.getTypeLabel(
                        this.filtreType
                    )}`;
                }
                if (this.filtreGestionnaire !== "all") {
                    infosFiltres += ` • Gestionnaire: ${this.filtreGestionnaire}`;
                }

                doc.text(infosFiltres, pageWidth / 2, yPosition, {
                    align: "center",
                });
                yPosition += lineHeight * 3;

                // En-tête du tableau
                doc.setFontSize(10);
                doc.setFont(undefined, "bold");
                doc.text("Date", margin, yPosition);
                doc.text("Type", margin + 35, yPosition);
                doc.text("Gestionnaire", margin + 70, yPosition);
                doc.text("Description", margin + 120, yPosition);
                doc.text("Montant", pageWidth - margin - 15, yPosition, {
                    align: "right",
                });

                yPosition += lineHeight;

                // Ligne séparatrice
                doc.line(margin, yPosition, pageWidth - margin, yPosition);
                yPosition += lineHeight;

                // Liste des opérations
                doc.setFontSize(8);
                doc.setFont(undefined, "normal");

                this.mouvementsFiltres.forEach((mouvement, index) => {
                    // Vérifier si on dépasse la page
                    if (yPosition > doc.internal.pageSize.getHeight() - 20) {
                        doc.addPage();
                        yPosition = 20;
                    }

                    const dateFormatee = new Date(
                        mouvement.date
                    ).toLocaleDateString("fr-FR");
                    const type = this.getTypeLabel(mouvement.type);
                    const gestionnaire = mouvement.gestionnaire;
                    const description =
                        mouvement.description.length > 30
                            ? mouvement.description.substring(0, 30) + "..."
                            : mouvement.description;
                    const montant = `${parseFloat(mouvement.montant).toFixed(
                        2
                    )} €`;

                    doc.text(dateFormatee, margin, yPosition);
                    doc.text(type, margin + 35, yPosition);
                    doc.text(gestionnaire, margin + 70, yPosition);
                    doc.text(description, margin + 120, yPosition);
                    doc.text(montant, pageWidth - margin - 15, yPosition, {
                        align: "right",
                    });

                    yPosition += lineHeight;
                });

                // Sauvegarder le PDF avec nom adapté aux filtres
                let nomFichier = "historique-complet";
                if (this.filtreType !== "all") {
                    nomFichier += `-${this.filtreType}`;
                }
                if (this.filtreGestionnaire !== "all") {
                    nomFichier += `-${this.filtreGestionnaire}`;
                }
                nomFichier += `-${new Date().toISOString().split("T")[0]}.pdf`;

                doc.save(nomFichier);

                console.log(
                    `✅ PDF exporté : ${this.totalOperations} opérations filtrées`
                );
            } catch (error) {
                console.error("Erreur génération PDF:", error);
                alert("Erreur lors de la génération du PDF");
            }
        },
        // Méthodes pour chaque cas de statistiques (inchangées)
        getStatsGlobales() {
            return [
                {
                    titre: "Opérations",
                    valeur: this.totalOperations,
                    couleur: "bg-primary",
                },
                {
                    titre: "Dépenses totales",
                    valeur: this.totalDepenses + " €",
                    couleur: "bg-danger",
                },
                {
                    titre: "Avances totales",
                    valeur: this.totalAvances + " €",
                    couleur: "bg-warning",
                },
                {
                    titre: "Remboursements",
                    valeur: this.totalRemboursements + " €",
                    couleur: "bg-success",
                },
            ];
        },

        getStatsGestionnaire() {
            return [
                {
                    titre: "Opérations",
                    valeur: this.totalOperations,
                    couleur: "bg-primary",
                },
                {
                    titre: "Argent reçu",
                    valeur: this.totalArgentRecuGestionnaire + " €",
                    couleur: "bg-warning",
                },
                {
                    titre: "Argent rendu",
                    valeur: this.totalArgentRenduGestionnaire + " €",
                    couleur: "bg-success",
                },
                {
                    titre: "Reste dû",
                    valeur: this.resteDuGestionnaire + " €",
                    couleur: "bg-danger",
                },
            ];
        },

        getStatsType() {
            switch (this.filtreType) {
                case "depense":
                    return [
                        {
                            titre: "Dépenses",
                            valeur: this.totalOperations,
                            couleur: "bg-primary",
                        },
                        {
                            titre: "Montant total",
                            valeur: this.totalMontant + " €",
                            couleur: "bg-danger",
                        },
                        {
                            titre: "Moyenne",
                            valeur: this.montantMoyen + " €",
                            couleur: "bg-info",
                        },
                        {
                            titre: "Plus grosse",
                            valeur: this.plusGrosMontant + " €",
                            couleur: "bg-warning",
                        },
                    ];

                case "avance":
                    return [
                        {
                            titre: "Avances",
                            valeur: this.totalOperations,
                            couleur: "bg-primary",
                        },
                        {
                            titre: "Total avancé",
                            valeur: this.totalAvances + " €",
                            couleur: "bg-warning",
                        },
                        {
                            titre: "Remboursé",
                            valeur: this.totalRembourseAvances + " €",
                            couleur: "bg-success",
                        },
                        {
                            titre: "Reste à payer",
                            valeur: this.resteAPayerAvances + " €",
                            couleur: "bg-danger",
                        },
                    ];

                case "remboursement":
                    return [
                        {
                            titre: "Remboursements",
                            valeur: this.totalOperations,
                            couleur: "bg-primary",
                        },
                        {
                            titre: "Montant total",
                            valeur: this.totalMontant + " €",
                            couleur: "bg-success",
                        },
                        {
                            titre: "Moyenne",
                            valeur: this.montantMoyen + " €",
                            couleur: "bg-info",
                        },
                        {
                            titre: "Plus gros",
                            valeur: this.plusGrosMontant + " €",
                            couleur: "bg-warning",
                        },
                    ];

                default:
                    return this.getStatsGlobales();
            }
        },

        getStatsGestionnaireType() {
            const gestionnaire = this.filtreGestionnaire;
            const type = this.filtreType;

            switch (type) {
                case "depense":
                    return [
                        {
                            titre: "Dépenses " + gestionnaire,
                            valeur: this.totalOperations,
                            couleur: "bg-primary",
                        },
                        {
                            titre: "Montant total",
                            valeur: this.totalMontant + " €",
                            couleur: "bg-danger",
                        },
                        {
                            titre: "Moyenne",
                            valeur: this.montantMoyen + " €",
                            couleur: "bg-info",
                        },
                        {
                            titre: "Plus grosse",
                            valeur: this.plusGrosMontant + " €",
                            couleur: "bg-warning",
                        },
                    ];

                case "avance":
                    return [
                        {
                            titre: "Avances " + gestionnaire,
                            valeur: this.totalOperations,
                            couleur: "bg-primary",
                        },
                        {
                            titre: "Total avancé",
                            valeur: this.totalAvances + " €",
                            couleur: "bg-warning",
                        },
                        {
                            titre: "Remboursé",
                            valeur: this.totalRembourseAvances + " €",
                            couleur: "bg-success",
                        },
                        {
                            titre: "Reste à payer",
                            valeur: this.resteAPayerAvances + " €",
                            couleur: "bg-danger",
                        },
                    ];

                case "remboursement":
                    return [
                        {
                            titre: "Remboursements " + gestionnaire,
                            valeur: this.totalOperations,
                            couleur: "bg-primary",
                        },
                        {
                            titre: "Montant total",
                            valeur: this.totalMontant + " €",
                            couleur: "bg-success",
                        },
                        {
                            titre: "Moyenne",
                            valeur: this.montantMoyen + " €",
                            couleur: "bg-info",
                        },
                        {
                            titre: "Plus gros",
                            valeur: this.plusGrosMontant + " €",
                            couleur: "bg-warning",
                        },
                    ];

                default:
                    return this.getStatsGestionnaire();
            }
        },

        // 🔹 ICÔNES POUR L'UI
        getTypeIcon(type) {
            const icons = {
                depense: "bi-arrow-up-circle",
                avance: "bi-cash-coin",
                remboursement: "bi-arrow-down-circle",
            };
            return icons[type] || "bi-circle";
        },

        getStatutIcon(mouvement) {
            if (mouvement.rembourse) return "bi-check-circle";
            if (mouvement.montantRemboursePartiel > 0) return "bi-arrow-repeat";
            return "bi-clock";
        },

        async chargerHistoriqueComplet() {
            try {
                const response = await authFetch("/api/mouvements-caisse");
                if (response.ok) {
                    this.tousLesMouvements = await response.json();
                }
            } catch (error) {
                console.error("Erreur chargement historique", error);
            }
        },

        extraireGestionnaires() {
            const gestionnairesSet = new Set();
            this.tousLesMouvements.forEach((m) =>
                gestionnairesSet.add(m.gestionnaire)
            );
            this.gestionnaires = Array.from(gestionnairesSet).sort();
        },

        getBadgeClass(type) {
            const classes = {
                depense: "bg-danger",
                avance: "bg-warning text-dark",
                remboursement: "bg-success",
            };
            return classes[type] || "bg-secondary";
        },

        getTypeLabel(type) {
            const labels = {
                depense: "Dépense",
                avance: "Avance",
                remboursement: "Remboursement",
            };
            return labels[type] || type;
        },

        getMontantClass(type) {
            return type === "remboursement" ? "text-success" : "text-danger";
        },

        getStatutBadge(mouvement) {
            if (mouvement.rembourse) return "bg-success";
            if (mouvement.montantRemboursePartiel > 0) return "bg-info";
            return "bg-warning text-dark";
        },

        getStatutText(mouvement) {
            if (mouvement.rembourse) return "Remboursé";
            if (mouvement.montantRemboursePartiel > 0) {
                const reste =
                    mouvement.montant - mouvement.montantRemboursePartiel;
                return `${reste}€ restant`;
            }
            return "À rembourser";
        },

        formatDateComplete(dateString) {
            return new Date(dateString).toLocaleDateString("fr-FR", {
                day: "2-digit",
                month: "2-digit",
                year: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            });
        },
    },
};
</script>

<style scoped>

.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

</style>
