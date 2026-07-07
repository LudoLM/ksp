<script setup lang="ts">

import CustomButton from "@/components/forms/CustomButton.vue";
import {ref} from "vue";
import {UsersCoursDTO} from "@/types/coursDetails";
import {apiFetch} from "@/utils/useFetchInterceptor.ts";
import router from "@/router";
import {User} from "@/types";

const props = defineProps({
    coursId: {
        type: Number,
        required: true,
    },
    usersSubscribed: {
        type: Array as () => UsersCoursDTO[],
        required: true,
    },
    usersOnStandby: {
        type: Array as () => UsersCoursDTO[],
        required: true,
    },
});
const emit = defineEmits(['updateUnsubscribeUsersValue', 'unSubscriptionResponse']);
const usersChecked = ref<number[]>([]);

const handleMultiUnSubscription = async () => {
    const response = await apiFetch(`/api/admin/remove-users/${props.coursId}`, {
        method: 'POST',
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
        usersChecked.value = [];
    }
};

</script>

<template>
    <div class="users-section flex flex-col md:flex-row gap-8 md:gap-6">
        <!-- Participants Column -->
        <div class="flex-1 p-6">
            <div class="users-card">
                <h6 class="users-card-title">Participants ({{usersSubscribed.length}})</h6>
                <transition-group
                    v-if="usersSubscribed.length > 0"
                    name="user-row"
                    tag="div"
                    class="users-list mt-4"
                >
                    <div v-for="(user, index) in usersSubscribed" :key="user.user.id" class="user-row-item grid grid-cols-[1fr_5fr_5fr] text-sm">
                        <div>{{ index +1 }}</div>
                        <div
                            class="flex items-center gap-2 cursor-pointer"
                            @click="router.push({ name: 'AdminProfile', params: { id: user.user.id }})"
                        >
                            <div class="w-1/5">{{ user.user.id }}</div>
                            <div><span>{{ user.user.prenom }}</span> <span>{{ user.user.nom }}</span></div>
                        </div>


                        <div class="flex justify-end"><input
                            type="checkbox"
                            :value="user.user.id"
                            v-model="usersChecked"
                        /></div>
                    </div>
                </transition-group>
                <div v-else>
                    <p class="text-sm italic mt-4">Pas de participants.</p>
                </div>
                <div class="unsubscribe-row">
                    <transition name="fade-slide">
                        <div
                            v-show="usersSubscribed.length > 0 && usersChecked.length > 0"
                            class="flex justify-end"
                        >
                            <CustomButton
                                @click="handleMultiUnSubscription"
                            >Désinscrire</CustomButton>
                        </div>
                    </transition>
                </div>

            </div>
        </div>

        <!-- Standby Column -->
        <div class="flex-1 p-6">
            <div class="users-card">
                <h6 class="users-card-title">En attente ({{usersOnStandby.length}})</h6>
                <div v-if="usersOnStandby.length > 0" class="users-list mt-4">
                    <div v-for="(user, index) in usersOnStandby" :key="index">
                        <span>{{ user.user.prenom }} {{ user.user.nom }}</span>
                    </div>
                </div>
                <div v-else>
                    <p class="text-sm italic mt-6">Personne en attente.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss">
.users-list {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
  position: relative;
}

.user-row-item {
  padding: 0.5rem 0;
}

.unsubscribe-row {
  min-height: 42px;
  margin-top: 0.75rem;
}

.user-row-move,
.user-row-enter-active,
.user-row-leave-active {
  transition: transform 0.2s ease, opacity 0.2s ease;
}

.user-row-enter-from,
.user-row-leave-to {
  opacity: 0;
}

.user-row-leave-active {
  position: absolute;
  width: 100%;
  left: 0;
  right: 0;
}

.user-row-enter-from {
  transform: translateY(1px);
}

.user-row-leave-to {
  transform: translateY(-1px);
}

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.1s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(2px);
}
</style>
