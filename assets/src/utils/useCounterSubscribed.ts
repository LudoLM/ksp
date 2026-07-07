import {UsersCours} from "@/types";


const isActiveSubscription = (usersCours: UsersCours) => {
  if (usersCours.isOnWaitingList) {
    return false;
  }
  if (usersCours.unsubscribedAt === null || usersCours.unsubscribedAt === undefined) {
    return true;
  }
  const unsubscribedAt = new Date(usersCours.unsubscribedAt);
  const createdAt = new Date(usersCours.createdAt);
  return unsubscribedAt <= createdAt;
};

export const useCounterSubscribed = (usersCours: UsersCours[]) : number => {
  return usersCours.filter(isActiveSubscription).length;
};
