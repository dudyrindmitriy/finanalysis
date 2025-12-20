document.addEventListener("DOMContentLoaded", function () {
    const chatForm = document.getElementById('chat-form');
    chatForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const messageInput = document.getElementById('chat-message');
        const userMessage = messageInput.value.trim();

        if (!userMessage) return;

        // Показываем сообщение пользователя сразу
        addMessageToChat('USER', userMessage);

        const payload = {
            message: userMessage,
        };
        messageInput.value = '';
        const askAiRoute = chatForm.dataset.askAiRoute;
        fetch(askAiRoute, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            },
            body: JSON.stringify(payload)
        })
            .then(response => response.json())
            .then(data => {
                console.log('Cohere response:', data);

                // Обработка ответа от Cohere API
                if (data.content) {
                    // Прямой ответ от Cohere
                    addMessageToChat('CHATBOT', data.content);

                } else {
                    addMessageToChat('CHATBOT', 'Непонятный формат ответа');
                }

                scrollToBottom();
            })
            .catch(error => {
                console.error('Fetch error:', error);
                addMessageToChat('CHATBOT', 'Ошибка соединения с сервером');
            });
    });

    // Обновлённая функция
    function addMessageToChat(role, content) {
        const chatHistory = document.getElementById('chat-history');

        const messageDiv = document.createElement('div');

        if (role === 'USER') {
            messageDiv.className = 'message user-message';
        } else if (role === 'CHATBOT') {
            messageDiv.className = 'message assistant-message';
        }

        const contentDiv = document.createElement('div');
        contentDiv.className = 'message-content';

        const textDiv = document.createElement('div');
        textDiv.className = 'message-text';
        textDiv.textContent = content;

        contentDiv.appendChild(textDiv);
        messageDiv.appendChild(contentDiv);

        chatHistory.appendChild(messageDiv);
        scrollToBottom();

    }

    function scrollToBottom() {
        const chatHistory = document.getElementById('chat-history');
        chatHistory.scrollTop = chatHistory.scrollHeight;
    }
});
function initImport() {
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
            option.addEventListener('click', function () {
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
        fileInput.addEventListener('change', function () {
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
        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const originalText = uploadBtn.textContent;
            uploadBtn.disabled = true;
            uploadBtn.textContent = 'Загрузка...';
            resultDiv.style.display = 'none';

            const formData = new FormData(this);
            const parseRoute = form.dataset.parseRoute
            try {
                const response = await fetch(parseRoute, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': form.querySelector('input[name="_token"]')
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
                    reloadPage();
                    return;
                }
                const data = await response.json();

                if (data.success) {
                    showResult(
                        `✓ Добавлено ${data.stats.saved} транзакций` +
                        (data.stats.duplicated > 0 ?
                            `, ${data.stats.duplicated} дубликатов пропущено` : ''),
                        'success', resultDiv
                    );

                    // Сброс формы
                    form.reset();
                    fileInfo.textContent = '';
                    bankOptions.forEach(opt => opt.classList.remove('selected'));

                    // Обновляем страницу
                    setTimeout(() => reloadPage(), 1500);
                } else {
                    showResult(`✗ ${data.message}`, 'error', resultDiv);
                    uploadBtn.disabled = false;
                }
            } catch (error) {
                showResult('✗ ' + error, 'error', resultDiv);
                uploadBtn.disabled = false;
            }

            uploadBtn.textContent = originalText;
        });
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }
};

function initManualImport() {
    const form = document.getElementById('manual-transaction-form');
    const uploadBtn = document.querySelector('.manual-btn');
    const resultDiv = document.getElementById('manual-result');

    if (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const originalText = uploadBtn.textContent;
            uploadBtn.disabled = true;
            uploadBtn.textContent = 'Загрузка...';
            resultDiv.style.display = 'none';

            const formData = new FormData(this);

            const importRoute = form.dataset.importRoute;
            try {
                const response = await fetch(importRoute, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': form.querySelector('input[name="_token"]')
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
                    reloadPage();
                    return;
                }
                const data = await response.json();

                if (data.success) {
                    showResult(
                        `✓ Добавлено ${data.stats.saved} транзакций` +
                        (data.stats.duplicated > 0 ?
                            `, ${data.stats.duplicated} дубликатов пропущено` : ''),
                        'success', resultDiv
                    );

                    form.reset();
                    setTimeout(() => reloadPage(), 1500);
                } else {
                    showResult(`✗ ${data.message}`, 'error', resultDiv);
                    uploadBtn.disabled = false;
                }
            } catch (error) {
                showResult('✗ ' + error, 'error', resultDiv);
                uploadBtn.disabled = false;
            }

            uploadBtn.textContent = originalText;
        });
    }
}
function showResult(message, type, resultDiv) {
    resultDiv.textContent = message;
    resultDiv.className = `upload-result ${type}`;
    resultDiv.style.display = 'block';
}

