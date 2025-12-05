@extends('layouts.app')
<style>
    .card {
        height: 90vh !important;
    }

    article {
        padding: 0 !important;
    }

    .charts-container {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
    }

    .chart-wrapper {
        flex: 1;
        min-height: 300px;
    }

    .chart-wrapper canvas {
        width: 100% !important;
        height: 300px !important;
    }
</style>
@section('content')
    <div class="grid">
        <div class="sidebar liquid-glass-card">
            <div class="content">
                <h1>FinAnalysis</h1>
                <nav class="main-nav"> <a href="{{ route('home') }}" class="nav-link active ">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"
                            fill="#e3e3e3">
                            <path
                                d="M240-200h120v-240h240v240h120v-360L480-740 240-560v360Zm-80 80v-480l320-240 320 240v480H520v-240h-80v240H160Zm320-350Z" />
                        </svg>
                        Домой
                    </a>

                    <a href="" class="nav-link ">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"
                            fill="#e3e3e3">
                            <path
                                d="M480-120q-138 0-240.5-91.5T122-440h82q14 104 92.5 172T480-200q117 0 198.5-81.5T760-480q0-117-81.5-198.5T480-760q-69 0-129 32t-101 88h110v80H120v-240h80v94q51-64 124.5-99T480-840q75 0 140.5 28.5t114 77q48.5 48.5 77 114T840-480q0 75-28.5 140.5t-77 114q-48.5 48.5-114 77T480-120Zm112-192L440-464v-216h80v184l128 128-56 56Z" />
                        </svg>
                        Транзакции
                    </a>

                </nav>
            </div>
        </div>
        <div class="dashboard-content-area">
            <article class="liquid-glass-card">
                <div class="charts-container">
                    <div class="chart-wrapper">
                        <canvas id="monthlyExpenseChart"></canvas>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="expensePieChart"></canvas>
                    </div>
                </div>
            </article>
            <article class="liquid-glass-card">
                <ul class="transaction-list">
                    @forelse ($recentTransactions as $transaction)
                        <li>
                            <span class="category-badge">
                                <span class="category-icon" style="background-color: {{ $transaction->category->color }};">
                                    {{ $transaction->category->getAbbreviationAttribute() }}
                                </span>
                            </span>
                            <span class="transaction-description" title="{{ $transaction->description }}">
                                {{ $transaction->description ?: 'Без описания' }}
                            </span>
                            <span class="{{ $transaction->type === 'income' ? 'success' : 'danger' }}">
                                {{ $transaction->type === 'income' ? '+' : '-' }}
                                ₽{{ number_format($transaction->amount, 2) }}
                            </span>
                        </li>
                    @empty
                        <li>Транзакций пока нет.</li>
                    @endforelse
                </ul>
            </article>
            <article class="liquid-glass-card">
                @auth

                    <form id="statement-upload-form" enctype="multipart/form-data">
                        @csrf

                        <!-- Выбор банка (теперь без margin на названиях) -->
                        <div class="bank-selection">
                            <label class="bank-option">
                                <input type="radio" name="parserType" value="sber" hidden>
                                <span class="bank-badge" style="background-color: #249936;">СБ</span>
                                <span class="bank-name">Сбер</span>
                            </label>

                            <label class="bank-option">
                                <input type="radio" name="parserType" value="tbank" hidden>
                                <span class="bank-badge" style="background-color: #f4d52b; color: black">ТБ</span>
                                <span class="bank-name">ТБанк</span>
                            </label>

                            <label class="bank-option">
                                <input type="radio" name="parserType" value="alfa" hidden>
                                <span class="bank-badge" style="background-color: #da1d2b;">АБ</span>
                                <span class="bank-name">Альфа-Банк</span>
                            </label>
                        </div>

                        <!-- Drag & Drop зона -->
                        <div class="file-drop-zone" id="drop-zone">
                            <input type="file" name="statement" id="statement-file" hidden required>
                            <div class="drop-content">
                                <div class="drop-icon"><svg xmlns="http://www.w3.org/2000/svg" height="24px"
                                        viewBox="0 -960 960 960" width="24px" fill="#e3e3e3">
                                        <path
                                            d="M720-330q0 104-73 177T470-80q-104 0-177-73t-73-177v-370q0-75 52.5-127.5T400-880q75 0 127.5 52.5T580-700v350q0 46-32 78t-78 32q-46 0-78-32t-32-78v-370h80v370q0 13 8.5 21.5T470-320q13 0 21.5-8.5T500-350v-350q-1-42-29.5-71T400-800q-42 0-71 29t-29 71v370q-1 71 49 120.5T470-160q70 0 119-49.5T640-330v-390h80v390Z" />
                                    </svg></div>
                                <div class="drop-text">
                                    <p>Перетащите выписку сюда</p>
                                    <p class="drop-hint">или нажмите для выбора</p>
                                </div>
                            </div>
                            <div class="file-info" id="file-info"></div>
                        </div>

                        <!-- Кнопка -->
                        <button type="submit" class="upload-btn" disabled>Загрузить выписку</button>

                        <!-- Результат -->
                        <div class="upload-result" id="upload-result"></div>
                    </form>
                @else
                    <div class="auth-required">
                        <p>Для добавления выписок требуется авторизация</p>
                        <a href="{{ route('login') }}" class="btn-login">Войти</a>
                    </div>
                @endauth
            </article>
            <a href="{{ route('parse-form') }}">Спарсить выписку</a>
            <a href="{{ route('logout') }}">Выйти</a>

        </div>
        <div class="liquid-glass-card chat-container">
            <div class="chat-history" id="chat-history">
                @if (!empty($chatHistory))
                    @foreach ($chatHistory as $index => $chat)
                        @if ($chat['role'] == 'user')
                            <div class="message user-message">
                                <div class="message-avatar">👤</div>
                                <div class="message-content">
                                    <div class="message-text">{{ $chat['content'] }}</div>
                                </div>
                            </div>
                        @endif

                        @if ($chat['role'] == 'assistant')
                            <div class="message assistant-message">
                                <div class="message-avatar">🤖</div>
                                <div class="message-content">
                                    <div class="message-text">{{ $chat['content'] }}</div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @else
                    <div class="empty-chat">
                        <p>Задайте вопрос по вашим финансам</p>
                    </div>
                @endif

            </div>
            <div class="chat-input-container">
                    <form id="chat-form" class="chat-form">
                        @csrf
                        <div class="input-group">
                            <textarea name="message" id="chat-message" rows="3" placeholder="Задайте вопрос по своим финансам..." required></textarea>
                        </div>
                        <div>
                            <button type="submit" id="send-button">Отправить запрос</button>
                        </div>
                    </form>
                </div>
        </div>
    </div>
