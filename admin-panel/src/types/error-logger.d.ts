declare module '@/services/error-logger' {
  export interface ErrorLogger {
    logError: (error: Error, context?: Record<string, unknown>) => void
    logWarning: (message: string, context?: Record<string, unknown>) => void
    logInfo: (message: string, context?: Record<string, unknown>) => void
  }

  const errorLogger: ErrorLogger
  export default errorLogger
} 