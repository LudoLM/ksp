<template>
  <div class="nav-icon" ref="navButton">
    <div class="line line1"></div>
    <div class="line line2"></div>
    <div class="line line3"></div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const navButton = ref(null);

onMounted(() => {
  navButton.value.addEventListener("click", () => {
    if (navButton.value.classList.contains("close")) {
      navButton.value.classList.remove("close");
      navButton.value.classList.add("ham");
    } else {
      navButton.value.classList.remove("ham");
      navButton.value.classList.add("close");
    }
  });
});
</script>

<style scoped lang="scss">

.nav-icon {
  position: relative;
  cursor: pointer;
  background-color: transparent;
  width: 40px;
  height: 25px;
}

.nav-icon > .line {
  position: absolute;
  width: 35px;
  height: 4px;
  background-color: #000;
  transform-origin: center center;
  border-radius: 2px;
}

.nav-icon > .line2 {
  top: 10px;
}

.nav-icon > .line3 {
  top: 20px;
}

/* hamburer to Cross animation */

.nav-icon.close > .line1 {
  animation: hamToCloseline1 0.8s 1 ease forwards;
}

.nav-icon.close > .line2 {
  animation: hamToCloseline2 0.8s 1 ease forwards;
}

.nav-icon.close > .line3 {
  animation: hamToCloseline3 0.8s 1 ease forwards;
}

@keyframes hamToCloseline1 {
  50% {
    transform: translateY(10px);
  }
  100% {
    transform: translateY(10px) rotate(45deg);
  }
}

@keyframes hamToCloseline2 {
  100% {
    transform: scale(0);
  }
}

@keyframes hamToCloseline3 {
  50% {
    transform: translateY(-10px);
  }
  100% {
    transform: translateY(-10px) rotate(135deg);
  }
}

/* Close to Hamburger animation */

.nav-icon.ham > .line1 {
  animation: crossToHamline1 0.8s 1 ease forwards;
}

.nav-icon.ham > .line2 {
  animation: crossToHamline2 0.8s 1 ease forwards;
}

.nav-icon.ham > .line3 {
  animation: crossToHamline3 0.8s 1 ease forwards;
}

@keyframes crossToHamline1 {
  0% {
    transform: translateY(10px) rotate(45deg);
  }
  50% {
    transform: translateY(10px);
  }
  100% {
    transform: translateY(0);
  }
}

@keyframes crossToHamline2 {
  0% {
    transform: scale(0);
  }
  100% {
    transform: scale(1);
  }
}

@keyframes crossToHamline3 {
  0% {
    transform: translateY(-10px) rotate(135deg);
  }
  50% {
    transform: translateY(-10px);
  }
  100% {
    transform: translateY(0);
  }
}
</style>
