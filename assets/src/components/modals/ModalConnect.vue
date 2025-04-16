<template>
    <v-dialog v-model="localIsOpen" max-width="500">
        <template v-slot:activator="{ props: activatorProps }">
            <CustomButton
                :class="isPricingSizeButton ? 'isPricingSizeButton' : ''"
                v-bind="activatorProps">
                <slot></slot>
            </CustomButton>
        </template>

        <v-card>
            <v-card-title class="text-h5">{{ title }}</v-card-title>
            <v-card-text>
                {{ message }}
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <CustomButton v-if="showLoginButton" @click="handleLoginClick">Se connecter</CustomButton>
                <CustomButton @click="closeDialog">Fermer</CustomButton>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import { ref } from 'vue';
import CustomButton from "../forms/CustomButton.vue";

export default {
    name: "ModalConnect",
    components: { CustomButton },
    props: {
        title: {
            type: String,
            default: "Connexion requise",
        },
        message: {
            type: String,
            default: "Veuillez vous authentifier pour accéder à cette fonctionnalité.",
        },
        showLoginButton: {
            type: Boolean,
            default: true,
        },
        isOpen: {
            type: Boolean,
            required: true,
        },
        isPricingSizeButton: {
            type: Boolean,
            default: false,
        },
    },

    setup(props, { emit }) {

        const localIsOpen = ref(props.isOpen);
        const closeDialog = () => {
            localIsOpen.value = false;
            emit("update:isOpen", false);
        };

        const handleLoginClick = () => {
            emit("login");
        };

        return {
            localIsOpen,
            closeDialog,
            handleLoginClick,
            title: props.title,
            message: props.message,
            showLoginButton: props.showLoginButton,
        };
    },
};
</script>


<style scoped lang="scss">
.isPricingSizeButton{
    width: clamp(200px, 50%, 300px);
    height: 40px;
    background: #6366f1;
}
</style>
