<script setup>

import Checked from "../../icons/Checked.vue";
import EditCoursIcon from "../../icons/adminActions/EditCoursIcon.vue";
import DeleteItem from "../../icons/adminActions/DeleteItem.vue";
import {getImageUrl} from "../utils/useAssetHelper.js";

const props = defineProps({
    cours: {
        type: Object
    },
    typeCours: {
        type: Object
    },
    widthCard:{
        type: Number,
        default: 15
    },
});

if (props.cours && props.cours.timeSelected.includes("T")) {
    props.cours.timeSelected = props.cours.timeSelected.split("T")[1].substring(0, 5);
}

const emit = defineEmits([
    "deleteCours",
    "updateCours"
]);

</script>

<template>
    <div
        class="coursCard relative min-w-[100px] border border-gray-300 mb-2.5"
        :style="{ width: !typeCours ? widthCard + 'vw' : '15vw'}"
        :class="typeCours ? 'min-h-[6.25rem]' : 'min-h-[15rem]'"
    >
        <div class="card-image">
            <img :src="getImageUrl(typeCours ? typeCours.thumbnail : cours.typeCours.thumbnail)" alt="">
        </div>
        <div v-if="!cours" class="card-infos ">
            <svg xmlns="http://www.w3.org/2000/svg"
                 fill="none" viewBox="0 0 24 24"
                 stroke-width="1.5"
                 stroke="transparent"
                 class="size-8">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <div class="card-title">
                <h3>{{ typeCours ? typeCours.libelle : cours.typeCours.libelle }}</h3>
            </div>
        </div>
        <div v-if="cours" class="card-infos-details flex justify-center items-center w-full">
            <div class="card-title">
                <h3>{{ cours.typeCours.libelle }}</h3>
            </div>
            <div class="card-details w-full h-full pb-2 flex flex-col justify-between">
                <div class="flex justify-between mb-4">
                    <div class="flex items-center">
                        {{ cours.timeSelected }}
                        <span
                            class="tag"
                            :class="cours.timeSelected <= '12:00' ? 'bg-cyan-400' : 'bg-red-400'"
                        ></span>
                    </div>
                    <p>{{ cours.duree }} mns</p>
                </div>
                <p>{{ cours.nbInscriptionMax }} pers. max</p>
                <div class="mt-4 mr-2 flex justify-between">
                    <div>
                        <p v-if="cours.hasPriority" class="flex">
                            <Checked size="14" class="mr-2"/>Avec priorité
                        </p>
                        <p v-if="cours.hasLimitOfOneCoursPerWeek" class="flex">
                            <Checked size="14" class="mr-2"/>Limité
                        </p>
                    </div>
                </div>
                <div
                    class="flex justify-end w-full mt-2 gap-2"
                >
                    <button
                        @click="emit('updateCours', cours)"
                    >
                        <EditCoursIcon size="16" />
                    </button>
                    <button
                        @click="emit('deleteCours', cours)"
                    >
                        <DeleteItem size="16" />
                    </button>
                </div>


            </div>
        </div>
    </div>
</template>

<style scoped lang="scss">

.coursCard {

    .card-image {
        position: absolute;
        width: 100%;
        height: 100%;
        overflow: hidden;
        z-index: 10;

        img {
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 1s ease-in-out;
        }
    }

    .tag{
        display: inline-block;
        margin-left: 10px;
        width: 15px;
        height: 15px;
        border-radius: 50%;

    }

    .card-title {
        font-weight: 900;
        font-style: italic;
        color: #ffffff;
    }

    h3 {
        color: #ffffff;
        font-size: clamp(.8rem, 1.2vw, 1.2rem);
        margin-bottom: 5px;
        transition: all .5s ease-in-out;
    }

    &:hover .card-infos{
        background-color: rgba(0, 0, 0, 0.7);
        transition: all .5s ease;

        svg{
            stroke: #fff;
        }
    }

    .card-infos{
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 100%;
        padding: 10px;
        background-color: rgba(0, 0, 0, 0.2);
        transition: all .5s ease;
        color: #ffffff;
        z-index: 100;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;

        svg {
            position: absolute;
            top: 30%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: clamp(1.5rem, 3vw, 2rem);
            height: clamp(1.5rem, 3vh, 2rem);
            margin-bottom: 5px;
            fill: transparent;
            transition: fill .5s ease-in-out;
        }

    }

    .card-infos-details {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 100%;
        padding: 10px;
        background-color: rgba(0, 0, 0, 0.8);
        transition: all .5s ease;
        color: #ffffff;
        z-index: 100;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        gap: 10%;
        font-size: clamp(.7rem, .8vw, .8rem);
    }

    button{
        padding: 10px;
        border: 1px solid #E5E7EB;
        border-radius: 50%;
    }
}
</style>
