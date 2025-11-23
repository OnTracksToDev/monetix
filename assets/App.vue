<template>
    <div id="app">
        <Navbar v-if="showNavbar" />
        <div :class="['main-content', { 'with-navbar': showNavbar }]">
            <router-view />
        </div>
    </div>
</template>

<script>
import Navbar from "./components/Navbar.vue";
import authService from "./services/auth.js";

export default {
    name: "App",
    components: {
        Navbar,
    },
    data() {
        return {
            isAuthenticated: authService.isAuthenticated(),
        };
    },
    computed: {
        showNavbar() {
            // Navbar visible seulement pour les routes privées
            const publicRoutes = ["/login", "/forgot-password", "/reset-password"];
            const isPublicRoute = publicRoutes.some(path => this.$route.path.startsWith(path));
            return this.isAuthenticated && !isPublicRoute;
        },
    },
    mounted() {
        // Vérifier l'authentification au chargement
        this.checkAuth();

        const publicRoutes = ["/login", "/forgot-password", "/reset-password"];
        const isPublicRoute = publicRoutes.some(path => this.$route.path.startsWith(this.$route.path));

        if (!this.isAuthenticated && !isPublicRoute) {
            this.$router.push("/login");
        }
    },
    watch: {
        $route(to, from) {
            this.checkAuth();

            const publicRoutes = ["/login", "/forgot-password", "/reset-password"];
            const isPublicRoute = publicRoutes.some(path => to.path.startsWith(path));

            if (!this.isAuthenticated && !isPublicRoute) {
                this.$router.push("/login");
            }
        },
    },
    methods: {
        checkAuth() {
            this.isAuthenticated = authService.isAuthenticated();
        },
    },
};
</script>

<style scoped>
.main-content.with-navbar {
    margin-top: 76px; /* Margin seulement quand navbar est visible */
}
</style>
