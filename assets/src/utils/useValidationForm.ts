import type { Ref } from 'vue'

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
  } else {
    errors.value = {}
    const [key, value] = Object.entries(message['errors'][0])[0]
    errors.value[key] = String(value)
  }
}
