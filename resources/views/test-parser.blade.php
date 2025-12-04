<!DOCTYPE html>
<html>

<head>
    <title>Тест парсера</title>
</head>

<body>
    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
@endif
    <h1>Загрузите PDF выписку Сбербанка</h1>

    <form action="{{ route('parse') }}" method="post" enctype="multipart/form-data">
        @csrf
        <input type="file" name="statement" accept=".pdf .xlsx" required>
        <select name="parserType" id="">
            <option value="sber">Сбер</option>
            <option value="tbank">ТБанк</option>
            <option value="alfa">АльфаБанк</option>
        </select>
        <button type="submit">Протестировать парсинг</button>
    </form>

    @if (isset($error))
        <div style="color: red; margin-top: 20px;">
            <strong>Ошибка:</strong> {{ $error }}
        </div>
    @endif

    @if (isset($result))
        <div style="margin-top: 20px;">
            <h2>Результат парсинга:</h2>
            <p><strong>Длина текста:</strong> {{ $result['text_length'] }} символов</p>

            <h3>Полный текст:</h3>
            <pre style="background: #f5f5f5; padding: 15px; border: 1px solid #ddd; overflow: auto; max-height: 500px;">
            @if (is_string($result['full_text']))
{{ $result['full_text'] }}
@elseif (is_array($result['full_text']))
{{ var_dump($result['full_text']) }}
@endif
        </pre>
        </div>
    @endif
    @if (isset($result['transactions']))
        <div style="margin-top: 20px;">
            <h2>Найдено транзакций: {{ $result['transactions_count'] }}</h2>

            <table border="1" style="border-collapse: collapse; width: 100%;">
                <thead>
                    <tr>
                        <th>Дата</th>
                        <th>Время</th>
                        <th>Категория</th>
                        <th>Категория Банка</th>
                        <th>Сумма</th>
                        <th>Тип</th>
                        <th>Описание</th>
                        <th>Остаток</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($result['transactions'] as $transaction)
                        <tr>
                            <td>{{ $transaction['date'] }}</td>
                            <td>{{ $transaction['time'] }}</td>
                            <td>{{ $transaction['category'] }}</td>
                            <td>{{ $transaction['bank_category'] }}</td>
                            <td style="color: {{ $transaction['type'] == 'income' ? 'green' : 'red' }};">
                                {{ $transaction['amount'] }}
                            </td>
                            <td>{{ $transaction['type'] == 'income' ? 'Пополнение' : 'Списание' }}
                            <td>{{ $transaction['description'] }}</td>
                            <td>{{ $transaction['balance'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{-- {{
    var_dump($result['transactions']);
}} --}}
    @endif
</body>

</html>
