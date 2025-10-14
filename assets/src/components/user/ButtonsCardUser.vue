<script setup>
import {ref, watch} from "vue";
import CustomButton from "../forms/CustomButton.vue";
import { useSubscription, useUnSubscription } from "../../utils/useSubscribing";
import {useUserStore} from "../../store/user";
import {alertStore} from "../../store/alert";

const userStore = useUserStore();

const props = defineProps({
    userId: {
        type: Number,
    },
    isSubscribed: {
        type: Boolean,
        required: true,
    },
    isUserOnWaitingList: {
        type: Boolean,
        required: true,
    },
    statusCours: {
        type: Object,
        required: true,
    },
    coursId: {
        type: Number,
        required: true,
    },
});
// Utiliser une seule fonction de surveillance pour plusieurs propriétés
watch(
    [() => props.isSubscribed, () => props.isUserOnWaitingList],
    ([newIsSubscribed, newIsUserOnWaitingList]) => {
        localData.value.isSubscribed = newIsSubscribed;
        localData.value.isUserOnWaitingList = newIsUserOnWaitingList;
    }
);

// Déclarer les événements
const emit = defineEmits(["updateCoursStatus", "subscriptionResponse", "unSubscriptionResponse"]);

const localData = ref({
    statusCours: props.statusCours,
    isSubscribed: props.isSubscribed,
    isUserOnWaitingList: props.isUserOnWaitingList,
});

// Gestion de l'inscription
const handleSubscription = async (isUserOnWaitingList) => {
    try {
        const result = await useSubscription(props.coursId, isUserOnWaitingList);
        if (result.success) {
            localData.value = {
                statusCours: JSON.parse(result.statusChange),
                isUserOnWaitingList: !!isUserOnWaitingList,
                isSubscribed: !isUserOnWaitingList
            };

            // Informer le parent
            emit("updateCoursStatus", {
                statusCoursValue: localData.value.statusCours,
                usersCountValue: result.usersCount,
                isSubscribedValue: localData.value.isSubscribed,
                isUserOnWaitingListValue: localData.value.isUserOnWaitingList,

            });

            userStore.userNombreCours = result.userCoursQuantity;
            alertStore.setAlert(result.message, "success");

        } else {
            alertStore.setAlert(result.message, "error");
        }
    } catch (error) {
        alertStore.setAlert("Une erreur inattendue s'est produite.", "error");
        console.log(error);
    }
};

// Gestion de la désinscription
const handleUnsubscription = async (isUserOnWaitingList) => {
    try {
        const result = await useUnSubscription(props.coursId, isUserOnWaitingList);
        if (result.success) {
            localData.value = {
                statusCours: JSON.parse(result.statusChange),
                isUserOnWaitingList: !!isUserOnWaitingList,
                isSubscribed: !isUserOnWaitingList
            };
            // Mettre à jour les variables locales en vérifiant si les données sont valides
            isUserOnWaitingList ? localData.value.isUserOnWaitingList = false : localData.value.isSubscribed = false;

            // Informer le parent
            emit("updateCoursStatus", {
                statusCoursValue: localData.value.statusCours,
                usersCountValue: result.usersCount,
                isSubscribedValue: localData.value.isSubscribed,
                isUserOnWaitingListValue: localData.value.isUserOnWaitingList,
            });
            userStore.userNombreCours = result.userCoursQuantity;
            alertStore.setAlert(result.message, "success");
        } else {
            alertStore.setAlert(result.message, "error");
        }
    } catch (error) {
        alertStore.setAlert("Une erreur inattendue s'est produite.", "error");
        console.log(error);
    }
};

</script>

<template>
    <CustomButton v-if="userId && !localData.isSubscribed && localData.statusCours.libelle === 'Ouvert'" @click="handleSubscription(false)">
        S'inscrire
    </CustomButton>
    <CustomButton v-if="userId && localData.isSubscribed" @click="handleUnsubscription(false)">
        Se désinscrire
    </CustomButton>
    <CustomButton v-if="userId && !localData.isSubscribed && !localData.isUserOnWaitingList && localData.statusCours.libelle === 'Complet'" @click="handleSubscription(true)">
        Liste d'attente
    </CustomButton>
    <CustomButton v-if="userId && localData.isUserOnWaitingList && localData.statusCours.libelle === 'Complet'" @click="handleUnsubscription(true)">
        Retirer attente
    </CustomButton>
</template>
