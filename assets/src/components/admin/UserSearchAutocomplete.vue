<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { apiFetch } from '@/utils/useFetchInterceptor';
import type { User } from '@/types/user';
import CustomButton from '@/components/forms/CustomButton.vue';

interface Props {
  coursId: number;
  label?: string;
  placeholder?: string;
}

interface Emits {
  (e: 'user-selected', user: User): void;
}

const props = withDefaults(defineProps<Props>(), {
  label: 'Rechercher un utilisateur',
  placeholder: 'Nom ou email...',
});

const emit = defineEmits<Emits>();

const searchQuery = ref('');
const allUsers = ref<User[]>([]);
const selectedUser = ref<User | null>(null);
const isLoading = ref(false);
const showDropdown = ref(false);


const displayName = computed(() => {
  if (!selectedUser.value) return '';
  return `${selectedUser.value.prenom ?? ''} ${selectedUser.value.nom ?? ''}`;
});

const loadUsers = async () => {
  // Éviter les appels concurrents
  if (isLoading.value) return;

  try {
    isLoading.value = true;
      const params = new URLSearchParams({ search: searchQuery.value });
      const response = await apiFetch(`/api/admin/users-not-in-cours/${props.coursId}?${params}`, {
        method: 'GET',
    });
    if (response.ok) {
      allUsers.value = await response.json();
    }
  } catch (error) {
    console.error('Erreur lors de la récupération des utilisateurs:', error);
  } finally {
    isLoading.value = false;
  }
};

const onSearchInput = () => {
  // Afficher la dropdown immédiatement si au moins 3 caractères sont tapés
  if (searchQuery.value.length >= 3) {
    loadUsers();
    showDropdown.value = true;
  } else {
    showDropdown.value = false;
  }
};

const selectUser = (user: User) => {
  selectedUser.value = user;
  searchQuery.value = '';
  showDropdown.value = false;
};

const clearSelection = () => {
  selectedUser.value = null;
  searchQuery.value = '';
  showDropdown.value = false;
};

const handleAddUser = () => {
  if (selectedUser.value) {
    emit('user-selected', selectedUser.value);
    clearSelection();
    loadUsers();
  }
};

// Lifecycle
watch(
  () => props.coursId,
  () => {
    loadUsers();
  },
  { immediate: true }
);

// Focus handling
const onFocus = () => {
  if (searchQuery.value.length >= 3) {
    loadUsers();
    showDropdown.value = true;
  }
};

const onBlur = () => {
  // Délai pour permettre les clics sur la dropdown avant de la fermer
  setTimeout(() => {
    showDropdown.value = false;
  }, 150);
};
</script>

<template>
  <div class="px-6">
    <div class="users-card">
      <h6 class="users-card-title">Ajouter un participant</h6>

      <div class="add-participant-content mt-4">
        <!-- Formulaire de recherche -->
        <div v-if="!selectedUser" class="search-wrapper">
          <label :for="'user-search'" class="search-label">
            {{ label }}
          </label>
          <div class="relative">
            <input
              id="user-search"
              v-model="searchQuery"
              type="text"
              :placeholder="placeholder"
              @input="onSearchInput"
              @focus="onFocus"
              @blur="onBlur"
              class="search-input"
            />

            <!-- Dropdown des suggestions -->
            <div v-if="showDropdown && searchQuery.length >= 3" class="dropdown-menu">
              <div v-if="isLoading" class="dropdown-item loading">
                Chargement...
              </div>
              <div
                v-else-if="allUsers.length > 0"
                class="users-dropdown-list"
              >
                <div
                  v-for="user in allUsers"
                  :key="user.id"
                  class="dropdown-item"
                  @click="selectUser(user)"
                  @mousedown.prevent
                >
                  <div class="user-info">
                    <div class="user-name">{{ user.id }} - {{ user.prenom }} {{ user.nom }}</div>
                  </div>
                </div>
              </div>
              <div v-else class="dropdown-item empty-state">
                Aucun utilisateur trouvé
              </div>
            </div>
          </div>
        </div>

        <!-- Utilisateur sélectionné -->
        <div v-else class="selected-user-section">
          <div class="selected-user-display">
            <div class="selected-user-info">
              <span class="font-semibold">{{ displayName }}</span>
            </div>
            <div class="action-buttons flex gap-2">
              <CustomButton @click="handleAddUser">Ajouter</CustomButton>
              <CustomButton @click="clearSelection" class="secondary-btn">
                Annuler
              </CustomButton>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">

.add-participant-content {
  .search-wrapper {
    position: relative;

    .search-label {
      display: block;
      margin-bottom: 0.5rem;
      font-size: 0.875rem;
      font-weight: 500;
      color: #374151;
    }

    .search-input {
      width: 100%;
      padding: 0.625rem 1rem;
      border: 1px solid #d1d5db;
      border-radius: 0.5rem;
      font-size: 0.875rem;
      background-color: white;
      color: #1f2937;

      &::placeholder {
        color: #9ca3af;
      }

      &:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
      }
    }
  }
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.375rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  z-index: 50;
  margin-top: 0.25rem;
  max-height: 300px;
  overflow-y: auto;
}

.dropdown-item {
  padding: 0.75rem 1rem;
  cursor: pointer;
  transition: background-color 0.15s ease;
  font-size: 0.875rem;

  &:hover:not(.empty-state):not(.loading) {
    background-color: #f3f4f6;
  }

  &.empty-state,
  &.loading {
    cursor: default;
    color: #6b7280;
    text-align: center;
  }
}

.users-dropdown-list {
  .user-info {
    .user-name {
      font-weight: 500;
      color: #1f2937;
    }
  }
}

.selected-user-section {
  .selected-user-display {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 0.375rem;
    border: 1px solid #e5e7eb;

    .selected-user-info {
      color: #1f2937;
      font-size: 0.9375rem;
    }

    .action-buttons {
      display: flex;
      gap: 0.5rem;

      :deep(.secondary-btn) {
        background-color: #e5e7eb;
        color: #374151;

        &:hover {
          background-color: #d1d5db;
        }
      }
    }
  }
}
</style>
