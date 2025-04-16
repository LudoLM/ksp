<template>
    <v-dialog v-model="localIsOpen" max-width="500">
        <template v-slot:activator="{ props: activatorProps }">
            <div
                v-bind="activatorProps">
                <slot></slot>
            </div>
        </template>

        <v-card>
            <v-card-title class="text-h5">{{ title }}</v-card-title>
            <v-card-text>
                {{ message }}
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <CustomButton v-if="showConfirmButton" @click="handleConfirmClick">Confirmer</CustomButton>
                <CustomButton @click="closeDialog">Fermer</CustomButton>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import { ref } from 'vue';
import CustomButton from "../forms/CustomButton.vue";

export default {
    name: "ModalConfirm",
    components: { CustomButton },
    props: {
        title: {
            type: String,
            default: "Confirmer ",
        },
        message: {
            type: String,
            default: "Etes-vous sûr?",
        },
        showConfirmButton: {
            type: Boolean,
            default: true,
        },
        isOpen: {
            type: Boolean,
            required: true,
        },
    },

    setup(props, { emit }) {

        const localIsOpen = ref(props.isOpen);
        const closeDialog = () => {
            localIsOpen.value = false;
            emit("update:isOpen", false);
        };

        const handleConfirmClick = () => {
            closeDialog();
            emit("confirmActions");
        };

        return {
            localIsOpen,
            closeDialog,
            handleConfirmClick,
            title: props.title,
            message: props.message,
            showLoginButton: props.showConfirmButton,
        };
    },
};
</script>

