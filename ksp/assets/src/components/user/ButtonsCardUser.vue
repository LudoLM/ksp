<script setup>
import {ref, watch} from "vue";
import CustomButton from "../CustomButton.vue";
import { useSubscription, useUnSubscription } from "../../utils/useSubscribing";

const props = defineProps({
    userId: {
        type: Number,
    },
    isSubscribed: {
        type: Boolean,
        required: true,
    },
    isUserAttente: {
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
    [() => props.isSubscribed, () => props.isUserAttente],
    ([newIsSubscribed, newIsUserAttente]) => {
        localData.value.isSubscribed = newIsSubscribed;
        localData.value.isUserAttente = newIsUserAttente;
    }
);

// Déclarer les événements
const emit = defineEmits(["updateCoursStatus", "subscriptionResponse", "unSubscriptionResponse"]);

const localData = ref({
    statusCours: props.statusCours,
    isSubscribed: props.isSubscribed,
    isUserAttente: props.isUserAttente,
});

// Gestion de l'inscription
const handleSubscription = async (isAttente) => {
    try {
        const result = await useSubscription(props.coursId, isAttente);
        if (result.success) {
            localData.value = {
                statusCours: JSON.parse(result.statusChange),
                isUserAttente: !!isAttente,
                isSubscribed: !isAttente
            };

            // Informer le parent
            emit("updateCoursStatus", {
                statusCoursValue: localData.value.statusCours,
                usersCountValue: result.usersCount,
                isSubscribedValue: localData.value.isSubscribed,
                isUserAttenteValue: localData.value.isUserAttente,

            });
            emit("subscriptionResponse", {
                type: "success",
                message: result.message,
            });

        } else {
            emit("subscriptionResponse", {
                type: "error",
                message: result.message,
            });
        }
    } catch (error) {
        emit("subscriptionResponse", {
            type: "error",
            message: "Une erreur inattendue s'est produite.",
        });
        console.log(error);
    }
};

// Gestion de la désinscription
const handleUnsubscription = async (isAttente) => {
    try {
        const result = await useUnSubscription(props.coursId, isAttente);
        if (result.success) {
            localData.value = {
                statusCours: JSON.parse(result.statusChange),
                isUserAttente: !!isAttente,
                isSubscribed: !isAttente
            };
            // Mettre à jour les variables locales en vérifiant si les données sont valides
            isAttente ? localData.value.isUserAttente = false : localData.value.isSubscribed = false;

            // Informer le parent
            emit("updateCoursStatus", {
                statusCoursValue: localData.value.statusCours,
                usersCountValue: result.usersCount,
                isSubscribedValue: localData.value.isSubscribed,
                isUserAttenteValue: localData.value.isUserAttente,
            });
            emit("unSubscriptionResponse", {
                type: "success",
                message: result.message,
            });
        } else {
            emit("unSubscriptionResponse", {
                type: "error",
                message: result.message,
            });
        }
    } catch (error) {
        emit("unSubscriptionResponse", {
            type: "error",
            message: "Une erreur inattendue s'est produite.",
        });
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
    <CustomButton v-if="userId && !localData.isSubscribed && !localData.isUserAttente && localData.statusCours.libelle === 'Complet'" @click="handleSubscription(true)">
        Liste d'attente
    </CustomButton>
    <CustomButton v-if="userId && localData.isUserAttente && localData.statusCours.libelle === 'Complet'" @click="handleUnsubscription(true)">
        Retirer attente
    </CustomButton>
</template>
