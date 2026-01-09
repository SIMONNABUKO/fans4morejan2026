import { ref } from 'vue'

interface ErrorLog {
  id: number
  message: string
  timestamp: Date
  stack?: string
}

// Error logger service for handling application errors and logging
export interface ErrorLogger {
  logError: (error: Error) => void;
  logWarning: (message: string) => void;
  logInfo: (message: string) => void;
}

class ErrorLoggerService implements ErrorLogger {
  private formatError(error: Error): string {
    return `[${new Date().toISOString()}] ${error.name}: ${error.message}\n${error.stack || ''}`
  }

  private formatMessage(level: string, message: string): string {
    return `[${new Date().toISOString()}] ${level}: ${message}`
  }

  logError(error: Error): void {
    console.error(this.formatError(error))
    // Here you could add additional error reporting services
    // like Sentry, LogRocket, etc.
  }

  logWarning(message: string): void {
    console.warn(this.formatMessage('WARNING', message))
  }

  logInfo(message: string): void {
    console.info(this.formatMessage('INFO', message))
  }
}

// Singleton instance
const errorLogger = new ErrorLoggerService()

// Export the singleton instance
export default errorLogger

export const useErrorLogger = () => {
  const errors = ref<ErrorLog[]>([])
  let errorId = 0

  const logError = (error: Error) => {
    const errorLog: ErrorLog = {
      id: ++errorId,
      message: error.message,
      timestamp: new Date(),
      stack: error.stack
    }
    errors.value.push(errorLog)
    console.error('Error logged:', errorLog)
  }

  const clearErrors = () => {
    errors.value = []
  }

  const getErrors = () => {
    return errors.value
  }

  return {
    errors,
    logError,
    clearErrors,
    getErrors
  }
} 