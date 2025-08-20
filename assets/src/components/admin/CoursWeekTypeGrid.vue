"<script setup>
import CoursCardTypeCours from "../CoursCardTypeCours.vue"
import { daysOfWeek} from "../../constants/daysOfWeek";
import {ref} from "vue";

const props = defineProps({
        weekType : {
            type: Object,
        },
    }
);
const daySelected = ref(0);


function selectDay(index) {
    daySelected.value = index;
}

const emit = defineEmits(["deleteCours", "updateCours"])

const handleDeleteCours = (cours) => {
    emit('deleteCours', cours)
}

const openModalForEditCours = (cours) => {
    emit('updateCours', cours)
}

</script>

<template>
    <div class="hidden md:grid md:grid-cols-6 md:gap-4">
        <div
            v-for="(day, index) in daysOfWeek"
            :key="index"
            class="flex flex-col items-center"
        >
            <p class="text-center mb-4">{{ day.name.slice(0, 3) }}</p>
            <CoursCardTypeCours
                v-for="cours in weekType.filter(c => c.daySelected === index)"
                :key="cours.id"
                :cours="cours"
                @deleteCours="handleDeleteCours"
                @updateCours="openModalForEditCours"
            />
        </div>

        <p
            v-if="weekType.length === 0"
            class="col-span-6 flex justify-center items-center mt-6 min-h-[250px]"
        >
            Aucun cours
        </p>
    </div>


    <div class="relative flex flex-col justify-center items-center gap-5 md:hidden">
            <div class="flex justify-between w-full">
                <div
                    v-for="(day, index) in daysOfWeek"
                    :key="day.name"
                    class="text-center flex-1 cursor-pointer mx-2 border-b-2 pb-4"
                    @click="selectDay(index)"
                    :class="{
                      'border-templateSecondColor': day.id === daySelected,
                      'border-transparent opacity-40': day.id !== daySelected,
                      'font-bold text-templateSecondColor': weekType.some(c => c.daySelected === index),
                    }"

                >
                    {{ day.name.slice(0, 3) }}
                </div>
            </div>
        <div class="flex justify-around gap-4 w-full">
            <div
                class="dayBefore"
                :class="daySelected !== 0 ? 'visible': 'invisible'"
                @click="selectDay(daySelected - 1)"
            ></div>

            <div
                v-if="weekType.filter(c => c.daySelected === daySelected).length > 0"
                class="flex flex-col justify-center items-center gap-5 sm:min-h-[250px]"
            >
                <CoursCardTypeCours
                    v-for="cours in weekType.filter(c => c.daySelected === daySelected)"
                    :key="cours.id"
                    :cours="cours"
                    :widthCard="60"
                    @deleteCours="handleDeleteCours"
                    @updateCours="openModalForEditCours"
                />
            </div>

            <div
                v-else
                class="flex justify-center items-center gap-5 min-h-[250px]"
            >
                Aucun cours
            </div>
                <div
                    class='dayAfter'
                    :class="daySelected !== 5 ? 'visible': 'invisible'"
                    @click="selectDay(daySelected + 1)"
                ></div>
            </div>
    </div>
</template>

<style scoped lang="scss">
.dayBefore, .dayAfter {
    position:absolute;
    right: 20px;
    width: clamp(40px, 9vw, 150px);
    height: clamp(40px, 9vw, 150px);
    background: url('../../../../assets/icons/arrow.svg') no-repeat center;
    background-size: 50%;
    margin-top: 100px;
    cursor: pointer;

    &::after{
        position: absolute;
        content: '';
        width: clamp(40px, 9vw, 150px);
        height: clamp(40px, 9vw, 150px);
        background: rgba(0, 0, 0, 0.1);
        border-radius: 50%;
        z-index: 0;
    }
}

.dayBefore {
    transform: rotate(180deg);
    left: 20px;
}

</style>

