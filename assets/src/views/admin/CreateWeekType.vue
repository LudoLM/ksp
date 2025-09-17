<script setup>
import Banner from "../../components/Banner.vue";
import bannerImage from "../../../images/banners/imageBanner5.jpg";
import {useGetTypesCours} from "../../utils/useActionCours";
import {onMounted, ref, inject, watch, computed} from 'vue';
import CoursCardTypeCours from "../../components/CoursCardTypeCours.vue";
import ModalAddCoursWeekType from "../../components/modals/ModalAddCoursWeekType.vue";
import CustomButton from "../../components/forms/CustomButton.vue";
import {apiFetch} from "../../utils/useFetchInterceptor";
import CustomInput from "../../components/forms/CustomInput.vue";
import Tabs from "../../components/tabs/Tabs.vue";
import Tab from "../../components/tabs/Tab.vue";
import CoursWeekTypeGrid from "../../components/admin/CoursWeekTypeGrid.vue";
import VueDatePicker from "@vuepic/vue-datepicker";
import CustomSelect from "../../components/forms/CustomSelect.vue";
import {useConvertFormatDate} from "../../utils/useConvertFormatDate";
import ModalDuplicateWeekType from "../../components/modals/ModalDuplicateWeekType.vue";
import {useValidationForm} from "../../utils/useValidationForm";

const title = 'Gestion de semaine type';
const typeCoursList = ref([]);

// État des modals
const isAddCoursWeekTypeModalOpen = ref(false);
const isDuplicateCoursWeekTypeModalOpen = ref(false);
const coursOrTypeCours = ref(null);
const IsEditCours = ref(false);
const weekCoursAssigned = ref([]);
const errors = ref(
    {
        name: null,
        weekTypeArray: null
    }
);

const isCreateMode = ref(true)

const weekType = ref([]);
const weekTypeName = ref();

const alertStore = inject('alertStore');

const openModalForTypeCours = (typeCours) => {
    coursOrTypeCours.value = {
        typeCours: typeCours,
        duree:  60,
        nbInscriptionMax: 12,
        specialNote: "Cours sympathique",
        hasPriority: true,
        hasLimitOfOneCoursPerWeek: true,
        daySelected: 0,
        timeSelected: "18:00",
        uid: Date.now(),
    };
    IsEditCours.value = false;
    isAddCoursWeekTypeModalOpen.value = true;
};

const openModalForEditCours = (cours) => {
    cours.uid = cours.id ? cours.id : Date.now() ;
    coursOrTypeCours.value = { ...cours };
    IsEditCours.value = true;
    isAddCoursWeekTypeModalOpen.value = true;
};


const fetchTypeCours = async () => {
    try {
        typeCoursList.value = await useGetTypesCours();
    } catch (error) {
        console.error("Erreur lors de la récupération des cours:", error);
    }
};

const saveCoursWeekType = (data) => {
    const target = isCreateMode.value ? weekType : weekCoursAssigned;

    if (IsEditCours.value) {
        const index = target.value.findIndex(c => c.uid === data.uid);
        if (index !== -1) {
            target.value[index] = { ...data };
        }
    } else {
        target.value = [...target.value, data];
    }
    target.value.sort((a, b) => a.timeSelected.localeCompare(b.timeSelected));
};


const handleDeleteCours = (data) => {
    if(isCreateMode.value) {
        // On est dans le cas d'une création de weekType
        if(data.uid) {
            weekType.value = weekType.value.filter(c => c.uid !== data.uid);
        }
        // On est dans le cas d'une duplication
        else{
            weekType.value = weekType.value.filter(c => c.id !== data.id);
        }
    }
    else{
        // Dans le cas d'une modification d'une weekType existante, je modifie la copie
        if(data.uid) {
            weekCoursAssigned.value = weekCoursAssigned.value.filter(c => c.uid !== data.uid);
        }
        else {
            weekCoursAssigned.value = weekCoursAssigned.value.filter(c => c.id !== data.id);

        }
    }
}

const handleCreateWeekType = async () => {
    try {
        const response = await apiFetch('/api/weekType/create', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                name: weekTypeName.value,
                weekTypeArray: weekType.value,
            }),
        });

        if (response.ok) {
            const data = await response.json();
            alertStore.setAlert(data.message, "success");
            await fetchSuperLightWeekType();
            errors.value = [];
        } else {
            const result = await response.json();
            await useValidationForm(result, errors);
        }
    } catch (error) {
        console.error("Erreur:", error);
    }
};

const handleDeleteWeekType = async () => {
    weekType.value = [];
}

const handleDuplicateCours = (weekTypeId) => {
    fetchtWeekTypeDetails("duplicate", weekTypeId);
    isDuplicateCoursWeekTypeModalOpen.value = false;
};

onMounted(async () => {
    await fetchTypeCours();
    await fetchSuperLightWeekType();
});


//AddWeekPart
const weekTypeOptions = ref([]);
const weekTypeSelectedId = ref(0);
const weekTypeSelectedDetails = ref([]);
const date = ref(null);
const showDateError = ref(false)
const fetchtWeekTypeDetails = async (mode, weekTypeId) => {
    try {
        const response = await apiFetch(`/api/getWeekTypeById/${weekTypeId}`, {
            method: "GET",
        });
        const result = await response.json()
        if(mode ==="assign"){
            weekTypeSelectedDetails.value = result;

            //Je crée un copie qui servira de snapShot
            weekCoursAssigned.value = JSON.parse(JSON.stringify(result.coursWeekTypes || []));
        }
        else{
            weekType.value = [...result.coursWeekTypes];
        }
    } catch (error) {
        console.error("Erreur lors de la récupération des semaines:", error);
    }
};

