@extends('admin.layouts.app')

@section('content')
    <div class="container mt-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Service Stats Report</h2>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    Filter by Period
                </button>
                <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                    <li><a class="dropdown-item" href="#" onclick="filterData('week')">Last 7 Days</a></li>
                    <li><a class="dropdown-item" href="#" onclick="filterData('month')">Last 30 Days</a></li>
                    <li><a class="dropdown-item" href="#" onclick="filterData('all')">All Time</a></li>
                </ul>
            </div>
        </div>

        <!-- Services Popularity Chart -->
        <div class="card p-3 mb-4">
            <h5 class="fw-bold mb-3">Service Popularity & Revenue</h5>
            <div class="row">
                <div class="col-12">
                    <div style="height: 400px;">
                        <canvas id="servicesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Services Table -->
        <div class="card p-3">
            <h5 class="fw-bold">Service Revenue Details</h5>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col" class="text-muted">Service Name</th>
                        <th scope="col" class="text-muted">Total Orders</th>
                        <th scope="col" class="text-muted">Revenue</th>
                        <th scope="col" class="text-muted">Popularity Rank</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($serviceStats as $stat)
                        <tr>
                            <td>{{ $stat->service }}</td>
                            <td>{{ $stat->total_orders }}</td>
                            <td>₱{{ number_format($stat->revenue, 2) }}</td>
                            <td>{{ $stat->rank }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        // Wait for the DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Get the canvas element
            const canvas = document.getElementById('servicesChart');
            if (!canvas) {
                console.error('Canvas element not found');
                return;
            }

            // Get the context
            const ctx = canvas.getContext('2d');
            if (!ctx) {
                console.error('Could not get canvas context');
                return;
            }

            // Create the chart
            const chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($labels) !!},
                    datasets: [
                        {
                            label: 'Revenue (₱)',
                            data: {!! json_encode($revenues) !!},
                            backgroundColor: 'rgba(54, 162, 235, 0.8)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1,
                            yAxisID: 'y',
                            order: 2
                        },
                        {
                            label: 'Total Orders',
                            data: {!! json_encode($orders) !!},
                            backgroundColor: 'rgba(75, 192, 192, 0.8)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1,
                            yAxisID: 'y1',
                            order: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Services',
                                font: {
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Revenue (₱)',
                                font: {
                                    weight: 'bold'
                                }
                            },
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value.toLocaleString();
                                }
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Number of Orders',
                                font: {
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                drawOnChartArea: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.dataset.yAxisID === 'y') {
                                        label += '₱' + context.parsed.y.toLocaleString();
                                    } else {
                                        label += context.parsed.y;
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });

            // Store chart instance for later use
            window.servicesChart = chart;
        });

        function filterData(period) {
            // Show loading state
            document.querySelector('.card').style.opacity = '0.5';
            
            // Fetch new data
            fetch(`/admin/reports/service-stats?period=${period}`)
                .then(response => response.text())
                .then(html => {
                    // Update the page content
                    document.documentElement.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.querySelector('.card').style.opacity = '1';
                });
        }
    </script>
@endsection
