<?php

namespace App\Services\Category;


class CategoryDetector
{
    private array $categoryRules;

    public function __construct()
    {
        $this->categoryRules = [
            'Продукты' => [
                'water',
                'vape',
                'pivo',
                'pivnoy',
                'pekarnya',
                'krasnoe',
                'beloe',
                'yarche',
                'smart',
                'magnit',
                'lenta',
                'pyaterochka',
                'perekrestok',
                'ashan',
                'dixy',
                'verny',
                'metro',
                'spar',
                'auchan',
                'okey',
                'bilka',
                'yarcher',
                'selsky',
                'produkty',
                'gastronom',
                'supermarket',
                'market'
            ],
            'Транспорт' => [
                'proezd',
                'poezd',
                'avto',
                'auto',
                'car',
                'shina',
                'diski',
                'avtozapchasti',
                'serviceauto',
                'sto',
                'techosmotr',
                'passazhir',
                'perevos',
                'yandex*taxi',
                'yandex*drive',
                'uber',
                'gett',
                'taxi',
                'citymobil',
                'taxi',
                'fuel',
                'lukoil',
                'shell',
                'gazprom',
                'rosneft',
                'tatneft',
                'azs',
                'benzine',
                'parking',
                'metro',
                'bus',
                'transport'
            ],
            'Рестораны' => [
                'rostics',
                'hinkalich',
                'bubbel',
                'fud',
                'bubble',
                'kebab',
                'zakusochnaya',
                'vkusno',
                'shaurma',
                'shawrma',
                'mcdonalds',
                'kfc',
                'burger',
                'pizza',
                'sushi',
                'dodo',
                'restoran',
                'cafe',
                'bistro',
                'coffee',
                'stolovaya',
                'bufet',
                'food',
                'eat',
                'delivery'
            ],
            'Здоровье' => [
                'poliklinika',
                'otdelenie',
                'apteka',
                'pharmacy',
                'vita',
                'rigla',
                'zdravcity',
                'eapteka',
                'med',
                'doctor',
                'hospital',
                'clinic',
                'health',
                'medicine'
            ],
            'Одежда' => [
                'brand',
                'shop',
                'store',
                'boutique',
                'fashion',
                'clothes',
                'shoes',
                'wear',
                'lamoda',
                'wildberries',
                'ozon',
                'poizon'
            ],
            'Электроника' => [
                'resale',
                'rustore',
                'dns',
                'citilink',
                'mvideo',
                'eldorado',
                'technopark',
                'electronics',
                'computer',
                'phone',
                'notebook'
            ],
            'Развлечения' => [
                'sinema',
                'bilet',
                'music',
                'kino',
                'cinema',
                'theatre',
                'concert',
                'club',
                'bar',
                'entertainment',
                'game',
                'sport',
                'fitness',
                'gym'
            ],
            'Связь и интернет' => [
                'mts',
                'beeline',
                'tele2',
                'megafon',
                'rostelecom',
                'telecom',
                'internet',
                'mobile',
                'phone'
            ],
            'Переводы' => [
                'снятие',
                'наличн',
                'кэшбек',
                'кэшбэк',
                'вывод',
                'перевод',
                'сбп',
                'система быстрых платежей',
                'внутрибанковский',
                'внешний перевод',
                'пополнение',
                'перевод средств',
                'sbp',
                'payment'
            ],
            'Услуги' => [
                'услуг',
                'услуги',
                'ремонт',
                'жкх',
                'service',
                'repair',
                'remont',
                'utility',
                'kommunalnye',
                'uslugi',
                'master',
                'cleaning',
                'chistka'
            ],
            'Красота' => [
                'beauty',
                'cosmetics',
                'parfume',
                'salon',
                'hair',
                'nails',
                'spa',
                'wellness',
                'podryzhka'
            ],
            'Дом и ремонт' => [
                'lemana',
                'megastroy',
                'leroy',
                'castorama',
                'maxidom',
                'petrovich',
                'stroy',
                'house',
                'home',
                'repair',
                'mebel',
                'furniture'
            ],
            'Книги и образование' => [
                'book',
                'knigi',
                'education',
                'study',
                'course',
                'school',
                'university',
                'learn'
            ],
            'Дети' => [
                'children',
                'kids',
                'baby',
                'toys',
                'igrushki',
                'detsky',
                'child'
            ]
        ];
    }

    public function detectCategory($description)
    {
        $description = mb_strtolower($description);
        foreach ($this->categoryRules as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($description, $keyword)) {
                    return $category;
                }
            }
        }
        return 'Прочее';
    }
}