watch(weekTypeSelectedId, () => {
    fetchtWeekTypeDetails("assign", weekTypeSelectedId.value);
}, { immediate: true });

watch(date, (newDate) => {
    if (newDate && (Array.isArray(newDate) ? newDate[0] : true)) {
        showDateError.value = false;
    }
});


const fetchSuperLightWeekType = async () => {
    try {
        const response = await apiFetch("/api/getSuperLightAllWeekType", {
            method: "GET",
        });
        weekTypeOptions.value = await response.json();
        weekTypeSelectedId.value = weekTypeOptions.value[0].id;
    } catch (error) {
        console.error("Erreur lors de la récupération des semaines:", error);
    }
};

const handleAddWeek = async () => {
    try {
        if(date.value === null) {
            showDateError.value = true;
        }
        const response = await apiFetch("/api/week/assign", {
            method: "POST",
            body: JSON.stringify({
                weekTypeId: Number(weekTypeSelectedId.value),
                dateMonday: useConvertFormatDate(date.value[0]),
                coursList: weekCoursAssigned.value,

            }),
        });
        const result = await response.json();
        if (response.ok) {
            alertStore.setAlert(result.message, "success");
            date.value = null;
        } else {
            alertStore.setAlert("Erreur lors de l'ajout de la semaine : " + result.message)
        }
    } catch (error) {
        console.error("Erreur lors de l'ajout de la semaine:", error);
    }
};
</script>

<template>
    <Banner
        :title="title"
        :hasButton=false
        :backgroundColor="'rgba(30, 27, 75, .9)'"
        :image="bannerImage"
    />
    <div>
        <Tabs
            @changeTab="isCreateMode = $event === 0"
        >
            <Tab
                title="Création"
            >
                <div class="flex flex-col items-center md:flex-row md:items-baseline justify-center my-10 gap-2">
                    <div class="flex flex-col gap-2">
                        <CustomInput
                            v-model="weekTypeName"
                            id="weekTypeName"
                            placeholder="Nom de la semaine type"
                            :error="errors.name"
                        />
                        <div
                            class="text-red-500 text-xs">
                            {{ errors.weekTypeArray}}
                        </div>

                    </div>
                    <div class="flex justify-center items-baseline gap-2">
                        <CustomButton
                            @click="handleCreateWeekType"
                        >Enregistrer
                        </CustomButton>
                        <CustomButton
                            @click="handleDeleteWeekType"
                        >Effacer
                        </CustomButton>
                        <CustomButton
                            @click="isDuplicateCoursWeekTypeModalOpen = true"
                        >Dupliquer
                        </CustomButton>
                    </div>

                </div>
                <div

                </div>
            </Tab>
            <Tab
                title="Assignation"
            >
                <div class="flex flex-col items-center md:flex-row md:items-baseline justify-center my-10 gap-2">
                    <CustomSelect
                        v-model="weekTypeSelectedId"
                        :label="'Semaine'"
                        :options="weekTypeOptions"
                        class="w-1/2 md:w-1/5"
                    />
                    <div class="flex flex-col w-1/2 md:w-1/5 mb-4">
                        <VueDatePicker
                            v-model="date"
                            placeholder="Semaine"
                            :week-numbers="{ type: 'iso' }"
                            week-picker
                            :teleport="true"
                        />
                        <p
                            class="text-red text-xs"
                            v-if="showDateError"
                        >La semaine ne peut pas être vide
                        </p>
                    </div>

                    <CustomButton
                        @click="handleAddWeek()"
                    >
                        Assigner
                    </CustomButton>
                </div>
            </Tab>
        </Tabs>

        <ModalDuplicateWeekType
            v-if="isDuplicateCoursWeekTypeModalOpen"
            :isOpen="isDuplicateCoursWeekTypeModalOpen"
            :weekTypeOptions="weekTypeOptions"
            @select:cours="handleDuplicateCours"
            @update:isOpen="isDuplicateCoursWeekTypeModalOpen = $event"
        />
    </div>


    <div>
        <ul class="flex flex-nowrap md:flex-wrap justify-start gap-2 m-4 md:m-10 overflow-x-auto">
            <li
                v-for="typeCours in typeCoursList" :key="typeCours.id">
                <CoursCardTypeCours
                    :typeCours="typeCours"
                    class="cursor-pointer"
                    @click="openModalForTypeCours(typeCours)"
                />
            </li>
        </ul>
        <ModalAddCoursWeekType
            v-if="isAddCoursWeekTypeModalOpen"
            :isOpen="isAddCoursWeekTypeModalOpen"
            :info="coursOrTypeCours"
            :isEditMode="IsEditCours"
            @save:cours="saveCoursWeekType"
            @update:isOpen="isAddCoursWeekTypeModalOpen = $event"
        >
        </ModalAddCoursWeekType>
    </div>

    <div class="bg-white py-10">
        <div
            v-if="isCreateMode"
        >
            <CoursWeekTypeGrid
                v-if=" weekType !== undefined"
                :weekType = "weekType"
                @deleteCours="handleDeleteCours"
                @updateCours="openModalForEditCours"
            />
        </div>
        <div
            v-else
        >
            <CoursWeekTypeGrid
                v-if="weekCoursAssigned !== undefined"
                :weekType = "weekCoursAssigned"
                @deleteCours="handleDeleteCours"
                @updateCours="openModalForEditCours"
            />
        </div>
    </div>

</template>


<style scoped lang="scss">



</style>

