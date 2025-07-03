<script setup>
import {computed, ref} from "vue";
import {useUserStore} from "../store/user";
import StatusCoursTag from "./StatusCoursTag.vue";

const props = defineProps({
    info: {
        type: Object,
        required: true,
    },
});

const formattedHour = computed(() => {
    const date = new Date(props.info.dateCours);
    const hours = date.getHours();
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${hours}:${minutes}`;
});
const userStore = useUserStore();
const userId = userStore.userId;
const isUserOnWaitingList = ref(props.info.usersCours.some(userCours => userCours.user.id === userId && userCours.isOnWaitingList === true));

const isSubscribed = computed(() => {
    if (!props.info?.usersCours) return false;
    return props.info.usersCours.some(
        (userCours) => userCours.user.id === userId &&
            userCours.isOnWaitingList === false
    );
});


</script>

<template>
    <router-link
        class="coursCardCalendar"
        :to="{ name: 'CoursDetails', params: { id: info.id }}"
    >
        <StatusCoursTag
            class="statusCoursTag"
            :statusCours="info.statusCours"
        />
        <div class="card-image">
            <img :src="require(`../../images/uploads/${info.typeCours.thumbnail}`)" alt="">
        </div>
        <div class="card-infos ">
            <svg
                class="fill-body dark:hover:fill-primary"
                viewBox="0 0 20 20"
                fill="#000000"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    fill-rule="evenodd"
                    clip-rule="evenodd"
                    d="M9.16666 3.33332C5.945 3.33332 3.33332 5.945 3.33332 9.16666C3.33332 12.3883 5.945 15 9.16666 15C12.3883 15 15 12.3883 15 9.16666C15 5.945 12.3883 3.33332 9.16666 3.33332ZM1.66666 9.16666C1.66666 5.02452 5.02452 1.66666 9.16666 1.66666C13.3088 1.66666 16.6667 5.02452 16.6667 9.16666C16.6667 13.3088 13.3088 16.6667 9.16666 16.6667C5.02452 16.6667 1.66666 13.3088 1.66666 9.16666Z"
                />
                <path
                    fill-rule="evenodd"
                    clip-rule="evenodd"
                    d="M13.2857 13.2857C13.6112 12.9603 14.1388 12.9603 14.4642 13.2857L18.0892 16.9107C18.4147 17.2362 18.4147 17.7638 18.0892 18.0892C17.7638 18.4147 17.2362 18.4147 16.9107 18.0892L13.2857 14.4642C12.9603 14.1388 12.9603 13.6112 13.2857 13.2857Z"
                />
            </svg>
            <div :class="isSubscribed ? 'isSubscribed' : 'invisible'">Je participe</div>
            <div :class="isUserOnWaitingList ? 'isUserOnWaitingList' : 'invisible'">
                En attente
            </div>
            <div class="card_title">
                <h3>{{ info.typeCours.libelle }}</h3>
            </div>
            <div class="card_times">
                {{ formattedHour }} - {{ info.duree }} mns
            </div>
        </div>
    </router-link>


</template>

<style scoped lang="scss">

.coursCardCalendar {
    position: relative;
    width: 100%;
    height: 250px;
    border: 1px solid #ccc;
    margin-bottom: 10px;

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

    &:hover img{
        -ms-transform: scale(1.5); /* IE 9 */
        -webkit-transform: scale(1.5); /* Safari 3-8 */
        transform: scale(1.5);
        transition: transform 5s ease-in-out;

    }

    &:hover .card-infos{
        background-color: rgba(0, 0, 0, 0.7);
        transition: all .5s ease;

        svg{
            fill: #fff;
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

        h3 {
            color: #ffffff;
            font-size: clamp(1rem, 1.8vw, 1.5rem);
            margin-bottom: 5px;
            transition: all .5s ease-in-out;
        }

        svg {
            position: absolute;
            top: 30%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: clamp(1.5rem, 2vw, 2rem);
            height: clamp(1.5rem, 2vh, 2rem);
            margin-bottom: 5px;
            fill: transparent;
            transition: fill .5s ease-in-out;
        }

        .isSubscribed, .isUserOnWaitingList {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: clamp(0.7rem, 1.2vw, 1rem);
        }


        &:has(.isSubscribed, .isUserOnWaitingList) {
            background-color: rgba(0, 0, 0, 0.8);
        }
    }

    .card_title {
        font-size: 1.5rem;
        font-weight: 900;
        font-style: italic;
        color: #ffffff;
    }

    .statusCoursTag {
      position: absolute;
      top: -10px;
      right: 10px;
      z-index: 101;
    }

    .card_times {
        font-size: clamp(0.8rem, 1.2vw, 1rem);
    }
}

</style>
