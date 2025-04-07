<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IoT Monitoring Dashboard_by Rangga</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Luxon for better date handling -->
    <script src="https://cdn.jsdelivr.net/npm/luxon@2.0.2/build/global/luxon.min.js"></script>
    <!-- Chart.js adapter for Luxon -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-luxon"></script>
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        [x-cloak] { display: none !important; }
        .gradient-card {
            background: linear-gradient(145deg, #ffffff 0%, #f3f4f6 100%);
        }
        .chart-container {
            position: relative;
            transition: all 0.3s ease;
            border-radius: 12px;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .chart-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .metric-card {
            transition: all 0.3s ease;
            border-radius: 12px;
        }
        .metric-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-gray-50">
    <div x-data="dashboard()" x-init="init()" x-cloak>
        <!-- Navigation -->
        <nav class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between h-16">
                    <div class="flex items-center space-x-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 text-transparent bg-clip-text">Jalur Langit Monitoring</span>
                    </div>
                    <div class="flex items-center">
                        <div class="flex items-center space-x-2 text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span x-text="currentTime" class="font-medium"></span>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Sensor Section -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Sensor Metrics
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Temperature Card -->
                    <div class="gradient-card rounded-xl shadow-lg p-6 metric-card">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Suhu</p>
                                <p class="mt-2 flex items-baseline">
                                    <span class="text-3xl font-bold text-gray-900" x-text="formatNumber(sensorData.suhu)"></span>
                                    <span class="ml-1 text-xl text-gray-500">°C</span>
                                </p>
                            </div>
                            <div class="p-3 bg-blue-100 rounded-lg">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">Last updated</span>
                                <span class="font-medium text-blue-600" x-text="getLastUpdateTime()"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Humidity Card -->
                    <div class="gradient-card rounded-xl shadow-lg p-6 metric-card">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Kelembapan</p>
                                <p class="mt-2 flex items-baseline">
                                    <span class="text-3xl font-bold text-gray-900" x-text="formatNumber(sensorData.kelembapan)"></span>
                                    <span class="ml-1 text-xl text-gray-500">%</span>
                                </p>
                            </div>
                            <div class="p-3 bg-green-100 rounded-lg">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">Last updated</span>
                                <span class="font-medium text-green-600" x-text="getLastUpdateTime()"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Light Card -->
                    <div class="gradient-card rounded-xl shadow-lg p-6 metric-card">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Cahaya</p>
                                <p class="mt-2 flex items-baseline">
                                    <span class="text-3xl font-bold text-gray-900" x-text="formatNumber(sensorData.cahaya)"></span>
                                    <span class="ml-1 text-xl text-gray-500">lux</span>
                                </p>
                            </div>
                            <div class="p-3 bg-yellow-100 rounded-lg">
                                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">Last updated</span>
                                <span class="font-medium text-yellow-600" x-text="getLastUpdateTime()"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Power Section -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Power Metrics
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <template x-for="(value, key) in powerData" :key="key">
                        <div class="gradient-card rounded-xl shadow-lg p-6 metric-card">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600" x-text="formatLabel(key)"></p>
                                    <p class="mt-2 flex items-baseline">
                                        <span class="text-3xl font-bold text-gray-900" x-text="formatNumber(value)"></span>
                                        <span class="ml-1 text-xl text-gray-500" x-text="getUnit(key)"></span>
                                    </p>
                                </div>
                                <div class="p-3 bg-purple-100 rounded-lg">
                                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-4">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500">Last updated</span>
                                    <span class="font-medium text-purple-600" x-text="getLastUpdateTime()"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
                <!-- Filter Section - Dipindah ke atas dan full width -->
                <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800">Data Filter</h3>
                        <div class="flex space-x-4">
                        <!-- Export Buttons -->
                        <div class="flex space-x-2">
                            <button @click="exportToExcel('sensor')" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                                Export Sensor Data
                            </button>
                            <button @click="exportToExcel('power')" class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                Export Power Data
                            </button>
                        </div>
                    </div>
                    <div class="flex space-x-4">
                            <!-- Quick Filters -->
                            <div class="flex space-x-2">
                                <button @click="setTimeRange('1h')" class="px-3 py-2 text-sm font-medium rounded-md" :class="timeRange === '1h' ? 'bg-blue-100 text-blue-600' : 'text-gray-600 hover:bg-gray-100'">1h</button>
                                <button @click="setTimeRange('6h')" class="px-3 py-2 text-sm font-medium rounded-md" :class="timeRange === '6h' ? 'bg-blue-100 text-blue-600' : 'text-gray-600 hover:bg-gray-100'">6h</button>
                                <button @click="setTimeRange('24h')" class="px-3 py-2 text-sm font-medium rounded-md" :class="timeRange === '24h' ? 'bg-blue-100 text-blue-600' : 'text-gray-600 hover:bg-gray-100'">24h</button>
                            </div>
                            <!-- Custom Date Range -->
                            <div class="relative">
                                <input 
                                    type="text" 
                                    id="dateRange" 
                                    class="px-4 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Custom Range"
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sensor Chart dengan fixed height -->
                <div class="bg-white rounded-xl shadow-lg p-6 chart-container" style="height: 400px; overflow: hidden;">
                    <h3 class="text-lg font-semibold mb-6 text-gray-800">Sensor Metrics Trend</h3>
                    <div style="height: calc(100% - 60px);">
                        <canvas id="sensorChart"></canvas>
                    </div>
                </div>
                
                <!-- Power Chart dengan fixed height -->
                <div class="bg-white rounded-xl shadow-lg p-6 chart-container" style="height: 400px; overflow: hidden;">
                    <h3 class="text-lg font-semibold mb-6 text-gray-800">Power Metrics Trend</h3>
                    <div style="height: calc(100% - 60px);">
                        <canvas id="powerChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let sensorChart;
        let powerChart;

        function initCharts() {
            Chart.defaults.font.family = "'Inter', 'system-ui', '-apple-system', 'sans-serif'";
            Chart.defaults.font.size = 12;
            Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(17, 24, 39, 0.9)';
            Chart.defaults.plugins.tooltip.padding = 12;
            Chart.defaults.plugins.tooltip.titleFont.size = 14;
            Chart.defaults.plugins.tooltip.titleFont.weight = '600';
            Chart.defaults.plugins.tooltip.bodyFont.size = 13;
            Chart.defaults.plugins.legend.labels.padding = 15;

            // Sensor Chart
            const sensorCtx = document.getElementById('sensorChart').getContext('2d');
            const sensorGradient = sensorCtx.createLinearGradient(0, 0, 0, 400);
            sensorGradient.addColorStop(0, 'rgba(37, 99, 235, 0.2)');
            sensorGradient.addColorStop(1, 'rgba(37, 99, 235, 0)');

            const humidityGradient = sensorCtx.createLinearGradient(0, 0, 0, 400);
            humidityGradient.addColorStop(0, 'rgba(16, 185, 129, 0.2)');
            humidityGradient.addColorStop(1, 'rgba(16, 185, 129, 0)');

            const lightGradient = sensorCtx.createLinearGradient(0, 0, 0, 400);
            lightGradient.addColorStop(0, 'rgba(245, 158, 11, 0.2)');
            lightGradient.addColorStop(1, 'rgba(245, 158, 11, 0)');

            sensorChart = new Chart(sensorCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Suhu (°C)',
                        borderColor: 'rgb(37, 99, 235)',
                        backgroundColor: sensorGradient,
                        borderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 4,
                        pointBackgroundColor: 'white',
                        pointHoverBackgroundColor: 'rgb(37, 99, 235)',
                        tension: 0.4,
                        fill: true,
                        data: []
                    }, {
                        label: 'Kelembapan (%)',
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: humidityGradient,
                        borderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 4,
                        pointBackgroundColor: 'white',
                        pointHoverBackgroundColor: 'rgb(16, 185, 129)',
                        tension: 0.4,
                        fill: true,
                        data: []
                    }, {
                        label: 'Cahaya (lux)',
                        borderColor: 'rgb(245, 158, 11)',
                        backgroundColor: lightGradient,
                        borderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 4,
                        pointBackgroundColor: 'white',
                        pointHoverBackgroundColor: 'rgb(245, 158, 11)',
                        tension: 0.4,
                        fill: true,
                        data: []
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 12,
                                    weight: '500'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.9)',
                            titleColor: 'rgba(255, 255, 255, 0.9)',
                            bodyColor: 'rgba(255, 255, 255, 0.9)',
                            padding: 12,
                            displayColors: true,
                            usePointStyle: true
                        }
                    },
                    scales: {
                        x: {
                            type: 'time',
                            time: {
                                unit: 'minute',
                                displayFormats: {
                                    minute: 'HH:mm'
                                }
                            },
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                },
                                padding: 10,
                                color: 'rgba(107, 114, 128, 0.7)'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                },
                                padding: 10,
                                color: 'rgba(107, 114, 128, 0.7)'
                            }
                        }
                    }
                }
            });

            // Power Chart
            const powerCtx = document.getElementById('powerChart').getContext('2d');
            const voltageGradient = powerCtx.createLinearGradient(0, 0, 0, 400);
            voltageGradient.addColorStop(0, 'rgba(139, 92, 246, 0.2)');
            voltageGradient.addColorStop(1, 'rgba(139, 92, 246, 0)');

            const currentGradient = powerCtx.createLinearGradient(0, 0, 0, 400);
            currentGradient.addColorStop(0, 'rgba(236, 72, 153, 0.2)');
            currentGradient.addColorStop(1, 'rgba(236, 72, 153, 0)');

            powerChart = new Chart(powerCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Voltage (V)',
                        borderColor: 'rgb(139, 92, 246)',
                        backgroundColor: voltageGradient,
                        borderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 4,
                        pointBackgroundColor: 'white',
                        pointHoverBackgroundColor: 'rgb(139, 92, 246)',
                        tension: 0.4,
                        fill: true,
                        data: []
                    }, {
                        label: 'Current (A)',
                        borderColor: 'rgb(236, 72, 153)',
                        backgroundColor: currentGradient,
                        borderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 4,
                        pointBackgroundColor: 'white',
                        pointHoverBackgroundColor: 'rgb(236, 72, 153)',
                        tension: 0.4,
                        fill: true,
                        data: []
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 12,
                                    weight: '500'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.9)',
                            titleColor: 'rgba(255, 255, 255, 0.9)',
                            bodyColor: 'rgba(255, 255, 255, 0.9)',
                            padding: 12,
                            displayColors: true,
                            usePointStyle: true
                        }
                    },
                    scales: {
                        x: {
                            type: 'time',
                            time: {
                                unit: 'minute',
                                displayFormats: {
                                    minute: 'HH:mm'
                                }
                            },
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                },
                                padding: 10,
                                color: 'rgba(107, 114, 128, 0.7)'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                },
                                padding: 10,
                                color: 'rgba(107, 114, 128, 0.7)'
                            }
                        }
                    }
                }
            });
        }

        function updateCharts(data) {
            const sensorHistory = data.sensor.history;
            const powerHistory = data.power.history;

            // Update Sensor Chart with animation
            sensorChart.data.labels = sensorHistory.map(d => d.created_at);
            sensorChart.data.datasets[0].data = sensorHistory.map(d => d.suhu);
            sensorChart.data.datasets[1].data = sensorHistory.map(d => d.kelembapan);
            sensorChart.data.datasets[2].data = sensorHistory.map(d => d.cahaya);
            sensorChart.update('show');

            // Update Power Chart with animation
            powerChart.data.labels = powerHistory.map(d => d.created_at);
            powerChart.data.datasets[0].data = powerHistory.map(d => d.voltage);
            powerChart.data.datasets[1].data = powerHistory.map(d => d.current);
            powerChart.update('show');
        }

        function dashboard() {
            return {
                sensorData: {},
                powerData: {},
                currentTime: '',
                timeRange: '1h',
                dateRange: null,
                
                init() {
                    initCharts();
                    this.updateData();
                    this.updateCurrentTime();
                    this.initDateRangePicker();
                    
                    setInterval(() => this.updateData(), 5000);
                    setInterval(() => this.updateCurrentTime(), 1000);
                },
                initDateRangePicker() {
                    const fp = flatpickr("#dateRange", {
                        mode: "range",
                        enableTime: true,
                        dateFormat: "Y-m-d H:i",
                        onChange: (selectedDates) => {
                            if (selectedDates.length === 2) {
                                this.timeRange = 'custom';
                                this.dateRange = selectedDates;
                                this.updateData();
                            }
                        }
                    });
                },

                setTimeRange(range) {
                    this.timeRange = range;
                    this.dateRange = null;
                    document.querySelector('#dateRange')._flatpickr.clear();
                    this.updateData();
                },

                async updateData() {
                    try {
                        let url = '{{ route("dashboard.latest") }}?';
                        
                        if (this.timeRange === 'custom' && this.dateRange) {
                            url += `start=${this.dateRange[0].toISOString()}&end=${this.dateRange[1].toISOString()}`;
                        } else {
                            url += `range=${this.timeRange}`;
                        }

                        const response = await fetch(url);
                        const data = await response.json();
                        if (data.alert) {
                            this.showAlert(data.alert);
                        }
                        
                        // Smooth transition for data updates
                        this.sensorData = {
                            ...this.sensorData,
                            ...data.sensor.latest
                        };
                        this.powerData = {
                            ...this.powerData,
                            ...data.power.latest
                        };
                        
                        if (data.sensor.history && data.power.history) {
                            updateCharts(data);
                        }
                    } catch (error) {
                        console.error('Error fetching data:', error);
                    }
                },

                showAlert(alert) {
                    const alertDiv = document.createElement('div');
                    alertDiv.className = `fixed top-4 right-4 p-4 rounded-lg z-50 ${
                        alert.type === 'danger' ? 'bg-red-500' : 'bg-yellow-500'
                    } text-white shadow-lg transition-opacity duration-500`;
                    alertDiv.textContent = alert.message;
                    
                    document.body.appendChild(alertDiv);

                    // Fade out effect
                    setTimeout(() => {
                        alertDiv.style.opacity = '0';
                        setTimeout(() => alertDiv.remove(), 500);
                    }, 4500);
                },

                updateCurrentTime() {
                    const formatter = new Intl.DateTimeFormat('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: false,
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    });
                    this.currentTime = formatter.format(new Date());
                },

                formatNumber(value) {
                    if (value === null || value === undefined || isNaN(value)) {
                        return '0.00';
                    }
                    return Number(value).toFixed(2);
                },

                formatLabel(key) {
                    return key.split('_').map(word => 
                        word.charAt(0).toUpperCase() + word.slice(1)
                    ).join(' ');
                },

                getUnit(key) {
                    const units = {
                        reactive_energy: 'kVArh',
                        reactive_power: 'kVAr',
                        current: 'A',
                        voltage: 'V',
                        frequency: 'Hz',
                        power_factor: '',
                        apparent_power: 'kVA',
                        power_timestamp: '',
                        created_at: '',
                        updated_at: ''
                    };
                    return units[key] || '';
                },

                getLastUpdateTime() {
                    const now = new Date();
                    const formatter = new Intl.RelativeTimeFormat('id-ID', {
                        numeric: 'auto'
                    });
                    const seconds = Math.floor((now - new Date(this.sensorData.updated_at || now)) / 1000);
                    
                    if (seconds < 60) return 'Just now';
                    if (seconds < 3600) return formatter.format(-Math.floor(seconds / 60), 'minute');
                    return formatter.format(-Math.floor(seconds / 3600), 'hour');
                }
            }
        }
    </script>
</body>
</html>