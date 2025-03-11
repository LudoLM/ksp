<template>
    <v-dialog v-model="localIsOpen" max-width="500">
        <template v-slot:activator="{ props: activatorProps }">
            <CustomButton v-bind="activatorProps">
                Voir les participants
            </CustomButton>
        </template>

        <v-card class="scrollable-card">
            <v-card-title class="text-h5 flex justify-center">Liste des participants</v-card-title>
            <v-card-text class="scrollable-content">
                <div class="flex justify-around">
                    <div v-if="cours.statusCours.id === 1 || cours.statusCours.id === 2" class="susbscribed">
                        <strong>Participants ({{ usersSubscribed.length }})</strong>
                        <CheckboxThree
                            v-for="userCours in usersSubscribed"
                            :key="userCours.id"
                            :user="userCours.user"
                            @checkboxToggle="handleCheckboxToggle"
                        />
                    </div>
                    <div v-else class="subscribed">
                        <strong>Participants ({{ usersSubscribed.length }})</strong>
                        <ul>
                            <li v-for="userCours in usersSubscribed" :key="userCours.id">
                                {{ userCours.user.nom }} {{ userCours.user.prenom }}
                            </li>
                        </ul>
                    </div>
                    <div class="onStandBy">
                        <strong>En attente ({{ usersOnStandby.length }})</strong>
                        <ul>
                            <li v-for="userCours in usersOnStandby" :key="userCours.id">
                                {{ userCours.user.nom }} {{ userCours.user.prenom }}
                            </li>
                        </ul>
                    </div>
                </div>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <CustomButton
                    @click="handleMultiUnSubscription"
                    :disabled="usersChecked.length === 0"
                    :class="usersChecked.length > 0 ? '' : 'opacity-50 cursor-not-allowed'"
                >Désinscrire</CustomButton>
                <CustomButton @click="closeDialog">Fermer</CustomButton>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>


<script setup>
import { ref, watch } from 'vue';
import CustomButton from "../forms/CustomButton.vue";
import CheckboxThree from "../forms/CheckboxThree.vue";
import { apiFetch } from "../../utils/useFetchInterceptor";

const props = defineProps({
    cours: {
        type: Object,
        required: true,
    },
    usersSubscribed: {
        type: Array,
        required: true,
    },
    usersOnStandby: {
        type: Array,
        required: true,
    },
    isModalUnsubscribedUsersOpen: {
        type: Boolean,
        required: true,
    },
});

const emit = defineEmits(['update:isModalUnsubscribedUsersOpen', 'updateUnsubscribeUsersValue', 'unSubscriptionResponse']);
const localIsOpen = ref(props.isModalUnsubscribedUsersOpen);
const usersChecked = ref([]);

watch(() => props.isModalUnsubscribedUsersOpen, (newVal) => {
    localIsOpen.value = newVal;
});

const closeDialog = () => {
    usersChecked.value = [];
    localIsOpen.value = false;
    emit("update:isModalUnsubscribedUsersOpen", false);
};

// Gestion de la sélection des participants à désinscrire
const handleCheckboxToggle = ($event) => {
    if($event.checked) {
        usersChecked.value.push($event.id);
    } else {
        usersChecked.value = usersChecked.value.filter(id => id !== $event.id);
    }
}

const handleMultiUnSubscription = async () => {
    const response = await apiFetch(`/api/removeUsers/${props.cours.id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
        },
        body: JSON.stringify({ usersChecked: usersChecked.value })
    });
    if (response.ok) {
        const result = await response.json();
        emit('updateUnsubscribeUsersValue', {
            // Mettre à jour le statut du cours
            statusCoursValue: JSON.parse(result.statusChange),
            // Mettre à jour la liste des participants
            usersSubscribedValue: props.usersSubscribed.filter(userCours => !usersChecked.value.includes(userCours.user.id)),
        });
        emit('unSubscriptionResponse', {
            type: result.success ? 'success' : 'error',
            message: result.message,
        });
        closeDialog();
    }
};
</script>


<style scoped>
.scrollable-card {
    max-height: 50vh;
    display: flex;
    flex-direction: column;
}

.scrollable-content {
    overflow-y: auto;
    flex-grow: 1;
}
</style>
