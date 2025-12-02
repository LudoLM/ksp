<template>
  <v-dialog v-model="localIsOpen" max-width="500">
    <template v-slot:activator="{ props: activatorProps }" class="flex justify-center align-center">
      <button v-bind="activatorProps">
        <slot></slot>
      </button>
    </template>

    <v-card>
      <v-card-title class="text-h5">{{ title }}</v-card-title>
      <v-card-text>
        {{ message }}

        <!-- Sélecteur avec tous les utilisateurs -->
        <v-select
            v-model="selectedUser"
            :items="userOptions"
            item-title="nom"
            item-value="id"
            bg-color="red"
            label="Utilisateur"
        ></v-select>
      </v-card-text>

      <v-card-actions>
        <v-spacer></v-spacer>
        <CustomButton v-if="selectedUser !== null" @click="handleAddExtraClick">Ajouter</CustomButton>
        <CustomButton @click="closeDialog">Fermer</CustomButton>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import CustomButton from '../forms/CustomButton.vue';
import { useSubscription } from "../../utils/useSubscribing";
import {useUserStore} from "../../store/user";

// Définition des props
const props = defineProps({
  title: {
    type: String,
    default: "Ajouter un extra",
  },
  message: {
    type: String,
    default: "Sélectionner l'extra à ajouter.",
  },
  isOpen: {
    type: Boolean,
    required: true,
  },
  cours: {
    type: Number,
    required: true,
  },
});
// Définition des événements
const emit = defineEmits(['subscriptionResponse', 'update:isOpen']);

const localIsOpen = ref(props.isOpen);
const selectedUser = ref(null);
const users = ref([]);
const usersAdded = ref([]);

// Utilisation de computed pour filtrer users en fonction de usersAdded
const userOptions = computed(() =>
    users.value
        .filter(user => !usersAdded.value.includes(user.id))
        .map(user => ({
          id: user.id,
          nom: `${user.nom} ${user.prenom}`,
        }))
);


const getUsers = async () => {
  const response = await fetch(`/api/admin/users-not-in-cours/${props.cours}`, {
    method: 'GET',
  });


  if (response.ok) {
    users.value = await response.json();
  } else {
    console.error("Erreur lors de la récupération des utilisateurs");
  }
};

onMounted(async () => {
  await getUsers();
});

const closeDialog = () => {
  localIsOpen.value = false;
  emit('update:isOpen', false); // Émet l'événement pour fermer le dialogue
};

const handleAddExtraClick = async () => {
  const result = await useSubscription(props.cours, false, selectedUser.value);
  if (result.success) {
    emit('subscriptionResponse', {
      type: 'success',
      message: result.message,
      statusChange: result.statusChange
    });
  } else {
    emit('subscriptionResponse', {
      type: 'error',
      message: result.message,
      statusChange: result.statusChange
    });

  }
  usersAdded.value.push(selectedUser.value);
  selectedUser.value = null;
  closeDialog();
};
</script>
