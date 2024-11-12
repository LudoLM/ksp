<template>
  <v-dialog v-model="localIsOpen" max-width="500">
    <template v-slot:activator="{ props: activatorProps }">
      <CustomButton v-bind="activatorProps">
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
        <CustomButton v-if="showLoginButton" @click="handleLoginClick">Login</CustomButton>
        <CustomButton @click="closeDialog">Fermer</CustomButton>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import { ref } from 'vue';
import CustomButton from "../CustomButton.vue";

export default {
  name: "ModalConfirm",
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