document.addEventListener("DOMContentLoaded", function () {
    const navLinks = document.querySelectorAll('.nav-link');
    const activeLink = document.querySelector('.nav-link.active');

    // Функции для загрузки страниц


    function setupNavigation() {
        navLinks.forEach(navLink => {
            navLink.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                // Обновляем активную ссылку
                navLinks.forEach(link => {
                    link.classList.remove('active');
                });
                this.classList.add('active');

                // Загружаем страницу
                const route = this.dataset.route;
                const page = this.dataset.page;
                loadPage(route, page);
            });
        });
    }

    function loadActivePage() {
        if (activeLink) {
            const activeRoute = activeLink.dataset.route;
            const activePage = activeLink.dataset.page;
            loadPage(activeRoute, activePage);
        }
    }

    // Инициализация
    loadActivePage();
    setupNavigation();
});
function loadPage(route, pageType) {
    const contentArea = document.querySelector('.dashboard-content-area');

    fetch(route, {
        method: 'GET'
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(html => {
            contentArea.innerHTML = html;

            // Вызываем соответствующую инициализацию
            switch (pageType) {
                case 'dashboard':
                    initDashboardPage();
                    break;
                case 'transactions':
                    initTransactionsPage();
                    break;
                case 'import':
                    initImportPage();
                    break;
                case 'goals':
                    initGoalsPage();
                    break;
                default:
                    console.log('Page type not specified');
            }
        })
        .catch(error => {
            console.error('There was a problem with the fetch operation:', error);
            contentArea.innerHTML = '<div class="error">Ошибка загрузки страницы</div>';
        });
}


function reloadPage() {
    const activeLink = document.querySelector('.nav-link.active');
    if (activeLink) {
        const route = activeLink.dataset.route;
        const page = activeLink.dataset.page;
        loadPage(route, page);
    }
}
function initToTtansactionsBtn() {
    const toTransactionsBtn = document.querySelector('#toTransactionsPage');
    const transactionLink = document.querySelector('.nav-link[data-page="transactions"]');
    if (toTransactionsBtn && transactionLink) {
        toTransactionsBtn.addEventListener('click', function (e) {
            e.preventDefault();
            console.log('click')
            transactionLink.click();
        });
    }
}
function initGraphs() {

    const expenseChart = document.getElementById('monthlyExpenseChart');
    if (expenseChart) {
        const expenseLabels = JSON.parse(expenseChart.dataset.labels);
        const expenseData = JSON.parse(expenseChart.dataset.data);
        const expenseChartCtx = expenseChart.getContext('2d');

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
    }

    const expensePie = document.getElementById('expensePieChart');
    if (expensePie) {
        const expenseCategories = JSON.parse(expensePie.dataset.categories);
        const expenseCategoryAmounts = JSON.parse(expensePie.dataset.amounts);
        const expenseCategoryColors = JSON.parse(expensePie.dataset.colors);
        const expensePieCtx = expensePie.getContext('2d');

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
                            label: function (context) {
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
    }
}
function getPaginationElements() {
    const showMoreBtn = document.querySelector('.pagination .next-page');
    const nav = document.querySelector('.dashboard-content-area > article > nav');
    const nextA = nav ? nav.querySelector('.flex').children[1] : null;
    const isValid = nextA && nextA.tagName === 'A';

    return { showMoreBtn, nav, nextA, isValid };
}

function initPagination() {
    const elements = getPaginationElements();

    if (elements.showMoreBtn) {
        elements.showMoreBtn.disabled = !elements.isValid;
        elements.showMoreBtn.removeEventListener('click', clickHandler);
        elements.showMoreBtn.addEventListener('click', clickHandler);
    }
}

const clickHandler = function (e) {
    e.preventDefault();
    e.stopPropagation();

    // Получаем актуальные элементы при клике
    const elements = getPaginationElements();

    if (!elements.isValid) return;

    e.currentTarget.disabled = true;

    fetch(elements.nextA.href, {
        method: 'GET'
    })
        .then(response => response.text())
        .then(htmlString => {
            const parser = new DOMParser();
            const html = parser.parseFromString(htmlString, 'text/html');

            const transactionList = document.querySelector('.dashboard-content-area .transaction-list');
            const newTransactions = html.querySelector('.transaction-list');
            if (transactionList && newTransactions) {
                transactionList.innerHTML += newTransactions.innerHTML;
            }

            const newNav = html.querySelector('nav');
            if (newNav && elements.nav) {
                elements.nav.innerHTML = newNav.innerHTML;
            }

            initPagination();
        })
        .catch(error => {
            console.error('Fetch error:', error);
            e.currentTarget.disabled = false;
        });
};

function initDashboardPage() {
    initGraphs();
    initToTtansactionsBtn();
    initImport();
}

function initTransactionsPage() {
    initPagination();
}

function initImportPage() {
    initImport();
    initManualImport();
}

function initGoalsPage() {
    console.log('goals')
    // Создание цели
    const goalForm = document.getElementById('create-goal-form');
    if (goalForm) {
        const route = goalForm.dataset.goalcreateRoute;
        goalForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            // Устанавливаем минимальную дату - сегодня
            const deadlineInput = this.querySelector('input[name="deadline"]');
            const today = new Date().toISOString().split('T')[0];
            if (deadlineInput.value < today) {
                alert('Дата должна быть не раньше сегодняшнего дня');
                return;
            }

            const response = await fetch(route, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                }
            });

            const data = await response.json();
            if (data.success) {
                reloadPage();
            } else {
                alert(data.message || 'Ошибка создания цели');
            }
        });
    }

    document.querySelectorAll('.add-money-btn').forEach(button => {
        button.addEventListener('click', async function () {
            const goalId = this.dataset.goalId;
            const input = document.querySelector(`.add-money-input[data-goal-id="${goalId}"]`);
            const amount = input.value;
            const route = this.dataset.addmoneyRoute;
            if (!amount || parseFloat(amount) <= 0) {
                alert('Введите сумму больше 0');
                return;
            }

            const response = await fetch(route, {
                method: 'POST',
                body: JSON.stringify({
                    goal_id: goalId,
                    amount: amount,
                    _token: document.querySelector('input[name="_token"]').value
                }),
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                }
            });

            const data = await response.json();
            if (data.success) {
                if (data.message) {
                    alert(data.message);
                }

                // Если цель выполнена
                if (data.completed) {
                    // Можно добавить визуальное выделение
                    const goalItem = this.closest('.goal-item');
                    if (goalItem) {
                        goalItem.classList.add('goal-completed');
                        // Показываем поздравление
                        setTimeout(() => {
                            alert('🎉 Поздравляем! Цель достигнута!');
                        }, 100);
                    }
                }
                reloadPage();
            } else {
                alert(data.message || 'Ошибка');
            }
        });
    });
}
