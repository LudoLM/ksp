<script setup>
import { computed } from 'vue';

// Définition des props (vous aurez besoin de `totalPages` et `itemsPerPage` ou de calculer totalPages à l'extérieur)
const props = defineProps({
    // Ces props DOIVENT être fournies par le composant parent
    totalPages: {
        type: Number,
        required: true,
    },
    currentPage: {
        type: Number,
        required: true,
    },
    // Optionnel, pour définir la taille de la fenêtre visible
    maxPagesToShow: {
        type: Number,
        default: 5,
    },
});

const emit = defineEmits(['page-changed']);

function generateSmartPagination(totalPages, currentPage, maxPagesToShow) {
    const pages = [];

    if (totalPages <= maxPagesToShow + 2) {
        for (let i = 1; i <= totalPages; i++) {
            pages.push(i);
        }
        return pages;
    }

    let startPage = Math.max(2, currentPage - Math.floor(maxPagesToShow / 2));
    let endPage = Math.min(totalPages - 1, startPage + maxPagesToShow - 1);

    if (endPage === totalPages - 1) {
        startPage = Math.max(2, endPage - maxPagesToShow + 1);
    }

    pages.push(1);
    if (startPage > 2) {
        pages.push('...');
    }

    for (let i = startPage; i <= endPage; i++) {
        pages.push(i);
    }

    if (endPage < totalPages - 1) {
        pages.push('...');
    }

    if (totalPages > 1) {
        pages.push(totalPages);
    }

    return pages;
}


const pagesToDisplay = computed(() => {
    return generateSmartPagination(
        props.totalPages,
        props.currentPage,
        props.maxPagesToShow
    );
});

function changePage(pageNumber) {
    if (pageNumber > 0 && pageNumber <= props.totalPages) {
        // Envoie l'événement au composant parent (qui gérera l'appel API)
        emit('page-changed', pageNumber);
    }
}

</script>

<template>
    <div class="pagination-container">
        <div id="pagination-controls">
            <button
                :disabled="props.currentPage === 1"
                @click="changePage(props.currentPage - 1)">
                «
            </button>

            <template v-for="(page, index) in pagesToDisplay" :key="index">

                <span v-if="page === '...'" class="ellipsis">...</span>

                <button
                    v-else
                    :class="{ active: page === props.currentPage }"
                    :disabled="page === props.currentPage"
                    @click="changePage(page)">
                    {{ page }}
                </button>
            </template>

            <button
                :disabled="props.currentPage === props.totalPages"
                @click="changePage(props.currentPage + 1)">
                »
            </button>
        </div>
    </div>
</template>

<style scoped lang="scss">
.pagination-container {
    display: flex;
    justify-content: center;
    margin: 20px 0;
    font-family: Arial, sans-serif;
}

#pagination-controls{
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    justify-content: center;
}

#pagination-controls button {
    background-color: #f4f4f4;
    border: 1px solid #ddd;
    padding: 8px 15px;
    margin: 0 4px;
    cursor: pointer;
    border-radius: 50%;
    width: clamp(30px, 4vw, 40px);
    height: clamp(30px, 4vw, 40px);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: clamp(0.8rem, 1.2vw, .8rem);
    transition: background-color 0.2s;
}
#pagination-controls button:not(:disabled):hover {
    background-color: #e0e0e0;
}
#pagination-controls button.active {
    background-color: rgb(30, 27, 75);
    color: white;
    border-color: rgb(30, 27, 75);
    cursor: default;
}
#pagination-controls button:disabled {
    cursor: not-allowed;
    opacity: 0.9;
}
#pagination-controls .ellipsis {
    padding: 8px 4px;
    margin: 0 4px;
    color: #6c757d;
    user-select: none;
}
</style>
