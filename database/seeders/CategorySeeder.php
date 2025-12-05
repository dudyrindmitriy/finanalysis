<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'Переводы',
                'color' => '#0172ad', // основной синий
            ],
            [
                'name' => 'Продукты',
                'color' => '#2e8fc8', // светлый синий
            ],
            [
                'name' => 'Прочее',
                'color' => '#5cace3', // голубой
            ],
            [
                'name' => 'Транспорт',
                'color' => '#89c9ff', // очень светлый синий
            ],
            [
                'name' => 'Рестораны',
                'color' => '#adc2ff', // лавандовый
            ],
            [
                'name' => 'Здоровье',
                'color' => '#d1bbff', // сиреневый
            ],
            [
                'name' => 'Одежда',
                'color' => '#f5b3ff', // розоватый
            ],
            [
                'name' => 'Развлечения',
                'color' => '#ffacf7', // фуксия
            ],
            [
                'name' => 'Электроника',
                'color' => '#ffa5d1', // кораллово-розовый
            ],
            [
                'name' => 'Услуги',
                'color' => '#ff9eab', // лососевый
            ],
            [
                'name' => 'Связь и интернет',
                'color' => '#ff9785', // персиковый
            ],
            [
                'name' => 'Красота',
                'color' => '#ff9060', // оранжевый
            ],
            [
                'name' => 'Дом и ремонт',
                'color' => '#ff893b', // яркий оранжевый
            ],
            [
                'name' => 'Книги и образование',
                'color' => '#ff8216', // темный оранжевый
            ],
            [
                'name' => 'Дети',
                'color' => '#ff7b00', // янтарный
            ]
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
