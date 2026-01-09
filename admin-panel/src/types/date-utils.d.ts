declare module '@/utils/date' {
  export function formatDate(date: string | Date, format?: string): string
  export function parseDate(dateString: string): Date
  export function isValidDate(date: any): boolean
  export function getRelativeTime(date: string | Date): string
  export function getDaysDifference(startDate: string | Date, endDate: string | Date): number
} 