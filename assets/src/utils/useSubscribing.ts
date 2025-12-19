import { apiFetch } from './useFetchInterceptor'

interface SubscriptionResponse {
  success: boolean
  [key: string]: any
}

export async function useSubscription(
  coursId: number,
  isOnWaitingList: boolean,
  userId: number | null = null
): Promise<SubscriptionResponse> {
  try {
    const response = await apiFetch(`/api/add-user`, {
      method: 'POST',
      body: JSON.stringify({ coursId, isOnWaitingList, userId }),
    })

    const data = await response.json()

    if (data.success) {
      return data
    } else {
      throw data
    }
  } catch (error) {
    return error as SubscriptionResponse
  }
}

export async function useUnSubscription(
  coursId: number,
  isOnWaitingList: boolean
): Promise<SubscriptionResponse> {
  try {
    const response = await apiFetch(`/api/remove-user`, {
      method: 'PUT',
      body: JSON.stringify({ coursId, isOnWaitingList }),
    })

    const data = await response.json()
    if (data.success) {
      return data
    } else {
      throw data
    }
  } catch (error) {
    return error as SubscriptionResponse
  }
}