@endsection
<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById('chat-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const messageInput = document.getElementById('chat-message');
            const userMessage = messageInput.value.trim();

            if (!userMessage) return;

            const payload = {
                message: userMessage,
            };

            fetch('{{ route('ask-ai') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    },
                    body: JSON.stringify(payload)
                })
                .then(function(result) {
                    console.log(result);
                })
                .catch(function(error) {
                    console.error(error);
                })
        });

        const expenseLabels = @json($expenseLabels);
        const expenseData = @json($expenseData);
        const expenseChartCtx = document.getElementById('monthlyExpenseChart').getContext('2d');

        new Chart(expenseChartCtx, {
            type: 'bar',
            data: {
                labels: expenseLabels,
                datasets: [{
                    label: 'Сумма расходов',
                    data: expenseData,
                    backgroundColor: '#0172ad',
                    borderWidth: 1,
                    borderRadius: {
                        topLeft: 6,
                        topRight: 6,
                        bottomLeft: 0,
                        bottomRight: 0
                    }
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.3)',
                            borderColor: 'white'
                        },
                        ticks: {
                            display: false
                        },
                        border: {
                            display: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        border: {
                            display: false,
                        }
                    }
                }
            }
        });

        const expenseCategories = @json($expenseCategories);
        const expenseCategoryAmounts = @json($expenseCategoryAmounts);
        const expenseCategoryColors = @json($expenseCategoryColors);
        const expensePieCtx = document.getElementById('expensePieChart').getContext('2d');

        new Chart(expensePieCtx, {
            type: 'doughnut',
            data: {
                labels: expenseCategories.map((label, index) => {
                    const total = expenseCategoryAmounts.reduce((a, b) => a + b, 0);
                    const percentage = Math.round((expenseCategoryAmounts[index] / total) *
                        100);
                    return `${label} (${percentage}%)`;
                }),
                datasets: [{
                    data: expenseCategoryAmounts,
                    backgroundColor: expenseCategoryColors,
                    borderWidth: 0,
                    // hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 8, // маленькие квадратики
                            boxHeight: 8, // делаем их круглыми
                            usePointStyle: true, // используем стиль точки (кружочки)
                            pointStyle: 'circle', // явно указываем кружочки
                            padding: 10, // небольшой отступ между элементами
                            font: {
                                size: 9 // маленький шрифт
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const value = context.raw || 0;
                                label += '₽' + value.toLocaleString();
                                return label;
                            }
                        }
                    }
                },
                cutout: '50%'
            }
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('statement-upload-form');
        const fileInput = document.getElementById('statement-file');
        const dropZone = document.getElementById('drop-zone');
        const fileInfo = document.getElementById('file-info');
        const uploadBtn = document.querySelector('.upload-btn');
        const resultDiv = document.getElementById('upload-result');
        const bankOptions = document.querySelectorAll('.bank-option');

        // Выбор банка
        if (form) {
            bankOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const radio = this.querySelector('input');
                    radio.checked = true;

                    // Убираем выделение у всех
                    bankOptions.forEach(opt => {
                        opt.classList.remove('selected');
                    });

                    // Добавляем выбранному
                    this.classList.add('selected');
                    checkFormValidity();
                });
            });

            // Drag & Drop
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(event => {
                dropZone.addEventListener(event, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(event => {
                dropZone.addEventListener(event, () => dropZone.classList.add('dragover'), false);
            });

            ['dragleave', 'drop'].forEach(event => {
                dropZone.addEventListener(event, () => dropZone.classList.remove('dragover'), false);
            });

            dropZone.addEventListener('drop', handleDrop, false);
            dropZone.addEventListener('click', () => fileInput.click());

            function handleDrop(e) {
                const files = e.dataTransfer.files;
                fileInput.files = files;
                handleFileSelect(files);
            }

            // Выбор файла
            fileInput.addEventListener('change', function() {
                handleFileSelect(this.files);
            });

            function handleFileSelect(files) {
                if (files.length > 0) {
                    const file = files[0];
                    fileInfo.textContent = `${file.name} (${formatFileSize(file.size)})`;
                    checkFormValidity();
                }
            }

            // Проверка формы
            function checkFormValidity() {
                const bankSelected = document.querySelector('input[name="parserType"]:checked');
                const fileSelected = fileInput.files.length > 0;

                uploadBtn.disabled = !(bankSelected && fileSelected);
            }

            // Отправка
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const originalText = uploadBtn.textContent;
                uploadBtn.disabled = true;
                uploadBtn.textContent = 'Загрузка...';
                resultDiv.style.display = 'none';

                const formData = new FormData(this);

                try {
                    const response = await fetch('{{ route('parse') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')
                                .value,
                        },
                        body: formData
                    });
                    console.log(response.status);
                    if (response.status === 401) {
                        window.location.href = '/login';
                        return;
                    }

                    if (response.status === 419) {
                        location.reload();
                        return;
                    }
                    const data = await response.json();

                    if (data.success) {
                        showResult(
                            `✓ Добавлено ${data.stats.saved} транзакций` +
                            (data.stats.duplicated > 0 ?
                                `, ${data.stats.duplicated} дубликатов пропущено` : ''),
                            'success'
                        );

                        // Сброс формы
                        form.reset();
                        fileInfo.textContent = '';
                        bankOptions.forEach(opt => opt.classList.remove('selected'));

                        // Обновляем страницу
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showResult(`✗ ${data.message}`, 'error');
                        uploadBtn.disabled = false;
                    }
                } catch (error) {
                    showResult('✗ ' + error, 'error');
                    uploadBtn.disabled = false;
                }

                uploadBtn.textContent = originalText;
            });
        }

        function showResult(message, type) {
            resultDiv.textContent = message;
            resultDiv.className = `upload-result ${type}`;
            resultDiv.style.display = 'block';
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        }
    });
</script>
