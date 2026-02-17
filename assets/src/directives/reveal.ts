import type { Directive, DirectiveBinding } from 'vue'
import { useIntersectionObserver } from '@vueuse/core'

interface RevealOptions {
    delay?: number
}

export const vReveal: Directive<HTMLElement, RevealOptions> = {
    mounted(el: HTMLElement, binding: DirectiveBinding<RevealOptions>) {
        const delay = binding.value?.delay ?? 0

        el.style.opacity = '0'
        el.style.transform = 'translateY(40px)'
        el.style.transition = `all 0.9s ease ${delay}s`

        const { stop } = useIntersectionObserver(
            el,
            ([entry]) => {
                if (entry?.isIntersecting) {
                    el.style.opacity = '1'
                    el.style.transform = 'translateY(0)'
                    stop()
                }
            },
            { threshold: 0.15, rootMargin: '0px 0px -50px 0px' }
        )
    }
}
