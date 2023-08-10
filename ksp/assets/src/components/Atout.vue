<template>
  <div class='assets_item'>
    <div class="puce"><i class="far fa-times-circle"></i></div>
    <slot></slot>
  </div>

</template>

<script>
export default {
  name: 'Myasset',
};
</script>

<style lang="scss" scoped>


    $widthContent : 980px;
    $mainColor: #A289CC;


    .assets_item{
        display: flex;
        align-items: center;


        .puce{
            margin-right: 10px;
        }

        .far{
            font-size: 30px;
            color: $mainColor;
            transition: color .3s ease-in-out;
            transform: rotate(45deg);
        }

        p{
            margin-bottom: 10px;
        }
    }

    .assets_item:hover{
        .far{
            color: #b1ce46;
        }
    }
</style>

