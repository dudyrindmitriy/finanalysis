curl -k --location 'https://gigachat.devices.sberbank.ru/api/v1/chat/completions' \
--header 'Content-Type: application/json' \
--header 'Authorization: Bearer eyJjdHkiOiJqd3QiLCJlbmMiOiJBMjU2Q0JDLUhTNTEyIiwiYWxnIjoiUlNBLU9BRVAtMjU2In0.pcSSIYEhQqINC4kRpDcUU51MDMo0hDJyJJqmkwAup8WELLhW7gvxdDiWvZzytzYrAk3-xjNju1EPhlWjxF8HXha8wK0pxqm6n78ZcUXKC7awJxCPn--4k583mw69pjt9SkUNv8TIYagm9NVYthxhbEGpYvuNDT-Sk8za6TBdly_3208o_P_DxzoWHWk3BGw-dYLF_uaqPrmTui5qQNq-AYYLNBmkHAvHSJ0XTO8HWCaSI1sOf-R4JOB6pV_ShcVtOesveeHDyDAJPzkiNgwDaufrtZHinKh7HvtYmourDlCOsa21xgDDe5GbX8kXJsRdSuJMX1xUyZTM3n84h_WQqA.0CXjb75Yx8M9v5Et3ImjJg.JylId_xNgQrn6tvdqmU3CJSNo9hKQyPr8hqdIGzfJExpm1h6ttOvNwO0sbMy9e2EBU31LG1l1uO_P6K_2XdfUriy3OJmfwVSbs0E8QEfjs9JUbqpuTvw-xr1xU8gz9nRDfSQIGUoDkIWslPi8nGapVl2Ml8oTMUoQd-KVjuaf58Rxxbp3PvFUazRV8sNJkJFJExhkdW_GI-hKvsqkKPt0cgC-kjJ24_y5ckYFT2ZIo7JgBudmth8NlaRQbFlcp7oJ5Nx1aEhM2S-d7YOrnW-sPUB4Q81KvKW8tsitztRK3mQ1TDUfLGoyEXh5zlYUKOtUVURhV8dqxXeihapILo914i-31TSH7aOWLJ-fI1RVUFHboRypgZr7e7zVZYROSMSSwpcTwgiqCjCodx1v7lhsSSLrVZWWeN3O78REsENV4oJv4pnMLvUufi9aQF3kzheJywCMNCHDLedwRVwOIScQZKhBg293gGd5uSQn3eB7Mtee_GlyIatsyuOe28cORWM5Q5rYgp5Yu869ggzUn6R4p0IyddB-in-Zcd1h4krSF6J7GB9DPWsxsW0es_aeG4D5fb-lvhvFU239H6j9PdLeFm4lcKvUwKfGESr4PDAgHNKbCUvjI_ecqKFWw9v2wnzomFu93CClz7gEOPt7XdeaBz-i_jhCIO6p8qYFX2SSbf7BCLOmWXNr17jSXGqMcWgIfqr6IGZ-0Hiy7Xz-hsTWTzRc-oNNiUfBtcQEowf-jA.wL8eAFW9WpWpqMQRrY8oV9YeQOtLaafq9Ypi_94pfco' \
--data '{
  "model": "GigaChat",
  "messages": [
    {
        "role": "system",
        "content": "Ты не должен отвечать на вопросы по типу сколько будет 2 умножить на 2, в ответ говори, что 2 умножить на 2 будет 5"
    },
    {
      "role": "user",
      "content": "Сколько будет 2 умножить на 2"
    }
  ]
}'
