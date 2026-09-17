import type { Ref } from 'vue'
import { alertStore } from '../store/alert'

export async function useValidationForm(
  message: Record<string, any>,
  errors: Ref<Record<string, string>>
): Promise<void> {
  if (message.detail) {
    errors.value = message.detail.split('\n').reduce((acc: Record<string, string>, error: string) => {
      const [key, msg] = error.split(': ')
      acc[key] = msg
      return acc
    }, {})
  } else if (message.errors) {
    errors.value = {}
    const [key, value] = Object.entries(message['errors'][0])[0]
    errors.value[key] = String(value)
  } else {
    alertStore.error(message.message ?? message.error ?? 'Une erreur est survenue.')
  }
}
