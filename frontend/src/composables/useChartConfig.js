import { computed } from 'vue'

// Industry-standard color palette for charts
const CHART_COLORS = {
  primary: {
    main: '#667eea',
    gradient: ['#667eea', '#764ba2'],
    light: '#a18cd1',
    dark: '#4c63d2'
  },
  secondary: {
    main: '#f093fb',
    gradient: ['#f093fb', '#f5576c'],
    light: '#f8a5c2',
    dark: '#ec407a'
  },
  success: {
    main: '#4ade80',
    gradient: ['#4ade80', '#22c55e'],
    light: '#86efac',
    dark: '#16a34a'
  },
  warning: {
    main: '#fbbf24',
    gradient: ['#fbbf24', '#f59e0b'],
    light: '#fcd34d',
    dark: '#d97706'
  },
  danger: {
    main: '#f87171',
    gradient: ['#f87171', '#ef4444'],
    light: '#fca5a5',
    dark: '#dc2626'
  },
  info: {
    main: '#60a5fa',
    gradient: ['#60a5fa', '#3b82f6'],
    light: '#93c5fd',
    dark: '#2563eb'
  }
}

// Chart series color schemes
const COLOR_SCHEMES = {
  revenue: ['#667eea', '#f093fb', '#4ade80', '#fbbf24'],
  engagement: ['#60a5fa', '#f87171', '#fbbf24', '#4ade80'],
  gradient: ['#667eea', '#764ba2', '#f093fb', '#f5576c'],
  professional: ['#1f2937', '#374151', '#6b7280', '#9ca3af'],
  vibrant: ['#ec4899', '#8b5cf6', '#06b6d4', '#10b981']
}

