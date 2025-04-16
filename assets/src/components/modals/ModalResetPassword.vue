<template>
    <v-dialog v-model="localIsOpen" max-width="500">
        <template v-slot:activator="{ props: activatorProps }">
            <p class="text-sm font-normal text-gray-700 dark:text-gray-400 text-right">
                <a class="text-right" v-bind="activatorProps">
                    <slot></slot>
                </a>
            </p>
        </template>

        <v-card>
            <v-card-title class="text-h5">{{ title }}</v-card-title>
            <v-card-text>
                {{ message }}
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <div class="flex flex-col w-full">
                    <CustomInput
                        type="email"
                        id="email"
                        placeholder="Renseigner votre email"
                        v-model="resetPasswordEmail"
                    />
                    <div class="error-message text-red-500 text-xs h-4">
                        {{ error }}
                    </div>
                    <div class="flex justify-center gap-10 mt-4">
                        <CustomButton @click="handleResetPassword">Envoyer un lien</CustomButton>
                        <CustomButton @click="closeDialog">Fermer</CustomButton>
                    </div>
                </div>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script setup>
import {inject, ref, watch} from 'vue';
import CustomButton from "../forms/CustomButton.vue";
import CustomInput from "../forms/CustomInput.vue";


const props = defineProps({
    title: {
        type: String,
        default: "Envoyer un lien de réinitialisation",
    },
    message: {
        type: String,
        default: "Veuillez renseigner votre email.",
    },
    isOpen: {
        type: Boolean,
        required: true,
    },
    modelValue: String

});

const alertStore = inject('alertStore');
const emit = defineEmits(["update:isOpen", "forgotPassword", "update:modelValue"]);
const localIsOpen = ref(props.isOpen);
const resetPasswordEmail = ref('');
const error = ref('');

watch(() => props.isOpen, (newVal) => {
    localIsOpen.value = newVal;
});



const closeDialog = () => {
    localIsOpen.value = false;
    emit("update:isOpen", false);
};

const handleResetPassword = async () => {
    if (resetPasswordEmail.value) {
        //Si l'email n'est pas valide
        if(!/\S+@\S+\.\S+/.test(resetPasswordEmail.value)) {
            error.value = 'Veuillez renseigner un email valide.';
            return;
        }
        const response = await fetch('/api/forgot-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email: resetPasswordEmail.value }),
        });

        const result =  await response.json();
        if (!response.ok) {
            error.value = result.message;
            return;
        }
        resetPasswordEmail.value = '';
        localIsOpen.value = false;
        await alertStore.setAlert(result.message, result.type);

    }

}
</script>

<style scoped>
a {
    color: #e2a945;
}
</style>
