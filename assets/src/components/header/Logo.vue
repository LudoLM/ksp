<template>
    <div class="logo">
        <router-link :to="{ name: 'Accueil' }">
            <img src="../../../../public/images/logo.png" alt="logo de kine sport santé">
        </router-link>
    </div>
</template>

<style scoped lang="scss">
    .logo {
        width: 25vw;
        min-width: 170px;
        max-width: 200px;
    }
</style>