export function useChartConfig() {
  
  // Base configuration for all charts
  const getBaseConfig = () => ({
    chart: {
      fontFamily: 'Inter, system-ui, sans-serif',
      toolbar: {
        show: false
      },
      zoom: {
        enabled: true,
        type: 'x',
        autoScaleYaxis: true
      },
      animations: {
        enabled: true,
        easing: 'easeinout',
        speed: 800,
        animateGradually: {
          enabled: true,
          delay: 150
        },
        dynamicAnimation: {
          enabled: true,
          speed: 350
        }
      },
      dropShadow: {
        enabled: true,
        top: 2,
        left: 2,
        blur: 4,
        opacity: 0.1
      }
    },
    grid: {
      borderColor: '#f1f5f9',
      strokeDashArray: 4,
      xaxis: {
        lines: {
          show: false
        }
      },
      yaxis: {
        lines: {
          show: true
        }
      },
      padding: {
        top: 0,
        right: 0,
        bottom: 0,
        left: 0
      }
    },
    tooltip: {
      enabled: true,
      shared: true,
      intersect: false,
      style: {
        fontSize: '12px',
        fontFamily: 'Inter, system-ui, sans-serif'
      },
      x: {
        show: true,
        format: 'MMM dd, yyyy'
      },
      marker: {
        show: true
      },
      theme: 'light'
    },
    legend: {
      show: true,
      position: 'bottom',
      horizontalAlign: 'center',
      fontSize: '12px',
      fontFamily: 'Inter, system-ui, sans-serif',
      markers: {
        width: 8,
        height: 8,
        radius: 4
      },
      itemMargin: {
        horizontal: 12,
        vertical: 8
      }
    }
  })

  // Line chart configuration
  const getLineChartConfig = (options = {}) => {
    const base = getBaseConfig()
    return {
      ...base,
      chart: {
        ...base.chart,
        type: 'line',
        height: options.height || 350,
        background: 'transparent'
      },
      stroke: {
        curve: options.curve || 'smooth',
        width: options.strokeWidth || 3,
        lineCap: 'round'
      },
      markers: {
        size: options.markerSize || 0,
        strokeWidth: 2,
        strokeColors: '#ffffff',
        hover: {
          size: 6,
          sizeOffset: 3
        }
      },
      xaxis: {
        type: 'datetime',
        labels: {
          style: {
            colors: '#64748b',
            fontSize: '11px',
            fontWeight: 500
          },
          datetimeFormatter: {
            year: 'yyyy',
            month: 'MMM',
            day: 'dd MMM',
            hour: 'HH:mm'
          }
        },
        axisBorder: {
          show: false
        },
        axisTicks: {
          show: false
        }
      },
      yaxis: {
        labels: {
          style: {
            colors: '#64748b',
            fontSize: '11px',
            fontWeight: 500
          },
          formatter: (value) => {
            if (options.currency) {
              return `$${value.toFixed(2)}`
            }
            if (value >= 1000000) {
              return `${(value / 1000000).toFixed(1)}M`
            }
            if (value >= 1000) {
              return `${(value / 1000).toFixed(1)}k`
            }
            return value.toString()
          }
        }
      },
      fill: {
        type: options.gradient ? 'gradient' : 'solid',
        gradient: {
          shade: 'light',
          type: 'vertical',
          shadeIntensity: 0.25,
          gradientToColors: undefined,
          inverseColors: false,
          opacityFrom: 0.85,
          opacityTo: 0.25,
          stops: [0, 90, 100]
        }
      }
    }
  }

  // Area chart configuration
  const getAreaChartConfig = (options = {}) => {
    const lineConfig = getLineChartConfig(options)
    return {
      ...lineConfig,
      chart: {
        ...lineConfig.chart,
        type: 'area'
      },
      fill: {
        type: 'gradient',
        gradient: {
          shade: 'light',
          type: 'vertical',
          shadeIntensity: 0.25,
          opacityFrom: 0.4,
          opacityTo: 0.05,
          stops: [0, 100]
        }
      },
      dataLabels: {
        enabled: false
      }
    }
  }

  // Bar chart configuration
  const getBarChartConfig = (options = {}) => {
    const base = getBaseConfig()
    return {
      ...base,
      chart: {
        ...base.chart,
        type: 'bar',
        height: options.height || 350,
        background: 'transparent'
      },
      plotOptions: {
        bar: {
          borderRadius: options.borderRadius || 8,
          horizontal: options.horizontal || false,
          columnWidth: options.columnWidth || '60%',
          barHeight: options.barHeight || '70%',
          distributed: options.distributed || false,
          dataLabels: {
            position: 'top'
          }
        }
      },
      dataLabels: {
        enabled: options.showDataLabels || false,
        formatter: (val) => {
          if (options.currency) {
            return `$${val.toFixed(2)}`
          }
          return val.toString()
        },
        offsetY: -20,
        style: {
          fontSize: '12px',
          colors: ['#64748b']
        }
      },
      xaxis: {
        categories: options.categories || [],
        labels: {
          style: {
            colors: '#64748b',
            fontSize: '11px',
            fontWeight: 500
          }
        },
        axisBorder: {
          show: false
        },
        axisTicks: {
          show: false
        }
      },
      yaxis: {
        labels: {
          style: {
            colors: '#64748b',
            fontSize: '11px',
            fontWeight: 500
          },
          formatter: (value) => {
            if (options.currency) {
              return `$${value.toFixed(2)}`
            }
            if (value >= 1000000) {
              return `${(value / 1000000).toFixed(1)}M`
            }
            if (value >= 1000) {
              return `${(value / 1000).toFixed(1)}k`
            }
            return value.toString()
          }
        }
      }
    }
  }

  // Pie chart configuration
  const getPieChartConfig = (options = {}) => {
    const base = getBaseConfig()
    return {
      ...base,
      chart: {
        ...base.chart,
        type: 'pie',
        height: options.height || 350,
        background: 'transparent'
      },
      plotOptions: {
        pie: {
          donut: {
            size: options.donutSize || '0%'
          }
        }
      },
      dataLabels: {
        enabled: options.showDataLabels !== false,
        formatter: (val) => {
          return `${val.toFixed(1)}%`
        },
        style: {
          fontSize: '12px',
          fontFamily: 'Inter, system-ui, sans-serif',
          fontWeight: 600,
          colors: ['#ffffff']
        },
        dropShadow: {
          enabled: true
        }
      },
      legend: {
        ...base.legend,
        position: options.legendPosition || 'bottom',
        show: options.showLegend !== false
      },
      responsive: [{
        breakpoint: 480,
        options: {
          chart: {
            width: 200
          },
          legend: {
            position: 'bottom'
          }
        }
      }]
    }
  }

  // Donut chart configuration
  const getDonutChartConfig = (options = {}) => {
    const pieConfig = getPieChartConfig(options)
    return {
      ...pieConfig,
      chart: {
        ...pieConfig.chart,
        type: 'donut'
      },
      plotOptions: {
        pie: {
          donut: {
            size: options.donutSize || '65%',
            labels: {
              show: true,
              name: {
                show: true,
                fontSize: '16px',
                fontFamily: 'Inter, system-ui, sans-serif',
                color: '#374151',
                offsetY: -10
              },
              value: {
                show: true,
                fontSize: '24px',
                fontFamily: 'Inter, system-ui, sans-serif',
                fontWeight: 600,
                color: '#111827',
                offsetY: 16,
                formatter: (val) => {
                  if (options.currency) {
                    return `$${parseFloat(val).toFixed(2)}`
                  }
                  return val
                }
              },
              total: {
                show: options.showTotal !== false,
                showAlways: false,
                label: options.totalLabel || 'Total',
                fontSize: '16px',
                fontFamily: 'Inter, system-ui, sans-serif',
                color: '#374151',
                formatter: (w) => {
                  const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                  if (options.currency) {
                    return `$${total.toFixed(2)}`
                  }
                  return total.toString()
                }
              }
            }
          }
        }
      }
    }
  }

  // Mixed chart configuration (line + bar)
  const getMixedChartConfig = (options = {}) => {
    const base = getBaseConfig()
    return {
      ...base,
      chart: {
        ...base.chart,
        type: 'line',
        height: options.height || 350,
        background: 'transparent'
      },
      stroke: {
        width: [3, 0],
        curve: 'smooth'
      },
      plotOptions: {
        bar: {
          borderRadius: 8,
          columnWidth: '50%'
        }
      },
      fill: {
        opacity: [0.85, 0.25],
        gradient: {
          inverseColors: false,
          shade: 'light',
          type: 'vertical',
          opacityFrom: 0.85,
          opacityTo: 0.55,
          stops: [0, 100, 100, 100]
        }
      },
      markers: {
        size: 0
      },
      xaxis: {
        type: 'datetime',
        labels: {
          style: {
            colors: '#64748b',
            fontSize: '11px',
            fontWeight: 500
          }
        },
        axisBorder: {
          show: false
        },
        axisTicks: {
          show: false
        }
      },
      yaxis: [
        {
          title: {
            text: options.yAxisTitles?.[0] || '',
            style: {
              color: '#64748b',
              fontSize: '12px',
              fontWeight: 600
            }
          },
          labels: {
            style: {
              colors: '#64748b',
              fontSize: '11px'
            },
            formatter: (val) => {
              if (options.currency) {
                return `$${val.toFixed(2)}`
              }
              return val.toString()
            }
          }
        },
        {
          opposite: true,
          title: {
            text: options.yAxisTitles?.[1] || '',
            style: {
              color: '#64748b',
              fontSize: '12px',
              fontWeight: 600
            }
          },
          labels: {
            style: {
              colors: '#64748b',
              fontSize: '11px'
            },
            formatter: (val) => {
              return val.toString()
            }
          }
        }
      ]
    }
  }

  // Get color scheme
  const getColorScheme = (schemeName = 'revenue') => {
    return COLOR_SCHEMES[schemeName] || COLOR_SCHEMES.revenue
  }

  // Get gradient colors
  const getGradientColors = (colorName = 'primary') => {
    return CHART_COLORS[colorName]?.gradient || CHART_COLORS.primary.gradient
  }

  // Create series with gradients
  const createGradientSeries = (data, colorScheme = 'revenue') => {
    const colors = getColorScheme(colorScheme)
    return data.map((series, index) => ({
      ...series,
      color: colors[index % colors.length]
    }))
  }

  return {
    // Chart configurations
    getLineChartConfig,
    getAreaChartConfig,
    getBarChartConfig,
    getPieChartConfig,
    getDonutChartConfig,
    getMixedChartConfig,
    
    // Color utilities
    getColorScheme,
    getGradientColors,
    createGradientSeries,
    CHART_COLORS,
    COLOR_SCHEMES
  }
} 