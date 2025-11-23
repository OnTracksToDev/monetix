import { createRouter, createWebHistory } from "vue-router";
import Accueil from "../components/Accueil.vue";
import Vente from "../components/Vente.vue";
import TourneeDetail from "../components/TourneeDetail.vue";
import Historique from "../components/Historique.vue";
import Adherents from "../components/Adherents.vue";
import Caisse from "../components/Caisse.vue";
import CaisseHistoriqueComplet from "../components/CaisseHistoriqueComplet.vue";
import Login from "../components/Login.vue";
import ForgotPassword from "../components/ForgotPassword.vue";
import ResetPassword from "../components/ResetPassword.vue";
import authService from "../services/auth.js";

const routes = [
    // Routes publiques
    { path: "/login", name: "Login", component: Login },
    { path: "/forgot-password", name: "ForgotPassword", component: ForgotPassword },
    { path: "/reset-password/:token", name: "ResetPassword", component: ResetPassword, props: true },

    // Routes privées
    { path: "/", name: "Accueil", component: Accueil },
    { path: "/vente", name: "Vente", component: Vente },
    { path: "/tournee/:id", name: "TourneeDetail", component: TourneeDetail },
    { path: "/historique", name: "Historique", component: Historique },
    { path: "/adherents", name: "Adherents", component: Adherents },
    { path: "/caisse", name: "Caisse", component: Caisse },
    { path: "/caisse-historique-complet", name: "CaisseHistoriqueComplet", component: CaisseHistoriqueComplet },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Guard global
router.beforeEach((to, from, next) => {
    const publicPages = ["/login", "/forgot-password", "/reset-password"];
    const authRequired = !publicPages.some(path => to.path.startsWith(path));
    const loggedIn = authService.isAuthenticated();

    // Si route privée et pas connecté → login
    if (authRequired && !loggedIn) {
        return next("/login");
    }

    // Si route publique et connecté → accueil
    if (publicPages.includes(to.path) && loggedIn) {
        return next("/");
    }

    next();
});

export default router;
