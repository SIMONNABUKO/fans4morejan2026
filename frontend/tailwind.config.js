/** @type {import('tailwindcss').Config} */
export default {
    content: [
      "./index.html",
      "./src/**/*.{vue,js,ts,jsx,tsx}",
    ],
    darkMode: 'class',
    theme: {
      extend: {
        colors: {
          primary: {
            light: '#4169e1',
            dark: '#6495ED',
          },
          background: {
            light: '#ffffff',
            dark: '#111827', // Modern gray-900
          },
          surface: {
            light: '#f8f9fa',
            dark: '#1f2937', // Modern gray-800
          },
          border: {
            light: '#e5e7eb',
            dark: 'rgba(75, 85, 99, 0.5)', // Modern gray-600 with opacity
          },
          text: {
            light: {
              primary: '#1f2937',
              secondary: '#6b7280',
            },
            dark: {
              primary: '#ffffff',
              secondary: '#9ca3af', // Modern gray-400
            },
          },
          // Semantic status colors
          success: {
            light: '#10b981', // emerald-500
            dark: '#059669', // emerald-600
            muted: '#d1fae5', // emerald-100
          },
          warning: {
            light: '#f59e0b', // amber-500
            dark: '#d97706', // amber-600
            muted: '#fef3c7', // amber-100
          },
          error: {
            light: '#ef4444', // red-500
            dark: '#dc2626', // red-600
            muted: '#fee2e2', // red-100
          },
          info: {
            light: '#3b82f6', // blue-500
            dark: '#2563eb', // blue-600
            muted: '#dbeafe', // blue-100
          },
          // Status indicators
          status: {
            online: '#10b981', // emerald-500
            offline: '#6b7280', // gray-500
            busy: '#f59e0b', // amber-500
            away: '#f97316', // orange-500
          },
          // UI element colors
          ui: {
            highlight: '#8b5cf6', // purple-500
            accent: '#06b6d4', // cyan-500
            neutral: '#6b7280', // gray-500
          },
          // Modern glassmorphism colors
          glass: {
            light: 'rgba(255, 255, 255, 0.9)',
            dark: 'rgba(17, 24, 39, 0.9)', // gray-900 with opacity
          },
          glassmorphism: {
            light: {
              primary: 'rgba(255, 255, 255, 0.9)',
              secondary: 'rgba(255, 255, 255, 0.8)',
              tertiary: 'rgba(255, 255, 255, 0.6)',
              border: 'rgba(255, 255, 255, 0.2)',
            },
            dark: {
              primary: 'rgba(17, 24, 39, 0.9)', // gray-900
              secondary: 'rgba(31, 41, 55, 0.8)', // gray-800
              tertiary: 'rgba(55, 65, 81, 0.6)', // gray-700
              border: 'rgba(75, 85, 99, 0.5)', // gray-600
            },
          },
          // Modern gradient colors
          gradient: {
            blue: {
              from: '#3b82f6', // blue-500
              to: '#1d4ed8', // blue-700
            },
            indigo: {
              from: '#6366f1', // indigo-500
              to: '#4338ca', // indigo-700
            },
            purple: {
              from: '#8b5cf6', // purple-500
              to: '#7c3aed', // purple-600
            },
            green: {
              from: '#10b981', // emerald-500
              to: '#059669', // emerald-600
            },
            orange: {
              from: '#f97316', // orange-500
              to: '#ea580c', // orange-600
            },
            red: {
              from: '#ef4444', // red-500
              to: '#dc2626', // red-600
            },
          },
        },
        backdropBlur: {
          xs: '2px',
        },
        animation: {
          'fade-in-up': 'fadeInUp 0.6s ease-out',
          'slide-in-bottom': 'slideInFromBottom 0.5s ease-out',
          'scale-in': 'scaleIn 0.4s ease-out',
          'pulse-slow': 'pulse 2s infinite',
        },
        keyframes: {
          fadeInUp: {
            '0%': {
              opacity: '0',
              transform: 'translateY(20px)',
            },
            '100%': {
              opacity: '1',
              transform: 'translateY(0)',
            },
          },
          slideInFromBottom: {
            '0%': {
              opacity: '0',
              transform: 'translateY(100%)',
            },
            '100%': {
              opacity: '1',
              transform: 'translateY(0)',
            },
          },
          scaleIn: {
            '0%': {
              opacity: '0',
              transform: 'scale(0.9)',
            },
            '100%': {
              opacity: '1',
              transform: 'scale(1)',
            },
          },
        },
      },
    },
    plugins: [],
  }
  
  