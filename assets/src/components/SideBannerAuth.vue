<template>
    <div class="banner relative items-center hidden w-full h-full lg:w-1/2 lg:grid">
        <div class="overlay flex items-center justify-center">
            <div class="logoWrapper flex flex-col items-center max-w-xs">
                <router-link to="/" class="block mb-4">
                    <h3>Kiné Sport Santé</h3>
                </router-link>
                <p class="text-center text-gray-300 dark:text-white/70">
                    Soulagez vos douleurs et renforcez votre bien-être.
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss">
.banner {
    background: url('../../images/banners/imageBanner13.jpg') no-repeat center center / cover;

    .overlay {
        background: rgba(30, 27, 75, .9);
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
    }

    h3{
        color: #fff;
    }

    p{
        color: #d9d9d9;
        font-size: clamp(0.6rem, 1.2vw, .8rem);
    };
}
</style>
