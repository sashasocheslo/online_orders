<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Меню Макдональдс
        // Курка
        Product::create([
            'name' => 'Курячі крильця',
            'price' => 93.00,
            'menu_id' => 1,
            'image' => '1.jpg',
            'description' => 'Соковиті та хрусткі курячі крильця, приготовані до золотистої скоринки.',
            'category_id' => 1
        ]);
        Product::create([
            'name' => 'Курячі нагетси',
            'price' => 97.00,
            'menu_id' => 1,
            'image' => '2.jpg',
            'description' => 'Ніжні шматочки курячого філе в хрусткій паніровці, подаються з вибором соусів.',
            'category_id' => 1
        ]);
        Product::create([
            'name' => 'Курячі стріпси',
            'price' => 302.00,
            'menu_id' => 1,
            'image' => '3.jpg',
            'description' => 'Соковиті смужки курячого філе в хрусткій паніровці, ідеальні для перекусу.',
            'category_id' => 1
        ]);

        // МакМеню
        Product::create([
            'name' => 'МакМеню Біг Мак',
            'price' => 228.00,
            'menu_id' => 1,
            'image' => '1-(mac).png',
            'description' => 'Класичний Біг Мак із двома яловичими котлетами, сиром, салатом, маринованими огірками, цибулею та фірмовим соусом, подається з картоплею фрі та напоєм.',
            'category_id' => 2,
        ]);
        Product::create([
            'name' => 'МакМеню Роял',
            'price' => 248.00,
            'menu_id' => 1,
            'image' => '2-(mac).png',
            'description' => 'Роял Чізбургер з великою яловичою котлетою, сиром, свіжими овочами та соусами, подається з картоплею фрі та напоєм.',
            'category_id' => 2,
        ]);
        Product::create([
            'name' => 'МакМеню Роял Дабл Чізбургер',
            'price' => 205.00,
            'menu_id' => 1,
            'image' => '3-(mac).png',
            'description' => 'Подвійний чізбургер з двома яловичими котлетами, сиром, маринованими огірками, цибулею, кетчупом та гірчицею, подається з картоплею фрі та напоєм.',
            'category_id' => 2,
        ]);
        Product::create([
            'name' => 'МакМеню Філе-о-Фіш',
            'price' => 205.00,
            'menu_id' => 1,
            'image' => '4-(mac).png',
            'description' => 'Ніжне рибне філе в паніровці з сиром та тартаром на м\'якій булочці, подається з картоплею фрі та напоєм.',
            'category_id' => 2,
        ]);

        // Бургери
        Product::create([
            'name' => 'ГАМБУРГЕР',
            'price' => 60.00,
            'menu_id' => 1,
            'image' => '1-(hamberger).png',
            'description' => 'Біфштекс із яловичини, цибуля, шматочок маринованого огірка, заправлені гірчицею і кетчупом, у запашній булочці із пшеничного борошна. ',
            'category_id' => 3
        ]);
        Product::create([
            'name' => 'ЧІЗБУРГЕР',
            'price' => 70.00,
            'menu_id' => 1,
            'image' => '2-(cheesburger).png',
            'description' => 'Біфштекс із яловичини, шматочок сиру «Чедер», шматочок маринованого огірка та цибуля, заправлені гірчицею і кетчупом, у булочці із пшеничного борошна.',
            'category_id' => 3
        ]);
        Product::create([
            'name' => 'МАКЧІКЕН',
            'price' => 117.00,
            'menu_id' => 1,
            'image' => '3-(macchicken).png',
            'description' => 'Хрустка куряча котлета зі свіжим салатом і ніжним соусом.',
            'category_id' => 3
        ]);
        Product::create([
            'name' => 'РОЯЛ ЧІЗБУРГЕР',
            'price' => 157.00,
            'menu_id' => 1,
            'image' => '4-(RoyalCheeseburger).png',
            'description' => 'Біфштекс із натуральної яловичини, два слайси сиру «Чедер», два шматочки маринованого огірка та свіжа цибуля, заправлені гірчицею та кетчупом.',
            'category_id' => 3
        ]);
        Product::create([
            'name' => 'БІГ МАК',
            'price' => 137.00,
            'menu_id' => 1,
            'image' => '5-(bigmac).png',
            'description' => 'Два біфштекси з яловичини, цибуля, маринований огірок, сир «Чедер», свіжий салат, заправлені соусом «Біг Мак», у булочці з насінням сезаму.',
            'category_id' => 3
        ]);

        // Десерти
        Product::create([
            'name' => 'МАФІН КРЕМ-СОЛОНА КАРАМЕЛЬ',
            'price' => 71.00 ,
            'menu_id' => 1,
            'image' => '1-(mafin).png',
            'description' => 'Мафін із ніжним поєднанням кремової начинки й солоної карамелі, политий зверху шоколадом та присипаний хрусткими горішками.',
            'category_id' => 4
        ]);
        Product::create([
            'name' => 'МАКПИРІГ® СЛИВА-КОРИЦЯ',
            'price' => 53.00,
            'menu_id' => 1,
            'image' => '2-(McPie).png',
            'description' => 'Морозиво з додаванням шматочків батончика Snickers та карамельною поливою.',
            'category_id' => 4
        ]);
        Product::create([
            'name' => 'КРУАСАН З КАКАО-КРЕМОМ',
            'price' => 53.00 ,
            'menu_id' => 1,
            'image' => '3-(CROISANT_CHOKO).png',
            'description' => 'Запашний круасан із хрумкою скоринкою та ніжним какао-кремом.',
            'category_id' => 4
        ]);
        Product::create([
            'name' => 'МАКПИРІГ ВИШНЕВИЙ',
            'price' => 47.00,
            'menu_id' => 1,
            'image' => '4-(PIE_TOP).png',
            'description' => 'Хрумкий, свіжоспечений МакПиріг® із вишневим наповнювачем.',
            'category_id' => 4
        ]);

        // Напої
        Product::create([
            'name' => 'Кока-Кола',
            'price' => 55.00,
            'menu_id' => 1,
            'image' => '1-cola-big.jpg',
            'description' => 'Класичний газований напій зі смаком коли.',
            'category_id' => 5,
            'size' => 'Велика'
        ]);
        Product::create([
            'name' => 'Кока-Кола',
            'price' => 51.00,
            'menu_id' => 1,
            'image' => '1-cola-middle.jpg',
            'description' => 'Класичний газований напій зі смаком коли.',
            'category_id' => 5,
            'size' => 'Середня'
        ]);
        Product::create([
            'name' => 'Кока-Кола',
            'price' => 40.00,
            'menu_id' => 1,
            'image' => '1-cola-small.jpg',
            'description' => 'Класичний газований напій зі смаком коли.',
            'category_id' => 5,
            'size' => 'Маленька'
        ]);

        Product::create([
            'name' => 'Фанта',
            'price' => 55.00,
            'menu_id' => 1,
            'image' => '2-fanta-big.jpg',
            'description' => 'Освіжаючий газований напій зі смаком апельсина.',
            'category_id' => 5,
            'size' => 'Велика'
        ]);
        Product::create([
            'name' => 'Фанта',
            'price' => 51.00,
            'menu_id' => 1,
            'image' => '2-fanta-middle.jpg',
            'description' => 'Освіжаючий газований напій зі смаком апельсина.',
            'category_id' => 5,
            'size' => 'Середня'
        ]);
        Product::create([
            'name' => 'Фанта',
            'price' => 40.00,
            'menu_id' => 1,
            'image' => '2-fanta-small.jpg',
            'description' => 'Освіжаючий газований напій зі смаком апельсина.',
            'category_id' => 5,
            'size' => 'Маленька'
        ]);

        Product::create([
            'name' => 'Спрайт',
            'price' => 55.00,
            'menu_id' => 1,
            'image' => '3-sprite-big.jpg',
            'description' => 'Легкий газований напій зі смаком лимона та лайма.',
            'category_id' => 5,
            'size' => 'Велика'
        ]);
        Product::create([
            'name' => 'Спрайт',
            'price' => 51.00,
            'menu_id' => 1,
            'image' => '3-sprite.middle.jpg',
            'description' => 'Легкий газований напій зі смаком лимона та лайма.',
            'category_id' => 5,
            'size' => 'Середня'
        ]);
        Product::create([
            'name' => 'Спрайт',
            'price' => 40.00,
            'menu_id' => 1,
            'image' => '3-sprite.small.jpg',
            'description' => 'Легкий газований напій зі смаком лимона та лайма.',
            'category_id' => 5,
            'size' => 'Маленька'
        ]);

        Product::create([
            'name' => 'Чай чорний',
            'price' => 150.00,
            'menu_id' => 1,
            'image' => '4-(tea).jpg',
            'description' => 'Гарячий напій із заварки чорного чаю.',
            'category_id' => 5
        ]);
        Product::create([
            'name' => 'Чай зелений',
            'price' => 150.00,
            'menu_id' => 1,
            'image' => '5-(tea).png',
            'description' => 'Легкий та ароматний зелений чай, ідеальний для релаксу.',
            'category_id' => 5
        ]);

        // Роли
        Product::create([
            'name' => 'ФІШ РОЛ',
            'price' => 141.00,
            'menu_id' => 1,
            'image' => '1-(FishRoll).png',
            'description' => 'Лаваш із соковитим філе тріски в паніровці, свіжий огірок і салат латук із соусом «Тартар».',
            'category_id' => 6
        ]);
        Product::create([
            'name' => 'ЧІКЕН РОЛ',
            'price' => 139.00,
            'menu_id' => 1,
            'image' => '2-(СhickenRoll).png',
            'description' => 'Загорнуті в тонкий лаваш курячі стріпси, свіжі помідори, хрумкий салат латук із «Медово-гірчичним» соусом.',
            'category_id' => 6
        ]);

        // Картопля
        Product::create([
            'name' => 'КАРТОПЛЯ ФРІ З СИРНИМ СОУСОМ ТА БЕКОНОМ',
            'price' => 112.00,
            'menu_id' => 1,
            'image' => '1-(potato).png',
            'description' => 'Улюблена картопелька Фрі з апетитним сирним соусом, що посипана хрумким обсмаженим беконом.',
            'category_id' => 7
        ]);

        Product::create([
            'name' => 'КАРТОПЛЯ ФРІ',
            'price' => 82.00,
            'menu_id' => 1,
            'image' => '2-potato-big.png',
            'description' => 'Добірна картопля, засмажена в натуральній олії та трохи підсолена.',
            'category_id' => 7,
            'size' => 'Велика'
        ]);

        Product::create([
            'name' => 'КАРТОПЛЯ ФРІ',
            'price' => 68.00,
            'menu_id' => 1,
            'image' => '2-potato-middle.png',
            'description' => 'Добірна картопля, засмажена в натуральній олії та трохи підсолена.',
            'category_id' => 7,
            'size' => 'Середня'
        ]);

        Product::create([
            'name' => 'КАРТОПЛЯ ФРІ',
            'price' => 55.00,
            'menu_id' => 1,
            'image' => '2-potato-small.png',
            'description' => 'Добірна картопля, засмажена в натуральній олії та трохи підсолена.',
            'category_id' => 7,
            'size' => 'Маленька'
        ]);

        // Меню КФС
        // Соковита курка
        Product::create([
            'name' => '20 нагетсів',
            'price' => 297.00,
            'menu_id' => 2,
            'image' => '1-(nagets).png',
            'description' => 'Нагетси із соковитої курки в оригінальній паніровці.',
            'category_id' => 8
        ]);

        Product::create([
            'name' => '8 стріпсів гострих',
            'price' => 230.00,
            'menu_id' => 2,
            'image' => '1-(strips).png',
            'description' => '8 стріпсів в гострій паніровці.',
            'category_id' => 8
        ]);

        Product::create([
            'name' => '4 ніжки',
            'price' => 203.00,
            'menu_id' => 2,
            'image' => '1-(legs).png',
            'description' => '4 ніжки обсмажені в хрусткій паніровці.',
            'category_id' => 8
        ]);

        Product::create([
            'name' => 'Байтс Барбекю',
            'price' => 149.00,
            'menu_id' => 2,
            'image' => '1-(bitsbarbequ).png',
            'description' => 'Соковиті шматочки курочки у пікантній хрусткій паніровці з соусом барбекю.',
            'category_id' => 8
        ]);

        Product::create([
            'name' => '9 нагетсів',
            'price' => 138.00,
            'menu_id' => 2,
            'image' => '1-(9-nagets).png',
            'description' => 'Нагетси із соковитої курки в оригінальній паніровці.',
            'category_id' => 8
        ]);

         // Бургери КФС
         Product::create([
            'name' => 'Грандер Бургер гострий',
            'price' => 220.00,
            'menu_id' => 2,
            'image' => '2-(grander-burger).png',
            'description' => 'Хрустка булочка з начинкою з гострого курячого філе, маринованих огірків, томатів, салату, сиру та беконового соусу.',
            'category_id' => 9
        ]);

        Product::create([
            'name' => 'Грандер Бургер оригінальний',
            'price' => 220.00,
            'menu_id' => 2,
            'image' => '2-(grander-burger_2).png',
            'description' => 'Хрустка булочка з начинкою з оригінального курячого філе, маринованих огірків, томатів, салату, сиру та беконового соусу.',
            'category_id' => 9
        ]);

        Product::create([
            'name' => 'Український Дабл Чікен OR',
            'price' => 211.00,
            'menu_id' => 2,
            'image' => '2-(urk-double.chicken_or).png',
            'description' => 'Два соковитих оригінальних шматків курячого філе з начинкою маринованованих огірків, слайсом бекону, сухої цибулі, скибочки сиру приправлена часниковим соусом, поміж них.',
            'category_id' => 9
        ]);

        Product::create([
            'name' => 'Італійський Бургер оригінальний',
            'price' => 150.00,
            'menu_id' => 2,
            'image' => '2-(italy-burger_orig).png',
            'description' => 'Булочка з насінням кунжуту, начинка з оригінального курячого філе, курячої ковбаси Пепероні та сиру, приправлені соусами.',
            'category_id' => 9
        ]);

        Product::create([
            'name' => 'Чізбургер new',
            'price' => 72.00,
            'menu_id' => 2,
            'image' => '2-(chees_burger).png',
            'description' => 'Стріпс в оригінальній паніровці, сир, кетчуп, гірчиця, огірки з пшеничною булочкою.',
            'category_id' => 9
        ]);


        // Гарніри
        Product::create([
            'name' => 'Рис Бокс',
            'price' => 184.00,
            'menu_id' => 2,
            'image' => '3-(rise_box).png',
            'description' => 'Риз з овочами та 6 байтсів під соусом теріякі з кунжутом.',
            'category_id' => 10
        ]);

        Product::create([
            'name' => 'Цибулеві Кільця 8шт',
            'price' => 129.00,
            'menu_id' => 2,
            'image' => '3-(onion_rings).png',
            'description' => 'Хрумкі, в золотистій скоринці пікантні цибулеві кільця, з пряним смаком.',
            'category_id' => 10
        ]);

        Product::create([
            'name' => 'Картопля по-домашньому',
            'price' => 94.00,
            'menu_id' => 2,
            'image' => '3-(potatos_home).png',
            'description' => 'Смачна хрустка картопля по-домашньому.',
            'category_id' => 10
        ]);

        Product::create([
            'name' => 'Українська картопля фрі',
            'price' => 106.00,
            'menu_id' => 2,
            'image' => '3(urk_potatos_free).png',
            'description' => 'Золотиста картопля фрі у поєднанні з часниковим соусом та хрусткою цибулею.',
            'category_id' => 10
        ]);

        // Піца-твістер
        Product::create([
            'name' => 'Гавайська Піца-твістер',
            'price' => 199.00,
            'menu_id' => 2,
            'image' => '4-(gavai_pizza_twister).png',
            'description' => 'Обсмажена в тостері тортилья, з начинкою з байтсів з курячого філе, свіжої руколи, бекону, шматочків ананаса та сиру приправлена соусами.',
            'category_id' => 11
        ]);

        Product::create([
            'name' => 'Пепероні Піца-твістер',
            'price' => 199.00,
            'menu_id' => 2,
            'image' => '4-(paperoni_pizza_twister).png',
            'description' => 'Обсмажена в тостері тортилья, з начинкою з байтсів з курячого філе, свіжої руколи, бекону, шматочків ананаса та сиру приправлена соусами.',
            'category_id' => 11
        ]);

        Product::create([
            'name' => 'Барбекю Піца-твістер',
            'price' => 199.00,
            'menu_id' => 2,
            'image' => '4-(barbequ_pizza_twister).png',
            'description' => 'Обсмажена в тостері тортилья з начинкою з байтсів курячого філе, свіжої руколи, бекону, кукурудзи та хрусткої смаженої цибулі, приправлена соусами.',
            'category_id' => 11
        ]);

        Product::create([
            'name' => 'Сирна Піца-твістер',
            'price' => 199.00,
            'menu_id' => 2,
            'image' => '4-(chees_pizza_twister).png',
            'description' => 'Обсмажена в тостері тортилья з начинкою з байтсів з курячого філе, плавленого сиру, сиру Маасдам, руколи, кукурудзи приправлена соусом.',
            'category_id' => 11
        ]);


        // Дисерти
        Product::create([
            'name' => 'Сінабон з вишнею',
            'price' => 100.00,
            'menu_id' => 2,
            'image' => '5-(sinabon_with_cherry).png',
            'description' => 'Смачна булочка сінабон з вишнею та шоколадом.',
            'category_id' => 11
        ]);

        Product::create([
            'name' => 'Морозиво "Кріспі Кранч',
            'price' => 84.00,
            'menu_id' => 2,
            'image' => '5-(ice_cream_cristi_cranch).png',
            'description' => 'Ніжне вершкове морозиво з хрусткою крихтою та топінгом на вибір: шоколад, полуниця, карамель.',
            'category_id' => 12
        ]);

        Product::create([
            'name' => 'Донат полуничний',
            'price' => 61.00,
            'menu_id' => 2,
            'image' => '5-(donats_strawberry).png',
            'description' => 'Ніжний полуничний донат.',
            'category_id' => 12
        ]);

        Product::create([
            'name' => 'Донат маракуйя',
            'price' => 61.00,
            'menu_id' => 2,
            'image' => '5-(donats_maraqua).jpg',
            'description' => 'Екзотичний донат з начинкою із маракуйї та шматочками чорного шоколаду.',
            'category_id' => 12
        ]);

        Product::create([
            'name' => 'Донат солона карамель',
            'price' => 61.00,
            'menu_id' => 2,
            'image' => '5-(donats_salt_caramel).png',
            'description' => 'Дивовижний донат солона кармель.',
            'category_id' => 12
        ]);

        Product::create([
            'name' => 'Пиріжок із вишнею',
            'price' => 50.00,
            'menu_id' => 2,
            'image' => '5-(cherry_pie).png',
            'description' => 'Гарячий пиріжок в ніжному тісті з вишневою начинкою.',
            'category_id' => 12
        ]);

        // Напої KFC
        Product::create([
            'name' => 'Пепсі Блек(пляшка)',
            'price' => 55.00,
            'menu_id' => 2,
            'image' => '6-(pepsi_bleck).png',
            'category_id' => 13
        ]);

        Product::create([
            'name' => 'Мірінда(пляшка)',
            'price' => 55.00,
            'menu_id' => 2,
            'image' => '6-(mirinda_bottle).png',
            'category_id' => 13
        ]);

        Product::create([
            'name' => 'Севен Ап(пляшка)',
            'price' => 55.00,
            'menu_id' => 2,
            'image' => '6-(seven_ap).png',
            'category_id' => 13
        ]);

        Product::create([
            'name' => 'Пепсі(пляшка)',
            'price' => 55.00,
            'menu_id' => 2,
            'image' => '6-(pepsi).png',
            'category_id' => 13
        ]);

        // Мілкшейк
        Product::create([
            'name' => 'Шейк Шоколад-Горіх',
            'price' => 83.00,
            'menu_id' => 2,
            'image' => '7-(milk_shak_nut).png',
            'description' => 'Молочний коктейль шоколадно-горіховий.',
            'category_id' => 14
        ]);

        Product::create([
            'name' => 'Шейк Полуниця',
            'price' => 83.00,
            'menu_id' => 2,
            'image' => '7-(strawberry_shake).png',
            'description' => 'Молочний коктейль зі смаком полуниці .',
            'category_id' => 14
        ]);

        // Меню Domino's Pizza
        // Піца
        Product::create([
            'name' => 'Піца Гавайська',
            'price' => 288.00,
            'menu_id' => 3,
            'image' => 'pizza_hawaiian.png',
            'description' => 'Ананас, курка, моцарела, соус Domino’s.',
            'category_id' => 15
        ]);

        Product::create([
            'name' => 'Піца П’ять Сирів',
            'price' => 352.00,
            'menu_id' => 3,
            'image' => 'pizza_five_cheeses.png',
            'description' => 'Бергадер Блю, моцарела, пармезан, соус Альфредо, фета, чеддер.',
            'category_id' => 15
        ]);

        Product::create([
            'name' => 'Піца Мітца',
            'price' => 352.00,
            'menu_id' => 3,
            'image' => 'pizza_meatza.png',
            'description' => 'Бекон, ковбаски баварські, моцарела, пармезан, пепероні, соус Domino’s, шинка.',
            'category_id' => 15
        ]);

        Product::create([
            'name' => 'Піца Маргарита',
            'price' => 257.00,
            'menu_id' => 3,
            'image' => 'pizza_margherita.png',
            'description' => 'Подвійна порція моцарели, соус Domino’s.',
            'category_id' => 15
        ]);

        Product::create([
            'name' => 'Піца Барбекю',
            'price' => 199.00,
            'menu_id' => 3,
            'image' => 'pizza_bbq.png',
            'description' => 'Бекон, гриби, курка, моцарела, соус Барбекю, цибуля.',
            'category_id' => 15
        ]);

        // Десерти
        Product::create([
            'name' => 'Чізкейк Нью-Йорк',
            'price' => 75.00 ,
            'menu_id' => 3,
            'image' => 'cheesecake_ny.png',
            'description' => 'Класичний чізкейк у стилі Нью-Йорк.',
            'category_id' => 16
        ]);

        Product::create([
            'name' => 'Мафін Кокосовий',
            'price' => 44.00,
            'menu_id' => 3,
            'image' => 'mafin_coconaut.png',
            'description' => 'Кокосовий смак з нотками ванілі та лимону, що створює досить екзотичну та освіжаючу смакову комбінацію.',
            'category_id' => 16
        ]);

        Product::create([
            'name' => 'Захер',
            'price' => 75.00,
            'menu_id' => 3,
            'image' => 'zaher.png',
            'description' => 'Легендарний австрійський десерт із багатим шоколадним смаком, доповненим тонким шаром абрикосового джему.',
            'category_id' => 16
        ]);

        Product::create([
            'name' => 'Мафін дабл карамель',
            'price' => 50.00,
            'menu_id' => 3,
            'image' => 'muffin_doublecaramel.png',
            'description' => 'Ніжна та легка текстура шоколадної основи мафіну, у середині з карамельною начинкою.',
            'category_id' => 16
        ]);

        // Напої
        Product::create([
            'name' => 'Coca-Cola',
            'price' => 54.00,
            'menu_id' => 3,
            'image' => 'coca_cola_05.png',
            'description' => 'Охолоджений напій Coca-Cola об’ємом.',
            'category_id' => 17
        ]);

        Product::create([
            'name' => 'Сік апельсиновий Rich',
            'price' => 112.00,
            'menu_id' => 3,
            'image' => 'rich_orange_025.png',
            'description' => 'Сік апельсиновий Rich об’ємом.',
            'category_id' => 17
        ]);

        Product::create([
            'name' => 'Мінеральна вода Bonaqua',
            'price' => 41.00,
            'menu_id' => 3,
            'image' => 'bonaqua_05.png',
            'description' => 'Мінеральна вода Bonaqua об’ємом.',
            'category_id' => 17
        ]);

        Product::create([
            'name' => 'Schweppes Індіан Тонік',
            'price' => 47.00,
            'menu_id' => 3,
            'image' => 'shweppes_indinian_tonik.png',
            'description' => 'Коктейль з Jack Daniel’s та Coca-Cola.',
            'category_id' => 17
        ]);


        // Закуски
        Product::create([
            'name' => 'Курячі крильця BBQ',
            'price' => 208.00,
            'menu_id' => 3,
            'image' => 'bbq_wings.png',
            'description' => 'Курячі крильця в соусі BBQ.',
            'category_id' => 18
        ]);

        Product::create([
            'name' => 'Запечена картопля з ковбасками',
            'price' => 162.00,
            'menu_id' => 3,
            'image' => 'potato_baked_with_sousich.png',
            'description' => 'Соус Барбекю, Картопляні Шматочки, Сосиски Баварські, Бекон, Чеддер, Соус Бургер.',
            'category_id' => 18
        ]);

        Product::create([
            'name' => 'Салат з куркою',
            'price' => 135.00,
            'menu_id' => 3,
            'image' => 'salad_with_chicken.png',
            'description' => 'Свіже листя салату, шматочки соковитої курячої грудки, чері, огірки, болгарський перець і хрусткі сухарики.',
            'category_id' => 18
        ]);

        Product::create([
            'name' => 'Салат з в’яленими томатами',
            'price' => 145.00,
            'menu_id' => 3,
            'image' => 'salad-driedtomato-sideview.png',
            'description' => 'Соковиті міні роли з курячим філе та сиром у ніжному тісті.',
            'category_id' => 18
        ]);
    }
}
