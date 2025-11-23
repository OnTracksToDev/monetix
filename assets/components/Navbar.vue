<template>
    <nav class="navbar navbar-dark bg-primary shadow-sm fixed-top py-2">
        <div
            class="container-fluid d-flex align-items-center justify-content-between position-relative"
        >
            <!-- Logo avec pastille ronde -->
            <router-link
                to="/"
                class="navbar-brand d-flex align-items-center ms-2"
            >
                <div
                    class="logo-container rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center"
                >
                    <img
                        src="/images/logo.png"
                        alt="Logo"
                        class="logo-img-nav"
                    />
                </div>
            </router-link>

            <!-- Nom utilisateur centré -->
            <div
                class="user-name position-absolute start-50 translate-middle-x text-white fw-semibold text-uppercase"
            >
                {{ user?.nom || "Utilisateur" }}
            </div>

            <!-- Burger + menu -->
            <div class="dropdown">
                <button
                    class="navbar-toggler border-0 px-2"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                >
                    <span class="navbar-toggler-icon"></span>
                </button>

                <ul
                    class="dropdown-menu dropdown-menu-end mt-2 shadow border-0 rounded-3 overflow-hidden"
                >
                    <li>
                        <button
                            @click="logout"
                            class="dropdown-item text-danger py-2 d-flex align-items-center justify-content-start fw-semibold"
                        >
                            <i class="bi bi-box-arrow-right me-2"></i
                            >Déconnexion
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</template>

<script>
import authService from "../services/auth.js";

export default {
    name: "Navbar",
    data() {
        return {
            user: authService.getCurrentUser(),
        };
    },
    methods: {
        logout() {
            authService.logout();
            this.$router.push("/login");
        },
    },
};
</script>

<style scoped>
.logo-container {
    width: 50px;
    height: 50px;
    padding: 6px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); 
}

.logo-img-nav {
    height: 32px;
    width: auto;
    
}

.user-name {
    font-size: 1rem;
    letter-spacing: 0.3px;
}


@keyframes dropdownFade {
    from {
        opacity: 0;
        transform: translateY(-5px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 576px) {
    .navbar {
        padding: 0.4rem 1rem;
    }

    .user-name {
        font-size: 0.9rem;
    }

    .logo-container {
        width: 44px;
        height: 44px;
        padding: 5px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); 
    }

    .logo-img-nav {
        height: 28px;
    }
}
</style>
