/**
 * Calendar-specific TypeScript types and interfaces
 */

import { TypeCours, StatusCours, CalendarCours } from './cours'

export interface CalendarNextCoursInfo {
  type?: 'info_next_cours' | 'no_cours' | string
  message?: string
  nextCoursDate?: {
    date: string
  }
  typeCours?: string
  statusCours?: string
  [key: string]: any // Allow additional properties for flexibility with API responses
}

export interface CalendarState {
  date: Date
  daySelected: number
  selectedTypeCours: number
  selectedStatusCours: number
  infos: CalendarNextCoursInfo
  weekInfos: CalendarCours[][]
  firstNextCoursInNextWeeks: CalendarNextCoursInfo | null
  uniqueTypeCoursList: TypeCours[]
  uniqueStatusCoursList: StatusCours[]
  weekString: string
  days: string[]
}

export interface CalendarHeaderProps {
  weekString: string
  uniqueTypeCoursList: TypeCours[]
  uniqueStatusCoursList: StatusCours[]
  selectedTypeCours: number
  selectedStatusCours: number
  shouldPreviousWeekDisabled: boolean
  isAdmin: boolean
  canLaunchWeek: boolean
}

export interface CalendarGridProps {
  days: string[]
  weekInfos: CalendarCours[][]
  daySelected: number
  infos: CalendarNextCoursInfo
  nextDateInNextWeek: string | undefined
}

export interface CalendarMobileProps {
  days: string[]
  daySelected: number
  weekInfos: CalendarCours[][]
  date: Date
  dateToday: Date
  infos: CalendarNextCoursInfo
  nextDateInNextWeek: string | undefined
  nextDateInTheWeek: string | undefined
  nextDateIndex: number | null
  firstNextCoursInNextWeeks: CalendarNextCoursInfo | null
}

export interface CalendarLogicParams {
  calendarStore: any // Pinia store type
  selectedTypeCours: any // Ref or ComputedRef
  selectedStatusCours: any // Ref or ComputedRef
  days: any // ComputedRef
}
