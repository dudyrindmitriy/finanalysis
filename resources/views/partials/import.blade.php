<article class="liquid-glass-card">
    @auth

        <form id="statement-upload-form" enctype="multipart/form-data" data-parse-route="{{ route('parse') }}">
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
                    <div class="drop-icon"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960"
                            width="24px" fill="#e3e3e3">
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
<article class="liquid-glass-card manual-import">
    @auth
        <h3>Добавить транзакцию</h3>
        <form id="manual-transaction-form" data-import-route="{{ route('importTransaction') }}">
            @csrf

            <!-- Тип -->
            <div class="type-selection">
                <label class="type-option">
                    <input type="radio" name="type" value="expense" checked hidden>
                    <span class="type-badge expense">Расход</span>
                </label>
                <label class="type-option">
                    <input type="radio" name="type" value="income" hidden>
                    <span class="type-badge income">Доход</span>
                </label>
            </div>

            <!-- Поля формы -->
            <input type="number" name="amount" placeholder="Сумма" step="0.01" min="0.01"  max="99999999.99" required>
            <input type="date" name="date" value="{{ date('Y-m-d') }}" required>
            <input type="text" name="description" placeholder="Описание">

            <!-- Категория -->
            <select name="category_id">
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>

            <!-- Кнопки -->
            <div class="form-actions">
                <button type="submit" class="manual-btn">Добавить</button>
            </div>

            <!-- Результат -->
             <div class="result" id="manual-result"></div>
        </form>
    @else
        <p>Для добавления транзакций требуется авторизация</p>
        <a href="{{ route('login') }}">Войти</a>
    @endauth
</article>
