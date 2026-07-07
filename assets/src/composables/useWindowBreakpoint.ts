import { ref, onMounted, onBeforeUnmount } from 'vue'

export const useWindowBreakpoint = (breakpoint: number = 980) => {
  const isMobile =  ref(window.innerWidth < 980)
  let resizeListener: (() => void) | null = null

  onMounted(() => {
    const checkMobile = () => {
      isMobile.value = window.innerWidth < breakpoint
    }

    // Check on mount
    checkMobile()

    // Add resize listener
    resizeListener = checkMobile
    window.addEventListener('resize', resizeListener)
  })

  onBeforeUnmount(() => {
    if (resizeListener) {
      window.removeEventListener('resize', resizeListener)
    }
  })

  return { isMobile }
}
