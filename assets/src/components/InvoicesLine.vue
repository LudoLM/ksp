<script setup>

import DownloadInvoice from "../../icons/userActions/DownloadInvoice.vue";
import {apiFetch} from "../utils/useFetchInterceptor";
import Tooltip from "./Tooltip.vue";
import {useUserStore} from "../store/user";

const props = defineProps(
    {
        paiement: Object
    }
)

const formatDateTime = (date) => {
    return new Date(date).toLocaleString().split(" ");
};


const handleInvoicePDF = async (paiementId) => {
    try {
        const response = await apiFetch("/api/getInvoicePDF", {
            method: "POST",
            body: JSON.stringify({ paiementId: paiementId }),
        });


        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = "facture_KSS.pdf";
        document.body.appendChild(a);
        a.click();
        a.remove();

        // Libérer l'URL après le téléchargement
        window.URL.revokeObjectURL(url);
    } catch (error) {
        alertStore.setAlert(error.message, error.type);
    }
};

</script>

<template>
    <div class="container py-5 px-4">
        <!-- Date d'achat -->
        <div class="date">
            <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ formatDateTime(paiement.date)[0] }}</p>
        </div>
        <!-- Heure d'achat-->
        <div class="hour">
            <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ formatDateTime(paiement.date)[1] }}</p>
        </div>
        <!-- Pack acheté -->
        <div class="pack">
            <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ paiement["pack"].nom.replace("Pack", "") }}</p>
        </div>
        <!-- Prix -->
        <div class="price">
            <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ (paiement["pack"].tarif / 100).toFixed(2,0) }} €</p>
        </div>
        <!-- download facture -->
        <div class="actions">
            <p class="text-sm font-medium text-gray-800 dark:text-white/90 text-center">
                <button
                    class="icons"
                    @click="handleInvoicePDF(paiement.id)"
                >
                    <Tooltip
                        :title="'Télécharger la facture.'"
                        :tooltipPos="'left'"
                    >
                        <DownloadInvoice size="18"/>
                    </Tooltip>
                </button>
            </p>
        </div>
    </div>

</template>

<style scoped lang="scss">
.container {
    display: grid;
    grid-template-columns: 2fr 2fr 2fr 2fr 1fr;
    gap: 1rem;
    width: 100%;
    max-width: 100%;
    align-items: center;
}

button{
    padding: 10px;
    border: 1px solid #E5E7EB;
    border-radius: 50%;
    transition: border 0.3s ease-in-out;
}

@media (max-width: 850px) {
    .container {
        display: grid;
        grid-template-areas:
            "date pack actions"
            "hour price actions";
        gap: 1rem;
        grid-template-columns: 1fr 1fr auto;
    }

    .date {
        grid-area: date;
    }

    .hour {
        grid-area: hour;
    }

    .pack {
        grid-area: pack;
    }

    .price {
        grid-area: price;
    }

    .actions {
        grid-area: actions;
    }
}

</style>
