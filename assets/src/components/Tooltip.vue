<script setup>

defineProps({
    title: {
        type: String,
        required: true
    },
    tooltipPos: {
        type: String,
        default: "up"
    },
    tooltipLength: {
        type: String,
        default: "medium"
    }
})


</script>

<template>
    <span class="tooltip" :data-tooltip="title" :data-tooltip-pos="tooltipPos" :data-tooltip-length="tooltipLength">
        <slot></slot>
    </span>
</template>

<style scoped lang="scss">
    // Tooltip

    .tooltip {
        position: relative;
        font-size: clamp(0.8rem, .8vw, 1rem);
        text-align: center;
    }
    .tooltip:after, .tooltip:before {
        opacity: 0;
        pointer-events: none;
        bottom: 100%;
        left: 50%;
        position: absolute;
        z-index: 10;
        transform: translate(-50%, 10px);
        transform-origin: top;
        transition: all 0.18s ease-out 0.18s; }

    .tooltip:after {
        background-color: #f5f5f5;
        box-shadow: 0 0 3px #ddd;
        content: attr(data-tooltip);
        padding: 10px;
        white-space: nowrap;
        margin-bottom: 11px; }

    .tooltip:before {
        width: 0;
        height: 0;
        content: "";
        margin-bottom: 6px; }

    .tooltip:hover:before,
    .tooltip:hover:after {
        opacity: 1;
        pointer-events: auto;
        transform: translate(-50%, 0); }

    [data-tooltip-pos="up"]:before {
        border-left: 6px solid transparent;
        border-right: 6px solid transparent;
        border-top: 6px solid #f5f5f5;
        margin-bottom: 6px; }

    [data-tooltip-pos="left"]:before,
    [data-tooltip-pos="left"]:after {
        bottom: auto;
        left: auto;
        right: 100%;
        top: 50%;
        transform: translate(10px, -50%); }

    [data-tooltip-pos="left"]:after {
        margin-right: 11px; }

    [data-tooltip-pos="left"]:hover:before,
    [data-tooltip-pos="left"]:hover:after {
        transform: translate(0, -50%); }


    [data-tooltip-length]:after {
        white-space: normal; }

    [data-tooltip-length="small"]:after {
        width: 80px; }

    [data-tooltip-length="medium"]:after {
        width: 150px; }

    [data-tooltip-length="large"]:after {
        width: 260px; }

    [data-tooltip-length="fit"]:after {
        width: 100%; }
</style>
